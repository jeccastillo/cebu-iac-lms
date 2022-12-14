<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new TCPDF("P", PDF_UNIT, array(8.5, 11), true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle($student['strLastname'] . ", " . $student['strFirstname'] . ', ' . substr($student['strMiddlename'], 0,1). ".-". $student['strProgramCode']);
    
    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    //$pdf->SetAutoPageBreak(TRUE, 6);
    
   //font setting
    //$pdf->SetFont('calibril_0', '', 10, '', 'false');
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage();
    //$payment_division = $tuition['total'] / 4;


    
    // Set some content to print
$html = <<<EOD
 
 
<h1>Welcome to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0001;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF Example</span>&nbsp;</a>!</h1>
 
<i>This is the principal case of TCPDF library.</i>
 
 
This content is printed utilizing the <i>writeHTMLCell()</i> strategy however you can likewise utilize: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.
 
 
 
Please check the source code documentation and different cases for further information.
 
 
EOD;

$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
// $html = utf8_encode($html);
// $pdf->writeHTML($html);

//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output($student['strLastname'] . ", " . $student['strFirstname'] . ', ' . substr($student['strMiddlename'], 0,1). ".-". $student['strProgramCode'] . ".pdf", 'I');


?>