<?php 
include "mysql.php";
require_once "services.php";

$cnt = 0;
$err = '';

if( $addevent )
{
    //    mysql> create table aedevents ( serial varchar( 255 ), eventdate date, notes text, dateeventreported date )
    db_query( "insert into aedevents ( serial, eventdate, notes, dateeventreported ) values ( '$serial', '$eventdate', '', '$dateeventreported' )" );
}
if( $delevent )
{
    //    mysql> create table aedevents ( serial varchar( 255 ), eventdate date, notes text, dateeventreported date )
    db_query( "delete from aedevents where id = $delevent" );
}

if( $update && !$delete )
{
    $pediatricpads = fixdate( $pediatricpads );
    $padaexpiration = fixdate( $padaexpiration );
    $padbexpiration = fixdate( $padbexpiration );
    $batterydate = fixdate( $batterydate );
    $sparedate = fixdate( $sparedate );

    if( $serial )
        $cnt = db_query_first_cell( "select aedid from aed_esi, company_esi where iscorp = '$session_iscorp' and company_esi.id = clientid and company_esi.deleted = 0 and aed_esi.deleted = 0 and serial = '$serial' and aedid <> '$aedid'" );

    $ser = "";
    $ext = "";

    if( $aedid )
    {
        $ser = ", serial = '$serial'";
        if( $cnt )
        {
            $ser = "";
            $err = "<br><br>There is already an AED with this serial #: <a target=_blank href='editaed.php?aedid=$cnt'>$serial</a><br>";
        }
        $aedrow = getAedRow( $aedid );
        if( $installcomplete )
            $newinstall = "";        
        
        if( $installcomplete && empty($aedrow["installcomplete"]) )   
            $ext = ", datecompleted = now() ";
        else if( $datecompleted )
            $ext .= ", datecompleted = '$datecompleted'";

    if( $eventdate )
            $ext .= ", eventdate = '$eventdate'";
    if( $dateeventreported )
            $ext .= ", dateeventreported = '$dateeventreported'";
        
// remember to edit the new AED section too!

        if( $aedstolen ) $aedmissing = "";
        $sql = ( "update aed_esi set hasbeenupdated = '$hasbeenupdated', aedinactive = '$aedinactive', buildingcode='$buildingcode', purchasedate = '$purchasedate', otherequiptype = '$otherequiptype', otherequipserial = '$otherequipserial', pediatrickey = '$pediatrickey', pedpadna = '$pedpadna', sparebattna = '$sparebattna', aedmissing = '$aedmissing', aedstolen = '$aedstolen', outofservice = '$outofservice', aedretired = '$aedretired', outofwarranty = '$outofwarranty', aedstolentext = '$aedstolentext' $ser, model = '$model', newinstall = '$newinstall', installcomplete = '$installcomplete', isrma = '$isrma', readytoreturn = '$readytoreturn', invoiced = '$invoiced', invoiceno = '$invoiceno', floor = '$floor', location = '$location', padaexpiration = '$padaexpiration', padbexpiration = '$padbexpiration', batterydate = '$batterydate', warrantydate = '$warrantydate', sparedate = '$sparedate', date = Now(), eventhistory= '$eventhistory', aedservicehistory= '$aedservicehistory', aedcomments= '$aedcomments', pediatricpads = '$pediatricpads', padalot = '$padalot', padblot = '$padblot', pedpadlot = '$pedpadlot', sncorp = '$sncorp', imei = '$imei', smartlink = '$smartlink', irn = '$irn' $ext where aedid = $aedid " );
        
        // Fix: Quoted keys
        if( $padaexpiration != $aedrow["padaexpiration"] )
        {
            addOldDate( $aedid, $aedrow["padaexpiration"], "padaexpiration" );
        }
        if( $padbexpiration != $aedrow["padbexpiration"] )
        {
            addOldDate( $aedid, $aedrow["padbexpiration"], "padbexpiration" );
        }
        if( $sparedate != $aedrow["sparedate"] )
        {
            addOldDate( $aedid, $aedrow["sparedate"], "sparedate" );
        }
        if( $aedstolen && $aedstolen != $aedrow["aedstolen"] )
        {
            addOldDate( $aedid, date( "Y-m-d" ), "aedstolen" );
        }
        if( $outofservice && $outofservice != $aedrow["outofservice"] )
        {
            addOldDate( $aedid, date( "Y-m-d" ), "outofservice" );
        }
        if( $aedretired && $aedretired != $aedrow["aedretired"] )
        {
            addOldDate( $aedid, date( "Y-m-d" ), "aedretired" );
        }
        if( $pediatricpads != $aedrow["pediatricpads"] )
        {
            addOldDate( $aedid, $aedrow["pediatricpads"], "pediatricpads" );
        }
        if( $aedstolen && !$session_iscorp )
        {
//            $newschoolid = 2810;
        }   
        
        if( $newschoolid )
        {
//    mysql> create table oldaedschools ( aedid integer, clientid integer, movedate datetime );
            $schoolid = db_query_first_cell( "select clientid from aed_esi where aedid = $aedid" );
            db_query( "insert into oldaedschools ( aedid, clientid, movedate, movedby ) values ( '$aedid', '$schoolid', Now(), '$session_userid' ) " );
            db_query( "update aed_esi set clientid = $newschoolid where aedid = $aedid" );
        }

        
        $arow = getAedRow( $aedid );
        $val = updateAED( $arow );

    }
    else
    {
        if( $cnt )
        {
            $err = "There is already an AED with this serial #: <a target=_blank href='editaed.php?aedid=$cnt'>$serial</a>";
            $serial = "";
        }
        
        $sql = ( "insert into aed_esi ( clientid, serial, model, floor, location, padaexpiration, padbexpiration, batterydate, warrantydate, sparedate, date, eventhistory, aedservicehistory, aedcomments, deleted, pediatricpads, pediatrickey, municipalna, irn, hasbeenupdated, aedinactive, newinstall, buildingcode) values ('$id','$serial','$model','$floor','$location','$padaexpiration','$padbexpiration','$batterydate','$warrantydate','$sparedate',Now(),'$eventhistory','$aedservicehistory','$aedcomments','$deleted','$pediatricpads','$pediatrickey','$municipalna','$irn','$hasbeenupdated','$aedinactive','$newinstall','$buildingcode') " );
    }

    db_query( $sql );
    if( !$aedid )
    {
        $aedid = mysqli_insert_id( $link );
    }
       if( $aedstolen ) $aedmissing = "";
    
    $sql = ( "update aed_esi set hasbeenupdated = '$hasbeenupdated', aedinactive = '$aedinactive', purchasedate = '$purchasedate', otherequiptype = '$otherequiptype', otherequipserial = '$otherequipserial', pediatrickey = '$pediatrickey', pedpadna = '$pedpadna', sparebattna = '$sparebattna', aedmissing = '$aedmissing', aedstolen = '$aedstolen', outofservice = '$outofservice', aedretired = '$aedretired', outofwarranty = '$outofwarranty', aedstolentext = '$aedstolentext' $ser, model = '$model', newinstall = '$newinstall', installcomplete = '$installcomplete', readytoreturn = '$readytoreturn', isrma = '$isrma', invoiced = '$invoiced', invoiceno = '$invoiceno', floor = '$floor', location = '$location', padaexpiration = '$padaexpiration', padbexpiration = '$padbexpiration', batterydate = '$batterydate', warrantydate = '$warrantydate', sparedate = '$sparedate', directorname = '$directorname', filingexpirationdate = '$filingexpirationdate', date = Now(), eventhistory= '$eventhistory', aedservicehistory= '$aedservicehistory', pediatricpads = '$pediatricpads', padalot = '$padalot', padblot = '$padblot', pedpadlot = '$pedpadlot', irn = '$irn', medicalinvoicedate = '$medicalinvoicedate' $ext where aedid = $aedid " );
    db_query( $sql );
//      echo( $sql );
//      exit;

//     if( $aedstolen && !$session_iscorp )
//     {
//         $newschoolid = 2810;
//     }    

    $arow = getAedRow( $aedid );
    $val = updateAED( $arow );

    if( !$err && !$submit && !$addevent){
    
        header( "location: $redirect " );
        exit;
        }
}

