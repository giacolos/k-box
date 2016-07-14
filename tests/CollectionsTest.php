<?php

use Laracasts\TestDummy\Factory;
use KlinkDMS\User;
use KlinkDMS\Group;
use KlinkDMS\Capability;
use KlinkDMS\DocumentDescriptor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Collection;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/*
 * Test something related to document descriptors management
*/
class CollectionsTest extends TestCase {
    
    use DatabaseTransactions;
    
    
    public function user_provider_for_editpage_public_checkbox_test() {
        return array( 
			array(Capability::$ADMIN, true),
			array(Capability::$DMS_MASTER, false),
			array(Capability::$PROJECT_MANAGER, true),
			array(Capability::$PARTNER, false),
			array(Capability::$GUEST, false),
		);
    }

	/**
	 * Test that the route for documents.show is not leaking private documents to anyone
	 *
	 * @return void
	 */
	public function testSeePersonalCollectionLoginRequired( )
	{
        // create a document
        
        $user = $this->createAdminUser();
        
        // $user_not_owner = $this->createAdminUser();
        
        $service = app('Klink\DmsDocuments\DocumentsService');        
        
        $collection = $service->createGroup($user, 'Personal Collection Name');

        $url = route( 'documents.groups.show', $collection->id );
        
        $this->visit( $url )->seePageIs( route('auth.login') );
        
	}
    
    public function testSeePersonalCollectionAccessGranted( )
	{
        // create a document
        
        $user = $this->createAdminUser();
        
        $service = app('Klink\DmsDocuments\DocumentsService');        
        
        $collection = $service->createGroup($user, 'Personal Collection Name');

        $url = route( 'documents.groups.show', $collection->id );
        
        // test with login with the owner user
		
        $this->actingAs($user);
        
        $this->visit( $url )->seePageIs( $url );
        
        $this->assertResponseOk();
        
        
	}
    
    public function testSeePersonalCollectionAccessDenieded( )
	{
        // create a document
        
        $user = $this->createAdminUser();
        
        $user_not_owner = $this->createUser(Capability::$PARTNER);
        
        $service = app('Klink\DmsDocuments\DocumentsService');        
        
        $collection = $service->createGroup($user, 'Personal Collection Name');

        $url = route( 'documents.groups.show', $collection->id );
        
        // test with login with another user
        
        $this->actingAs($user_not_owner);
        
        $this->call( 'GET', $url );
        
        $this->assertResponseStatus(403);
        
	}
    
    
    public function testCollectionListing(){
        
        $user1 = $this->createUser(Capability::$PROJECT_MANAGER_NO_CLEAN_TRASH);
        
        $user2 = $this->createUser(Capability::$PROJECT_MANAGER_NO_CLEAN_TRASH);
        
        $user_admin = $this->createAdminUser();
        
        // $users = [$user1, $user2];
        
        $projectA = factory('KlinkDMS\Project')->create(['user_id' => $user1->id]);
        $projectB = factory('KlinkDMS\Project')->create(['user_id' => $user1->id]);
        $projectC = factory('KlinkDMS\Project')->create(['user_id' => $user2->id]);
        
        $service = app('Klink\DmsDocuments\DocumentsService');
        
        $grp1 = $service->createGroup($user1, 'Personal collection of user ' . $user1->id);
        $grp2 = $service->createGroup($user2, 'Personal collection of user ' . $user2->id);
        
        // current expected status:
        // $user1 => $projectA + $projectB and $grp1
        // $user2 => $projectC and $grp2
        
        $collections_user1 = $service->getCollectionsAccessibleByUser($user1);
        $this->assertNotNull($collections_user1);
        $this->assertNotNull($collections_user1->personal, 'null personal of User1');
        $this->assertNotNull($collections_user1->projects, 'null projects of User1');
        $this->assertEquals(2, $collections_user1->projects->count());
        $this->assertEquals(1, $collections_user1->personal->count());
        
        $collections_user2 = $service->getCollectionsAccessibleByUser($user2);
        $this->assertNotNull($collections_user2);
        $this->assertNotNull($collections_user2->personal, 'null personal of User2');
        $this->assertNotNull($collections_user2->projects, 'null projects of User2');
        $this->assertEquals(1, $collections_user2->projects->count());
        $this->assertEquals(1, $collections_user2->personal->count());
        
        $collections_user_admin = $service->getCollectionsAccessibleByUser($user_admin);
        $this->assertNotNull($collections_user_admin);
        $this->assertNotNull($collections_user_admin->personal, 'null personal of UserAdmin');
        $this->assertNotNull($collections_user_admin->projects, 'null projects of UserAdmin');
        $this->assertEquals(3, $collections_user_admin->projects->count());
        $this->assertEquals(0, $collections_user_admin->personal->count());
        
        // User 2 is added to Project A
        $projectA->users()->save($user2);
        
        $collections_user1 = $service->getCollectionsAccessibleByUser($user1);
        $this->assertNotNull($collections_user1);
        $this->assertNotNull($collections_user1->personal);
        $this->assertNotNull($collections_user1->projects);
        $this->assertEquals(2, $collections_user1->projects->count());
        $this->assertEquals(1, $collections_user1->personal->count());
        
        $collections_user2 = $service->getCollectionsAccessibleByUser($user2);
        $this->assertNotNull($collections_user2);
        $this->assertNotNull($collections_user2->personal);
        $this->assertNotNull($collections_user2->projects);
        $this->assertEquals(2, $collections_user2->projects->count(), 'Projects collection count after user2 has been added to ProjectA');
        $this->assertEquals(1, $collections_user2->personal->count());
        
        $collections_user_admin = $service->getCollectionsAccessibleByUser($user_admin);
        $this->assertNotNull($collections_user_admin);
        $this->assertNotNull($collections_user_admin->personal);
        $this->assertNotNull($collections_user_admin->projects);
        $this->assertEquals(3, $collections_user_admin->projects->count());
        $this->assertEquals(0, $collections_user_admin->personal->count());
        
        
        $grp3 = $service->createGroup($user2, 'Another Personal collection of user ' . $user2->id);
        
        $collections_user2 = $service->getCollectionsAccessibleByUser($user2);
        $this->assertNotNull($collections_user2);
        $this->assertNotNull($collections_user2->personal);
        $this->assertNotNull($collections_user2->projects);
        $this->assertEquals(2, $collections_user2->projects->count(), 'Projects collection count after user2 has been added to ProjectA');
        $this->assertEquals(2, $collections_user2->personal->count(), 'Personal collection final count');
        
    }
    
