<?php

require_once('mysql.php');

// Define variables to prevent notices in modern PHP environments
$id = $_GET['id'] ?? null;

// Initialize variables that might not be set by the DB query or later logic, 
// preventing notices when accessed in the HTML body.
$companyname = $address = $city = $borough = $companyid = $date_str = $time_str = $code = $name = '';
$firstname = $mi = $lastname = $phone = $phone_ext = $fax = $email = '';
$alt_firstname = $alt_mi = $alt_lastname = $alt_phone = $alt_phone_ext = $alt_fax = $alt_email = '';
$parking_security = $nearest_subway = $notes = '';
$available_tvvcr = $available_tvdvd = $parking_reserved = $reserved_class_adequate = $room_permit = false;
$scheduler_is_contact = false; // Assuming this is set elsewhere or defaults to false

if (!$id) {
    // Handle case where class ID is missing
    echo "Error: Class ID missing.";
    exit;
}

$sql = "
    SELECT 
        c.*,
        s.companyname,
        s.address,
        s.city,
        s.borough,
        s.id as companyid,
        DATE_FORMAT(c.startdate, '%W, %M %e, %Y') as date_str,
        DATE_FORMAT(c.startdate, '%k:%i %p') as time_str
    FROM 
        `class` as c,
        company_esi as s
    WHERE 
        c.id = '{$id}'
        AND c.companyid = s.id
";

$class = db_query_first($sql);

if ($class) {
    // Populate variables from the $class array. This variable variable use is preserved
    // from the original code but is generally not a best practice.
    foreach ($class as $key => $val) {
        ${$key} = $val;
    }
} else {
    echo "Error: Class not found.";
    exit;
}

// Check if the scheduler is the contact, then fetch contact details from the 'user' table
if (isset($scheduler_is_contact) && $scheduler_is_contact) {
    // Use $companyid which was populated from $class
    $sql = "SELECT * FROM user WHERE companyid = '{$companyid}'";
    $user = db_query_first($sql);

    if ($user) {
        // Fix array key access
        $firstname = $user['first_name'] ?? '';
        $mi = $user['mi'] ?? '';
        $lastname = $user['last_name'] ?? '';
        $phone = $user['phone'] ?? '';
        $phone_ext = $user['phone_ext'] ?? '';
        $fax = $user['fax'] ?? '';
        $email = $user['userid'] ?? '';
    }
}

// Apply phone extensions, checking if base variable is set
if (isset($phone_ext) && $phone_ext) {
    $phone = (isset($phone) ? $phone : '') . " Ext. {$phone_ext}";
}

if (isset($alt_phone_ext) && $alt_phone_ext) {
    $alt_phone = (isset($alt_phone) ? $alt_phone : '') . " Ext. {$alt_phone_ext}";
}

// Assuming $class_names is an external array/map
$name = $class_names[$code] ?? "N/A";


// --- HTML Output ---
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>Emergency Skills Inc. -- Optimize Your Response Time</title>

<META NAME="Keywords" CONTENT="CPR, CPR Classes, AED, AED training, AED Sales, Defib Sales, Defibrillator, Defibrillators, Defibrillation, Defib, Automatic External Defibrillator, Automatic External Defibrillator, Emergency Skills, Emergency Services, New York, New York City, NYC, NY, emergency, emergencies, urgent, 911, help, cardiac arrest, heart attack, stroke, public defibrillation, public access defibrillator, defib vendor, defib sales, defib training, Cardio Pulmonary Recussitation, Recussitate, Heimlich, Heimlich manuever, choking, pocket mask, ventilation, rescue breathing, manikin, manakin">

<META NAME="Description" CONTENT="EMERGENCY SKILLS, Inc., a corporate safety training company, provides AED sales and CPR training the New York City metro area.">


<link rel="stylesheet" href="https://<?php echo SUB_DOE. ".".URL_WITHOUT_SUBDOMAIN; ?>/css/style.css">

</head>