if( $delete )
{
    db_query( "update aed_esi set deleted = 1 where aedid = $aedid " );
//    echo( "update aed_esi set deleted = 1 where aedid = $aedid " );
    header( "location: $redirect " );
        exit;
}


//get info for the form
$aed_row = array();
if( $aedid )
{
    $aed_row = db_query_first( "select * from aed_esi where aedid = $aedid" );
    if ($aed_row) {
        $id = $aed_row["clientid"];
    }
}

// Initialize row if empty to avoid undefined index warnings in form
if( !$aed_row )
{
    $aed_row = array(
        'serial' => '', 'model' => '', 'aedretired' => 0, 'outofwarranty' => 0, 'outofservice' => 0,
        'aedmissing' => 0, 'aedstolen' => 0, 'aedstolentext' => '', 'hasbeenupdated' => 0,
        'lastupdateresult' => '', 'lastupdatedate' => '', 'aedinactive' => 0, 'warrantydate' => '',
        'floor' => '', 'location' => '', 'padaexpiration' => '', 'padalot' => '', 'padbexpiration' => '',
        'padblot' => '', 'pediatricpads' => '', 'pediatrickey' => 0, 'pedpadna' => 0, 'pedpadlot' => '',
        'purchasedate' => '', 'sparedate' => '', 'sparebattna' => 0, 'irn' => '', 'batterydate' => '',
        'clientid' => 0, 'buildingcode' => '', 'smartlink' => '', 'sncorp' => '', 'imei' => '',
        'otherequiptype' => '', 'otherequipserial' => '', 'newinstall' => 0, 'invoiced' => 0, 'invoiceno' => '',
        'installcomplete' => 0, 'datecompleted' => '', 'readytoreturn' => 0, 'isrma' => 0, 'aedservicehistory' => '',
        'aedcomments' => '', 'eventhistory' => '', 'aedid' => 0
    );
}
//print_r( $aed_row );
//exit;
if( !empty($aed_row["deleted"]) )
{
    // Fix: Quoted key
    header( "Location: viewcompany.php?id={$aed_row['clientid']}" );
    exit;
}
$company_row = getCompanyRow( $id );
$model_rows = db_query_rows("select value from esioptionvalues where datatype='model' order by value");
$director_rows = db_query_rows("select value from esioptionvalues where datatype='director' order by value");