    public function testIsCollectionAccessible(){
        
        $service = app('Klink\DmsDocuments\DocumentsService');
        
        // create a project
        
        $project = factory('KlinkDMS\Project')->create();
        
        $user = $project->manager()->first();
        
        $project_collection = $project->collection()->first();
        
        // add a collection to it
        $collection = $service->createGroup( $user, 'sub-collection name', null, $project_collection, false );
        
        // test if sub-collection is accessible by the project admin
        
        $accessible = $service->isCollectionAccessible($user, $collection);
        
        $this->assertTrue($accessible, 'Collection is not accessible by the creator');
        
        
        $projectA = factory('KlinkDMS\Project')->create(['user_id' => $user->id]);
        $projectB = factory('KlinkDMS\Project')->create(['user_id' => $user->id]);
        $projectC = factory('KlinkDMS\Project')->create(['user_id' => $user->id]);
        
        $collection2 = $service->createGroup( $user, 'sub-sub-collection name', null, $collection, false );
        
        $accessible = $service->isCollectionAccessible($user, $collection2);
        
        $this->assertTrue($accessible, 'Collection is not accessible by the creator');
        
        $user_admin = $this->createAdminUser();
        
        
        $collection3 = $service->createGroup( $user, 'by admin', null, $project_collection, false );
        
        $accessible = $service->isCollectionAccessible($user, $collection3);
        
        $this->assertTrue($accessible, 'Collection is not accessible by the creator');
        
        $partner = $this->createUser(Capability::$PARTNER);
        
        $accessible = $service->isCollectionAccessible($partner, $collection);
        $this->assertFalse($accessible, 'Collection 1 is accessible by Partner user not added to the project');
        $accessible = $service->isCollectionAccessible($partner, $collection2);
        $this->assertFalse($accessible, 'Collection 2 is accessible by Partner user not added to the project');
        $accessible = $service->isCollectionAccessible($partner, $collection3);
        $this->assertFalse($accessible, 'Collection 3 is accessible by Partner user not added to the project');
        
        // add 1 partner user to the project
        
        $project->users()->save($partner);
        
        $accessible = $service->isCollectionAccessible($partner, $collection);
        $this->assertTrue($accessible, 'Collection 1 is not accessible by Partner user not added to the project');
        $accessible = $service->isCollectionAccessible($partner, $collection2);
        $this->assertTrue($accessible, 'Collection 2 is not accessible by Partner user not added to the project');
        $accessible = $service->isCollectionAccessible($partner, $collection3);
        $this->assertTrue($accessible, 'Collection 3 is not accessible by Partner user not added to the project');
        
    }
    
