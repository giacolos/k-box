<?php

namespace KlinkDMS\Listeners;

use Log;
use KlinkDMS\File;
use KlinkDMS\DocumentDescriptor;
use KlinkDMS\Events\UploadCompleted;
use Klink\DmsDocuments\DocumentsService;
use Avvertix\TusUpload\Events\TusUploadCompleted;

class TusUploadCompletedHandler
{
    /**
     * @var \Klink\DmsDocuments\DocumentsService
     */
    private $documentsService = null;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(DocumentsService $documentsService)
    {
        $this->documentsService = $documentsService;
    }

    /**
     * Handle the event.
     *
     * @param  TusUploadCompleted  $event
     * @return void
     */
    public function handle(TusUploadCompleted $event)
    {
        Log::info("Upload {$event->upload->request_id} completed.");

        try {
            $file = File::where('request_id', $event->upload->request_id)->first();

            if (is_null($file)) {
                throw new \Exception("File for upload {$event->upload->request_id} not found");
            }

            $descriptor = $this->updateDescriptor($file, $event);

            $descriptor = $descriptor->fresh();
            
            event(new UploadCompleted($descriptor, $descriptor->owner));
        } catch (\Exception $ex) {
            Log::error('File move or descriptor update error while handling the TusUploadCompleted event.', ['upload' => $event->upload,'error' => $ex]);
        }
    }

    private function updateDescriptor($file, $event)
    {
        $descriptor = $file->document;
        
        try {

            // the base filename on disk is based on the UUID of the Descriptor
            // then the call to  $this->documentsService->constructLocalPathForImport will give us

            $extension = pathinfo($file->name, PATHINFO_EXTENSION);

            if (empty($extension)) {
                $extension = \KlinkDocumentUtils::getExtensionFromMimeType($file->mime_type);
            }

            $filename = $descriptor->uuid.'.'.$extension;

            $destination = $this->documentsService->constructLocalPathForImport($filename);

            // move the file to the new location
            Log::info("Moving from {$event->upload->path()} to {$destination}");
            rename($event->upload->path(), $destination);

            $file->path = $destination;

            $file->hash = \KlinkDocumentUtils::generateDocumentHash($destination);
            
            $file->upload_completed_at = \Carbon\Carbon::now();
            
            $file->save();

            $descriptor->hash = $file->hash;
            
            $descriptor->status = DocumentDescriptor::STATUS_UPLOAD_COMPLETED;
            
            $descriptor->save();
            
            return $descriptor;
        } catch (\Exception $ex) {
            Log::error('File move or descriptor update error while handling the TusUploadCompleted event.', ['upload' => $event->upload,'error' => $ex]);
            $descriptor->status = DocumentDescriptor::STATUS_ERROR;
            $descriptor->last_error = $ex;
            $descriptor->save();
        }
    }
}
