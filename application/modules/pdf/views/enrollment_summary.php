<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle("Enrollment Summary");
    
    // set margins
    $pdf->SetMargins(10, 15 , 10);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetFont('helvetica','',10);
    //$pdf->SetAutoPageBreak(TRUE, 6);
    
   //font setting
    //$pdf->SetFont('calibril_0', '', 10, '', 'false');
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage('P', 'LEGAL');            

    
    
    // Set some content to print
    $html = '<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">
            <tr>
                <td width="20%" align="center" style="text-align:center;vertical-align: bottom"><img src= "https://i.ibb.co/1spYkNx/seal.png"  width="100" height="95"/></td>
                <td width="80%" style="text-align: center;line-height:1;vertical-align: middle">  
                    <br /><br /><br />
                    <font style="font-family:Calibri Light; font-size: 12;font-weight: bold;">Information & Communications Technology Academy </font><br /><br />
                    <font style="font-family:Calibri Light; font-size: 10;">5F Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City, Philippines</font>
                </td>  
            </tr>
            </table>
            <table border="0" cellspacing="0" cellpadding="1" style="color:#333; font-size:9;">
            <tr>                            
                <td width="100%" style="text-align: center; border-bottom:1px solid #333">             
                    <font style="font-family:Calibri Light; font-size: 11;font-weight: bold;">Enrollment Summary for '.$sem['enumSem'].' Term SY'.$sem['strYearStart'].'-'.$sem['strYearEnd'].'</font>
                </td>
            </tr>        

           ';
    



$html .= '   
    </table>
     <br />
    <table border="0" cellpadding="0" style="color:#333; font-size:8;" width="528px">     
     <tr>
      <td width="80px" >&nbsp;</td>
      <td width="250px">&nbsp;</td>
      <td width="113px"></td>
      <td width="85px" ></td>
      
     </tr>
    </table> '; 
  
            
$pdf->writeHTML($html, true, false, true, false, '');

//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output("enrollmentSummary".date("Ymdhis").".pdf", 'I');


?>