$overridecname = "newschoolid";
$overrideiscorp = isset($_GET['setcorp']) ? intval($_GET['setcorp']) : $session_iscorp;
?>
<?php 
$noleftnav = 1;
include "ssi/top.php"; ?>       
<?php include "getschooldropdown_ajax.php"; ?>

<script LANGUAGE="JavaScript1.1" SRC="/FormChek.js"></script>
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
if( !$redirect && $id )
    $redirect="/viewcompany.php?id=$id";
else if( !$redirect )
    $redirect="/aeds.php";

?>
<form method="post" name="aedform">
<input type="hidden" name ="redirect" value="<?=$redirect?>">
<input type="hidden" name ="update" value="true">
<input type="hidden" name ="aedid" value="<?=$aedid?>">
<input type="hidden" name ="id" value="<?=$id?>">
<?php if( $specialadmin ) { ?>
        <table cellpadding="5" cellspacing="1" border="0" width="100%">
            <tr>
                <td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="<?=$redirect?>">&laquo; Back to Admin Main</a></strong></span></td>               
            </tr>
        </table>
<?php } ?>
        <strong><a href='viewserial.php?aedid=<?=$aedid?>'>THIS AED</a> IS FOR:</strong><br>
        <a href='viewcompany.php?id=<?=$id?>'><?php echo  $company_row['companyname']."</a><br>".$company_row['address']."<br>".$company_row['floor']."<br>".$company_row['city'].", ".$company_row['state']." ".$company_row['zip']; ?>
        <?php $ar =( getPreviousSchoolsAed( $aedid ) );
