<?php
// 465-3637
require_once('mysql.php');

function db_escape($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}
function db_escape_like($value) {
    // Escape LIKE wildcards
    $value = db_escape($value);
    $value = str_replace(['%', '_'], ['\%', '\_'], $value);
    return $value;
}

// // Security: Validate and sanitize inputs
// $update = isset($_POST['update']) ? true : false;
// $delete = isset($_POST['delete']) ? true : false;
// $del = isset($_GET['del']) ? intval($_GET['del']) : 0;
// $search = isset($_POST['search']) ? true : false;
// $lname = isset($_POST['lname']) ? trim($_POST['lname']) : '';
// $region = isset($_POST['region']) ? trim($_POST['region']) : '';
// $oaonly = isset($_POST['oaonly']) ? intval($_POST['oaonly']) : 0;
// $ob = isset($_POST['ob']) ? trim($_POST['ob']) : 'last_name, first_name';
// $specialadmin = $specialadmin ?? false;
// $session_iscorp = $session_iscorp ?? '';
// $session_userid = $session_userid ?? '';
// $overalladmin = $overalladmin ?? false;

// Initialize variables
$extra = '';
$eq = '';
$trainers = [];

// Process form submissions
if($update) {
    foreach($_POST['us'] as $u) {
        $u = intval($u);
        if(isset($_POST['canupd'][$u])) {
            $canupd_value = intval($_POST['canupd'][$u]);
            db_query("UPDATE user SET canschedule = " . db_escape($canupd_value) . " WHERE id = " . $u);
        }
    }
}

if($delete) {
    foreach($_POST['todel'] as $u => $throwaway) {
        $u = intval($u);
        db_query("DELETE FROM user WHERE id = " . $u);
    }
}

if($del) {
    $del = intval($del);
    db_query("DELETE FROM user WHERE id = " . $del);
}

// Authorization check
if(!$specialadmin) {
    header("Location: login.php");
    exit;
}

// Build search filters
if($lname) {
    $safe_lname = db_escape_like($lname);
    $extra .= " AND (last_name LIKE '%" . $safe_lname . "%' OR first_name LIKE '%" . $safe_lname . "%' OR userid LIKE '%" . $safe_lname . "%')";
    $eq .= "&lname=" . urlencode($lname);
}

if($region) {
    $safe_region = db_escape($region);
    $extra .= " AND (region = '" . $safe_region . "')";
    $eq .= "&region=" . urlencode($region);
}

if($oaonly) {
    $extra .= " AND overalladmin = 1";
    $eq .= "&oaonly=1";
}

if($search || $ob) {
    $eq .= "&ob=" . urlencode($ob);
    $new_ob = ($ob === 'companyname, last_name, first_name') ? 'companyname, last_name, first_name' : 'last_name, first_name';
    
    $query = "SELECT user.* FROM user LEFT JOIN company_esi ON companyid = company_esi.id 
              WHERE usertype = 'principal' AND user.iscorp = '" . db_escape($session_iscorp) . "' 
              $extra ORDER BY " . $new_ob;
    
    $trainers = db_query_rows($query);
}
?>
<?php include "ssi/top.php"; ?>
        <p>
            <strong><span class="title">MANAGE USERS</span></strong>
        <p>
<!--start center content-->
        <form method='post'>
        <span class='copy'>Search: <input type='text' name='lname' class='copy' value="<?php echo htmlspecialchars($lname); ?>"> 
<?php if($overalladmin) { ?>
ESI Staff only: <input type='checkbox' name='oaonly' value='1' <?php echo $oaonly ? "CHECKED" : ""; ?>>
<?php } ?>
Sort By: <select name='ob'>
    <option value='last_name, first_name' <?php echo ($ob === 'last_name, first_name') ? "SELECTED" : ""; ?>>Last Name</option>
    <option value='companyname, last_name, first_name' <?php echo ($ob === 'companyname, last_name, first_name') ? "SELECTED" : ""; ?>>School</option>
</select>
<input class='copy' type='submit' name='search' value='Search'><br><br>
Region: <input type='text' name='region' class='region' size='2' value="<?php echo htmlspecialchars($region); ?>"> &nbsp;&nbsp;
<i>Note: pink is deleted! yellow is unconfirmed!</i><br><br>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3">
    <tr bgcolor="#e1e1f6">
        <th class='copy'>Name</th>
        <th class='copy'>Password</th>
        <th class='copy'>Login</th>
        <th class='copy'><?php echo getSchoolStr("School"); ?></th>
        <th class='copy'>Can Schedule</th>
        <?php if($session_userid == "sarahg@emergencyskills.com") { ?>
        <th class='copy'>Delete</th>
        <?php } ?>
    </tr>

<?php
foreach($trainers as $t) {
    $crow = getCompanyRow($t['companyid']);
    echo "<input type='hidden' name='us[]' value='" . intval($t['id']) . "'>";
    $bgc = "#FFFFFF";
    if($crow['deleted']) $bgc = "#ffcccc";
    if(!$t['emailconfirmed']) $bgc = '#ffffad';
    
    echo "<tr bgcolor='$bgc'>
            <td class='copy' valign='top'>
                <a href='edituser.php?id=" . intval($t['id']) . "'>" . htmlspecialchars($t['first_name'] . " " . $t['last_name']) . "</a>
            </td>";
    
    if(($session_userid == "sarahg@emergencyskills.com") && !$t['overalladmin']) { 
        echo "<td valign='top' class='copy'>" . 
        // htmlspecialchars($t['password'] ?? '') . 
        "<a href='users.php?del=" . intval($t['id']) . $eq . "'>delete</a>
              </td>";
    } else {
        echo "<td>&nbsp;</td>";
    }
    
    echo "<td valign='top' class='copy'>" . htmlspecialchars($t['userid'] ?? '') . "</td>
          <td class='copy'>
            <a href='editcompany.php?id=" . intval($t['companyid']) . "'>" . htmlspecialchars(getCompanyName($t['companyid'])) . "</a>
          </td>
          <td align='center' valign='top'>
            <input type='checkbox' name='canupd[" . intval($t['id']) . "]' value='1' " . ($t['canschedule'] ? "CHECKED" : "") . ">
          </td>";
    
    if(($session_userid == "sarahg@emergencyskills.com") && !$t['overalladmin']) {
        echo "<td align='center' valign='top'>
                <input type='checkbox' name='todel[" . intval($t['id']) . "]' value='1'>
              </td>";
    }
    
    echo "</tr>";
}
?>
</table><p>
<input type='submit' name='update' value='Update'>
<?php if($session_userid == "sarahg@emergencyskills.com") { ?>
    <input type='submit' name='delete' onClick='return confirm("Are you SURE you want to delete these users?")' value='Delete'>
<?php } ?>
<br><br><br>
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