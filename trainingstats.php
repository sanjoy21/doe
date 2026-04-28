<?php 
include "mysql.php";

function db_escape_string($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

if( isset($xls) && $xls ) {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="trainingstats.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    $firstmonth = "$frommonth/1/$fromyear";
    $endmonth = "$tomonth/1/$toyear";
    
    // Define columns structure
    $cols = array(
        "Corporate Heartsaver CPR, AED and/or First Aid" => array(),
        "Corporate BLS" => array(),
        "Corporate Alive! First Aid" => array(),
        "Corporate non certification i.e. Infant anytime and friends and family." => array(),
        "Mental Health First Aid" => array(),
        "Instructor Training" => array(),
        "DOE CPR/AED" => array( "reg" ),
        "DOE First Aid" => array( "cohfa" ),
        "Training Sites" => array( "" )
    );
    
    // Prepare and write headers
    $headers = array("month");
    foreach( $cols as $colname=>$throwaway ) {
        $headers[] = $colname;
    }
    fputcsv($output, $headers);
    
    $i = 0;
    while( strtotime( $firstmonth ) <= strtotime( $endmonth ) && $i < 100 )
    {
        $i++; 
        
        // Start new row
        $row_data = array();
        $row_data[] = date( "m/Y", strtotime( $firstmonth ) );
        
        foreach( $cols as $colname=>$codes )
        {
            $dt = date( "Y-m", strtotime( $firstmonth ) );
            if( $colname == "Training Sites" )
            {
                // Escape TRAININGSITES constant for SQL safety
                $training_sites_value = (int)TRAININGSITES;
                $numresps = db_query_first_cell( "select count(*) from responder_training_dates rtd, class cl, company_esi c where c.id = companyid and iscorp = " . $training_sites_value . " and rtd.classid = cl.id and startdate like '$dt%'" );
            }
            else 
            {
                if( !count( $codes ) )
                {
                    // Escape column name for SQL safety
                    $escaped_colname = db_escape_string($colname);
                    $codes = db_query_array( "select shortname from esioptionvalues where reportcode ='" . $escaped_colname . "'", "shortname", "shortname" );
                }
                
                $codestr = "-1";
                foreach( $codes as $c )
                {
                    // Escape each code value for SQL safety
                    $escaped_c = db_escape_string($c);
                    $codestr .= ",'$escaped_c'";
                }
                $numresps = db_query_first_cell( "select count(*) from responder_training_dates rtd, class cl where code in ( $codestr ) and rtd.classid = cl.id and startdate like '$dt%'" );
            }
            $row_data[] = $numresps ?? 0;
        }
        
        // Write row to CSV
        fputcsv($output, $row_data);
        
        // Move to next month
        $firstmonth = date( "m/d/Y", strtotime( "$firstmonth + 1 month" ) );
    }
    
    fclose($output);
    exit;
}

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">Training Report</span></strong>
<p>

<form method='post'>
From: 
<select name='frommonth'>
<?php for( $i = 1; $i <= 12; $i++ ) { 
    $sel = "";
    if( !isset($frommonth) && $i == date( "n" ) ) $sel = "SELECTED";
    if( isset($frommonth) && $i == $frommonth ) $sel = "SELECTED";
    ?>
<option value='<?=$i?>' <?=$sel?>><?=$i?></option>
<?php } ?>
</select>
<select name='fromyear'>
<?php for( $i = 2013; $i <= date( "Y" ); $i++ ) { 
    $sel = "";
    if( !isset($fromyear) && $i == date( "Y" ) ) $sel = "SELECTED";
    if( isset($fromyear) && $i == $fromyear ) $sel = "SELECTED";
    ?>
<option value='<?=$i?>' <?=$sel?>><?=$i?></option>
<?php } ?>
</select>
<br>
<br>
To:
<select name='tomonth'>
<?php for( $i = 1; $i <= 12; $i++ ) { 
    $sel = "";
    if( !isset($tomonth) && $i == date( "n" ) ) $sel = "SELECTED";
    if( isset($tomonth) && $i == $tomonth ) $sel = "SELECTED";
    ?>
<option value='<?=$i?>' <?=$sel?>><?=$i?></option>
<?php } ?>
</select>
<select name='toyear'>
<?php for( $i = 2013; $i <= date( "Y" ); $i++ ) { 
    $sel = "";
    if( !isset($toyear) && $i == date( "Y" ) ) $sel = "SELECTED";
    if( isset($toyear) && $i == $toyear ) $sel = "SELECTED";
    ?>
<option value='<?=$i?>' <?=$sel?>><?=$i?></option>
<?php } ?>
</select>
<br>
<br>
<input type='submit' name='xls' value='Write Training Stats Report'>
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