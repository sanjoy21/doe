<?php 
require_once "mysql.php";
//echo( "id:$id<br>" );
$appuploadrow = getAppUploadRow($id ?? '');
$uploader = db_query_first( "select * from user where userid = '{$appuploadrow['uploader']}'" );
$values = getAppUploadValues( $id ?? '' );
$companyid = $values["id"] ?? '';
$crow = getCompanyRow( $companyid ?? '' );
$aeds = getAppServiceCallRows( $id ?? '' );
$servicecallid = isset($values["serviceid"]) && $values["serviceid"] ? $values["serviceid"] : ($values["servideid"] ?? '');
if( isset($values["servicecallid"]) && $values["servicecallid"] )
    $servicecallid = $values["servicecallid"];
if( $servicecallid )
    $sc = getServiceCallRow( $servicecallid );
else
{
    print_r( $values );
}

if( isset($values["media_file"]) && $values["media_file"] == $id . "_" )
{
    $allposs = db_query_array( "select uploadid from appuploaddata where name = 'servicecallid' and value = '{$values['servicecallid']}'", "uploadid", "uploadid" );
    $allposs[] = -1;
    $posssig = db_query_first_cell( "select value from appuploaddata where name = 'media_file' and value like '%.%' and uploadid in ( ". implode( ", ", $allposs ) . " )" );
    $posssig2 = db_query_first_cell( "select value from appuploaddata where name = 'media_file_esr' and value like '%.%' and uploadid in ( ". implode( ", ", $allposs ) . " )" );
    $values["media_file"] = $posssig;
    $values["media_file_esr"] = $posssig2;
    
}
if( isset($drillid) && $drillid )
{
    db_query( "update servicecall set fromdrill = 1 where servicecallid = '{$sc['servicecallid']}'" );
    db_query( "update drill set signedby = '" . mysql_escape_string( $appuploadrow['name'] ?? '' ) . "' where drillid = '$drillid'" );          
//    echo( "update drill set signedby = '" . mysql_escape_string( $appuploadrow[name] ) . "' where drillid = '$drillid'" );          
        // echo( "update drill set signedby = '" . mysql_escape_string( $appuploadrow[name] ) . "' where drillid = '$drillid'" );          
}

