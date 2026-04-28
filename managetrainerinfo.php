<?php
// 465-3637 - Original comment preserved
require_once('mysql.php');


if (is_array($todel) && !empty($todel)) {
    foreach ($todel as $d_raw) {
        $d = (int)$d_raw; // Ensure ID is an integer
        if ($d > 0) {
            db_query("DELETE FROM trainerinfo WHERE id = {$d}");
        }
    }
}

// --- 2. Fetch Trainer Records ---
$trainers = db_query_rows("SELECT * FROM trainerinfo WHERE 1 ORDER BY dateadded DESC");
?>
<?php include "ssi/top.php"; ?>
<p>
    <strong><span class="title">INFORMATION FOR TRAINERS</span></strong>

</p>
<form method='post'>
    <table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3">
        <tr bgcolor="#e1e1f6">
            <th class='copy'>Del?</th>
            <th class='copy'>Title</th>
            <th class='copy'>Date Added</th>
            <th class='copy'>Edit</th>
        </tr>
        <?php
        foreach ($trainers as $t) {
            // Sanitize output data
            $t_id_safe = htmlspecialchars($t['id']);
            $t_title_safe = htmlspecialchars($t['title']);
            $t_dateadded_safe = htmlspecialchars($t['dateadded']);

            echo ("<tr bgcolor='white'>
            <td><input type='checkbox' name='todel[]' value='{$t_id_safe}'></td>
            <td>{$t_title_safe}</td>
            <td>{$t_dateadded_safe}</td>
            <td><a href='edittrainerinfo.php?trainerinfoid={$t_id_safe}'>Edit</a></td>
        </tr>"
            );
        }
        ?>
    </table>
    <input type='submit' name='update' value='Update' onClick='return confirm("Are you sure you want to delete these?")'><br><br><br>
    <input type='button' name='addnew' onClick="document.location.href='edittrainerinfo.php'" value='Add New'>
    <?php include "ssi/footer.php"; ?>
    <!--end footer-->