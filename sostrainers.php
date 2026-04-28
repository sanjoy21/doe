<?php
// 465-3637
require_once('mysql.php');
if( isset($xls) && $xls ) {
    $fname = "sos_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Prepare headers
    $headers = array(
        "Class",
        "Class Date",
        "Date Requested",
        "Requested By",
        "Type",
        "Trainers When Requested"
    );
    
    // Write headers
    fputcsv($output, $headers);
    
    if( !isset($ob) || !$ob )
        $ob = "sostrainers.dateadded desc";
    
    $trainers = db_query_rows( "select sostrainers.*, concat( first_name, ' ', last_name ) as name from sostrainers, user where user.id = trainerid order by $ob" );
    
    $typearr = array( "1"=>"1 week", "36"=>"36 hour", "2"=>"2 week" );
    
    foreach( $trainers as $visitrow )
    {
        $row = array();
        $crow = getClassRow( $visitrow["classid"] );
        
        $row[] = $crow["id"] ?? '';
        $row[] = $crow["startdate"] ?? '';
        $row[] = $visitrow["dateadded"] ?? '';
        $row[] = $visitrow["name"] ?? '';
        $row[] = $typearr[$visitrow["type"]] ?? '';
        $row[] = $visitrow["currenttrainers"] ?? '';
        
        // Write row to CSV
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<p>
<strong><span class="title">SOS Requests</span></strong>
<a href='sostrainers.php?xls=1'>Export to CSV</a>
<p>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3" >
<tr bgcolor="#e1e1f6">
<th class='copy'>Class </th>
<th class='copy'>Class Date</th>
<th class='copy'><a href='sostrainers.php?ob=sostrainers.dateadded+desc'>Date Requested</a>        </th>
<th class='copy'><a href='sostrainers.php?ob=name'>Requested By</a></th>
<th class='copy'>Type        </th>
<th class='copy'><a href='sostrainers.php?ob=currenttrainers+desc'>Trainers When Requested</a></th>
</tr>
</tr>
<?php
// i tihnk we can assume they visited schools at the same time

if( !isset($ob) || !$ob )
    $ob = "sostrainers.dateadded desc";

$trainers = db_query_rows( "select sostrainers.*, concat( first_name, ' ', last_name ) as name from sostrainers, user where user.id = trainerid order by $ob" );

$typearr = array( "1"=>"1 week", "36"=>"36 hour", "2"=>"2 week" );
$bgcolor = "#FFFFFF";
foreach( $trainers as $visitrow )
{
    $crow = getClassRow( $visitrow["classid"] );
    $bgcolor = $bgcolor=="#FFFFFF"?"#CCFFCC":"#FFFFFF";
    echo( "<tr bgcolor='$bgcolor'>" );
    echo( "<td class='copy'><a href='class_detail.php?id=" . $visitrow["classid"] . "'>" . $crow["id"] . "</a></td>" );
    echo( "<td class='copy'> " . $crow["startdate"] . "</td>" );
    echo( "<td class='copy'>" . $visitrow["dateadded"] . "</td>" );
    echo( "<td class='copy'>". $visitrow["name"]."</td>" );
    echo( "<td class='copy'>" . $typearr[$visitrow["type"]]."</td>" );
    echo( "<td class='copy'>". $visitrow["currenttrainers"]."</td></tr>" );
}
?>
</table><p>

<?php include "ssi/footer.php" ; ?>

<!--end footer-->