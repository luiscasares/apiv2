<?php
include "./cors.php";
require "./vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

 require './vendor/phpmailer/phpmailer/src/Exception.php';
require './vendor/phpmailer/phpmailer/src/PHPMailer.php';
require './vendor/phpmailer/phpmailer/src/SMTP.php';



include './Plantilla.php';
require './R/includes/dbcnx.php';

/**
 * Constante de Recaptcha
 * Variables de Formulario y sanitización
 */


/**
 * Variables del formulario web para solicitar la Garantía Extendida
 */
$nombre=  utf8_decode(filter_var(quitar_tildes($_POST["nombre"]), FILTER_SANITIZE_SPECIAL_CHARS));//utf8_decode('Nombre propietario');
$emailCliente=  filter_var($_POST["email"], FILTER_SANITIZE_EMAIL); //'emailcliente@email.com';
$modeloHerramienta=  filter_var($_POST["modHerr"], FILTER_SANITIZE_SPECIAL_CHARS); //'Modelo Herramienta';
$nombreHerramienta=  utf8_decode(filter_var(quitar_tildes($_POST["nomHerr"]), FILTER_SANITIZE_SPECIAL_CHARS));//'Nombre Herramienta'
$numSerie=  filter_var($_POST["numSerie"] , FILTER_SANITIZE_SPECIAL_CHARS);//'Numero de Serie';
$fechaCompra= date("dd-mm-YYYY");
$nomDistri=  utf8_decode(filter_var(quitar_tildes($_POST["nomDis"]), FILTER_SANITIZE_SPECIAL_CHARS));//'Nombre Distribuidor'
$ubiDistri=  utf8_decode(quitar_tildes($_POST["dirDis"]));//'Ubicación Distribuidor';
$numFactura=  filter_var($_POST["numFac"], FILTER_SANITIZE_SPECIAL_CHARS);//'numero de Factura'

/**
 * Extraer los valores en un JSON extra para llenar un objeto y posteriormente
 * insertarla en la base da datos como un string
 */

$myJSON = json_encode(array(
    'nombre' => $nombre,
    'email' => $emailCliente,
    'modelo' => $modeloHerramienta,
    'nombreHerr' => $nombreHerramienta,
    'numSerie' => $numSerie,
    'fechaCompra' => $fechaCompra,
    'nomDistri' => $nomDistri,
    'numFactura' => $numFactura,
    'ubiDistri' => $ubiDistri,
), JSON_FORCE_OBJECT);


