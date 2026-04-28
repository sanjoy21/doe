<?php
// Initialize external variables and include DB connection
require_once('mysql.php');
$specialadmin = $specialadmin ?? false;

// Safely retrieve inputs
$classid = $_GET['classid'] ?? ($_POST['classid'] ?? null);
$update = $_POST['update'] ?? false;
$compare = $_POST['compare'] ?? false;
$parsedtxtf = $_POST['parsedtxtf'] ?? [];
$parsedtxtl = $_POST['parsedtxtl'] ?? [];
$textinemail = $_POST['textinemail'] ?? '';
$err = '';

// --- Access Control ---
// isOverallAdmin() is assumed to be an external function
if (!$specialadmin && !isOverallAdmin()) {
    header("location: login.php");
    exit;
}

// Assumed external functions (getClassRow, getClassEmail, getClassContact, getCompanyRow)
$crow = getClassRow($classid);
$classemail = getClassEmail($crow);
$classcontact = getClassContact($crow);
$company = getCompanyRow($crow["companyid"]);

// --- Update Logic ---
if ($update) {
    // Note: Database update calls are commented out in the original script
    $db_conn = $GLOBALS['link'] ?? null;
    
    foreach ($parsedtxtf as $rid => $val) {
        $rid = intval($rid);
        $val_safe = mysqli_real_escape_string($db_conn, $val);
        $sql = "UPDATE responders_esi SET firstname = '{$val_safe}' WHERE responderid = {$rid}";
        echo htmlspecialchars($sql) . "<br>";
        // db_query($sql); // Original DB call commented out
    }
    
    foreach ($parsedtxtl as $rid => $val) {
        $rid = intval($rid);
        $val_safe = mysqli_real_escape_string($db_conn, $val);
        $sql = "UPDATE responders_esi SET lastname = '{$val_safe}' WHERE responderid = {$rid}";
        echo htmlspecialchars($sql) . "<br>";
        // db_query($sql); // Original DB call commented out
    }
    $err = "<br><br><font color='red'>Updated!</font>";
}

// --- Utility Function: Get Name Part ---
/**
 * Splits a full name string by the first space.
 * @param string $val Full name string.
 * @param int $partnum 1 for the first part (first name), 2 for the remainder (last name/other).
 * @return string The requested part of the name.
 */
function getPart($val, $partnum)
{
    $exp = explode(" ", $val, 2); // Limit split to 2 parts
    if ($partnum == 1) {
        return $exp[0] ?? ''; // Return first part
    } else {
        return $exp[1] ?? ''; // Return second part (the rest)
    }
}

// --- Data Preparation: Fetch and Sort Attendees ---
// Assumed external function get_attendees() and get_attendee()
$tmpattendees = get_attendees($crow["id"], false, true);
$attendees = [];

foreach ($tmpattendees as $arow) {
    $attendee = get_attendee($arow["responderid"]);
    // Use an associative array key for sorting by "lastname,firstname,id"
    $key = ($attendee['lastname'] ?? '') . "," . ($attendee['firstname'] ?? '') . "," . ($arow['responderid'] ?? '');
    $attendees[$key] = $attendee;
}
ksort($attendees);

// --- Comparison Logic: Parse Email Text ---
$parsed = [];
if ($compare) {
    // $textinemail is already safe as we check $_POST['textinemail']
    $textinemail = trim($textinemail);
    $p = explode("\n", $textinemail);
    
    $cnt = 0;
    // The original logic iterated up to the attendee count, which is fine, 
    // but the inner loop is inefficient. We'll improve the parsing.
    
    foreach ($p as $pline) {
        $pline = trim($pline);
        // Look for lines starting with "1.", "2.", "3.", etc.
        if (preg_match('/^(\d+)\.\s*(.*)/', $pline, $matches)) {
            $index = intval($matches[1]);
            $name_value = trim($matches[2]);
            $parsed[$index] = $name_value;
        }
    }
    
}
?>

<?php include "ssi/top.php"; ?> 		
    <p>
        <strong><span class="title">Confirm Names for <a href='class_detail.php?id=<?php echo htmlspecialchars($classid); ?>'>Class #<?php echo htmlspecialchars($classid); ?></a> - <a href='viewcompany.php?id=<?php echo htmlspecialchars($crow["companyid"]); ?>'><?php echo htmlspecialchars($company["companyname"]); ?></a></span></strong>
        <?php echo $err; ?> 	
    <p>
<?php if (!($_POST["doit"] ?? false) && !($_POST["compare"] ?? false)) { ?>
    <form method='post'>
        <input type='hidden' name='classid' value='<?php echo htmlspecialchars($classid); ?>'>
        <input type='hidden' name='doit' value='1'>
        <table>
            <tr><td>Text In Email (e.g., 1. John Doe): </td></tr>
            <tr><td colspan='2'>
                <textarea name='textinemail' rows='20' cols='80'></textarea></td></tr>
            <tr><td></td><td><input type='submit' name='compare' value='Compare'></td></tr>
        </table>
    </form>
<?php } ?>

<?php if (($_POST["compare"] ?? false)) { ?>
    <form method='post'>
        <input type='hidden' name='classid' value='<?php echo htmlspecialchars($classid); ?>'>
        <input type='hidden' name='doit' value='1'>
        <input type='hidden' name='textinemail' value='<?php echo htmlspecialchars($textinemail); ?>'>
        
        <br><br><table border=1 cellpadding=2 cellspacing=0>
        <tr>
            <th>#</th><th>ID</th><th>DB Value</th><th>Email Value</th><th>New First Name</th><th>New Last Name</th>
        </tr>
        <?php 
        $cnt = 0;
        foreach ($attendees as $a) { 
            $cnt++;
            $aname = trim(($a["firstname"] ?? '') . " " . ($a["lastname"] ?? ''));
            $parsed_val = $parsed[$cnt] ?? '';
            
            // Check for name match
            $fnt_color = ($aname !== trim($parsed_val)) ? 'red' : 'black';
            
            // Get parts for the new input fields
            $new_first_name = getPart($parsed_val, 1);
            $new_last_name = getPart($parsed_val, 2);
        ?>
        <tr>
            <td><?php echo $cnt; ?>.</td>
            <td><a href='editresponder.php?responderid=<?php echo htmlspecialchars($a["responderid"]); ?>'><?php echo htmlspecialchars($a["responderid"]); ?></a></td>
            <td><?php echo htmlspecialchars($aname); ?></td>
            <td><font color='<?php echo $fnt_color; ?>'><?php echo htmlspecialchars($parsed_val); ?></font></td>
            <td><input type='text' name="parsedtxtf[<?php echo htmlspecialchars($a["responderid"]); ?>]" value="<?php echo htmlspecialchars($new_first_name); ?>"></td>
            <td><input type='text' name="parsedtxtl[<?php echo htmlspecialchars($a["responderid"]); ?>]" value="<?php echo htmlspecialchars($new_last_name); ?>"></td>
        </tr>
        <?php } ?>
        </table>
        <br><input type='submit' name='update' value='Accept Changes'>
    </form>
<?php } ?>

<br><br>

</div>
</body>
</html>