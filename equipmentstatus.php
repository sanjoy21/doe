<?php 
include "mysql.php"; 

// $xls = $_REQUEST['xls'] ?? null;
// $session_iscorp = $session_iscorp ?? 0;
// $table = $table ?? 'class_equipment_report';
// $otherfields = $otherfields ?? []; 


$equipmentstatusdate = getsetting('equipmentstatusdate');

// --- Main Query ---
$sql = "SELECT class.*, companyname, schoolcode, company_esi.iscorp 
        FROM company_esi, class 
        WHERE iscorp = '{$session_iscorp}' 
        AND equipreturned = 0 
        AND companyid = company_esi.id 
        AND startdate < NOW() 
        AND class.deleted = 0 
        AND confirmdate IS NOT NULL 
        AND startdate > '{$equipmentstatusdate}' 
        ORDER BY class.startdate";

$res = db_query_rows($sql);

// --- Output Section (CSV or HTML) ---
if (isset($xls) && $xls) {
    // --- CSV Generation (Replacing deprecated Spreadsheet_Excel_Writer) ---
    $filename = "report_{$table}_" . date('Ymd') . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    
    // Define Header Row
    $header = array(
        "Training Date", "School/Company Name", "Location", "Number", "Notes", "Equipment Returned"
    );
    // Add any external fields to the header
    $header = array_merge($header, $otherfields);
    
    fputcsv($output, $header);

    // Write Data Rows
    foreach ($res as $r) {
        $data_row = array(
            fixdatefordisplay($r['startdate']),
            $r["companyname"],
            $r["equipdelivinstr"],
            $r["equipnumber"],
            $r["equipnotes"],
            ($r["equipreturned"]) ? "Y" : "N"
        );
        
        // Add values for any external fields
        foreach ($otherfields as $o) {
            $data_row[] = $r[$o];
        }
        
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
        <th class='copy'>class date</th>
        <th class='copy'>school</th>
        <th class='copy'>location</th>
        <th class='copy'>number</th>
        <th class='copy'>notes</th>
        <th class='copy'>returned?</th>
    </tr>
    <?php 
    foreach ($res as $r) {
        $class_id = $r['id'];
        $company_id = $r["companyid"];
        $returned_status = ($r['equipreturned']) ? "Y" : "N";
    ?>
    <tr>
        <td valign='top' class='copy'>
            <a href='class_detail.php?id=<?php echo $class_id; ?>'>
                <?php echo fixdatefordisplay($r['startdate'], true); // Assumed external function ?>
            </a>
        </td>
        <td valign='top' class='copy'>
            <a href='viewcompany.php?id=<?php echo $company_id; ?>'>
                <?php echo htmlspecialchars($r["companyname"]); ?>
            </a>
        </td>
        <td valign='top' class='copy'><?php echo htmlspecialchars($r['equipdelivinstr']); ?></td>
        <td valign='top' class='copy'><?php echo htmlspecialchars($r['equipnumber']); ?>&nbsp;</td>
        <td valign='top' class='copy'><?php echo htmlspecialchars($r['equipnotes']); ?>&nbsp;</td>
        <td valign='top' class='copy'><?php echo $returned_status; ?></td>
    </tr>
    <?php } ?>
    </table>
     <?php } // End of if ($xls) / else block ?>
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