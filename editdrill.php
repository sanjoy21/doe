<?php
include "mysql.php"; 
function db_escape_string($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

if(isset($markcompleted) && $markcompleted)
{
    db_query("update drill set completed = '1' where drillid = " . intval($drillid));
    $appid = db_query_first_cell("select appid from drill where drillid = " . intval($drillid));
    if($appid)
        db_query("update appuploads set archived = 1 where id = " . intval($appid));
    $appid = db_query_first_cell("select appid from servicecall where assocdrillid = " . intval($drillid));
    if($appid)
        db_query("update appuploads set archived = 1 where id = " . intval($appid));
}

if(isset($updatecomment) && $updatecomment)
{
    if(isset($doecomment) && trim($doecomment))
    {
        db_query("insert into drillcomments (drillid, comment, user, commentdate) values('" . intval($drillid) . "', '" . db_escape_string($doecomment) . "', '" . db_escape_string(isset($session_userid) ? $session_userid : '') . "', now())");
    }
}

if(isset($updatesentaeds) && $updatesentaeds)
{
    if(isset($dateaedsent) && $dateaedsent)
    {
        db_query("update drill set dateaedsent = now(), upstracking = '" . db_escape_string(isset($upstracking) ? $upstracking : '') . "' where drillid = " . intval($drillid));
        if(isset($newaeds) && $newaeds)
        {
            $aeds = explode(",", $newaeds);
            foreach($aeds as $new)
            {
                $new = trim($new);
                $cnt = db_query_first_cell("select aedid from aed_esi, company_esi where iscorp = '" . (isset($session_iscorp) ? $session_iscorp : 0) . "' and company_esi.id = clientid and company_esi.deleted = 0 and aed_esi.deleted = 0 and serial = '" . db_escape_string($new) . "'");
                if($cnt)
                {
                    echo "<br><font color='red'>There is already an AED with this serial #: <a target=_blank href='editaed.php?aedid=" . htmlspecialchars($cnt) . "'>" . htmlspecialchars($new) . "</a><br></font>";
                }
                else
                {
                    $sql = "insert into aed_esi (clientid, serial, newinstall) values ('" . intval(isset($id) ? $id : 0) . "','" . db_escape_string($new) . "',1)";
                    db_query($sql);
                }
            }
        }
    }
}

if(isset($update) && $update)
{
    $nextdate = isset($nextdate) ? fixdate($nextdate) : '';
    $drilldate = isset($drilldate) ? fixdate($drilldate) : '';
    
    if(isset($drillid) && $drillid)
    {
        $olddrillrow = db_query_first("select * from drill where drillid = '" . intval($drillid) . "'");
        $thecid = isset($olddrillrow["companyid"]) ? $olddrillrow["companyid"] : 0;
        $ext = ''; // Initialize $ext variable
        
        db_query("update drill set score = '" . db_escape_string(isset($score) ? $score : '') . "', signedby = '" . db_escape_string(isset($signedby) ? $signedby : '') . "', mainentrancesignposted = '" . db_escape_string(isset($mainentrancesignposted) ? $mainentrancesignposted : '') . "', codeblue = '" . db_escape_string(isset($codeblue) ? $codeblue : '') . "', codeblueother = '" . db_escape_string(isset($codeblueother) ? $codeblueother : '') . "', actionneeded = '" . db_escape_string(isset($actionneeded) ? $actionneeded : '') . "', completed = '" . db_escape_string(isset($completed) ? $completed : '') . "', callnumber = '" . db_escape_string(isset($callnumber) ? $callnumber : '') . "', drilldate = '" . db_escape_string($drilldate) . "', drilltime = '" . db_escape_string(isset($drilltime) ? $drilltime : '') . "', comments = '" . db_escape_string(isset($comments) ? $comments : '') . "', participants = '" . db_escape_string(isset($participants) ? $participants : '') . "', nextdate = '" . db_escape_string($nextdate) . "', inspector = '" . db_escape_string(isset($inspector) ? $inspector : '') . "', invoiced = '" . db_escape_string(isset($invoiced) ? $invoiced : '') . "', isdone = '" . db_escape_string(isset($isdone) ? $isdone : '') . "', invoiceno = '" . db_escape_string(isset($invoiceno) ? $invoiceno : '') . "' $ext where drillid = " . intval($drillid));
    }
    else
    {
        $thecid = isset($id) ? $id : 0;
        $drillid = db_query_insert_id("insert into drill (companyid, signedby, mainentrancesignposted, score, codeblue, codeblueother, drilldate, drilltime, comments, sherrycomments, participants, nextdate, inspector, callnumber, invoiced, invoiceno, completed, isdone, shipped, received) values ('" . intval($thecid) . "','" . db_escape_string(isset($signedby) ? $signedby : '') . "', '" . db_escape_string(isset($mainentrancesignposted) ? $mainentrancesignposted : '') . "', '" . db_escape_string(isset($score) ? $score : '') . "','" . db_escape_string(isset($codeblue) ? $codeblue : '') . "','" . db_escape_string(isset($codeblueother) ? $codeblueother : '') . "','" . db_escape_string($drilldate) . "','" . db_escape_string(isset($drilltime) ? $drilltime : '') . "','" . db_escape_string(isset($comments) ? $comments : '') . "', '" . db_escape_string(isset($sherrycomments) ? $sherrycomments : '') . "','" . db_escape_string(isset($participants) ? $participants : '') . "','" . db_escape_string($nextdate) . "','" . db_escape_string(isset($inspector) ? $inspector : '') . "','" . db_escape_string(isset($callnumber) ? $callnumber : '') . "','" . db_escape_string(isset($invoiced) ? $invoiced : '') . "','" . db_escape_string(isset($invoiceno) ? $invoiceno : '') . "','" . db_escape_string(isset($completed) ? $completed : '') . "','" . db_escape_string(isset($isdone) ? $isdone : '') . "','" . db_escape_string(isset($shipped) ? $shipped : '') . "','" . db_escape_string(isset($received) ? $received : '') . "')");
    }

    if(isset($removemanual) && $removemanual)
    {
        db_query("update drill set haspdf = 0 where drillid = '" . intval($drillid) . "'");
    }
    
    if(isset($_FILES["manualupload"]["tmp_name"]) && $_FILES["manualupload"]["tmp_name"])
    {
        move_uploaded_file($_FILES["manualupload"]["tmp_name"], "drillupload/" . intval($drillid) . ".pdf");
        db_query("update drill set haspdf = 1 where drillid = '" . intval($drillid) . "'");
    }
    
    if(isset($completed) && $completed)
    {
        $appid = db_query_first_cell("select appid from drill where drillid = " . intval($drillid));
        if($appid)
            db_query("update appuploads set archived = 1 where id = " . intval($appid));
        $appid = db_query_first_cell("select appid from servicecall where assocdrillid = " . intval($drillid));
        if($appid)
            db_query("update appuploads set archived = 1 where id = " . intval($appid));
    }
    
    if(isset($isdone) && $isdone)
    {
        $sd = db_query_first_cell("select doneby from drill where drillid = " . intval($drillid));
        if(!$sd)
            db_query("update drill set doneby = " . intval(isset($session_id) ? $session_id : 0) . " where drillid = " . intval($drillid));
    }
    
    if(isset($session_userid) && strtolower($session_userid) == "sarahg@emergencyskills.com")
        db_query("update drill set sherrycomments = '" . db_escape_string(isset($sherrycomments) ? $sherrycomments : '') . "' where drillid = " . intval($drillid));
        
    $os = "";
    
    db_query("delete from drill_to_companyid where drillid = '" . intval($drillid) . "'");
    db_query("insert into drill_to_companyid (drillid, companyid, showed) values ('" . intval($drillid) . "', '" . intval($thecid) . "', 1)");
    
    if(isset($otherschools) && is_array($otherschools) && count($otherschools))
    {
        foreach($otherschools as $o=>$showed)
        {
            db_query("insert into drill_to_companyid (drillid, companyid, showed) values ('" . intval($drillid) . "', '" . intval($o) . "', " . intval($showed) . ")");
        }
    }
    
    db_query("update drill set followup = '" . db_escape_string(isset($followup) ? $followup : '') . "', notrained = '" . db_escape_string(isset($notrained) ? $notrained : '') . "', other = '" . db_escape_string(isset($other) ? $other : '') . "' where drillid = " . intval($drillid));

    if(!isset($olddrillrow["followup"]) && isset($followup) && $followup)
    {
        if(!isset($olddrillrow))
            $comrow = getCompanyRow(isset($id) ? $id : 0);
        else
            $comrow = getCompanyRow(isset($olddrillrow["companyid"]) ? $olddrillrow["companyid"] : 0);
            
        if(isset($comrow["zip"]))
        {
            $trainers = getTrainersForZip($comrow["zip"]);
            if(is_array($trainers))
            {
                foreach($trainers as $trow)
                {
                    $body = "The following school failed their code blue drill on " . date("m/d/Y", strtotime($drilldate)) . ":\n" . 
                            (isset($comrow["companyname"]) ? $comrow["companyname"] : '') . "\n" . 
                            (isset($comrow["address"]) ? $comrow["address"] : '') . "\n" . 
                            (isset($comrow["borough"]) ? $comrow["borough"] : '') . " " . (isset($comrow["zip"]) ? $comrow["zip"] : '') . "\n" . 
                            "Please return in 3-4 weeks for a followup drill.\n";
                    
                    if(isset($trow["userid"]))
                        sendMail($trow["userid"], "Follow up drill needed", $body, "info@emergencyskills.com");
                }
            }
        }
    }
    
    if(isset($dateaedsent) && $dateaedsent)
    {
        db_query("update drill set dateaedsent = now(), upstracking = '" . db_escape_string(isset($upstracking) ? $upstracking : '') . "' where drillid = " . intval($drillid));
        if(isset($newaeds) && $newaeds)
        {
            $aeds = explode(",", $newaeds);
            foreach($aeds as $new)
            {
                $new = trim($new);
                $sql = "insert into aed_esi (clientid, serial, newinstall) values ('" . intval(isset($id) ? $id : 0) . "','" . db_escape_string($new) . "',1)";
                db_query($sql);
            }
        }
    }
        
    if(isset($doecomment) && trim($doecomment))
    {
        db_query("update drill set doecomment = '" . db_escape_string($doecomment) . "', doecommentwho = '" . db_escape_string(isset($session_userid) ? $session_userid : '') . "', doecommentdate = now() where drillid = " . intval($drillid));
    }

    db_query("delete from aed_to_drill where drillid = '" . intval($drillid) . "'");
    
    if(isset($serials) && is_array($serials))
    {
        foreach($serials as $ser)
        {
            db_query("insert into aed_to_drill (drillid, serial) values (" . intval($drillid) . ", '" . db_escape_string($ser) . "')");
        }
    }

    if(isset($notifysherry) && $notifysherry)
    {
        $crow = getCompanyRow(isset($id) ? $id : 0);
        $unavail = "";

        $drill_row = db_query_first("select * from drill where drillid = " . intval($drillid));
        $scappid = db_query_first_cell("select appid from servicecall where companyid = '" . intval(isset($drill_row["companyid"]) ? $drill_row["companyid"] : 0) . "' and servicecalldate = '" . db_escape_string(isset($drill_row["drilldate"]) ? $drill_row["drilldate"] : '') . "' and companyid > ''");
        
        if($scappid)
        {
            $aeds = getAppServiceCallRows($scappid);
            
            if(is_array($aeds))
            {
                foreach($aeds as $tmparow)
                {
                    $aedvalues = getAppServiceCallDetailRows(isset($tmparow["id"]) ? $tmparow["id"] : 0);
                    $realaedrow = getAedRow(isset($aedvalues["aedid"]) ? $aedvalues["aedid"] : 0);
                    
                    if(isset($aedvalues["unit_unavailable"]) && $aedvalues["unit_unavailable"] == "yes")
                    {
                        $unavail .= "Unavailable AED: " . (isset($realaedrow["serial"]) ? $realaedrow["serial"] : '') . "\n";
                        db_query("update aed_esi set aedservicehistory = concat(aedservicehistory, '\nAED Marked Unavailable " . (isset($drill_row["drilldate"]) ? $drill_row["drilldate"] : '') . "\n') where aedid = " . intval(isset($aedvalues["aedid"]) ? $aedvalues["aedid"] : 0));
                    }
                    
                    if(isset($realaedrow["aedmissing"]) && $realaedrow["aedmissing"])
                    {
                        $unavail .= "AED Missing: " . (isset($realaedrow["serial"]) ? $realaedrow["serial"] : '') . "\n";
                    }
                }
            }
        }
        
        sendMail("sarahg@emergencyskills.com", (isset($crow["companyname"]) ? $crow["companyname"] : '') . ": Action Needed", "Drill " . $drillid . " needs action.\n https://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/editdrill.php?drillid=" . $drillid . "\n" . $unavail, "info@emergencyskills.com");
    }

    
    if(isset($notifydoe) && $notifydoe)
    {
        $drill_row = db_query_first("select * from drill where drillid = " . intval($drillid));
        $crow = getCompanyRow(isset($id) ? $id : 0);
        $initial = "";

        if(isset($drill_row["notrained"]) && $drill_row["notrained"])
        {
            $initial .= "The following school has failed a code blue drill.  We recommend immediate training for this location.";
        }
        
        if(isset($drill_row["other"]) && $drill_row["other"])
        {
           $initial .= "Failure Comments: " . $drill_row["other"] . "\n";
        }
        
        db_query("update drill set lastnotified = now() where drillid = " . intval($drillid));        
        db_query("insert into drillnotifies (drillid, notifydate) values ('" . intval($drillid) . "', now())");
        
        $subject = (isset($battreq) && $battreq ? (isset($crow["companyname"]) ? $crow["companyname"] : '') . ": Accessory Request" : (isset($crow["companyname"]) ? $crow["companyname"] : '') . ": ALIVE!net Alert");
        if(isset($notrained) && $notrained) 
            $subject = (isset($crow["companyname"]) ? $crow["companyname"] : '') . " ALIVE!net ALERT: Failed Code Blue Drill";
            
        $body = $initial . "\n" . 
                (isset($drill_row["drilldate"]) ? $drill_row["drilldate"] : '') . "\n" . 
                (isset($crow["companyname"]) ? $crow["companyname"] : '') . "\n" . 
                (isset($crow["schoolcode"]) ? $crow["schoolcode"] : '') . "\n" . 
                (isset($crow["address"]) ? $crow["address"] : '') . " " . (isset($crow["city"]) ? $crow["city"] : '') . ", " . (isset($crow["zip"]) ? $crow["zip"] : '') . "\n" . 
                "ESI Drill/Inspector: " . (isset($drill_row["inspector"]) ? $drill_row["inspector"] : '') . "\n" . 
                "Principal Name: " . (isset($crow["principalname"]) ? $crow["principalname"] : '') . "\n" . 
                "Principal Email: " . (isset($crow["principalemail"]) ? $crow["principalemail"] : '') . "\n" . 
                "School Phone: " . (isset($crow["schoolphone"]) ? $crow["schoolphone"] : '') . "\n" . 
                "Emergency Skills, Inc.";

        mail("hthomps@schools.nyc.gov, cmcgee3@schools.nyc.gov", $subject, $body, "From:rebekah@emergencyskills.com\nCc:rebekah@emergencyskills.com");
    }
    

    if(isset($senddrillpassed) && $senddrillpassed)
    {
        $drillrow = getDrillRow(isset($drillid) ? $drillid : 0);
        $crow = getCompanyRow(isset($drillrow["companyid"]) ? $drillrow["companyid"] : 0);
        $contents = file_get_contents("drillpassedemail.html");
        $principalemail = isset($crow["principalemail"]) ? $crow["principalemail"] : '';
        
        if(!$principalemail)
        {
            echo "<font color='red'>No email for this principal!</font>";
            $redirect = "";
        }
        else
        {
            $resp = "http://".SUB_DOE. "." . URL_WITHOUT_SUBDOMAIN. "/response_plan.php?id=" . (isset($crow["id"]) ? $crow["id"] : '');
            $contents = str_replace("SITERESPONSELINK", $resp, $contents);
            $newaed = "http://".SUB_DOE. "." . URL_WITHOUT_SUBDOMAIN. "/printaedsign.php?id=" . (isset($crow["id"]) ? $crow["id"] : '');
            $contents = str_replace("SITERESPONSEPOSTER", $newaed, $contents);
            $code = "http://".SUB_DOE. "." . URL_WITHOUT_SUBDOMAIN. "/billingworksheet.php?d=" . (isset($drillrow["drilldate"]) ? $drillrow["drilldate"] : '') . "&schoolid=" . (isset($crow["id"]) ? $crow["id"] : '');
            $contents = str_replace("CODEBLUEDRILL", $code, $contents);

            $companies = db_query_array("select companyid, principalemail from drill_to_companyid, company_esi where companyid = company_esi.id and drillid = " . intval($drillid) . " and showed = 1", "companyid", "principalemail");
            $names = "";
            
            if(is_array($companies))
            {
                foreach($companies as $tmpcompanyid=>$principalemail)
                {
                    $tmpcompanyname = getCompanyName($tmpcompanyid);
                    $attendees = getNonExpiredResponders($tmpcompanyid);
                    
                    if(is_array($attendees))
                    {
                        foreach($attendees as $arow)
                        {
                            $dt = date("m/d/Y", strtotime((isset($arow["trainingdate"]) ? $arow["trainingdate"] : '') . " + 2 years"));
                            $names .= "<tr><td>" . (isset($arow["firstname"]) ? htmlspecialchars($arow["firstname"]) : '') . " " . (isset($arow["lastname"]) ? htmlspecialchars($arow["lastname"]) : '') . "</td><td>" . $dt . "</td><td>" . htmlspecialchars($tmpcompanyname) . "</td></tr>\n";
                        }
                    }
                }
            }
            
            $contents = str_replace("NAMESANDDATES", $names, $contents);

            if(is_array($companies))
            {
                foreach($companies as $principalemail)
                {
                    sendHTMLMail($principalemail, "Congratulations on your successful Code Blue Drill!", $contents, "info@emergencyskills.com"); 
                }
            }
            
            db_query("update drill set lastdrillpassedsent = now() where drillid = " . intval($drillid));
        }
    }
    
    if(isset($redirect) && $redirect && !isset($dontredirect))
    {
        Header("location: " . $redirect);
        exit;
    }
    
    if(isset($dontredirect) && $dontredirect) 
        $err = "<br><font color='red'>Saved.</font><br>";
}

if(isset($delete) && $delete)
{
    db_query("delete from drill where drillid = " . intval($drillid));
    Header("location: " . (isset($redirect) ? $redirect : ''));
    exit;
}

//get info for the form
if(isset($drillid) && $drillid)
{
    $drill_row = db_query_first("select * from drill where drillid = " . intval($drillid));
    $id = isset($drill_row["companyid"]) ? $drill_row["companyid"] : 0;
    
    if(!isset($drill_row["drillid"]) || !$drill_row["drillid"])
    {
        echo "No drill " . htmlspecialchars($drillid);
        exit;
    }
}

if(!isset($drill_row) || !is_array($drill_row))
{
    $drill_row = array();
}

$company_row = getCompanyRow(isset($id) ? $id : 0);
$scho = getSchoolsInCampus(isset($company_row["campusid"]) ? $company_row["campusid"] : 0, isset($company_row["id"]) ? $company_row["id"] : 0);

if(isset($scho) && is_array($scho) && count($scho))
{
     $otherschools = db_query_array("select companyid, showed from drill_to_companyid where drillid = '" . intval(isset($drillid) ? $drillid : 0) . "'", "companyid", "showed");
}

if(isset($senddoemail) && $senddoemail)
{
    $subject = "Code Blue Drill - " . getCampusName(isset($company_row["campusid"]) ? $company_row["campusid"] : 0);
    $body = "A drill was performed at \"" . getCampusName(isset($company_row["campusid"]) ? $company_row["campusid"] : 0) . "\". The following schools did not participate in the drill. \n\n";
    
    if(isset($scho) && is_array($scho))
    {
        foreach($scho as $sc)
        {
            if((!isset($otherschoolsc[$sc["id"]]) || (isset($otherschools[$sc["id"]]) && $otherschools[$sc["id"]] < 0)))
            {
                $body .= (isset($sc["companyname"]) ? $sc["companyname"] : '') . " - " . (isset($sc["principalname"]) ? $sc["principalname"] : '') . " - " . (isset($sc["schoolphone"]) ? $sc["schoolphone"] : '') . "\n";
            }
        }
    }
    
    $body .= "\n" . (isset($drill_row["comments"]) ? $drill_row["comments"] : '');
    mail("sarahg@emergencyskills.com", $subject, $body, "From: info@emergencyskills.com");
    mail("hthomps@schools.nyc.gov, cmcgee3@schools.nyc.gov", $subject, $body, "From: info@emergencyskills.com");
    $err = "<br><font color='red'>Sent!</font>";
}

// Continue with the HTML form part - truncated for brevity...
// The HTML form would need similar updates with isset() checks and htmlspecialchars()

// For the remainder of the HTML form, you would need to:
// 1. Change all <? to <?php
// 2. Change all <?= to <?php echo
// 3. Add isset() checks for all variables
// 4. Add htmlspecialchars() for all output
// 5. Add intval() for numeric values in URLs and forms

// Due to the length, I'm showing the pattern for the rest:
?>

<?php
// The rest of the HTML form would continue here with similar conversions
// For example:
?>

<?php 
//$noleftnav = 1;
include "ssi/top.php"; ?>
<?php echo isset($err) ? $err : ''; ?>
<!--start center content-->
<script LANGUAGE="JavaScript">
<!--
function confirmDelete()
{
var agree=confirm("Are you sure you wish to delete?");
if (agree) {
return true ;
}
else
{
return false ;
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


function validRequired(formField,fieldLabel)
{
var result = true;

if (formField.value == "")
{
alert('Please enter a value for the "' + fieldLabel +'" field.');
formField.focus();
result = false;
}

return result;
}

function allDigits(str)
{
return inValidCharSet(str,"0123456789");
}

function inValidCharSet(str,charset)
{
var result = true;

for (var i=0;i<str.length;i++)
if (charset.indexOf(str.substr(i,1))<0)
{
result = false;
break;
}

return result;
}

function isValidShortDate(formField,fieldLabel,required)
{
    if (required && (formField.value.length>7))
    {
        alert('Please enter a date in the format MM/YYYY for the "' + fieldLabel +'" field.');
formField.focus();
return false;
    }
var result = true;
var formValue = formField.value;

if (required && !validRequired(formField,fieldLabel))
result = false;
  
 if (result && (formField.value.length>0))
 {
 var elems = formValue.split("/");
 
 result = (elems.length == 2); // should be two components
 var expired = false;
 
 if (result)
 {
 var month = parseInt(elems[0],10);
 var year = parseInt(elems[1],10);
 
 if (elems[1].length == 2)
 year += 2000;
 
 var now = new Date();
 
 var nowMonth = now.getMonth() + 1;
 var nowYear = now.getFullYear();
 
 
 
result = allDigits(elems[0]) && (month > 0) && (month < 13) &&
 allDigits(elems[1]) && ((elems[1].length == 2) || (elems[1].length == 4));
 }
 
  if (!result)
 {
 alert('Please enter a date in the format MM/YYYY for the "' + fieldLabel +'" field.');
formField.focus();
}
} 
return result;
}
</script>

<?php
if(!isset($redirect) || !$redirect)
    $redirect = "/viewcompany.php?id=" . (isset($id) ? $id : '');
?>

<form onsubmit="return validateUSPersonalInfo(this)"  method="post" enctype='multipart/form-data'>
<input type="hidden" name ="redirect" value="<?php echo htmlspecialchars(isset($redirect) ? $redirect : ''); ?>">
<input type="hidden" name ="drillid" value="<?php echo htmlspecialchars(isset($drillid) ? $drillid : ''); ?>">
<input type="hidden" name ="dontredirect" value="1">
<input type="hidden" name ="id" value="<?php echo htmlspecialchars(isset($id) ? $id : ''); ?>">
<br>
<?php if(isset($specialadmin) && $specialadmin) { ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="schools.php">&laquo; Back to Admin Main</a></strong></span></td>
</tr>
</table>
<?php } ?>
<strong>THIS DRILL IS FOR:</strong><br>
<?php echo "<a href='viewcompany.php?id=" . (isset($id) ? htmlspecialchars($id) : '') . "'>" . (isset($company_row['companyname']) ? htmlspecialchars($company_row['companyname']) : '') . "</a><br>" . (isset($company_row['address']) ? htmlspecialchars($company_row['address']) : '') . "<br>" . (isset($company_row['floor']) ? htmlspecialchars($company_row['floor']) : '') . "<br>" . (isset($company_row['city']) ? htmlspecialchars($company_row['city']) : '') . ", " . (isset($company_row['state']) ? htmlspecialchars($company_row['state']) : '') . " " . (isset($company_row['zip']) ? htmlspecialchars($company_row['zip']) : ''); ?>
<br><br>
        <?php if(isset($drill_row["appid"]) && $drill_row["appid"]) { ?>
    <a href='appdrill.php?id=<?php echo htmlspecialchars($drill_row["appid"]); ?>'><b>View Drill Worksheet from App</b></a><br><br>
                 <a href='http://<?php echo SUB_DOE.".". URL_WITHOUT_SUBDOMAIN; ?>/billingworksheet.php?d=<?php echo htmlspecialchars(isset($drill_row["drilldate"]) ? $drill_row["drilldate"] : ''); ?>&schoolid=<?php echo htmlspecialchars(isset($id) ? $id : ''); ?>'>View Billing Worksheet</a><br><br>
    <?php } ?>
    <?php
            $scid = db_query_first_cell("select servicecallid from servicecall where companyid = '" . intval(isset($drill_row["companyid"]) ? $drill_row["companyid"] : 0) . "' and servicecalldate = '" . db_escape_string(isset($drill_row["drilldate"]) ? $drill_row["drilldate"] : '') . "' and companyid > ''");
if(isset($scid) && $scid){
?>
        <A href='editservicecall.php?servicecallid=<?php echo htmlspecialchars($scid); ?>'><b>View Related Service Call</b></a><br><br>
<?php
}
?>

<table cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><strong>DRILL Information</strong></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>DRILL Date*:</strong><br><input type="text" size="10" VALUE="<?php echo htmlspecialchars(isset($drill_row['drilldate']) ? $drill_row['drilldate'] : ''); ?>" maxlength="10" name="drilldate" style="font-size: 10px;  font-family: verdana;"> Time: <input type="text" size="10" VALUE="<?php echo htmlspecialchars(isset($drill_row['drilltime']) ? $drill_row['drilltime'] : ''); ?>" maxlength="10" name="drilltime" style="font-size: 10px;  font-family: verdana;"> </span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Next DRILL Date*:</strong><br><input type="text" size="40" VALUE="<?php echo htmlspecialchars(isset($drill_row['nextdate']) ? $drill_row['nextdate'] : ''); ?>" maxlength="50" name="nextdate" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Call Number:</strong><br><input type="text" size="5" VALUE="<?php echo htmlspecialchars(isset($drill_row['callnumber']) && $drill_row['callnumber'] ? $drill_row['callnumber'] : (isset($drill_row['drillid']) ? $drill_row['drillid'] : '')); ?>" maxlength="50" name="callnumber" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<?php
        $myaeds = getAedsForDrill(isset($drillid) ? $drillid : 0);
        $aed_rows = getAedRows(isset($id) ? $id : 0, false, isset($company_row["campusid"]) ? $company_row["campusid"] : 0, isset($drillid) ? $drillid : 0); 
?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>AED:</strong><br><select name="serials[]" multiple class=copy>
<option value=''>Please Choose</option>
<?php
$already = array();
if(isset($myaeds) && is_array($myaeds))
{
    foreach($myaeds as $ser => $throwaway)
    {
        printOption($ser, $ser, $ser);
        $already[$ser] = 1;
    }
}

if(isset($aed_rows) && is_array($aed_rows))
{
    foreach($aed_rows as $a)
    {
        if(isset($already[$a['serial']]) && $already[$a['serial']])
            continue;
        printOption(isset($a['serial']) ? $a['serial'] : '', isset($a['serial']) ? $a['serial'] : '');
    }
}
?>
</select></span></td>
</tr>
<?php
if(isset($scho) && is_array($scho) && count($scho))
{
?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Other participating <?php echo getSchoolStr("Schools"); ?>:</strong><br>
<table>
<?php
foreach($scho as $sc)
{
    if(isset($sc['id']) && $sc['id'] != (isset($drill_row['companyid']) ? $drill_row['companyid'] : 0))
    {
        $already = (!isset($drillid) || !$drillid || (isset($otherschools[$sc['id']]) && $otherschools[$sc['id']] > 0)) ? "CHECKED" : "";
        $alreadyno = isset($drillid) && $drillid && isset($otherschools[$sc['id']]) && $otherschools[$sc['id']] <= 0 ? "CHECKED" : "";
        echo "<tr><td><a href='viewcompany.php?id=" . htmlspecialchars($sc['id']) . "'>" . htmlspecialchars(isset($sc['companyname']) ? $sc['companyname'] : '') . "</a></td><td> <input type='radio' name='otherschools[" . htmlspecialchars($sc['id']) . "]' value='1' $already > Yes <input type='radio' name='otherschools[" . htmlspecialchars($sc['id']) . "]' value='-1' $alreadyno > No</td></tr>";
    }
}
?>
</table>
</span></td>
</tr>
<?php } ?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Score:</strong><br><input type="text" size="40" VALUE="<?php echo htmlspecialchars(isset($drill_row['score']) ? $drill_row['score'] : ''); ?>" maxlength="50" name="score" style="font-size: 10px;  font-family: verdana;"><br>

Failed Drill: <input type='checkbox' <?php echo isset($drill_row["notrained"]) && $drill_row["notrained"] ? "CHECKED" : ""; ?> name='notrained' value='1'> &nbsp;&nbsp;&nbsp;
<br>Comments: <textarea cols='40' name='other'><?php echo htmlspecialchars(isset($drill_row['other']) ? $drill_row['other'] : ''); ?></textarea>&nbsp;&nbsp;&nbsp;<br>
<?php if(isOverallAdmin() || (isset($session_userid) && (strtolower($session_userid) == "amopper@schools.nyc.gov" || strtolower($session_userid) == "cmcgee3@schools.nyc.gov" || strtolower($session_userid) == "hthomps@schools.nyc.gov"))) {  ?>
<?php
    if(isset($drillid) && $drillid)
        $comm = db_query_rows("select * from drillcomments where drillid = '" . intval($drillid) . "'");
    
    if(isset($comm) && is_array($comm))
    {
        foreach($comm as $commrow)
        {
?><br>DOE Comment: <?php echo htmlspecialchars(isset($commrow['comment']) ? $commrow['comment'] : ''); ?> at <?php echo htmlspecialchars(isset($commrow["commentdate"]) ? $commrow["commentdate"] : ''); ?> by <?php echo htmlspecialchars(isset($commrow["user"]) ? $commrow["user"] : ''); ?><br>
<?php 
        }
    }
    ?>
<br>New DOE Comment: <input type='text' name='doecomment' value=''> <input type='submit' name='updatecomment' value='Save'>
<?php } ?>
</span></td>
</tr>
<?php if(isset($drillid) && $drillid) { ?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Accessory Request:</strong>
<?php 
$f = db_query_first("select * from accessoryrequests where drillid = " . intval($drillid));
if($f)
{
    echo "<a href='editaccessoryrequest.php?accessoryrequestid=" . htmlspecialchars(isset($f['id']) ? $f['id'] : '') . "'>AR" . htmlspecialchars(isset($f['id']) ? $f['id'] : '') . "</a>";
}   
else
{
    echo "<a target=_blank href='editaccessoryrequest.php?id=" . htmlspecialchars(isset($drill_row['companyid']) ? $drill_row['companyid'] : '') . "&did=" . htmlspecialchars(isset($drill_row['drillid']) ? $drill_row['drillid'] : '') . "'>add new</a>";
}
?>
</span></td>
</tr>
<?php } ?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Participants:</strong><br><textarea name="participants" rows="5" cols="50" style="font-size: 10px;  font-family: verdana;"><?php echo htmlspecialchars(isset($drill_row['participants']) ? $drill_row['participants'] : ''); ?></textarea></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>ESI Drill/Inspector:</strong><br><select name="inspector">
<option value=''>Please Choose</option>
<option <?php echo isset($drill_row["inspector"]) && $drill_row["inspector"] ? "SELECTED" : ""; ?> value="<?php echo htmlspecialchars(isset($drill_row['inspector']) ? $drill_row['inspector'] : ''); ?>"><?php echo htmlspecialchars(isset($drill_row['inspector']) ? $drill_row['inspector'] : ''); ?></option>
<?php 
$opts = db_query_array("select value from esioptionvalues where datatype = 'trainer' order by priority, value", "value", "value");
if(isset($opts) && is_array($opts))
{
    foreach($opts as $o) 
    echo "<option value=\"" . htmlspecialchars($o) . "\">" . htmlspecialchars($o) . "</option>";
}
?>
</select>
</span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Signed By:</strong><br><input name="signedby" value="<?php echo htmlspecialchars(isset($drill_row['signedby']) ? $drill_row['signedby'] : ''); ?>">
</span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Main Entrance AED Sign In Posted:</strong><br><input name="mainentrancesignposted" type='checkbox' value="1" <?php echo isset($drill_row['mainentrancesignposted']) && $drill_row['mainentrancesignposted'] ? "CHECKED" : ""; ?>>
</span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Method for calling Code Blue:</strong><br><input type='radio' name="codeblue" value='PA' <?php echo isset($drill_row['codeblue']) && $drill_row['codeblue']=="PA" ? "CHECKED" : ""; ?>> PA&nbsp;&nbsp;&nbsp;<input type='radio' name="codeblue" value='Cell Phone' <?php echo isset($drill_row['codeblue']) && $drill_row['codeblue']=="Cell Phone" ? "CHECKED" : ""; ?>> Cell Phone&nbsp;&nbsp;&nbsp;<input type='radio' name="codeblue" value='Radio' <?php echo isset($drill_row['codeblue']) && $drill_row['codeblue']=="Radio" ? "CHECKED" : ""; ?>> Radio&nbsp;&nbsp;&nbsp;<input type='radio' name="codeblue" value='Other' <?php echo isset($drill_row['codeblue']) && $drill_row['codeblue']=="Other" ? "CHECKED" : ""; ?>> Other <input type='text' name='codeblueother' value="<?php echo htmlspecialchars(isset($drill_row['codeblueother']) ? $drill_row['codeblueother'] : ''); ?>">&nbsp;&nbsp;&nbsp;</span></td>
</tr>

<tr><td class='copy' bgcolor="#E2DFDF">
<table width='500'>

<tr><td class='copy'><strong>Completed: </strong></td><td class='copy'><input type='checkbox' class='copy' name='completed' value="1" <?php echo isset($drill_row["completed"]) && $drill_row["completed"] ? "checked" : ""; ?>>&nbsp;&nbsp;&nbsp;</td></tr>
<tr><td class=copy><strong>Notify Sarah:</strong> </td><td class=copy><input type='checkbox' name='notifysherry' value='1'></td></tr>
<tr><td class='copy'><strong>Done:</strong></td><td class=copy> <input type='checkbox' class='copy' name='isdone' value="1" <?php echo isset($drill_row["isdone"]) && $drill_row["isdone"] ? "checked" : ""; ?>> </td></tr>
<tr><td class=copy><strong>Action Needed:</strong></td><td class=copy> <input type='checkbox' class='copy' name='actionneeded' value="1" <?php echo isset($drill_row["actionneeded"]) && $drill_row["actionneeded"] ? "checked" : ""; ?>> </td></tr>
<tr><td class=copy><strong>Invoiced: </strong></td><td class=copy><input type='checkbox' class='copy' name='invoiced' value="1" <?php echo isset($drill_row["invoiced"]) && $drill_row["invoiced"] ? "checked" : ""; ?>></td></tr>
<?php if(isset($company_row["iscorp"]) && !$company_row["iscorp"]) { ?>
<tr><td class=copy><strong>Notify DOE:</strong> </td><td class=copy><nobr><input type='checkbox' name='notifydoe' value='1'> (Last Notified: <?php echo isset($drill_row["lastnotified"]) && $drill_row["lastnotified"] ? getFormattedDateWTime($drill_row["lastnotified"]) : "N/A"; ?> <?php echo isset($drillid) ? getOtherNotifies($drillid, isset($drill_row["lastnotified"]) ? $drill_row["lastnotified"] : '') : ''; ?> )</td></tr>
<?php } ?>
<tr><td class=copy><strong>Return for Follow-up Drill in 3-4 weeks:</strong> </td><td class=copy><input type='checkbox' name='followup' value='1' <?php echo isset($drill_row["followup"]) && $drill_row["followup"] ? "CHECKED" : ""; ?>></td></tr>
<tr><td class=copy><strong>Send Drill Passed Email:</strong> </td><td class=copy><input type='checkbox' name='senddrillpassed' value='1'>  Last Sent: <?php echo isset($drill_row["lastdrillpassedsent"]) ? htmlspecialchars($drill_row["lastdrillpassedsent"]) : ''; ?></td></tr>
</table>
</td></tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Invoice No:</strong><br><input type='text' name="invoiceno" size='10' maxlength='10' style="font-size: 10px;  font-family: verdana;" value="<?php echo htmlspecialchars(isset($drill_row['invoiceno']) ? $drill_row['invoiceno'] : ''); ?>"></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Comments:</strong><br><textarea rows="5" cols='50' name="comments" style="font-size: 10px;  font-family: verdana;"><?php echo htmlspecialchars(isset($drill_row['comments']) ? $drill_row['comments'] : ''); ?></textarea></span></td>
</tr>
<?php if(isOverallAdmin()) { ?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Manual PDF Upload:</strong><br><input type='file' name='manualupload'></span><br>
<?php if(isset($drill_row["haspdf"]) && $drill_row["haspdf"]) { ?>
<a target='_blank' href='drillupload/<?php echo htmlspecialchars(isset($drill_row['drillid']) ? $drill_row['drillid'] : ''); ?>.pdf'>View Current PDF</a><br>
<input type='checkbox' name='removemanual' value='1'> Remove Current?
<?php } ?>
</td>
</tr>
<?php } ?>
<?php if(isset($session_userid) && $session_userid == "sarahg@emergencyskills.com") { ?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Comments For Sarah:</strong><br><textarea rows="5" cols='50' name="sherrycomments" style="font-size: 10px;  font-family: verdana;"><?php echo htmlspecialchars(isset($drill_row['sherrycomments']) ? $drill_row['sherrycomments'] : ''); ?></textarea></span></td>
</tr>
<?php } ?>

<tr>
<td valign="top" bgcolor="#FFFFFF" colspan="2">
                <br>
<?php if(!isset($readonly) || !$readonly) { ?>
                <div align="center">
                    <input type="submit" name="update" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if(isset($drillid) && $drillid && isOverallAdmin()){ ?>
                     <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
<?php if(isset($session_iscorp) && !$session_iscorp) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='senddoemail' value="Send DOE email about schools that don't show"> <?php } ?>

<?php } ?>
                </div>
<?php } else if(isset($drillid) && $drillid && (!isset($drill_row["isdone"]) || !$drill_row["isdone"])) { ?>
                <div align="center">
                     <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
                </div>
<?php } ?>
                </td>
</tr>
</table>
                <?php 
                $assoc = db_query_rows("select * from servicecall where fromdrill = 1 and assocdrillid = '" . intval(isset($drillid) ? $drillid : 0) . "'");
                $noheaderorfooter = 1;
                
                if(isset($assoc) && is_array($assoc))
                {
                    foreach($assoc as $a)
                    {
                        $servicecallid = isset($a['servicecallid']) ? $a['servicecallid'] : 0;
                        include "editservicecall.php";
                    }
                }
                ?>                
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