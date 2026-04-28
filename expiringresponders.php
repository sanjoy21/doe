<?php
require_once('mysql.php');

$typedispl = "Responders";

if( !$iscorp )
    $iscorp = 0;

if( !$go ) {
    // When no search is performed, show current month
    $fromdt_display = date('Y-m-01');
    $todt_display = date('Y-m-t');
    $title = getSessionTypeDisplay( $iscorp ) . " " . $typedispl . " Expiring in " . date( "F" );
} else {
    $fromdt_display = $fromdt;
    $todt_display = $todt;
    $title = getSessionTypeDisplay( $iscorp ) . " " . $typedispl . " Expiring between $fromdt and $todt";
}
?>
<?php include "ssi/top.php"; ?>

<form method='get'>
<input type='hidden' name='iscorp' value='<?=$iscorp?>'>
Expiring responders between: <?=printdates2( "fromdt", $fromdt_display )?> and <?=printdates2( "todt", $todt_display )?><br>
<input type='submit' name='go' value='Search'>
</form>

<?php 
// Only show results if search was performed or we're showing default view
if( $go || !isset($_GET['go']) ) 
{
    if( $go ) {
        // When searching, adjust dates back 2 years for training date comparison
        $fromdt_query = date("Y-m-d", strtotime($fromdt . " - 2 years"));
        $todt_query = date("Y-m-d", strtotime($todt . " - 2 years"));
    } else {
        // Default view: show certifications expiring this month
        $fromdt_query = date("Y-m-d", strtotime("first day of this month - 2 years"));
        $todt_query = date("Y-m-d", strtotime("last day of this month - 2 years"));
        $fromdt_display = date('Y-m-01');
        $todt_display = date('Y-m-t');
    }
?>
<span class="page-head"><?=$title?></span><br><br><br clear="all">
<!----------begin FR2 Adult Pads box------------->

<table class="table2">
<tr>
<th class="left">Expiration Date</th>
<th class="left">Name</th>
<th class="left">Company</th>
<th class="left">Next Class</th>
</tr>
<?php
    // Debug the query
    // echo "Query dates: fromdt_query=$fromdt_query, todt_query=$todt_query<br>";
    
    // Fix the SQL query - there's a column name mismatch
    $sql = "SELECT r.* FROM responders_esi r 
            WHERE r.responderid IN (
                SELECT responderid FROM responder_training_dates 
                WHERE trainingdate >= '$fromdt_query' 
                AND responderid NOT IN (
                    SELECT responderid FROM responder_to_class, class 
                    WHERE startdate > NOW() AND class.id = classid
                ) 
                AND trainingdate < '$todt_query'
            ) 
            AND deleted = 0 
            AND clientid IN (
                SELECT id FROM company_esi 
                WHERE iscorp = 0 
                AND deleted = 0 
                AND showsondrillreports = 1
            ) 
            AND r.responderid NOT IN (
                SELECT responderid FROM responder_training_dates 
                WHERE trainingdate >= '$todt_query'
            )";
    
    // Debug: echo the SQL to check it
    // echo "SQL: $sql<br>";
    
    $rows = db_query_rows( $sql );
    
    // Debug: Check if we got any results
    // echo "Number of rows: " . count($rows) . "<br>";

    $rarr = array();
    if ($rows) {
        foreach( $rows as $r )
        {
            $exp = db_query_first_cell( "SELECT MAX(trainingdate) FROM responder_training_dates WHERE responderid = " . ($r['responderid']) );
            if ($exp) {
                $exp = strtotime( $exp );
                $tm = mktime( 0,0,0,date( "m", $exp ) , date( "d", $exp ) , date( "Y", $exp )  + 2 );
            } else {
                $tm = 0;
            }
            
            $r['tm'] = $tm;
            // Fixed the column name - should be 'lastname' not 'last_name'
            $rarr[$tm. "_" . ($r['lastname']) . "_" . ($r['responderid'])] = $r;
        }
    }

    if (count($rarr) > 0) {
        ksort( $rarr );
        foreach( $rarr as $r )
        {
            $tm = $r['tm'];
            $dtstr = $tm ? date( "m/d/Y", $tm ) : 'N/A';
            
            $next = db_query_first( "SELECT * FROM class WHERE companyid = '" . ($r['clientid']) . "' AND startdate > NOW() AND deleted = 0 AND canceldate IS NULL ORDER BY startdate LIMIT 1" );
            if( !empty($next['id']) )
            {
                $nextdisplay = "<a href='class_detail.php?id=" . ($next['id']) . "'>" . date("m/d/Y", strtotime($next['startdate'])) . "</a><br>";
            }
            else
            {
                $nextdisplay = "None<br>";
            }
?>
<tr>
<td class="left"><?=$dtstr?></td>
                     <td class="left"><a href="editresponder.php?responderid=<?=$r['responderid']?>"><?=($r['firstname']) . " " . ($r['lastname'])?></a></td>
                     <td class="left"><a href="viewcompany.php?id=<?=$r['clientid']?>"><?=getCompanyName( $r['clientid'] )?></a></td>
<td class="left"><?=$nextdisplay?></td>
</tr>
        <?php } 
    } else { ?>
        <tr>
            <td colspan="4" class="left">No expiring responders found for the selected date range.</td>
        </tr>
    <?php } ?>
</table>

<!----------end FR2 Adult Pads box------------->

<?php
}?>

<?php include "ssi/footer.php" ; ?>
</body>
</html>