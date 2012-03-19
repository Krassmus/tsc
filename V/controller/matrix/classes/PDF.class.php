<?php

require_once(dirname(__file__).'/tcpdf/tcpdf.php');


// Extend the TCPDF class to create custom Header and Footer
class PDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        //$image_file = K_PATH_IMAGES.'logo_example.jpg';
        //$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('1979', '', 13);
        // Title
        //$this->Cell(0, 15, 'TSC', 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->writeHTML("TSC<br>V", true, false, true, false, 'C');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('ocraext', 'I', 8);
        // Page number
		//$this->getAliasNbPages()
        //$this->Cell(0, 10, $this->getAliasNumPage(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}