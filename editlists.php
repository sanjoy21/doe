<?php
include "mysql.php";

function db_escape_string($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

if($reorder) {
    foreach($progr as $p => $prio) {
        db_query("update esioptionvalues set priority = '" . db_escape_string($prio) . "' where value = '" . db_escape_string($p) . "' and datatype = 'program'");
    }
    
    foreach($shortn as $p => $prio) {
        db_query("update esioptionvalues set shortname = '" . db_escape_string($prio) . "' where value = '" . db_escape_string($p) . "' and datatype = 'program'");
    }
    
    db_query("update esioptionvalues set isaed = '0' where datatype = 'program'");
    
    foreach($isaed as $p => $prio) {
        db_query("update esioptionvalues set isaed = '1' where value = '" . db_escape_string($p) . "' and datatype = 'program'");
    }
    
    db_query("update esioptionvalues set isretired = '0' where datatype = 'program'");
    
    foreach($isretired as $p => $prio) {
        db_query("update esioptionvalues set isretired = '1' where value = '" . db_escape_string($p) . "' and datatype = 'program'");
    }
    
    foreach($reportcode as $p => $prio) {
        db_query("update esioptionvalues set reportcode = '" . db_escape_string($prio) . "' where value = '" . db_escape_string($p) . "' and datatype = 'program'");
    }
    
    foreach($categories as $p => $prio) {
        db_query("update esioptionvalues set category = '" . db_escape_string($prio) . "' where value = '" . db_escape_string($p) . "' and datatype = 'program'");
    }
}

if($delid) {
    db_query("delete from esioptionvalues where id = '" . intval($delid) . "' and datatype = 'program'");
}

if($deltype && $delname) {
    db_query("delete from esioptionvalues where datatype = '" . db_escape_string($deltype) . "' and value = '" . db_escape_string($delname) . "'");
}

if($insert && $datatype && $new_value) {
    db_query("insert into esioptionvalues (datatype, value, date, shortname) values ('" . db_escape_string($datatype) . "','" . db_escape_string($new_value) . "',Now(), '" . db_escape_string($shortname) . "')");    
    Header("location: " . $redirect);
    exit;
}

$program_rows = db_query_rows("select * from esioptionvalues where datatype = 'program' order by isretired, priority, value");
$instructor_rows = db_query_array("select value from esioptionvalues where datatype = 'instructor name' order by value", "value", "value");
$training_rows = db_query_array("select value from esioptionvalues where datatype = 'training site' order by value", "value", "value");
$model_rows = db_query_array("select value from esioptionvalues where datatype = 'model' order by value", "value", "value");
$trainer_rows = db_query_array("select value from esioptionvalues where datatype = 'trainer' order by value", "value", "value");
$director_rows = db_query_array("select value from esioptionvalues where datatype = 'director' order by value", "value", "value");
$mdaddress_rows = db_query_array("select value from esioptionvalues where datatype = 'mdaddress' order by value", "value", "value");
$mdphone_rows = db_query_array("select value from esioptionvalues where datatype = 'mdphone' order by value", "value", "value");
$mdfax_rows = db_query_array("select value from esioptionvalues where datatype = 'mdfax' order by value", "value", "value");
$equip_rows = db_query_array("select value from esioptionvalues where datatype = 'avequip' order by value", "value", "value");
$ar_rows = db_query_array("select value from esioptionvalues where datatype = 'areqs' order by value", "value", "value");
$el_rows = db_query_array("select value from esioptionvalues where datatype = 'emaillist' order by value", "value", "value");
?>

<?php include "ssi/top.php"; ?>
<!--start center content-->
<SCRIPT LANGUAGE="JavaScript">
<!---------- JavaScript begins...
function deleteSelected(ele, type) {
    var val = ele.options[ele.selectedIndex].value;
    if(val == "") {
        return;
    } else {
        if(confirm("Are you sure you want to delete '" + val + "'?")) {
            document.location.href='editlists.php?deltype=' + encodeURIComponent(type) + '&delname=' + encodeURIComponent(val);
        }
    }
}

function promptValue(form, label, note) {
    var message = label;
    if(note) {
        message += " (" + note + ")";
    }
    var value = prompt(message + " : ", "");
    if(!value) { 
        return; 
    }
    var value2 = prompt("shortname : ", "");

    var ok = confirm("OK to add \"" + value + "\" to the " + label + " list?");
    if(ok) {
        form.datatype.value = label;
        form.new_value.value = value;
        form.shortname.value = value2;
        form.submit();
    } else {
        form.datatype.value = '';
        form.new_value.value = '';
    }
    return;
}

function ChangeImage(ImageName, FileName) {
    document[ImageName].src = FileName;
}
// JavaScript ends ---------->
</SCRIPT>
<form action="editlists.php" method="POST" name="form1">
<input type="hidden" name="insert" value="true">
<input type="hidden" name="datatype" value="">
<input type="hidden" name="new_value" value="">
<input type="hidden" name="shortname" value="">

<?php
if(!$redirect) {
    $redirect = "/editlists.php";
}
?>

<strong><span class="title">MANAGE DROPDOWN MENUS</span></strong>
<p>

<table cellpadding="10" cellspacing="1" border="0" width="455" class="table3">
    <tr>
        <td>
            <span class="copy">Trainers/Inspectors:<br>
            <select name="trainer" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            if(isset($trainer_rows) && is_array($trainer_rows)) {
foreach($trainer_rows as $model) { 
            ?>
<option value="<?php echo htmlspecialchars($model); ?>"><?php echo htmlspecialchars($model); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.trainer, "trainer")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'trainer','');">[Add New]</a>
            </span>
        </td>
    </tr>

    <tr>
        <td>
            <span class="copy">AED Model/Type:<br>
            <select name="model" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            if(isset($model_rows) && is_array($model_rows)) {
foreach($model_rows as $model) { 
            ?>
<option value="<?php echo htmlspecialchars($model); ?>"><?php echo htmlspecialchars($model); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.model, "model")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'model','');">[Add New]</a>
            </span>
        </td>
    </tr>

    <tr>
        <td>
            <span class="copy">A/V Equipment Number:<br>
            <select name="avequip" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            if(isset($equip_rows) && is_array($equip_rows)) {
foreach($equip_rows as $av) { 
            ?>
<option value="<?php echo htmlspecialchars($av); ?>"><?php echo htmlspecialchars($av); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.avequip, "avequip")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'avequip','');">[Add New]</a>&nbsp;&nbsp;<span class="small">
            </span>
        </td>
    </tr>

    <tr>
        <td>
            <span class="copy">AED/Accessory Requests:<br>
            <select name="areqs" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            if(isset($ar_rows) && is_array($ar_rows)) {
foreach($ar_rows as $av) { 
            ?>
<option value="<?php echo htmlspecialchars($av); ?>"><?php echo htmlspecialchars($av); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.areqs, "areqs")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'areqs','');">[Add New]</a>&nbsp;&nbsp;<span class="small">
            </span>
        </td>
    </tr>

    <tr>
        <td>
            <span class="copy">Medical Director Name:<br>
            <select name="directorname" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            if(isset($director_rows) && is_array($director_rows)) {
foreach($director_rows as $director) { 
            ?>
<option value="<?php echo htmlspecialchars($director); ?>"><?php echo htmlspecialchars($director); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.directorname, "director")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'director','as Jane Doe, MD');">[Add New]</a>&nbsp;&nbsp;<span class="small"><em>(Please add as Jane Doe, MD)</em>
            </span>
        </td>
    </tr>
    
    <tr>
        <td>
            <span class="copy">Medical Director Address:<br>
            <select name="mdaddress" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            if(isset($mdaddress_rows) && is_array($mdaddress_rows)) {
foreach($mdaddress_rows as $mdaddress) { 
            ?>
<option value="<?php echo htmlspecialchars($mdaddress); ?>"><?php echo htmlspecialchars($mdaddress); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.mdaddress, "mdaddress")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'mdaddress','as 404 Park Ave, New York NY 10016');">[Add New]</a>&nbsp;&nbsp;<span class="small"><em>(Please add as 404 Park Ave, New York NY 10016)</em>
            </span>
        </td>
    </tr>
    
    <tr>
        <td>
            <span class="copy">Medical Director Phone:<br>
            <select name="mdphone" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            if(isset($mdphone_rows) && is_array($mdphone_rows)) {
foreach($mdphone_rows as $mdphone) { 
            ?>
<option value="<?php echo htmlspecialchars($mdphone); ?>"><?php echo htmlspecialchars($mdphone); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.mdphone, "mdphone")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'mdphone','as 212-564-6833');">[Add New]</a>&nbsp;&nbsp;<span class="small"><em>(Please add as 212-564-6833)</em>
            </span>
        </td>
    </tr>
    
    <tr>
        <td>
            <span class="copy">Medical Director Fax:<br>
            <select name="mdfax" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            if(isset($mdfax_rows) && is_array($mdfax_rows)) {
foreach($mdfax_rows as $mdfax) { 
            ?>
<option value="<?php echo htmlspecialchars($mdfax); ?>"><?php echo htmlspecialchars($mdfax); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.mdfax, "mdfax")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'mdfax','as 212-564-6793');">[Add New]</a>&nbsp;&nbsp;<span class="small"><em>(Please add as 212-564-6793)</em>
            </span>
        </td>
    </tr>

    <tr>
        <td>
            <span class="copy">Type Of Entity:<br>
            <select name="typeofentity" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            $drop_rows = db_query_rows("select value from esioptionvalues where datatype='typeofentity' order by value");
            if(isset($drop_rows) && is_array($drop_rows)) {
foreach($drop_rows as $d) { 
            ?>
<option value="<?php echo htmlspecialchars(isset($d['value']) ? $d['value'] : ''); ?>"><?php echo htmlspecialchars(isset($d['value']) ? $d['value'] : ''); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.typeofentity, "typeofentity")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'typeofentity','');">[Add New]</a>&nbsp;&nbsp;<span class="small">
            </span>
        </td>
    </tr>
    
    <tr>
        <td>
            <span class="copy">NYC Local Law 20 Entity:<br>
            <select name="nycentity" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            $drop_rows = db_query_rows("select value from esioptionvalues where datatype='nycentity' order by value");
            if(isset($drop_rows) && is_array($drop_rows)) {
foreach($drop_rows as $d) { 
            ?>
<option value="<?php echo htmlspecialchars(isset($d['value']) ? $d['value'] : ''); ?>"><?php echo htmlspecialchars(isset($d['value']) ? $d['value'] : ''); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.nycentity, "nycentity")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'nycentity','');">[Add New]</a>&nbsp;&nbsp;<span class="small">
            </span>
        </td>
    </tr>
    
    <tr>
        <td>
            <span class="copy">Ambulance/911 Center:<br>
            <select name="ambulance" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            $drop_rows = db_query_rows("select value from esioptionvalues where datatype='ambulance' order by value");
            if(isset($drop_rows) && is_array($drop_rows)) {
foreach($drop_rows as $d) { 
            ?>
<option value="<?php echo htmlspecialchars(isset($d['value']) ? $d['value'] : ''); ?>"><?php echo htmlspecialchars(isset($d['value']) ? $d['value'] : ''); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.ambulance, "ambulance")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'ambulance','');">[Add New]</a>&nbsp;&nbsp;<span class="small">
            </span>
        </td>
    </tr>
    
    <tr>
        <td>
            <span class="copy">County:<br>
            <select name="county" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            $drop_rows = db_query_rows("select value from esioptionvalues where datatype='county' order by value");
            if(isset($drop_rows) && is_array($drop_rows)) {
foreach($drop_rows as $d) { 
            ?>
<option value="<?php echo htmlspecialchars(isset($d['value']) ? $d['value'] : ''); ?>"><?php echo htmlspecialchars(isset($d['value']) ? $d['value'] : ''); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.county, "county")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'county','');">[Add New]</a>&nbsp;&nbsp;<span class="small">
            </span>
        </td>
    </tr>

    <tr>
        <td>
            <span class="copy">Email Lists:<br>
            <select name="myemaillist" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            if(isset($el_rows) && is_array($el_rows)) {
foreach($el_rows as $av) { 
            ?>
<option value="<?php echo htmlspecialchars($av); ?>"><?php echo htmlspecialchars($av); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.myemaillist, "emaillist")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'emaillist','');">[Add New]</a>&nbsp;&nbsp;<span class="small">
            </span>
        </td>
    </tr>

    <tr>
        <td>
            <span class="copy">
<table class="table3">
    <tr>
        <td>OrderBy</td>
        <td>Shortname</td>
        <td>Is AED</td>
        <td>Retired?</td>
        <td>Category</td>
        <td>Program</td>
    </tr>
<?php
if(isset($program_rows) && is_array($program_rows)) {
    foreach($program_rows as $program) {
        $value = isset($program['value']) ? $program['value'] : '';
        $priority = isset($program['priority']) ? $program['priority'] : '';
        $shortname_val = isset($program['shortname']) ? $program['shortname'] : '';
        $isaed_checked = isset($program['isaed']) && $program['isaed'] ? "CHECKED" : "";
        $isretired_checked = isset($program['isretired']) && $program['isretired'] ? "CHECKED" : "";
        $category = isset($program['category']) ? $program['category'] : '';
        $reportcode_val = isset($program['reportcode']) ? $program['reportcode'] : '';
        $id = isset($program['id']) ? $program['id'] : '';
        
        echo "<tr>";
        echo "<td><input type='text' size='3' name='progr[" . htmlspecialchars($value) . "]' value='" . htmlspecialchars($priority) . "'></td>";
        echo "<td><input type='text' size='6' name='shortn[" . htmlspecialchars($value) . "]' value='" . htmlspecialchars($shortname_val) . "'></td>";
        echo "<td><input type='checkbox' name='isaed[" . htmlspecialchars($value) . "]' value='1' $isaed_checked></td>";
        echo "<td><input type='checkbox' name='isretired[" . htmlspecialchars($value) . "]' value='1' $isretired_checked></td>";
        echo "<td><input type='text' name='categories[" . htmlspecialchars($value) . "]' value=\"" . htmlspecialchars($category) . "\"></td>";
        echo "<td><select name='reportcode[" . htmlspecialchars($value) . "]' style='width:150px'>";
        echo "<option></option>";
        
        $report_options = array(
            "Corporate Heartsaver CPR, AED and/or First Aid",
            "Corporate BLS",
            "Corporate Alive! First Aid",
            "Corporate non certification i.e. Infant anytime and friends and family.",
            "Mental Health First Aid",
            "Instructor Training"
        );
        
        foreach($report_options as $coltitle) {
            $sel = $reportcode_val == $coltitle ? "SELECTED" : "";
            echo "<option value='" . htmlspecialchars($coltitle) . "' $sel>" . htmlspecialchars($coltitle) . "</option>";
        }
        
        echo "</select></td>";
        echo "<td>" . htmlspecialchars($value) . "</td>";
        echo "<td> <a href='editlists.php?delid=" . htmlspecialchars($id) . "'>Delete</a> </td>";
        echo "</tr>";
    }
}
?>
</table>
<a href="javascript:promptValue(document.form1,'program','');">[Add New]</a>
<input type='submit' name='reorder' value='Update'>
            </span>
        </td>
    </tr>
    
    <tr>              
        <td>
            <span class="copy">Instructor Name:<br>
            <select name="instructor" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            if(isset($instructor_rows) && is_array($instructor_rows)) {
foreach($instructor_rows as $instructor) { 
            ?>
<option value="<?php echo htmlspecialchars($instructor); ?>"><?php echo htmlspecialchars($instructor); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.instructor, "instructor")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'instructor name','as Smith, John');">[Add New]</a>&nbsp;&nbsp;<span class="small"><em>(Please add as Smith, John)</em> 
            </span>
        </td>
    </tr>
    
    <tr>
        <td>
            <span class="copy">Training Site:<br>
            <select name="trainingsite" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <?php 
            if(isset($training_rows) && is_array($training_rows)) {
foreach($training_rows as $training) { 
            ?>
<option value="<?php echo htmlspecialchars($training); ?>"><?php echo htmlspecialchars($training); ?></option>
            <?php 
}
            }
            ?>
            </select>&nbsp;&nbsp;<A href='javascript:deleteSelected(document.form1.trainingsite, "training site")'>delete selected</a> &nbsp;&nbsp;
            <a href="javascript:promptValue(document.form1,'training site','');">[Add New]</a> 
            </span>
        </td>
    </tr>
</table>
<br><br><br><br><br><br><br><br><br>
<!--end center content-->
<?php include "ssi/footer.php"; ?>

<!--end footer-->
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