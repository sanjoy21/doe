<?php
require_once('mysql.php');
require_once('services.php');

// Add db_escape function if not already in mysql.php
if (!function_exists('db_escape')) {
    function db_escape($value) {
        global $link; // Your MySQLi connection variable
        
        if (isset($link) && $link instanceof mysqli) {
            return mysqli_real_escape_string($link, $value);
        }
        
        // Fallback if connection not available
        if (is_string($value)) {
            return addslashes($value);
        }
        
        return $value;
    }
}

if(isset($updatecompletions) && $updatecompletions) {
    $saveme = isset($saveme) ? $saveme : array();
    $saveme[] = -1;
    db_query("delete from responder_training_dates where classid = '" . db_escape($id) . "' and responderid not in ( " . implode(", ", $saveme) . " )");
    
    $crow = getClassRow($id);
    
    if(isset($hiddencompletion) && is_array($hiddencompletion)) {
        foreach($hiddencompletion as $a) {
            if(!$a) continue;
            db_query("delete from responder_training_dates where classid = '" . db_escape($id) . "' and responderid = '" . db_escape($a) . "'");
            db_query("insert into responder_training_dates(classid, responderid, trainingdate, program, addedby, okayedby, datecompleted) values ('" . db_escape($id) . "', '" . db_escape($a) . "', '" . db_escape($crow['startdate']) . "', '" . db_escape($crow['code']) . "', '" . db_escape($session_id) . "', '" . db_escape($okayed) . "', now())");
            
            $arow = getResponderRow($a);
            if(false) { // Changed from 1 == 0 to false for clarity
                updateResponder($arow);
            }
        }
    }
}

$sql = "
SELECT c.*,
s.companyname,
s.address,
s.city,
s.zip,
s.borough,
schoolcode,
s.id as companyid,
date_format(c.startdate, '%W, %M %e, %Y') as date_str,
date_format(c.startdate, '%h:%i %p') as time_str
FROM `class` as c,
company_esi as s
where c.id = '" . db_escape($id) . "'
and c.companyid = s.id
";
$class = db_query_first($sql);
$attendees = get_attendees($id);

// Extract class variables
foreach ($class as $key => $val) {
    ${$key} = $val;
}

if(isset($c) && $c) {
    $code = $c;
}

// Name of the class based on class code.
$crow = getClassRow($id);
$company = getCompanyRow($crow["companyid"]);
$class_names = $allclass_names[$company["iscorp"]];
$name = isset($class_names[$code]) ? $class_names[$code] : '';

if(isset($scheduler_is_contact) && $scheduler_is_contact) {
    $urow = getUserRow($addedby);
    
    if(!isset($firstname) || !$firstname) {
        $firstname = isset($urow["first_name"]) ? $urow["first_name"] : '';
    }
    if(!isset($lastname) || !$lastname) {
        $lastname = isset($urow["last_name"]) ? $urow["last_name"] : '';
    }
    if(!isset($mi) || !$mi) {
        $mi = isset($urow["mi"]) ? $urow["mi"] : '';
    }
    if(!isset($phone) || !$phone) {
        $phone = isset($urow["phone"]) ? $urow["phone"] : '';
    }
    if(!isset($phone_ext) || !$phone_ext) {
        $phone_ext = isset($urow["phone_ext"]) ? $urow["phone_ext"] : '';
    }
    if(!isset($email) || !$email) {
        $email = isset($urow["userid"]) ? $urow["userid"] : '';
    }
    if(!isset($fax) || !$fax) {
        $fax = isset($urow["fax"]) ? $urow["fax"] : '';
    }
}
?>
<?php include "ssi/top.php"; ?>
<?php include "getschooldropdown.php"; ?>
<!--start center content-->

