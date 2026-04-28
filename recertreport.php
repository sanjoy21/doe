<?php
include "mysql.php";
$iscalendar = true;

// Initialize variables
$doit = $_POST['doit'] ?? false;
$noupcoming = $_POST['noupcoming'] ?? false;
$xls = $_POST['xls'] ?? false;
$rfrom = $_POST['rfrom'] ?? '';
$rto = $_POST['rto'] ?? '';
$res = [];
$upcomingext = "";
$dt = "";

if( $doit ){ 
    $thetable = "supplyrequests";
    $table = "supplyrequests";
    $extrafields = ", schoolcode, zip";

    if( $noupcoming )
    {
        $dt = date( "Y-m-d", mktime( 0,0,0,date("m")-2, date("d"), date("Y") ) );
        $upcomingArray = db_query_array( "select distinct( companyid ) from class, company_esi where class.companyid = company_esi.id and startdate > '$dt' and class.deleted = 0 ", "companyid", "companyid" );
        if (!empty($upcomingArray)) {
            $up = implode( ", " , $upcomingArray );
            $upcomingext = " and companyid not in ( $up )";
        }
    }

    $sql = "select t.*, companyname from company_esi, class t where t.deleted = 0 and company_esi.deleted = 0 and iscorp = '1' and companyid = company_esi.id and startdate > '$rfrom' and startdate < '$rto' $upcomingext order by startdate";
    $res = db_query_rows( $sql );
}

if( $xls ) {
    // CSV Export
    $filename = "recertreport_" . date('Y-m-d') . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Write headers
    $headers = [
        "company name",
        "contact name", 
        "phone",
        "email",
        "medical director name",
        "date",
        "type", 
        "class #",
        "upcoming class",
        "notes"
    ];
    fputcsv($output, $headers);
    
    // Write data rows
    foreach( $res as $r )
    {
        $medicaldirector = "";
        $medicaldirectorArray = db_query_array( 
            "select distinct( directorname ) from aed_esi where clientid = " . (int)$r['companyid'], 
            "directorname", 
            "directorname" 
        );
        if (!empty($medicaldirectorArray)) {
            $medicaldirector = implode(", ", $medicaldirectorArray);
        }
        
        $recertnotes = db_query_first_cell( 
            "select recertificationnotes from recertnotes where companyid = " . (int)$r['companyid'] . 
            " order by recertdate desc limit 1" 
        );
        
        $row = [
            $r["companyname"] ?? '',
            ($r["firstname"] ?? '') . " " . ($r["lastname"] ?? ''),
            $r["phone"] ?? '',
            $r["email"] ?? '',
            $medicaldirector,
            $r["startdate"] ?? '',
            $allclass_names[1][$r["code"] ?? ''] ?? '',
            $r["id"] ?? '',
            '',
            $recertnotes ?? ''
        ];
        
        if( !$noupcoming && !empty($dt) )
        {
            $next = db_query_first( 
                "select t.* from class t where t.deleted = 0 and companyid = " . (int)$r['companyid'] . 
                " and startdate > '$dt' order by startdate desc limit 1" 
            );
            if( $next["id"] ?? false )
            {
                $row[8] = ($next["id"] ?? '') . ":" . ($next["startdate"] ?? '');
            }
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
Recerts between: <?=printdates2( "rfrom", $rfrom )?> and <?=printdates2( "rto", $rto )?>
    <br>Don't show companies with a class within the last 2 months or in the future: <input type='checkbox' name='noupcoming' value='1' <?=$noupcoming?"CHECKED":""?> ><br>
    <br>CSV Export: <input type='checkbox' name='xls' value='1' <?=$xls?"CHECKED":""?> ><br>
<input type='submit' name='doit' value='Go'>
</form>

<?php if( $doit ) { ?>
<table cellpadding="3" cellspacing="0" border="1" width="100%">

<tr>
    <th class='copy'>company name</th>
    <th class='copy'>contact name</th>
    <th class='copy'>phone</th>
    <th class='copy'>email</th>
    <th class='copy'>medical director name</th>
    <th class='copy'>date</th>
    <th class='copy'>type</th>
    <th class='copy'>class #</th>
    <th class='copy'>upcoming class</th>
    <th class='copy'>notes</th>
</tr>
<?php foreach( $res as $r ) {
    $medicaldirectorArray = db_query_array( 
        "select distinct( directorname ) from aed_esi where clientid = " . (int)$r['companyid'], 
        "directorname", 
        "directorname" 
    );
    $medicaldirector = "";
    if (!empty($medicaldirectorArray)) {
        $medicaldirector = implode(", ", $medicaldirectorArray);
    }
    
    $recertnotes = db_query_first_cell( 
        "select recertificationnotes from recertnotes where companyid = " . (int)$r['companyid'] . 
        " order by recertdate desc limit 1" 
    );
?>
<tr>
    <td valign='top' class='copy'><a target='_blank' href='viewcompany.php?id=<?=htmlspecialchars($r["companyid"] ?? '')?>'><?=htmlspecialchars($r["companyname"] ?? '')?></a></td>
    <td valign='top' class='copy'><?=htmlspecialchars(($r["firstname"] ?? '') . " " . ($r["lastname"] ?? ''))?></td>
    <td valign='top' class='copy'><?=htmlspecialchars($r["phone"] ?? '')?></td>
    <td valign='top' class='copy'><a href='mailto:<?=htmlspecialchars($r["email"] ?? '')?>'><?=htmlspecialchars($r["email"] ?? '')?></a></td>
    <td valign='top' class='copy'><?=htmlspecialchars($medicaldirector)?></td>
    <td valign='top' class='copy'><?=htmlspecialchars($r["startdate"] ?? '')?></td>
    <td valign='top' class='copy'><?=htmlspecialchars($allclass_names[1][$r["code"] ?? ''] ?? '')?></td>
    <td valign='top' class='copy'><a target='_blank' href='class_detail.php?id=<?=htmlspecialchars($r["id"] ?? '')?>'><?=htmlspecialchars($r["id"] ?? '')?></a></td>
    <?php if( !$noupcoming && !empty($dt) ) {
        $next = db_query_first( 
            "select t.* from class t where t.deleted = 0 and companyid = " . (int)$r['companyid'] . 
            " and startdate > '$dt' order by startdate desc limit 1" 
        );
        if( $next["id"] ?? false ) {
            echo( "<td valign='top' class='copy'><a target='_blank' href='class_detail.php?id=" . htmlspecialchars($next["id"]) . "'>" . 
                  htmlspecialchars($next["id"]) . " : " . htmlspecialchars($next["startdate"] ?? '') . "</a></td>" );
        } else {
            echo( "<td>&nbsp;</td>" );
        }
    } else {
        echo( "<td>&nbsp;</td>" );
    } ?>
    <td valign='top' class='copy'><?=nl2br(htmlspecialchars($recertnotes ?? ''))?></td>
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
<?php } ?>