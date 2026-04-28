<?php
// NOTE: This code implements manual sanitation (db_escape_string) for all user inputs 
// to mitigate SQL Injection. The function db_escape_string() (or similar) 
// and database helper functions (e.g., db_query_rows, sendEmail) 
// MUST be defined and functional in 'mysql.php'.

$nologinrequired = true;
include "mysql.php";

// Define a placeholder for the escaping function, required for security.
if (!function_exists('db_escape_string')) {
    function db_escape_string($str) {
        // !!! REPLACE THIS WITH YOUR ACTUAL ESCAPING FUNCTION !!!
        global $link; // Assuming $link is your mysqli connection object
        // Use an appropriate function if $link is defined
        // return mysqli_real_escape_string($link, (string) $str); 
        return addslashes((string) $str); // Fallback if mysqli is not globally available
    }
}

// Extract variables from POST, providing a default value
$submit = $_POST['submit'] ?? null;
$q1 = $_POST['q1'] ?? null;
$q2 = $_POST['q2'] ?? null;
$q3 = $_POST['q3'] ?? null;
$q4 = $_POST['q4'] ?? null;
$email = $_POST['email'] ?? null;
$classid = $_POST['classid'] ?? null;
$classid = (int)$classid; // Sanitize classid as integer

if ($submit) {
    
    // --- 1. Sanitize User Input for Database Insertion (CRITICAL SECURITY AREA) ---
    $esc_q1 = db_escape_string($q1);
    $esc_q2 = db_escape_string($q2);
    $esc_q3 = db_escape_string($q3);
    $esc_q4 = db_escape_string($q4);
    $esc_email = db_escape_string($email);
    $esc_classid = db_escape_string($classid);

    // SECURE: Insert data into the database using sanitized values
    $sql = "INSERT INTO covidquestionsindividual (q1, q2, q3, q4, dateadded, userid, classid) 
            VALUES ('{$esc_q1}', '{$esc_q2}', '{$esc_q3}', '{$esc_q4}', NOW(), '{$esc_email}', '{$esc_classid}')";
    db_query($sql);

    // --- 2. Email Notification Logic ---
    // Only send email if ANY question was answered 'Yes' (value '1')
    if ($q1 == '1' || $q2 == '1' || $q3 == '1' || $q4 == '1') {
        
        // Define hardcoded recipients (assuming these are constant)
        $recipient_list = "'smushogillen@gmail.com', 'bhbrandt@gmail.com'";
        
        // SECURE: Fetch recipient details
        $recipients = db_query_rows("SELECT * FROM user WHERE usertype = 'trainer' AND (userid IN ({$recipient_list}))");
        
        $email_body = $esc_email . " answered " . 
                      ($q1 == '1' ? "Yes" : "No") . " to Q1, " . 
                      ($q2 == '1' ? "Yes" : "No") . " to Q2, " . 
                      ($q3 == '1' ? "Yes" : "No") . " to Q3, " . 
                      ($q4 == '1' ? "Yes" : "No") . " to Q4";

        // Assuming sendEmail is a safe helper function
        foreach ($recipients as $recipientrow) {
            sendEmail($recipientrow['email'], "Covid Check INDIVIDUAL Finished with YES answers", $email_body);
        }
    }
    
    // Original redirection logic is commented out, so we proceed to display the "Thanks" message.
}

include "ssi/top.php";
?>

