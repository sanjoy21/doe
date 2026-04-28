<?php
require_once('mysql.php');
if( getcurrentusercompany() > 0 )
{
    header( "location: login.php" );
    exit;
}

// Initialize variables
$emailchecked = $_POST['emailchecked'] ?? false;
$go = $_POST['go'] ?? false;
$xls = $_POST['xls'] ?? false;
$zip = $_POST['zip'] ?? '';
$fromdate = $_POST['fromdate'] ?? '';
$todate = $_POST['todate'] ?? '';
$schoolids = $_POST['schoolids'] ?? [];
$err = '';
$result = [];
// $session_iscorp = $session_iscorp ?? '';

if( $emailchecked && ( !$fromdate || !$todate ) )
{
    $err = "Visit dates are required.";
}
else if( $emailchecked )
{
    $tstr = [];
    $trainers = getTrainersForZip( $zip );
    foreach( $trainers as $trow )
    {
        $tstr[] = ($trow['first_name'] ?? '') . " " . ($trow['last_name'] ?? '');
    }
    $tstr = implode( ", " , $tstr );
    
    $toemail = [];
    foreach( $schoolids as $sid )
    {
        db_query("insert into drillwarningsent ( dateadded, schoolid ) values ( now(), " . (int)$sid . ")" );
        $srow = getCompanyRow( $sid );
        if( !empty($srow['contactemail']) )
            $toemail[$srow['contactemail']] = [ "schoolid"=>$sid, "name"=>($srow['contactname'] ?? ''), "school"=>($srow['companyname'] ?? '') ];
        if( !empty($srow['contact2email']) )
            $toemail[$srow['contact2email']] = [ "schoolid"=>$sid, "name"=>($srow['contact2name'] ?? ''), "school"=>($srow['companyname'] ?? '') ];
        if( !empty($srow['contact3email']) )
            $toemail[$srow['contact3email']] = [ "schoolid"=>$sid, "name"=>($srow['contact3name'] ?? ''), "school"=>($srow['companyname'] ?? '') ];
    }

    $mybody = nl2br( file_get_contents( "drillnotification.txt" ) );
    require_once "class.phpmailer.php";

    $fromname = "Sarah Gillen";
    $fromemail = "sarahg@emergencyskills.com";
    $subject = "Your upcoming Code Blue Drill";

    $datestr = date( "F j", strtotime( $fromdate ) ) . " - " . date( "F j", strtotime( $todate ) );
    
    foreach( $toemail as $t=>$arr )
    {
        $tname = $arr["name"];
        $sid = $arr["schoolid"];
        $qs = "email=" . urlencode( $t ) . "&id=" . $sid;
        $mail = new PHPMailer();
        $mail->From = $fromemail;
        $mail->FromName = $fromname;
        $mail->AddReplyTo( $fromemail );
        
        $mail->Subject = stripslashes( $subject  . " - " . getSchoolCode( $sid ) . " - " . $arr["school"] );
        $mail->IsHTML(true);

        if( trim( $t ) )
        {
            if( !$tname )
                $tname = "AED Contact";
        }
        $tmpbody = str_replace( "CONTACTNAME", $tname, $mybody );
        $tmpbody = str_replace( "DATES", $datestr, $tmpbody );
        $tmpbody = str_replace( "QUERYSTRING", $qs, $tmpbody );
        $tmpbody = str_replace( "TRAINERNAME", $tstr, $tmpbody );
        
        $mail->Body = $tmpbody;
        $mail->AddAddress(trim( $t ));
        $mail->Send();
    }
    $err = "Email Sent.";
}

if( $go || $err )
{
    $where = "";
    if( $zip )
        $where .= " and zip = '$zip'";

    $sql = "select c.* from company_esi c where iscorp = '$session_iscorp' and deleted = 0 and showsondrillreports = 1 $where $order";
    if( $where && $zip )
        $result = db_query_rows( $sql );
    else if( $go )
    {
        $err = "You need to choose a zip code.";
    }
}

if( $xls && !$err )
{
    // CSV Export
    $filename = "schools_" . date('Y-m-d') . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Write headers
    $headers = [
        "school",
        "campus",
        "contact email",
        "trainer",
        "last sent",
        "last drill date",
        "last service call date"
    ];
    fputcsv($output, $headers);
    
    $trainers = getTrainersForZip( $zip );
    $trainerNames = [];
    foreach( $trainers as $trow ) {
        $trainerNames[] = ($trow['first_name'] ?? '') . ' ' . ($trow['last_name'] ?? '');
    }
    $tstr = implode(", ", $trainerNames);

    foreach( $result as $r )
    {
        $ls = db_query_first_cell( "select max( dateadded ) from drillwarningsent where schoolid = " . (int)$r['id'] );
        $last = db_query_first_cell( "select d.drillid from drill d, drill_to_companyid dtc where dtc.companyid = " . (int)$r['id'] . " and drilldate >= '2021-02-01' and dtc.drillid = d.drillid " );
        if( $last ) continue;
        
        if( $ls )
            $ls = date( "m/d/Y h:i a", strtotime( $ls ) );
        $last = db_query_first_cell( "select max( drilldate ) from drill d, drill_to_companyid dtc where dtc.companyid = " . (int)$r['id'] . " and dtc.drillid = d.drillid " );
        $lastsc = db_query_first_cell( "select max( servicecalldate ) from servicecall d, servicecall_to_companyid dtc where dtc.companyid = " . (int)$r['id'] . " and dtc.servicecallid = d.servicecallid " );
        
        $rowData = [
            getCompanyNameWithColorString( $r, false ),
            getCampusName( $r["campusid"] ?? '' ),
            $r["contactemail"] ?? '',
            $tstr,
            $ls ?? '',
            $last ?? '',
            $lastsc ?? ''
        ];
        fputcsv($output, $rowData);
    }
    
    fclose($output);
    exit();
}