foreach( $ar as $prev )
if( $prev['clientid'] )
{
    echo( "<br>Previous ".getSchoolStr( "School" ).": <a href='viewcompany.php?id={$prev['clientid']}'>" . getCompanyName( $prev['clientid'] ) . "</a>, Moved: ".getFormattedDateWTime( $prev['movedate'] ). "" ); 
}
?>
        <br><br>
        <?php if( $specialadmin && $aedid ) { ?> Set New <?=getSchoolStr( "School" )?>:
                             <select id=borough name="borough" onChange="updateCompanies();" style="font-size: 10px;  font-family: verdana;">
                                        <option value=""></option>
<?php if( $session_iscorp ) { ?>
                                        <option value="other">Other</option>
<?php  } ?>

                                        <option value="Bronx">The Bronx</option>
                                        <option value="Brooklyn">Brooklyn</option>
                                        <option value="Manhattan">Manhattan</option>
                                        <option value="Queens">Queens</option>
                                        <option value="Staten Island">Staten Island</option>
                                    </select>

                            <span class='copy'><?=getSchoolStr( "School" )?> Name: </span> <input type='text' id='tmpschoolname' name='tmpschoolname' class='copy' onChange='changeBorough()'> <input type='button' value='Search' class=copy onClick='updateCompanies()'>
                            <span id='school_select'>

</span>
                                 <?php } ?>

        <font color='red'><?=$err?></font><br>
        <table cellpadding="5" cellspacing="1" border="0" width="100%">
            <tr>
                <td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><strong>AED Information</strong></span></td>            
            </tr>   
            
            
            <tr>
                <td valign="top" bgcolor="#E2DFDF" width='40%'>
                <span class="copy"><strong>AED Serial Number*:</strong><br><input type="text" size="20" VALUE="<?=$aed_row['serial']?>" maxlength="50" name="serial" style="font-size: 10px;  font-family: verdana;">
<?php if( !$aedid && empty($company_row["iscorp"]) ) { ?>
<br><b><font color='green'>Update Warranty Date</font></b>: <input type='checkbox' name='updatewarranty' value='1' onClick='updateWarrantySerial( document.forms["aedform"].serial.value );return false; '>

<?php } ?>

<br> <input type='checkbox' name='aedretired' value='1' <?=$aed_row["aedretired"]?"CHECKED":""?>> <font color='red'>Retired?</font><br><input type='checkbox' name='outofwarranty' value='1' <?=$aed_row["outofwarranty"]?"CHECKED":""?>> Out of Warranty <input type='checkbox' name='outofservice' value='1' <?=$aed_row["outofservice"]?"CHECKED":""?>> Out of Service<br> <input type='checkbox' name='aedmissing' value='1' <?=$aed_row["aedmissing"]?"CHECKED":""?>> AED Missing?<br> <input type='checkbox' name='aedstolen' value='1' <?=$aed_row["aedstolen"]?"CHECKED":""?>> Stolen? Police Report: <input type='text' name='aedstolentext' value="<?=$aed_row["aedstolentext"]?>" > &nbsp; </span></td>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>AED Model/Type*:</strong><br>
                <select name="model" style="font-size: 10px;  font-family: verdana;">
                <option value="<?=$aed_row['model'];?>"><?=$aed_row['model'];?></option>
                <?php foreach ($model_rows as $model) { ?>
                                <option value="<?=$model["value"];?>"><?=$model["value"];?></option>
                                <?php } ?>
                </select>
<input type='checkbox' name='hasbeenupdated' value='1' <?=$aed_row["hasbeenupdated"]?"checked":""?>> G2005 Update
<?php if( isOverallAdmin() && empty($session_iscorp) ) { ?><br>
                                Update Result: <?=$aed_row["lastupdateresult"]?> at <?=$aed_row["lastupdatedate"]?>
<br><b>Inactive in LCGMS: <input type='checkbox' name='aedinactive' value='1' <?=$aed_row["aedinactive"]?"CHECKED":""?>>
                            <?php } ?>
                <Br><br>
                    Warranty Date: <?=printdates2( "warrantydate", $aed_row["warrantydate"] ) ?>
                </td>
            </tr>



            </tr>
            
            <tr>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Floor:</strong><br><input type="text" size="40" VALUE="<?=$aed_row['floor'];?>" maxlength="50" name="floor" style="font-size: 10px;  font-family: verdana;"></span></td>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Location:</strong><br><input type="text" size="40" VALUE="<?=$aed_row['location'];?>" maxlength="50" name="location" style="font-size: 10px;  font-family: verdana;"></span></td>
            </tr>
            
            <tr>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Pads A Exp. Date*:</strong><br><span class="small"><em>Please enter as MM/YYYY</em></span><br><input type="text" size="40" VALUE="<?=fixdatefordisplay( $aed_row['padaexpiration']);?>" maxlength="50" name="padaexpiration" style="font-size: 10px;  font-family: verdana;"><br>
