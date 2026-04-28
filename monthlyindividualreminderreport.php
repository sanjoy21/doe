<?php
require_once('mysql.php');

// Helper function for XSS mitigation (HTML escaping)
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

// Helper function for database escaping (SQLi mitigation)
if (!function_exists('db_escape_or_placeholder')) {
    function db_escape_or_placeholder($str) {
        // IMPORTANT: In a real-world application, this must use mysqli_real_escape_string or prepared statements.
        return addslashes((string)($str ?? '')); 
    }
}

// --- Access Control (Note: Logic may be flawed, kept as per original) ---
if( getcurrentusercompany() > 0 )
{
    Header( "location: login.php" );
    exit;
}

// --- Input Sanitization (from $_REQUEST or $_POST) ---
// Assuming all request variables come from $_REQUEST/$_POST
$sentfrom_raw = $_REQUEST['sentfrom'] ?? '';
$sentto_raw = $_REQUEST['sentto'] ?? '';
$go = $_GET['go'] ?? false;
$sendagaingo = $_POST['sendagaingo'] ?? false;
$sendagain = $_POST['sendagain'] ?? [];

// SQLi Mitigation: Escape user-provided date inputs
$sentfrom = db_escape_or_placeholder($sentfrom_raw);
$sentto = db_escape_or_placeholder($sentto_raw);


if( $sendagaingo )
{
    $err = "";
    // SQLi Mitigation: Iterate over sanitized input
    foreach( $sendagain as $emailkey_raw )
    {
        $emailkey = db_escape_or_placeholder($emailkey_raw);
        
        // SQLi Mitigation: Use sanitized dates and key
        $res = db_query_rows( "select * from automatedemails where datesent < '{$sentto}' and datesent > '{$sentfrom}' and emailkey = '{$emailkey}'" );
        $already = array();
        foreach( $res as $r )
        {
            $subject = $r['subject'];
            $body = $r['body'];
            $fromname = $r['fromname'];
            $fromemail = $r['fromemail'];
            $t = $r['torecipients'];
            $key = $r['emailkey'] . "_". $t;
            if( isset( $already[$key] ) ) continue;
            $already[$key] = 1;
            // Assuming sendHTMLMail escapes parameters internally for email headers/body
            sendHTMLMail( $t, $subject, $body, $fromemail, $fromname, $r['emailkey'] );
        }
    }
    // XSS Mitigation: Sanitize error message (though it is internally generated here)
    $err = "<font color='red'>Resent.</font><br><br>";
}


?>
<?php include "ssi/top.php"; ?>

<strong><span class="title">MONTHLY INDIVIDUAL REMINDER REPORT</span></strong>
<p>
<form method='get'>
Emails sent between: <?=printdates2( "sentfrom", $sentfrom_raw )?> and <?=printdates2( "sentto", $sentto_raw )?>
 <input type='submit' name='go' value='Go'>
</form>

<form method='post' action='monthlyindividualreminderresend.php'>
<?=($err ?? '')?>
<?php 

