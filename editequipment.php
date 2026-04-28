<?php
include "mysql.php";

function db_escape($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

// Initialize variables
$delete = $_POST['delete'] ?? $_GET['delete'] ?? null;
$update = $_POST['update'] ?? $_GET['update'] ?? null;
// $id = $_POST['id'] ?? $_GET['id'] ?? null;
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '';
// $specialadmin = $_POST['specialadmin'] ?? $_GET['specialadmin'] ?? null;
$readonly = $_POST['readonly'] ?? $_GET['readonly'] ?? null;
$equipmenttypes = isset($equipmenttypes) ? $equipmenttypes : [];

// Form field variables
$barcode = $_POST['barcode'] ?? '';
$type = $_POST['type'] ?? '';
$hidden = $_POST['hidden'] ?? '';
$newtype = $_POST['newtype'] ?? '';

if ($delete && $id) {
    $id = (int)$id;
    db_query("UPDATE equipment SET retired = 1 WHERE id = $id");
    db_query("UPDATE equipmentstatus SET iscurrent = 0 WHERE equipmentid = $id");
    
    if (!$redirect) {
        $redirect = "equipment.php";
    }
    header("location: $redirect");
    exit;
}

if ($update) {
    // Escape input values
    $barcodeEscaped = db_escape($barcode);
    $typeEscaped = db_escape($type);
    $hiddenEscaped = db_escape($hidden);
    
    if ($id) {
        $id = (int)$id;
        db_query("UPDATE equipment SET barcode = '$barcodeEscaped', type = '$typeEscaped', hidden = '$hiddenEscaped' WHERE id = $id");
    } else {
        $equipmentid = db_query_insert_id("INSERT INTO equipment (barcode, type) VALUES ('$barcodeEscaped', '$typeEscaped')");
        
        // Check if addEquipmentStatus function exists
        if (function_exists('addEquipmentStatus')) {
            addEquipmentStatus($equipmentid, "Created");
        }
    }
    
    if (!$redirect) {
        $redirect = "equipment.php";
    }
    header("location: $redirect");
    exit;
}

// Get info for the form
$equipment_row = [];
if ($id) {
    $id = (int)$id;
    $equipment_row = db_query_first("SELECT * FROM equipment WHERE id = $id");
    
    if (!$equipment_row) {
        $equipment_row = [];
    }
}

if (!$redirect) {
    $redirect = "equipment.php";
}

$noleftnav = 1;
include "ssi/top.php";
?>

<!--start center content-->
<form method="post">
<input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect ?? ''); ?>">
<input type="hidden" name="update" value="true">
<input type="hidden" name="id" value="<?php echo htmlspecialchars($id ?? ''); ?>">

<script LANGUAGE="JavaScript">
<!--
function confirmDelete()
{
    var agree = confirm("Are you sure you wish to delete?");
    if (agree) {
        return true;
    } else {
        return false;
    }
}
// -->
</script>

</head>

<script LANGUAGE="JavaScript1.1" SRC="/FormChek.js"></script>
<script language="JavaScript">
<!--
function validateUSPersonalInfo(form)
{ 
    return true;
}
//-->
</script>

<script language="JavaScript">

function validRequired(formField, fieldLabel)
{
    var result = true;
    
    if (formField.value == "") {
        alert('Please enter a value for the "' + fieldLabel + '" field.');
        formField.focus();
        result = false;
    }
    
    return result;
}

function allDigits(str)
{
    return inValidCharSet(str, "0123456789");
}

function inValidCharSet(str, charset)
{
    var result = true;
    
    for (var i = 0; i < str.length; i++) {
        if (charset.indexOf(str.substr(i, 1)) < 0) {
            result = false;
            break;
        }
    }
    
    return result;
}

