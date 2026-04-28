<?php
$nologinrequired = true;
include "mysql.php";

// Safely retrieve external/form variables
// Assuming data comes from POST, but defaulting to global scope if POST isn't used (as in original)
$email = $_POST['email'] ?? ($email ?? null);
$id = $_POST['id'] ?? ($id ?? null); // Assuming $id is the schoolid
$submit = $_POST['submit'] ?? null;
$status = $_POST['status'] ?? null;
$comments = $_POST['comments'] ?? null;
$classid = $_POST['classid'] ?? ($classid ?? null);


// --- Submission Logic ---
if( $submit )
{
    // Safety: Escape user-provided data for SQL
    $safe_email = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $email ?? '');
    $safe_schoolid = (int)($id ?? 0);
    $safe_status = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $status ?? '0');
    $safe_comments = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $comments ?? '');

    $sql = "INSERT INTO oktodrill ( email, schoolid, dateadded, status, comments ) 
            VALUES ( '{$safe_email}', '{$safe_schoolid}', NOW(), '{$safe_status}', '{$safe_comments}' )";
            
    db_query( $sql );
}

include "ssi/top.php";

?>

<form method='post' onSubmit='return checkOK()'>
<input type='hidden' name='classid' value='<?= htmlspecialchars($classid ?? '') ?>'>
<input type='hidden' name='email' value='<?= htmlspecialchars($email ?? '') ?>'>
<input type='hidden' name='id' value='<?= htmlspecialchars($id ?? '') ?>'>

    <?php if( $submit ) { ?>
    Thanks. Your answer has been recorded. 
    <?php } else { ?>
<b>ALIVE!net Code Blue Acknowledgment</b><br>

1. I acknowledge that an ESI representative will be entering our school. 
    <input type='radio' name='status' value='1'> Yes&nbsp;&nbsp;&nbsp;<input type='radio' name='status' value='-1'> No

<br>
<br>
<b>2. Commnts?<br></b>
    <input type='text' size='80' name='comments' value='<?= htmlspecialchars($comments ?? '') ?>'>
<br>
<br>
<i>By submitting I hereby confirm that the information I have given above is true, and that I will comply with the ESI policies and procedures.<br></i>
<br>
<input type='submit' name='submit' value='Submit'>
    <?php } ?>
  </span>
   <br><br></td></tr>

   </td></tr>
  </table>

<script language='javascript'>
    // Assuming jQuery is loaded as the original code uses $()
    function checkOK()
    {
        var radioValue = $("input[name='status']:checked");
        if( radioValue.length === 0 )
        {
            alert( "Question 1 is required." );
            return false;
        }
        return true;
    }
</script>

<br><br><br><br><br><br><br>
<?php include "ssi/footer.php" ; ?>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>