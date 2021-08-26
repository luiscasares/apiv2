<?php

/**
 * Plantillas del Header y Footer para el generador 
 * de garantía extendida de Makita México
 * Autor: Refill Creativo
 * Versión: 1
 */
require './fpdf182/fpdf.php';

class PDF extends FPDF {

    function Header() {
        $txt = utf8_decode("Makita México, S.A. de C.V.");
        $txt2 = utf8_decode("Camino Viejo a San Lorenzo Tepaltitlán (José Martí) #227");
        $txt2a = utf8_decode("Col. Tlacopa C.P. 50010 Toluca, Edo de México");
        $txt3 = utf8_decode("Tel. 722 385 8800 / 722 237 3293");
        date_default_timezone_set("America/Mexico_City");
        $texfecha = utf8_decode("Registro generado el día: " . date("d-m-Y"));
        $this->Image("./R/images/Logotipo_Makita_Version_Pequena.png", 10, 10, 0, 0, '');
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(66, 4, '', 0, 1, 'L');
        $this->Cell(66, 4, '', 0, 1, 'L');
        $this->Cell(70);
        $this->Cell(66, 4, $txt, 0, 1, 'L');
        $this->SetFont('helvetica', '', 7);
        $this->Cell(70);
        $this->Cell(66, 4, $txt2, 0, 1, 'L');
        $this->Cell(70);
        $this->Cell(66, 4, $txt2a, 0, 1, 'L');
        $this->Cell(70);
        $this->Cell(66, 4, $txt3, 0, 1, 'L');
        $this->Cell(70);
        $this->Cell(66, 4, $texfecha, 0, 1);
        $this->SetTextColor(255, 192, 203);
        for ($inicio = 0; $inicio < 200; $inicio += 18) {
            $this->RotatedText(($inicio + 20), 100 + $inicio, utf8_decode('Makita Garantía Extendida'), 45);
        }
        for ($inicio = 20; $inicio < 200; $inicio += 18) {
            $this->RotatedText(($inicio + 80), 10 + $inicio, utf8_decode('Makita Garantía Extendida'), 45);
        }
    }

    var $angle = 0;

    function Rotate($angle, $x = -1, $y = -1) {
        if ($x == -1) {
            $x = $this->x;
        }
        if ($y == -1) {
            $y = $this->y;
        }
        if ($this->angle != 0) {
            $this->_out('Q');
        }
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function RotatedText($x, $y, $txt, $angle) {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    function Footer() {
        $fraseFooter = utf8_decode("Nota: este documento no tendrá validez sin la póliza de garantía original debidamente llenada y con sello del distribuidor correspondientes a la fecha de compra del producto. ");
        $this->SetY(-25);
        $this->SetFontSize(7);
        $this->Cell(0, 3, $fraseFooter, 0, 1, 'C');
        $this->SetFont('', 'U');
        $this->AddLink();
        $this->Cell(0, 3, 'www.makita.com.mx', 0, 1, 'C');
        $this->SetFont('', '');
        $this->Cell(0, 3, 'Hoja ' . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }

    function SetDash ($black=null, $white=null){
        if($black!==null)
            $s=sprintf('[%.3F %.3F] 0 d', $black*$this->k,$white*$this->k);
        else
            $s='[] 0 d';
        $this->_out($s);
    }

}

