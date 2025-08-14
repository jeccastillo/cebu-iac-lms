<?php
require_once('tcpdf/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('iACADEMY');
$pdf->SetTitle('Permanent Record');
$pdf->SetSubject('Student Record');
$pdf->SetKeywords('TCPDF, PDF, student, record');

// Set default header data
$pdf->SetHeaderData('', 0, 'Permanent Record', '');

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 14));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', 10));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(15, 20, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

// Set font for the main content
$pdf->SetFont('helvetica', '', 12);

// Content
$html = '
<h1 style="text-align:center; font-size:18px; margin-bottom:20px;">Permanent Record</h1>
<p style="margin-bottom: 5px;"><strong>Name:</strong> CHING, YAMILLAH LOTERTE</p>
<p style="margin-bottom: 5px;"><strong>ID No.:</strong> 2024SHA01064</p>
<p style="margin-bottom: 5px;"><strong>LRN No.:</strong> 436017150088</p>
<p style="margin-bottom: 5px;"><strong>Track/Strand:</strong> Arts and Design (Media Arts and Visual Arts)</p>
<p style="margin-bottom: 5px;"><strong>Gender:</strong> Female</p>
<p style="margin-bottom: 5px;"><strong>Address:</strong> Blk 20 Lot 9 Phase 1-B, Natania Homes, Pasong Kawayan 2, General Trias, Cavite</p>
<p style="margin-bottom: 5px;"><strong>Date of Birth:</strong> September 03, 2007</p>
<p style="margin-bottom: 5px;"><strong>Place of Birth:</strong> Legaspi City, Albay</p>
<p style="margin-bottom: 5px;"><strong>Citizenship:</strong> Filipino</p>
<p style="margin-bottom: 5px;"><strong>Parent/Guardian:</strong> Wilson G. Ching, May Grace A. Loterte</p>
<p style="margin-bottom: 5px;"><strong>Junior High School:</strong> St. Mary of the Woods School</p>
<p style="margin-bottom: 5px;"><strong>Date of Admission:</strong> </p>
<p style="margin-bottom: 5px;"><strong>Senior High School:</strong> </p>
<p style="margin-bottom: 5px;"><strong>Date of Graduation:</strong> November 30, 1999</p>
<p style="margin-bottom: 5px;"><strong>Grade:</strong> Grade 11</p>
<p style="margin-bottom: 5px;"><strong>Sem:</strong> First Semester</p>
<p style="margin-bottom: 5px;"><strong>School Year:</strong> 2024-2025</p>

<h2 style="font-size:16px; margin-top:20px;">First Semester Subjects</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th style="font-size:14px;">Subject</th>
        <th style="font-size:14px;">Final Grade</th>
        <th style="font-size:14px;">Remarks</th>
    </tr>
    <tr>
        <td>Creative Industries 1: Arts and Design Appreciation and Production</td>
        <td>95</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Conduct</td>
        <td>T</td>
        <td></td>
    </tr>
    <tr>
        <td>Earth and Life Science</td>
        <td>92</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>General Math</td>
        <td>99</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Oral Communication</td>
        <td>95</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Physical Education and Health 1</td>
        <td>99</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Layout Design for Print Publications</td>
        <td>95</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Understanding Culture, Society and Politics</td>
        <td>98</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Komunikasyon at Pananaliksik sa Wika at Kulturang Pilipino</td>
        <td>96</td>
        <td>PASSED</td>
    </tr>
</table>
<p style="margin-top: 10px;"><strong>Days of School:</strong> 90</p>
<p style="margin-bottom: 10px;"><strong>General Average:</strong> 96</p>
<p style="margin-bottom: 10px;"><strong>Days Present:</strong> 90</p>

<h2 style="font-size:16px; margin-top:20px;">Second Semester Subjects</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th style="font-size:14px;">Subject</th>
        <th style="font-size:14px;">Final Grade</th>
        <th style="font-size:14px;">Remarks</th>
    </tr>
    <tr>
        <td>Research in Daily Life 1</td>
        <td>93</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Conduct</td>
        <td>T</td>
        <td></td>
    </tr>
    <tr>
        <td>Developing Filipino Identity in the Arts with Videography</td>
        <td>96</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Pagbasa at Pagsusuri ng Ibat-Ibang Teksto Tungo sa Pananaliksik</td>
        <td>96</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Physical Education and Health 2</td>
        <td>97</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Personal Development</td>
        <td>95</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Physical Science</td>
        <td>94</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Reading and Writing</td>
        <td>97</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Statistics and Probability</td>
        <td>93</td>
        <td>PASSED</td>
    </tr>
    <tr>
        <td>Vector Graphics and Illustration</td>
        <td>94</td>
        <td>PASSED</td>
    </tr>
</table>
<p style="margin-top: 10px;"><strong>Days of School:</strong> 113</p>
<p style="margin-bottom: 10px;"><strong>General Average:</strong> 95</p>
<p style="margin-bottom: 10px;"><strong>Days Present:</strong> 113</p>
<p>Remarks: ****Nothing Follows****</p>

<h2 style="font-size:16px; margin-top:20px;">Transfer Eligibility</h2>
<p>I certify that this is the true record of CHING, YAMILLAH LOTERTE who is eligible for admission to and has no outstanding obligation to the school.</p>

<p style="margin-top: 10px;"><strong>Grading System:</strong> 90-100 (Outstanding); 85-89 (Very Satisfactory); 80-84 (Satisfactory); 75-79 (Fairly Satisfactory); Below 75 (Did Not Meet Expectation); IP (In Progress); OW (Officially Withdrawn); OD (Officially Dropped)</p>
<p>Note: This is an Official Electronic document issued by Information and Communications Technology Academy (iACADEMY). The document\'s authenticity may be verified through <a href="mailto:registrar@iacademy.edu.ph">registrar@iacademy.edu.ph</a>. Documentary stamp has been affixed to the record on file.</p>
<p>Prepared by: MENDOZA, APPLES PUZON</p>
<p>Verified by: Ms. Jocelyn R. Baniago</p>
<p>Date Issued: Registrar</p>
';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('permanent_record.pdf', 'I');
?>
