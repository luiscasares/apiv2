<?php

include "./cors.php";
require "./funciones/authcheker.php";

require './R/includes/dbcnx.php';


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/json');

$queryGarantias = 'SELECT * FROM garantias ORDER BY `fecha` DESC';
try {
    $stame = $pdocnx->prepare($queryGarantias);
    $resDb = $stame->execute();
    if ($resDb) {
      $documento = new Spreadsheet();
      $nombreDelDocumento = "Garantias.xlsx";

      $hoja = $documento->getActiveSheet();
      $hoja->setTitle("Garantias");
      //$hoja->setCellValueByColumnAndRow(1, 1, "Un valor en 1, 1");
      $hoja->setCellValue("A1", "Nombre");
      $hoja->setCellValue("B1", "Email");
      $hoja->setCellValue("C1", "Modelo herramienta");
      $hoja->setCellValue("D1", "Nombre herramienta");
      $hoja->setCellValue("E1", "Numero de serie");
      $hoja->setCellValue("F1", "Fecha");
      $hoja->setCellValue("G1", "Nombre del distribuidor");
      $hoja->setCellValue("H1", "Direccion del distribuidor");

      $hoja->setCellValue("I1", "Numero de factura");

      $hoja->getColumnDimension('A')->setWidth(15);
      $hoja->getColumnDimension('B')->setWidth(15);
      $hoja->getColumnDimension('C')->setWidth(15);
      $hoja->getColumnDimension('D')->setWidth(15);
      $hoja->getColumnDimension('E')->setWidth(15);
      $hoja->getColumnDimension('F')->setWidth(15);
      $hoja->getColumnDimension('G')->setWidth(15);
      $hoja->getColumnDimension('H')->setWidth(25);
      $hoja->getColumnDimension('I')->setWidth(15);



      $hoja->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('1579f6');

      $i = 2;

          while ($row = $stame->fetch(PDO::FETCH_ASSOC)) {
              $obj = json_decode($row['valores']);
              //echo $obj->{'nombre'};

               $hoja->setCellValue('A'.$i ,$obj->{'nombre'});
              $hoja->setCellValue('B'.$i , $obj->{'email'});
              $hoja->setCellValue('C'.$i , $obj->{'modelo'});
              $hoja->setCellValue('D'.$i , $obj->{'nombreHerr'});
              $hoja->setCellValue('E'.$i , $obj->{'numSerie'});
              $hoja->setCellValue('F'.$i , $row['fecha']);
              $hoja->setCellValue('G'.$i , $obj->{'nomDistri'});
              $hoja->setCellValue('H'.$i , $obj->{'ubiDistri'});
              $hoja->setCellValue('I'.$i , $obj->{'numFactura'});
              $i++;
          }

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $nombreDelDocumento . '"');
      header('Cache-Control: max-age=0');

      $writer = IOFactory::createWriter($documento, 'Xlsx');
      ob_start();
      $writer->save('php://output');
      $xlsData = ob_get_contents();
      ob_end_clean();

      $response =  array(
          'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
      );
      http_response_code(200);
      die(json_encode($response));
    } else {
      http_response_code(400);
      $respuesta = array(
          'respuesta' =>  $stmt->errorInfo()
      );
      echo json_encode( $respuesta);
    }



} catch (Exception $exc) {
    http_response_code(500);
    echo $exc->getMessage();
}



?>