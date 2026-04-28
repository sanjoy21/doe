<?php
require_once('mysql.php');
$crow = getClassRow($id);
$corow = getCompanyRow($crow["companyid"]);

if ($corow["iscorp"]) {
    include "packing_sheet_corp.php";
    exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">
html {
margin: 0;
}
body {
margin: 0;
}

.dotTop { border: 1pt dashed #666666;
}

</style>
<link rel="stylesheet" href="https://<?php echo SUB_DOE. "." . URL_WITHOUT_SUBDOMAIN; ?>/css/style.css">
<style type="text/css">

P.breakhere {page-break-before: always}
.style1 {
 color: #000000;
 font-size: 14px;
}
body {
 margin-top: 0px;
 margin-bottom: 0px;
 margin-left: 0px;
 margin-right: 0px;
}
.style5 {
 font-size: 10px;
 font-style: italic;
}
.style7 {font-size: 14px; text-decoration: none; line-height: 12px; }

</style>
</head>

<body>
<table width="910" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><img src="images/1x1.gif" width="10" height="1" /></td>
    <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td><table cellpadding=0 cellspacing=0 border=0><tr><td><img src="images/roster_logo.jpg" height="80" /></td><td style="color: #097bb9">emergency skills, inc<br>
305 7th Avenue, Suite 1100
<br>
new york, ny 10001<br>
<span  style='font-size:8px'>212-564-6833 (tel)<br>
212-564-6793 (fax)<br></span>
<a style="color: #097bb9" href='http://www.emergencyskills.com/'>www.emergencyskills.com</a></td></tr></table></td>

            <td class="copy"><table><tr><td><strong><span class="style7">HOST SCHOOL/ORGANIZATION: </span></strong></td></tr><tr><td><span class="style7"><?php echo htmlspecialchars($corow["companyname"]); ?>  <?php echo htmlspecialchars($corow["schoolcode"]); ?></span></td></tr>
<tr><td><strong><span class="style1">SITE CONTACT: </span></strong><span class="style1"><?php echo htmlspecialchars($corow["contactname"]); ?></span></td></tr>
</table>
</td>
            </tr>
          <tr>
            <td class="title"></td>
            </tr>
    </table>
      <span class="copy">
<hr />
      </span>
    <table width="100%" border="0" cellspacing="4" cellpadding="0">
      <tr>
<?php
if (isset($allclass_names[$corow["iscorp"]])) {
    $mynames = $allclass_names[$corow["iscorp"]];
} else {
    $mynames = array();
}
?>
        <td height="20" width=200 class="copy"><nobr><strong>Program Type:</strong> <?php 
            if (isset($mynames[$crow["code"]])) {
                echo htmlspecialchars($mynames[$crow["code"]]);
            }
        ?></td>
            <td width="160" class="copy" ><nobr><strong>Session  Date:</strong> <?php echo fixdatefordisplay($crow["startdate"], true); ?></td>
            <td width="150" class="copy"><nobr><strong>Start  Time:</strong> <?php 
                if (isset($crow["startdate"])) {
                    echo date("h:i A", strtotime($crow["startdate"]));
                }
            ?></td>
            <td height="20" class="copy"><nobr><strong>End  Time:</strong> <?php 
                if (isset($crow["startdate"]) && isset($crow["code"])) {
                    echo date("h:i A", getEndTime($crow["startdate"], $crow["code"]));
                }
            ?></td>
          </tr>
      <tr>
</table>

<span class='copy'>
<?php
$currenttrainers = getTrainers($id);
$i = 1;
if (isset($currenttrainers) && is_array($currenttrainers)) {
    foreach ($currenttrainers as $t) {
        if (isset($t["trainerid"])) {
            $trow = getUserRow($t["trainerid"]);
            echo $i . ". " . (isset($trow["first_name"]) ? $trow["first_name"] : "") . " " . (isset($trow["last_name"]) ? $trow["last_name"] : "") . "<br>";
            $i++;
        }
    }
}

$aeds = getAedRows($crow["companyid"]);
$numadult = 0;
$numped = 0;
$sixmonths = mktime(0, 0, 0, date("m") + 6);
// echo(date("y-m-d", $sixmonths));
$str = "<br>AED Model(s): ";
$first = true;
if (isset($aeds) && is_array($aeds)) {
    foreach ($aeds as $arow) {
        // echo($arow["padaexpiration"] . "<br>");
        if (isset($arow["padaexpiration"]) && $arow["padaexpiration"] && $arow["padaexpiration"] != "0000-00-00" && strtotime($arow["padaexpiration"]) < $sixmonths) {
            $numadult++;
        }
        if (isset($arow["padbexpiration"]) && $arow["padbexpiration"] && $arow["padbexpiration"] != "0000-00-00" && strtotime($arow["padbexpiration"]) < $sixmonths) {
            $numadult++;
        }
        if (isset($arow["pediatricpads"]) && $arow["pediatricpads"] && $arow["pediatricpads"] != "0000-00-00" && strtotime($arow["pediatricpads"]) < $sixmonths) {
            $numped++;
        }
        if (!$first) {
            $str .= ", ";
        }
        $first = false;
        $str .= (isset($arow["model"]) ? $arow["model"] : "");
    }
}
echo $str;
echo "<br>Num Adult Pads: $numadult<br>";
echo "Num Ped Pads: $numped<br>";

if (isset($crow["available_tvvcr"]) && $crow["available_tvvcr"]) {
    echo "VCR Available<br>";
}
if ((isset($crow["available_tvdvd"]) && $crow["available_tvdvd"]) || (!isset($crow["available_tvvcr"]) || !$crow["available_tvvcr"])) {
    echo "DVD Available<br>";
}

echo "<br>Return Instructions: " . (isset($crow["equipdelivinstr"]) ? htmlspecialchars($crow["equipdelivinstr"]) : "") . "<br>";
echo "<br>Notes: " . (isset($crow["notes"]) ? htmlspecialchars($crow["notes"]) : "") . "<br>";
echo "<br>Instructor Notes: " . (isset($crow["instructornotes"]) ? htmlspecialchars($crow["instructornotes"]) : "") . "<br>";
echo "<br>Confirmation Notes: " . (isset($crow["confirmationnotes"]) ? htmlspecialchars($crow["confirmationnotes"]) : "") . "<br>";
echo "<br>Equipment Notes: " . (isset($crow["equipnotes"]) ? htmlspecialchars($crow["equipnotes"]) : "") . "<br>";
?>

</span>
</body>
</html>