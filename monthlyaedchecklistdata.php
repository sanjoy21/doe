<?php
require_once('mysql.php');

if(!isset($id) || !$id)
{
    $id = isset($thisusersrow["companyid"]) ? $thisusersrow["companyid"] : 0;
}

if(!isOverallAdmin() && isset($thisusersrow["companyid"]) && $thisusersrow["companyid"] != $id)
{
    Header("Location: index.php?noaccess");
    exit;
}

function printAedValue($val, $fontredval = "")
{
    $fstart = "";
    $fend = "";
    if($val == $fontredval)
    {
        $fstart = "<font color='red'>";
        $fend = "</font>";
    }
    if($val == "-1") return "{$fstart}No{$fend}";
    if($val == "1") return "{$fstart}Yes{$fend}";
    return "{$fstart}No data given{$fend}";
}

$schoolrow = array();
if(isset($id) && $id)
{
    $schoolrow = getCompanyRow($id);
}

$cidstr = isset($id) && $id ? "and clientid = " . intval($id) : "";
if(isset($datefrom) && $datefrom)
{
    $cidstr .= " and ai.thedate >= '" . db_escape_string($datefrom) . "'";
}
if(isset($dateto) && $dateto)
{
    $cidstr .= " and ai.thedate <= '" . db_escape_string($dateto) . "'";
}

$session_iscorp = isset($session_iscorp) ? $session_iscorp : 0;
$extsql = $session_iscorp ? " and sendmonthlyaedchecklist = 1" : " and borough = 'Staten Island'";

$inspectionrows = db_query_rows("select aed_esi.*, ai.* from aed_esi, aedinspections ai, company_esi c where c.id = aed_esi.clientid and  aed_esi.aedid = ai.aedid $cidstr $extsql order by thedate desc");

$sql = "select clientid, count(*) cnt from aed_esi a , company_esi c where a.deleted = 0 and c.deleted = 0 and c.id = a.clientid and aedstolen = 0 and principalemail <> contactemail $extsql and contactemail > '' group by clientid";
$totalresults = db_query_array($sql, "clientid", "cnt");
$numschools = isset($totalresults) && is_array($totalresults) ? count($totalresults) : 0;

$partial = 0;
$totally = 0;

if(!$cidstr)
{
    $cidstr = "and thedate like '" . date("Y-m") . "%' ";
}

$sql = "select clientid, count(*) cnt from aed_esi a , company_esi c, aedinspections ai where a.deleted = 0 and c.deleted = 0 and c.id = a.clientid and a.aedid = ai.aedid $cidstr and aedstolen = 0 and principalemail <> contactemail $extsql and contactemail > '' group by clientid";
$thismonth = db_query_array($sql, "clientid", "cnt");

if(isset($thismonth) && is_array($thismonth) && isset($totalresults) && is_array($totalresults))
{
    foreach($thismonth as $cid=>$cnt)
    {
        if(isset($totalresults[$cid]) && $cnt >= $totalresults[$cid])
        {
            $totally++;
        }
        if(isset($totalresults[$cid]) && $cnt < $totalresults[$cid])
        {
            $partial++;
        }
    }
}

$sql = "select contactemail, companyname  from aed_esi a , company_esi c where a.deleted = 0 and c.deleted = 0 and c.id = a.clientid and aedstolen = 0 and contactemail <> principalemail $extsql and contactemail > '' group by clientid";
$allemails = db_query_array($sql, "contactemail", "companyname");
?>
<?php include "ssi/top.php"; ?>
<br>
<h3>Monthly Inspection Email Report</h3>
    <br>
    <h4><?php echo !isset($id) || !$id ? "All " . getSchoolStr("Schools") : (isset($schoolrow["companyname"]) ? htmlspecialchars($schoolrow["companyname"]) : ''); ?></h4>
