<?php 
$nologinrequired = true;
require "mysql.php";

if( !($fieldname ?? null) )
    $fieldname = "buildingcode";
?>
<Font color='black'><b>Last, choose your building:</b></font><br>
<font color='black'>If your building does not appear, please disregard and continue with registration.</font>
<span class=copy>
    <?php echo getBuildingPulldown( $id ?? null, "", $fieldname, "style='font-size: 10px; font-family: verdana;' id='companyid'" ); ?>