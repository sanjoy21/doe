<?php
require_once('mysql.php');
if( getcurrentusertype() != 'principal' )
{
    header( "location: login.php" );
    exit;
}

$dtstr = "";
$res = [];

if( isset($_REQUEST['go']) )
{
    $datefrom = $_REQUEST['datefrom'] ?? '';
    $dateto = $_REQUEST['dateto'] ?? '';
    
    if( $datefrom )
        $dtstr .= " and thedate >= '$datefrom'";
    if( $dateto )
        $dtstr .= " and thedate <= '$dateto'";
        
    $res = db_query_rows( "select u.*, thedate from user u, tdrillcalendar t where t.userid = u.id and status = 1 $dtstr order by thedate" );
}

if( isset($_REQUEST['xls']) )
{
    $filename = "cbavail_" . date('Y-m-d') . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Write headers
    fputcsv($output, ['Name', 'Email', 'Date']);
    
    // Write data rows
    foreach( $res as $row )
    {
        $csvRow = [
            $row['first_name'] . ' ' . $row['last_name'],
            $row['userid'] ?? '',
            $row['thedate'] ?? ''
        ];
        fputcsv($output, $csvRow);
    }
    
    fclose($output);
    exit;
}

$contentextrastyle = "width: auto";
?>
<?php include "ssi/top.php"; ?>		
<!--start center content-->

<strong><span class="title">Code Blue Availability</span> <a href='codebluecalendar.php' target='_blank'>Calendar View</a></strong>
<?=isset($err) ? htmlspecialchars($err) : ''?>
<form method='get' action="codeblueavail.php">
    <table cellpadding="3" cellspacing="0" border="0" class="table3">
        <tr>
            <td valign="middle"><span class="copy">Date From</span></td>
            <td valign="middle"><span class="copy"><?=printdates2( "datefrom", $datefrom ?? '' )?></span></td>
            <td valign="middle"><span class="copy">to</span></td>
            <td valign="middle"><span class="copy"><?=printdates2( "dateto", $dateto ?? '' )?></span></td>
            <td valign="middle"><span class="copy">CSV Export? <input type='checkbox' name='xls' value='1'></span></td>
            <td valign="middle"><input type='submit' name='go' value='Go'></td>
        </tr>
    </table>
</form>

<?php if( isset($_REQUEST['go']) ): ?>
<table border=1 cellpadding=4 class='table2' cellspacing=0>
    <tr>
        <th>Trainer</th>
        <th>Email</th>
        <th>Date</th>
    </tr>
    <?php foreach( $res as $u ): ?>
    <tr>
        <td><?=htmlspecialchars($u['first_name'] . ' ' . $u['last_name'])?></td>
        <td><?=htmlspecialchars($u['userid'] ?? '')?></td>
        <td><?=htmlspecialchars($u['thedate'] ?? '')?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<br><br>

</body>
</html>