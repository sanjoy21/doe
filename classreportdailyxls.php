<table>
<tr>
<td>Course Date</td>
<td>Course Type</td>
<td>Host School Name</td>
<td>Host School Number</td>
<td>Host School Address</td>
<td>Contact Name</td>
<td>Trainer</td>
<td>Notes</td>
<td>School ID</td>
</tr>
<?php 
foreach( $rep as $class )
{
    // Safely retrieve company row
    $crow = getCompanyRow( $class["companyid"] ?? null );
?>
    <tr>
        <td><?= $class["startdate"] ?? '' ?></td>
        <td><?= $allclass_names[$crow["iscorp"] ?? 0][$class["code"] ?? ''] ?? '' ?></td>
        <td><?= getCompanyName( $class["companyid"] ?? null ) ?></td>
        <td><?= $crow["schoolcode"] ?? '' ?></td>
        <td><?= getCompanyAddress( $class["companyid"] ?? null ) ?></td>
        <td><?= ($class["firstname"] ?? '') . " " . ($class["lastname"] ?? '') ?></td>
        <td>
<?php
$trainers = getTrainers( $class["id"] ?? null );
$any = false;
    foreach( $trainers as $trainerid => $trow ) { ?>
<?= $any ? ", " : "" ?>
        <?= getFullname( $trainerid ) . " - " . ($trow["trainerconfirmeddate"] ?? 'N/A') ?>
<?php $any = true; ?>
        <?php }
?>
</td>
        <td><?= $class["confirmationnotes"] ?? '' ?></td>
        <td><?= $class["companyid"] ?? '' ?></td>
    </tr>
<?php
}
?>
</table>