<?php

 require "./vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


include "./cors.php";
require './R/includes/dbcnx.php';



//Libreria para poder utilizar jsonwebtokens como forma de autenticacion
// https://jwt.io/

use \Firebase\JWT\JWT;



$email = '';
$password = '';

$data = json_decode(file_get_contents("php://input"));

$email = $data->email;
$password = $data->password;

$table_name = 'users';

/**
 * Consulta SQL que trae a un usuario por medio del email
 */
 $query = "SELECT id, nombre, email, password FROM " . $table_name . " WHERE email = ? LIMIT 0,1";
$stmt = $pdocnx->prepare( $query );
$stmt->bindParam(1, $email);
$stmt->execute();
$num = $stmt->rowCount();
 if($num > 0){
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $id = $row['id'];
    $name = $row['nombre'];
    $email = $row['email'];
    $password2 = $row['password'];

    // comparar la contraseña ingresada con la almacenada en la base de datos
    if(password_verify($password, $password2))
    {
         $secret_key =  $_ENV['JWT_SECRET'];
        $issuer_claim = "THE_ISSUER"; // this can be the servername
        $audience_claim = "THE_AUDIENCE";
        $issuedat_claim = time(); // fecha de creacion del token
        $expire_claim = $issuedat_claim + 6000;  // Tiempo de expiración del token (1 hora)

        //Informacion que sera almacenada en el payload del token
        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "exp" => $expire_claim,
            "data" => array(
                "id" => $id,
                "name" => $name,
                "email" => $email
        ));

        http_response_code(200);

        $jwt = JWT::encode($token, $secret_key);
        echo json_encode(
            array(
                "message" => "Successful login.",
                "jwt" => utf8ize($jwt),
                "email" => utf8ize($email),
                "expireAt" => utf8ize($expire_claim)
            ));
    }
    else{

        http_response_code(401);
        echo json_encode(array("type" => "unautorized"));
    }
} else {
    http_response_code(401);
        echo json_encode(array("type" => "unautorized"));
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