Lot No: <input type='text' name="padalot" value="<?=$aed_row["padalot"]?>" style="font-size: 10px;  font-family: verdana;">
</span></td>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Pads B Exp. Date:</strong><br><span class="small"><em>Please enter as MM/YYYY</em></span><br><input type="text" size="40" VALUE="<?=fixdatefordisplay( $aed_row['padbexpiration'] );?>" maxlength="50" name="padbexpiration" style="font-size: 10px;  font-family: verdana;"><br>
Lot No: <input type='text' name="padblot" value="<?=$aed_row["padblot"]?>" style="font-size: 10px;  font-family: verdana;"></span></td>
            </tr>
            
            
            <tr>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Pediatric Pads Exp. Date:</strong><br><span class="small"><em>Please enter as MM/YYYY</em></span><br><input type="text" size="40" VALUE="<?=fixdatefordisplay( $aed_row['pediatricpads'] );?>" maxlength="50" name="pediatricpads" style="font-size: 10px;  font-family: verdana;">
<input type='checkbox' name='pediatrickey' value='1' <?=$aed_row["pediatrickey"]?"CHECKED":""?>> Key?<br> <input type='checkbox' name='pedpadna' value='1' <?=$aed_row["pedpadna"]?"CHECKED":""?>> N/A?
<br>
Lot No: <input type='text' name="pedpadlot" value="<?=$aed_row["pedpadlot"]?>" style="font-size: 10px;  font-family: verdana;">
</span></td>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Purchase Date:</strong><br><input type="text" size="40" VALUE="<?=$aed_row['purchasedate'];?>" maxlength="50" name="purchasedate" style="font-size: 10px;  font-family: verdana;"></span>

<?php
if( !empty($company_row["iscorp"]) ) {
?>
<br><font color='green'><b>Update Warranty Date:</font></b> <input type='checkbox' name='updatewarranty' value='1' onClick='updateWarranty( document.forms["aedform"].purchasedate.value ); return false '>
<?php
}
?>
</td>
            </tr>
            
            
            <tr>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Spare Battery Install Before Date:</strong><br><span class="small"><em>Please enter as MM/YYYY</em></span><br><input type="text" size="40" VALUE="<?=fixdatefordisplay( $aed_row['sparedate'] );?>" maxlength="50" name="sparedate" style="font-size: 10px;  font-family: verdana;">
