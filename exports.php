<?php
require_once('mysql.php');

$table_name = array(
    'company'   => 'company_esi',
    'aed'       => 'aed_esi',
    'responder' => 'responder_training_dates'
);

if ($update_data && $data) {
    if ($data == "company") {
        db_query("update " . $table_name[$data] . " set exported=1 where exported=0 and iscorp = '" . intval($session_iscorp) . "'");
    }
    elseif ($data == "responder") {
        db_query("update " . $table_name[$data] . " set exported=1 where exported=0 and classid <> '16160' and responderid in (select responderid from responders_esi, company_esi where company_esi.id = clientid and iscorp = '" . intval($session_iscorp) . "')");
    }
    else {
        db_query("update " . $table_name[$data] . " set exported=1 where exported=0 and clientid in (select id from company_esi where iscorp = '" . intval($session_iscorp) . "')");
    }
    Header("location: schools.php");
    exit;
}

if(!$specialadmin) {
    Header("location: login.php");
    exit;
}
?>
<?php include "ssi/top.php"; ?>
<SCRIPT LANGUAGE="JavaScript">

function confirmExported(form,label) {
    var ok=confirm("OK to mark all rows of "+label+" as exported?");
    if (ok) {
        form.update_data.value=1;
        form.data.value=label;
        form.submit();
    }
    return;
}

function ChangeImage(ImageName,FileName) {
    document[ImageName].src = FileName;
}

</SCRIPT>
<!--start center content-->
<form method='post' name="exportform"> 
    <strong><span class="title">EXPORT TO FILE</span></strong>
    
    <input type="hidden" name="update_data" value="">
    <input type="hidden" name="data" value="">
    <table border=1 cellspacing=0 cellpadding=4 class="table3">
        <tr>
            <td><a href="/report.php?exportedonly=true&data=company&xls=true"><u>New Company Data</u></a></td>
            <td><a href="javascript:confirmExported(document.forms['exportform'],'company')">(<u>mark as exported</u>)</a></td>
        </tr>
        <!--<tr><td><a href="/report.php?data=aed&xls=true&expired=true"><u>Expired Aed Data</u></a></td></tr>-->
        <tr>
            <td><a href="/report.php?exportedonly=true&data=aed&xls=true"><u>New Aed Data</u></a></td>
            <td><a href="javascript:confirmExported(document.forms['exportform'],'aed')">(<u>mark as exported</u>)</a></td>
        </tr>
        <?php if(strtolower($session_userid) == "sarahg@emergencyskills.com") { ?>
        <tr>
            <td><a href="/exportresponders.php?exportedonly=true&xls=true&ob=lastname"><u>New Responder Data</u></a></td>
            <td><a href="/writepdf.php"><u>New Responder Data (New Export)</u></a></td>
            <td><a href="javascript:confirmExported(document.forms['exportform'],'responder')">(<u>mark as exported</u>)</a></td>
        </tr>
        <?php } ?>
    </table>
    <!-- &gt;<a href="/report?dataexportedonly=true&=aedhistory"><u>New Event History</u></a>-->
    </strong>
    </span>
    <br>
</table>

<br><br><br><br><br><br><br>

<!--end center content-->

<?php include "ssi/footer.php"; ?>

<!--end footer-->