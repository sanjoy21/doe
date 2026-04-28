<?php
require_once('mysql.php');

// Initialize variables from request/session
$id = $_REQUEST['id'] ?? null;
$redirect = $_REQUEST['redirect'] ?? null;
$specialadmin = $specialadmin ?? null;

// Cast ID to integer for safety
$id_safe = (int)$id;

// --- Get Equipment Info ---
$equipment_row = [];
if ($id) {
    // Fetch equipment row, safe SQL injection assumed handled by db_query_first function
    $equipment_row = db_query_first("SELECT * FROM equipment WHERE id = {$id_safe}");
}
// Ensure $equipment_row is always an array, even if empty
if (!$equipment_row) {
    $equipment_row = [];
}
?>

<?php
$noleftnav = 1;
include "ssi/top.php"; 
?>
<form method="post">
<input type="hidden" name ="redirect" value="<?php echo htmlspecialchars($redirect ?? 'equipment.php'); ?>">
<input type="hidden" name ="update" value="true">
<input type="hidden" name ="id" value="<?php echo $id_safe; ?>">

<?php
if (!$redirect) {
    $redirect = "equipment.php";
}
?>

<?php if ($specialadmin) { ?>
 <table cellpadding="5" cellspacing="1" border="0" width="100%" class="table3">
 <tr>
 <td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="equipment.php">&laquo; Back to <?php echo getSchoolStr("All Equipment"); ?></a></strong></span></td>              
 </tr>
 </table>
<?php } ?>

 <table cellpadding="5" cellspacing="1" border="0" width="100%" class="table3">
 <tr>
 <td valign="top" bgcolor="#5a179e" colspan="2">
                    <span class="white">
                        <strong>
                            <?php echo getSchoolStr("Equipment"); ?> Information - 
                            <?php echo htmlspecialchars($equipment_row["barcode"] ?? ''); ?> - 
                            <?php echo htmlspecialchars($equipment_row["type"] ?? ''); ?>
                        </strong>
                    </span>
                </td>
 </tr>
 <tr>
 <td valign="top">
 <table cellpadding="5" cellspacing="0" border="1" width="100%" class="table3">
            <tr>
                <th align='left'>Date</th>
                <th align='left'>Person</th>
                <th align='left'>Status</th>
                <th align='left'>Bag</th>
                <th align='left'>Class</th>
            </tr>
<?php 
// --- Fetch and Display Statuses ---
if ($id_safe > 0) {
    // Fetch all status updates for this equipment
    $statuses = db_query_rows("SELECT * FROM equipmentstatus WHERE equipmentid = {$id_safe} ORDER BY statusdate DESC");

    foreach ($statuses as $srow) {
        $class_link = "";
        $classid = $srow['classid'] ?? null;
        if ($classid) { 
            $class_link = "<A href='class_detail.php?id={$classid}' target=_blank>{$classid}</a>"; 
        }
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($srow['statusdate'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($srow['statusupdatedby'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($srow['status'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($srow['bag'] ?? '') . "</td>";
        echo "<td>{$class_link}</td>";
        echo "</tr>";
    }
}
?>
</table></td>
</tr>

 </table>
 <br><br>
 <br><br>
 <?php include "ssi/footer.php"; ?>
 </span>
 </td>
 <td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
 </tr>
</table>
<br><br>
</div>
</form>
</body>
</html>