<?php
// NOTE: Assuming db_query_insert_id() and db_query() execute queries.
// SECURE: An escape function (db_escape_string) is REQUIRED to prevent SQL injection.
// This function must be defined and functional in 'mysql.php'.

$nologinrequired = 1;
require_once "mysql.php";
$nonewschool = 1;

// --- 1. Input Retrieval (Using modern null-coalescing for robustness) ---
$saveandsend = $_POST['saveandsend'] ?? null;
$firstname = $_POST['firstname'] ?? '';
$lastname = $_POST['lastname'] ?? '';
$email = $_POST['email'] ?? '';
$companyid = $_POST['companyid'] ?? null;
// Note: $row is not populated in this script, so the VALUE attributes in the form will be empty unless $row is defined elsewhere.

if ($saveandsend) {

    // --- 2. Security: Manually Escape All User Input ---
    $esc_firstname = db_escape_string($firstname);
    $esc_lastname = db_escape_string($lastname);
    $esc_email = db_escape_string($email);
    $esc_companyid = db_escape_string($companyid);

    // --- 3. Database Operations ---

    // Insert new registrant record
    $id = db_query_insert_id("INSERT INTO free_registrants (dateadded) VALUES (NOW())");

    // Update with mandatory fields (using the escaped variables)
    db_query("
        UPDATE free_registrants 
        SET firstname = '{$esc_firstname}', lastname = '{$esc_lastname}', email = '{$esc_email}' 
        WHERE id = {$id} 
    ");

    // Update with optional school ID
    if (!empty($esc_companyid) && is_numeric($id)) {
        db_query("UPDATE free_registrants SET schoolid = '{$esc_companyid}' WHERE id = {$id}");
    }

    // --- 4. Email Logic ---
    $body = "Thank you for your inquiry on how to complete a CPR/AED training program at no charge through the New York City Department of Education (NYCDOE). Since you are not a full time employee of the NYCDOE, a school official such as Athletic Director or Principal must submit a request in writing to Celeste McGee and Husain Thompson at the NYCDOE for approval. 
<br> <br> 
Your request must meet the following criteria in order to be considered:
<ul><li>Written on School Letterhead</li></ul>
Letter must:
<ul>
<li>Include Name and title or status of person to be trained</li>
<li>Clearly indicate request for individual to complete CPR/AED course</li>
<li>Be signed by school official with their full name and title clearly indicated below the signature</li>
<li>Provide the email address the approval should be sent to</li>
</ul>
<br><br> 
Please send your request to one of the following:<br> 
Celeste McGee: <a href='mailto:CMcGee3@schools.nyc.gov'>CMcGee3@schools.nyc.gov</a> Ph: (718) 391-8566  Fax: (718) 391-8128<br>
Husain Thompson: <a href='mailto:hthomps@schools.nyc.gov'>HThomps@schools.nyc.gov</a> Ph: (718) 391-8227  Fax: (718) 391-8128<br> 
<br>
If approved, an email will be sent to the address indicated with instructions on how to register. You must follow those instructions to complete the registration process. <br>
";

    // Assuming sendFormattedHTMLMail is defined elsewhere
    sendFormattedHTMLMail(
        $email,
        "CERTIFICATION REQUEST APPROVAL PROCESS",
        $body,
        "info@emergencyskills.com",
        "",
        false,
        true
    );

    // Final database update
    if (is_numeric($id)) {
        db_query("UPDATE free_registrants SET sentemail = '1', dateemailsent = NOW() WHERE id = {$id}");
    }

    // --- 5. Redirect ---
    header("Location: cthanks.php");
    exit;
}
?>
<?php include "ssi/top.php"; ?>
<?php include "getschooldropdown_ajax.php"; ?>
<script>
    function checkOk(frm) {
        // Using .trim() for better user experience
        if (frm.firstname.value.trim() === "") {
            alert("First name is required.");
            return false;
        }
        if (frm.lastname.value.trim() === "") {
            alert("Last name is required.");
            return false;
        }
        if (frm.email.value.trim() === "") {
            alert("Email is required.");
            return false;
        }
        // Note: School dropdown check is commented out, preserving original logic
        return true;
    }
</script>
<form method="post" onsubmit="return checkOk(this)">
    <center>
        <h3> Application for complimentary CPR/AED training for Part Time DOE Employees.</h3>
    </center>
    <table cellpadding="5" cellspacing="1" border="0" width="100%" class="table3">
        <tr>
            <td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><strong>Please Enter Your Information</strong></span></td>
        </tr>


        <tr>
            <td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>First Name*:</strong><br><input type="text" size="40" value="<?= htmlspecialchars($firstname) ?>" name="firstname" style="font-size: 10px; font-family: verdana;"></span>
            </td>
        </tr>
        <tr>
            <td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Last Name*:</strong><br><input type="text" size="40" value="<?= htmlspecialchars($lastname) ?>" name="lastname" style="font-size: 10px; font-family: verdana;"></span>
            </td>
        </tr>
        <tr>
            <td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Email*:</strong><br><input type="email" size="40" value="<?= htmlspecialchars($email) ?>" name="email" style="font-size: 10px; font-family: verdana;"></span>
            </td>
        </tr>
        <tr>
            <td valign="top" bgcolor="#E2DFDF">
                <div align="center">
                    <input type="submit" name='saveandsend' value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </div>
                <br><br>
                <br><br>
                <?php include "ssi/footer.php"; ?>
                </span>
                </div>
</form>
</body>

</html>