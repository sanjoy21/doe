<?php
$nologinrequired = true;
require_once "mysql.php";

// Initialize variables from POST/assumed globals, using null coalescing for safety
$id = $_REQUEST['id'] ?? 0;
$submit = $_POST['submit'] ?? false;
$altcontactname = $_POST['altcontactname'] ?? '';
$equipnotes = $_POST['equipnotes'] ?? '';
$availtechnologies = $_POST['availtechnologies'] ?? [];
$newcoi = $_POST['newcoi'] ?? '';
$invoicename = $_POST['invoicename'] ?? '';
$invoicephone = $_POST['invoicephone'] ?? '';
$invoiceemail = $_POST['invoiceemail'] ?? '';
$invoiceinstr = $_POST['invoiceinstr'] ?? '';
$ecards = $_POST['ecards'] ?? 0; // Checkbox, value only present if checked
$none_available = $_POST['none_available'] ?? 0;
$altcontactphone = $_POST['altcontactphone'] ?? '';
$altcontactcell = $_POST['altcontactcell'] ?? '';
$acceptpaymentpolicy = $_POST['acceptpaymentpolicy'] ?? '';
$available_dvdremote = $_POST['available_dvdremote'] ?? '';
$available_computer = $_POST['available_computer'] ?? '';
$available_powerpoint = $_POST['available_powerpoint'] ?? '';

