<table>
<tr>
<td>Instructor Name</td>
<td>Confirmed Date</td>
<td>Host School Name</td>
<td>Host School Address</td>
<td>Training Location</td>
<td>Entrance</td>
<td>Course ID</td>
<td>Course Type</td>
<td>Course Date</td>
<td>Course Start Time</td>
<td>Course End Time</td>
<td>Roster Received</td>
<td>Cancel Date</td>
</tr>
<?php 
foreach( $rep as $class )
{
if( !isset($class["tid"]) || !$class["tid"] )  
{
continue;
}
$comrow = getCompanyRow( $class["companyid"] );
$class_names = $allclass_names[$comrow["iscorp"]];
?>
<tr>
<td><?=getUserNameLastFirst( $class["tid"] )?></td>
<td><?=isset($class["trainerconfirmeddate"]) && $class["trainerconfirmeddate"] && $class["trainerconfirmeddate"]!= "0000-00-00"?$class["trainerconfirmeddate"]:""?></td>
<td><?=$comrow["companyname"]?></td>
<td><?=getCompanyAddress( $class["companyid"] )?></td>
<td><?=getTrainingAddress( $class )?></td>
<td><?=isset($class["school_entrance"])?$class["school_entrance"]:""?></td>
<td><?=$class["id"]?></td>
<td><?=strip_tags( $class_names[$class["code"]] )?></td>
<td><?=date( "Y-m-d", strtotime( $class["startdate"] ) )?></td>
<td><?=date( "h:i A", strtotime( $class["startdate"] ) )?></td>
<td><?=isset($class["enddate"])?$class["enddate"]:""?></td>
<td><?=isset($class["rosterreceived"]) && $class["rosterreceived"]?"Yes":"No"?></td>
<td><?=isset($class["canceldate"]) && $class["canceldate"]?date( "Y-m-d", strtotime( $class["canceldate"] ) ):""?></td>
</tr>
<?php
}
?>