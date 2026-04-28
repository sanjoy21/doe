<?php
include "mysql.php";
$row = getCompanyRow($id);

if (isset($markc) && $markc) {
    $comnotes_safe = mysqli_real_escape_string($link, $comnotes);
    db_query("update recertnotes set completed = 1, completednotes = '$comnotes_safe' where id = '$markc'");
    Header("Location: editrecertnotes.php?id=$id");
    exit;
}

if (isset($addnote) && $addnote) {
    $thesd = strtotime("$sd");
    $thesd = mktime(0, 0, 0, date("m", $thesd) + 10, date("d", $thesd) + 15);
    $nextDate = fixdatefordb(date("Y-m-d", $thesd));
    db_query("insert into recertnotes (recertificationnotes, recertdate, recertperson, companyid, nextcalldate, assignedto, tassignedto) values ('Annual', now(), $session_id, $id, '$nextDate', '" . RECERTPERSON . "', '' )");
    header("Location: editrecertnotes.php?id=$id");
}

if (isset($addnamesnote) && $addnamesnote) {
    $thesd = mktime(0, 0, 0, date("m"), date("d") + 3);
    $nextDate = fixdatefordb(date("Y-m-d", $thesd));
    db_query("insert into recertnotes (recertificationnotes, recertdate, recertperson, companyid, nextcalldate, assignedto, tassignedto) values ('Names and Email Addresses Requested', now(), $session_id, $id, '$nextDate', '" . RECERTPERSON . "', '' )");
    header("Location: editrecertnotes.php?id=$id");
}

if (isset($updaterecert) && $updaterecert) {
    $recertificationnotes_safe = mysqli_real_escape_string($link, $recertificationnotes);
    $nextcalldate_safe = mysqli_real_escape_string($link, fixdatefordb($nextcalldate));
    $assignedto_safe = mysqli_real_escape_string($link, $assignedto);
    $tassignedto_safe = mysqli_real_escape_string($link, $tassignedto);
    
    db_query("insert into recertnotes (recertificationnotes, recertdate, recertperson, companyid, nextcalldate, assignedto, tassignedto) values ('$recertificationnotes_safe', now(), $session_id, $id, '$nextcalldate_safe', '$assignedto_safe', '$tassignedto_safe')");

    $body = "Company: " . (isset($row["companyname"]) ? $row["companyname"] : "") . "\n\n" . stripslashes($recertificationnotes) . "\n\nNext Call Date:" . $nextcalldate;
    $body .= "\n\nAdded by: " . (isset($thisusersrow["first_name"]) ? $thisusersrow["first_name"] : "") . " " . (isset($thisusersrow["last_name"]) ? $thisusersrow["last_name"] : "");

    if (isset($assignedto) && $assignedto) {
        sendMail(getEmail($assignedto), "New Recertifcation note for: " . (isset($row["companyname"]) ? $row["companyname"] : ""), $body, "info@emergencyskills.com");
    }
    
    if (isset($tassignedto) && $tassignedto) {
        sendMail(getEmail($tassignedto), "New Recertifcation note for: " . (isset($row["companyname"]) ? $row["companyname"] : ""), $body, "info@emergencyskills.com");
    }
    // sendMail("rachelc@gmail.com", "New Recertifcation note for: $row[companyname]", $body, "info@emergencyskills.com");
}

if (isset($clearrecert) && $clearrecert) {
    // db_query("update company_esi set recertificationnotes = '', recertdate = now(), recertperson = $session_id where id = $id");
    db_query("delete from recertnotes where companyid = '$id'");
}

if (isset($delre) && $delre) {
    db_query("delete from recertnotes where id = '$delre'");
    Header("Location: editrecertnotes.php?id=$id");
    exit;
}
?>
<?php include "ssi/top.php"; ?>  
<!--start center content-->
<tr><td>  
<form method='post' id='editrecert'>
  <br>
  <table cellpadding="8" cellspacing="1" border="0" width="100%">
   <tr>
    <td valign="top" colspan="2">
        <?php
        ?>   
