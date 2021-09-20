<?php

// antes de mandar a llamar este archivo llamar $dotenv->load();
/**
 * Conexión a la BD usando PDO en MySQL
 * V 1
 * Autor: Refill Creativo
 */
$dnspdo =$_ENV['DB_URI'];
$username=$_ENV['DB_USER'];
$passwd=$_ENV['DB_PASS'];




try {
    //echo "sadas";
    $pdocnx = new PDO($dnspdo, $username, $passwd);
} catch (PDOException $exc) {
    echo $exc->getTraceAsString();
    echo $exc->getMessage();
    die();
}
?>