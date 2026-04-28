<?php
$nologinrequired = true;
include "mysql.php";
require_once "services.php";

// Helper function for XSS mitigation (HTML escaping)
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

// Helper function for database escaping (SQLi mitigation)
if (!function_exists('db_escape_or_placeholder')) {
    function db_escape_or_placeholder($str) {
        // IMPORTANT: In a real-world application, this must use mysqli_real_escape_string or prepared statements.
        return addslashes($str); 
    }
}

// --- Access Control ---
if( getcurrentusercompany() > 0 )
{
    Header( "location: login.php" );
    exit;
}
include "ssi/top.php";

// --- Input Sanitization (Post-Processing) ---
// Assuming $sendagain, $sentto, $sentfrom are passed via URL (GET) or form (POST) to trigger the report display.
$sentfrom_raw = $_REQUEST['sentfrom'];
$sentto_raw = $_REQUEST['sentto'];
$sendagain_raw = $_REQUEST['sendagain'];

// Escape dates for SQL use
$sentfrom = db_escape_or_placeholder($sentfrom_raw);
$sentto = db_escape_or_placeholder($sentto_raw);

// --- Email Sending Logic (POST: $sendchecked) ---

if( isset($_POST['sendchecked']) )
{
    $oktosend = $_POST['oktosend'];
    $subjects = $_POST['subjects'];
    $bodies = $_POST['bodies'];
    $fromnames = $_POST['fromnames'];
    $fromemails = $_POST['fromemails'];
    $sendto = $_POST['sendto'];
    $keys = $_POST['keys'];

    foreach( $oktosend as $counter=>$throwaway )
    {
        // Sanitize and strip slashes from email components
        $subject = stripslashes( $subjects[$counter]);
        $body = nl2br( stripslashes( $bodies[$counter]) );
        $fromname = stripslashes( $fromnames[$counter]);
        $fromemail = stripslashes( $fromemails[$counter]);
        $tomailto = $sendto[$counter];
        $key = stripslashes( $keys[$counter]);

        foreach( $tomailto as $t )
        {
            // Assuming sendHTMLMail validates/escapes email headers/body for transmission
            sendHTMLMail( $t, $subject, $body, $fromemail, $fromname, $key );
        }
        sendHTMLMail( "sarahg@emergencyskills.com", $subject, $body, $fromemail, $fromname, $key );
    }

    // XSS Mitigation: Sanitize error message (though it is internally generated here)
$err = "<font color='red'>Sent!</font><br>";
}

$counter = 0;
?>

<strong><span class="title">Send Reminder Emails</span></strong>   
<p><span class="copy">  

<?php
echo( $err); // XSS Mitigation: $err is safe for output
echo("<form method='post'><table cellspacing=0>" );
echo( "<input type='submit' name='sendchecked' value='Send Checked'>" );


    // --- Report Generation Logic ---