<span class='copy'>Recertification Notes for : <A href='viewcompany.php?id=<?php echo $id; ?>'><?php echo htmlspecialchars($row["companyname"]); ?></a>:</span><br>
<?php
$next = db_query_first("select * from class where companyid = '$id' and startdate > now() and deleted = 0 and canceldate is null order by startdate");
if (isset($next["id"]) && $next["id"]) {
    echo "Next Class: <a href='class_detail.php?id=" . $next["id"] . "'>" . $next["startdate"] . "</a><br>";
} else {
    echo "Next Class: None<br>";
}
$next = db_query_first("select * from class where companyid = '$id' and startdate < now() and deleted = 0 and canceldate is null order by startdate desc limit 1");
if (isset($next["id"]) && $next["id"]) {
    echo "Most Recent Class: <a href='class_detail.php?id=" . $next["id"] . "'>" . $next["startdate"] . "</a><br>";
} else {
    echo "Most Recent Class: None<br>";
}
?>

<?php if (isOverallAdmin()) { ?>
<table><tr><td valign='top'><b>Add New:</td><td><textarea cols='30' rows='5' name='recertificationnotes'><?php 
    if (isset($row["recertificationnotes"])) {
        echo htmlspecialchars($row["recertificationnotes"]);
    }
?></textarea></td></tr>
<tr><td>Assigned To:</td><td>
<select name='assignedto'>
<option value=''></option>
<?php
$ovadmins = db_query_rows("select userid, id from user where overalladmin = 1 and inactive = 0 order by userid");
foreach ($ovadmins as $o) { ?>
<option value='<?php echo $o["id"]; ?>'><?php echo htmlspecialchars($o["userid"]); ?></option>
<?php } ?>
</select>
</td></tr>
<tr><td>Assigned Trainer:</td><td>
<select name='tassignedto'>
<option value=''></option>
<?php
$trainers = getAllTrainers();
foreach ($trainers as $o) { ?>
<option value='<?php echo $o["id"]; ?>'><?php echo htmlspecialchars($o["userid"]); ?></option>
<?php } ?>
</select>
</td></tr>
<tr><td>Next Call Date:</td><td><?php echo printdates2("nextcalldate"); ?></td></tr>
</table>
<br><input type='submit' name='updaterecert' value='Add'> <br><br>
<?php } ?>
<table border=1 cellpadding=2 cellspacing=0 >
<tr><th>Note</th><th>Added By</th><th>Assigned To</th><th>Assigned Trainer</th><th>Next Call Date</th><th>Date Added</th><th>Completed?</th>
<?php if (isOverallAdmin()) { ?>
<th>Action</th>
<?php } ?>
</tr>
<?php  $recertnotes = db_query_rows("select * from recertnotes where companyid = " . (isset($row["id"]) ? $row["id"] : 0) . " order by recertdate desc");
foreach ($recertnotes as $r) { ?>
<tr><td> <?php echo nl2br(htmlspecialchars($r["recertificationnotes"])); ?>
<td><?php echo getUserName($r["recertperson"]); ?>
<td><?php echo getUserName($r["assignedto"]); ?>&nbsp;</td>
<td><?php echo getUserName($r["tassignedto"]); ?>&nbsp;</td>
<td><?php echo fixdatefordisplay($r["nextcalldate"], true); ?></td>
<td><?php echo date("m/d/Y h:i a", strtotime($r["recertdate"])); ?> </td>
<td><?php 
    if (isset($r["completed"]) && $r["completed"]) {
        echo "Yes: " . htmlspecialchars($r["completednotes"]);
    } else {
        echo "No";
    }
?> </td>

<?php if (isOverallAdmin()) { ?>
<td>
<?php if (!isset($r["completed"]) || !$r["completed"]) { ?>
<a href='#' onClick='javascript: markcompleted( <?php echo $r["id"]; ?> ); return false' >(mark completed)</a>
<?php } ?>
&nbsp;<a onclick='return confirm("Are you sure you want to delete this?")' href='editrecertnotes.php?delre=<?php echo $r["id"]; ?>&id=<?php echo $row["id"]; ?>'>(del)</a>
<?php } ?>
</tr>
<?php } ?>
</table>
<?php if (isOverallAdmin()) { ?>
<input type='submit' name='clearrecert' value='Clear All Notes' onClick='return confirm("Are you sure you want to clear all notes?")'>
<?php } ?>
<input type='hidden' name='markc' id='markc'>
<input type='hidden' name='comnotes' id='comnotes'>
<script language='javascript'>
function markcompleted(id) {
    var p = prompt("What is the outcome of the call?");
    if (p != null) {
        document.getElementById("markc").value = id;
        document.getElementById("comnotes").value = p;
        document.getElementById("editrecert").submit();
    }
}
</script>
<br><br>
</div>
</form>
</body>
</html>