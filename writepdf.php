<?php
require_once('mysql.php');

// let's print the international format for the en_US locale
setlocale(LC_MONETARY, 'en_US');
// require_once('pdfstuff/fpdf.php'); 
require_once('pdfstuff/fpdi.php'); 

function fixRate($rate)
{
    return str_replace(",", "", str_replace("\$", "", $rate));
}

function writeDescription($Description, $notes, $lineheight)
{
    global $pdf;
    $l = $notes ? $Description . " - " . $notes : $Description;
    $wrapped = explode("\n", wordwrap(stripslashes($l), 55));
    // print_r( $wrapped );
    // exit;
    foreach($wrapped as $w)
    {
        $pdf->SetXY(40, $lineheight); 
        $pdf->Cell(87, 12, "$w", 0, 1, 'L'); 
        $lineheight += 5;
    }

    return $lineheight;
}

$data = "responders";
$table = "responders_esi";

$extra .= "concat( firstname, ' ', lastname ) as fullname, title, ";

if(!isset($specificclass) || !$specificclass)
{
    $extrawhere .= " and responder_training_dates.exported=0 ";
}
else
{
    $extrawhere .= " and classid = " . intval($specificclass);
}

$orderby = "order by classid, lastname";

if((isset($go) && $go) || (isset($savepdf) && $savepdf))
{
    if(isset($responderstoprint) && is_array($responderstoprint))
    {
        $extrawhere .= " and responders_esi.responderid in (" . implode(", ", array_keys($responderstoprint)) . ") ";
    }
}

if(isset($markchecked) && $markchecked)
{
    if(isset($responderstoprint) && is_array($responderstoprint))
    {
        $escaped_iscorp = mysqli_real_escape_string($link, $session_iscorp);
        db_query("update responder_training_dates set exported=1 where exported=0 and responderid in (select responderid from responders_esi, company_esi where company_esi.id = clientid and iscorp = '$escaped_iscorp' and responderid in (" . implode(", ", array_keys($responderstoprint)) . "))"); 
    }
}

$cl = isset($classid) && $classid ? "and classid = " . intval($classid) : "";
$escaped_iscorp = mysqli_real_escape_string($link, $session_iscorp);
$extrawhere .= " and company_esi.iscorp = '$escaped_iscorp'";
$sql = "select classid, responder_training_dates.program as tprogram, responder_training_dates.id as tid, responder_training_dates.trainingdate as ttrainingdate, $extra responders_esi.* from responders_esi, responder_training_dates, company_esi where company_esi.id = responders_esi.clientid and responders_esi.deleted=0 and responders_esi.responderid = responder_training_dates.responderid and classid > 0 $cl $extrawhere $orderby";

// echo( $sql ); exit;
$result = db_query_rows($sql);