function isValidShortDate(formField, fieldLabel, required)
{
    if (required && (formField.value.length > 7)) {
        alert('Please enter a date in the format MM/YYYY for the "' + fieldLabel + '" field.');
        formField.focus();
        return false;
    }
    
    var result = true;
    var formValue = formField.value;

    if (required && !validRequired(formField, fieldLabel)) {
        result = false;
    }
  
    if (result && (formField.value.length > 0)) {
        var elems = formValue.split("/");
        
        result = (elems.length == 2); // should be two components
        var expired = false;
        
        if (result) {
            var month = parseInt(elems[0], 10);
            var year = parseInt(elems[1], 10);
            
            if (elems[1].length == 2) {
                year += 2000;
            }
            
            var now = new Date();
            
            var nowMonth = now.getMonth() + 1;
            var nowYear = now.getFullYear();
            
            result = allDigits(elems[0]) && (month > 0) && (month < 13) &&
                     allDigits(elems[1]) && ((elems[1].length == 2) || (elems[1].length == 4));
        }
        
        if (!result) {
            alert('Please enter a date in the format MM/YYYY for the "' + fieldLabel + '" field.');
            formField.focus();
        }
    } 
    
    return result;
}
</script>

<?php if (isset($specialadmin) && $specialadmin) { ?>
    <table cellpadding="5" cellspacing="1" border="0" width="100%" class="table3">
        <tr>
            <td valign="top" align="right" bgcolor="#ffffff" colspan="2">
                <span class="small">
                    <strong><a href="equipment.php">&laquo; Back to <?php echo htmlspecialchars(getSchoolStr("All Equipment") ?? 'All Equipment'); ?></a></strong>
                </span>
            </td>
        </tr>
    </table>
<?php } ?>

<table cellpadding="5" cellspacing="1" border="0" width="100%" class="table3">
    <tr>
        <td valign="top" bgcolor="#5a179e" colspan="2">
            <span class="white">
                <strong><?php echo htmlspecialchars(getSchoolStr("Equipment") ?? 'Equipment'); ?> Information</strong>
            </span>
        </td>
    </tr>
    
    <tr>
        <td valign="top" bgcolor="#E2DFDF">
            <span class="copy">
                <strong><?php echo htmlspecialchars(getSchoolStr("Equipment") ?? 'Equipment'); ?> Barcode*:</strong><br>
                <input type="text" size="40" value="<?php echo htmlspecialchars($equipment_row['barcode'] ?? ''); ?>" name="barcode" style="font-size: 10px; font-family: verdana;">
            </span>
        </td>
    </tr>
    
    <tr>
        <td valign="top" bgcolor="#E2DFDF">
            <span class="copy">
                <strong>Type*:</strong><br>
                <select name='type'>
                    <option value=""></option>
                    <?php 
                    if (is_array($equipmenttypes)) {
                        foreach ($equipmenttypes as $t) {
                            $selected = '';
                            if (isset($equipment_row['type']) && $equipment_row['type'] == $t) {
                                $selected = 'SELECTED';
                            } elseif (!$id && isset($newtype) && $newtype == $t) {
                                $selected = 'SELECTED';
                            }
                            echo "<option value='" . htmlspecialchars($t) . "' $selected>" . htmlspecialchars($t) . "</option>";
                        }
                    }
                    ?>
                </select>
            </span>
        </td>
    </tr>

    <tr>
        <td valign="top" bgcolor="#E2DFDF">
            <span class="copy">
                <strong>Hidden?:</strong><br>
                <input type='checkbox' name='hidden' value='1' <?php echo (isset($equipment_row['hidden']) && $equipment_row['hidden']) ? 'CHECKED' : ''; ?>>
            </span>
        </td>
    </tr>

    <tr>
        <td valign="top" bgcolor="#FFFFFF" colspan="2">
            <br>
            <?php if (!isset($readonly) || !$readonly) { ?>
                <div align="center">
                    <input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php if ($id) { ?>
                        <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
                    <?php } ?>
                </div>
            <?php } ?>
        </td>
    </tr>    
</table>
<br><br>
<br><br>
<?php include "ssi/footer.php"; ?>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</form>
</body>
</html>