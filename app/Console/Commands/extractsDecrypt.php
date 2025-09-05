<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class extractsDecrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank:decrypt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Desencriptar los archivos del banco';

    /**
     * Execute the console command.
     */
    public function handle(){
        // Lee los archivos y directorios de manera no recursiva dentro del directorio como una coleccion
        $encryptedDisk = Storage::disk("encrypted");
        $decryptedDisk = Storage::disk("decrypted");
        $privateKey = Storage::disk("files")->path("h2h_BI_privada.asc");
        $Listado = collect($encryptedDisk->listContents("", true));
        // funcion para ordenar el contenido segun la longitud de los archivos, el tercer parametro indica si es descendente
        $Listado = $Listado->sortBy("lastModified", SORT_REGULAR, false);
        
        foreach($Listado as $Archivo) {
            $SeparaNombre = explode(".", $Archivo['path']);
            $Nombre = $SeparaNombre[0];
            $origen = $encryptedDisk->path($Archivo['path']);
            $destino = $decryptedDisk->path("$Nombre.txt");
            $origen = escapeshellarg($origen);
            $destino = escapeshellarg($destino);
            
            $command = "gpg --import $privateKey && gpg --batch --yes --pinentry-mode loopback --passphrase 'H2H_Zetagas' -o {$destino} -d {$origen}";
            exec($command, $output, $return);
            
            $encryptedDisk->delete($Archivo['path']);
        }
    }
}
