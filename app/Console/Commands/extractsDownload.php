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
        // Lee los archivos y directorios de manera no recursiva dentro del directorio como una coleccion
        $bankDisk = Storage::disk("bankToDownload");
        $encryptedDisk = Storage::disk("encryptedDownload");
        $backupDisk = Storage::disk("encryptedBackup");
        
        $Listado = collect($bankDisk->listContents("", true));
        
        // funcion para ordenar el contenido segun la longitud de los archivos, el tercer parametro indica si es descendente
        $Listado = $Listado->sortBy("lastModified", SORT_REGULAR, false);
        
        $Listado = $Listado->filter(function ($item) {
            return $item['type'] === 'file'; // solo archivos
        });
        // dd($Listado);
        
        foreach($Listado as $Archivo) {
            // Leer contenido desde el banco
            $contenido = $bankDisk->get($Archivo['path']);

            // copiar archivo en local
            $encryptedDisk->put($Archivo['path'], $contenido);
            $backupDisk->put($Archivo['path'], $contenido);
        }
        $X=0;
    }
}
