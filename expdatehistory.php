<?php
require_once('mysql.php');
$drows = db_query_rows( "select * from trainer_exp_dates where trainerid = $trainerid order by id desc" );
$types = array( "tc"=>"TC Affiliation", "other"=>"Other Credentials", "firstaid"=>"First Aid Provider", "cpr"=>"CPR Provider", "aha"=>"AHA CPR Provider" );
?>
<table><tr><td>Group</td><td>Type</td><td>Site</td><td>Exp Date</td></tr>

<?php foreach( $drows as $drow ) { ?>
<tr>
<td><?php echo $drow["expgroup"]; ?></td>
<td><?php 
$typeKey = $drow["type"];
if (isset($types[$typeKey])) {
    echo $types[$typeKey];
} else {
    echo $typeKey;
}
?></td>
<td><?php echo $drow["site"]; ?></td>
<td><?php echo $drow["expdate"]; ?></td>
<?php } ?>