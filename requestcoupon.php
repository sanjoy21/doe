<?php 
$nologinrequired = 1;
include "mysql.php";

// Safely retrieve and sanitize POST variables
$request = $_POST['request'] ?? null;
$firstname = $_POST['firstname'] ?? null;
$lastname = $_POST['lastname'] ?? null;
$email = $_POST['email'] ?? null;
$phone = $_POST['phone'] ?? null;
$club = $_POST['club'] ?? null;
$requestedurl = $_POST['requestedurl'] ?? null;
$gm = $_POST['gm'] ?? null;
$filenumber = $_POST['filenumber'] ?? null;

// --- Main Registration and Email Logic ---
if( $request )
{
    // 1. Insert initial record and get the ID
    $sql_insert = "INSERT INTO tsi_registrants ( dateadded ) VALUES ( NOW() )";
    $id = db_query_insert_id( $sql_insert );
    
    if ($id) {
        // Safety: Escape user-provided strings for SQL UPDATE
        $safe_firstname = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $firstname ?? '');
        $safe_lastname = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $lastname ?? '');
        $safe_email = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $email ?? '');
        $safe_phone = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $phone ?? '');
        $safe_club = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $club ?? '');
        $safe_requestedurl = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $requestedurl ?? '');
        $safe_gm = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $gm ?? '');
        $safe_filenumber = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $filenumber ?? '');
        
        // 2. Update record with user data
        $sql_update = "UPDATE tsi_registrants 
                       SET 
                           firstname = '{$safe_firstname}', 
                           lastname = '{$safe_lastname}', 
                           email = '{$safe_email}', 
                           phone = '{$safe_phone}', 
                           club = '{$safe_club}', 
                           requestedurl = '{$safe_requestedurl}', 
                           gm = '{$safe_gm}', 
                           filenumber = '{$safe_filenumber}' 
                       WHERE id = {$id}";
        db_query( $sql_update );
        
        $body = "Thank you for your inquiry on how to complete a CPR/AED training program at no charge through TSI. <br><br>

If approved, an email will be sent to the address indicated with instructions on how to register. You must follow those instructions to complete the registration process. <br>
";
        sendFormattedHTMLMail( $email, "CERTIFICATION REQUEST APPROVAL PROCESS" , $body, "info@emergencyskills.com", "", false, true );
        
        $notification_body = "Below is a copy of the email sent to the person who requested access. ({$email}) <br><br>" . $body;
        sendFormattedHTMLMail( "TSILearningandDevelopment@tsiclubs.com", "CERTIFICATION REQUEST APPROVAL PROCESS - NEW REQUEST" , $notification_body, "info@emergencyskills.com", "", false, true );
        // sendFormattedHTMLMail( "rachelc@gmail.com", "CERTIFICATION REQUEST APPROVAL PROCESS - NEW REQUEST" , "Below is a copy of the email sent to the person who requested access. ($email) <br><br>" . $body, "info@emergencyskills.com", "", false, true );
        
        db_query( "UPDATE tsi_registrants SET sentemail = '1', dateemailsent = NOW() WHERE id = {$id}" );
    }
}

?>
<?php include "ssi/top.php"; ?>

<div align="center">
Thank you for your inquiry on how to complete a CPR/AED training program at no charge through TSI. If approved, an email will be sent to the address indicated with instructions on how to register. <br>

<br><br>
<b>YOU ARE NOT REGISTERED FOR A CLASS</b> until you follow those instructions to complete the registration process.

</div>
<br><br>
<br><br>
<?php include "ssi/footer.php"; ?>
</span>
</div>
</form> 
</body>
</html>