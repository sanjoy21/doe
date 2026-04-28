<?php

require_once('mysql.php');

//sendMail( "rachelc@gmail.com", "whatever", "whatever", "info@emergencyskills.com" );
if( getcurrentusercompany() > 0 )
{
    Header( "location: login.php" );
    exit;
}

function getRTTDisplay( $r )
{
    // Use quoted array keys (PHP 8.2 syntax) and null coalescing for safety.
    $done = $r['done'];

    if( !$done )
        return "Pending";
    if( $done == -1 )
        return "Denied By ESI";
    if( $done == -5 )
        return "Replied No";
    if( $done == -4 )
        return "Clicked Link but Did Not Reply";
    if( $done == 1 )
        return "Accepted By ESI";
    if( $done == -2 )
        return "Ignored By ESI";
    if( $done == -6 )
        return "Not Seen";
}

if( $go )
{
    if( $fromdate )
        $extra .= " and rtt.requestdate >= '$fromdate' ";
    
    if( $todate )
    {
        $special = date( "Y-m-d 23:59:59", strtotime( $todate ) );
        $extra .= " and rtt.requestdate <= '$special' ";
    }

    $sql = "select rtt.*, startdate from requesttotrain rtt, class c where c.id = rtt.classid $extra order by startdate";
    // Assuming db_query_rows() returns an array or similar structure
    $rep = db_query_rows( $sql );
}

if( $xls )
{
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="rttreport.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write headers
    $headers = array("Date", "Trainer", "Class", "Class Date", "Status", "Why?");
    fputcsv($output, $headers);
    
    $alltrainers = db_query_rows( "select * from user where usertype= 'trainer'", "id" );
    
    foreach( $rep as $r )
    {
        // Get trainer row with null safety
        $trow = $alltrainers[$r['trainerid']] ?? array('first_name' => '', 'last_name' => '');
        
        // Prepare row data
        $row_data = array(
            getFormattedDateWTime( $r['requestdate'] ) ?? '',
            ($trow['first_name'] ?? '') . " " . ($trow['last_name'] ?? ''),
            $r['classid'] ?? '',
            getFormattedDateWTime( $r['startdate'] ) ?? '',
            getRTTDisplay( $r ) ?? '',
            ($r['done'] == -5) ? ($r['whynot'] ?? '') : ''
        );
        
        // Write row to CSV
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    exit;
}
?>

<?php include "ssi/top.php"; ?>
<!--start center content-->
<strong><span class="title">REQUEST TO TRAIN REPORT</span></strong>

<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
<form method='get'>
<table>
<tr><td class='copy'>
From (YYYY-MM-DD)<?=printdates2( "fromdate", $fromdate )?> To <?=printdates2( "todate", $todate )?><Br>
<input type='checkbox' name='xls' value='1'> XLS?
<input type='submit' name='go' class=copy value='Go'>
</td></tr></table>
<!--end center content-->
<?php
if( $go )
{
$alltrainers = db_query_rows( "select * from user where usertype= 'trainer'", "id" );
?>
<table cellpadding = 2 border = 1 cellspacing = 0 >
<tr><th>Date</th><th>Trainer</th><th>Class</th><th>Class Date</th><th>Status</th><th>Why?</th></tr>
<?php
foreach($rep as $r)
{
if( $r['done'] == -6 ) continue;
$trow = $alltrainers[$r['trainerid']];
$trainerName = $trow['first_name']  . " " . $trow['last_name'];
echo "<tr><td>".getFormattedDateWTime($r['requestdate'] )."</td><td>".$trainerName."</td><td><a href='class_detail.php?id={$r['classid']}'>{$r['classid']}</a></td><td>".getFormattedDateWTime($r['startdate']) ."</td><td>";
echo getRTTDisplay($r);
echo "</td><td>";
if( $r['done']  == -5 )
echo $r['whynot'];
else
echo "&nbsp;";
echo "</td></tr>";
}
echo "</table>";
}
?>
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