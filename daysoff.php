<?php 
require "mysql.php";

if( !isOverallAdmin() || (strtolower( $session_userid ) != "sarahg@emergencyskills.com" ) )
{
Header( "Location: /index.php");
exit;
}

$whr = "";
if( isset($year) && $year )
    $whr .= " and datefor like '$year%'";
if( isset($types) && $types )
{
    $arr = array();
    foreach( $types as $t )
    {
        if( $t )
            $arr[] = "'$t'";
    }
    if( count( $arr ) )
        $whr .= " and whatdoing in ( ".implode( ",", $arr ) . " ) ";
}

if( isset($xls) && $xls )
{
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="daysoff.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write headers
    $headers = array("Date", "Name", "Reason");
    fputcsv($output, $headers);
    
    $res = mysqli_query( $link, "Select user.userid as username, workingon.* from workingon, user where user.id = workingon.userid $whr order by user.userid, datefor" );
    
    $lastperson = "";
    $count = 0;
    $rows_written = 0;
    
    while( $row = mysqli_fetch_assoc( $res ) )
    {
        // Add blank row and count when switching to a new person
        if( $lastperson != "" && $lastperson != $row["username"] )
        {
            // Write count row
            fputcsv($output, array("", "", $count));
            $rows_written++;
            
            // Write blank row
            fputcsv($output, array("", "", ""));
            $rows_written++;
            
            $count = 0;
        }
        
        $lastperson = $row["username"];
        $count++;
        
        // Write data row
        $data_row = array(
            date("m/d/Y", strtotime($row["datefor"])),
            $row["username"],
            $row["whatdoing"]
        );
        fputcsv($output, $data_row);
        $rows_written++;
    }
    
    // Write final count if we had any data
    if( $lastperson )
    {
        fputcsv($output, array("", "", $count));
        $rows_written++;
    }
    
    fclose($output);
    exit;
}

$isdashboard = 1;
include "ssi/top.php";
?>

<div class="box-header">
<div class="box-title">View Schedule</div>
</div><!--end box-header-->

<br>
<form method='post'>
Year: <select name=year>
<option value=''>All Years</option>
<?php for( $i = 2013; $i <= date( "Y" ); $i++ ) { ?>
<option value='<?=$i?>' <?php echo isset($year) && $year == $i?"SELECTED":"" ?>><?=$i?></option>
<?php } ?>
</select><br>
</select>
<select multiple size='5' name=types[]>
<option <?php echo isset($types) && in_array( "Working from home", $types )?"SELECTED":"" ?> >Working from home</option>
<option <?php echo isset($types) && in_array( "Not scheduled to work (ie weekend)", $types )?"SELECTED":"" ?> >Not scheduled to work (ie weekend)</option>
<option <?php echo isset($types) && in_array( "Working", $types )?"SELECTED":"" ?> >Working</option>
<option <?php echo isset($types) && in_array( "Vacation/Personal", $types )?"SELECTED":"" ?> >Vacation/Personal</option>
<option <?php echo isset($types) && in_array( "Out sick", $types )?"SELECTED":"" ?> >Out sick</option>
<option <?php echo isset($types) && in_array( "Bereavement", $types )?"SELECTED":"" ?> >Bereavement</option>
</select>
    <br>
    <i>select multiple by control-clicking</i>
    <br>
    <input type='checkbox' name='xls' value='1'> Export to Excel?<br>
<input type='submit' name='go' value='Search'>
<?php
    if( isset($go) && $go ) { 
        $res = mysqli_query( $link, "Select user.userid as username, workingon.* from workingon, user where user.id = workingon.userid $whr order by user.userid, datefor" );

        $lastperson = "";
        ?>

        <table cellpadding='2' cellspacing=0 width='400'  border=1>
        <tr><td><b>Date</b></td>
        <td><b>Name</b></td>
        <td><b>Reason</b></td>
        </tr>

<?php
        $count = 0;
        while( $row = mysqli_fetch_assoc( $res ) )
        {
            if( $lastperson != "" && $lastperson != $row["username"] )
            {
                echo( "<tr><td colspan='3' align='right'><b>$count</b>&nbsp;</td></tr>" );
                $count = 0;
                echo( "<tr><td colspan='4'>&nbsp;</td></tr>" );
            }
            $lastperson = $row["username"];
            $count++;
            echo( "<tr><td>".date( "m/d/Y", strtotime( $row["datefor"] ) ) . "</td><td>$row[username]</td><td>$row[whatdoing]</td></tr>" );
        }
        if( $lastperson )
        {
                echo( "<tr><td colspan='3' align='right'><b>$count</b>&nbsp;</td></tr>" );
                $count = 0;
        }
        ?>
        
        </table>
        <?php
    }

?>

<?php
include "ssi/footer.php" ; ?>
?>