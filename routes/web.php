<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/speak', function () {
    return "hola";
});

Route::get('/', function () {
    // Lee los archivos y directorios de manera no recursiva dentro del directorio como una coleccion
    $disk = Storage::disk("bankToDownload");
    $Listado = collect($disk->listContents("", true));
    // funcion para ordenar el contenido segun la longitud de los archivos, el tercer parametro indica si es descendente
    $Listado = $Listado->sortBy("lastModified", SORT_REGULAR, false);
    
    $Items = [];
    $Config = [];
    
    foreach($Listado as $Archivo) {
        if($Archivo["type"] == "file"){
            $TipoArchivo = $disk->mimeType($Archivo['path']);
            $TamanoArchivo = $disk->size($Archivo['path']);
            $SeparaTipo = explode("/", $TipoArchivo);
            $Url = $disk->url($Archivo['path']);
            
            $SeparaNombre = explode("/", $Archivo['path']);
            $ext = $SeparaTipo[0] != "application" ? $SeparaTipo[0] : "desconocido";
            
            
            $Config[] = [
                "type" => $ext,      // check previewTypes (set it to 'other' if you want no content preview)
                "filetype" => $TipoArchivo,
                "caption" => end($SeparaNombre), // caption
                "size" => $TamanoArchivo,    // file size
                // 'key' => $fileId,       // keys for deleting/reorganizing preview
                'fileId' => "boddando",    // file identifier
                // 'zoomData' => $zoomUrl, // separate larger zoom data
                // "url" => route('Admin.GrandesArchivos.Procesa'),
                "downloadUrl" => $disk->url($Archivo['path']),
                
                // overide deleteExtraData
                'extra' => (object)[
                    "_token" => csrf_token(),
                    "Proceso" => "delete",
                    "borrar" => $Archivo['path'],
                ],
            ];
            
            $Items[] = $Url;
        }
    }
    
    $Response = [
        "Items" => $Items
        , "Config" => $Config
    ];
    
    return $Response;
});

Route::get('/copy/{name}', function ($name){
    // return $name;
    /* Copiar archivos entre discos, Al ejecutar $privado→readStream($fileName) se comenzará la descarga y, mientras se van descargando los bytes se van escribiendo en el disco local con $Publico->writeStream().*/
    // $Publico = Storage::disk("downloadFiles");
    $privado = Storage::disk("bankToDownload");
    
    if(!$privado->exists($name))
        return "No existe el archivo solicitado";
        
    // $Publico->writeStream($name, $privado->readStream($name));
    
    // if(!$Publico->exists($name))
    //     return "No no se ha podido descargar el archivo";
        
    // // return $name;
    // return $privado->download($name);use Illuminate\Support\Facades\Storage;


    if ($privado->exists($name)) {
        $fileContent = $privado->get($name);
        $mimeType = $privado->mimeType($name);
        $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($fileContent);

        // Ejemplo: pasar a una vista, respuesta JSON, etc.
        return response()->json(['base64' => $base64]);
    } else {
        return response()->json(['error' => 'File not found'], 404);
    }
});