    public function testCollectionCacheForUserUpdate(){
        
        
        $user = $this->createAdminUser();
        
        $service = app('Klink\DmsDocuments\DocumentsService');
        
        
        \Cache::shouldReceive('forget')
                    ->once()
                    ->with('dms_personal_collections'. $user->id)
                    ->andReturn(true);
        
        $grp1 = $service->createGroup($user, 'Personal collection of user ' . $user->id);
        
        
        
    }
    
    
    public function testBulkCopyToCollection(){
        
        $user = $this->createAdminUser();
        
        $service = app('Klink\DmsDocuments\DocumentsService');
        
        // create one document
        $doc = factory('KlinkDMS\DocumentDescriptor')->create([
            'owner_id' => $user->id
        ]);
        
        $doc2 = factory('KlinkDMS\DocumentDescriptor')->create([
            'owner_id' => $user->id
        ]);
        
        // create one collection
        $grp1 = $service->createGroup($user, 'Personal collection of user ' . $user->id);
        
        // Add doc to collection using the BulkController@copyTo method
        // This also tests if the bulk controller handles correctly the duplicates
        
        \Session::start();
        
        $this->actingAs( $user );
        
        $this->json( 'POST', route('documents.bulk.copyto'), [
			'documents' => [ $doc->id ],
			'destination_group' => $grp1->id,
			'_token' => csrf_token()
		])->seeJson([
            'status' => 'ok',
            'message' => trans('documents.bulk.copy_completed_all', ['collection' => $grp1->name]),
        ]);
        
		$this->assertEquals(1, $grp1->documents()->count());
        
        
        // try to add a second document and again the first document
        
        $this->json( 'POST', route('documents.bulk.copyto'), [
			'documents' => [ $doc->id, $doc2->id ],
			'destination_group' => $grp1->id,
			'_token' => csrf_token()
		])->seeJson([
            'status' => 'ok',
            'message' => trans_choice('documents.bulk.copy_completed_some', 1, ['count' => 1, 'collection' => $grp1->name, 'remaining' => 1]),
        ]);
        
		$this->assertEquals(2, $grp1->documents()->count());
    }
    
    
    public function testDmsCollectionsCleanDuplicatesCommand(){
        
        $user = $this->createAdminUser();
        
        $service = app('Klink\DmsDocuments\DocumentsService');
        
        // create one document
        $doc = factory('KlinkDMS\DocumentDescriptor')->create([
            'owner_id' => $user->id
        ]);
        
        // create one collection
        $grp1 = $service->createGroup($user, 'Personal collection of user ' . $user->id);
        $grp2 = $service->createGroup($user, 'Another collection of user ' . $user->id);
        
        
        $service->addDocumentsToGroup($user, Collection::make([$doc]), $grp1, false);
        $service->addDocumentsToGroup($user, Collection::make([$doc]), $grp1, false);
        $service->addDocumentsToGroup($user, Collection::make([$doc]), $grp1, false);
        
        $this->assertEquals(3, $grp1->documents()->count());
        $this->assertEquals(0, $grp2->documents()->count());
        
        
        $exitCode = \Artisan::call('collections:clean-duplicates', [
            'collection' => $grp1->id,
            '--yes' => true,
            '--no-interaction' => true,
        ]);
        
        $this->assertEquals(0, $exitCode);
        
        $this->assertEquals(1, $grp1->documents()->count());
        $this->assertEquals(0, $grp2->documents()->count());
        
    }

    public function testDocumentService_deleteGroup(){

        $user = $this->createUser( Capability::$PARTNER );

        $group = $this->createCollection($user, true, 3);

        // get childs
        $children = $group->getChildren();

        $children_ids = $children->fetch('id')->toArray();

        $this->assertEquals(3, $children->count(), 'Children count pre-condition');

        $service = app('Klink\DmsDocuments\DocumentsService');

        $is_deleted = $service->deleteGroup($user, $group);

        $this->assertTrue($is_deleted);

        $group = Group::withTrashed()->findOrFail($group->id);

        $trashed_children = Group::withTrashed()->whereIn('id', $children_ids)->get();

        $after_delete_children = $group->getChildren();

        $this->assertTrue($group->trashed());

        // assert all childs are trashed

        $this->assertEquals(0, $after_delete_children->count());
        $this->assertEquals(3, $trashed_children->count());

    }

    /**
     * @expectedException KlinkDMS\Exceptions\ForbiddenException
     */
    public function testDocumentService_deleteGroup_forbidden(){

        $user = $this->createUser( Capability::$PARTNER );
        $user2 = $this->createUser( Capability::$PARTNER );

        $doc = $this->createCollection($user);

        $service = app('Klink\DmsDocuments\DocumentsService');

        $service->deleteGroup($user2, $doc);

    }

