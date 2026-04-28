<?php
require_once('mysql.php');
require_once('services.php');

$eighteen = date("Y-m-d", strtotime("18 months ago"));
$extresp = " and responders_esi.responderid not in ( select responderid from responder_to_class rtc, class c where rtc.classid = c.id and c.startdate > '$eighteen' )";

eval("\$selected_{$companyid} = \"SELECTED\";");

if (!$companyid && isset($_GET["companyid"])) {
    $companyid = $_GET["companyid"];
}
if (!$companyid) {
    $sql = "select companyid from user where id = '$session_id'";
    $companyid = db_query_first_cell($sql);
}

$iscorp = false;
if ($companyid) {
    $iscorp = db_query_first_cell("select iscorp from company_esi where id = $companyid");
}

if ($listin) {
    $code = db_query_first_cell("select schoolcode from company_esi where id = $companyid");
    $companyname = db_query_first_cell("select companyname from company_esi where id = $companyid");
    
    $ext = $iscorp ? "" : " and ( pmsidvalidated = 1 or emptype in ( 'Charter School Employee', 'SSA', 'Custodial Staff', 'Non DOE' ) )";
    $ischarter = isCharter($companyname, $code);
    if (!$iscorp && $ischarter) {
        $ext = "";
    }

    if (!$iscorp) {
        $ext .= " and email > '' ";
    }

    $inschool = db_query_rows("select * from responders_esi where clientid = $companyid and deleted = 0 $ext $extresp order by lastname, firstname");
}

if ($fromview && $addresponder) {
    $iscorp = db_query_first_cell("select iscorp from company_esi where id = $companyid");
    $alreadyinclass = db_query_first_cell("select responderid from responder_to_class r, class c where c.id = r.classid and startdate > now() and c.deleted = 0 and responderid = " . $addresponder);
    if ($alreadyinclass && !$iscorp) {
        $confirm = "<div id='error'>This person is already in an upcoming class.</div>";
        $valid = false;
        $is_added = 0;
    } else {
        $i = db_query_first_cell("Select max(position) from responder_to_class where classid = $fromview");
        $i++;
        addAttendee(time(), $fromview, $addresponder, $i, "");
    }
} elseif ($addresponder) {
    $iscorp = db_query_first_cell("select iscorp from company_esi where id = $companyid");
    $alreadyinclass = db_query_first_cell("select responderid from responder_to_class r, class c where c.id = r.classid and startdate > now() and c.deleted = 0 and responderid = " . $addresponder);
    if ($alreadyinclass && !$iscorp) {
        $confirm = "<div id='error'>This person is already in an upcoming class.</div>";
        $valid = false;
        $is_added = 0;
        $addresponder = 0;
    }
}

if ($companyid) {
    $iscorp = db_query_first_cell("select iscorp from company_esi where id = $companyid");
    $crow = getCompanyRow($companyid);
    $companyname_crow = isset($crow['companyname']) ? $crow['companyname'] : '';
    $schoolcode_crow = isset($crow['schoolcode']) ? $crow['schoolcode'] : '';
    $ischarter = isCharter($companyname_crow, $schoolcode_crow);
    $isssa = isSSA($crow);
}

