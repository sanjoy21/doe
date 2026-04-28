<?php
// Assumed external functions: require_once('mysql.php'), get_companyid, db_query_rows, db_escape
require_once('mysql.php');

// Initialize variables safely
$session_id = $_SESSION['session_id'] ?? ''; // Assumed session variable
$companyid = $_REQUEST['companyid'] ?? null; // Can be set via GET/POST or retrieved below
$specialadmin = $_SESSION['specialadmin'] ?? false; // Assumed privilege flag
$visi = $_SESSION['visi_filter'] ?? ''; // Assumed visibility filter for admin

$defibs = [];
$rows = [];

// Determine the client ID for viewing
if (!$companyid) {
    // Attempt to retrieve company ID from session/user data
    $companyid = get_companyid($session_id);
}

// Ensure $companyid is treated as a string for SQL LIKE comparison
$companyid_filter = db_escape($companyid ?? '');
if (empty($companyid_filter)) {
    // Default to viewing all if necessary, or prevent query
    $companyid_filter = '%';
}

if ($companyid_filter !== '') {
    // --- 1. Fetch AED Details ---
    $sql = "
        SELECT
            serial, model, floor, location, 
            DATE_FORMAT(padaexpiration, '%m/%Y') AS padaexpiration, 
            DATE_FORMAT(padbexpiration, '%m/%Y') AS padbexpiration, 
            DATE_FORMAT(pediatric_pads_exp_date, '%m/%Y') AS pediatric_pads_exp_date, 
            DATE_FORMAT(sparedate, '%m/%Y') AS sparedate, 
            DATE_FORMAT(batterydate, '%m/%Y') AS batterydate, 
            directorname, 
            DATE_FORMAT(filingexpirationdate, '%m/%Y') AS filingexpirationdate, 
            DATE_FORMAT(medicalinvoicedate, '%m/%Y') AS medicalinvoicedate, 
            eventhistory, irn
        FROM aed_esi
        WHERE clientid LIKE '{$companyid_filter}'
    ";
    
    // If $companyid_filter is '%', this fetches all AEDs. If it's a specific ID, it fetches that client's AEDs.
    $defibs = db_query_rows($sql);
}

// --- 2. Fetch Company List (for Admin Filter) ---
$sql_companies = "SELECT * FROM company_esi WHERE deleted=0 {$visi} ORDER BY companyname";
$rows = db_query_rows($sql_companies);
?>

<?php include "ssi/top.php"; ?>    
<span class="title"><strong>Defibrillator Information</strong></span>
       <BR><hr>

       The following defibrillators are associated with your school. Click the links below, or scroll down for detailed information.<p>
<?php if ($specialadmin) { ?>    
<form>
Select a specific school to view: 
<select name='companyid' onChange='javascript:document.location.href="defib_detail.php?companyid=" + this.options[this.selectedIndex].value'>
    <option value='%' <?php echo $companyid == '%' ? "SELECTED" : ""; ?>>-- View All Schools --</option>
<?php foreach ($rows as $r) { 
    $row_id = htmlspecialchars($r['id'] ?? '');
    $row_name = htmlspecialchars($r['companyname'] ?? '');
    ?>
    <option <?php echo $companyid == $row_id ? "SELECTED" : ""; ?> value='<?php echo $row_id; ?>'><?php echo $row_name; ?></option>
<?php } ?>
</select>
</form> 
<?php } ?>
       <ul>
         <?php
         if (is_array($defibs)) {
             foreach ($defibs as $defib) {
                                $serial_safe = htmlspecialchars($defib['serial'] ?? '');
                 echo "<a href='#{$serial_safe}'>{$serial_safe}</a><br>";
             }
         }
         ?>
       </ul>
       
       <hr>
       
<?php
if (empty($defibs) && $companyid_filter !== '%') {
    echo "<div style='font-style:italic;padding:20px;'>There are currently no defibrillators available for this company.</div>";
}

