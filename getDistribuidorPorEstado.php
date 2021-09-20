<?php
include "./cors.php";

require "./vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//Archivo de conexion
require './R/includes/dbcnx.php';
$data = json_decode(file_get_contents("php://input"));
$idEstado = $data->idEstado;
 if(!empty($idEstado)){

   $queryDistribuidor = "SELECT * FROM distribuidores WHERE idEstado = $idEstado AND activo = 1 ORDER BY nombreDistribuidor ASC";

   try {
    $stDistri = $pdocnx->prepare($queryDistribuidor);
    $stDistri->execute();
    $resultadosDistribuidores=$stDistri->fetchAll(PDO::FETCH_ASSOC);
    http_response_code(200);
    echo json_encode(array(
        "distribuidores" =>utf8ize($resultadosDistribuidores)
    ));
   } catch (Exception $exc) {
    http_response_code(500);
    echo $exc->getMessage();
}

}  else {
    http_response_code(400);
    echo json_encode(array("message" => "idEstatus es requerido"));
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