<br><input type='checkbox' name='sparebattna' value='1' <?=$aed_row["sparebattna"]?"CHECKED":""?>> N/A?
</span></td>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Internal Reference:</strong><br><input type="text" size="40" VALUE="<?=$aed_row['irn'];?>" maxlength="50" name="irn" style="font-size: 10px;  font-family: verdana;"></span></td>
            </tr>
        
            
            <tr>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Battery Installation Date:</strong><br><span class="small"><em>Please enter as MM/YYYY</em></span><br><input type="text" size="40" VALUE="<?=fixdatefordisplay( $aed_row['batterydate'] );?>" maxlength="50" name="batterydate" style="font-size: 10px;  font-family: verdana;"></span></td>
                <td valign="top" bgcolor="#E2DFDF">
        <?php if( empty($company_row['iscorp']) ) { ?>
<b>Building:</b>
<a href='editcompany.php?id=<?=$aed_row['clientid']?>'>edit buildings</a><br>
                                     <?=getBuildingPulldown( $id, $aed_row['buildingcode'], "buildingcode", 'style="width:500px;font-size: 10px;  font-family: verdana;"', 1 )?>
                                     <?php } else { ?>

<b>SMARTLINK:</b> <input type='text' name='smartlink' value="<?=$aed_row["smartlink"]?>"><br>

<b>S/N:</b> <input type='text' name='sncorp' value="<?=$aed_row["sncorp"]?>"><br>

<b>IMEI:</b> <input type='text' name='imei' value="<?=$aed_row["imei"]?>"><br>
<?php } ?>
</td>           </tr>
            
            
<?php if( !empty($company_row["iscorp"]) ) {?>
            <tr>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong><input type='text' name='otherequiptype'  style="font-size: 10px;  font-family: verdana;" size='15' value="<?=$aed_row["otherequiptype"]?>"> ID #:</strong><br><input type="text" size="40" VALUE="<?=$aed_row['otherequipserial']?>" name="otherequipserial" style="font-size: 10px;  font-family: verdana;"></span></td>
<td valign="top" bgcolor="#E2DFDF">&nbsp;</td>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><?php if( empty($company_row["iscorp"]) ) {?> <strong>New Installation?</strong><br><input type="radio" VALUE="1" <?=$aed_row["newinstall"]?"CHECKED":""?> name="newinstall" style="font-size: 10px;  font-family: verdana;"> Yes  <input type="radio" VALUE="0" <?=!$aed_row["newinstall"]?"CHECKED":""?> name="newinstall" style="font-size: 10px;  font-family: verdana;"> No</span><?php } ?></td>
</tr>
<?php } ?>
<?php if( empty($company_row["iscorp"]) ) {?>
            <tr>
                <td valign="top" bgcolor="#E2DFDF"><?php if( empty($company_row["iscorp"]) ) { ?><span class="copy"><strong>Invoiced?</strong><br><input type="radio" VALUE="1" <?=$aed_row["invoiced"]?"CHECKED":""?> name="invoiced" style="font-size: 10px;  font-family: verdana;"> Yes  <input type="radio" VALUE="0" <?=!$aed_row["invoiced"]?"CHECKED":""?> name="invoiced" style="font-size: 10px;  font-family: verdana;"> No
&nbsp;&nbsp;&nbsp;
Invoice number: <input type='text' name='invoiceno' value="<?=$aed_row["invoiceno"]?>" size='10' style="font-size: 10px;  font-family: verdana;"></span><?php } ?></td>
                <td valign="top" bgcolor="#E2DFDF">
<table border='0'><tr><td valign='top'>
<span class="copy"><?php if( empty($company_row["iscorp"]) ) {?> <strong>New Installation?</strong><br><input type="radio" VALUE="1" <?=$aed_row["newinstall"]?"CHECKED":""?> name="newinstall" style="font-size: 10px;  font-family: verdana;"> Yes  <input type="radio" VALUE="0" <?=!$aed_row["newinstall"]?"CHECKED":""?> name="newinstall" style="font-size: 10px;  font-family: verdana;"> No</span><?php } ?></td>
                                                                                                                                                                          <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Install Complete?</strong><br><input type="radio" VALUE="1" <?=$aed_row["installcomplete"]?"CHECKED":""?> name="installcomplete" style="font-size: 10px;  font-family: verdana;"> Yes  <input type="radio" VALUE="0" <?=!$aed_row["installcomplete"]?"CHECKED":""?> name="installcomplete" style="font-size: 10px;  font-family: verdana;"> No</span> </td>
                                                                                                                                                                          <?php if ( $aed_row["installcomplete"] ) { echo( "<td><span class='copy'><strong>Complete as of:</strong><br> " ); printdates2( 'datecompleted', $aed_row["datecompleted"] ); echo( "</span></td>" ); } ?>                                                                                                                                                                          
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Ready To Return?</strong><br><input type="checkbox" VALUE="1" <?=$aed_row["readytoreturn"]?"CHECKED":""?> name="readytoreturn" style="font-size: 10px;  font-family: verdana;"> </td>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>RMA?</strong><br><input type="checkbox" VALUE="1" <?=$aed_row["isrma"]?"CHECKED":""?> name="isrma" style="font-size: 10px;  font-family: verdana;"> </td>
</tr>
</table></td></tr>
<?php } ?>    
                    
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan="2"><span class="copy"><strong>AED Service History:</strong><br>
                <textarea cols="85" name="aedservicehistory" rows="3" wrap="virtual" style="font-size: 10px;  font-family: verdana;"><?=$aed_row['aedservicehistory'];?></textarea></span>
                </td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan="2"><span class="copy"><strong>AED Comments:</strong><br>
                <textarea cols="85" name="aedcomments" rows="3" wrap="virtual" style="font-size: 10px;  font-family: verdana;"><?=$aed_row['aedcomments'];?></textarea></span>
                </td>
            </tr>
