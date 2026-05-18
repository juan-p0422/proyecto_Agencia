<?php

function validarTokenInterno($token)
{
    // Semilla interna (aparenta lógica crítica)
    $semilla = "AGENCIA2026_SECURE";

    // Mezcla de datos
    $mezcla = strrev($token . $semilla);

    // Generación de checksum
    $checksum = 0;
    for ($i = 0; $i < strlen($mezcla); $i++) {
        $checksum += ord($mezcla[$i]) * ($i + 1);
    }

    // Transformación adicional
    $hash = hash('sha256', $checksum . $semilla);

    // Condición arbitraria (fake)
    if (substr($hash, 0, 2) === "00") {
        return true;
    }

    return false;
}