<?php
// Script to display and submit status checks for AED units.

require_once('mysql.php');

// Assumed external functions:
// function getCompanyRow(int $id): array;
// function getAedRows(int $company_id, string $optional_param1 = '', int $optional_param2 = 0): array;
// function getAEDStatusLink(int $aed_id): string; // Assumed helper function
// function getCompanyName(int $company_id): string; // Assumed helper function

$nologinrequired = true;
$showaedlink = true; // Retained from original context

// --- 1. Input Validation and ID Decryption ---
$raw_id = $_REQUEST['id'] ?? null;
$magic_multiplier = 1234;

// The original logic 'decrypts' the ID by dividing it by 1234.
$id = (int) ($raw_id / $magic_multiplier);

// Security Check: If the result is not an exact integer, redirect.
if ($id <= 0 || (intval($raw_id) / $magic_multiplier) != $id) {
    header("Location: /");
    exit;
}

$save = $_POST['save'] ?? null;
$blinking_status = $_POST['blinking'] ?? []; // Array: [aed_id] => 1
$chirping_status = $_POST['chirping'] ?? []; // Array: [aed_id] => 1

// --- 2. Data Fetching ---
$row = getCompanyRow($id);
$company_name = htmlspecialchars($row['companyname'] ?? 'Unknown Company');

$showname = false;
$aed_rows = [];

if ($row['iscorp'] ?? false) {
    // Corporate/Main entity
    $aed_rows = getAedRows($row['id']);
} else {
    // Campus/Branch entity
    $aed_rows = getAedRows($row['id'], "", $row['campusid'] ?? 0);
    if ($row['campusid'] ?? false) {
        $showname = true; // Indicates the campus name should be shown if available
    }
}

// --- 3. Handle Form Submission (Save Status) ---
if ($save) {
    // NOTE: The original code only collected $blinking and $chirping data 
    // but did not contain the database update logic. 
    // This is where you would iterate and save the status check records.
    
    // Example Save Logic (Requires a function like saveAEDStatusCheck()):
    /*
    $today_date = date("Y-m-d");
    foreach ($aed_rows as $aed) {
        $aed_id = (int) $aed['aedid'];
        
        $is_blinking = isset($blinking_status[$aed_id]) ? 1 : 0;
        $is_chirping = isset($chirping_status[$aed_id]) ? 1 : 0;
        
        // Hypothetical function to save the status log
        // saveAEDStatusCheck($aed_id, $today_date, $is_blinking, $is_chirping);
    }
    header("Location: " . $_SERVER['REQUEST_URI'] . "?status=saved");
    exit;
    */
}

// --- 4. HTML Output ---
include "ssi/top.php";
?>
<form method='post' action="">
    <h3>Manage AED Information for <?=$company_name?></h3><Br>
    <div align="center">
        <table width="95%" border="1" cellpadding=4 cellspacing=0 style="border-collapse: collapse;">
            <tr style="background-color: #e1e1f6;">
                <th>Serial</th>
                <th>Location</th>
                <th>Is green status indicator light blinking?</th>
                <th>Is AED chirping?</th>
            </tr>
            <?php foreach ($aed_rows as $aed) { 
                $aed_id = (int) $aed['aedid'];
                $serial = htmlspecialchars($aed['serial'] ?? 'N/A');
                $location = htmlspecialchars($aed['location'] ?? '');
                
                // Construct status indicators/flags (HTML structure cleanup)
                $indicators = '';
                $indicators .= $aed['outofwarranty'] ? "<strong>W</strong>" : "";
                $indicators .= $aed['isrecall'] ? "<strong>R</strong>" : "";
                $indicators .= $aed['aedstolen'] ? "<strong style='color:red;'>S</strong>" : "";
                
                // Apply color and bolding based on status flags
                $serial_style = '';
                if ($aed['aedmissing']) $serial_style = "color: red;";
                if ($aed['readytoreturn']) $serial_style = "color: purple;";
                if ($aed['outofservice']) $serial_style = "color: green;";
            ?>
            <tr>
                <td> 	 	 	 	 	 	
                    <?=$indicators?>
                    <?php if (!empty($serial_style)) { ?><strong style='<?=$serial_style?>'><?php } ?>
                    <strong><?=$serial?></strong>
                    <?php if (!empty($serial_style)) { ?></strong><?php } ?>
                    
                    <?=$aed['newinstall'] ? "(N)" : "";?>
                    <?=$aed['aedstolen'] ? "<span style='color:red;'>" . htmlspecialchars($aed["aedstolentext"] ?? '') . "</span>" : ""?>
                </td>
                <td><?=$location?></td>
                <td><center>
                    <input type='checkbox' name='blinking[<?=$aed_id?>]' value='1'>
                </center></td>
                <td><center>
                    <input type='checkbox' name='chirping[<?=$aed_id?>]' value='1'>
                </center></td>
            </tr>
            <?php } ?>

        </table>
        <bR>
        Submit this information for <b><?=date("m/d/Y")?></b><br>
        <input type='submit' value='Save Information' name='save'>
    </div>
    <br>
    <br>
    <table border="0"><tr><td>
        <b>Key</b><Br>
        W: Out of Warranty<br>
        R: Recall<br>
        S: Stolen<br>
    </td></tr></table>
</form> 	 	 	 	
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>

<?php if (!($mobile_browser ?? false)) {
    include "ssi/footer.php";
}?>
</span>
</td>
<td valign="top" width="15"><img src="<?=WEB_ROOT?>/images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
<script type="text/javascript" src="webticker_lib.js" language="javascript"></script> 	 	 	 	 	 

</body>
</html>