if((isset($go) && $go) || (isset($savepdf) && $savepdf))
{
    $chunks = array_chunk($result, 3);
    // $doback = false;
    $pdf = new FPDI();
    if(isset($doback) && $doback) 
    {
        $pagecount = $pdf->setSourceFile('newversion.pdf'); // test_blank.pdf
    }
    $numCharsPerLine = 90; // The Number of Characters allowed per line 
    
    $pagenum = 0;
    $allclassids = array();
    foreach($chunks as $chunkarr)
    {
        if(isset($doback) && $doback) 
        {
            $tplidx = $pdf->importPage(1); 
        }
        $pdf->addPage(); 
        // $pdf->_out('/ViewerPreferences [/PrintScaling/None]');
        // $pdf->_out('/ViewerPreferences<</Duplex/Simplex/Enforce[/PrintScaling]/PrintScaling/None>>');
        $toadd = 0; // add this if you want a border at the top 
        $pdf->SetXY(0, 10); 
        if(isset($doback) && $doback) 
        {
            $pdf->useTemplate($tplidx, -3, $toadd); 
        }
        $starty = 2.5;
        $num = 0;
        foreach($chunkarr as $row)
        {
            $num++;
            $allclassids[$row["classid"]] = $row["classid"];
            $crow = isset($row["classid"]) && $row["classid"] ? getClassRow($row["classid"]) : array();
            $comrow = isset($crow["companyid"]) && $crow["companyid"] ? getCompanyRow($crow["companyid"]) : array();
            $mycomrow = isset($row["clientid"]) && $row["clientid"] ? getCompanyRow($row["clientid"]) : array();
            $program = isset($crow["code"]) && $crow["code"] && isset($class_names[$crow["code"]]) ? $class_names[$crow["code"]] : (isset($row["tprogram"]) ? $row["tprogram"] : "");
            $trainer = "";
            if($crow && !empty($crow))
            {
                $trainers = getTrainers($crow["id"]);
                foreach($trainers as $trow)
                {
                    // if($trainer)
                    //     $trainer .= ", ";
                    $trainer = getFullname($trow["trainerid"]);
                    $escaped_trainerid = mysqli_real_escape_string($link, $trow["trainerid"]);
                    $ahaid = db_query_first_cell("Select ahaid from user where id = '$escaped_trainerid'");
                    break;
                }
            }
            else
            {
                $trainer = isset($row["instructor"]) ? $row["instructor"] : "";
            }
        
            if($comrow && !empty($comrow))
            {
                $trainingsite = $comrow["companyname"];
            }
            else
            {
                $trainingsite = isset($row["trainingsite"]) ? $row["trainingsite"] : "";
            }
        
            $td = strtotime($row["ttrainingdate"]);
            $td2 = mktime(0, 0, 0, date("m", $td), date("d", $td), date("Y", $td) + 2);

            // $pdf->AddFont('verdanab'); 
            $pdf->SetTextColor(255, 0, 0);
            // POSITIONSIN, INLOCATION, JOBDESCRIPTION 
            $fontsize = 10; 
            $pdf->SetFont('arial', '', $fontsize - 4); 
            // X AND Y ARE BACKWARDS FROM WHAT YOU ARE THINKING!!!!!
            if(isset($comrow["iscorp"]) && $comrow["iscorp"])
            {
                $classdescr = "$trainingsite";
            }
            else
            {
                $classdescr = "$trainingsite, " . (isset($comrow["schoolcode"]) ? $comrow["schoolcode"] : "");
            }

            $pdf->SetXY(28, $starty); 
            $pdf->Cell(87, 10, "CLASS: ", 0, 1, 'L'); 
            $pdf->SetFont('arial', 'U', $fontsize - 4); 
            $pdf->SetXY(43, $starty); 
            $pdf->Cell(87, 10, date("m/d/y", $td) . " / #" . $row["classid"] . " / $classdescr", 0, 1, 'L'); 

            $pdf->SetFont('arial', 'I', $fontsize - 2); 
            $pdf->SetXY(28, $starty + 3); 
            $pdf->Cell(87, 10, "RESPONDER FOR: " . (isset($mycomrow["companyname"]) ? $mycomrow["companyname"] : "") . " " . (isset($mycomrow["schoolcode"]) ? $mycomrow["schoolcode"] : ""), 0, 1, 'L'); 

            $pdf->SetFont('arial', 'B', $fontsize - 2); 
            $pdf->SetXY(28, $starty + 5); 
            $pdf->Cell(87, 12, "DO NOT LOSE THIS CARD - \$15 FEE TO REPLACE ", 0, 1, 'L'); 

            if($num == 1)
            {
                $starty -= 2.5;
            }
            if($num == 1)
            {
                $placement = $starty + 42;
            }
            if($num == 2)
            {
                $placement = $starty + 42;
            }
            if($num == 3)
            {
                $placement = $starty + 42;
            }
            $pdf->SetXY(28, $placement); 
            $pdf->Cell(87, 12, strtoupper($row["fullname"]), 0, 1, 'L'); 
            $pdf->SetXY(132, $starty + 25); 
            $pdf->SetFont('arial', '', $fontsize - 2); 
            $pdf->Cell(87, 12, "Emergency Skills, Inc.", 0, 1, 'L'); 
            $pdf->SetXY(168, $starty + 25); 
            $pdf->Cell(87, 12, "NY056938", 0, 1, 'L'); 
            $pdf->SetXY(131, $starty + 33); 
            $pdf->Cell(87, 12, "305 7th Avenue, Suite 1100 NY, NY 10001    212-564-6833", 0, 1, 'L'); 

            $pdf->SetXY(131, $starty + 41.5);
            $trainingsite = strlen($trainingsite) > 30 ? substr($trainingsite, 0, 27) . "..." : $trainingsite;
            $pdf->Cell(87, 12, "$trainingsite", 0, 1, 'L'); 

            $pdf->SetXY(131, $starty + 50.5); 
            $pdf->Cell(87, 12, "$trainer", 0, 1, 'L'); 
            $pdf->SetXY(168, $starty + 50.5); 
            $pdf->Cell(87, 12, (isset($trow["ahaid"]) ? $trow["ahaid"] : ""), 0, 1, 'L'); 

            $pdf->SetFont('arial', '', $fontsize); 
            $pdf->SetXY(28, $starty + 59); 
            $pdf->Cell(25, 12, date("M Y", $td), 0, 1, 'L');
            $pdf->SetXY(74, $starty + 59); 
            $pdf->Cell(25, 12, date("M Y", $td2), 0, 1, 'L');

            if(isset($comrow["iscorp"]) && !$comrow["iscorp"])
            {
                $pdf->SetXY(79, $starty + 55); 
                $pdf->Cell(25, 12, "---------", 0, 1, 'L');
            }

            if($num == 1)
            {
                $starty += 93.5;
            }
            else
            {
                $starty += 93.5;
            }
        }
    }

    if(isset($go) && $go) 
    {
        $pdf->SetDisplayMode('real');
        $pdf->Output(time() . ".pdf", "D"); 
        exit;
    }
    else 
    {
        if(isset($savepdf) && $savepdf)
        {
            foreach($allclassids as $classid)
            {
                $pdffilename = "savedcardpdfs/" . intval($classid) . "_" . time() . ".pdf";
                $pdf->Output($pdffilename, "F");
                $err = "<br><font color='red'>Written.</font><br>";
            }
        }
    }
}
?>
<?php include "ssi/top.php"; ?>		
<script type="text/javascript" language="javascript">// <![CDATA[
function checkAll(formname, checktoggle)
{
    var checkboxes = new Array();
    checkboxes = document[formname].getElementsByTagName('input');

    for (var i=0; i<checkboxes.length; i++)  {
        if (checkboxes[i].type == 'checkbox')   {
            checkboxes[i].checked = checktoggle;
        }
    }
}
function checkAllForClass(formname, partofid, checktoggle)
{
    var checkboxes = new Array();
    checkboxes = document[formname].getElementsByTagName('input');

    for (var i=0; i<checkboxes.length; i++)  {
        if (checkboxes[i].type == 'checkbox' && checkboxes[i].id.indexOf(partofid) > 0)   {
            checkboxes[i].checked = checktoggle;
        }
    }
    return false;
}
// ]]></script>
<form method='post' name="exportform">
    <h3>Responders To Print</h3>
    <?php echo isset($err) ? $err : ''; ?>
    <a onclick="javascript:checkAll('exportform', true);" href="javascript:void();">check all</a>
    <a onclick="javascript:checkAll('exportform', false);" href="javascript:void();">uncheck all</a>
    <table border=1 cellspacing=0 cellpadding=0 class="table3" width='700'>
        <tr><th>ID</th><th>Name</th><th>Responder Location</th></tr>
        <?php
        $num = 0;
        $lastclass = 0;
        if(isset($result) && is_array($result))
        {
            foreach($result as $row) 
            {
                if($row["classid"] != $lastclass)
                {
                    if($lastclass)
                    {
                        echo "<tr><td colspan='4'>&nbsp;</td></tr>";
                    }
                    $num = 1;
                    $lastclass = $row["classid"];
                    $crow = getClassRow($row["classid"]);
                    $trainers = getTrainers($crow["id"]);
                    $trainer = "";
                    foreach($trainers as $trow)
                    {
                        $trainer = getFullname($trow["trainerid"]);
                        break;
                    }
                
                    $comrow = isset($crow["companyid"]) && $crow["companyid"] ? getCompanyRow($crow["companyid"]) : array();
                    if($comrow && !empty($comrow))
                    {
                        $trainingsite = $comrow["companyname"];
                    }
                    else
                    {
                        $trainingsite = isset($row["trainingsite"]) ? $row["trainingsite"] : "";
                    }
                    
                    if(isset($comrow["iscorp"]) && $comrow["iscorp"])
                    {
                        $classdescr = "$trainingsite";
                    }
                    else
                    {
                        $classdescr = "$trainingsite, " . (isset($comrow["schoolcode"]) ? $comrow["schoolcode"] : "");
                    }

                    echo "<tr><td colspan='4'>Class: <a href='class_detail.php?id=" . intval($crow["id"]) . "'>" . htmlspecialchars($crow["id"]) . "</a> <a href='viewcompany.php?id=" . intval($crow["companyid"]) . "'>" . htmlspecialchars($classdescr) . "</a><br>Instructor: " . htmlspecialchars($trainer);
                    echo "<br><a href='#' onClick=\"return checkAllForClass('exportform', '_classcheck_" . intval($crow["id"]) . "_', true)\">Check All</a> | <a href='#' onClick=\"return checkAllForClass('exportform', '_classcheck_" . intval($crow["id"]) . "_', false)\">Uncheck All</a>";
                    echo "</td></tr>";
                }
                else
                {
                    $num++;
                }
                
                $ext = "";
                $color = "black";
                if(isset($row["emptype"]) && ($row["emptype"] == "Charter School Employee" || $row["emptype"] == "Custodial Staff" || $row["emptype"] == "SSA"))
                {
                    $color = "green";
                }
                if(checkNeedsValidation($row["responderid"]))
                {
                    $color = "red";
                }
                $mycomrow = isset($row["clientid"]) && $row["clientid"] ? getCompanyRow($row["clientid"]) : array();
                $rloc = "<a href='viewcompany.php?id=" . intval($mycomrow["id"]) . "'>" . htmlspecialchars($mycomrow["companyname"]) . " " . htmlspecialchars($mycomrow["schoolcode"]) . "</a>";

                $checked = $color != "red" ? "CHECKED" : "";
                ?>
                <tr>
                    <td><input type='checkbox' id="<?php echo intval($row["responderid"]); ?>_classcheck_<?php echo intval($crow["id"]); ?>_" name='responderstoprint[<?php echo intval($row["responderid"]); ?>]' value='1' <?php echo $checked; ?>></td>
                    <td><font color='<?php echo htmlspecialchars($color); ?>'><?php echo intval($num); ?>. <?php echo htmlspecialchars($row["fullname"]); ?></font></td>
                    <td><?php echo $rloc; ?></td>
                </tr>
                <?php 
            }
        }
        ?>
    </table>
    <input type='checkbox' name='doback' value='1'> Do full background (for testing)<br>
    <input type='submit' name='go' value='Create PDF For Printing'>
    <input type='submit' name='markchecked' value='Mark Checked As Exported'>
    <input type='submit' name='savepdf' value='Save PDF To Class'>
</form>