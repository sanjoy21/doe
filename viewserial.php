<?php include "mysql.php"; ?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<?php
$row = getAedRow( $aedid );
$crow = isset($row["clientid"]) ? getCompanyRow( $row["clientid"] ) : array();
?>
<?php if( isset($specialadmin) && $specialadmin ) { ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small">
                    <strong><a href="viewcompany.php?id=<?php echo isset($row["clientid"]) ? $row["clientid"] : ''?>">&laquo; Back to <?php echo getSchoolStr( "School")?></a></strong>
                    </span>
                </td>
</tr>
</table>
<?php } ?>
<br>
<table cellpadding="5" cellspacing="1" border="0" width="100%">

<tr>
<td valign="top" bgcolor="#5a179e" colspan="2">
<table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td><span class="white"><strong>AED Information</strong></span></td>
                            <td valign="top" bgcolor="#5a179e" align="right">
<?php if( !isset($readonly) || !$readonly ) { ?>
                                <a href="editaed.php?id=<?php echo isset($row['clientid']) ? $row['clientid'] : '';?>&aedid=<?php echo isset($row['aedid']) ? $row['aedid'] : '';?>"><span class="white">[Edit AED Info]</span></a>
<?php } ?>
                            </td>
                        </tr>
                    </table>
</td>
</tr>
<tr>
<td valign="top" bgcolor="#ffffff">
<table cellpadding="5" border="0" width="100%">
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"width="55%"><span class="copy"><strong>Serial Number:</strong></td>
<td valign="top"><span class="copy"><?php if( isset($row["outofservice"]) && $row["outofservice"] ) { ?><font color='green'><?php } ?><?php echo isset($row['serial']) ? $row['serial'] : '';?></font> &nbsp;&nbsp;&nbsp;G2005 Update: <?php echo isset($row["hasbeenupdated"]) && $row["hasbeenupdated"] ? "Yes":"No"?>&nbsp;&nbsp;&nbsp;<?php echo isset($row["aedmissing"]) && $row["aedmissing"] ? "<font color='red'><b>MISSING</b></font>":""?><?php echo isset($row["aedretired"]) && $row["aedretired"] ? "<font color='red'><b>RETIRED</b></font>":""?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Model/Type:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['model']) ? $row['model'] : '';?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Floor:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['floor']) ? $row['floor'] : '';?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Location:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['location']) ? $row['location'] : '';?></span></td>
</tr>
<?php 
$str = getOldSchoolsString( $aedid );
if( $str )
{
?>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Previous Locations:</strong></td>
<td valign="top"><span class="copy"><?php echo $str;?></span></td>
</tr>
<?php } ?>

<?php
$newbattinstall = isset($row["aedid"]) ? getNewBatteryInstallDates( $row["aedid"] ) : array();
if( isset($newbattinstall) && count( $newbattinstall ))
{?>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Previous Locations:</strong></td>
<td valign="top"><span class="copy">
<?php
foreach( $newbattinstall as $date=>$servicecallid )
{
echo( "$servicecallid : " . getFormattedDate( $date ) . "<br>" );
}
?>
</span></td>
</tr>

<?php
}
 ?>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Pads A Exp. Date:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['padaexpiration']) ? fixdatefordisplay( $row['padaexpiration'] ) : '';?>
<br>Lot #: <?php echo isset($row['padalot']) ? $row['padalot'] : ''?>

<?php 
$str = isset($aedid) ? getOldDateString( $aedid, "padaexpiration" ) : ''; 
if( $str )
echo( "<br>Old Dates:<br>" . $str );
?>
</span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Pads B Exp. Date:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['padbexpiration']) ? fixdatefordisplay( $row['padbexpiration'] ) : '';?>
<br>Lot #: <?php echo isset($row['padblot']) ? $row['padblot'] : ''?>
<?php 
$str = isset($aedid) ? getOldDateString( $aedid, "padbexpiration" ) : ''; 
if( $str )
echo( "<br>Old Dates:<br>" . $str );
?>
</span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Pediatric Pads Exp. Date:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['pediatricpads']) ? fixdatefordisplay( $row['pediatricpads']) : '';?>
<?php if( isset($row["pediatrickey"]) && $row["pediatrickey"] )
{
echo( "Key" );
}
if( isset($row["pedpadna"]) && $row["pedpadna"] )
{
echo( " N/A" );
}
?>

<br>Lot #: <?php echo isset($row['pedpadlot']) ? $row['pedpadlot'] : ''?><br>
<?php 
   $str = isset($aedid) ? getOldDateString( $aedid, "pediatricpads" ) : ''; 
    if( $str )
        echo( "<br>Old Dates:<br>" . $str );
?>

</span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Spare Battery Install Before Date:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['sparedate']) ? fixdatefordisplay( $row['sparedate']) : '';?>
<?php if( isset($row["sparebattna"]) && $row["sparebattna"] )
{
echo( "N/A" );
}
?>

<?php 
$str = isset($aedid) ? getOldDateString( $aedid, "sparedate" ) : ''; 
if( $str )
echo( "<br>Old Dates:<br>" . $str );
?>
</span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Battery Installation Date:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['batterydate']) ? fixdatefordisplay( $row['batterydate']) : '';?>
<?php 
$installdates = isset($aedid) ? db_query_rows( "select * from aed_new_battery_dates where aedid = $aedid order by dateadded desc limit 10" ) : array();
if( isset($installdates) && is_array($installdates) ) {
    foreach( $installdates as $i )
    {
        echo( getFormattedDate( isset($i["dateadded"]) ? $i["dateadded"] : '' ) . " via service call " . (isset($i["servicecallid"]) ? $i["servicecallid"] : '') . "<br>" );
    }
}
?>

</span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Event History:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['eventhistory']) ? $row['eventhistory'] : '';?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>AED Service History:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['aedservicehistory']) ? $row['aedservicehistory'] : '';?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Purchase Date:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['purchasedate']) ? $row['purchasedate'] : '';?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Internal Reference #:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['irn']) ? $row['irn'] : '';?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Additional Equipment:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['otherequiptype']) ? $row['otherequiptype'] : ''?> ID#: <?php echo isset($row['otherequipserial']) ? $row['otherequipserial'] : ''?></span></td>
</tr>
<?php if( isset($crow["iscorp"]) && !$crow["iscorp"] ) { ?>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Install Complete?</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['installcomplete']) && $row['installcomplete'] ? "Yes - " . (isset($row["datecompleted"]) ? $row["datecompleted"] : '') : "No"?></span></td>
</tr>
<?php  } ?>

</table>
</td>
</tr>
</table>

<br><br>
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