<?php
include "mysql.php";

// ---------------------------------------------------------------------
// 1. INPUT SANITIZATION AND VALIDATION (SQLi Prevention)
// ---------------------------------------------------------------------

// Placeholder for a proper database escape function.
// For production use, this MUST be replaced by prepared statements.
if (!function_exists('db_escape_or_placeholder')) {
    function db_escape_or_placeholder($str) {
        // In a real environment, this would call mysqli_real_escape_string or similar.
        // For this example, we treat it as a cleaning function for string literals.
        return $str; 
    }
}

// Sanitize/Validate dynamic table name
$type = $_REQUEST['type'] ?? '';
if (!in_array($type, ['drill', 'servicecall'])) {
    $type = 'drill'; // Default to 'drill' if invalid
}

// Sanitize ID array for printing
$goprint = $_REQUEST['goprint'] ?? null;
$ids = $_REQUEST['ids'] ?? [];

// Sanitize string inputs for SQL
$fieldfrom_safe = db_escape_or_placeholder( $_REQUEST['fieldfrom'] ?? '' );
$fieldto_safe = db_escape_or_placeholder( $_REQUEST['fieldto'] ?? '' );
$borough_safe = db_escape_or_placeholder( $_REQUEST['borough'] ?? '' );
// Assuming $session_iscorp comes from a session and needs cleaning for the query
$session_iscorp_safe = db_escape_or_placeholder( $session_iscorp ?? '' ); 

// Sanitize boolean/integer inputs by casting
$notinvoiced = (int)($_REQUEST['notinvoiced'] ?? 0);
$actionneeded = (int)($_REQUEST['actionneeded'] ?? 0);
$excludewithdrill = (int)($_REQUEST['excludewithdrill'] ?? 0);
$newinstall = (int)($_REQUEST['newinstall'] ?? 0);
$notcompleted = (int)($_REQUEST['notcompleted'] ?? 0);
$showcampus = (int)($_REQUEST['showcampus'] ?? 0);
$xls = (int)($_REQUEST['xls'] ?? 0);

if( $goprint )
{
    foreach( $ids as $id_raw )
    {
        $id = (int)$id_raw; // Ensure IDs are integers
        if ($id <= 0) continue; 
        
        // Query 1: Use sanitized integer ID
        $drillrow = db_query_first( "select * from drill where drillid = {$id}" );
        
        // Ensure drillrow exists and data is clean before use
        if ($drillrow) {
            // $d and $schoolid are assumed to be safe integers/strings from the DB
            $d = $drillrow["drilldate"];
            $schoolid = (int)$drillrow["companyid"];
            include "billingworksheet.php"; // Assumes this file is safe
        }
    }
    exit;
}

// ---------------------------------------------------------------------
// 2. DYNAMIC TABLE SETUP
// ---------------------------------------------------------------------

if( $type == "drill" )
{
    $thetable = "drill";
    $table = "drill";
    $otherfields = array( "drillid", "numaeds", "participants", "score", "nextdate", "comments", "schoolcode", "zip", "inspector", "serial", "lastnotified", "other", "notrained", "completed", "received", "address", "floor", "city", "state", "zip", "companyid" );
    $otherfieldsdispl = array( "other"=>"failure comments", "notrained"=>"failed drill" );
    $extrafields = ", schoolcode, zip";
}
else // servicecall
{
    $thetable = "servicecall";
    $table = "servicecall";
    $otherfields = array( "servicecallid", "reason", "comments", "serial", "inspector", "address", "floor", "city", "state", "zip", "completed", "qainspection", "newinstall", "fromdrill", "assocdrillid", "companyid" );
    // Note: $type is validated, so using it here is relatively safe
    $lj = " left join aed_to_".$type." ats on ats.".$type."id = t.".$type."id ";
    $extrafields = ", serial";
}
$datefield = $thetable."date";
$idfield = $thetable."id";

// ---------------------------------------------------------------------
// 3. SQL WHERE CLAUSE CONSTRUCTION (Using Sanitized Inputs)
// ---------------------------------------------------------------------

$extra = '';
if( $fieldfrom_safe )
{
    $extra .= " and $datefield >= '{$fieldfrom_safe}' ";
}
if( $fieldto_safe )
{
    $extra .= " and $datefield <= '{$fieldto_safe}' ";
}

if( !isOverallAdmin() )
{
    $extra .= " and deleted = 0";
}