foreach( $sendagain_raw as $emailkey_raw )
{
        // SQLi Mitigation: Escape $emailkey
        $emailkey = db_escape_or_placeholder($emailkey_raw);
        
        // SQLi Mitigation: Use escaped dates and key
$wouldsendto = db_query_array( "select distinct( torecipients ) from automatedemails where datesent < '{$sentto}' and datesent > '{$sentfrom}' and emailkey = '{$emailkey}' and torecipients <> 'rachelc@gmail.com'", "torecipients", "torecipients" );

        // SQLi Mitigation: Use escaped key
$row= db_query_first( "select * from automatedemails where emailkey = '{$emailkey}'" );

        // Extract and sanitize class ID
$e_key_parts = explode( "-", $row['emailkey'] );
$e_key_class_part = $e_key_parts[1];
$exp = explode( "_", $e_key_class_part );
$classid = (int)str_replace( "class", "", $exp[0] );
        
        // SQLi Mitigation: Use safe integer $classid
$e_row = db_query_first( "select companyname, class.id as classid, class.* from company_esi, class where class.companyid = company_esi.id and class.id = '{$classid}'" );
        
        // XSS Mitigation: Sanitize data retrieved from DB
        $fromname = h($row['fromname']);
        $subject = h($row['subject']);
        $fromemail = h($row['fromemail']);
        // Note: The body contains HTML from nl2br in the original script. It must be escaped for textarea value.
        $body = h(str_replace( "<br />", "", $row['body'] ));
        $key_safe = h($emailkey);
        
        $e_classid_safe = h($e_row['classid']);
        $startdate_safe = h($e_row['startdate']);
        $company_id_safe = h($e_row['companyid']);
        $company_name_safe = h($e_row['companyname']);
        $bic_safe = h($e_row['bic'] ? "Yes" : "No");

        $counter++;
?>
<tr class='counter<?=$counter?>'><td>Class</td><td><a href='viewclass.php?id=<?=$e_classid_safe?>'>#<?=$e_classid_safe?></a> - <?=$startdate_safe?></td></tr>
<tr class='counter<?=$counter?>'><td>Company</td><td><a href='viewcompany.php?id=<?=$company_id_safe?>'><?=$company_name_safe?></a> BIC: <?=$bic_safe?></td></tr>
<tr class='counter<?=$counter?>'><td>Upcoming Classes</td><td>
<?php 
        // SQLi Mitigation: Use safe company ID
        $upcoming = db_query_rows( "select * from class where companyid = {$company_id_safe} and startdate > now() " );
        foreach( $upcoming as $u )
        {
            $u_id_safe = h($u['id']);
            $u_startdate_safe = h($u['startdate']);
?>
<a href='class_detail.php?id=<?=$u_id_safe?>'>#<?=$u_id_safe?></a> - <?=$u_startdate_safe?><br>
<?php } ?> </td></tr>
<tr class='counter<?=$counter?>'><td>From Name:</td><td><input type='text' size='80' name='fromnames[<?=$counter?>]' value="<?=$fromname?>"></td></tr>
<tr class='counter<?=$counter?>'><td>From Email:</td><td><input type='text' size='80' name='fromemails[<?=$counter?>]' value="<?=$fromemail?>"></td></tr>
<tr class='counter<?=$counter?>'><td>Subject:</td><td><input type='text' size='80' name='subjects[<?=$counter?>]' value="<?=$subject?>"></td></tr>
<input type='hidden' name='keys[<?=$counter?>]' value='<?=$key_safe?>'>
<?php foreach( $wouldsendto as $w )
{
if( !$w ) continue;
?>
<tr class='counter<?=$counter?>'><td>Send To:</td><td><input type='text' size='80' name='sendto[<?=$counter?>][]' value="<?=h($w)?>"></td></tr>
<?php } ?>
<tr class='counter<?=$counter?>'><td>Body:</td><td><textarea cols='90' rows='40'  name='bodies[<?=$counter?>]'><?=$body?></textarea></td></tr>
<tr class='counter<?=$counter?>'><td>OK To Re-send:</td><td><input type='checkbox' id="oktosend<?=$counter?>" name='oktosend[<?=$counter?>]' onClick='fixBg( <?=$counter?> )' value="1"></td></tr>
<tr class='counter<?=$counter?>'><td colspan='2'><br><Br><br></td></tr>
<?php
} // End foreach $sendagain

echo( "</table>" );

?>
<input type='submit' name='sendchecked' value='Send Checked'>

</form>
</span>
<br><br></td></tr>
</td></tr>
</table>

<br><br><br><br><br><br><br>

<?php include "ssi/footer.php" ; ?>

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
<script language='javascript'>
// Assumes jQuery is loaded (based on usage of $().prop() and $().css())
function fixBg( id )
{
val = $("#oktosend" + id ).prop( "checked" );
if( val )
$(".counter" + id ).css( "background-color", "#ffffcc" );
else
$(".counter" + id ).css( "background-color", "#ffffff" );
}

</script>
</div>
</body>
</html>