if ($_POST && !$listin) {
    $valid = true;
    if (!$firstname) {
        $confirm = "<div id='error'>No first name was entered.  Please try again</div>";
        $valid = false;
    }
    if (!$lastname) {
        $confirm = "<div id='error'>No last name was entered.  Please try again</div>";
        $valid = false;
    }

    if (!$iscorp && !trim($pmsid) && !in_array($emptype, array('Charter School Employee', 'SSA', 'Custodial Staff', 'Non DOE')) && !$ischarter && !$isssa) {
        $confirm = "<div id='error'>No " . getSchoolStr("PMS ID") . " was entered.  Please try again</div>";
        $valid = false;
    }

    if ($valid) {
        $ext = $iscorp ? "" : " and ( pmsidvalidated = 1 or emptype in ( 'Charter School Employee', 'SSA', 'Custodial Staff', 'Non DOE' ) )";
        $is_added = db_query_first_cell("select responderid from responders_esi where clientid = $companyid and firstname like '$firstname' and lastname like '$lastname' and deleted = 0 $ext");

        if ($is_added) {
            $is_added_email = db_query_first_cell("select email from responders_esi where responderid = $is_added");
            if (!$is_added_email || strtolower($is_added_email) == "x@x.com") {
                db_query("update responders_esi set email = '$email' where responderid = $is_added");
            }
        }

        $pmsid = trim($pmsid);
        if (!$is_added) {
            $okaytoadd = true;
            if (!$iscorp) {
                $pmsidvalidated = validateEmployee($pmsid, $lastname, "add attendee");
            }

            if (!$iscorp && !$pmsidvalidated && $emptype == "DOE Employee" && !isOverallAdmin()) {
                $confirm = "<div id='error'>Payroll Reference # not valid. Please reenter.</div>";
                $valid = false;
            } else {
                if (!$iscorp && $pmsid && !$ischarter && !in_array($emptype, array('Charter School Employee', 'SSA', 'Custodial Staff', 'Non DOE')) && !$isssa && $pmsid != "x" && $pmsid != "1234" && $pmsid != "123456" && $pmsid != "111111") {
                    $is_added = db_query_first_cell("select responderid from responders_esi where clientid = $companyid and pmsid = '$pmsid' and deleted = 0");
                }
                
                if ($is_added) {
                    db_query("update responders_esi set pmsid = '$pmsid', firstname = '$firstname', lastname = '$lastname', pmsidvalidated = '$pmsidvalidated', lastpmsvalidated = now(), emptype = '$emptype', title = '$title', email = '$email' where responderid = $is_added");
                } elseif ($valid) {
                    $sql = "INSERT INTO responders_esi (clientid, firstname, lastname, filenumber, pmsid, pmsidvalidated, lastpmsvalidated, email, dayphone, dayphoneExtension, title, buildingcode, emptype, raddedby, raddeddate) VALUES ('$companyid', '$firstname', '$lastname', '$filenumber', '$pmsid', '$pmsidvalidated', now(), '$email', '$phone', '$phoneext', '$title', '$buildingcode', '$emptype', '$session_id', now())";
                    $is_added = db_query_insert_id($sql);
                }
                $confirm = "<div id='success'>Successful!</div>";
            }
        } else {
            $alreadyinclass = db_query_first_cell("select responderid from responder_to_class r, class c where c.id = r.classid and startdate > now() and c.deleted = 0 and responderid = " . $is_added);
            if ($alreadyinclass && !$iscorp) {
                $confirm = "<div id='error'>This person is already in an upcoming class.</div>";
                $valid = false;
                $is_added = 0;
            } else {
                $confirm = "<div id='success'>Successful!</div>";
            }
        }
        if ($fromview && $is_added) {
            $i = db_query_first_cell("Select max(position) from responder_to_class where classid = $fromview");
            $i++;
            addAttendee(time(), $fromview, $is_added, $i, "");
        }
    }
}

$afirstname = $firstname;
$alastname = $lastname;
$afilenumber = $filenumber;
$apmsid = $pmsid;
$aemail = $email;
$aphone = $phone;
$aphoneext = $phoneext;
$atitle = $title;