if( isset($savedata) && $savedata )
{
    db_query( "update servicecall set appid = $id where servicecallid = $servicecallid" );
    if( isset($acceptschool) && $acceptschool )
    {
        $uplvalues = array();
        $uplvalues["address"] =             $values["address"] ?? '';
        $uplvalues["city"] =             $values["city"] ?? '';
        $uplvalues["state"] =             $values["state"] ?? '';
        $uplvalues["zip"] =             $values["zip"] ?? '';
        $uplvalues["schoolphone"] =             $values["phone"] ?? '';
        $uplvalues["principalname"] =             $values["principal"] ?? '';
        $uplvalues["principalemail"] =             $values["principalemail"] ?? '';
        $uplvalues["contactname"] =             $values["contact"] ?? '';
        $uplvalues["contactemail"] =             $values["contactemail"] ?? '';
        $uplvalues["contactphone"] =             $values["contactphone"] ?? '';
        
        foreach( $uplvalues as $colname=>$value )
        {
//            echo( "update company_esi set $colname = '" . mysql_escape_string( $value ) . "' where id = $companyid" );
            db_query( "update company_esi set $colname = '" . mysql_escape_string( $value ) . "' where id = $companyid" );
        }


    }

    $serialkeys = array();
    $serialkeys["adultpadA_lot"] = "padalot";
    $serialkeys["adultpadA_newdate"] = "padaexpiration";
    $serialkeys["adultpadB_lot"] = "padblot";
    $serialkeys["adultpadB_newdate"] = "padbexpiration";
    $serialkeys["pediatric_lot"] = "pedpadlot";
    $serialkeys["pediatric_newdate"] = "pediatricpads";
    $serialkeys["has_frx_pediatric_key"] = "pediatrickey";
    $serialkeys["physicallocation"] = "location";
    $serialkeys["spare_battery_new_date"] = "sparedate";
    $serialkeys["main_battery_new_date"] = "batterydate";
//    $serialkeys["comments"] = "aedcomments";
// we removed this
//    $serialkeys["serial_number"] = "serial";

// not sure for any below
        // $serialkeys["PSAL_AED_out_with_coach"] = "| no                                   |";
//     $serialkeys["request_doe_send_pediatric_key"] = "| no                                   |";
//     $serialkeys["request_doe_send_fast_response_kit"] = "| no                                   |";
//    $serialkeys["datacardstatus"] = "| no                                   |";
    // $serialkeys["status_indicator"] = "| yes                                  |";
    // $serialkeys["unit_unavailable"] = "| no                                   |";
    // $serialkeys["error_with_unit"] = "| no                                   |";
    // $serialkeys["request_doe_send_spare_battery"] = "| no                                   |";
    // $serialkeys["error_info"] = "|                                      |";


    // updating the comments here 
    foreach( $aeds as $tmparow )
    {
        $aedvalues = getAppServiceCallDetailRows( $tmparow['id'] ?? '' );
        $origarow = db_query_first( "select * from aed_esi where aedid = '{$aedvalues['aedid']}' and deleted = 0" );
        $comment = $aedcomments[$origarow['aedid']] ?? '';
        if(isset($comment)) {
            db_query( "update scuploaddetail set value = '" . mysql_escape_string( $comment ) . "' where dataid = '{$tmparow['id']}' and name = 'comments'" );
        }

    }
    
    
    if( isset($acceptservicecall) && $acceptservicecall )
    {
        $uplvalues = array();
        $uplvalues["servicecalldate"] = date( "Y-m-d", strtotime( $appuploadrow["dateinupload"] ?? '' ));
        $uplvalues["servicecalltime"] = date( "H:i:s", strtotime( $appuploadrow["dateinupload"] ?? '' ));
        $uplvalues["inspector"] = ($uploader["first_name"] ?? '') . " " . ($uploader["last_name"] ?? '');
//       print_r( $uplvalues );
        foreach( $uplvalues as $colname=>$value )
        {
            db_query( "update servicecall set $colname = '" . mysql_escape_string( $value ) . "' where servicecallid = $servicecallid" );
        }
        
        db_query( "delete from aed_to_servicecall where servicecallid = '$servicecallid'" );
        if( isset($drillid) && $drillid )
            db_query( "delete from aed_to_drill where drillid = '$drillid'" );
            
        db_query( "delete from aed_new_battery_dates where servicecallid = $servicecallid" );
        
        foreach( $aeds as $tmparow )
        {
            $aedvalues = getAppServiceCallDetailRows( $tmparow['id'] ?? '' );

            $origarow = db_query_first( "select * from aed_esi where aedid = '{$aedvalues['aedid']}' and deleted = 0" );
//            $s = $aedvalues[serial_number]?$aedvalues[serial_number]:$origarow["serial"];
            $s = $origarow["serial"] ?? '';
            if( (isset($aedvalues['status_indicator']) && $aedvalues['status_indicator'] == "yes") || (isset($aedvalues['error_with_unit']) && $aedvalues['error_with_unit'] == "yes") )
            {
                db_query( "insert into aed_to_servicecall ( servicecallid, serial ) values ( '$servicecallid', '$s' )" );
                if( isset($drillid) && $drillid )
                    db_query( "insert into aed_to_drill ( drillid, serial ) values ( '$drillid', '$s' )" );
                    
            }

            if( isset($aedvalues["unit_unavailable"]) && $aedvalues["unit_unavailable"] == "yes" )
            {
                db_query( "update aed_esi set aedservicehistory = concat( aedservicehistory, '\nAED Marked Unavailable {$drillrow['drilldate']}\n' ) where aedid = '{$aedvalues['aedid']}'" );
            }
//            echo( "aedvalued: " . $aedvalues["installed_new_battery"] . "<Br>");
            if( isset($aedvalues["installed_new_battery"]) && $aedvalues["installed_new_battery"] == "yes")
            {
                $servicecalldate = date( "Y-m-d", strtotime( $appuploadrow["dateinupload"] ?? '' ));
                db_query( "insert into aed_new_battery_dates ( aedid, dateadded, servicecallid ) values ( '{$aedvalues['aedid']}', '" . $servicecalldate . "','$servicecallid' )" );
//                echo( "insert into aed_new_battery_dates ( aedid, dateadded, servicecallid ) values ( '$aedvalues[aedid]', '" . $servicecalldate. "','$servicecallid' )" );
            }
                    
            
            $pada = $aedvalues["adultpadA_newdate"] ?? '';

            $uplvalues = array();
            foreach( $serialkeys as $key=>$tablename )
            {
                if( isset($aedvalues[$key]) && $aedvalues[$key] )
                {
                    $val = $aedvalues[$key];
                    if( $val == "yes" ) $val = 1;
                    if( $val == "no" ) $val = 0;

                    if( $tablename == "serial" && !trim( $val ) ) continue;
    
                    $uplvalues[$tablename] = $val;
                    
                    
                }
            }
            foreach( $uplvalues as $column=>$value )
            {
                if( $column == "sparedate" && $value != ($origarow[$column] ?? '') )
                    addOldDate( $origarow['aedid'] ?? '', $origarow["sparedate"] ?? '', "sparedate" );

                db_query( "update aed_esi set $column = '" . mysql_escape_string( $value ) . "' where aedid = '{$origarow['aedid']}'" );
            }


            $toupdate = array( "padaexpiration", "padbexpiration", "sparedate", "batterydate", "pediatricpads" );
            foreach( $toupdate as $fieldname )
            {
                if( isset($origarow[$fieldname]) && $origarow[$fieldname] && isset($uplvalues[$fieldname]) && $uplvalues[$fieldname] && $uplvalues[$fieldname] != $origarow[$fieldname] )
                    addOldDate( $aedid ?? '', $origarow[$fieldname], $fieldname );
            }
            $toupdate = array( "outofservice", "aedstolen" );

            foreach( $toupdate as $fieldname )
            {
                if( isset($origarow[$fieldname]) && $origarow[$fieldname] && isset($uplvalues[$fieldname]) && $uplvalues[$fieldname] && $uplvalues[$fieldname] != $origarow[$fieldname] )
                    addOldDate( $aedid ?? '', date( "Y-m-d" ), $fieldname );
            }
            
            $comment = $aedcomments[$origarow['aedid']] ?? '';
            db_query( "update aed_esi set aedcomments = '" . mysql_escape_string( $comment ) . "' where aedid = '{$origarow['aedid']}'" );
//            echo( "update scuploaddetail set value = '" . mysql_escape_string( $comment ) . "' where dataid = '$tmparow[id]' and name = 'comments'" );
            db_query( "update scuploaddetail set value = '" . mysql_escape_string( $comment ) . "' where dataid = '{$tmparow['id']}' and name = 'comments'" );
            
            $hist = $origarow["aedservicehistory"] ?? '';
//            print_r( $aedvalues );
            if( isset($aedvalues["PSAL_AED_out_with_coach"]) && $aedvalues["PSAL_AED_out_with_coach"] == "yes" )
            {
                if( $hist )
                    $hist .= "\n";
                $hist .= "Out with Coach: " . date( "Y-m-d H:i:s", strtotime( $appuploadrow["dateinupload"] ?? '' ));
            }
            
            if( isset($aedvalues["error_with_unit"]) && $aedvalues["error_with_unit"] == "yes" )
            {
                if( $hist )
                    $hist .= "\n";
                $hist .= "Error With Unit: "  . date( "Y-m-d H:i:s", strtotime( $appuploadrow["dateinupload"] ?? '' ));
            }
            if( isset($aedvalues["error_info"]) && $aedvalues["error_info"] )
            {
                if( $hist )
                    $hist .= "\n";
                $hist .= "Error Description: " . $aedvalues["error_info"];
            }
//            echo( "update aed_esi set aedservicehistory = '" . mysql_escape_string( $hist ) . "' where aedid = '$origarow[aedid]'<br>" );
            db_query( "update aed_esi set aedservicehistory = '" . mysql_escape_string( $hist ) . "' where aedid = '{$origarow['aedid']}'" );
//    db_query( " update aed_esi set aedservicehistory =  replace( aedservicehistory, '\\\'', '''' )" );
            
        }
            // echo( $hist );
            // exit;

    }

        $err = "<br><br><font color='red'>Data saved. <a href='editservicecall.php?servicecallid=$servicecallid'>Click here</a> to view.</font><br><br>";
    
}




