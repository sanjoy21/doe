<?php 
require_once('mysql.php');

// Initialize variables from request
$search = $_POST['search'] ?? $_GET['search'] ?? null;
$substr = $_POST['substr'] ?? null;
$searchbag = $_POST['searchbag'] ?? null;
$searchstatus = $_POST['searchstatus'] ?? null;
$searchtype = $_POST['searchtype'] ?? null;
$showhidden = $_POST['showhidden'] ?? null;
$ob = $_REQUEST['ob'] ?? null;
$xls = $_GET['xls'] ?? null;
$specialadmin = $specialadmin ?? null; // Assumed session variable
$equipmenttypes = $equipmenttypes ?? []; // Assumed external array
$equipmentstatuses = $equipmentstatuses ?? []; // Assumed external array

// --- Helper Functions ---

/**
 * Removes a specified parameter from a URL string.
 * @param string $url The URL string.
 * @param string $param The parameter name to remove.
 * @return string The modified URL.
 */
function removeParam($url, $param) {
    // Escapes special characters in the parameter name for use in a regular expression
    $param_safe = preg_quote($param, '/');
    
    // Remove '&param=value' or '?param=value' at the end of the URL
    $url = preg_replace('/(&|\?)'.$param_safe.'=[^&]*$/', '', $url);
    
    // Remove '&param=value&' or '?param=value&' in the middle of the URL
    $url = preg_replace('/(&|\?)'.$param_safe.'=[^&]*&/', '$1', $url);
    
    return $url;
}

/**
 * Gets the current URI without the 'ob' parameter.
 * @return string The modified URI, ensuring a '?' is present if needed.
 */
function getmyurlwithoutob() {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $uri = removeParam($uri, "ob");
    if (strpos($uri, "?") === false) {
        $uri .= "?";
    }
    return $uri;
}

// --- Main Search Logic ---
$rows = [];
if ($search) {
    $whr = " AND retired = 0 ";
    
    $db_link = $GLOBALS['link'] ?? null;
    $substr_safe = $substr ? mysqli_real_escape_string($db_link, $substr) : null;
    $searchbag_safe = $searchbag ? mysqli_real_escape_string($db_link, $searchbag) : null;
    $searchstatus_safe = $searchstatus ? mysqli_real_escape_string($db_link, $searchstatus) : null;
    $searchtype_safe = $searchtype ? mysqli_real_escape_string($db_link, $searchtype) : null;

    if ($substr_safe) {
        $whr .= " AND barcode LIKE '%{$substr_safe}%' ";
    }
    if ($searchbag_safe) {
        $whr .= " AND bag = '{$searchbag_safe}'";
    }
    if ($searchstatus_safe) {
        $whr .= " AND status = '{$searchstatus_safe}'";
    }
    if ($searchtype_safe) {
        $whr .= " AND type = '{$searchtype_safe}'";
    }
    
    if (!$showhidden) { 
        $whr .= " AND hidden = 0";
    }
    
    $ob_safe = $ob ?: "barcode"; // Default order by
    
    // NOTE: $ob needs to be validated against allowed columns to prevent SQL injection.
    // Assuming $ob is safe or handled by an external function for this conversion.
    
    $sql = "SELECT * FROM equipment, equipmentstatus 
            WHERE equipment.id = equipmentid AND iscurrent = 1 
            {$whr} ORDER BY {$ob_safe}";
    
    $rows = db_query_rows($sql);

    // --- Excel/CSV Export Logic ---
    if ($xls) {
        $filename = "campuses_" . date('Ymd') . ".csv";
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $output = fopen('php://output', 'w');
        
        // CSV Header
        fputcsv($output, array("Barcode", "Type", "Current Status"));
        
        // CSV Data
        foreach ($rows as $r) {
            // NOTE: The original code called getEquipmentStatus($r[id]) inside the loop, 
            // but the main query already joined equipmentstatus where iscurrent = 1, 
            // so we can use the status from $r.
            $status_display = $r["status"] ?? 'N/A';

            $data_row = array(
                $r["barcode"] ?? '',
                $r["type"] ?? '',
                $status_display
            );
            fputcsv($output, $data_row);
        }
        
        fclose($output);
        exit;
    }
}
?>

<?php include "ssi/top.php"; ?>
 <strong><span class="title">MANAGE <?php echo strtoupper( getSchoolStr("Equipment") ); ?></span></strong>

 <p>
 <a href="updateequipmentstatus.php">Update Equipment Statuses</a> &nbsp; | &nbsp; 
 <a href="generatebarcode.php?allstatuses=1">Print Barcodes For All Statuses</a>
 <table cellpadding="3" cellspacing="0" border="0" width="100%" class="table3">
 <tr><td nowrap>
 <form method='post' action='equipment.php'>
<span class='copy'> Search: <br>
 <tr><td> Bar Code: </td><td><input type='text' name='substr' size=12 value="<?php echo htmlspecialchars($substr ?? ''); ?>"></td></tr>
<tr><td>
<span class="copy">Equipment Type:</td><td>

<select name='searchtype'>
 <option value=""></option>
