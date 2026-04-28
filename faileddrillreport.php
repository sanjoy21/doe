<?php
include "mysql.php";
// require_once "Spreadsheet/Excel/Writer.php";

if( isset($doit) && $doit ) { 
    $thetable = "drill";
    $table = "drill";
    $otherfields = array( "drillid", "participants", "score", "nextdate", "comments", "schoolcode", "zip", "serial", "inspector", "lastnotified" );
    $extrafields = ", schoolcode, zip";

    if( isset($concat) && $concat )
        $extrafields .= ", group_concat( ' ', serial ) as serial ";
    else
        $extrafields .= ", serial";

    $datefield =  $thetable."date";
    $extra = "";
    $lj = "";
    $swhr = "";

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

    if( isset($thisusersrow["usertype"]) && $thisusersrow["usertype"] == "trainer" )
        $extra .= isset($visi) ? $visi : "";

    $lj .= " left join aed_to_drill ats on ats.drillid = t.drillid ";

    if( isset($since) && $since )
        $swhr = " and drilldate > '".date( "Y-m-d", strtotime( $since ) )."'";

    if( isset($concat) && $concat )
        $extra .= " group by drillid " ;
    
    $notcompleted = isset($notcompleted) ? $notcompleted : 0;
    $sql = ( "select t.*, companyname, address, city, borough, principalname, contactphone, contactname, contactemail, schoolcode $extrafields from company_esi, $table t $lj where iscorp = '$session_iscorp' and completed = ".($notcompleted?0:1)." and companyid = company_esi.id and (failedother = 1 or refused = 1 or notrained = 1 ) and showsondrillreports = 1 $swhr $extra order by $datefield" );
    
    //echo( "<font color='black'>$sql</font>" ); // and lastnotified > '0000-00-00' 
    $res = db_query_rows( $sql );

   if( isset($xls) && $xls ) {
    // Generate CSV instead of Excel
    $filename = "report_" . $table . "_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write header row
    $header = [
        "school",
        "schoolcode",
        "address",
        "telephone number",
        "principal name",
        "aed contact name",
        "aed contact email",
        "drill date",
        "reason for failure",
        "ESI Recommendations and Remediation",
        "DOE Comments"
    ];
    
    fputcsv($output, $header);
    
    foreach( $res as $r )
    {
        // Build address string
        $address = (isset($r["address"]) ? $r["address"] : "") . ", " . 
                   (isset($r["city"]) ? $r["city"] : "") . ", " . 
                   (isset($r["zip"]) ? $r["zip"] : "") . ", " . 
                   (isset($r["borough"]) ? $r["borough"] : "");
        
        // Determine reason for failure
        $reason = "";
        if( isset($r["refused"]) && $r["refused"] )
            $reason = "Refused Drill";
        if( isset($r["needsmoreaed"]) && $r["needsmoreaed"] )
            $reason = "Building Requires Additional AED";
        if( isset($r["failedother"]) && $r["failedother"] )
            $reason = "Other: " . (isset($r["other"]) ? $r["other"] : "");
        
        // Get drill comments
        $drillid = isset($r["drillid"]) ? $r["drillid"] : "";
        $comm = db_query_array( "select * from drillcomments where drillid = '$drillid'", "comment", "comment" );
        $doeComments = is_array($comm) ? join( "; ", $comm ) : "";
        
        // Prepare data row
        $rowData = [
            isset($r["companyname"]) ? $r["companyname"] : "",
            isset($r["schoolcode"]) ? $r["schoolcode"] : "",
            $address,
            isset($r["contactphone"]) ? $r["contactphone"] : "",
            isset($r["principalname"]) ? $r["principalname"] : "",
            isset($r["contactname"]) ? $r["contactname"] : "",
            isset($r["contactemail"]) ? $r["contactemail"] : "",
            isset($r[$datefield]) ? $r[$datefield] : "",
            $reason,
            isset($r["comments"]) ? $r["comments"] : "",
            $doeComments
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
<tr><th class='copy'>school</th><th class='copy'>schoolcode</th>
<th class='copy'>address</th>
<th class='copy'>telephone number</th>
<th class='copy'>principal name</th>
<th class='copy'>aed contact name</th>
<th class='copy'>aed contact email</th>
<th class='copy'>drill date</th>
<th class='copy'>reason for failure</th>
<th class='copy'>ESI Recommendations and Remediation</th>
<th class='copy'>DOE Comments</th>
</tr>
<?php
foreach( $res as $r )
{
    $reason = "";
    if( isset($r["refused"]) && $r["refused"] )
        $reason = "Refused Drill";
    if( isset($r["needsmoreaed"]) && $r["needsmoreaed"] )
        $reason = "Building Requires Additional AED";
    if( isset($r["failedother"]) && $r["failedother"] )
        $reason = "Other: " . (isset($r["other"]) ? $r["other"] : "");
    
    $comm = db_query_array( "select * from drillcomments where drillid = '".(isset($r["drillid"]) ? $r["drillid"] : "")."'", "comment", "comment" );
?>
<tr>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?php echo isset($r["companyid"]) ? $r["companyid"] : ''; ?>'><?php echo isset($r["companyname"]) ? $r["companyname"] : ''; ?></a></td>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?php echo isset($r["companyid"]) ? $r["companyid"] : ''; ?>'><?php echo isset($r["schoolcode"]) ? $r["schoolcode"] : ''; ?></a></td>
<td valign='top' class='copy'><?php echo isset($r["address"]) ? $r["address"] : ''; ?>, <?php echo isset($r["city"]) ? $r["city"] : ''; ?>, <?php echo isset($r["zip"]) ? $r["zip"] : ''; ?>, <?php echo isset($r["borough"]) ? $r["borough"] : ''; ?></td>
<td valign='top' class='copy'><?php echo isset($r["contactphone"]) ? $r["contactphone"] : ''; ?></td>
<td valign='top' class='copy'><?php echo isset($r["principalname"]) ? $r["principalname"] : ''; ?></td>
<td valign='top' class='copy'><?php echo isset($r["contactname"]) ? $r["contactname"] : ''; ?></td>
<td valign='top' class='copy'><?php echo isset($r["contactemail"]) ? $r["contactemail"] : ''; ?></td>
<td valign='top' class='copy'><?php echo isset($r[$datefield]) ? $r[$datefield] : ''; ?></td>
<td valign='top' class='copy'><?php echo $reason; ?></td>
<td valign='top' class='copy'><?php echo isset($r["comments"]) ? $r["comments"] : ''; ?></td>
<td valign='top' class='copy'><?php echo is_array($comm) ? join( "; ", $comm ) : ''; ?></td>
</tr>
<?php } ?>
</table>
<?php } ?>
<br><br><br>
<!--end center content-->
<!--end center content-->
<?php include "ssi/footer.php"; ?>
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
<?php } else { ?>
<form method='post'>
Failed Drills Since: <input type='text' name='since' ><br>
XLS: <input type='checkbox' name='xls' value='1' <?php echo isset($xls) && $xls ? "CHECKED" : ""; ?> ><br>
All Drills From One School On One Line: <input type='checkbox' name='concat' <?php echo isset($concat) && $concat ? "CHECKED" : ""; ?> value='1' CHECKED><br>
<input type='submit' name='doit' value='Go'>
</form>
<?php } ?>