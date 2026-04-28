<?php 
include "mysql.php";

$rrdate = getsetting( 'rosterreceiveddate' );

$sql = ( "select class.*, companyname, schoolcode, company_esi.iscorp from company_esi, class where rosterreceived = 1 and ( cardsmaileddate is null or cardsmaileddate = '' )   and companyid = company_esi.id and startdate < now() and class.deleted = 0 and confirmdate is not null and startdate > '$rrdate' order by class.startdate " );
//echo( $sql );
$res = db_query_rows( $sql );

if( isset($xls) && $xls ) {
    // Generate CSV instead of Excel
    $filename = "report_" . ($table ?? '') . "_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write header row
    $header = [
        "class date",
        "class #",
        "location"
        // "roster received" - commented out to match original
    ];
    
    fputcsv($output, $header);
    
    // Write data rows
    foreach( $res as $r )
    {
        $class_date = fixdatefordisplay( $r["startdate"] ?? '', true );
        $class_id = $r["id"] ?? '';
        $location = $r["companyname"] ?? '';
        // $roster_received = isset($r["rosterreceived"]) && $r["rosterreceived"] ? "Y" : "N";
        
        // Prepare data row
        $rowData = [
            $class_date,
            $class_id,
            $location
            // $roster_received - commented out to match original
        ];
        
        // Escape any formulas that might start with =, +, - or @ to prevent CSV injection
        foreach($rowData as &$value) {
            if($value !== null && $value !== '') {
                $firstChar = substr($value, 0, 1);
                if(in_array($firstChar, array('=', '+', '-', '@'))) {
                    $value = "'" . $value;
                }
            }
        }
        
        fputcsv($output, $rowData);
    }
    
    fclose($output);
    exit();
}
else
{
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<table cellpadding="3" cellspacing="0" border="1" width="100%">

<tr><th class='copy'>class date</th><th class='copy'>class #</th><th class='copy'>location</th><!--<th class='copy'>returned?</th>-->
<?php 
foreach( $res as $r )
{
?>
<tr>
<td valign='top' class='copy'><a href='class_detail.php?id=<?=$r["id"]?>'><?=fixdatefordisplay( $r["startdate"], true )?></a></td>
<td valign='top' class='copy'><?=$r["id"]?></td>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?=$r["companyid"]?>'><?=$r["companyname"]?></a></td>
<!--<td valign='top' class='copy'><?=$r["rosterreceived"]?"Y":"N"?></td>-->
</tr>
<?php } ?>
</table>
<?php } ?>

<br><br><br>
<!--end center content-->
<!--end center content-->

<?php include "ssi/footer.php" ; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>