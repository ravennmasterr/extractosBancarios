<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class orderBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank:order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'order backup files';

    /**
     * Execute the console command.
     */
    public function handle() {
        // Move to backup dir
        $backupDisk = Storage::disk("encryptedBackup");
        
        // List dir content
        $list = collect($backupDisk->listContents(""));
        
        // $dirs = collect($backupDisk->directories("", false));
        // $files = collect($backupDisk->files("", false));
        
        $dirs = $list->filter(function ($item) {
            return $item['type'] === 'dir';
        });
        
        $files = $list->filter(function ($item) {
            return $item['type'] === 'file';
        });
        
        foreach ($files as $file) {
            // Get file path and name
            $path = $file['path'];
            $name = basename($path);

            // Ignore first 3 characters y and retrive next five 5
            $month = substr($name, 3, 2);
            $year = substr($name, 5, 4);
                
            $code = "$year$month";

            // Search for a folder whose name contains the code
            $dir = $dirs->first(function ($item) use ($code) {
                return str_contains(
                    basename($item['path']),
                    $code
                );
            });

            // create folder if not exists
            if (!$dir) {
                $backupDisk->makeDirectory($code);

                // Agregar el dir al listado para futuras búsquedas
                $dir = [
                    'type' => 'dir',
                    'path' => $code
                ];

                $dirs->push($dir);

            } else {
                $folder = $dir['path'];
            }

            // Construir la ruta final conservando el nombre del archivo
            $newPath = "$folder/$name";

            // Mover el archivo al dir correspondiente
            $backupDisk->move($path, $newPath);
        }
    }
}
