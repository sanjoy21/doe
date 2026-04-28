<?php
require_once('mysql.php');

// Assumed external functions:
// function mergeUsers(int $from_id, int $to_id): void;
// function getCompanyName(int $client_id): string;
// function db_escape(string $s): string; // Assumed modern SQL escape function

// --- 1. Secure Input Retrieval and Merge Action ---
$update = $_REQUEST['update'] ?? null;
$go = $_REQUEST['go'] ?? null;
// Input from checkboxes: $tomerge[from_id] = to_id
$tomerge = $_REQUEST['tomerge'] ?? [];

$result = []; // Final array for display

if ($update && is_array($tomerge)) {
    $already_merged_from = [];
    foreach ($tomerge as $from_id => $to_id) {
        $from_id = (int) $from_id;
        $to_id = (int) $to_id;
        
        // Skip if IDs are invalid or if this 'from' user was already selected to be merged
        if ($from_id <= 0 || $to_id <= 0 || in_array($from_id, $already_merged_from)) {
            continue;
        }
        
        // Assumes mergeUsers() handles necessary updates and deletion of $from_id
        mergeUsers($from_id, $to_id);
        $already_merged_from[] = $from_id;
    }
}

// --- 2. Search Action (Find Possible Duplicates) ---
if ($go) {
    // Step 1: Get all active responders
    $sql = "SELECT firstname, lastname, clientid, responderid, borough FROM responders_esi r WHERE r.deleted = 0 ORDER BY lastname, firstname";
    $tmpresult = db_query_rows($sql); // Fetches all records

    // Step 2: Iterate over all records to check for duplicates
    foreach ((array) $tmpresult as $r) {
        $current_responder_id = (int) ($r['responderid'] ?? 0);
        $client_id = (int) ($r['clientid'] ?? 0);
        
        // Sanitize names for the LIKE search
        $safe_first = db_escape($r['firstname'] ?? '');
        $safe_last = db_escape($r['lastname'] ?? '');

        // Find another responder (potential duplicate)
        $other = db_query_first("
            SELECT * FROM responders_esi 
            WHERE firstname LIKE '%{$safe_first}%' 
            AND lastname LIKE '%{$safe_last}%' 
            AND deleted = 0 
            AND clientid = {$client_id} 
            AND responderid <> {$current_responder_id} 
            LIMIT 1
        ");
        
        // If a duplicate ($other) is found, add the current record ($r) to the display list
        if ($other) {
            $r["other"] = $other;
            $result[] = $r;
        }
    }
}

// --- 3. HTML Output Start ---
?>
<?php include "ssi/top.php"; ?> 	 	 	 	
	 	 	 	 <!--start center content-->
	 	 	 	 
	 	 	 	 <strong><span class="title">POSSIBLE MERGES - RESPONDERS</span></strong> 	 	 	 	
	 	 	 	 <p>
	 	 	 	 
	 	 	 	 
	 	 	 	 <table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3">
	 	 	 	 	 	 <tr height="23" bgcolor="#e1e1f6"><td valign="top"><span class="copy"><strong>Responders:</strong></span></td></tr>
	 	 	 	 	 	 <tr bgcolor="#ffffff"><td valign="bottom"><span class="copy">
	 	 	 	 	 	 
<form method='post' action='possiblemerges.php'>
    <input type='hidden' name='go' value='true'>
    <input type='hidden' name='order' value='order by lastname, firstname'>
    <table cellpadding="3" cellspacing="0" border="0">
        <tr>
            <td valign="middle"><input type='submit' value='Go (Find Duplicates)'></td>
        </tr>
    </table>
</form> 	 	 	 	 	 	 	 	
<?php if (!empty($result)) { ?>
<form method='post' action='possiblemerges.php'>
<input type='hidden' name='update' value='true'>
<table class="table3">
    <tr>
        <th class='copy'>Merge?</th>
        <th class='copy'>Responder (Source)</th>
        <th class='copy'>Client</th>
        <th class='copy'>Borough</th>
        <th class='copy'>Action</th>
    </tr>
    <?php foreach ($result as $row) {
        $other = $row["other"];
        
        // Sanitize output
        $row_id = (int) $row["responderid"];
        $other_id = (int) $other["responderid"];
        $name = htmlspecialchars($row["firstname"] . " " . $row["lastname"]);
        $client_id = (int) $row["clientid"];
        $borough = htmlspecialchars($row["borough"] ?? '');
        $other_name = htmlspecialchars($other["firstname"] . " " . $other["lastname"]);
    ?>
    <tr>
        <td class='copy'>
            <!-- Checkbox: merges $row[responderid] INTO $other[responderid] -->
            <input type='checkbox' name='tomerge[<?=$row_id?>]' value='<?=$other_id?>'>
        </td>
        <td class='copy'>
            <a href='viewresponder.php?responderid=<?=$row_id?>'><?=$name?></a>
        </td>
        <td class='copy'>
            <a href='viewcompany.php?id=<?=$client_id?>'><?=htmlspecialchars(getCompanyName($client_id))?></a>
        </td>
        <td class='copy'><?=$borough?></td>
        <td class='copy'>
            <!-- Single action merge link (using GET, which is unsafe but preserves original intent) -->
            <a 
                onClick='return confirm("Are you sure you want to merge <?=$name?> INTO <?=$other_name?>? Note: <?=$name?> (ID: <?=$row_id?>) will be deleted.");' 
                href='possiblemerges.php?go=true&update=true&tomerge[<?=$row_id?>]=<?=$other_id?>'>
                merge with <?=$other_name?>
            </a>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <td colspan='5'>
            <input type='submit' name='updatechecked' value='Merge Checked (Merge selected INTO their respective suggested duplicates)'>
        </td>
    </tr>
</table>
</form>
<?php } ?>

</td></tr></table> 	 	 	 <!--end center content-->
	 	 	 	 
	 	 	 	 	 	 	 	 	 <?php include "ssi/footer.php"; ?>
	 	 	 	 
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