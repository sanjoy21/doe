<?php
// NOTE: Assumed functions like db_query_safe() and db_query_rows_safe() 
// from "mysql.php" are defined and available.
require_once('mysql.php');
require_once('services.php');

// Assume $id (Class ID) is passed via the URL query string (GET)
$class_id = (int) ($_GET['id'] ?? 0);
$err = "";

// --- 1. Handle Form Submission (Update) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phones = $_POST['phones'] ?? [];
    
    // Use prepared statements for secure updates
    foreach ($phones as $pid_str => $phone) {
        $responder_id = (int) $pid_str;
        $safe_phone = trim($phone);
        
        if ($responder_id > 0) {
            $sql = "UPDATE responders_esi SET dayphone = ? WHERE responderid = ?";
            // Assuming db_query_safe handles the prepared statement execution
            db_query($sql, [$safe_phone, $responder_id]);
        }
    }
    
    $err = "<font color='red'>**Updated.**</font><br>";
}

// --- 2. Fetch Attendees ---
// Use prepared statement to fetch the list of attendees for the current class
$sql_select = "
    SELECT 
        rtc.*, 
        r.firstname, 
        r.lastname, 
        r.dayphone 
    FROM responder_to_class rtc
    JOIN responders_esi r ON r.responderid = rtc.responderid 
    WHERE rtc.classid = ? 
    ORDER BY r.lastname, r.firstname, rtc.position
";
// Assuming db_query_rows_safe handles the prepared statement execution
$attendees = db_query_rows($sql_select, [$class_id]);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>ESI: Update Phone Numbers</title>
    <meta name="Keywords" content="">
    <meta name="Description" content=""> 	
	
    <link rel="stylesheet" href="css/style.css">
</head>

<body bgcolor="#ffffff" marginwidth="20" marginheight="20">
<form method="post">
    <span class="copy">
        <strong><span class="title">Update Phone Numbers for Class #<?= htmlspecialchars($class_id) ?></span></strong>
        <p>
        <?= $err ?>

        <table cellpadding="0" cellspacing="4" border="0"> 	 	 	 	 	
        <?php
        foreach ($attendees as $isrow) {
            $responder_id = htmlspecialchars($isrow['responderid'] ?? '');
            $firstname = htmlspecialchars($isrow['firstname'] ?? '');
            $lastname = htmlspecialchars($isrow['lastname'] ?? '');
            $dayphone = htmlspecialchars($isrow['dayphone'] ?? '');
        ?>
        <tr>
            <td><?= $firstname ?> <?= $lastname ?></td>
            <td>
                <input type='text' name='phones[<?= $responder_id ?>]' value="<?= $dayphone ?>">
            </td>
        </tr>
        <?php } ?>
        </table>
        
        <input type="submit" name='update' value='Update'>
        <input type="button" name='button' value='Close Window' onClick='closeWindow()'>

        <script>
        function closeWindow() {
            if (confirm("Are you sure you want to close the window? Any unsaved changes will be lost.")) {
                window.close();
            }
        }
        </script>
    </span>
</form>
</body>
</html>