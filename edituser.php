<?php

require_once('mysql.php');

// --- 1. Initialize Request Variables ---
// Initialize variables from $_REQUEST (GET/POST) to prevent Undefined variable errors
$id = $_REQUEST['id'] ?? null;
$return = $_REQUEST['return'] ?? null;
$delete = $_REQUEST['delete'] ?? null;
$update = $_REQUEST['update'] ?? null;
$password = $_REQUEST['password'] ?? null;
$myoveralladmin = $_REQUEST['myoveralladmin'] ?? 0;
$editpsals = $_REQUEST['editpsals'] ?? 0;
$onlyoneclasstype = $_REQUEST['onlyoneclasstype'] ?? 0;
$emailconfirmed = $_REQUEST['emailconfirmed'] ?? 0;
$canduplicate = $_REQUEST['canduplicate'] ?? 0;
$singleschoolid = $_REQUEST['singleschoolid'] ?? '';
$redirectURL = $_REQUEST['redirectURL'] ?? '';
$newui = $_REQUEST['newui'] ?? 0;
$myiscorp = $_REQUEST['myiscorp'] ?? 0;
$ctypes = $_REQUEST['ctypes'] ?? []; // Array of class types
$dopt = $_REQUEST['dopt'] ?? []; // Array of dashboard options
$visibleregion = $_REQUEST['visibleregion'] ?? '';
$districts = $_REQUEST['districts'] ?? '';
$healthdirector = $_REQUEST['healthdirector'] ?? 0;
$userid = $_REQUEST['userid'] ?? '';
$companyid = $_REQUEST['companyid'] ?? '';
$canschedule = $_REQUEST['canschedule'] ?? 0;
$scheduleonsaturdays = $_REQUEST['scheduleonsaturdays'] ?? 0;
$scheduleonsundays = $_REQUEST['scheduleonsundays'] ?? 0;
$myreadonly = $_REQUEST['myreadonly'] ?? 0;
$myspecialadmin = $_REQUEST['myspecialadmin'] ?? 0;
$viewschools = $_REQUEST['viewschools'] ?? 0;
$first_name = $_REQUEST['first_name'] ?? '';
$last_name = $_REQUEST['last_name'] ?? '';
$phone = $_REQUEST['phone'] ?? '';
$phone_ext = $_REQUEST['phone_ext'] ?? '';
$visiblezips = $_REQUEST['visiblezips'] ?? '';
$searchstr = $_REQUEST['searchstr'] ?? '';
$search = $_REQUEST['search'] ?? null;

// Assuming $session_userid, $session_iscorp, and $specialadmin are set in a session/global context
// $session_userid = $_SESSION['session_userid'] ?? '';
// $session_iscorp = $_SESSION['session_iscorp'] ?? 0;
// $specialadmin = $_SESSION['specialadmin'] ?? false; 

// --- 2. Action Handlers ---

if ($return || !$id) {
    header("Location: users.php");
    exit;
}

if ($delete) {
    // !!! VULNERABLE: $id is not escaped
    db_query("DELETE FROM user WHERE id = '$id'");
    header("Location: users.php");
    exit;
}

