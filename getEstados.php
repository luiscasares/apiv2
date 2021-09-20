<?php
include "./cors.php";
require "./vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


//Archivo de conexion
require './R/includes/dbcnx.php';



/**
 * Consulta SQL que trae los estados donde el estatus es 1 significa que cuenta
 * con distribuidores de la empresa.
 */
$queryEstados = 'SELECT * FROM estados WHERE estatus = 1 ORDER BY estado ASC';
 try {
    $stame = $pdocnx->prepare($queryEstados);
    $stame->execute();
    $resultadosEstado=$stame->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode(array(
        "estados" => utf8ize($resultadosEstado)
    ));
     http_response_code(200);

} catch (Exception $exc) {
    http_response_code(500);
    echo $exc->getMessage();
}

function utf8ize( $mixed ) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
}

?>