if ($is_added && $addanother) {
    $firstname = "";
    $lastname = "";
    $filenumber = "";
    $pmsid = "";
    $email = "";
    $phone = "";
    $phoneext = "";
    $title = "";
    $currcnt++;
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>ESI: Add Attendee</title>
    <META NAME="Keywords" CONTENT="">
    <META NAME="Description" CONTENT="">    
    <SCRIPT LANGUAGE="JavaScript">
    function validateEmail(email) {
        var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
        return re.test(email);
    }

    function EmailOkay(emailfield) {
        <?php if (!$iscorp) { ?>
        if (emailfield.value == "" || !validateEmail(emailfield.value)) {
            alert("A valid email address is required.");
            return false;
        }
        var titlefield = document.getElementById("title");
        if (titlefield.value == "") {
            alert("Position is required.");
            return false;
        }
        if (titlefield.value.toLowerCase().indexOf("nurse") > -1) {
            alert("This training does not meet the requirements for school nurses. For nurses and healthcare provider training information, please click go to this url: https://doe.emergencyskills.com/nurses.php");
            return false;
        }
        <?php } ?>
        return true;
    }

    function ChangeImage(ImageName, FileName) {
        document[ImageName].src = FileName;
    }
    </SCRIPT>    
    <?php 
    if ($specialadmin) {
        ${"selected_".$companyid} = "SELECTED";
        $overrideiscorp = $iscorp;
        include "getschooldropdown.php"; 
    }
    ?>
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>   
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script> 
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">       
    <style TYPE="text/css">
    BODY {margin:20}
    </style>    
    <script>
    $(document).ready(function() {
        <?php 
        $arr = db_query_rows("select distinct(trim(name)) as name from jobtitles order by name");
        ?>
        var availableTagsJTs = [
        <?php 
        $first = true;
        foreach ($arr as $row) { 
            if (!$first) {
                echo ", ";
            }
            echo '"' . $row['name'] . '"';
            $first = false;
        } 
        ?>
        ];
        
        $("#title").autocomplete({
            source: availableTagsJTs
        });
    });
    </script>
    <link rel="stylesheet" href="css/style.css">
</head>

<body bgcolor="#ffffff" marginwidth="20" marginheight="20">
<form method="post">
<input type="hidden" name="action" value="add">
<input type="hidden" name="currcnt" value="<?php echo $currcnt; ?>">
<input type="hidden" name="maxcnt" value="<?php echo $maxcnt; ?>">
<span class="copy">
<strong><span class="title">Add Attendee</span></strong><p>
Please add your attendee by filling in the boxes below:<p>
        <table cellpadding="0" cellspacing="4" border="0">            
<?php if ($specialadmin) { ?>
<tr>
    <td valign="top"><span class="copy">Borough:</span><br>
        <select id="borough" name="borough" onChange="changeBorough();" style="font-size: 10px; font-family: verdana;">
            <option value=""></option>
            <option value="Bronx">The Bronx</option>
            <option value="Brooklyn">Brooklyn</option>
            <option value="Manhattan">Manhattan</option>
            <option value="Queens">Queens</option>
            <option value="Staten Island">Staten Island</option>
        </select>
    </td>
</tr>
<tr>
    <td> <span class='copy'><?php echo getSchoolStr("School", $iscorp); ?> Name: </span><br> <input type='text' id='tmpschoolname' name='tmpschoolname' class='copy' value="<?php echo $companyid ? getCompanyName($companyid) : ""; ?>" onChange='changeBorough()'> <input type='button' value='Search' class='copy' onClick='changeBorough()'></td>
</tr>
<tr> <td valign="top" id='school_select'> </td> </tr>
<tr><td><input class='copy' type='submit' name='listin' value='List People In This <?php echo getSchoolStr("School", $iscorp); ?>'></td></tr>
<?php if ($listin) {
    echo "<tr><td>";
    foreach ($inschool as $isrow) {
        echo $isrow['firstname'] . " " . $isrow['lastname'] . " <a href='add_attendee.php?fromview=" . $fromview . "&companyid=" . $companyid . "&addresponder=" . $isrow['responderid'] . "'>Add</a><br>";
    }
    echo "</td></tr>";
} ?>
<?php } ?>
<tr></tr>
<tr><td><br>Please note: CPR Certification card will be printed according to the spelling you have entered. Please verify spelling. <b><font color='blue'><br>There will be a $25 fee to reprint cards with misspelled names.</b></font></td></tr>
<?php
$ext = "";
if (!$iscorp) {
    $ext = "<b>as it appears on pay stub</b>";
}
if ($ischarter) {
    $ext = "<br><b><font color='red'>(Charter schools use<br> last 4 digits of social)</font></b>";
}
?>
<tr>
    <td valign="middle"><span class="copy">Last Name <?php echo !$iscorp ? "<b>as it appears on pay stub</b>" : ""; ?>:</span><br><input name="lastname" value="<?php echo $lastname; ?>" type="text" size="40" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<tr>
    <td valign="middle"><span class="copy">First Name <?php echo !$iscorp ? "<b>as it appears on pay stub</b>" : ""; ?>:</span><br><input name="firstname" value="<?php echo $firstname; ?>" type="text" size="40" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<tr><td><font color='red'><b><?php echo $confirm; ?></b></font></td></tr>
<?php if (!$iscorp) { ?>
<tr>
    <td valign="middle"><span class="copy">Employee Type: (required)</span><br>
        <select name="emptype">
            <option value='DOE Employee'>DOE Employee</option>
            <option <?php echo $ischarter ? "SELECTED" : ""; ?> value='Charter School Employee'>Charter School Employee</option>
            <option value='SSA'>SSA</option>
            <option value='Custodial Staff'>Custodial Staff</option>
            <?php if (isOverallAdmin()) { ?>
            <option value='Non DOE'>Non DOE</option>
            <?php } ?>
        </select>
    </td>
</tr>
<tr>
    <td valign="middle">
        <table>
            <tr>
                <td><span class="copy"><a href='images/NYC001.jpg' target='_blank'><?php echo getSchoolStr("PMS ID"); ?>: (help)</a><?php echo $ext; ?></span></td>
                <td><input name="pmsid" value="<?php echo $pmsid; ?>" type="text" size="8" maxlength="8" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
            </tr>
        </table>
    </td>
</tr>
<?php } else { ?>
<tr>
    <td valign="middle"><span class="copy"><?php echo getSchoolStr("File Number"); ?>:</span><input name="filenumber" value="<?php echo $filenumber; ?>" type="text" size="8" maxlength="8" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<?php } ?>
<tr>
    <td valign="middle"><span class="copy">Email:</span><br><input name="email" value="<?php echo $email; ?>" type="text" id="emailaddress" size="40" style="font-family: verdana; font-size: 11px; line-height: 13px">
    <table>
    <?php 
    $classemail = '';
    if (isset($crow['contactemail']) && $crow['contactemail']) {
        $parts = explode("@", $crow['contactemail']);
        $classemail = end($parts);
    }
    $arr = array();
    if (isset($crow["iscorp"]) && !$crow["iscorp"]) $arr[] = "@schools.nyc.gov";
    if ($classemail) $arr[] = "@" . $classemail;
    $arr[] = "@gmail.com";
    $arr[] = "@yahoo.com";
    $arr[] = "@aol.com";
    $arr = array_unique($arr);
    foreach ($arr as $a) { 
    ?>
    <tr><td><input type='radio' name='ignoreme' onClick='addToEmail("<?php echo $a; ?>")'> <?php echo $a; ?></td></tr>
    <?php } ?>
    </table>
    </td>
</tr>
<tr>
    <td valign="middle"><span class="copy">Phone (optional):</span><br><input name="phone" value="<?php echo $phone; ?>" type="text" size="12" style="font-family: verdana; font-size: 11px; line-height: 13px"> Ext. <input name="phoneext" value="<?php echo $phoneext; ?>" type="text" size="4" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<tr>
    <td valign="middle"><span class="copy"><?php if ($session_iscorp) { ?>Job Title:<?php } else { ?>Position (i.e. Coach, Principal, Teacher):<?php } ?></span><br><input name="title" value="<?php echo $title; ?>" type="text" id="title" size="40" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<?php if (isOverallAdmin()) { ?>
<tr>
    <td valign="middle"><span class="copy">Timeslot:</span><br><input name="timeslot" value="<?php echo $timeslot; ?>" type="text" id="timeslot" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<?php } ?>
<?php if (!$iscorp) { ?>
<tr style='display:none'><td>Building: <br><?php echo getBuildingPulldown($companyid); ?></td></tr>
<?php } ?>
<tr>
    <td>
        <table>
            <tr>
                <td><input type="image" src="images/button_add.gif" onClick="return EmailOkay(document.getElementById('emailaddress'))"></td>
                <td><?php if (!$fromview && (!$maxcnt || $maxcnt > $currcnt + 1)) { ?><input type="submit" name='addanother' value='Add Another'></td><td><?php } ?><input type="button" name='close' value='Close' onClick='javascript:window.close()'></td>
            </tr>
        </table>
    </td>
</tr>
        </table>
    </span>

<?php if ($is_added) { ?>
<script type="text/javascript">
    <?php if ($fromview) { ?>
        window.opener.location.reload();
    <?php } else { ?>
        window.opener.addMyOption("<?php echo $alastname; ?>, <?php echo $afirstname; ?> (#<?php echo $apmsid ? $apmsid : $afilenumber; ?>)",<?php echo $is_added; ?>, "<?php echo $timeslot; ?>");
        if (window.opener.classfullinner(false)) {
            setTimeout('window.close()', 1000);
        }
    <?php } ?>
    <?php if (!$addanother) { ?>
        setTimeout('window.close()', 1000);
    <?php } ?>
</script>
<?php } elseif ($addresponder) {
    $rrow = db_query_first("select * from responders_esi where responderid = $addresponder"); 
    $identifier = getIdentifier($rrow);
    ?>
<script type="text/javascript">
    <?php if ($fromview) { ?>
        window.opener.location.reload();
    <?php } else { ?>
        window.opener.addMyOption("<?php echo $rrow['lastname']; ?>, <?php echo $rrow['firstname']; ?> (#<?php echo $identifier; ?>)",<?php echo $addresponder; ?>, "<?php echo $timeslot; ?>");
    <?php } ?>
    <?php if (!$addanother) { ?>
        setTimeout('window.close()', 1000);
    <?php } ?>
</script>
<?php } else { ?>
<script language='javascript'>
    changeBorough();
</script>
<?php } ?>
<script language='javascript'>
    function addToEmail(ext) {
        var exp = document.forms[0].email.value.split("@");
        document.forms[0].email.value = exp[0] + ext;
    }
</script>
</form>
</body>
</html>