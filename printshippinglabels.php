<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>HTML & CSS Avery Labels</title>
    <link href="labels.css" rel="stylesheet" type="text/css" >
    <style>
    body {
        width: 8.5in;
    margin: .25in .1875in; 
        
        font-size: 14px;
        font-family: 'arial';
        }

    .label{
        width: 3.5in; /* plus .6 inches from padding */
        height: 1.875in; /* plus .125 inches from padding */
        padding: .125in .3in 0;
        margin-right: .125in; /* the gutter */

        float: left;

        text-align: center;
        overflow: hidden;

        outline: 1px dotted; /* outline doesn't occupy space like border does */
        }
    .page-break  {
        clear: left;
        display:block;
        page-break-after:always;
        }
    </style>

</head>
<body>
<?php
// Note: This script assumes external functions getClassRow, getCompanyRow, and getClassInfo are defined.
// It also assumes $ids is an array of class IDs available in the script's scope.

$ids = $ids ?? []; // Initialize $ids safely
$ids = array_unique($ids);

foreach ($ids as $id) {

    // Assumed external functions
    $crow = getClassRow($id);
    $comrow = getCompanyRow($crow['companyid'] ?? null);
    $info = getClassInfo($id);

    // Skip if class info is missing or "Pick Up Date" is "jumping"
    if (!count($info) || ($info["Pick Up Date"]["value"] ?? '') == "jumping") {
        continue;
    }

    // Safely extract values for HTML output
    $company_name = htmlspecialchars($comrow['companyname'] ?? '');
    $attention = htmlspecialchars($info["Delivery Attention"]["value"] ?? '');
    $address = htmlspecialchars($info["Delivery Address"]["value"] ?? '');
    $city = htmlspecialchars($info["Delivery City"]["value"] ?? '');
    $state = htmlspecialchars($info["Delivery State"]["value"] ?? '');
    $zip = htmlspecialchars($info["Delivery Zip"]["value"] ?? '');
    $ship_date = htmlspecialchars($info["Pick Up Date"]["value"] ?? '');
    $return_date = htmlspecialchars($info["Return Pick Up Date"]["value"] ?? '');
?>


<div class="label">
<center> <?php echo $company_name; ?><br>
ATTN: <?php echo $attention; ?><br>
<?php echo $address; ?><br>
<?php echo $city; ?> <?php echo $state; ?>, 	<?php echo $zip; ?><br>
SHIP: <?php echo $ship_date; ?><Br>
BAGSET: 19 <br>
</center>
<table width='100%'>
    <tr>
        <td><font size='2'>Return Date: <?php echo $return_date; ?></font></td>
        <td align='right'>Is Jumping?: No</td>
    </tr>
</table>
</div>

<?php
}
?>
<div class="page-break"></div>
</body>
</html>