<br>
    <?php if(isOverallAdmin()) { ?>
    <A href='#' onClick='if( $("#showreplies").css( "display" ) == "none" ) $("#showreplies").css( "display", "" ); else $("#showreplies").css( "display", "none" ); '>Show # Replies by Email</a><br>
    <div id="showreplies" style="display:none">
    <table><tr><td>Email</td><td>School</td><td># Replies</td></tr>
     <?php 
     if(isset($allemails) && is_array($allemails))
     {
         foreach($allemails as $e=>$tcompanyname) 
         {
             $uid = getOrCreateEmailId($e);
             if($uid)
             {
                 $mynum = db_query_first_cell("select count(*) from aedinspections ai where userid = " . intval($uid));
             }
             else
             {
                 $mynum = "user not found";
             }
     ?>
     <tr><td><?php echo htmlspecialchars($e); ?></td><td><?php echo isset($tcompanyname) ? htmlspecialchars($tcompanyname) : ''; ?></td><td><?php echo !$mynum ? "<font color='red'>" . htmlspecialchars($mynum) . "</font>" : htmlspecialchars($mynum); ?></td></tr>
     <?php 
         }
     }
     ?>
     </table><br><br>
           </div>
    <table>
     <tr><td>Number of Participating <?php echo getSchoolStr("Schools"); ?>:</td><td><?php echo $numschools; ?></td></tr>
     <tr><td>Partially Completed:</td><td><?php echo $numschools > 0 ? number_format((($partial/$numschools)*100), 2) . "% ($partial)" : "0% (0)"; ?></td></tr>
     <tr><td>Totally Completed:</td><td><?php echo $numschools > 0 ? number_format((($totally/$numschools)*100), 2) . "% ($totally)" : "0% (0)"; ?></td></tr>
     <tr><td>Not Yet Responded:</td><td><?php echo $numschools > 0 ? number_format(((($numschools-$partial-$totally)/$numschools)*100), 2) . "% (" . ($numschools-$totally-$partial) . ")" : "0% (0)"; ?></td></tr>
<?php
     if(isset($shownames) && $shownames)
     {
         $sql = "select clientid, count(*) from aed_esi a , company_esi c where a.deleted = 0 and c.deleted = 0 and c.id = a.clientid and aedstolen = 0 and contactemail <> contactemail $extsql and contactemail > '' group by clientid";
         $res = db_query_array($sql, "clientid", "clientid");
         $count = 0;
         echo "<table><tr><th>contact email</th><th>principal email</th><th>School Code</th><th>School name</th></tr>";
         if(isset($res) && is_array($res))
         {
             foreach($res as $clientid)
             {
                 $count++;
                 $companyrow = getCompanyRow($clientid);
                 echo "<tr><td>$count. " . (isset($companyrow['contactemail']) ? htmlspecialchars($companyrow['contactemail']) : '') . "</td><td>" . (isset($companyrow['principalemail']) ? htmlspecialchars($companyrow['principalemail']) : '') . "</td><td>" . (isset($companyrow['schoolcode']) ? htmlspecialchars($companyrow['schoolcode']) : '') . "</td><td>" . (isset($companyrow['companyname']) ? htmlspecialchars($companyrow['companyname']) : '') . "</td></tr>";
             }
         }
     }
    ?>
<form method='post'>
Dates:  <?php echo printdates2("datefrom", isset($datefrom) ? $datefrom : ''); ?> to <?php echo printdates2("dateto", isset($dateto) ? $dateto : ''); ?><br>
     <?php echo getSchoolStr("School"); ?>: <select name='id'>
     <option value=''>All</option>
     <?php
     $poss = db_query_array("select companyname, clientid from aed_esi a, company_esi c, aedinspections ai where a.aedid = ai.aedid and c.id = clientid and iscorp = " . intval($session_iscorp), "clientid", "companyname");
     if(isset($poss) && is_array($poss))
     {
         foreach($poss as $pid=>$pname)
         {
             $sel = isset($id) && $pid == $id ? "SELECTED" : "";
             echo "<option value='" . htmlspecialchars($pid) . "' $sel>" . htmlspecialchars($pname) . "</option>\n";
         }
     }
     ?>
     </select><br>
         <input type='checkbox' name='shownames' value='1' <?php echo isset($shownames) && $shownames ? "checked" : ""; ?>> Show Emails?<br><br>
         <input type='submit' name='dofilter' value='Search'><br><br>
    </form>
     <?php } ?>    
<table width="100%" cellpadding="0" cellspacing="0" border="1">
<tr>
<th><?php echo getSchoolStr("School"); ?></th>
<th>Date of Inspection</th>
<th>Serial #</th>
<th>AED Location</th>
<th>Are you viewing the AED?</th>
<th>Is the AED Chirping?</th>
<th>Do you see the Green Status Indicator blinking?</th>
<th>Name of Inspector</th>
<th>Email Sent To</th>
</tr>
    <?php 
    if(isset($inspectionrows) && is_array($inspectionrows))
    {
        foreach($inspectionrows as $arow) 
        { 
    ?>
<tr>
     <td align='center'><?php echo getCompanyName(isset($arow["clientid"]) ? $arow["clientid"] : 0); ?></td>
    <td align='center'><?php echo isset($arow["thedate"]) ? date("m/d/Y", strtotime($arow["thedate"])) : ''; ?></td>
<td align='center'><?php echo isset($arow["serial"]) ? htmlspecialchars($arow["serial"]) : ''; ?></td>
<td align='center'><?php echo isset($arow["location"]) ? htmlspecialchars($arow["location"]) : ''; ?></td>
                         <td align='center'><?php echo printAedValue(isset($arow["viewing"]) ? $arow["viewing"] : '', -1); ?></td>
                         <td align='center'><?php echo printAedValue(isset($arow["chirping"]) ? $arow["chirping"] : '', 1); ?></td>
                         <td align='center'><?php echo printAedValue(isset($arow["blinking"]) ? $arow["blinking"] : '', -1); ?></td>
    <td align='center'><?php echo isset($arow["inspectorname"]) ? htmlspecialchars($arow["inspectorname"]) : ''; ?></td>
                         <td align='center'><?php echo getEmailFromId(isset($arow["userid"]) ? $arow["userid"] : 0); ?></td>
                  </tr>

<?php 
        }
    } 
?>
    </table><br><br>
        <br><br>
        <?php include "ssi/footer.php"; ?>
        </span>
        </td>
        <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
    </tr>
</table>
<br><br>
</form>
</body>
</html>