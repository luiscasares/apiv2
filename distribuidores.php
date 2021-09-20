<?php
include "./cors.php";

//Archivo para comprobar que el usuario esta autenticado por medio de JWT
require "./funciones/authcheker.php";

//Archivo de conexion
require './R/includes/dbcnx.php';


$data = json_decode(file_get_contents("php://input"));
/**
 * Accion que realiza una inserción en la tabla  distribuidores
 */
$accion = $data->accion;
if ($accion === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombreDis = $data->nombreDistribuidor;
    $direccionDis = $data->direccionDistribuidor;
    $tel1 = $data->tel1;

    if(isset($data->tel2))
      $tel2 = $data->tel2;

    if(isset($data->tel3))
      $tel3 = $data->tel3;

    if(isset($data->web))
      $web = $data->web;


    $email=$data->email;
    $activo= $data->activo;
    $idEstado= $data->idEstado;

    $queryDistribuidor = "INSERT INTO `distribuidores` (`idDistribuidor`, `nombreDistribuidor`, `direccionDistribuidor`, `Tel1`, `Tel2`, `Tel3`, `email`, `web`, `activo`, `idEstado`)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
    try {
        $stmt = $pdocnx->prepare($queryDistribuidor);
        $resDb = $stmt->execute([NULL,$nombreDis, $direccionDis, $tel1, $tel2, $tel3, $email, $web, $activo, $idEstado]);
        if ($resDb) {
             http_response_code(200);
            $respuesta = array(
                'respuesta' => 'correcto',
            );
         } else {
            http_response_code(400);
            $respuesta = array(
                'respuesta' =>  $stmt->errorInfo()
            );
         }

    } catch (Exception $e) {
        http_response_code(400);
        $respuesta = array(
            'error' => $e->getMessage()
        );
    }
    echo json_encode( $respuesta);
}

if ($accion === 'update' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
  $idDistribuidor = $data->idDistribuidor;
  $nombreDis = $data->nombreDistribuidor;
  $direccionDis = $data->direccionDistribuidor;
  $tel1 = $data->tel1;
  $tel2 = $data->tel2;
  $tel3 = $data->tel3;
  $email=$data->email;
  $web ="web";
  $activo= $data->activo;
  $idEstado= $data->idEstado;

  if (empty($idDistribuidor)) {
    http_response_code(400);
    echo json_encode( array(
        'error' => "idDistribuidor es obligatorio"
    ));
    return;
  }

  $params = array();
  $sql = "UPDATE distribuidores SET nombreDistribuidor= :nombreDistribuidor ";
  $params[':nombreDistribuidor'] = $nombreDis;

      if (!empty($direccionDis)) {
    $sql .=  ",direccionDistribuidor= :direccionDistribuidor";
    $params[':direccionDistribuidor'] = $direccionDis;
  }
  if (!empty($tel1)) {
    $sql .=  ",Tel1= :Tel1";
    $params[':Tel1'] = $tel1;
  }
  if (!empty($tel2)) {
    $sql .=  ",Tel2= :Tel2";
    $params[':Tel2'] = $tel2;
  }
  if (!empty($tel3)) {
    $sql .=  ",Tel3= :Tel3";
    $params[':Tel3'] = $tel3;
  }
  if (!empty($email)) {
    $sql .=  ",email= :email";
    $params[':email'] = $email;
  }
 if ($activo == 1 || $activo == 0) {
    $sql .=  ",activo= :activo";
    $params[':activo'] = $activo;
  }
  if (is_numeric($idEstado)) {
    $sql .=  ",idEstado= :idEstado";
    $params[':idEstado'] = $idEstado;
  }

  $sql .=  " WHERE idDistribuidor = :idDistribuidor";
  $params[':idDistribuidor'] = $idDistribuidor;
  try {
      $stmt = $pdocnx->prepare($sql);
      $resDb = $stmt->execute($params);
      if ($resDb) {
           http_response_code(200);
          $respuesta = array(
              'respuesta' => 'correcto',
          );
       } else {
          http_response_code(400);
          print_r($stmt->errorInfo()) ;
          $respuesta = array(
              'error' =>  "2"
          );
       }

  } catch (Exception $e) {
      http_response_code(400);
      $respuesta = array(
          'error' => $e->getMessage()
      );
  }
  echo json_encode( $respuesta);
}

