<?php
include "mysql.php";

// --- 1. Safely Retrieve Form Data and Session Variables ---
$submit = $_POST['submit'] ?? null;
$q1 = $_POST['q1'] ?? 0;
$q2 = $_POST['q2'] ?? 0;
// Q3, Q4, Q5 are missing from the form but present in the database insert
$q3 = $_POST['q3'] ?? 0;
$q4 = $_POST['q4'] ?? 0;
$q5 = $_POST['q5'] ?? 0;

$classid = $_POST['classid'] ?? null;
$forlogin = $_POST['forlogin'] ?? null;
// $session_userid = $session_userid ?? null; // Assume user ID is set in the session
$session_iscorp = $session_iscorp ?? 0; // Assume iscorp flag is set
$thisusersrow = $thisusersrow ?? []; // Assume user's row data is available


// --- 2. Submission Logic ---
if( $submit )
{
    // Ensure data is safe for query
    $q1_safe = (int)$q1;
    $q2_safe = (int)$q2;
    $q3_safe = (int)$q3;
    $q4_safe = (int)$q4;
    $q5_safe = (int)$q5;
    $session_userid_safe = mysqli_real_escape_string($GLOBALS['link'], $session_userid);
    $classid_safe = mysqli_real_escape_string($GLOBALS['link'], $classid);

    // Insert into database (Note: q3, q4, q5 will be 0 as they aren't in the form)
    $sql_insert = "INSERT INTO covidquestions ( q1, q2, q3, q4, q5, dateadded, userid, classid ) 
                   VALUES ( '{$q1_safe}', '{$q2_safe}', '{$q3_safe}', '{$q4_safe}', '{$q5_safe}', NOW(), '{$session_userid_safe}', '{$classid_safe}' )";
    db_query( $sql_insert );

    // Send Text Alert if Q1 or Q2 is "Yes"
    if( $q1_safe || $q2_safe )
    {
        // Select specific managers (Barbara and Sarah)
        $sql_recipients = "SELECT * FROM user 
                           WHERE usertype = 'trainer' 
                           AND (userid = 'smushogillen@gmail.com' OR userid = 'bhbrandt@gmail.com')";
        $recipients = db_query_rows( $sql_recipients );
        
        $user_id_text_safe = htmlspecialchars($session_userid);
        
        // Craft text body to reflect only the questions asked
        $text_body = "Covid Check: {$user_id_text_safe} answered Q1: " . ($q1_safe ? "Yes" : "No") . " and Q2: " . ($q2_safe ? "Yes" : "No") . ".";

        foreach( $recipients as $recipientrow )
        {
            // Assumes sendText function is defined elsewhere
            sendText( "Covid Check Alert", $text_body, $recipientrow );
        }
    }

    // Handle Redirection
    if( $forlogin )
    {
        if( $_SESSION["goafter"] ?? null )
        {
            $redirectURL = $_SESSION["goafter"];
            header( "Location: {$redirectURL}" );
        }
        else
        {
            $redirectURL = ($thisusersrow['redirectURL'] ?? null) ? $thisusersrow['redirectURL'] : "/home.php";
            // Assumes getUrlPrefix and URL_WITHOUT_SUBDOMAIN are defined
            header( "Location: http://" . getUrlPrefix( $session_iscorp ) . "." . URL_WITHOUT_SUBDOMAIN . "{$redirectURL}" );
        }
    }
    // If not $forlogin, the user remains on the page to see the confirmation message.
}

// --- 3. HTML Form Display ---
include "ssi/top.php";
?>

<form method='post' onSubmit='return checkOK()'>
<input type='hidden' name='classid' value='<?php echo htmlspecialchars($classid); ?>'>
    <?php if( $submit ) { ?>
        Thanks. Your answers have been recorded. 
    <?php } else { ?>
<b>Alive!net Covid 19 Employee Return to Work Self Certification</b><br>
The purpose of the Coronavirus Self-Checker is to help you make decisions about going into the ESI corporate office or as a visitor to an ESI client. This system is not intended for the diagnosis or treatment of disease or other conditions, including COVID-19.<br>
<br>

<font color='red'>If you answer yes to either question, please call your supervisor immediately.<br></font>
    1. <b>Have you experienced any of the Covid-19 symptoms in the last 48 hours?</b><br>
    <i>Click link to CDC website for updated symptoms. <a href='https://www.cdc.gov/coronavirus/2019-ncov/symptoms-testing/symptoms.html'>https://www.cdc.gov/coronavirus/2019-ncov/symptoms-testing/symptoms.html</a></i><br>
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
    <li>Diarrhea</li></ul>
    </td></tr></table>
        <input type='radio' name='q1' value='1'> Yes&nbsp;&nbsp;&nbsp; &nbsp;
        <input type='radio' name='q1' value='0'> No

<br>
<br>
<b> 2. Have you tested positive for Covid-19 in the past 5 days? You are required to isolate for 5 days and to wear a high quality mask for 5 additional days. Send completed affirmation of isolation to <a href='mailto:invoice@emergencyskills.com'>invoice@emergencyskills.com</a> if you are requesting sick days.  <br></b>
    <input type='radio' name='q2' value='1'> Yes &nbsp;&nbsp;&nbsp;
    <input type='radio' name='q2' value='0'> No
<br>
<br>
<br>
<i>By submitting I hereby confirm that the information I have given above is true, and that I will comply with the ESI policies and procedures.<br></i>
<br>
<input type='submit' name='submit' value='Submit'>

</b>
<br>
    <?php } ?>
</span>
    <br><br></td></tr>

     </td></tr>
    </table>

<script>
    function checkOK()
    {
        // Use document.querySelector or jQuery ($) if available in ssi/top.php
        var radioValue1 = document.querySelector("input[name='q1']:checked");
        if( !radioValue1 )
        {
            alert( "Question 1 is required." );
            return false;
        }
        var radioValue2 = document.querySelector("input[name='q2']:checked");
        if( !radioValue2 )
        {
            alert( "Question 2 is required." );
            return false;
        }
        return true;
    }
</script>

<br><br><br><br><br><br><br>

 <?php include "ssi/footer.php" ; ?>
</span>
 </td>
 <td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
 </tr>
</table>
<br><br>
</div>
</body>
</html>