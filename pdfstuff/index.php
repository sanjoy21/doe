<? 
//include "../connect.php";
//$starow = mysql_fetch_array( mysql_query( "select * from stations where callletters = '$stationname'" ) );
if( $go )
{
require('fpdf.php');
require('fpdi.php');

$pdf =& new FPDI(); 
$pagecount = $pdf->setSourceFile('blank.pdf'); 
$tplidx = $pdf->importPage(1); 
$pdf->addPage();
$pdf->useTemplate($tplidx, -3, -3); 

$fontsize = 14;
$pdf->SetFont('Arial','',14);
$pdf->SetXY(197,100);
$pdf->Cell(100,20,"", 0,0,'C');
$pdf->SetFont('Arial','',$fontsize);
$infowidth = 50;
$infoheight = 105;
$lineheight = 6;
$pdf->SetXY($infowidth,$infoheight);
$pdf->Cell(100,20,"$line1", 0,0,'C');

$pdf->SetFont('Arial','B',$fontsize+4);
$infoheight += $lineheight;
$pdf->SetXY($infowidth,$infoheight);
$pdf->Cell(100,20,"$line2", 0,0,'C');

$pdf->SetFont('Arial','',$fontsize);
$infoheight += $lineheight;
$pdf->SetXY($infowidth,$infoheight);
$pdf->Cell(100,20,"$line3", 0,0,'C');

$infoheight += $lineheight;
$pdf->SetXY($infowidth,$infoheight);
$pdf->Cell(100,20,"$line4", 0,0,'C');

$infoheight += 20;
$pdf->SetXY($infowidth,$infoheight);
$pdf->Cell(100,20,"$line5", 0,0,'C');

$pdf->Output( "output.pdf", "D" );

PDF_delete($pdfFile); 
}
?>
<form method='post'>

Line 1: <input size='90' type='text' name='line1'><br>
Line 2: <input size='90' type='text' name='line2'><br>
Line 3: <input size='90' type='text' name='line3'><br>
Line 4: <input size='90' type='text' name='line4'><br>
Line 5: <input size='90' type='text' name='line5'><br>

<input type='submit' name='go' value='go'>