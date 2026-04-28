<?php

require_once "mysql.php";

// Safely access potential global variables (assuming they are defined elsewhere)
$cid = $cid ?? null;
$id = $id ?? null;
$where = $where ?? "";
$order = $order ?? "";
$xls = $xls ?? false;
$csv = $csv ?? false;
$session_iscorp = $session_iscorp ?? null;

if( $cid )
{
    // Fix: db_query_array requires quoted keys if the source array uses them, 
    // but the DB function call itself is fine.
    $possids = join( ", ", db_query_array( "select id from company_esi where deleted =0 and campusid = '$cid' ", "id", "id" ) );
    $session_iscorp = db_query_first_cell( "select iscorp from company_esi where deleted =0 and campusid = '$cid'" );
}
else
{
    $possids = $id;
}

$sql = "select r.*, region, companyname, schoolcode, iscorp from responders_esi r, company_esi c where c.id in ($possids) and r.deleted = 0 and c.deleted = 0 and c.id = r.clientid $where $order " ;

//echo( $sql );
$result = db_query_rows( $sql );


if( $xls )
{
    // CSV Download (replacing Excel)
    $filename = "report_responders_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Prepare header row
    $header = [
        "Last Name",
        "First Name",
        getSchoolStr("PMS ID"),
        "Title",
        "Location",
        "Email",
        "Phone",
        "Most Recent Training Date"
    ];

    if( !$session_iscorp ) {
        $header[] = "Training Extension Expiration Date";
    }

    $header = array_merge($header, [
        "Training Program Type",
        "Attended But Not Completed",
        "Upcoming Training Date",
        "Upcoming Training Type",
        "Current Certifications",
        "Paid?"
    ]);

    fputcsv($output, $header);
    
    foreach( $result as $row )
    {
        $tdate = "";
        $ttype = "";
        $upd = "";
        
        // Find most recent training
        // Quoted array key in query: '{$row['responderid']}'
        $tmpr = db_query_first( "select code, program, trainingdate from responder_training_dates left join class on class.id = classid where responderid = '{$row['responderid']}' order by trainingdate desc limit 1" ); 
        if( $tmpr["trainingdate"] ?? false )
        {
            $tdate = $tmpr["trainingdate"];
            $ttype = $tmpr["program"];
            if( !$ttype )
            {
                // Quoted array key: $tmpr['code']
                $ttype = $allclass_names[$session_iscorp][$tmpr['code']] ?? "";
            }
        }
        else
        {
            $tdate = $row["trainingdate"] ?? null;
            $ttype = $row["program"] ?? null;
        }

        // Find upcoming training dates
        // Quoted array key in query: '{$row['responderid']}'
        $upds = db_query_rows( "select startdate, code, ispaid from class, responder_to_class where classid = class.id and responderid = '{$row['responderid']}' and startdate > now() order by startdate desc" );

        if( !count( $upds ) )
        {
            $upds = array( ""=>array() );
        }

        // Get company row for location display
        // Quoted array key: $row['clientid']
        $s = getCompanyRow( $row['clientid'] );

        foreach( $upds as $updrow )
        {
            $upd = $updrow["startdate"] ?? null;
            $code = $updrow["code"] ?? null;
            $ispaid = ($updrow["code"] ?? null) ? (($updrow["ispaid"] ?? 0) ? "Yes":"No") : "";
            
            // Find current certifications (within last 2 years)
            // Quoted array key in query: '{$row['responderid']}'
            $currarr = db_query_rows( "select code, program, trainingdate from responder_training_dates left join class on class.id = classid where responderid = '{$row['responderid']}' and trainingdate > '" .date( "Y-m-d", strtotime( "2 years ago" )). "' " );
            $curr = array();
            foreach( $currarr as $c )
            {
                // Quoted array key: $c['code']
                $ctype = $allclass_names[$session_iscorp][$c['code']] ?? $c['program'];
                $curr[] = $ctype . ": " . $c['trainingdate']; 
            }
            
            // Attended But Not Completed (ABNC)
            $twomonths = date( "Y-m-d", strtotime( "2 months ago" ) );
            // Quoted array key in query: $row['responderid']
            $abnc = db_query_first_cell( "select concat( classid, ' - ', startdate ) from responder_to_class rtc, class c where rtc.attended = 1 and c.accepted = 1 and startdate > '$twomonths' and c.id = rtc.classid and responderid = {$row['responderid']} and responderid not in ( select responderid from responder_training_dates rtd where rtd.classid = c.id and rtd.responderid = {$row['responderid']} ) and startdate < now() order by c.startdate desc limit 1" );

            // Prepare data row
            $rowData = [
                $row["lastname"] ?? '',
                $row["firstname"] ?? '',
                $row["pmsid"] ?? '',
                $row["title"] ?? '',
                ($s['companyname'] ?? "") . " " . ($s['address'] ?? ""),
                $row["email"] ?? '',
                $row["dayphone"] ?? '',
                $tdate
            ];

            if( !$session_iscorp ) {
                $rowData[] = getExtensionDateByTrainingDate( $tdate );
            }

            $rowData = array_merge($rowData, [
                $ttype,
                $abnc,
                $upd,
                $code,
                implode( "; ", $curr ),
                $ispaid
            ]);

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
    }

    fclose($output);
    exit;
}


if( !$xls && !$csv )
{
?>

<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>

<html>
<head>
<title>Responder Report</title>

<link rel='stylesheet' href='../css/style.css'>
</head>

<body bgcolor='#ffffff'>
<table cellpadding="3" cellspacing="0" border="1" width="100%">
    <tr>
        <td valign="top"><span class="copy"><strong>Last Name</strong></span></td>
        <td valign="top"><span class="copy"><strong>First Name</strong></span></td>
        <td valign="top"><span class="copy"><strong><?php echo getSchoolStr( "PMS ID"); ?></strong></span></td>
        <td valign="top"><span class="copy"><strong>Title</strong></span></td>
        <td valign="top"><span class="copy"><strong>Location</strong></span></td>
        <td valign="top"><span class="copy"><strong>Email</strong></span></td>
        <td valign="top"><span class="copy"><strong>Phone</strong></span></td>
        <td valign="top"><span class="copy"><strong>Most Recent Training Date</strong></span></td>
    <?php if( !$session_iscorp ) { ?>
        <td valign="top"><span class="copy"><strong>Training Expiration Extension Date</strong></span></td>
                    <?php } ?>
        <td valign="top"><span class="copy"><strong>Attended But Not Completed</strong></span></td>
        <td valign="top"><span class="copy"><strong>Training Program Type</strong></span></td>
        <td valign="top"><span class="copy"><strong>Upcoming Training Date</strong></span></td>
        <td valign="top"><span class="copy"><strong>Upcoming Training Type</strong></span></td>
        <td valign="top"><span class="copy"><strong>Current Certifications</strong></span></td>
        <td valign="top"><span class="copy"><strong>Paid?</strong></span></td>
    </tr>
    <?php
    foreach( $result as $row )
{

    // Quoted array key in query: '{$row['responderid']}'
    $tmpr = db_query_first( "select code, program, trainingdate from responder_training_dates left join class on class.id = classid where responderid = '{$row['responderid']}' order by trainingdate desc limit 1" ); 
    $tdate = "";
    $ttype = "";
    if( $tmpr["trainingdate"] ?? false )
    {
        $tdate = $tmpr["trainingdate"];
        $ttype = $tmpr["program"];
        if( !$ttype )
        {
            // Quoted array key: $tmpr['code']
            $ttype = $allclass_names[$session_iscorp][$tmpr['code']] ?? "";
        }
    }
    else
    {
        $tdate = $row["trainingdate"] ?? null;
        $ttype = $row["program"] ?? null;
    }

    // Quoted array key in query: '{$row['responderid']}'
    $upds = db_query_rows( "select startdate, code, ispaid from class, responder_to_class where classid = class.id and responderid = '{$row['responderid']}' and startdate > now() order by startdate desc" );

    if( !count( $upds ) )
    {
        $upds = array( ""=>array() );
    }

    foreach( $upds as $updrow )
    {
        // Quoted array key: $row['clientid']
        $s = getCompanyRow( $row['clientid'] );

        $upd = $updrow["startdate"] ?? null;
        $code = $updrow["code"] ?? null;
        $ispaid = ($updrow["code"] ?? null) ? (($updrow["ispaid"] ?? 0) ? "Yes":"No"):"";

        // Quoted array key in query: '{$row['responderid']}'
        $currarr = db_query_rows( "select code, program, trainingdate from responder_training_dates left join class on class.id = classid where responderid = '{$row['responderid']}' and trainingdate > '" .date( "Y-m-d", strtotime( "2 years ago" )). "' " );
        $curr = array();
        foreach( $currarr as $c )
        {
            // Quoted array key: $c['code']
            $ctype = $allclass_names[$session_iscorp][$c['code']] ?? $c['program'];
            $curr[] = $ctype . ": " . ($c['trainingdate'] ?? null); 
        }

        $twomonths = date( "Y-m-d", strtotime( "2 months ago" ) );
        // Quoted array key in query: $row['responderid']
        $abnc = db_query_first_cell( "select concat( classid, ' - ', startdate ) from responder_to_class rtc, class c where rtc.attended = 1 and c.accepted = 1 and startdate > '$twomonths' and c.id = rtc.classid and responderid = {$row['responderid']} and responderid not in ( select responderid from responder_training_dates rtd where rtd.classid = c.id and rtd.responderid = {$row['responderid']} ) and startdate < now() order by c.startdate desc limit 1" );
        ?>
            <tr>
            <?php
            if( !($thisusersrow['healthdirector'] ?? false))
{
                    ?>
        <td valign="top"><span class="copy"><?php if( !$xls && !$csv ){ ?><a href="viewresponder.php?responderid=<?php echo $row["responderid"]; ?>"><?php } ?><?php echo $row["lastname"]; ?></a></span></td>
                                 <?php }
                                 else
                                 {
                                    ?>
            <td valign="top"><span class="copy"><?php if( !$xls && !$csv ){ ?><?php echo $row["lastname"]; ?><?php } ?></span></td>
                                <?php } ?>
        <td valign="top"><span class="copy"><?php echo $row["firstname"]; ?> </span></td>
        <td valign="top"><span class="copy"><?php echo $row["pmsid"]; ?> </span></td>
        <td valign="top"><span class="copy"><?php echo $row["title"]; ?> </span></td>
        <!-- Quoted array keys: $s['companyname'] and $s['address'] -->
        <td valign="top"><span class="copy"><?php echo ($s['companyname'] ?? "") . " - " . ($s['address'] ?? ""); ?></span></td>
        <td valign="top"><span class="copy"><?php echo $row["email"]; ?></span></td>
        <td valign="top"><span class="copy"><?php echo $row["dayphone"]; ?></span></td>
        <td valign="top"><span class="copy"><?php echo $tdate; ?></span></td>
             <?php
             if( !$session_iscorp ) { ?>
        <td valign="top"><span class="copy"><?php echo getExtensionDateByTrainingDate( $tdate ); ?></span></td>
                                     <?php } ?>
        <td valign="top"><span class="copy"><?php echo $abnc; ?></span></td>
        <td valign="top"><span class="copy"><?php echo $ttype; ?></span></td>
        <td valign="top"><span class="copy"><?php echo $upd; ?></span></td>
        <td valign="top"><span class="copy"><?php echo $code; ?></span></td>
        <td valign="top"><span class="copy"><?php echo implode( "<br>", $curr ); ?></span></td>
        <td valign="top"><span class="copy"><?php echo $ispaid; ?></span></td>
    </tr>
            <?php
    }
}
    ?>
</table>

<?php
}
?>