if (is_array($defibs)) {
    foreach ($defibs as $defib) {
        // Assigning to safe local variables for cleaner HTML output
        $serial = htmlspecialchars($defib['serial'] ?? '');
        $model = htmlspecialchars($defib['model'] ?? '');
        $floor = htmlspecialchars($defib['floor'] ?? '');
        $location = htmlspecialchars($defib['location'] ?? '');
        $padaexpiration = htmlspecialchars($defib['padaexpiration'] ?? '');
        $padbexpiration = htmlspecialchars($defib['padbexpiration'] ?? '');
        $pediatric_pads_exp_date = htmlspecialchars($defib['pediatric_pads_exp_date'] ?? '');
        $sparedate = htmlspecialchars($defib['sparedate'] ?? '');
        $batterydate = htmlspecialchars($defib['batterydate'] ?? '');
        $directorname = htmlspecialchars($defib['directorname'] ?? '');
        $filingexpirationdate = htmlspecialchars($defib['filingexpirationdate'] ?? '');
        $medicalinvoicedate = htmlspecialchars($defib['medicalinvoicedate'] ?? '');
        $eventhistory = htmlspecialchars($defib['eventhistory'] ?? '');
        $irn = htmlspecialchars($defib['irn'] ?? '');
        
?>

       <table cellpadding="5" cellspacing="1" border="0" width="100%">         
         <tr>
          <td valign="top" bgcolor="#ffffff">       
          <table cellpadding="5" border="0" width="100%"> 
       <tr>
       <td colspan="2" bgcolor="#5a179e"><span class="white"><strong><a name="<?php echo $serial; ?>">AED #<?php echo $serial; ?></a></strong></span></td>         
      </tr>
      <tr>
      <td valign="top" bgcolor="#E2DFDF" align="right" width="55%"><span class="copy"><strong>Serial Number:</strong></td>
     <td valign="top"><span class="copy"><?php echo $serial; ?></span></td>
      </tr>        
     <tr>
      <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Model/Type:</strong></td>
       <td valign="top"><span class="copy"><?php echo $model; ?></span></td>
       </tr>
       <tr>
       <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Floor:</strong></td>
       <td valign="top"><span class="copy"><?php echo $floor; ?></span></td>
      </tr>
       <tr>
       <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Location:</strong></td>
       <td valign="top"><span class="copy"><?php echo $location; ?></span></td>
       </tr>
       <tr>
       <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Pads A Exp. Date:</strong></td>
       <td valign="top"><span class="copy"><?php echo $padaexpiration; ?></span></td>
       </tr>
       <tr>
       <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Pads B Exp. Date:</strong></td>
       <td valign="top"><span class="copy"><?php echo $padbexpiration; ?></span></td>
      </tr>
       <tr>
       <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Pediatric Pads Exp. Date:</strong></td>
       <td valign="top"><span class="copy"><?php echo $pediatric_pads_exp_date; ?></span></td>
     </tr>
       <tr>
       <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Spare Battery Install Before Date:</strong></td>
       <td valign="top"><span class="copy"><?php echo $sparedate; ?></span></td>
       </tr>
      <tr>
       <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Battery Installation  Date:</strong></td>
       <td valign="top"><span class="copy"><?php echo $batterydate; ?></span></td>
      </tr>
       <tr>
       <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Medical Director:</strong></td>
       <td valign="top"><span class="copy"><?php echo $directorname; ?></span></td>
       </tr>
       <tr>
      <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Municipal Filing Exp. Date:</strong></td>
       <td valign="top"><span class="copy"><?php echo $filingexpirationdate; ?></span></td>
       </tr>
       <tr>
       <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Medical Direction Invoice Date:</strong></td>
      <td valign="top"><span class="copy"><?php echo $medicalinvoicedate; ?></span></td>
       </tr>
      <tr>
       <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Event History:</strong></td>
       <td valign="top"><span class="copy"><?php echo $eventhistory; ?></span></td>
      </tr>
       <tr>
       <td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Internal Reference #:</strong></td>
       <td valign="top"><span class="copy"><?php echo $irn; ?></span></td>
      </tr>
      
      </table>
      
       </td>
      </tr>
      </table>
      <br><div align="right"><a href="#top"><img src="images/backtotop.gif" border="0"></a></div><hr>

<?php
    }
}
?>


      <br><br><br><br>
      
       <?php include "ssi/footer.php"; ?>
       
       </span>
       </td>
      <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
       </tr>
</table>
<br><br>
</div>
</body>
</html>