<?php
require_once('mysql.php');

function db_escape_string($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

if(getcurrentusercompany() > 0)
{
    Header("location: login.php");
    exit;
}

$ext = '';
if(isset($go) && $go)
{
    if(isset($region) && $region)
    {
        $ext .= " and ( 1 = 0 ";
        $exp = explode(",", $region);
        foreach($exp as $e)
        {
            $e = trim($e);
            $ext .= " or region = '" . db_escape_string($e) . "' ";
        }
        $ext .= " ) ";
    }
    if(isset($trainfrom) && $trainfrom)
    {
        $ext .= " and c.startdate >= '" . fixdate($trainfrom) . "' ";
    }
    if(isset($companyid) && $companyid)
    {
        $ext .= " and co.id = '" . intval($companyid) . "' ";
    }
    if(isset($trainto) && $trainto)
    {
        $ext .= " and c.startdate <= '" . fixdate($trainto) . " 23:59:59' ";
    }
    if(isset($campusid) && $campusid)
    {
        $ext .= " and campusid = '" . intval($campusid) . "'";
    }
    
    $session_iscorp = isset($session_iscorp) ? $session_iscorp : 0;
    $sql = "select r.pmsid, r.email, r.title, classid, startdate, r.firstname, r.lastname, schoolcode, companyname, training_room_number, training_location, training_zip, training_city, training_state from responder_to_class rtc, responders_esi r, class c, company_esi co where co.id = r.clientid  and iscorp = '$session_iscorp' and startdate > now() $ext  and rtc.responderid = r.responderid and rtc.classid = c.id ";
    
    $res = db_query_rows($sql);
}

if(isset($xls) && $xls)
{
    // Generate CSV instead of Excel
    $filename = "report_aeds_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write header row
    $header = [
        "Class ID",
        "Class Date",
        "Last Name",
        "First Name",
        "Title",
        "Email",
        "Payroll Reference #",
        getSchoolStr("School"),
        getSchoolStr("School") . " Code",
        "Training Room Number",
        "Training Location",
        "Training City",
        "Training State",
        "Training Zip"
    ];
    
    fputcsv($output, $header);
    
    if(isset($res) && is_array($res))
    {
        foreach($res as $r)
        {
            // Prepare data row
            $rowData = [
                $r['classid'] ?? '',
                $r['startdate'] ?? '',
                $r['lastname'] ?? '',
                $r['firstname'] ?? '',
                $r['title'] ?? '',
                $r['email'] ?? '',
                $r['pmsid'] ?? '', // No special writeString needed for CSV
                $r['companyname'] ?? '',
                $r['schoolcode'] ?? '',
                $r['training_room_number'] ?? '',
                $r['training_location'] ?? '',
                $r['training_city'] ?? '',
                $r['training_state'] ?? '',
                $r['training_zip'] ?? ''
            ];
            
            // Escape any formulas that might start with =, +, - or @ to prevent CSV injection
            foreach($rowData as &$value) {
                if($value !== null && $value !== '') {
                    $firstChar = substr($value, 0, 1);
                    if(in_array($firstChar, array('=', '+', '-', '@'))) {
                        $value = "'" . $value;
                    }
                }
            }
            
            fputcsv($output, $rowData);
        }
    }
    
    fclose($output);
    exit;
}

if(!isset($xls) || !$xls)
{
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">UPCOMING ATTENDEES REPORT</span></strong>
<p>
<form method='get' >
    <?php echo isset($err) ? htmlspecialchars($err) : ''; ?>
<form method='get'>
<table>
<tr><td>Dates:</td><td>  <?php echo printdates2("trainfrom", isset($trainfrom) ? $trainfrom : ''); ?> to <?php echo printdates2("trainto", isset($trainto) ? $trainto : ''); ?></td></tr>
<tr><td>Region: (comma separated) </td><td><input type='text'  name="region" value="<?php echo isset($region) ? htmlspecialchars($region) : ''; ?>"><br>
        <tr><td class='copy'><?php echo getSchoolStr("Campus"); ?> ID:</td><td> <select name='campusid' class='copy'>
        <option value=''></option>
        <?php 
        $campuses = db_query_array("select id, name from campus where iscorp = '" . (isset($session_iscorp) ? $session_iscorp : 0) . "'  order by name", "id", "name");
        if(isset($campuses) && is_array($campuses))
        {
            foreach($campuses as $tid=>$tname)
            {
                $selected = (isset($campusid) && $tid == $campusid) ? "SELECTED" : "";
                echo "<option $selected value='" . htmlspecialchars($tid) . "'>" . htmlspecialchars($tname) . "</option>";
            }
        }
        ?>
</select>
</td></tr>
<tr><td>Company ID:</td><td> <input type='text'  name="companyid" value="<?php echo isset($companyid) ? htmlspecialchars($companyid) : ''; ?>"></td></tr>
<tr><td>XLS:</td><td> <input type='checkbox' name='xls' value='1' <?php echo isset($xls) && $xls ? "checked" : ""; ?>></td></tr>
      <tr><td> <input type='submit' name='go' value='Go'></td></tr>
</table>
</form>
<br><br>
<?php 
}
else
{
    header("Content-Disposition: attachment; filename=upcoming.xls");
    header("Content-Type: application/vnd.ms-excel");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
}

if(isset($go) && $go)
{
    if(isset($xls) && $xls) 
    {
        echo "<table>";
    }
    else
    {
        echo "<table cellspacing=0 border=1>";
    }
    
    echo "<tr><th>Class ID</th><th>Class Date</th><th>Last Name</th><th>First Name</th><th>Title</th><th>Email</th><th>Payroll Reference #</th><th>" . getSchoolStr("School") . "</th><th>" . getSchoolStr("School") . " Code</th><th>Training Room Number</th><th>Training Location</th><th>Training City</th><th>Training State</th><th>Training Zip</th></tr>";
    
    if(isset($res) && is_array($res))
    {
        foreach($res as $r)
        {
            echo "<tr>";
            if(isset($xls) && $xls)
            {
                echo "<td>" . (isset($r['classid']) ? htmlspecialchars($r['classid']) : '') . "</td>";
            }
            else
            {
                echo "<td><a href='class_detail.php?id=" . (isset($r['classid']) ? htmlspecialchars($r['classid']) : '') . "' target=_blank>" . (isset($r['classid']) ? htmlspecialchars($r['classid']) : '') . "</a></td>";
            }
            
            echo "<td>" . (isset($r['startdate']) ? htmlspecialchars($r['startdate']) : '') . "</td>";
            echo "<td>" . (isset($r['lastname']) ? htmlspecialchars($r['lastname']) : '') . "</td>";
            echo "<td>" . (isset($r['firstname']) ? htmlspecialchars($r['firstname']) : '') . "</td>";
            echo "<td>" . (isset($r['title']) ? htmlspecialchars($r['title']) : '') . "</td>";
            echo "<td>" . (isset($r['email']) ? htmlspecialchars($r['email']) : '') . "</td>";
            echo "<td>" . (isset($r['pmsid']) ? htmlspecialchars($r['pmsid']) : '') . "</td>";
            echo "<td>" . (isset($r['companyname']) ? htmlspecialchars($r['companyname']) : '') . "</td>";
            echo "<td>" . (isset($r['schoolcode']) ? htmlspecialchars($r['schoolcode']) : '') . "</td>";
            echo "<td>" . (isset($r['training_room_number']) ? htmlspecialchars($r['training_room_number']) : '') . "</td>";
            echo "<td>" . (isset($r['training_location']) ? htmlspecialchars($r['training_location']) : '') . "</td>";
            echo "<td>" . (isset($r['training_city']) ? htmlspecialchars($r['training_city']) : '') . "</td>";
            echo "<td>" . (isset($r['training_state']) ? htmlspecialchars($r['training_state']) : '') . "</td>";
            echo "<td>" . (isset($r['training_zip']) ? htmlspecialchars($r['training_zip']) : '') . "</td>";
            echo "</tr>";
        }
    }
?>
</table>
    <?php 
}

if(!isset($xls) || !$xls)
{ 
?>
<br><br><br><br><br><br><br>

<!--end center content-->

<?php include "ssi/footer.php"; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
</div>
</body>
</html>
<?php } ?>