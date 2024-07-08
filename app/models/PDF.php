<?php

use Fpdf\Fpdf;

class PDF extends FPDF
{
    // Funcion encargado de realizar el encabezado
    function Header()
    {
        // Logo
        $this->Image('./Fotos/Logo.png', 10, -1, 30);
        $this->SetFont('Arial', 'B', 13);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(60, 10, 'Comanda', 1, 0, 'C');
        // Line break
        $this->Ln(20);
    }
    // Funcion pie de pagina
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }



    function ProductosTable($header, $data)
    {
        //var_dump($header);
        //var_dump($data);
        // Column widths
        $w = array(8, 80, 50, 25);
        // Header
        for ($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
        $this->Ln();
        // Data
        foreach ($data as $fila) {
            $i = 0;
            $row = [];
            foreach ($header as $propiedad) {

                $this->Cell($w[$i], 8, $fila->$propiedad, 1, 0, 'C');


                $i++;
            }
            $this->Ln();
        }
        // Closing line
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}