<form method='post' onSubmit='return checkOK()'>
<input type='hidden' name='classid' value='<?=htmlspecialchars($classid)?>'>
    <?php if ($submit) { ?>
        <p>Thanks. Your answers have been recorded.</p> 
    <?php } else { ?>
        
        <h2><b>ALIVE!net Covid 19 Class Participant Self Assessment Certification</b></h2>
        <p>The purpose of the Coronavirus Self-Checker is to help you make decisions about going into the ESI corporate office or as a visitor to an ESI client. This system is not intended for the diagnosis or treatment of disease or other conditions, including COVID-19.</p>
        <br>

        <?php if (!$email) { ?>
            <p>Email Address: <input type='text' name='email'></p>
        <?php } ?>

        <p>
            1. <b>Have you experienced any of the Covid-19 symptoms in the last 48 hours?</b><br>
            <i>Click link to CDC website for updated symptoms. <a href='https://www.cdc.gov/coronavirus/2019-ncov/symptoms-testing/symptoms.html' target='_blank'>https://www.cdc.gov/coronavirus/2019-ncov/symptoms-testing/symptoms.html</a></i>
        </p>
        
        <table><tr><td>&nbsp;&nbsp;&nbsp;</td><td>
            <ul>
                <li>Fever or chills</li>
                <li>Cough</li>
                <li>Shortness of breath or difficulty breathing</li>
                <li>Fatigue</li>
                <li>Muscle or body aches</li>
                <li>Headache</li>
                <li>New loss of taste or smell</li>
                <li>Sore throat</li>
                <li>Congestion or runny nose</li>
                <li>Nausea or vomiting</li>
                <li>Diarrhea</li>
            </ul>
        </td></tr></table>
        
        <p>
            <input type='radio' name='q1' value='1' <?= $q1 == '1' ? 'checked' : '' ?>> Yes &nbsp;&nbsp;&nbsp; 
            <input type='radio' name='q1' value='0' <?= $q1 == '0' ? 'checked' : '' ?>> No
        </p>

        <p>
            <b>2. Have you tested positive for Covid-19 in the past 10 days?</b><br>
            <input type='radio' name='q2' value='1' <?= $q2 == '1' ? 'checked' : '' ?>> Yes &nbsp;&nbsp;&nbsp; 
            <input type='radio' name='q2' value='0' <?= $q2 == '0' ? 'checked' : '' ?>> No
        </p>

        <p>
            <b>3. In the last 10 days, did you care for or have close contact with someone diagnosed with COVID-19?</b><br>
            <input type='radio' name='q3' value='1' <?= $q3 == '1' ? 'checked' : '' ?>> Yes &nbsp;&nbsp;&nbsp; 
            <input type='radio' name='q3' value='0' ? 'checked' : '' ?>> No
        </p>

        <p>
            <b>4. Have you traveled to a country in the last 14 days of which the CDC has issued a Level 2 or 3 Travel Health Notice - even if you have not deplaned? </b><br>
            <input type='radio' name='q4' value='1' <?= $q4 == '1' ? 'checked' : '' ?>> Yes &nbsp;&nbsp;&nbsp; 
            <input type='radio' name='q4' value='0' <?= $q4 == '0' ? 'checked' : '' ?>> No
        </p>

        <p>
            <i>By submitting I hereby confirm that the information I have given above is true, and that I will comply with the ESI policies and procedures.</i>
        </p>
        
        <input type='submit' name='submit' value='Submit'>
        
    <?php } ?>
</form>

<script>
    // Ensure this runs only when jQuery is loaded (which is common practice for $())
    function checkOK()
    {
        // Check for required Email if it was displayed (if(!$email))
        if (!$('input[name="email"]').attr('disabled') && $('input[name="email"]').val() === '') {
            alert("Email Address is required.");
            return false;
        }

        var questions = ['q1', 'q2', 'q3', 'q4'];
        var required_alerts = {
            'q1': 'Question 1 is required.',
            'q2': 'Question 2 is required.',
            'q3': 'Question 3 is required.',
            'q4': 'Question 4 is required.'
        };

        for (var i = 0; i < questions.length; i++) {
            var q = questions[i];
            var radioValue = $("input[name='" + q + "']:checked");
            if (radioValue.length === 0) {
                alert(required_alerts[q]);
                return false;
            }
        }
        return true;
    }
</script>

<br><br><br><br><br><br><br>

<!--end center content-->

                    <? include "ssi/footer.php" ; ?>

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