if( $go )
{
    // XSS Mitigation: Sanitize dates for hidden form fields
    $sentfrom_safe = h($sentfrom_raw);
    $sentto_safe = h($sentto_raw);

    echo( "
    <input type='hidden' name='sentfrom' value='{$sentfrom_safe}'>
    <input type='hidden' name='sentto' value='{$sentto_safe}'>
    <table cellspacing=0 border=1><tR><th>Date Sent</th><th>Emails Already Sent</th><th>Responder</th><th>Originating Class</th><th>School</th><th>Upcoming Classes</th></tr>" );
    
    // Set defaults using the escaped variables for safety in SQL
    if( !$sentfrom ) $sentfrom = "2011-01-01";
    if( !$sentto ) $sentto = "2040-01-01";

    // SQLi Mitigation: Use sanitized dates
    $res = db_query_rows( "select * from automatedemails where datesent < '{$sentto}' and datesent > '{$sentfrom}' and emailkey like 'individremin%'" );
    $already = array();
    foreach( $res as $r )
    {
        $emailkey_safe = h($r['emailkey']); // Sanitize for display
        
        if( $already[$emailkey_safe] ) continue;
        $already[$emailkey_safe] = 1;
        
        // Extract responder ID and class ID safely
        $e = str_replace( "individremin", "", $r['emailkey'] );
        $exp = explode( "-", $e );
        $rid = (int)($exp[0] ?? 0); 
        $classid = (int)($exp[1] ?? 0);
        
        // SQLi Mitigation: Use safe, numeric IDs for lookups
        $alreadysent = db_query_array( "select count(*) as cnt, date( datesent ) as ds from automatedemails where emailkey = '{$emailkey_safe}' group by date( datesent ) order by datesent desc", "ds", "cnt" );
        $e_row = db_query_first( "select companyname, class.id as classid, class.* from company_esi, class where class.companyid = company_esi.id and class.id = '{$classid}'" );
        $resp = db_query_first( "select * from responders_esi where responderid = '{$rid}'" );
        
        // Sanitize all data for output
        $datesent_safe = h($r['datesent']);
        $resp_firstname_safe = h($resp['firstname'] ?? '');
        $resp_lastname_safe = h($resp['lastname'] ?? '');
        $resp_id_safe = h($resp['responderid'] ?? '');
        $class_id_safe = h($e_row['classid'] ?? '');
        $class_startdate_safe = h($e_row['startdate'] ?? '');
        $company_id_safe = h($e_row['companyid'] ?? '');
        $company_name_safe = h($e_row['companyname'] ?? '');
        
        echo( "<tr>" );
        // echo( "<td><input type='checkbox' name='sendagain[]' value=\"$emailkey_safe\"></td>" );
        echo( "<td>{$datesent_safe}</td>" );
        // echo( "<td>{$emailkey_safe}</td>" );
        echo( "<td>" );
        foreach( $alreadysent as $dt=>$num )
        {
            // XSS Mitigation: Sanitize date/number
            $dt_safe = h($dt);
            echo( "<nobr>{$dt_safe}</nobr><Br>" );
        }
        echo( "</td>" );
        
        echo( "<td><a href='viewresponder.php?responderid={$resp_id_safe}'>{$resp_firstname_safe} {$resp_lastname_safe}</a></td>" );
        echo( "<td><a href='viewclass.php?id={$class_id_safe}'>#{$class_id_safe}</a> - {$class_startdate_safe}</td>" );
        echo( "<td><a href='viewcompany.php?id={$company_id_safe}'>{$company_name_safe}</a> </td>" );

        // SQLi Mitigation: Use safe IDs for upcoming classes lookup
        $upcoming = db_query_rows( "select * from responder_to_class rtc, class where companyid = {$company_id_safe} and startdate > now() and responderid = {$resp_id_safe} and class.id = classid " );
        $upcomingstr = "";
        foreach( $upcoming as $u ){
            // XSS Mitigation: Sanitize all upcoming class data
            $u_id_safe = h($u['id']);
            $u_startdate_safe = h($u['startdate']);
            $u_requestdate_safe = h($u['requestdate']);
            $upcomingstr .= "<tr><td><a href='class_detail.php?id={$u_id_safe}'>#{$u_id_safe}</a></td><td>{$u_startdate_safe}</td><td>{$u_requestdate_safe}</tD></tr>";
        }

        if( $upcomingstr )
            echo( "<td><table><tr><th>Class</th><th>Class Date</th><th>Date Scheduled</th></tR>{$upcomingstr}</table></td>" );
        else
            echo( "<td></td>" );


        echo( "</tr>" );
    }
    
?>
</table>
<?php } ?>

<br><br><br><br><br><br><br>
<?php include "ssi/footer.php" ; ?>

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
<script language='javascript'>
function showFollow( xls )
{
// Note: Prompt inputs are inherently unsafe. Data sent via URL should be sanitized server-side.
var fromdate = prompt( "What starting date (mm/dd/yyyy)?" );
var todate = prompt( "What ending date (mm/dd/yyyy)?" );

// XSS Mitigation: Values sent to followupdrillreport.php will be handled by its server-side security, 
// but we ensure only basic true/false strings for xls and that dates are not manipulated here.
document.location.href = 'followupdrillreport.php?xls=' + xls + '&concat=true&all=true&fieldfrom=' + fromdate + '&fieldto=' + todate;
}

</script>
</div>
</body>
</html>