<?php 
include "mysql.php";
$noleftnav = 1;

// Safely retrieve external variables
$updateme = $_POST['updateme'] ?? null;
$dates = $_POST['dates'] ?? [];
$session_id = $session_id ?? null; // Assumed global session variable
$thisusersrow = $thisusersrow ?? []; // Assumed global user row
$session_iscorp = $session_iscorp ?? 0; // Assumed global session variable
$url_without_subdomain = $GLOBALS['URL_WITHOUT_SUBDOMAIN'] ?? URL_WITHOUT_SUBDOMAIN ?? 'example.com';
$mobile_browser = $mobile_browser ?? false; // Assumed global variable
$web_root = $GLOBALS['WEB_ROOT'] ?? WEB_ROOT ?? '';

$db_link = $GLOBALS['link'] ?? $link;

if( $updateme && is_array($dates) )
{
    // Safety: Escape session_id once
    $safe_session_id = mysqli_real_escape_string($db_link, $session_id);

    foreach( $dates as $d => $val )
    {
        // $d is a Unix timestamp (from the loop below), $val is the activity string
        $dstr = date( "Y-m-d", (int)$d );
        $safe_val = mysqli_real_escape_string($db_link, $val);
        
        // --- Insert the activity record ---
        db_query( "INSERT INTO workingon ( userid, dateadded, datefor, whatdoing ) 
                   VALUES ( '{$safe_session_id}', NOW(), '{$dstr}', '{$safe_val}' )" );
    }
    
    // --- Redirection Logic ---
    $redirectURL = $thisusersrow['redirectURL'] ?? "/home.php";
    
    // Assuming getUrlPrefix() and URL_WITHOUT_SUBDOMAIN are safe globals/functions
    $url_prefix = getUrlPrefix( $session_iscorp );

    header( "Location: http://{$url_prefix}.{$url_without_subdomain}{$redirectURL}" );
    exit;

}

// --- Fetch the latest logged date ---
$safe_session_id = mysqli_real_escape_string($db_link, $session_id);
$maxdate = db_query_first_cell( "SELECT MAX( datefor ) FROM workingon WHERE userid = '{$safe_session_id}'" );

// Initialize maxdate timestamp
if( !$maxdate ) {
    $maxdate_timestamp = strtotime("2013-12-12"); // Default start date if no logs exist
} else {
    $maxdate_timestamp = strtotime( $maxdate );
}

include "ssi/top.php";
?>
<form method='post'>
What were/are you doing these days? 
<table>
<?php 
// Initialize the loop date and string representation
$current_date_timestamp = $maxdate_timestamp;
$today_date_str = date( "Y-m-d" );

// Loop from the day *after* the max logged date until today
while( date( "Y-m-d", $current_date_timestamp ) !== $today_date_str )
{
    // Move to the next day
    $current_date_timestamp = strtotime( "+1 day", $current_date_timestamp ); 
    
    $dtstr = date("l, m/d/Y", $current_date_timestamp ); 
    
    // Use the timestamp as the array key for POST submission
    $input_name = "dates[{$current_date_timestamp}]";

    echo( "<tr><td> {$dtstr}</td><td><select name='{$input_name}'>" );
    
    // Check if it's a weekend
    $is_weekend = (date( "l", $current_date_timestamp ) === "Sunday" || date( "l", $current_date_timestamp ) === "Saturday");
    $we = $is_weekend ? "SELECTED" : "";

    // Output options
?>
<option>Working</option>
<option>Working from home</option>
<option>Vacation/Personal</option>
<option>Out sick</option>
<option>Bereavement</option>
<option <?= $we ?>>Not scheduled to work (ie weekend)</option>
<?php
    echo("</select> </td></tr>" );
}
?>
</table>
<input type='submit' name='updateme' value='Update'>
</form>
</table>
<p>&nbsp;</p>
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
 <?php if( !$mobile_browser )
 include "ssi/footer.php";?>
</span>
</td>
<td valign="top" width="15"><img src="<?= htmlspecialchars($web_root) ?>/images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
<script type="text/javascript" src="webticker_lib.js" language="javascript"></script>

</body>
</html>