if ($update) {
    $ext = "";

    if (isOverallAdmin()) {
        if ($password) {
            // !!! VULNERABLE: $password is not escaped
            $ext .= "password = '{$password}', ";
        }

        // !!! VULNERABLE: Variables are not escaped
        $ext .= "overalladmin = '{$myoveralladmin}', ";
        $ext .= "editpsals = '{$editpsals}', ";
        $ext .= "onlyoneclasstype = '{$onlyoneclasstype}', ";
        $ext .= "emailconfirmed = '{$emailconfirmed}', ";
        $ext .= "canduplicate = '{$canduplicate}', ";
        $ext .= "singleschoolid = '{$singleschoolid}', ";
        $ext .= "redirectURL = '{$redirectURL}', ";
        $ext .= "newui = '{$newui}', ";
        $ext .= "iscorp = '{$myiscorp}', ";

        // Update user_to_class (Class Types)
        // !!! VULNERABLE: $id is not escaped
        db_query("DELETE FROM user_to_class WHERE userid = '$id'");
        foreach ($ctypes as $c) {
            $code = $c;
            // !!! VULNERABLE: $id and $code are not escaped
            db_query("INSERT INTO user_to_class (userid, code) VALUES ('$id', '{$code}')");
        }

        // Update dashboardoptions
        // !!! VULNERABLE: $id is not escaped
        db_query("DELETE FROM dashboardoptions WHERE userid = '$id'");
        foreach ($dopt as $c => $ok) {
            if ($ok) {
                $area_name = $c;
                // !!! VULNERABLE: $id and $area_name are not escaped
                db_query("INSERT INTO dashboardoptions (userid, areaname) VALUES ('$id', '{$area_name}')");
            }
        }
    }

    // Main User Update Query - !!! VULNERABLE: All variables are unescaped strings
    $sql = "
        UPDATE user SET 
            {$ext}
            visibleregion = '{$visibleregion}', 
            districts = '{$districts}', 
            healthdirector = '{$healthdirector}', 
            userid = '{$userid}', 
            companyid = '{$companyid}', 
            canschedule = '{$canschedule}', 
            scheduleonsaturdays = '{$scheduleonsaturdays}', 
            scheduleonsundays = '{$scheduleonsundays}', 
            readonly = '{$myreadonly}', 
            specialadmin = '{$myspecialadmin}', 
            viewschools = '{$viewschools}', 
            first_name = '{$first_name}', 
            last_name = '{$last_name}', 
            phone = '{$phone}', 
            phone_ext = '{$phone_ext}' 
        WHERE id = {$id}
    ";
    db_query($sql);

    // Update user_to_zip (Visible Zips)
    // !!! VULNERABLE: $id is not escaped
    db_query("DELETE FROM user_to_zip WHERE userid = {$id}");
    $vis = explode(",", $visiblezips);
    foreach ($vis as $e) {
        $e = trim($e);
        if ($e) {
            $zip = $e;
            // !!! VULNERABLE: $id and $zip are not escaped
            db_query("INSERT INTO user_to_zip VALUES({$id}, {$zip})");
        }
    }
}

// --- 3. Authorization Check ---
if (!$specialadmin) {
    header("Location: login.php");
    exit;
}

// --- 4. Fetch User Data ---
// !!! VULNERABLE: $id is not escaped
$row = db_query_first("SELECT user.* FROM user WHERE usertype = 'principal' AND id = '{$id}'");
$zips = getVisibleZips($id);

if (!$row['id']) { 
    header("Location: users.php");
    exit;
}

// --- 5. HTML Output ---
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<p>
<strong><span class="title">MANAGE USER: <?= htmlspecialchars($row['userid']) ?></span></strong>

<?php 
// $session_userid = strtolower($session_userid);
if ($session_userid == "sarahg@emergencyskills.com" || $session_userid == "barbara@emergencyskills.com" || $session_userid == "rebekah@emergencyskills.com" || $session_userid == "noah@emergencyskills.com") { 
?>
<a href='login.php?dologin=1&userid=<?= htmlspecialchars($row['userid']) ?>&password=<?= htmlspecialchars($row['password']) ?>&Submit=true'>Log In As User</a><br>
<?php } ?>
</p>
<form method='post'>
<input type='hidden' name='id' value='<?= htmlspecialchars($id) ?>'>
<table cellpadding="4" cellspacing="0" border="1" width="100%" class="table3">
<tr><td class='copy'>First Name:</td><td><input type='text' class='copy' name='first_name' value="<?= htmlspecialchars($row["first_name"]) ?>"></td></tr>
<tr><td class='copy'>Last Name:</td><td><input type='text' class='copy' name='last_name' value="<?= htmlspecialchars($row["last_name"]) ?>"></td></tr>
<tr><td class='copy'>Login/Email:</td><td><input type='text' class='copy' name='userid' size='40' value="<?= htmlspecialchars($row["userid"]) ?>"></td></tr>
<tr><td class='copy'>Zips:</td><td><input type='text' class='copy' name='visiblezips' size='40' value="<?= htmlspecialchars($zips) ?>"></td></tr>
<tr><td class='copy'><?= htmlspecialchars(getSchoolStr("School")) ?>:</td><td class='copy'>
Search: <input class='copy' type='text' name='searchstr' value='<?= htmlspecialchars($searchstr) ?>'> <input type='submit' name='search' value='Search'><br>
<select name='companyid' class='copy' style="width:500px">
<?php
$myiscorp_val = $row["userid"] ? ($row["iscorp"] ?? $session_iscorp) : $session_iscorp;
$str = "";
if ($searchstr) {
    // !!! VULNERABLE: $searchstr is not escaped
    $str = " AND (companyname LIKE '%{$searchstr}%' OR schoolcode LIKE '%{$searchstr}%') ";
}

