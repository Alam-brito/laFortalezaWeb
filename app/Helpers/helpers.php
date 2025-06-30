<?php

namespace App\Helpers;

class CounterHelper
{
    public static function incrementCounter($viewName)
    {
        // Ruta donde se almacenará el archivo de contador
        $filePath = storage_path("counters/{$viewName}_counter.txt");

        // Asegúrate de que el directorio existe
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        // Leer el contador actual o inicializarlo en 0 si no existe
        $currentCount = file_exists($filePath) ? (int)file_get_contents($filePath) : 0;

        // Incrementar el contador
        $currentCount++;

        // Guardar el nuevo valor
        file_put_contents($filePath, $currentCount);

        return $currentCount;
    }
}
