<?php
// NOTE: Assumed functions like getCompanyRow(), db_query_rows_safe(), 
// isOverallAdmin() must be defined and available.

// Assume $id (Company ID) and $pagename (Current script name, e.g., 'company_view.php') 
// are available from the calling script's scope.
$id = (int) ($id ?? $_GET['id'] ?? 0); 
$pagename = htmlspecialchars($pagename ?? $_SERVER['PHP_SELF']); 

if ($id <= 0) {
    // Basic error handling if $id is missing
    echo "<div align='center'><span style='color:red;'>Error: Company ID missing.</span></div>";
    return;
}

// Fetch company information (optional, but in original code)
$row = getCompanyRow($id);

// Fetch phone call log data securely
$sql = "SELECT * FROM phonecalls WHERE companyid = ? ORDER BY calldate DESC";
$phonecalls = db_query_rows($sql, [$id]);
?>

<div align="center">
    <table width='100%'>
        <input type='hidden' name='completednote' value=''> 
        
        <script>
            function confirmComplete(id) {
                const note = prompt("Please enter any notes here.");
                if (note === null) {
                    return false; // User clicked cancel
                }
                
                // Get the base URL from the element's href
                const link = document.getElementById("scomplete" + id);
                let href = link.href.split('&completednote=')[0]; // Remove old note parameter
                
                // Append the new, encoded note
                link.href = href + "&completednote=" + encodeURIComponent(note);
                
                return true; // Proceed with navigation
            }
        </script>
        
        <tr>
            <td valign="top">
                <span class="copy">
                    <strong>
                        <table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
                            <tr bgcolor="#e1e1f6">
                                <th><span class='copy'>Completed?</span></th>
                                <th><span class='copy'>Date</span></th>
                                <th><span class='copy'>Description</span></th>
                                <?php if (isOverallAdmin()) { ?>
                                <th style='width: 1px;'></th> <?php } ?>
                            </tr>
                            
                            <?php foreach ($phonecalls as $r) { 
                                $is_completed = (bool) ($r['completed'] ?? false);
                                $call_id = htmlspecialchars($r['id'] ?? '');
                                $description = htmlspecialchars($r['description'] ?? '');
                                $date_formatted = date("m/d/y h:i a", strtotime($r['calldate'] ?? ''));
                            ?>
                            <tr bgcolor='#ffffff'>
                                <td> 
                                    <span class="copy">
                                        <?= $is_completed ? "Y" : "N" ?>

                                        <?php if (!$is_completed) { ?>
                                            <a id="scomplete<?= $call_id ?>" 
                                               onClick='return confirmComplete(<?= $call_id ?>)' 
                                               href='<?= $pagename ?>?id=<?= htmlspecialchars($id) ?>&markcompleted=<?= $call_id ?>'>
                                               Complete
                                            </a>
                                        <?php } ?>
                                    </span>
                                </td>
                                
                                <td class='copy'><?= $date_formatted ?></td> 
                                <td class='copy' width='50%'><?= $description ?></td> 
                                
                                <?php if (isOverallAdmin()) { ?>
                                <td style='width: 1px;'></td> <?php } ?>
                            </tr>
                            
                            <?php if (isOverallAdmin() && $is_completed) { 
                                $completed_date = date("m/d/y h:i a", strtotime($r['completeddate'] ?? ''));
                                $completed_note = htmlspecialchars($r['completednote'] ?? '');
                            ?>
                            <tr>
                                <td colspan='4' bgcolor='white' class='copy'>
                                    Completed on: <?= $completed_date ?>: <?= $completed_note ?>
                                </td>
                            </tr>
                            <?php } ?>
                            
                            <?php if (isOverallAdmin()) { ?>
                            <tr>
                                <td colspan='4'><img src='images/spacer.gif' height='1'></td>
                            </tr>
                            <?php } ?>
                            <?php } // End foreach ?>
                            
                            <?php if (isOverallAdmin()) { ?>
                            <tr>
                                <td><input class='copy' type='submit' name='addnewcall' value='Add New'></td>
                                <td>
                                    <input class='copy' type='text' name='newcalldate' size='15' 
                                           value="<?= date("m/d/y H:i") ?>">
                                </td>
                                <td colspan='2'>
                                    <textarea name='newcall' cols='45' rows='3'></textarea>
                                </td>
                            </tr>
                            <?php } ?>
                        </table>
                    </strong>
                </span>
            </td> 	 	 	 	 	 
        </tr>
    </table>
</div>
<br>