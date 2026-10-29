<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class extractsDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download extracts';

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->info("Downloading extracts");
        
        // prepare file locations
        $bankDisk = Storage::disk("bankToDownload");
        $backupDisk = Storage::disk("encryptedBackup");
        $count = 0;
        
        // Read only the files non recursive
        foreach($bankDisk->files() as $filename) {
            // Get the file content
            $content = $bankDisk->get($filename);

            // Encrypted file backup
            $backupDisk->put($filename, $content);
            
            // Select the required disk
            if (str_starts_with(strtolower($filename), 'f'))
                $encryptedDisk = Storage::disk("encryptedDownload");
                
            else
                $encryptedDisk = Storage::disk("responseDownload");
            
            // local encrypted file
            if ($encryptedDisk->put($filename, $content))
                $bankDisk->delete($filename);
        
            $count++;        
        }
        
        $this->info("Download $count files complete");
    }
}