<body bgcolor="#5a179e" marginwidth="0" marginheight="0">
<br>
<div align="center">
<table cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" width="700">
    <tr>
        <td valign="top" width="5"><img src="images/dotclear.gif" width="10" alt=""></td>
        <td valign="top" width="476">
        <span class="copy">
        
        <?php include "ssi/topbanner_doe.php"; ?>
        <p>
        
        <!--start center content-->
        
        <BR CLEAR="ALL">
        
        
        <strong><span class="title">CLASS DETAIL</span></strong>
        <hr>
        <table cellpadding="0" cellspacing="0" border="0" width="476">
          <tr>
            <td valign="top">
            <table cellpadding="0" cellspacing="4" border="0">
            <tr>
                <td valign="top" align="right"><span class="copy"><strong>Class:</strong></span></td> 
                <td valign="top"><span class="copy"><?= htmlspecialchars($name) ?></span></td>
            </tr>
            <tr>
                <td valign="top" align="right"><span class="copy"><strong>Date:</strong></span></td>  
                <td valign="top"><span class="copy"><?= htmlspecialchars($date_str) ?></span></td>
            </tr>
            <tr>
                <td valign="top" align="right"><span class="copy"><strong>Time:</strong></span></td>  
                <td valign="top"><span class="copy"><?= htmlspecialchars($time_str) ?></span></td>
            </tr>
            <tr><td colspan="2"><br></td></tr>
            <tr>
                <td valign="top" align="right"><span class="copy"><strong>
                  School:</strong></span></td>  
                <td valign="top"><span class="copy"><?= htmlspecialchars($companyname) ?></span></td>
            </tr>
            <tr>
                <td valign="top" align="right"><span class="copy"><strong>Location:</strong></span></td>  
                <td valign="top"><span class="copy"><?= htmlspecialchars($address) ?>, <?= htmlspecialchars($city) ?></span></td>
            </tr>
            <tr>
                <td valign="top" align="right"><span class="copy"><strong>Borough:</strong></span></td> 
                <td valign="top"><span class="copy"><?= htmlspecialchars($borough) ?></span></td>
            </tr>
            
            </table>
            </td>
            
          </tr>
        </table>
       
        <p><br>
        
        <strong><span class="copy">ATTENDEES</span></strong>
        <hr>
        <table cellpadding="0" cellspacing="0" border="0" width="476">
          <tr>
            <td valign="top">
            <table cellpadding="0" cellspacing="6" border="0">
                <?php 
                // Fix array key access
                $attendees = get_attendees($class["id"]);
                for ($i = 1; $i <= (int)$class["maxattendees"]; $i++) {
                    $arow = $attendees[$i] ?? [];
                    // Fix array key access
                    $attendee = get_attendee($arow["responderid"] ?? null);
                    if ($attendee) {
                ?>
            <tr>
                <td valign="top"><span class="copy"><?= $i ?>.</span></td>
                <td valign="top"><span class="copy"><strong><?= htmlspecialchars($attendee['firstname'] ?? '') ?> <?= htmlspecialchars($attendee['lastname'] ?? '') ?></strong></span></td>
                <td valign="top"><span class="copy">#<?= htmlspecialchars(getIdentifier($attendee)) ?></span></td>
                <td valign="top"><span class="copy"><?= htmlspecialchars($attendee['title'] ?? '') ?></span></td>
            </tr>
                <?php 
                    } // end if attendee
                } // end for loop 
                ?>
            </table>
            </td>  
          </tr>
        </table>
        
        <p><br>
        
        <strong><span class="copy">CONTACTS:</span></strong>
        <hr>

        <table cellpadding="0" cellspacing="4" border="0">
          <tr> 
            <td valign="top"><span class="copy"><strong>On-Site Contact:</strong></span></td>
            <td valign="top"><span class="copy">
              <?= htmlspecialchars($firstname) ?> <?= htmlspecialchars($mi) ?> <?= htmlspecialchars($lastname) ?><br>
              Phone: <?= htmlspecialchars($phone) ?><br>
              Fax: <?= htmlspecialchars($fax) ?><br>
              Email: <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a>
              </span><br><br>
            </td>
          </tr>
           <tr>
            <td valign="top"><span class="copy"><strong>Alternate Contact:</strong></span></td>
            <td valign="top"><span class="copy">
              <?= htmlspecialchars($alt_firstname) ?> <?= htmlspecialchars($alt_mi) ?> <?= htmlspecialchars($alt_lastname) ?><br>
              Phone: <?= htmlspecialchars($alt_phone) ?><br>
              Fax: <?= htmlspecialchars($alt_fax) ?><br>
              Email: <a href="mailto:<?= htmlspecialchars($alt_email) ?>"><?= htmlspecialchars($alt_email) ?></a>
              </span><br><br>
            </td>
          </tr>
        </table>
        
        <p><br>
        
        
        <strong><span class="copy">TRANSPORTATION INFO:</span></strong>
        <hr>
       <table cellpadding="0" cellspacing="4" border="0">
          <tr> 
            <td valign="top"><span class="copy"><strong>Parking/Security:</strong><br>
              <?= nl2br(htmlspecialchars($parking_security)) ?>
              </span><br><br>
            </td>
          </tr>
           <tr>
            <td valign="top"><span class="copy"><strong>Nearest Subway Line / Station:</strong><br>
              <?= nl2br(htmlspecialchars($nearest_subway)) ?>
              </span>
            </td>
          </tr>
        </table>
      
        <p><br>
        
       
        <strong><span class="copy">ADDITIONAL ITEMS::</span></strong>
        <hr>
       
        <?php
        // Clean up logic for available equipment and flags
        $available_equipment = [];
        if ($available_tvvcr ?? false) {
            $available_equipment[] = "TV/VCR";
        }
        if ($available_tvdvd ?? false) {
            $available_equipment[] = "TV/DVD";
        }
        $available = implode(", ", $available_equipment) ?: "None listed";

        $parking = ($parking_reserved ?? false) ? "Yes" : "No";
        $reserved = ($reserved_class_adequate ?? false) ? "Yes" : "No";
        // Assuming $room_permit is correctly set (original code only showed "Yes")
        $room = ($room_permit ?? false) ? "Yes" : "No"; 
        ?>

        <table cellpadding="0" cellspacing="4" border="0">
          <tr>
            <td valign="top"><span class="copy"><strong>Available Equipment:</strong> <?= htmlspecialchars($available) ?></span></td>
          </tr>
          <tr>
            <td valign="top"><span class="copy"><strong>Parking space reserved for the educator:</strong> <?= htmlspecialchars($parking) ?></span></td>
          </tr>
          <tr>
            <td valign="top"><span class="copy"><strong>Reserved classroom of adequate size:</strong>  <?= htmlspecialchars($reserved) ?></span></td>
          </tr>
          <tr>
            <td valign="top"><span class="copy"><strong>Building permit complete:</strong>  <?= htmlspecialchars($room) ?></span></td>
          </tr>
        </table>

    <p><br>
    
<?php if (isset($notes) && $notes) { ?>
        <strong><span class="COPY">NOTES:</span></strong>
        <hr>
        <table cellpadding="0" cellspacing="0" border="0" width="476">
          <tr>
            <td valign="top">
            <table cellpadding="0" cellspacing="4" border="0">
            <tr>
                <td valign="top"><span class="copy">
                  <?= nl2br(htmlspecialchars($notes)) ?>
                  </tr>
            </table>
            </td>  
          </tr>
        </table>
<?php } ?>
       
        <P>
       
       
        <BR><BR><BR><BR>
        
        <!--end center content-->
        
        <?php include "ssi/footer.php" ; ?>
        
        <!--end footer-->
        </span>
        </td>
        <td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
    </tr>
</table>
<br><br>
</div>

<script type="text/javascript">
// This function attempts to open the print dialog 2 seconds after the page loads.
setTimeout(function() {
    window.print();
}, 2000);
</script>

</body>
</html>