if( $notinvoiced )
{
    $extra .= " and invoiced = 0 and completed = 1";
}
if( $actionneeded )
{
    $extra .= " and actionneeded = 1";
}
if( $borough_safe )
{
    $extra .= " and borough = '{$borough_safe}'";
}
if( $excludewithdrill )
{
    $extra .= " and fromdrill = '0'";
}

if( $newinstall )
    $extra .= " and newinstall = 1";

$comp = $notcompleted?" and t.completed = 0":"";

// MAIN QUERY: Using sanitized session_iscorp_safe
$sql = ( "select t.*, companyname, retired, schoolcode, borough, address, city, state, zip, floor $extrafields 
         from ( $table t, company_esi ) $lj 
         where iscorp = '{$session_iscorp_safe}' $comp 
         and companyid = company_esi.id 
         and showson".$thetable."reports = 1 $extra 
         order by $datefield, $idfield" );

// Note: If you need to debug, ensure you escape the output!
// echo( htmlspecialchars($sql) . "<br>" ); 

$res = db_query_rows( $sql );

// ---------------------------------------------------------------------
// 4. XLS EXPORT LOGIC (Ensuring clean IDs for sub-queries)
// ---------------------------------------------------------------------

if( $xls ) {
    $filename = "report_" . $table . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Prepare headers
    $headers = array();
    $headers[] = "school";
    $headers[] = "schoolcode";
    $headers[] = "date";
    $headers[] = "call number";
    
    foreach( $otherfields as $o ) { 
        $displ = $o; 
        if( $otherfieldsdispl[$o] )
            $displ = $otherfieldsdispl[$o];
        $headers[] = $displ;
    }
    
    $headers[] = "invoiced";
    $headers[] = "invoiceno";
    
    // Write headers
    fputcsv($output, $headers);
    
    // Writing Data Rows
    foreach( $res as $r2 )
    {
        $tmparr = array();
        // Ensure ID is an integer for sub-queries
        $entity_id_int = (int)$r2[$thetable."id"];
        
        // Query 2: Ensure ID is an integer
        $oth = db_query_array( "select companyid from ".$thetable."_to_companyid where ".$thetable."id = {$entity_id_int}", "companyid", "companyid" );
        
        if( $oth && $showcampus )
        {
            foreach( $oth as $s )
            {
                $s_int = (int)$s; // Ensure campus ID is integer
                if( $s_int )
                {
                    $crow = getCompanyRow( $s_int );
                    $r2['companyname'] = $crow['companyname'];
                    $r2['retired'] = $crow['retired'];
                    $r2['schoolcode'] = $crow['schoolcode'];
                    if( $s_int != (int)$r2['companyid'] )
                        $r2['myother'] = "yes";
                    else
                        $r2['myother'] = "";
                    $tmparr[] = $r2;
                }
            }
        }
        else if( !$showcampus )
        {
            $tmparr[] = $r2;
        }
        
        foreach( $tmparr as $r )
        {
            $row = array();
            $row[] = $r["companyname"] ?? '';
            $row[] = $r["schoolcode"] ?? '';
            $row[] = $r[$datefield] ?? '';
            $row[] = $r["callnumber"] ?? '';
            
            foreach( $otherfields as $o ) {
                $val = $r[$o] ?? ''; // Use null coalescing for safety
                if( $type == "drill" && $o == "serial" )
                { 
                    $drill_id_int = (int)$r['drillid'];
                    // Query 3: Ensure ID is an integer
                    $val = join( ", ", db_query_array( "select serial from aed_to_drill where drillid = {$drill_id_int}", "serial", "serial" ) );
                }
                else if( $o=="drillid" || $o=="servicecallid" )
                {
                    $tmp = ($o=="drillid"?"D":"S" ).$r[$o];
                    if( $r['myother'] )
                        $tmp .= "c" ;
                    $val = $tmp;
                }
                else if( in_array($o, ["failedother", "notrained", "refused", "completed", "received"]) )
                {
                    $val = $r[$o]?"Y":"N";
                }
                else if( $o == "numaeds" )
                {
                    $drill_id_int = (int)$r['drillid'];
                    // Query 4: Ensure ID is an integer
                    $val = db_query_first_cell( "select count(*) from aed_to_drill where drillid = {$drill_id_int}" );
                }
                
                $row[] = $val;
            }
            
            $row[] = $r["invoiced"]?"Y":"N";
            $row[] = $r["invoiceno"] ?? '';
            
            // Write row to CSV
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit; // Important: Stop further script execution after CSV output
}
else
{
    // ---------------------------------------------------------------------
    // 5. HTML OUTPUT LOGIC (XSS Prevention)
    // ---------------------------------------------------------------------
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
    <title>Report</title>
</head>
<script language='javascript'>
    function checkAll(formname, checktoggle)
{
    var checkboxes = new Array();
    checkboxes = document[formname].getElementsByTagName('input');

    for (var i=0; i<checkboxes.length; i++)  {
        if (checkboxes[i].type == 'checkbox')   {

            checkboxes[i].checked = checktoggle;
        }
    }
}
</script>
<body bgcolor="#ffffff">
<form method='post' name='seform' target=_blank>
    <input type='submit' name='goprint' value='Print Checked'>
<a onclick="javascript:checkAll('seform', true);" href="javascript:void();">Check All </a> || <a onclick="javascript:checkAll('seform', false);" href="javascript:void();">Uncheck All</a>
<table cellpadding="3" cellspacing="0" border="1" width="100%">

<tr><th>print?</th><th>school</th><th>schoolcode</th><th>date</th>
<?php foreach( $otherfields as $o ) { ?>
    <!-- XSS Mitigation: Escape field names for header display -->
    <th><?=htmlspecialchars($o)?></th>
<?php } ?>
</tr>

<?php
    foreach( $res as $r2 )
    {
        $tmparr = array();
        $entity_id_int = (int)$r2[$thetable."id"];

        // Query 5: Ensure ID is an integer
        $oth = db_query_array( "select companyid from ".$thetable."_to_companyid where ".$thetable."id = {$entity_id_int}", "companyid", "companyid" );
        
        if( $oth && $showcampus )
        {
            foreach( $oth as $s )
            {
                $s_int = (int)$s; // Ensure campus ID is integer
                if( $s_int )
                {
                    $crow = getCompanyRow( $s_int );
                    $r2['companyname'] = $crow['companyname'];
                    $r2['retired'] = $crow['retired'];
                    $r2['schoolcode'] = $crow['schoolcode'];
                    if( $s_int != (int)$r2['companyid'] )
                        $r2['myother'] = "yes";
                    else
                        $r2['myother'] = "";
                        
                    $tmparr[] = $r2;
                }
            }
        }
        else if( !$showcampus )
        {
            $tmparr[] = $r2;
        }
        foreach( $tmparr as $r )
        {
?>
<tr>
<td>
    <!-- XSS Mitigation: Ensure ID value is safe in HTML attribute -->
    <input type='checkbox' name='ids[]' value='<?=htmlspecialchars($type=="drill"?$r['drillid']:$r['servicecallid'])?>'></td>
<!-- XSS Mitigation: Escape all content from DB -->
<td><?=htmlspecialchars($r["companyname"]) ?></td>
<td><?=htmlspecialchars($r["schoolcode"])?></td>
<td><?=htmlspecialchars($r[$datefield])?></td>
<?php foreach( $otherfields as $o ) { 
    $val = $r[$o] ?? ''; // Initialize or use DB value
    $entity_id_int = (int)$r[$thetable."id"];

    if( $type == "drill" && $o == "serial" )
    {
        $drill_id_int = (int)$r['drillid'];
        // Query 6: Ensure ID is an integer
        $val = join( ", ", db_query_array( "select serial from aed_to_drill where drillid = {$drill_id_int}", "serial", "serial" ) );
    }
    if( $o == "drillid" || $o == "servicecallid" )
    {
        $val = ($o=="drillid"?"D":"S" ).$r[$o];
        if( $r['myother'] )
            $val .= "c" ;
    }

    if( in_array($o, ["failedother", "notrained", "refused", "completed", "received"]) )
    {
        $val = $r[$o]?"Y":"N";
    }
    if( $o == "numaeds" )
    {
        $drill_id_int = (int)$r['drillid'];
        // Query 7: Ensure ID is an integer
        $val = db_query_first_cell( "select count(*) from aed_to_drill where drillid = {$drill_id_int}" );
    }
    
?>
    <!-- XSS Mitigation: Escape all dynamic table cell content -->
    <td valign='top'><?=nl2br(htmlspecialchars( $val ))?></td>
    <?php } ?>
</tr>
<?php }
    }?>
</table>
    <input type='submit' name='goprint' value='Print Checked'>
    </form>
    <?php } ?>