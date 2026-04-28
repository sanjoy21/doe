<?php

include "mysql.php";

// If the form has been submitted, proceed with data fetching and reporting
if ($doit) { 
    $thetable = "supplyrequests";
    $table = "supplyrequests";
    $extrafields = ", schoolcode, zip";
    $datefield = "datesent";
    $extra = "";
    $swhr = "";

    // --- Date Filtering ---
    if ($fieldfrom) {
        // Assumes fixdate is a function that formats date string for SQL
        $tm = fixdate($fieldfrom); 
        $extra .= " AND {$datefield} >= '{$tm}' ";
    }
    if ($fieldto) {
        $tm = fixdate($fieldto); 
        $extra .= " AND {$datefield} <= '{$tm}' ";
    }

    // --- Status Filtering ---
    if ($uncompletedonly) {
        $extra .= " AND completed = 0";
    }
    
    // --- User Type Filtering (Trainer Visibility) ---
    // Assumes $thisusersrow is populated by a session script
    if (($thisusersrow["usertype"]) == "trainer") {
        $extra .= $visi; // Assumes $visi contains trainer-specific visibility constraints
    }

    // --- 'Since' Date Filtering ---
    if ($since) {
        // Formats the 'since' date for the SQL query
        $since_date = date("Y-m-d", strtotime($since));
        $swhr = " AND datesent > '{$since_date}'";
    }

    // --- Main Query ---
    $sql = "SELECT t.*, companyname, address, city, borough, principalname, contactphone, contactname, contactemail, schoolcode {$extrafields} 
            FROM company_esi, {$table} t {$lj} 
            WHERE iscorp = '{$session_iscorp}' 
            AND companyid = company_esi.id 
            AND showsondrillreports = 1 
            AND datesent > '0000-00-00' {$swhr} {$extra} 
            ORDER BY {$datefield}";
    
    // Execute the query
    $res = db_query_rows($sql);

    // --- Output Section (CSV or HTML) ---
    if ($xls) {
        // --- CSV Generation (Replacing deprecated Spreadsheet_Excel_Writer) ---
        $filename = "report_{$table}_" . date('Ymd') . ".csv";
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $output = fopen('php://output', 'w');
        
        // Define and write Header Row
        $header = array(
            "School", "School Code", "Address", "Telephone Number", "Principal Name", 
            "AED Contact Name", "AED Contact Email", "Date Sent", "Date Replied", 
            "Description", "Completed", "Completed Note"
        );
        fputcsv($output, $header);

        // Write Data Rows
        foreach ($res as $r) {
            $data_row = array(
                $r['companyname'],
                $r['schoolcode'],
                ($r['address']) . ", " . ($r['city']) . ", " . ($r['zip']) . ", " . ($r['borough']),
                $r['contactphone'],
                $r['principalname'],
                $r['contactname'],
                $r['contactemail'],
                $r['datesent'],
                $r['datereplied'],
                $r['descr'],
                ($r['completed']) ? "Y" : "N",
                $r['completednotes']
            );
            fputcsv($output, $data_row);
        }
        
        fclose($output);
        exit;

    } else {
        // --- HTML Table Output ---
        ?>
        <?php include "ssi/top.php"; ?>
        <table cellpadding="3" cellspacing="0" border="1" width="100%">

        <tr>
            <th class='copy'>School</th>
            <th class='copy'>School Code</th>
            <th class='copy'>Address</th>
            <th class='copy'>Telephone Number</th>
            <th class='copy'>Principal Name</th>
            <th class='copy'>AED Contact Name</th>
            <th class='copy'>AED Contact Email</th>
            <th class='copy'>Date Sent</th>
            <th class='copy'>Date Replied</th>
            <th class='copy'>Description</th>
            <th class='copy'>Completed</th>
            <th class='copy'>Completed Notes</th>
        </tr>
        <?php
        foreach ($res as $r) {
        ?>
        <tr>
            <td valign='top' class='copy'><a target=_blank href='viewcompany.php?id=<?php echo $r["companyid"]; ?>'><?php echo $r["companyname"]; ?></a></td>
            <td valign='top' class='copy'><a target=_blank href='viewcompany.php?id=<?php echo $r["companyid"]; ?>'><?php echo $r["schoolcode"]; ?></a></td>
            <td valign='top' class='copy'><?php echo ($r['address']) . ", " . ($r['city']) . ", " . ($r['zip']) . ", " . ($r['borough']); ?></td>
            <td valign='top' class='copy'><?php echo $r["contactphone"]; ?></td>
            <td valign='top' class='copy'><?php echo $r["principalname"]; ?></td>
            <td valign='top' class='copy'><?php echo $r["contactname"]; ?></td>
            <td valign='top' class='copy'><?php echo $r["contactemail"]; ?></td>
            <td valign='top' class='copy'><?php echo $r['datesent']; ?></td>
            <td valign='top' class='copy'><?php echo $r['datereplied']; ?></td>
            <td valign='top' class='copy'><?php echo $r['descr']; ?></td>
            <td valign='top' class='copy'><?php echo ($r['completed']) ? "Y" : "N"; ?></td>
            <td valign='top' class='copy'><?php echo $r['completednotes']; ?></td>
        </tr>
        <?php } ?>
        </table>
        <?php 
        // End of the HTML table output block
        ?>
        
         <br><br><br>
        
         <?php include "ssi/footer.php" ; ?>
        
        </span>
         </td>
        <td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
         </tr>
        </table>
        <br><br>
        </div>
        </body>
        </html>
    <?php } // End of if ($xls) / else block
} else {
    // --- Input Form Display ---
?>
<form method='post'>
Since: <input type='text' name='since' ><br>
XLS: <input type='checkbox' name='xls' value='1' <?php echo $xls ? "CHECKED" : ""; ?> ><br>
Not Completed Only: <input type='checkbox' name='uncompletedonly' value='1' <?php echo $uncompletedonly ? "CHECKED" : ""; ?> ><br>
<?php //echo $concat ? "CHECKED" : ""; ?><input type='submit' name='doit' value='Go'>
</form>
<?php } ?>