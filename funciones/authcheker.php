<?php

require "./vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();
use \Firebase\JWT\JWT;


$secret_key =  $_ENV['JWT_SECRET'];;
$jwt = null;
$decoded = null ;


$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
$arr = explode("Bearer ", $authHeader);

$jwt = $arr[1];
if($jwt){
    try {
        // Access is granted. Add code of the operation here
        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
        //  echo json_encode(array(
        //     "message" => "Access granted:",
        //     "decoded" => $decoded
        // ));

    }catch (Exception $e){

    http_response_code(401);

    echo json_encode(array(
        "message" => "Access.",
        "error" => $e->getMessage()
    ));
    exit();

    }
} else {
    http_response_code(401);

    echo json_encode(array(
        "message" => "Access denied 11111.",
    ));
    exit();
}
?>