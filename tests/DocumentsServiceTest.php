<?php

use Laracasts\TestDummy\Factory;
use KlinkDMS\User;
use KlinkDMS\Institution;
use KlinkDMS\Group;
use KlinkDMS\Capability;
use KlinkDMS\DocumentDescriptor;
use Illuminate\Support\Facades\Artisan;


use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/*
 * Test something related to document descriptors management
*/
class DocumentsServiceTest extends TestCase {
    
    use DatabaseTransactions;
    
    
    public function user_provider_with_guest() {
        return array( 
			array(Capability::$ADMIN, 'admin'),
			array(Capability::$PROJECT_MANAGER, 'project_manager'),
			array(Capability::$PARTNER, 'partner'),
			array(Capability::$GUEST, 'guest'),
		);
    }

    public function user_provider_no_guest() {
        return array( 
			array(Capability::$ADMIN, 'admin'),
			array(Capability::$PROJECT_MANAGER, 'project_manager'),
			array(Capability::$PARTNER, 'partner'),
		);
    }

    
    
    /**
     * cover issue https://git.klink.asia/klinkdms/dms/issues/569
     * @dataProvider user_provider_no_guest
     */
    public function testGetDocumentCollections($caps){

        $service = app('Klink\DmsDocuments\DocumentsService');

        $user = $this->createUser( $caps );
        
        $doc = factory('KlinkDMS\DocumentDescriptor')->create([
            'owner_id' => $user->id
        ]);

        $owned_project = factory('KlinkDMS\Project')->create([
            'user_id' => $user->id,
        ]);

        $other_project = factory('KlinkDMS\Project')->create();

        $secondary_project = factory('KlinkDMS\Project')->create();

        $secondary_project->users()->save($user);
        
        $group = $service->createGroup($user, 'Personal collection of user ' . $user->id);
        
        $group->documents()->save($doc);

        Group::findOrFail($owned_project->collection_id)->documents()->save($doc);

        Group::findOrFail($secondary_project->collection_id)->documents()->save($doc);

        // simulate another user, who has access to both projects, that 
        // is adding the document to the second project
        Group::findOrFail($other_project->collection_id)->documents()->save($doc);
        
        // now get the collections of the doc
        $collections = $service->getDocumentCollections($doc, $user);

        $this->assertEquals($user->isDMSManager() ? 4 : 3, $collections->count());
        
    }
    
   
    
}