?>
<?php include "ssi/top.php"; ?>
<form name='myform' method='post' onSubmit='return checkZip( this.elements["zip"].value )' >
<input type='hidden' name='order' value='order by companyname'>

<!--start center content-->
<strong><span class="title">Schools</span></strong>
<p>

<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3">
    <tr height="23" bgcolor="#ffffff">
        <td valign="top"><span class="copy"><strong>Zip:</strong></span></td>
        <td><input type='text' name='zip' value='<?=htmlspecialchars($zip)?>'></td>
    </tr>
    <tr bgcolor="#ffffff">
        <td valign="bottom" colspan='2'><span class="copy">
        <font color='red'><?=htmlspecialchars($err)?></font><br>
        <input type='checkbox' name='xls' value='1'> Export to CSV? <br>
        <input type='submit' class='copy' name='go' value='Go'> </span>
        </td>
    </tr>
</table>

<?php if( !empty($result) ) { ?>
<a href='#' onClick='checkAll( true );return false '>Check All</a> | 
<a href='#' onClick='checkAll( false );return false '>Uncheck All</a>
<table class='table2'>
    <tr>
        <th></th>
        <th>School</th>
        <th>Campus</th>
        <th>Contact Email</th>
        <th>Trainer</th>
        <th>Last Sent</th>
        <th>Most Recent Drill</th>
        <th>Most Recent Service Call</th>
    </tr>
<?php
    $trainers = getTrainersForZip( $zip );
    $trainerNames = [];
    foreach( $trainers as $trow ) {
        $trainerNames[] = ($trow['first_name'] ?? '') . ' ' . ($trow['last_name'] ?? '');
    }
    $tstr = implode(", ", $trainerNames);

    foreach( $result as $row ) {
        $ls = db_query_first_cell( "select max( dateadded ) from drillwarningsent where schoolid = " . (int)$row['id'] );
        $last = db_query_first_cell( "select d.drillid from drill d, drill_to_companyid dtc where dtc.companyid = " . (int)$row['id'] . " and drilldate >= '2021-02-01' and dtc.drillid = d.drillid " );
        if( $last ) continue;
        
        if( $ls )
            $ls = date( "m/d/Y h:i a", strtotime( $ls ) );
        $last = db_query_first_cell( "select max( drilldate ) from drill d, drill_to_companyid dtc where dtc.companyid = " . (int)$row['id'] . " and dtc.drillid = d.drillid " );
        $lastsc = db_query_first_cell( "select max( servicecalldate ) from servicecall d, servicecall_to_companyid dtc where dtc.companyid = " . (int)$row['id'] . " and dtc.servicecallid = d.servicecallid " );
?>
<tr>
    <td><input type='checkbox' name='schoolids[]' class="tocheck" value='<?=htmlspecialchars($row['id'])?>' <?=in_array( $row['id'], $schoolids )?"CHECKED":""?>></td>
    <td class='copy'><a href='viewcompany.php?id=<?=htmlspecialchars($row["id"] ?? '')?>'><?= getCompanyNameWithColorString( $row )?></a></td>
    <td class='copy'><?=htmlspecialchars(getCampusName( $row["campusid"] ?? '' ))?></td>
    <td class='copy'><?=htmlspecialchars($row["contactemail"] ?? '')?></td>
    <td class='copy'><?=htmlspecialchars($tstr)?></td>
    <td class='copy'><?=htmlspecialchars($ls ?? '')?></td>
    <td class='copy'><?=htmlspecialchars($last ?? '')?></td>
    <td class='copy'><?=htmlspecialchars($lastsc ?? '')?></td>
</tr>
<?php } ?>
<tr>
    <td colspan='8'>
        Weekdates: <?=printdates2( "fromdate", $fromdate )?> - <?=printdates2( "todate", $todate )?><br>
        <input type='submit' class='copy' name='emailchecked' value='Email Checked'>
    </td>
</tr>
</table>
<?php
}
if( $go && empty($result) ) {
?>
<font color='red'>No results.</font>
<?php } ?>

</td></tr></table>
<!--end center content-->

<?php include "ssi/footer.php" ; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
<script language='javascript'>
function checkZip( zip )
{
    var badArray = new Array( <?=isset($bannedzips) ? implode( ", ", $bannedzips ) : ''?> );
    if( badArray.indexOf( parseInt( zip ) ) > -1  )
    {
        return confirm( "This is a restricted zip code. Are you sure you want to do this?" );
    }
    return true;
}

function checkAll( val )
{
    document.querySelectorAll(".tocheck").forEach(function(checkbox) {
        checkbox.checked = val;
    });
}
</script>
</tr>
</table>
<br><br>
</div>
</form>
</body>
</html>