?>
<?php if( !isset($noheader) || !$noheader ) { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?php echo date( "Y-m-d", strtotime( $appuploadrow["dateinupload"] ?? '' )); ?>-SC<?php echo $servicecallid ?? ''; ?> (<?php echo ($uploader["first_name"] ?? '') . " " . ($uploader["last_name"] ?? ''); ?>)</title>
  <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
  <?php if( !isset($hasdrillformalready) || !$hasdrillformalready ) { ?>
<form method='post'>

<?php } ?>
 <STYLE type="text/css">
P.breakhere {page-break-before: always}
   td {font-family: arial; font-size: 11px; color: #000000; height: 23px;}
  td.rowA1 {border-top: 1px solid #83afcc; border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 3px;}
td.rowA2 {border-top: 1px solid #83afcc; border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; padding: 3px;}
td.rowB1 {border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 3px;}
td.rowB2 {border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; padding: 3px;}
td.rowAB1 {border-right: 1px solid #83afcc;border-top: 1px solid #83afcc; border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 3px;}
td.rowAB2 {border-right: 1px solid #83afcc;border-top: 1px solid #83afcc; border-bottom: 1px solid #83afcc; padding: 3px;}
   .fontBig {font-size: 24px; font-weight: bold;}
.fontMed {font-size: 1
8px; font-weight: bold;}

 </STYLE>

</head>
<body>
<?php } ?>
      <?php echo $err ?? ''; ?>
<table cellpadding="0" cellspacing="0" border="0" width="650">
<tr>
<td valign="top" style="padding-bottom: 20px;">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="middle" width="70" style="padding-right: 10px;"><img src="images/servicecalllogo.jpg"></td>
<td valign="middle" style="color:#333333; font-size:12px;"><b>Emergency Skills, Inc. <?php if( !isset($crow['iscorp']) || !$crow['iscorp'] ) { ?>/ NYC Department of Education<?php } ?>
<br />AED <?php echo isset($sc["newinstall"]) && $sc["newinstall"] ? "New Install" : "Service Call"; ?></b></td>
<td valign="middle" align="right" style="color:#333333; font-size:12px;">
    <?php if( isset($drillrow["id"]) && $drillrow["id"] ) {    ?>
                               <b>Drill/Inspection</b><br />#&nbsp;D<?php echo $drillid ?? ''; ?>
                               <?php } else { ?>
    <b><?php echo isset($appuploadrow["type"]) && $appuploadrow["type"]=="sc" ? "Service Call" : "New Install"; ?></b><br />#&nbsp;<?php echo isset($appuploadrow["type"]) && $appuploadrow["type"]=="sc" ? "S" : "NI"; ?><?php echo $sc["servicecallid"] ?? ''; ?> <br> <?php if( (!isset($nosave) || !$nosave) && isOverallAdmin() ) { ?><a href='editservicecall.php?servicecallid=<?php echo $servicecallid ?? ''; ?>' target=_blank>View</a><?php } ?>
                                                                              <?php } ?>
<br>Date: <?php echo fixdatefordisplay( $sc["servicecalldate"] ?? '', true ); ?>

</td>
</tr>
</table>
</td>
</tr>
<tr>
<td valign="top">
<table cellpadding="10" cellspacing="0" border="0" style="width: 100%; background-color: #eff5f9; border: 1px #83afcc solid;">
<tr>
<td valign="top" colspan="3"><span class="fontBig"><?php echo isset($crow['iscorp']) && $crow['iscorp'] ? ($crow['displayname'] ?? '') : ($crow['companyname'] ?? ''); ?></span></td>
</tr>
<tr>
<td valign="top" style="width: 33%">
<b>ADDRESS &AMP; PHONE</b><br />
                        <?php echo $values["address"] ?? ''; ?><?php echo getDifferent( "address" ); ?><br />
          <?php echo $values["city"] ?? ''; ?><?php echo getDifferent( "city" ); ?> <?php echo $values["state"] ?? ''; ?><?php echo getDifferent( "state" ); ?>, <?php echo $values["zip"] ?? ''; ?><?php echo getDifferent( "zip" ); ?>
                                             <?php echo $values["phone"] ?? ''; ?><?php echo getDifferent( "phone", "schoolphone" ); ?>
<?php if( !isset($crow['iscorp']) || !$crow['iscorp'] ){  ?><br /><br /> <b>SCHOOL CODE:</b> <?php echo $crow['schoolcode'] ?? ''; ?><?php } ?>
<?php if( (!isset($nosave) || !$nosave) && isOverallAdmin() ) { ?>
<br><input type='checkbox' name='acceptschool' value=1> Accept School Data?
           <?php } ?>
</td>
<?php if( !isset($crow["iscorp"]) || !$crow["iscorp"] ) { ?>

<td valign="top" style="width: 33%">
<b>PRINCIPAL</b><br />
                            <?php echo $values["principal"] ?? ''; ?><?php echo getDifferent( "principal", "principalname" ); ?><br />
<?php echo $values["principalemail"] ?? ''; ?><?php echo getDifferent( "principalemail" ); ?>
</td>
<?php } ?>
<td valign="top" style="width: 33%">
<b>AED CONTACT</b><br />
                                             <?php echo $values["contact"] ?? ''; ?><?php echo getDifferent( "contact", "contactname" ); ?><br />
<?php echo $values["contactemail"] ?? ''; ?><?php echo getDifferent( "contactemail" ); ?><br>
<?php echo $values["contactphone"] ?? ''; ?><?php echo getDifferent( "contactphone" ); ?>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td valign="top">

<?php 

$cnt = 0;
$countfordisplay = 0;
if(isset($aeds) && is_array($aeds)) {
    foreach( $aeds as $tmparow )
    {
            
        $aedvalues = getAppServiceCallDetailRows( $tmparow['id'] ?? '' );
        $origarow = db_query_first( "select * from aed_esi where aedid = '{$aedvalues['aedid']}' and deleted = 0" );
    //     if( $arow[aedstolen] ) continue;
    // if( !$showallaeds && $sc["newinstall"] && !$arow[newinstall] ) continue;
    $cnt++;
    if( (isset($aedvalues["error_with_unit"]) && $aedvalues["error_with_unit"] == "yes") || (isset($aedvalues["status_indicator"]) && $aedvalues["status_indicator"] == "yes") ) $countfordisplay++;
?>
<div style="border: 1px solid black; margin-top: 5px; padding: 2px">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr >
<td valign="top" colspan="5" style="padding-top: 5px; border-bottom: 5px solid #83afcc;">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
                     <td align="bottom"><span class="fontMed">Serial #: <a href='editaed.php?aedid=<?php echo $origarow["aedid"] ?? ''; ?>'><?php echo isset($aedvalues["unit_unavailable"]) && $aedvalues["unit_unavailable"] == "yes" ? "<font color='red'>" : ""; ?><?php echo $origarow["serial"] ?? ''; ?> <?php echo isset($origarow["newinstall"]) && $origarow["newinstall"] ? "(N)" : ""; ?></font></a></span></td>
                     <td align="bottom">
                                                                                                                                                                                                                                                       <?php if( isset($aedvalues["status_indicator"]) && $aedvalues["status_indicator"] == "yes" ) { ?>
<font color='green'><input <?php echo isset($aedvalues["status_indicator"]) && $aedvalues["status_indicator"]=="yes"?"CHECKED":""; ?> type="checkbox">Status Indicator</font>
                                                                                                                                                                                                                                                                                                          <?php } else if( isset($aedvalues["unit_unavailable"]) && $aedvalues["unit_unavailable"] == "yes" ) { ?>
<input  <?php echo isset($aedvalues["unit_unavailable"]) && $aedvalues["unit_unavailable"]=="yes"?"CHECKED":""; ?> type="checkbox">Unit Unavailable
        <?php } else if( isset($aedvalues["error_with_unit"]) && $aedvalues["error_with_unit"] == "yes"  ) { ?>
<input  <?php echo isset($aedvalues["error_with_unit"]) && $aedvalues["error_with_unit"]=="yes"?"CHECKED":""; ?> type="checkbox">There is an ERROR with this unit
 <font color='red'><?php echo $aedvalues["error_info"] ?? ''; ?></font>
        <?php } ?>
                                                                                                                                                                                                                                                       </td>
                                                                                                                                                                                               <td valign="bottom" style="text-align: right; padding-bottom:4px;"><b>Physical Location:</b><br /><?php echo (isset($aedvalues["physicallocation"]) && $aedvalues["physicallocation"] != ($origarow["location"] ?? '')) ? "<font color='red'>" : "<font color='black'>"; ?><?php echo $aedvalues["physicallocation"] ?? ''; ?></font> </td>
</tr>
</table>
</td>
</tr>
<tr>
<td valign="bottom">&nbsp;</td>
<td valign="bottom"><b>Exp. Date:</b></td>
<td valign="bottom"><b>New Date:</b></td>
<td valign="bottom"><b>Lot #: </b></td>
<td valign="bottom"><b>Spare Battery Install Before Date:</b></td>
</tr><?php $red = "";?>
<tr>
<td class="rowA1" width='25%' ><b>Adult Pads A:</b></td>
<td class="rowA2" width='15%' ><?php echo $red . fixdatefordisplay( $aedvalues["adultpadA_expirationdate"] ?? '' ) . "</font>"; ?>&nbsp;</td>
                                                                                                                                         <td class="rowA2"  width='15%'><font color='green'><b><?php echo fixdatefordisplay( $aedvalues["adultpadA_newdate"] ?? '' ); ?></b></font>&nbsp;</td>
                                                                                                                                                                                                                                                                               <?php $red = (isset($aedvalues["adultpadA_lot"]) && $aedvalues["adultpadA_lot"] && ($aedvalues["adultpadA_lot"] != ($origarow["padalot"] ?? ''))) ? "<font color=green><b>" : "<font color='black'>";                                                                                                                                                                                                                                                                                ?>
                                                                                                                                                                                                                                                                               <td class="rowA2"  width='20%'><?php echo $red . (isset($aedvalues["adultpadA_lot"]) && $aedvalues["adultpadA_lot"] ? $aedvalues["adultpadA_lot"] : ($origarow["padalot"] ?? '')); ?></font></b></td>
<?php $red = (isset($aedvalues["spare_battery_new_date"]) && $aedvalues["spare_battery_new_date"] && ($aedvalues["spare_battery_new_date"] != ($origarow["sparedate"] ?? ''))) ? "<font color=red><b>" : "<font color='black'>";                                                                                                                                                                                                                                                                                ?>
                                                                                                                                                                                                                                                                                    <?php $redmain = (isset($aedvalues["main_battery_new_date"]) && $aedvalues["main_battery_new_date"] && ($aedvalues["main_battery_new_date"] != ($origarow["batterydate"] ?? ''))) ? "<font color=red><b>" : "<font color='black'>";                                                                                                                                                                                                                                                                                ?>
                                                                                                                                                                                                                                                                                    <td class="rowA2"  rowspan='1' width='25%' valign='top'><?php echo $red; ?><?php echo fixdatefordisplay( isset($aedvalues["spare_battery_new_date"]) && $aedvalues["spare_battery_new_date"] ? $aedvalues["spare_battery_new_date"] : ($origarow["sparedate"] ?? '') ); ?></font>&nbsp;

</td>
</tr>
<tr><?php $red = "";?>
<td class="rowB1"><b>Adult Pads B:</b></td>
<td class="rowB2"><?php echo $red . fixdatefordisplay( $aedvalues["adultpadB_expirationdate"] ?? '' ) . "</font>"; ?>&nbsp;</td>
<td class="rowB2"><font color='green'><b><?php echo $red . fixdatefordisplay( $aedvalues["adultpadB_newdate"] ?? '' ) . "</font></b>"; ?>&nbsp;<Br>
                                                                                                                                                       </td>
                                                                                                                                                                                                                                                                              <?php $red = (isset($aedvalues["adultpadB_lot"]) && $aedvalues["adultpadB_lot"] && ($aedvalues["adultpadB_lot"] != ($origarow["padblot"] ?? ''))) ? "<font color=green><b>" : "<font color='black'>";                                                                                                                                                                                                                                                                                ?>
                                                                                                                                                        <td class="rowB2"><?php echo $red . (isset($aedvalues["adultpadB_lot"]) && $aedvalues["adultpadB_lot"] ? $aedvalues["adultpadB_lot"] : ($origarow["padblot"] ?? '')); ?></font></td>
                                                                                                                                                             <td class="rowB2" rowspan='2'>  Main Battery Install Date: <?php echo $redmain; ?><?php echo fixdatefordisplay( isset($aedvalues["main_battery_new_date"]) && $aedvalues["main_battery_new_date"] ? $aedvalues["main_battery_new_date"] : ($origarow["batterydate"] ?? '') ); ?></font>&nbsp;</td>
                                                                                                                                                             </tr><?php $red = "";?>
<tr>
<td class="rowB1"><b>Pediatric Pads:</b></td>
<td class="rowB2"><?php echo $red . fixdatefordisplay( $aedvalues["pediatric_expirationdate"] ?? '' ) . "</font>"; ?>&nbsp;</td>
                                                                                                                                         <td class="rowB2"><font color='green'><b><?php echo fixdatefordisplay( $aedvalues["pediatric_newdate"] ?? '' ); ?></b></font>&nbsp;</td>
                                                                                                                                                                                                                                                                              <?php $red = (isset($aedvalues["pediatric_lot"]) && $aedvalues["pediatric_lot"] && ($aedvalues["pediatric_lot"] != ($origarow["pedpadlot"] ?? ''))) ? "<font color=green><b>" : "<font color='black'>";                                                                                                                                                                                                                                                                                ?>
                                                                                                                                                                                                                                                                  <td class="rowB2"><?php echo $red . (isset($aedvalues["pediatric_lot"]) && $aedvalues["pediatric_lot"] ? $aedvalues["pediatric_lot"] : ($origarow["pedpadlot"] ?? '')) . "</font></b>"; ?></td>
</tr>
<tr>
<td colspan="6" style="padding: 0px 0 0 0;"><b>Comments:</b></td>
</tr>
<tr>
<td colspan="6" class="rowA1" >
<textarea style="width: 625px; height: 50px;" name='aedcomments[<?php echo $aedvalues["aedid"] ?? ''; ?>]'><?php echo stripslashes( stripslashes( $aedvalues["comments"] ?? '' ) ); ?></textarea>&nbsp;<br><br><br>
</td>
</tr>
<tr>
<td class='rowB1' colspan='6'>
                                                                                                                           <nobr>
 <?php // print_r( $aedvalues ) ; ?>

                                                                <input  <?php echo isset($aedvalues["has_frx_pediatric_key"]) && $aedvalues["has_frx_pediatric_key"]=="yes"?"CHECKED":""; ?> type="checkbox">Pediatric Key
<?php echo isset($aedvalues["request_doe_send_pediatric_key"]) && $aedvalues["request_doe_send_pediatric_key"]=="yes"?"<font color='red'>":""; ?><input  <?php echo isset($aedvalues["request_doe_send_pediatric_key"]) && $aedvalues["request_doe_send_pediatric_key"]=="yes"?"CHECKED":""; ?> type="checkbox">Request DOE Send Pediatric Key</font>
<?php echo isset($aedvalues["PSAL_AED_out_with_coach"]) && $aedvalues["PSAL_AED_out_with_coach"]=="yes"?"<font color='red'>":""; ?><input <?php echo isset($aedvalues["PSAL_AED_out_with_coach"]) && $aedvalues["PSAL_AED_out_with_coach"]=="yes"?"CHECKED":""; ?> type="checkbox">PSAL Out with Coach</font>
<?php echo isset($aedvalues["fastresponsekit"]) && $aedvalues["fastresponsekit"]=="yes"?"<font color='red'>":""; ?><input  <?php echo isset($aedvalues["fastresponsekit"]) && $aedvalues["fastresponsekit"]=="yes"?"CHECKED":""; ?> type="checkbox">Fast Response Kit</font><br>
<?php echo isset($aedvalues["request_doe_send_fast_response_kit"]) && $aedvalues["request_doe_send_fast_response_kit"]=="yes"?"<font color='red'>":""; ?><input  <?php echo isset($aedvalues["request_doe_send_fast_response_kit"]) && $aedvalues["request_doe_send_fast_response_kit"]=="yes"?"CHECKED":""; ?> type="checkbox"> Request Send Fast Response Kit</font>
<?php echo isset($aedvalues["request_doe_send_spare_battery"]) && $aedvalues["request_doe_send_spare_battery"]=="yes"?"<font color='red'>":""; ?><input  <?php echo isset($aedvalues["request_doe_send_spare_battery"]) && $aedvalues["request_doe_send_spare_battery"]=="yes"?"CHECKED":""; ?> type="checkbox">Request DOE Send Spare Battery<br></font>

                                                                                                                                          <?php echo isset($aedvalues["installed_new_battery"]) && $aedvalues["installed_new_battery"]=="<font color='red'>"?"CHECKED":""; ?><input  <?php echo isset($aedvalues["installed_new_battery"]) && $aedvalues["installed_new_battery"]=="yes"?"CHECKED":""; ?> type="checkbox">Installed New Battery</font>
</td>
</tr>
</table>
</div>
<?php 
    if( ( $cnt == 2 || $cnt == 5 || $cnt == 8 ) && count( $aeds ) > $cnt )
        echo "        <p class='breakhere'></p>";
    } 
} ?>

</td>
</tr>
<tr>
<td valign="top" style="padding-top: 15px;">
<table cellpadding="0" cellspacing="0" border="0" width="500px">
<tr>
<td class="rowAB1" style="background-color: #eff5f9; width: 170px;"><b>Total # of AEDs at <?php echo getSchoolStr( "school" ); ?>:</b>
<td class="rowAB2"><?php echo count( $aeds ?? [] ); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
<td class="rowAB1" style="background-color: #eff5f9; width: 170px;"><b>Total # inspected:</b>
<td class="rowAB2"><?php echo $countfordisplay ?? 0; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
</tr>
</table>
</td>
</tr>
                    <?php if( isset($values["media_file"]) && $values["media_file"] != $id . "_" ) { ?>
<tr>
<td valign="top" style="padding-top: 20px;">
<table cellpadding="0" cellspacing="0" border="1" width="600" bgcolor="#999999">

      <tr bgcolor="#ffffff">
        <td valign="top">School Rep Name:</td>
        <td width="200" colspan='6' valign="top"><?php echo $appuploadrow["name"] ?? ''; ?>&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">School Rep Signature:</td>
        <td width="200" colspan='6' valign="top"><img src='signatures/<?php echo $values["media_file"] ?? ''; ?>' style="width:450px">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">ESI Rep Name:</td>
        <td width="200" colspan='6' valign="top"><?php echo $appuploadrow["esi_repname"] ?? ''; ?>&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">ESI Rep Signature:</td>
       <td width="200" colspan='6' valign="top"><img src='signatures/<?php echo $values["media_file_esr"] ?? ''; ?>' style="width:450px">&nbsp;</td>
      </tr>
                                                                                                                                     <?php } ?>
                                                             </table></td>
  </tr>

<?php if( (!isset($nosave) || !$nosave) && isOverallAdmin() ) { ?>
                                                             <tr><td><br><input type='checkbox' name='acceptservicecall' value=1> Accept Service Call Data?
                                                             <br><input type='submit' name='savedata' value='Save Data'>
                                                             </td></tr>
                                                             <tr><td><input type='button' name='whatever' value='View Printable Version' onClick='document.location.href="billingworksheet.php?d=<?php echo date( "Y-m-d", strtotime( $appuploadrow['dateinupload'] ?? '' )); ?>&schoolid=<?php echo $appuploadrow["schoolid"] ?? ''; ?>&printable=true&scid=<?php echo $id ?? ''; ?>"; '>
                                                             </td></tr>
                                                                                                                                         <?php } ?>
</table>

</body>
</html>