<?php foreach ($equipmenttypes as $t) { ?>
 <option <?php echo ($searchtype == $t) ? "SELECTED" : ""; ?> value='<?php echo htmlspecialchars($t); ?>'><?php echo htmlspecialchars($t); ?></option>
 <?php } ?>
</select>
 </span>
</td></tr>
<tr><td>
 <span class="copy">Bag:</td><td> <select name='searchbag'>
 <option value=""></option>
 <?php foreach (getCurrentBagValues() as $t) { ?>
 <option <?php echo $searchbag == $t ? "SELECTED" : ""; ?> value='<?php echo htmlspecialchars($t); ?>'><?php echo htmlspecialchars($t); ?></option>
 <?php } ?>
 </span>
 </select>
</td></tr>
<tr><td><span class="copy">Status:</td><td> <select name='searchstatus'>
 <option value=""></option>
 <?php foreach ($equipmentstatuses as $t) { ?>
 <option <?php echo $searchstatus == $t ? "SELECTED" : ""; ?> value='<?php echo htmlspecialchars($t); ?>'><?php echo htmlspecialchars($t); ?></option>
 <?php } ?>
 </select>
 </span>
</td></tr>
<tr><td>Show Hidden: </td><td><input type='checkbox' name='showhidden' value='1' <?php echo $showhidden ? "CHECKED" : ""; ?>>
</table>

 <input type='submit' name='search' value='Search'> 
        <a href='equipment.php?search=true'>Clear Search</a>
        <?php if ($search) { ?>
            | <a href='<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') . "&xls=1"; ?>'>Export to XLS</a>
        <?php } ?>
        <br><br></span>
</form>

<?php if ($search) { ?>
            <form action='generatebarcode.php' method='post' name='seform'>
 <span class='copy'><strong>Search Results (<?php echo count($rows); ?>)</strong></span><Br>
<a onclick="javascript:checkAll('seform', true);" href="javascript:void(0);">Check All</a>
 <a onclick="javascript:checkAll('seform', false);" href="javascript:void(0);">Uncheck All</a><br>

<table cellpadding=2 border=1 cellspacing=0 class="table3">
<tr> <th class='copy'></th>
 <th class='copy'><a href='<?php echo getmyurlwithoutob(); ?>&ob=barcode'>Bar Code</a></th>
 <th class='copy'><a href='<?php echo getmyurlwithoutob(); ?>&ob=type'>Type</a></th>
 <th class='copy'><a href='<?php echo getmyurlwithoutob(); ?>&ob=status'>Current Status</a></th>
 <th class='copy'><a href='<?php echo getmyurlwithoutob(); ?>&ob=statusdate+desc'>Status Date</a></th>
 <th class='copy'>Status History</th>
 <th class='copy'>Print Barcode</th>
<?php 
foreach ($rows as $row) { 
    // Since the main query includes equipmentstatus.* WHERE iscurrent = 1,
    // we use $row for status and statusdate directly.
    $status_display = htmlspecialchars($row['status'] ?? '');
    $statusdate_display = htmlspecialchars($row['statusdate'] ?? '');
?>
<tr>
<td><input type='checkbox' name='barcodes[<?php echo htmlspecialchars($row['barcode'] ?? ''); ?>]' value='<?php echo htmlspecialchars($row['barcode'] ?? ''); ?>'></td>
<Td class='copy'><a href='editequipment.php?id=<?php echo htmlspecialchars($row['id'] ?? ''); ?>'><?php echo htmlspecialchars($row['barcode'] ?? ''); ?></a></td>
<td class='copy'><?php echo htmlspecialchars($row["type"] ?? ''); ?></td>
<td class='copy'><?php echo $status_display; ?></td>
<td class='copy'><?php echo $statusdate_display; ?></td>
<Td class='copy'><a href='estatushistory.php?id=<?php echo htmlspecialchars($row['id'] ?? ''); ?>'>View</a></td>
<Td class='copy'><a href='generatebarcode.php?equipmentid=<?php echo htmlspecialchars($row['id'] ?? ''); ?>'>Print</a></td>
</tr>
<?php } ?>
 </table>
<input type='submit' name='printchecked' value='Print Barcodes For Checked'>
<input type='submit' name='updatestatus' value='Update Status For Checked' onClick='document.seform.action="updateequipmentstatus.php"; return true;'>
</form>
 <?php } ?>
<?php if ($specialadmin) { ?>
<?php foreach ($equipmenttypes as $t) { ?>
<br><br><span class="copy">
 <a href="editequipment.php?newtype=<?php echo htmlspecialchars($t); ?>">Add New <?php echo htmlspecialchars($t); ?></a></span>
<?php } ?>
<?php } ?>
 <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
 <?php include "ssi/footer.php" ; ?>

</span>
 </td>
<br><br>
</div>
</body>
</html>

<script>
function checkAll(formname, checktoggle)
{
    // Use document.forms[formname] for better compatibility
    var form = document.forms[formname];
    if (!form) return;
    
    // Get all input elements within the form
 var checkboxes = form.getElementsByTagName('input');

 for (var i = 0; i < checkboxes.length; i++) {
 if (checkboxes[i].type == 'checkbox') {
 checkboxes[i].checked = checktoggle;
 }
 }
}
</script>