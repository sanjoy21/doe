<?php

require_once('mysql.php');
require_once "birdie/api.php";

if (getcurrentusertype() !== 'principal' || $classid === 0) {
    header("location: index.php");
    exit;
}

// --- 2. Data Retrieval (Logs) ---
$sql = "SELECT * FROM birdielog WHERE classid = $classid ORDER BY whensent DESC";
// Assumed safe database function using parameter binding
$logs = db_query_rows($sql); 

$err = $_GET['err']; // Placeholder for error messages passed via URL
?>
<?php include "ssi/top.php"; ?>
        <script src="featherlight/release/featherlight.min.js" type="text/javascript" charset="utf-8"></script>
        <link type="text/css" rel="stylesheet" href="featherlight/release/featherlight.min.css" />

        <strong><span class="title">Birdie Log for Class <a href='class_edit.php?id=<?= htmlspecialchars($classid) ?>'><?= htmlspecialchars($classid) ?></a></span></strong>
    <?php if (!empty($err)) { echo "<span style='color:red;'>".htmlspecialchars($err)."</span>"; } ?>

<table width='100%' border='1' cellpadding='2' cellspacing='0' class="table3">
<thead>
<tr>
<th>whensent</th>
<th>who</th>
<th>type/call/method</th>
<th>fromdata</th>
<th>retval</th>
</tr>
</thead>
<tbody>
<?php 
if (empty($logs)) {
    echo "<tr><td align='center' colspan='5'>No Birdie logs found for this class.</td></tr>";
} else {
    foreach ($logs as $c) {
        $log_id = htmlspecialchars($c['id']);
        $whensent = htmlspecialchars($c['whensent']);
        $who = htmlspecialchars($c['who']);
        $type = htmlspecialchars($c['type']);
        $birdiecall = htmlspecialchars($c['birdiecall']);
        $method = htmlspecialchars($c['method']);
        
        // Escape content for display within Featherlight modals
        $fromdata_display = nl2br(htmlspecialchars($c['fromdata']));
        $retval_display = nl2br(htmlspecialchars($c['retval']));
?>
        <tr>
            <td valign='top'><?= $whensent ?> (<?= $log_id ?>)</td>
            <td valign='top'><?= $who ?></td>
            <td valign='top'>
                <?= $type ?> <br> 
                <?= $birdiecall ?> <br> 
                <?= $method ?>
            </td>
            <td valign='top'>
                <a data-featherlight="#fl<?= $log_id ?>" href="#">View Request</a>
                <div id='fl<?= $log_id ?>' class="featherlight-content" style='display: none;'>
                    <h3>Request Data (ID: <?= $log_id ?>)</h3>
                    <pre style='white-space: pre-wrap; word-wrap: break-word;'><?= $fromdata_display ?></pre>
                </div>
            </td>
            <td valign='top'>
                <a data-featherlight="#retfl<?= $log_id ?>" href="#">View Response</a>
                <div id='retfl<?= $log_id ?>' class="featherlight-content" style='display: none;'>
                    <h3>Response Data (ID: <?= $log_id ?>)</h3>
                    <pre style='white-space: pre-wrap; word-wrap: break-word;'><?= $retval_display ?></pre>
                </div>
            </td>
        </tr>
    <?php 
    }
}
?>
</tbody>
</table>