function db_escape($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

// Update host confirm date on page load/submission
db_query("UPDATE class SET hostconfirmdate = NOW() WHERE id = '" . (int)$id . "'");
$crow = getClassRow($id);
$comrow = getCompanyRow($crow['companyid'] ?? 0);

$thanks = false; // Flag for thank you message

if ($submit) {
    // --- 1. Process Alternate Contact Name ---
    $nm = explode(" ", stripslashes($altcontactname));
    $first = trim(array_shift($nm));
    $last = trim(implode(" ", $nm));

    // --- 2. Process Equipment Notes ---
    $enotes = $crow["notes"] ?? '';
    
    if (!empty($equipnotes)) {
        if (!empty($enotes)) {
            $enotes .= "\n\n";
        }
        $enotes .= stripslashes($equipnotes);
    }
    
    if ($none_available) { // This variable seems to be missing from the form but included in PHP
        if (!empty($enotes)) {
            $enotes .= "\n\n";
        }
        $enotes .= "NO AV AVAILABLE";
    }

    // --- 3. Process E-Cards ---
    if ($ecards) {
        db_query("INSERT INTO addedtoecardsviapage (classid, dateadded) VALUES ('" . (int)$id . "', NOW())");
        $ecards = 1; // Ensure it is stored as 1 if the form was submitted
    }

    // --- 4. Process COI Requests and Send Emails ---
    // Note: strpos returns false for "Yes" in the courier strings, so logic may be flawed. 
    // Assuming the intent is to check for specific string matches.
    
    $coi_request_made = false;

    if (str_contains($newcoi, "Yes, from ESI and the courier")) {
        // Option 1: ESI and Courier
        $cc = "jwright@emergencyskills.com, dfunnye@emergencyskills.com, savealife@emergencyskills.com";
        $subject = "Action Needed for COI - " . ($comrow['companyname'] ?? 'N/A') . " - Class " . (int)$id;
        $body = "Thank you for confirming your upcoming CPR AED course. You indicated that you need a COI from Emergency Skills, Inc. and from our courier.\n\nPlease REPLY ALL to this message and attach your sample COI so that we can obtain the requested documents.";
        
        $tos = ["toaddress" => $crow['email'] ?? '', "ccaddress" => $cc];
        sendMail($tos, $subject, $body);
        $coi_request_made = true;
    } 
    
    if (str_contains($newcoi, "Yes, but only from ESI")) {
        // Option 2: Only ESI
        $cc = "jwright@emergencyskills.com, savealife@emergencyskills.com";
        $subject = "Action Needed for COI - " . ($comrow['companyname'] ?? 'N/A') . " - Class " . (int)$id;
        $body = "Thank you for confirming your upcoming CPR AED course. You indicated that you need a COI from Emergency Skills, Inc.\n\nPlease REPLY ALL to this message and attach your sample COI so that we can obtain the requested documents.";
        
        $tos = ["toaddress" => $crow['email'] ?? '', "ccaddress" => $cc];
        sendMail($tos, $subject, $body);
        $coi_request_made = true;
    }
    
    // Original logic: "if( strpos( "Yes", $newcoi ) !== false )" - catches all "Yes" options.
    // If a request was made, send internal notification.
    if ($coi_request_made) { 
        $subject = "COI Request";
        $body = ($comrow['companyname'] ?? 'N/A') . " has requested a COI: {$newcoi}\n\n" .
                "Company name: " . ($comrow['companyname'] ?? 'N/A') . "\n\n" .
                "Class: " . (int)$id . "\n\n" .
                "Date: " . ($crow['startdate'] ?? 'N/A');
                
        // Send internal COI request emails
        sendMail("jwright@emergencyskills.com", $subject, $body);
        sendMail("dfunnye@emergencyskills.com", $subject, $body);
        // sendMail("rachelc@gmail.com", $subject, $body); // Commented out in original
    }
    

    // --- 5. Update Class Record ---
    $sql = "UPDATE class SET 
        notes = '" . db_escape($enotes) . "', 
        avail_technologies = '" . db_escape(implode(";", $availtechnologies)) . "', 
        newcoi = '" . db_escape($newcoi) . "', 
        invoicename = '" . db_escape($invoicename) . "', 
        invoicephone = '" . db_escape($invoicephone) . "', 
        invoiceemail = '" . db_escape($invoiceemail) . "', 
        invoiceinstr = '" . db_escape($invoiceinstr) . "', 
        getsecards = '" . db_escape($ecards) . "', 
        alt_firstname = '" . db_escape($first) . "', 
        alt_lastname = '" . db_escape($last) . "', 
        alt_phone = '" . db_escape($altcontactphone) . "', 
        altcellphone = '" . db_escape($altcontactcell) . "', 
        acceptpaymentpolicy = '" . db_escape($acceptpaymentpolicy) . "', 
        available_dvdremote = '" . db_escape($available_dvdremote) . "', 
        available_computer = '" . db_escape($available_computer) . "', 
        available_powerpoint = '" . db_escape($available_powerpoint) . "' 
        WHERE id = '" . (int)$id . "'";
        
    db_query($sql);
    
    $thanks = true;
}

$avail_equipment_exp = explode(";", $crow['avail_technologies'] ?? '');
$is_corp = (int)($comrow['iscorp'] ?? 0); // Determine if corporate for conditional display
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>Emergency Skills Inc. -- Optimize Your Response Time</title>

<META NAME="Keywords" CONTENT="CPR, CPR Classes, AED, AED training, AED Sales, Defib Sales, Defibrillator, Defibrillators, Defibrillation, Defib, Automatic External Defibrillator, Automatic External Defibrillator, Emergency Skills, Emergency Services, New York, New York City, NYC, NY, emergency, emergencies, urgent, 911, help, cardiac arrest, heart attack, stroke, public defibrillation, public access defibrillator, defib vendor, defib sales, defib training, Cardio Pulmonary Recussitation, Recussitate, Heimlich, Heimlich manuever, choking, pocket mask, ventilation, rescue breathing, manikin, manakin">

<META NAME="Description" CONTENT="EMERGENCY SKILLS, Inc., a corporate safety training company, provides AED sales and CPR training the New York City metro area."> 


<STYLE TYPE="text/css">
a:link { color: #330099; text-decoration: none }
a:active { color: #330099; text-decoration: none }
a:visited { color: #330099; text-decoration: none }
a:hover { color: #330099; text-decoration: none }
</STYLE> 

<link rel="stylesheet" href="/css/confstyle.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

</head>

<body bgcolor="#5a179e" marginwidth="0" marginheight="0" link="blue" visited="blue">

<?php include "ssi/top.php";?>
         

    <script language='javascript'>
    function checkSubmit()
    {
<?php if ($is_corp) { ?>
        if($('input[name=newcoi]:checked').length == 0)
        {
            alert("Please select a COI policy." );
            return false;
        }
        
        if($('input[name=acceptpaymentpolicy]:checked').length > 0)
        {
            return true;
        }
        else
        {
            alert( "You must accept or decline our payment terms." );
            return false;
        }
<?php } ?>
        return true;  
    }
    </script>
<?php if (!$thanks) { ?>
    <form method='post'  onSubmit="return checkSubmit()">
<h1>Thank you! The class details have been confirmed.</h1>

Please take a moment to provide us with this important information.<br /><br />

<strong> 1. All of ESI's courses are video driven. We require either the ability to use a USB drive, stream content from the internet, or attach a DVD player via HDMI to a TV monitor or projector. Please review the choices below and select ALL that apply and will be available to the instructor the day of the training.     
</strong>

<?php 
$allposs = [ 
    "Smartboard or Computer/Projector combination",
    "Hardwired internet",
    "Reliable WiFi network",
    "Available USB port in your computer/laptop",
    "TV/Monitor or Projector with HDMI hookup",
    "We have no A/V capabilities at all. " 
];

// Note: avail_equipment in original PHP was unused/wrong; using $avail_equipment_exp
?>
<div class="table1">
       <table cellpadding="0" cellspacing="0" border="0">
<?php foreach ($allposs as $a) { ?>
<tr>
<td valign='middle'><input type='checkbox' name='availtechnologies[]' value='<?php echo htmlspecialchars($a); ?>' <?php echo in_array($a, $avail_equipment_exp) ? "CHECKED" : ""; ?>></td>
<td valign='middle'><?php echo htmlspecialchars($a); ?></td>
         </tr>
<?php } ?>
       </table>
       </div>
       
<strong>2. Is there an alternate / emergency individual we should contact if we can't reach you?</strong>
           
       <div class="table1">
       <table><tr><td valign="top">
       <table cellpadding="0" cellspacing="0" border="0">
         <tr>
           <td valign="middle"></td>
           <td valign="middle" colspan="2"><strong>Yes, please contact this person:</strong></td>
           </tr>
           
           <tr>
             <td></td>
             <td valign="middle">Name:</td>
             <td valign="middle"><input type="text" name="altcontactname" value="<?php echo htmlspecialchars($crow["alt_firstname"] ?? '') . ' ' . htmlspecialchars($crow["alt_lastname"] ?? ''); ?>" size="30" /></td>
           </tr>
           <tr>
          <td></td>
      <td valign="middle">Phone:</td>
       <td valign="middle"><input type="text" name="altcontactphone" value="<?php echo htmlspecialchars($crow["alt_phone"] ?? ''); ?>" size="30" /></td>
      </tr>
       </table>
      
      </td>
     <td valign="top" style="padding-left: 20px;">
      
      <table cellpadding="0" cellspacing="0" border="0">
     <tr>
       <td valign="middle"></td>
      <td valign="middle"><strong>No, but here is my cell number:</strong></td>
       </tr>      
      <tr>
       <td></td>
      <td valign="middle"><input type="text" name="altcontactcell"   value="<?php echo htmlspecialchars($crow["altcellphone"] ?? ''); ?>" size="30" /></td>
       </tr>    
       </table>
      </td></tr></table>
      </div>
     

<strong>3. Would you like to give us any additional delivery instructions?</strong><br />
<div class="table1"><textarea name="equipnotes" rows="6" cols="50"></textarea></div>


<br><br>

<?php if ($is_corp) { ?>

4. Will you or your building require a new Certificate Of Insurance (COI) prior to your training? 

<table><tr>
<td valign='middle'><input type='radio' name='newcoi' value='Yes, from ESI and the courier who delivers the training equipment' <?php echo ($crow["newcoi"] ?? '') == 'Yes, from ESI and the courier who delivers the training equipment' ? "CHECKED" : ""; ?>></td>
<td valign='middle'>Yes, from ESI and the courier who delivers the training equipment</td>
</tr><tr>
<td valign='middle'><input type='radio' name='newcoi' value='Yes, but only from ESI' <?php echo ($crow["newcoi"] ?? '') == 'Yes, but only from ESI' ? "CHECKED" : ""; ?>></td>
<td valign='middle'>Yes, but only from ESI</td>
</tr><tr>

<td valign='middle'><input type='radio' name='newcoi' value='No, the one we have on file is current' <?php echo ($crow["newcoi"] ?? '') == 'No, the one we have on file is current' ? "CHECKED" : ""; ?>></td>
<td valign='middle'>No, the one we have on file is current</td>
</tr><tr>

<td valign='middle'><input type='radio' name='newcoi' value='I&apos;m not sure.' <?php echo ($crow["newcoi"] ?? '') == 'I\'m not sure.' ? "CHECKED" : ""; ?>></td>
<td valign='middle'>I'm not sure.</td>
</tr><tr>

<td valign='middle'><input type='radio' name='newcoi' value='No' <?php echo ($crow["newcoi"] ?? '') == 'No' ? "CHECKED" : ""; ?>></td>
<td valign='middle'>No</td>
</tr>
</table>

<Br><br>
<strong>5. Payment and Cancellation Policy:</strong>
<ul id="bullet-list-1" style="padding-left: 20px">
<li>Invoice will be sent upon completion of the program.</li>
        To whose attention should the invoice be sent:
        <table><tr>
                     <td>Name: <input type='text' name='invoicename' value='<?php echo htmlspecialchars($crow["invoicename"] ?? ''); ?>'></td>
                     <td> Phone Number: <input type='text' name='invoicephone' value='<?php echo htmlspecialchars($crow["invoicephone"] ?? ''); ?>'></td>
                     </tr>
      <tr>
                     <td> Email: <input type='text' name='invoiceemail' value='<?php echo htmlspecialchars($crow["invoiceemail"] ?? ''); ?>'></td>
                     <td> Add'l Instructions: <input type='text' name='invoiceinstr' value='<?php echo htmlspecialchars($crow["invoiceinstr"] ?? ''); ?>'></td>
                     </tr>
     </table>

<li>Billing will be based upon the final number of participants give 5 business days before the program start date. Any additional participants, thereafter, will be charged accordingly.</li>
<li>Terms: Net 30 days</li>
<li>ESI reserves the right to charge 25% of the agreed cost if a cancelation occurs less than 5 business days prior to program start date.  Cancelations within 24 hours of the program start time may incur the full cost of the course.</li>
<li>Emergency Skills, Inc. will make every effort to ensure that every participant successfully completes the program. However, ESI assumes no liability for a participant who does not complete the program.  They may retake the program at their own expense.</li>
</ul>

       <div class="table1">
         <table cellpadding="0" cellspacing="0" border="0">
           <tr>
            <td valign="middle"><input name='acceptpaymentpolicy' value='1' <?php echo ($crow["acceptpaymentpolicy"] ?? '') == 1 ? "CHECKED" : ""; ?> type="radio" /></td>
       <td valign="middle">Accept</td>
     <td valign="middle" style="padding-left: 20px;"><input name='acceptpaymentpolicy' <?php echo ($crow["acceptpaymentpolicy"] ?? '') == -1 ? "CHECKED" : ""; ?> value='-1' type="radio" /></td>
      <td valign="middle">Decline</td>
      </tr>
       </table>
      </div>
<?php } ?>

      <div class="table1"><input type="submit" name="submit" value="SUBMIT" class="button1" /></div>
    </form>

<?php } else { ?>
<h1>Thank you! The class details have been updated.</h1>
<?php }?>

     
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