<strong><span class="title">UPDATE CERTIFICATIONS for class <?php echo htmlspecialchars($id); ?></span></strong> &nbsp;&nbsp;
<form name="clform" method='post'>
    <br><hr><br>
    <font color='red'><?php echo isset($err) ? htmlspecialchars($err) : ''; ?></font>
    <table border="0" cellpadding="0" cellspacing="0"><tr><td valign="top">
        <table cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td valign="top">
                    <table cellpadding="0" cellspacing="4" border="0">
                        <tr>
                            <td valign="top" align="right"><span class="copy"><strong>Class:</strong></span></td>
                            <td valign="top"><span class="copy"><?php echo htmlspecialchars($name); ?></span></td>
                        </tr>
                        <tr>
                            <td valign="top" align="right"><span class="copy"><strong>Date:</strong></span></td>
                            <td valign="top"><span class="copy"><?php echo htmlspecialchars($date_str); ?></span></td>
                        </tr>
                        <tr>
                            <td valign="top" align="right"><span class="copy"><strong>Start Time:</strong></span></td>
                            <td valign="top"><span class="copy"><?php echo htmlspecialchars($time_str); ?></span></td>
                        </tr>
                        <tr>
                            <td valign="top" align="right"><span class="copy"><strong><?php echo htmlspecialchars(getSchoolStr("School")); ?>:</strong></span></td>
                            <td valign="top"><span class="copy"><a href='viewcompany.php?id=<?php echo htmlspecialchars($companyid); ?>'><?php echo htmlspecialchars($companyname); ?></a></span></td>
                        </tr>
                        <tr>
                            <td valign="top" align="right"><span class="copy"><strong><?php echo htmlspecialchars(getSchoolStr("School")); ?> Code:</strong></span></td>
                            <td valign="top"><span class="copy"><a href='viewcompany.php?id=<?php echo htmlspecialchars($companyid); ?>'><?php echo htmlspecialchars($schoolcode); ?></a></span></td>
                        </tr>
                        <tr>
                            <td valign="top" align="right"><span class="copy"><strong><?php echo htmlspecialchars(getSchoolStr("Location")); ?>:</strong></span></td>
                            <td valign="top"><span class="copy"><?php echo htmlspecialchars($address); ?>, <?php echo htmlspecialchars($city); ?> <?php echo htmlspecialchars($zip); ?></span></td>
                        </tr>
                        <tr><td colspan=2>
                            <br><br><strong><span class="COPY">ATTENDEES</span></strong>
                            <hr>
                            <br>
                            <b>Number of attendees</b>: <input type='text' size='3' name='numattended' id='numattended' value='<?php echo (isset($company['iscorp']) && $company['iscorp']) ? "" : ""; ?>' onChange='fillSpots(this.value, <?php echo htmlspecialchars($id); ?>)'> <input type='button' name='go' value='Go'>
                            <table>
                                <?php
                                // These are who already attended
                                $res = db_query_rows("select * from responder_training_dates, responders_esi where classid = '" . db_escape($id) . "' and responders_esi.responderid = responder_training_dates.responderid ", "responderid");
                                
                                if(false && isset($company['iscorp']) && $company['iscorp']) { // Changed from 1==0 to false
                                    $i = 0;
                                    foreach($attendees as $a) {
                                        if(!isset($a["attended"]) || !$a["attended"]) continue;
                                        if(isset($res[$a["responderid"]]) && $res[$a["responderid"]]) continue;
                                        $i++;
                                        $arow = get_attendee(isset($a['responderid']) ? $a['responderid'] : 0);
                                ?>
                                <tr><td><?php echo $i; ?>.</td><td><input type='text' id='lastname<?php echo $i; ?>' name='lastname[<?php echo $i; ?>]' onKeyUp='autoCompleteAttendee(event, this.value, <?php echo htmlspecialchars($id); ?>, <?php echo $i; ?>)' value="<?php echo htmlspecialchars((isset($arow['lastname']) ? $arow['lastname'] : '') . ', ' . (isset($arow['firstname']) ? $arow['firstname'] : '')); ?>"></td><td id='namedisplay<?php echo $i; ?>'><?php echo htmlspecialchars((isset($arow['lastname']) ? $arow['lastname'] : '') . ', ' . (isset($arow['firstname']) ? $arow['firstname'] : '')); ?></td><td><input type='hidden' name='hiddencompletion[<?php echo $i; ?>]' id='hiddencompletion<?php echo $i; ?>' value='<?php echo isset($a['responderid']) ? htmlspecialchars($a['responderid']) : ''; ?>'></td></tr>
                                <?php
                                    }
                                }
                                
                                if(isset($res) && is_array($res)) {
                                    foreach($res as $r) {
                                        echo "<tr><td>" . htmlspecialchars((isset($r['lastname']) ? $r['lastname'] : '') . ", " . (isset($r['firstname']) ? $r['firstname'] : '')) . "</td><td><input type='checkbox' name='saveme[]' value='" . htmlspecialchars($r['responderid']) . "' checked></td></tr>";
                                    }
                                }
                                ?>
                            </table>
                            <input type='hidden' id='numalready' value='<?php echo isset($res) ? count($res) : 0; ?>'>
                            <table cellpadding="0" cellspacing="0" border="0" width="470" id="attendeestable">
                            </table>
                            <table cellpadding="0" cellspacing="4" border="0">
                                <input type='submit' name='updatecompletions' value='Update Certifications' onClick='return confirmNames()'>
                                <input type='button' name='' onClick='document.location.href="class_edit.php?id=<?php echo htmlspecialchars($id); ?>"' value='Back to Class'>
                                <input type='hidden' name='okayed' id='okayed' value=''>
                            </table>
                        </form>
                        
                        <BR><BR><BR><BR>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</td>
</tr>
</table>
</td>
</tr>
</table>

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

<script type="text/javascript">
function confirmNames() {
    var numspots = $('#numattended').val();
    var popupnames = "";
    var numnames = 0;
    for(var i = 0; i <= numspots; i++) {
        if($('#namedisplay' + i) && $('#namedisplay' + i).html() > "") {
            popupnames += " - " + $('#namedisplay' + i).html() + "\n";
            numnames++;
        }
    }
    if(popupnames > "") {
        var val = prompt("Are you sure you want to mark " + numnames + " new name(s) certified?\n" + popupnames + "\n If so, please initial below and click OK.");
        if(val > "") {
            document.getElementById("okayed").value = val;
            return true;
        }
        return false;
    }
    return true;
}
</script>