/**
 * Accion que realiza una consulta SQL para traer a todos los distribuidores
 * agregando paginación
 */
if ($accion === 'getAll' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $pageSize =  (int)$_GET['pageSize'];
    $pageNum =  (int)$_GET['pageNum'];
    $search = $_GET['search'];

/*     SELECT * FROM customers
WHERE 'Robert Bob Smith III, PhD.' LIKE CONCAT('%',name,'%') */

    if (empty($pageSize) || $pageSize <= 0) $pageSize = 10;
    if (empty($pageNum) || $pageNum <= 0) $pageNum = 1;

    $offset = ($pageNum -1) * $pageSize;

    $queryDistribuidor = "SELECT  * FROM distribuidores ";
    $queryCountDistribuidor = "SELECT   COUNT(*) FROM distribuidores";

  $params = array();
  if (!empty($search)) {
    $queryDistribuidor .=  " WHERE nombreDistribuidor LIKE CONCAT('%',:nombreDistribuidor,'%') ORDER BY nombreDistribuidor ASC";
    $params[':nombreDistribuidor'] = $search;
  } else {
    $queryDistribuidor .= " ORDER BY nombreDistribuidor ASC LIMIT $pageSize OFFSET $offset";
  }

    try {
      $stDistri = $pdocnx->prepare($queryDistribuidor);
      $stDistri->execute($params);
      $resultadosDistribuidores=$stDistri->fetchAll(PDO::FETCH_ASSOC);

      $totalDistribuidores = null;
      if ($pageNum === 1) {
        $stCountDistri = $pdocnx->prepare($queryCountDistribuidor);
        $stCountDistri->execute();
        $totalDistribuidores=$stCountDistri->fetchColumn();
      }

      http_response_code(200);
      $respuesta = array(
          "distribuidores" => $resultadosDistribuidores,
          "total" => $totalDistribuidores
      );
    } catch (Exception $exc) {
      http_response_code(500);
      $respuesta = array(
        'error' => $exc->getMessage()
    );
    }

    echo json_encode( utf8ize($respuesta));
}


/**
 * Accion que elimina a un distribuidor por id
 */
if ($accion === 'delete' && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
   $idDistribuidor = $data->idDistribuidor;
   if(!empty($idDistribuidor)){
    $queryDeleteDist = "DELETE FROM distribuidores WHERE idDistribuidor = ?";
    try {
        $stmt = $pdocnx->prepare($queryDeleteDist);
        $resDb = $stmt->execute([$idDistribuidor]);
        $resDb = $stmt->rowCount();
        if ($resDb !== 0) {
            http_response_code(200);
            $respuesta = array(
                'respuesta' => 'correcto',
            );
         } else {
            http_response_code(400);
            $respuesta = array(
                'respuesta' =>  'No se encontro ningun distribuidor con este id'
            );
         }

    } catch (Exception $e) {
        http_response_code(500);
        $respuesta = array(
            'respuesta' => $e->getMessage()
        );
    }
   } else {
    $respuesta = array(
        'respuesta' => "El idDistribuidor debe ser enviado"
    );
   }
    echo json_encode( $respuesta);
}


// validar que se estan enviado una de las acciones permitidas
if (empty($accion) || ($accion !== 'delete' && $accion !== 'create'&& $accion !== 'getAll' &&$accion !=='update')) {
    http_response_code(400);
    $respuesta = array(
      'respuesta' => "Accion invalida"
  );
  echo json_encode( $respuesta);
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