<tr><td colspan=2>
            <table>
            <?php
            $events = db_query_rows( "select * from aedevents where serial = '{$aed_row['serial']}'" );
foreach( $events as $erow ) {
?>
<tr>
<td valign="top" bgcolor="#E2DFDF" colspan="2"><span class="copy"><strong>Event Date:</strong>
<?php   echo( $erow["eventdate"] );
    ?>
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="copy"><strong>Date Event Reported:</strong>
     <?php echo( $erow["dateeventreported"] ); ?>          
</span>
                </td>
      <td><a onClick='return confirm( "Are you sure you want to delete this event?" )'  href='editaed.php?aedid=<?=$aed_row['aedid']?>&id=<?=$aed_row['clientid']?>&delevent=<?=$erow['id']?>'>Delete Event</a></td>
            </tr>
  <?php  
}

    ?>          
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan="2"><span class="copy"><strong>Event Date:</strong>
            <?php           printdates2( 'eventdate', "" ); ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="copy"><strong>Date Event Reported:</strong>
<?php           printdates2( 'dateeventreported', "" ); ?>          
</span>
                </td>
<td valign="top" bgcolor="#E2DFDF"><input type='submit' name='addevent' value='Add Event'></td>
    </tr>
    </table>
    </td></tr>
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan="2"><span class="copy"><strong>Event History:</strong><br>
                <textarea cols="85" name="eventhistory" rows="3" wrap="virtual" style="font-size: 10px;  font-family: verdana;"><?=$aed_row['eventhistory'];?></textarea></span>
                </td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#FFFFFF" colspan="2">
                <br>
<?php if( !$readonly ) { ?>
                <div align="center">
                    <input type="submit" onclick="return validateUSPersonalInfo(this)"  name='submit' class=copy value="Save">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="submit" onclick="return validateUSPersonalInfo(this)"  name='submitreturn' class=copy value="Save and Return">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php if( $aedid ){ ?>
                     <input onclick="return confirmDelete()" class=copy type="Submit" name="delete" value="Delete">
        <?php } ?>
                </div>
<?php } ?>
                </td>           
            </tr>   
        </table>
        <br><br>
        <br><br>
        <?php include "ssi/footer.php"; ?>
        </span>
        </td>
        <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
    </tr>
</table>

<script language='javascript'>
function updateWarranty( thedate )
{
    strURL = "getwarranty.php?thedate=" + thedate;
    var req = getXMLHTTP(); // fuction to get xmlhttp object
    if (req)
    {
        req.onreadystatechange = function()
        {
            if (req.readyState == 4) { //data is retrieved from server
                if (req.status == 200) { // which reprents ok status
//      alert( req.responseText );
        document.forms["aedform"].warrantydate.value = req.responseText;
                }
                else
                {
                    alert("There was a problem while using XMLHTTP:\n");
                }
            }
        }
        req.open("GET", strURL, true); //open url using get method
        req.send(null);
    }


}
function updateWarrantySerial( theserial )
{
    strURL = "getwarranty.php?serial=" + theserial;
    var req = getXMLHTTP(); // fuction to get xmlhttp object
    if (req)
    {
        req.onreadystatechange = function()
        {
            if (req.readyState == 4) { //data is retrieved from server
                if (req.status == 200) { // which reprents ok status
//      alert( req.responseText );
        document.forms["aedform"].warrantydate.value = req.responseText;
                }
                else
                {
                    alert("There was a problem while using XMLHTTP:\n");
                }
            }
        }
        req.open("GET", strURL, true); //open url using get method
        req.send(null);
    }

}
</script>
<br><br>
</div>
</form>
</body>
</html>