    public function testCollectionDelete(){

        $user = $this->createUser( Capability::$PARTNER);

        $doc = $this->createCollection($user);

        \Session::start();

        $url = route( 'documents.groups.destroy', ['id' => $doc->id, 
                '_token' => csrf_token()] );
        
        $this->actingAs($user);

        $this->delete( $url );

        $this->see('ok');
		
		$this->assertResponseStatus(202);

        $doc = Group::withTrashed()->findOrFail($doc->id);

        $this->assertTrue($doc->trashed());

    }

    
    
    public function testDocumentService_permanentlyDeleteGroup(){

        $user = $this->createUser( Capability::$PROJECT_MANAGER );

        $group = $this->createCollection($user, true, 3);

        // get childs
        $children = $group->getChildren();

        $children_ids = $children->fetch('id')->toArray();

        $this->assertEquals(3, $children->count(), 'Children count pre-condition');



        $service = app('Klink\DmsDocuments\DocumentsService');

        $service->deleteGroup($user, $group); // put doc in trash
        
        $group = Group::withTrashed()->findOrFail($group->id);

        $is_deleted = $service->permanentlyDeleteGroup($group, $user);
        
        $this->assertTrue($is_deleted);

        $exists_doc = Group::withTrashed()->find($group->id);

        

        $this->assertNull($exists_doc);

        $after_delete_children = $group->getChildren();

        $trashed_children = Group::withTrashed()->whereIn('id', $children_ids)->get();

        // assert all childs are trashed
        $this->assertEquals(0, $after_delete_children->count());
        
    }

    /**
     * @expectedException KlinkDMS\Exceptions\ForbiddenException
     */
    public function testDocumentService_permanentlyDeleteGroup_forbidden(){

        $user = $this->createUser( Capability::$PROJECT_MANAGER );
        $user2 = $this->createUser( Capability::$PARTNER );

        $group = $this->createCollection($user, false);

        $service = app('Klink\DmsDocuments\DocumentsService');

        $service->deleteGroup($user, $group); // put doc in trash

        $is_deleted = $service->permanentlyDeleteDocument($group, $user2);
        
    }


    /**
     * @expectedException Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testGroupForceDelete(){

        $user = $this->createUser( Capability::$PROJECT_MANAGER);

        $doc = $this->createCollection($user);

        \Session::start();

        $url = route( 'documents.groups.destroy', ['id' => $doc->id, 
                '_token' => csrf_token()] );
        
        $this->actingAs($user);

        $this->delete( $url );

        $this->see('ok');
		
		$this->assertResponseStatus(202);

        $url = route( 'documents.groups.destroy', [
                'id' => $doc->id, 
                'force' => true, 
                '_token' => csrf_token()] );

        $this->delete( $url );

        $this->see('ok');
		
		$this->assertResponseStatus(202);

        $doc = Group::withTrashed()->findOrFail($doc->id);


    }


    public function testMoveFromPersonalToProject(){

        $service = app('Klink\DmsDocuments\DocumentsService');

        $project = factory('KlinkDMS\Project')->create();

        $user = $this->createUser(Capability::$PROJECT_MANAGER);
        
        $collection = $service->createGroup( $user, 'personal' );
        $collection_under = $service->createGroup( $user, 'personal-sub-collection', null, $collection );
        $collection_under2 = $service->createGroup( $user, 'personal-sub-sub-collection', null, $collection_under );

        // move $collection under $project->collection()

        $service->makeGroupPublic($user, $collection);
        $service->moveGroup($user, $collection, $project->collection);

        $collection_under = $collection_under->fresh();


        $this->assertFalse($collection->is_private);
        $this->assertFalse($collection_under->is_private);
        $this->assertNotNull($collection->parent_id);

        $this->assertEquals([false, false, false], $project->collection->getDescendants()->fetch('is_private')->toArray());

    }


    public function testMoveFromProjectToPersonal(){

        $service = app('Klink\DmsDocuments\DocumentsService');

        $project = factory('KlinkDMS\Project')->create();

        $user = $this->createUser(Capability::$PROJECT_MANAGER);
        
        $collection = $service->createGroup( $user, 'personal', null, $project->collection, false );
        $collection_container = $service->createGroup( $user, 'personal-container', null );
        $collection_under = $service->createGroup( $user, 'personal-sub-collection', null, $collection, false );
        $collection_under2 = $service->createGroup( $user, 'personal-sub-sub-collection', null, $collection_under, false );

        $service->makeGroupPrivate($user, $collection);
        $service->moveGroup($user, $collection, $collection_container);

        $collection_under = $collection_under->fresh();


        $this->assertTrue($collection->is_private);
        $this->assertTrue($collection_under->is_private);
        $this->assertNotNull($collection->parent_id);
        $this->assertEquals(0, $project->collection->getDescendants()->count());

        $this->assertEquals([true, true, true], $collection_container->getDescendants()->fetch('is_private')->toArray());

    }
       
}