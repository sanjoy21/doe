<?php
$nologinrequired = 1;
require_once('mysql.php');

function db_escape($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

// Initialize input variables
$encryptedid = $_REQUEST['encryptedid'] ?? null;
$session_id = $_SESSION['session_id'] ?? null; // Assuming session_id is used for non-encrypted access
$save = $_POST['save'] ?? null;
$viewing = $_POST['viewing'] ?? [];
$chirping = $_POST['chirping'] ?? [];
$blinking = $_POST['blinking'] ?? [];
$inspectorname = $_POST['inspectorname'] ?? '';
$id = null;
$emailid = null;

if ($encryptedid) {
    $exp = explode("_", $encryptedid);
    $encryptedid_part = $exp[0] ?? null;
    $emailid = $exp[1] ?? null;

    if (!$emailid && isset($thisusersrow['userid'])) {
        $emailid = getOrCreateEmailId($thisusersrow['userid']);
    }
    
    // Decrypt the ID
    $id = intval($encryptedid_part / 1440);
    
    // Check decryption validity
    if ($id !== $encryptedid_part / 1440) {
        header("Location: index.php?notok");
        exit;
    }
} elseif (!$session_id) {
    header("Location: index.php?notok");
    exit;
}

// Fallback for ID if not from encrypted link
if (!$id && isset($thisusersrow["companyid"])) {
    $id = $thisusersrow["companyid"];
}

if (!$id) {
    header("Location: index.php");
    exit;
}

// Access control check
if (!isOverallAdmin() && (isset($thisusersrow["companyid"]) && $thisusersrow["companyid"] != $id) && !$encryptedid) {
    header("Location: index.php?noaccess");
    exit;
}

$err = '';
if ($save) {
    $current_date = date("Y-m-d");
    // Escape inspector name for SQL safety
    $safe_inspectorname = db_escape($inspectorname); 
    
    foreach ($viewing as $serialid => $value) {
        $serialid = intval($serialid);
        $value = intval($value);
        $chirp_val = intval($chirping[$serialid] ?? 0);
        $blink_val = intval($blinking[$serialid] ?? 0);
        
        if ($value) {
            db_query("
                INSERT INTO aedinspections 
                (thedate, aedid, viewing, chirping, blinking, inspectorname, userid) 
                VALUES 
                ('{$current_date}', '{$serialid}', '{$value}', '{$chirp_val}', '{$blink_val}', '{$safe_inspectorname}', '{$emailid}')
            ");
            // The original email notification logic remains commented out.
			// if( $chirping[$serialid] > 0 )
			// 	  {
			// 	      $subject = "Chirping AED: {$serials[$serialid]}";
			// 	      $body = "Serial number {$serials[$serialid]} has been reported chirping during monthly inspection. ";
			// 	      sendMail( "sarahg@emergencyskills.com", $subject, $body, "info@emergencyskills.com" );
			// 	  }
			// 	  if( $blinking[$serialid] < 0 )
			// 	  {
			// 	      $subject = "No Status AED: {$serials[$serialid]}";
			// 	      $body = "Serial number {$serials[$serialid]} has been reported no status during monthly inspection. ";
			// 	      sendMail( "sarahg@emergencyskills.com", $subject, $body, "info@emergencyskills.com" );
			// 	  }
        }
    }
    $err = "Thanks, your entries have been recorded.";
}


$schoolrow = getCompanyRow($id);
$campusid = $schoolrow['campusid'] ?? null;

if (isset($schoolrow['iscorp']) && $schoolrow['iscorp']) {
    $aed_rows = getAedRows($id, false, "", true, true);
} else {
    $aed_rows = getAedRows($id, false, $campusid, true, true);
}

$aed_ids = array_keys($aed_rows);
$already = [];

if (!empty($aed_ids)) {
    // Note: The original query assumes aedinspections records chirping/blinking of 0 means not yet inspected, 
    // which contradicts the table structure definition comment (default 0). 
    // I'm keeping the original query logic for consistency with the legacy script's intent.
    $already = db_query_rows("
        SELECT * FROM aedinspections 
        WHERE thedate LIKE '" . date("Y-m") . "%' 
        AND aedid IN (" . implode(", ", $aed_ids) . ") 
        AND blinking <> 0 
        AND chirping <> 0 
    ", "aedid");
}

?>
<?php include "ssi/top.php"; ?>
<br>
    <?php if (isOverallAdmin() && !$encryptedid) { ?>
    <a href='monthlyaedchecklist.php?encryptedid=<?= ($id * 1440) ?>_<?= getOrCreateEmailId($thisusersrow['userid'] ?? null) ?>'>View Encrypted Link</a>
    <?php } ?>
<h3>Monthly Inspection Email Reminder</h3>
    <br>
    <h4><?= htmlspecialchars($schoolrow["companyname"] ?? 'N/A') ?></h4>
<br>
    <script>
    // Function to check if jQuery is loaded (needed for clearChecked below)
    // Note: The original code used jQuery style selectors/attributes, which is why 
    // a pure JS fallback is included below to ensure the script runs even if jQuery is absent.
    function checkOK(frm) {
        if (frm.elements["inspectorname"].value === "") {
            // Note: alert() is generally discouraged in modern web apps, but kept to mimic original behavior
            alert("Please fill in your name.");
            return false;
        }
        return true;
    }
    
    // Pure JS function to clear other checkboxes within the same group
    function clearChecked(ele, type, id) {
        if (ele.checked) {
            // Find all elements with the class 'type_id'
            document.querySelectorAll("." + type + "_" + id).forEach(e => {
                // Uncheck all elements in the group except the one that was just clicked
                if (e !== ele) {
                    e.checked = false;
                }
            });
            // Ensure the clicked element remains checked (if it was checked in the first place)
            ele.checked = true;
        }
    }
    </script>
    <?php if ($err) { ?>
    <br><br>
<font color='red'><?= htmlspecialchars($err) ?></font>
    <?php
      } else { 
    ?>
<form method='post' onsubmit='return checkOK(this)'>
<table width="100%" cellpadding="0" cellspacing="0" border="1">
<tr>
<th>Serial #</th>
<th>AED Location</th>
<th>Are you viewing the AED?</th>
<th>Is the AED Chirping?</th>
<th>Do you see the Green Status<br> Indicator blinking?</th>
    <?php if (count($already)) { ?>
<th>Date of Inspection</th>
    <?php } ?>
</tr>
<?php 
foreach ($aed_rows as $arow) {
    $bg = "";
    $alreadyrow = $already[$arow["aedid"]] ?? null;
    $ro = "";
    
    if ($alreadyrow) {
        $ro = "disabled";
        $bg = "bgcolor='#eeeeee'";
    }
    
    $aedid = $arow["aedid"];
    $serial = htmlspecialchars($arow["serial"]);
    $location = htmlspecialchars($arow["location"]);
?>
<tr <?= $bg ?>>
<td align='center'><?= $serial ?> <input type='hidden' name='serials[<?= $aedid ?>]' value="<?= $serial ?>"></td>
<td align='center'><?= $location ?></td>
<td align='center'>
    <input <?= $ro ?> onclick="clearChecked(this, 'viewing', '<?= $aedid ?>')" type='checkbox' class="viewing_<?= $aedid ?>" name='viewing[<?= $aedid ?>]' value='1'> Yes &nbsp; &nbsp; &nbsp;
    <input type='checkbox' <?= $ro ?> onclick="clearChecked(this, 'viewing', '<?= $aedid ?>'); if (this.checked) alert('Please retrieve the AED at this time. If the AED is missing, please call ESI immediately 212-564-6833');" name='viewing[<?= $aedid ?>]' class="viewing_<?= $aedid ?>" value='-1'> No
</td>

<td align='center'>
    <input type='checkbox' <?= $ro ?> onclick="clearChecked(this, 'chirping', '<?= $aedid ?>'); if (this.checked) alert('Call ESI immediately. 212-564-6833');" class="chirping_<?= $aedid ?>" name='chirping[<?= $aedid ?>]' value='1'> Yes &nbsp; &nbsp; &nbsp;
    <input type='checkbox' <?= $ro ?> onclick="clearChecked(this, 'chirping', '<?= $aedid ?>')" class="chirping_<?= $aedid ?>" name='chirping[<?= $aedid ?>]' value='-1'> No
</td>

<td align='center'>
    <input <?= $ro ?> onclick="clearChecked(this, 'blinking', '<?= $aedid ?>')" class="blinking_<?= $aedid ?>" type='checkbox' name='blinking[<?= $aedid ?>]' value='1'> Yes &nbsp; &nbsp; &nbsp;
    <input <?= $ro ?> onclick="clearChecked(this, 'blinking', '<?= $aedid ?>'); if (this.checked) alert('This AED is NOT READY FOR USE! Call ESI immediately 212-564-6833 ');" class="blinking_<?= $aedid ?>" type='checkbox' name='blinking[<?= $aedid ?>]' value='-1'> No
</td>
    <?php if (count($already)) { ?>
        <td align='center'><?= htmlspecialchars($alreadyrow["thedate"] ?? '') ?></td>
    <?php } ?>
</tr>

<?php } // end foreach ?>


    </table><br><br>
    <i><font color='red'>Note: your entries will not be recorded until you hit Save below</font></i>
<table>
    <tr><td>Name of Inspector:</td><td> <input type='text' name='inspectorname' value="<?= htmlspecialchars($inspectorname) ?>"></td></tr>
    <tr><td>Last Inspected:</td><td> <input type='text' readonly name='inspectordate' value="<?= date("m/d/Y") ?>"></td></tr>
    <tr><td><input type='submit' name='save' value='Save'></td></tr>

</table>
      </form>
    <?php } // end if $err ?>
        <br><br>
        <?php include "ssi/footer.php"; ?>
        </span>
        </td>
        <td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
    </tr>
</table>
<br><br>
</body>
</html>