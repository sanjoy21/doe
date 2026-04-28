<?php
include "mysql.php";

$thetable = "drill";
$table = "drill";
$otherfields = array( "drillid", "participants", "score", "nextdate", "comments", "schoolcode", "zip", "serial", "inspector", "lastnotified" );
$extrafields = ", schoolcode, zip";

if( isset($concat) && $concat )
    $extrafields .= ", group_concat( ' ', serial ) as serial ";
else
    $extrafields .= ", serial";

$datefield = $thetable."date";

if( isset($fieldfrom) && $fieldfrom == "null" ) $fieldfrom = "";
if( isset($fieldto) && $fieldto == "null" ) $fieldto = "";
    
$extra = "";
if( isset($fieldfrom) && $fieldfrom )
{
$tm = fixdate( $fieldfrom ); 
$extra .= " and $datefield >= '$tm' ";
}
if( isset($fieldto) && $fieldto )
{
$tm = fixdate( $fieldto ); 
$extra .= " and $datefield <= '$tm' ";
}

if( isset($needsaed) && $needsaed )
{
$extra .= " and needsmoreaed = 1 ";
$otherfields[] = "dateaedsent";
}
if( isset($battreq) && $battreq )
{
$extra .= " and batteryrequest > '' ";
$otherfields[] = "batteryrequest";
}

if( isset($thisusersrow["usertype"]) && $thisusersrow["usertype"] == "trainer" && isset($visi) )
    $extra .= $visi;

$lj = " left join aed_to_drill ats on ats.drillid = t.drillid ";

if( isset($concat) && $concat )
    $extra .= " group by drillid " ;
    
$session_iscorp = $session_iscorp ?? '';
$notcompleted = $notcompleted ?? 0;

$sql = "select t.*, companyname, schoolcode $extrafields, company_esi.iscorp from company_esi, $table t $lj where iscorp = '$session_iscorp' and completed = ".($notcompleted?0:1)." and companyid = company_esi.id and showsondrillreports = 1 and followup = 1 $extra order by $datefield";
//echo( "<font color='white'>$sql</font>" );
$res = db_query_rows( $sql );

if( isset($xls) && $xls ) {
    // CSV Export
    $filename = "report_" . $table . "_" . date('Y-m-d') . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write headers
    $headers = array("school", "schoolcode", "date", "call number");
    foreach( $otherfields as $o ) {
        $headers[] = $o;
    }
    fputcsv($output, $headers);
    
    // Write data rows
    foreach( $res as $r ) {
        $row = array(
            $r["companyname"] ?? '',
            $r["schoolcode"] ?? '',
            $r[$datefield] ?? '',
            $r["callnumber"] ?? ''
        );
        
        foreach( $otherfields as $o ) {
            $row[] = $r[$o] ?? '';
        }
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}
else
{
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<form method='post'>
Dates:  <?=printdates2( "fieldfrom", $fieldfrom ?? '' )?> and <?=printdates2( "fieldto", $fieldto ?? '' )?>
<br>
CSV Export? <input type='checkbox' name='xls' value='1'><br>
<input type='submit' name='go' value='Go'>
<br>
<table cellpadding="3" cellspacing="0" border="1" width="100%">

<tr><th class='copy'>school</th><th class='copy'>schoolcode</th><th class='copy'>date</th>
<?php foreach( $otherfields as $o ) { ?>
<th class='copy'><?=htmlspecialchars($o ?? '')?></th>
<?php } 
foreach( $res as $r )
{
?>
<tr>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?=htmlspecialchars($r["companyid"] ?? '')?>'><?=htmlspecialchars($r["companyname"] ?? '')?></a></td>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?=htmlspecialchars($r["companyid"] ?? '')?>'><?=htmlspecialchars($r["schoolcode"] ?? '')?></a></td>
<td valign='top' class='copy'><?=htmlspecialchars($r[$datefield] ?? '')?></td>
<?php foreach( $otherfields as $o ) { ?>
<td class='copy' valign='top'><?=nl2br( htmlspecialchars(($o=="drillid"?"D":"").($r[$o] ?? '')) )?></td>
<?php } ?>
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