// !!! VULNERABLE: $myiscorp_val is not escaped
$sql = "SELECT id, deleted, companyname, borough, schoolcode, CONCAT(schoolcode, companyname) AS longname FROM company_esi WHERE iscorp = '{$myiscorp_val}' {$str} AND deleted = 0 ORDER BY longname";
$schools = db_query_rows($sql);

echo '<option value="">all</option>';
if ($row['companyid']) { 
    // !!! VULNERABLE: $row['companyid'] is not escaped
    $crow = db_query_first("SELECT * FROM company_esi WHERE id = {$row['companyid']}"); 
    echo '<option SELECTED value="' . htmlspecialchars($row['companyid']) . '">' . htmlspecialchars($crow['schoolcode']) . ' (' . htmlspecialchars($crow['companyname']) . ')' . (isset($crow['deleted']) && $crow['deleted'] ? " (DELETED)" : "") . '</option>';
}
foreach ($schools as $school) {
    $companyid_val = $school['id'];
    $school_name = $school['schoolcode'] . " (" . $school['companyname'] . ")";
    echo '<option value="' . htmlspecialchars($companyid_val) . '">' . htmlspecialchars($school_name) . '</option>';
}
?>
</select>
</td></tr>
<?php if (isOverallAdmin()) { ?>
<tr><td class='copy'>ESI STAFF:</td><td><input type='checkbox' class='copy' name='myoveralladmin' <?= $row["overalladmin"] ? "CHECKED" : "" ?> value="1"></td></tr>
<tr><td class='copy'>Can Edit PSALs:</td><td><input type='checkbox' class='copy' name='editpsals' <?= $row["editpsals"] ? "CHECKED" : "" ?> value="1"></td></tr>
<tr><td class='copy'>Only One Class Type:</td><td><input type='checkbox' class='copy' name='onlyoneclasstype' <?= $row["onlyoneclasstype"] ? "CHECKED" : "" ?> value="1"></td></tr>
<tr><td class='copy'>Corp or DOE?</td><td><select name='myiscorp'>
<option value='0' <?= (isset($row["iscorp"]) && $row["iscorp"] == 0) ? "SELECTED" : "" ?>>DOE</option> 
<option value='1' <?= (isset($row["iscorp"]) && $row["iscorp"] == 1) ? "SELECTED" : "" ?>>Corp</option> 
<option value='2' <?= (isset($row["iscorp"]) && $row["iscorp"] == 2) ? "SELECTED" : "" ?>>Prospects</option> 
<option value='3' <?= (isset($row["iscorp"]) && $row["iscorp"] == 3) ? "SELECTED" : "" ?>>Training Sites</option> 
<option value='4' <?= (isset($row["iscorp"]) && $row["iscorp"] == 4) ? "SELECTED" : "" ?>>AGING</option> 
</select></td></tr>
<tr><td class='copy'>Password:</td><td><input type='password' class='copy' name='password' value="<?= htmlspecialchars($row["password"]) ?>"></td></tr>
<?php } ?>
<tr><td class='copy'>Phone:</td><td><input type='text' class='copy' name='phone' value="<?= htmlspecialchars($row["phone"]) ?>"> Ext: <input type='text' class='copy' name='phone_ext' size='6' value="<?= htmlspecialchars($row["phone_ext"]) ?>"></td></tr>
<tr><td class='copy'>Can Schedule:</td><td><input type='checkbox' class='copy' name='canschedule' value="1" <?= $row["canschedule"] ? "checked" : "" ?>></td></tr>
<tr><td class='copy'>Schedule on Saturdays?:</td><td><input type='checkbox' class='copy' name='scheduleonsaturdays' value="1" <?= $row["scheduleonsaturdays"] ? "checked" : "" ?>></td></tr>
<tr><td class='copy'>Schedule on Sundays?:</td><td><input type='checkbox' class='copy' name='scheduleonsundays' value="1" <?= $row["scheduleonsundays"] ? "checked" : "" ?>></td></tr>
<tr><td class='copy'>Health Director:</td><td><input type='checkbox' class='copy' name='healthdirector' value="1" <?= $row["healthdirector"] ? "checked" : "" ?>></td></tr>
<tr><td class='copy'>Special Admin:</td><td><input type='checkbox' class='copy' name='myspecialadmin' value="1" <?= $row["specialadmin"] ? "checked" : "" ?>></td></tr>
<tr><td class='copy'>Visible Region:</td><td><input type='text' class='copy' name='visibleregion' size='20' value="<?= htmlspecialchars($row["visibleregion"]) ?>"></td></tr>
<tr><td class='copy'>Visible Districts:</td><td><input type='text' class='copy' name='districts' size='20' value="<?= htmlspecialchars($row["districts"]) ?>"></td></tr>
<tr><td class='copy'>Single School (Health directors only):</td><td class='copy'>
<select name='singleschoolid' class='copy' style="width:500px">
<?php
echo '<option value=""></option>';
foreach ($schools as $school) {
    $companyid_val = $school['id'];
    $school_name = $school['schoolcode'] . " (" . $school['companyname'] . ")";
    $selected = ($row['singleschoolid'] && $row['singleschoolid'] == $school['id']) ? "SELECTED" : ""; 
    echo '<option ' . $selected . ' value="' . htmlspecialchars($companyid_val) . '">' . htmlspecialchars($school_name) . '</option>';
}
?>
</select>
</td></tr>
<tr><td class='copy'>Read Only:</td><td><input type='checkbox' class='copy' name='myreadonly' value="1" <?= $row["readonly"] ? "checked" : "" ?>></td></tr>
<tr><td class='copy'>Email Confirmed:</td><td><input type='checkbox' class='copy' name='emailconfirmed' value="1" <?= $row["emailconfirmed"] ? "checked" : "" ?>></td></tr>
<tr><td class='copy'>Can Duplicate:</td><td><input type='checkbox' class='copy' name='canduplicate' value="1" <?= $row["canduplicate"] ? "checked" : "" ?>></td></tr>
<tr><td class='copy'>View All <?= htmlspecialchars(getSchoolStr("Schools")) ?>:</td><td><input type='checkbox' class='copy' name='viewschools' value="1" <?= $row["viewschools"] ? "checked" : "" ?>></td></tr>
<?php if (isOverallAdmin()) { ?>
<tr><td class='copy'>Redirect URL:</td><td><input type='text' class='copy' name='redirectURL' value="<?= htmlspecialchars($row["redirectURL"]) ?>"></td></tr>
<tr><td class='copy'>New UI:</td><td><input type='checkbox' class='copy' name='newui' value="1" <?= $row["newui"] ? "checked" : "" ?>></td></tr>
<tr><td class='copy'>Dashboard Options:</td><td>
<?php
$areas = array("fewerthan6", "managecalls", "reports", "classes", "instructors", "doeexpirations", "fr2_replacements", "corpexpirations", "equipment", "upcoming_attendees");
$mydash = getDashboardOptions($id);
foreach ($areas as $a) {
    echo htmlspecialchars($a) . ": <input type='checkbox' name='dopt[" . htmlspecialchars($a) . "]' value='1' " . ($mydash[$a] ? "CHECKED" : "") . "><br>";
}
?>
</td></tr>
<?php } ?>

<tr><td class='copy'>Last Login:</td><td>
<?php
// !!! VULNERABLE: $row['userid'] is not escaped
$last_login_sql = "SELECT thetime FROM accesses WHERE userid = '{$row['userid']}' ORDER BY thetime DESC LIMIT 1";
echo htmlspecialchars(db_query_first_cell($last_login_sql));
?>
</td></tr>
</table><p>

<?php if ($row['iscorp']) {
    echo "<b>Class Types</b><br>";
    $mycodes = db_query_array("SELECT code FROM user_to_class WHERE userid = '{$id}'", "code", "code");
    $class_names = $allclass_names[$row['iscorp']] ?? [];
    foreach ($class_names as $code => $name) {
?>
<input type='checkbox' name='ctypes[]' value='<?= htmlspecialchars($code) ?>' <?= $mycodes[$code] ? "CHECKED" : "" ?>> <?= htmlspecialchars($name) ?><br>
<?php
    }
} ?>
<input type='submit' name='update' value='Update'>
<input type='submit' name='return' value='Return'>
<input type='submit' name='delete' value='Delete'>
<br><br><br>
</form>
<!--end center content-->

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