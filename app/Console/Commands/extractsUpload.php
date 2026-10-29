<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class extractsUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank:upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload extracts';

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->info("Uploading extracts");
        
        // prepare file locations
        $bankDisk = Storage::disk("bankToUpload");
        $uploadDisk = Storage::disk("toUpload");
        $count = 0;
        
        foreach($uploadDisk->files() as $fileName) {
            // read file content
            $content = $uploadDisk->get($fileName);

            // upload file
            $upload = $bankDisk->put($fileName, $content);
            
            // delete original file
            if ($upload)
                $uploadDisk->delete($fileName);
                
            $count++;
        }
        
        $this->info("Load $count files complete");
    }
}