$queryGarantias = "INSERT INTO `garantias` (`id`, `valores`)
VALUES (?, ?);";
try {
$stmt = $pdocnx->prepare($queryGarantias);
$resDb = $stmt->execute([NULL,$myJSON]);
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


#cc2128

/**
 * Cadenas de caracteres para formar el PDF de la garantía extendida
 * Si se quiere modificar el contenido de texto del cuerpo del PDF aqui es el lugar indicado
 * utf8_decode()sirve para poner caracteres en español
 * El header, footer y marca de agua se modifican en el script de plantilla
 */
$fraseBienvenida = utf8_decode("El presente documento certifica que al haberse efectuado");
$fraseGarantia = utf8_decode("Obtiene el derecho a un periodo de");
$frasecondiciones = utf8_decode("\n La garantía extendida esta estipulada por un lapso de seis meses adicionales al periodo común de garantía de un año, que será válida de acuerdo a las políticas estipuladas en la póliza de garantía incluida dentro del empaque de la herramienta. ");
$cuerpo ='El documento adjunto contiene la garantia extendida de makita México';
$txtgarantia = utf8_decode("Cada herramienta Makita está cuidadosamente inspeccionada y probada antes de salir de fábrica. Este producto está libre de defectos de funcionamiento y materiales, no obstante se garantiza por el periodo de un año a partir de la fecha de compra, contra cualquier defecto de fábrica así como en materiales y mano de obra empleados para su fabricación. Si surgiera algún problema durante este periodo, lleve el producto a uno de los centros de servicio autorizados por Makita. Si la inspección indica que hay algún problema de funcionamiento o material, Makita MÉxico S.A. de C.V. reemplazar el producto de ser necesario o cualquier pieza o componente defectuoso del mismo, incluyendo la mano de obra, así como los gastos de transportación del producto (dentro de la red de servicio de Makita) y demás gastos necesarios erogados para lograr el cumplimiento de la garantia en domicilio diverso al antes señalado.\n
Esta garantia aplica salvo las siguientes excepciones:\n
Cuando el producto se hubiese utilizado en condiciones distintas a las normales.\n
Cuando el producto no hubiese sido operado de acuerdo al instructivo de uso o manual de
instrucciones que le acompaña.Cuando el producto hubiese sido alterado o reparado por personas no autorizadas por el fabricante nacional, importador o comercializador responsable respectivo.\n
En caso de que el producto haya sido reparado o sometido a mantenimiento y el mismo presente deficiencias imputables al autor de la reparación o del mantenimiento dentro de los 30 días naturales posteriores a la entrega del producto al consumidor, éste tendrá derecho a que sea reparado o mantenido de nuevo sin costo alguno. El tiempo que duren las reparaciones al amparo de la garantía no es computable dentro del plazo de la misma.
Cuando el bien haya sido reparado se iniciará la garantla respecto de las piezas repuestas y continuará con relación al resto. En el caso de reposición del bien se renovará el plazo de ésta garantía.\n
NOTA: Al termino de la presente garantía, el cliente podrá acudir a cualquier centro de servicio autorizado Makita (ver hojas anexas), donde le será otorgado el servicio de reparación y mantenimiento al producto, con los costos correspondientes.\n");

/**
 * Script para crear el archivo PDF
 * V1
 * Autor Refill Creatvio
 * En algunas celdas o contenido se manda a llamar las variables que son capturadas desde el formulario
 * Si es necesario se debe de actualizar la libreria fpdf en el sitio web http://www.fpdf.org/
 * Versión de la librería 1.82 liberada en (07/12/2019)
 */
 $titulo = utf8_decode("GARANTÍA MAKITA");
$pdfplantilla = new PDF('P', 'mm', 'Letter');
$pdfplantilla->SetMargins(0.9,0.9,0.9);
$pdfplantilla->AddPage();
$pdfplantilla->SetFont('helvetica','B',10);
$pdfplantilla->SetXY(10, 30);
$pdfplantilla->SetDash(5,5);
$pdfplantilla->Rect(10,32,200,70,'');
$pdfplantilla->SetXY(15, 32);
$pdfplantilla->Cell(200, 10, $fraseBienvenida,0,1,'C');
$pdfplantilla->Cell(225, 0, 'el registro de producto por medio del sitio www.makita.com.mx',0,1,'C');

$pdfplantilla->SetTextColor(255, 0, 0);
$pdfplantilla->SetFont('helvetica','B',9);
$pdfplantilla->Cell(180, 7, 'FOLIO:',0,0,'R');
$pdfplantilla->SetFont('helvetica','B',10);
$pdfplantilla->Cell(187, 7, $numSerie,0,1);



$pdfplantilla->SetTextColor(0,0,0);
$pdfplantilla->SetFont('helvetica','',9);
$pdfplantilla->SetXY(40, 44);
$pdfplantilla->Cell(65, 7, 'La herramienta identificada con modelo:', 0, 0,'R');
$pdfplantilla->SetFont('helvetica','B',10);
$pdfplantilla->Cell(120, 7, $modeloHerramienta,0,1);
$pdfplantilla->SetFont('helvetica','',9);
$pdfplantilla->SetXY(40, 48);
$pdfplantilla->Cell(65, 7, 'Descrita como:', 0, 0,'R');
$pdfplantilla->SetFont('helvetica','B',10);
$pdfplantilla->Cell(120, 7, $nombreHerramienta,0,1);
$pdfplantilla->SetFont('helvetica','',9);
$pdfplantilla->SetXY(40, 52);
$pdfplantilla->Cell(65, 7, utf8_decode('Con número de serie:'), 0, 0,'R');
$pdfplantilla->SetFont('helvetica','B',10);
$pdfplantilla->Cell(120, 7, $numSerie,0,1);
$pdfplantilla->SetFont('helvetica','',12);
$pdfplantilla->Cell(200, 8, $fraseGarantia, 0, 1, 'C');
$pdfplantilla->SetFont('helvetica','B',25);
$pdfplantilla->Cell(200, 10, utf8_decode('GARANTÍA EXTENDIDA'), 0, 1, 'C');
$pdfplantilla->SetFont('helvetica','',9);
$pdfplantilla->SetX(10);
$pdfplantilla->Cell(38, 5, 'Nombre del distribudor:', 0, 0,'R');
$pdfplantilla->SetFont('helvetica','B',10);
$pdfplantilla->Cell(200, 5, $nomDistri,0,1);
$pdfplantilla->SetFont('helvetica','',9);
$pdfplantilla->SetX(10);
$pdfplantilla->Cell(38, 5, utf8_decode('Dirección del distribudor:'), 0, 0,'R');
$pdfplantilla->SetFont('helvetica','B',10);
$pdfplantilla->Cell(200, 5, $ubiDistri,0,1);
$pdfplantilla->SetFont('helvetica','',9);
$pdfplantilla->SetX(10);
$pdfplantilla->Cell(38, 5, 'No Factura:', 0, 0,'R');
$pdfplantilla->SetFont('helvetica','B',10);
$pdfplantilla->Cell(65, 5, $numFactura,0,1);
$pdfplantilla->SetFont('helvetica','',9);
$pdfplantilla->SetX(10);
$pdfplantilla->Cell(38, 5, 'Nombre del propietario:', 0, 0,'R');
$pdfplantilla->SetFont('helvetica','B',10);
$pdfplantilla->Cell(200, 5, $nombre,0,1);
$pdfplantilla->SetFont('helvetica','',9);
$pdfplantilla->SetX(10);
$pdfplantilla->Cell(38, 5, utf8_decode('Correo electrónico:'), 0, 0,'R');
$pdfplantilla->SetFont('helvetica','B',10);
$pdfplantilla->Cell(200, 5, $emailCliente,0,1);
$pdfplantilla->setFont('helvetica','B',17);

$pdfplantilla->Cell(200, 20,$titulo, 0,1,'C');
$pdfplantilla->SetDash();
$pdfplantilla->Line(30, 120, 190, 120);
$pdfplantilla->SetFont('helvetica','',8);
$pdfplantilla->SetLeftMargin(30);
$pdfplantilla->SetRightMargin(30);
$pdfplantilla->SetXY(30, 125);
$pdfplantilla->setFont('helvetica','',7);
$pdfplantilla->Write(4, $txtgarantia);
$pdfplantilla->Write(4, $frasecondiciones);
$pdfplantilla->AliasNbPages();
$pdfplantilla->SetAuthor('Makita México', true);
$pdfplantilla->SetTitle('Garantía Extendida Makita México', true);
$pdfplantilla->SetSubject('Para uso exclusivo de clientes de Makita México', true);
$garantiaextendidamakita=$pdfplantilla->Output('Garantia-extendida-makita.pdf', 'S');
$pdfAsBase64 = base64_encode($garantiaextendidamakita);
//$pdfAsBase64 = 'data:application/pdf;base64,'.$pdfAsBase64;

//echo json_encode($pdfAsBase64);


    /**
    * Script para crear adjuntar y enviar el correo
    * V1
    * Autor Refill Creatvio
    * En algunas celdas o contenido se manda a llamar las variables que son capturadas desde el formulario
    * Si es necesario se debe de actualizar la libreria phpmailer
    * Versión de la librería 5.5 última versión estable 6.1.6
     * El método AddAddress debe ser modificado por la variable que contiene el correo del cliente
     * Los datos del servidor SMTP deben ser modificados
    */
$mail = new PHPMailer(true);
    try {
        //Configuración

        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; //'tsl';//
  /*       $mail->SMTPOptions = array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            )
            ); */
        $mail->SetLanguage("es", "language/phpmailer.lang-es.php");
        $mail->Priority = 1;
        $mail->Port = 465;

        $mail->Host = "makita-mexico.com.mx" ;

        $mail->Username = "no-replay@makita-mexico.com.mx";

        $mail->Password = "R3n0mX54%";
        $mail->From = "no-reply@makita.com.mx";
        $mail->FromName = "Makita Garantia";
        $mail->From = "no-replay@makita-mexico.com.mx";
        $mail->Sender = "no-replay@makita-mexico.com.mx";

        $mail->AddAddress($emailCliente);
        //$mail->addBCC('makitawebmaster@gmail.com');
        $mail->Subject = "Makita México Garantia Extendida";
        $mail->Body = $cuerpo;
        $mail->SMTPDebug = true;
        $mail->CharSet = "utf-8";
        $mail->WordWrap = 50;
        $mail->AddStringAttachment($garantiaextendidamakita, 'garantia-extndida-makita.pdf', 'base64', 'application/pdf');

        // Agregar el comprobante de pago si existe
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0 && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $mail->AddAttachment($_FILES['image']['tmp_name'],$_FILES['image']['name']);
        }
        if(!$mail->Send()) {
            http_response_code(400);
        } else {
            http_response_code(200);
        }
        // send the pdf as base64
        echo json_encode($pdfAsBase64);
    } catch (Exception $e) {
        http_response_code(400);
        echo "El mensaje no se ha podido enviar. Mailer Error: {$mail->ErrorInfo}";
        error_log("Error en el archivo de generación de PDF");
    }


function quitar_tildes($cadena) {
    $no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
    $permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
    $texto = str_replace($no_permitidas, $permitidas ,$cadena);
    return $texto;
    }