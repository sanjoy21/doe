<?php
require_once "mysql.php"; 

// --- 1. Input Sanitization ---
// Ensure $id is an integer for database lookups
$id = (int)($_REQUEST['id'] ?? 0); 
$noheader = $_REQUEST['noheader'] ?? false; 

if( $noheader )
    $ids = array();
$mainrow = getCompanyRow( $id );
$ids[] = $id;

ob_start();

if( $mainrow && isset($mainrow["campusid"]) )
{
    // Assuming getSchoolsInCampus takes safe, validated parameters
    $others = getSchoolsInCampus( (int)$mainrow["campusid"], (int)$mainrow["id"] );
    foreach( $others as $o )
    {
        // Ensure only valid integer IDs are added
        $ids[] = (int)$o['id'];
    }
}

if( !$noheader )
{
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
    <title>Emergency Skills Inc. -- Optimize Your Response Time</title>

<META NAME="Keywords" CONTENT="CPR, CPR Classes, AED, AED training, AED Sales, Defib Sales, Defibrillator, Defibrillators, Defibrillation, Defib, Automatic External Defibrillator, Automatic External Defibrillator, Emergency Skills, Emergency Services, New York, New York City, NYC, NY, emergency, emergencies, urgent, 911, help, cardiac arrest, heart attack, stroke, public defibrillation, public access defibrillator, defib vendor, defib sales, defib training, Cardio Pulmonary Recussitation, Recussitate, Heimlich, Heimlich manuever, choking, pocket mask, ventilation, rescue breathing, manikin, manakin">

<META NAME="Description" CONTENT="EMERGENCY SKILLS, Inc., a corporate safety training company, provides AED sales and CPR training the New York City metro area.">

<link rel="stylesheet" href="https://<?php echo SUB_DOE. "." .URL_WITHOUT_SUBDOMAIN ; ?>/css/style.css">

</head>
<body>
<?php } ?>

<table cellpadding="0" cellspacing="0" border="0" width="460">
<tr>
<td valign="top">

<div align="right"><a href='javascript:window.print()'><img border=0 src="images/button_print.gif"></a></div>

<strong><span class="title">CARDIAC EMERGENCY RESPONSE PLAN</span></strong>

<?php
// --- 2. School/Campus Details (XSS Mitigation) ---
foreach( $ids as $tmpid_raw ) { 
    $tmpid = (int)$tmpid_raw; // Ensure ID is integer
 $row = getCompanyRow( $tmpid ); 
    if (!$row) continue; // Skip if row not found
?> 
<p>

<table cellpadding="4" cellspacing="1" border="0" width="450" bgcolor="#999999">
<tr bgcolor="#e1e1f6">
<td valign="top" width="120"><span class="small"><strong>School Name:</strong></span></td>
<td valign="top" width="330"><span class="small"><?=htmlspecialchars($row["companyname"])?></span></td>
</tr>
<tr bgcolor="#e1e1f6">
<td valign="top"><span class="small"><strong>School Number:</strong></span></td>
<td valign="top"><span class="small"><?=htmlspecialchars($row["schoolcode"])?></span></td>
</tr>
<tr bgcolor="#e1e1f6">
<td valign="top"><span class="small"><strong>Address:</strong></span></td>
<td valign="top"><span class="small">
<?=htmlspecialchars($row["address"])?><br>
<?=htmlspecialchars($row["city"])?> <?=htmlspecialchars($row["state"])?>, <?=htmlspecialchars($row["zip"])?></span></td>
</tr>
<tr bgcolor="#e1e1f6">
<td valign="top"><span class="small"><strong>Phone Number:</strong></span></td>
<td valign="top"><span class="small"><?=htmlspecialchars($row["schoolphone"])?></span></td>
</tr>
<tr bgcolor="#e1e1f6">
<td valign="top"><span class="small"><strong>Principal Name & Email:</strong></span></td>
<td valign="top"><span class="small">
<?=htmlspecialchars($row["principalname"])?><br><?=htmlspecialchars($row["principalemail"])?></span></td>
</tr>
<tr bgcolor="#e1e1f6">
<td valign="top"><span class="small"><strong>Signature:</strong></span></td>
<td valign="top"><span class="small"><br></span></td>
</tr>
</table>
<?php } ?>
<p>

<span class="copy">

Locations with Automated External Defibrillators (AED) must have a site specific cardiac emergency response plan. This plan summarizes your response during an emergency. Please post this plan in a conspicuous location, distribute to all staff, regardless of whether they have CPR/AED certification or not. All staff should review periodically to ensure readiness during an emergency. For awareness, the cardiac emergency response plan should be distributed to all new employees as part of the onboarding process.<p> 

<strong>PROGRAM MANAGEMENT</strong><br>
The NYC Department of Education's Office of School Health is responsible for oversight of the AED Program. Emergency Skills, Inc. is the company contracted to the NYC Department of Education to provide system-wide program management of this program, including an AED Program Manager. Questions about overall administration may be directed to Director of ORCS. Emergency Skills, Inc. has assigned Rebekah Carrow as Project Manager. 
<p>

<strong>SITE CONTACT</strong><br>
As designated by the Principal, the AED contact person at this school is<p>

<strong>Name:</strong> <?=htmlspecialchars($mainrow["contactname"] ?? '')?><br>
<strong>Phone Number:</strong> <?=htmlspecialchars($mainrow["contactphone"] ?? '')?><br>
<strong>Email Address:</strong> <?=htmlspecialchars($mainrow["contactemail"] ?? '')?>
<p>
<strong><em>The responsibilities of the site contact include:</em></strong><br>
Scheduling training, drills, and service calls; Performing monthly maintenance of the AED and notifying Emergency Skills, Inc. (rebekah@emergencyskills.com) of completion of maintenance or if the defibrillator is in need of service (see Maintenance Checklist); maintaining records of trained responders and informing Emergency Skills, Inc. of changes. If an athletic director, issuing AEDs to coaches and retrieving AEDs for scheduled drills and inspections. 
<p>

<strong>AED PLACEMENT AND QUANTITY</strong><br>
New York State (NYS) Law requires AED equipment be provided and maintained on-site in each instructional school facility in quantities adequate to ensure ready and appropriate access for use during emergencies. Ideally, AEDs should be located so that the response interval (time from collapse to arrival of the AED) is no more than three minutes and the call-to-shock interval (the time it takes responders to be notified, access the device, reach the victim, apply the electrodes and deliver the first shock) is no more than five minutes. Signage clearly indicating the location of the AED is required. A universal AED sign shall be installed above the cabinet. Where a school sponsored competitive athletic event is held at a site other than a public school facility, the school officials shall assure that AED equipment is provided on-site. Coaches are required to carry portable AEDs to all sport games and practices.

Locations are: 
<p>

<table cellpadding="4" cellspacing="1" border="0" width="450" bgcolor="#999999">
<tr bgcolor="#e1e1f6">
<td valign="top"><span class="small"><strong>Serial Number</strong></span></td>
<td valign="top"><span class="small"><strong>Specific Location</strong></span></td>
</tr>
<?php
// --- 3. AED Locations (SQLi and XSS Mitigation) ---
$safe_ids = array_filter($ids, 'is_int'); // Ensure only integers remain
$ids_list = join( ",", $safe_ids );

// SQLi Mitigation: Use safe, filtered IDs list
$aed_rows = db_query_rows("select * from aed_esi a where a.clientid in ( " . $ids_list ." ) and a.deleted=0 and a.aedstolen = 0 order by serial");

foreach( $aed_rows as $a )
{
?>
<tr bgcolor="#ffffff">
<td valign="top"><span class="small"><?=htmlspecialchars($a["serial"])?></span></td>
<td valign="top"><span class="small"><?=htmlspecialchars($a["location"])?></span></td>
</tr>
<?php } ?>
</table>

<p>

<strong>TRAINED RESPONDERS</strong><br>
In accordance with NYS law, whenever school facilities are used for public school sponsored or school approved curricular or extracurricular events or activities and whenever a school-sponsored athletic contest is held at any location, the school is responsible to ensure the presence of at least one staff person who is trained in a nationally recognized CPR/AED course. Additional trained responders are recommended. Trained Responders are:
<p>

<table cellpadding="4" cellspacing="1" border="0" width="450" bgcolor="#999999" width="470">
<tr bgcolor="#e1e1f6">
<td valign="bottom" width="300"><span class="small"><strong>Name</strong></span></td> 
<td valign="bottom" align="center" width="85"><span class="small"><strong>Cert Type</strong><br>(CPR/AED (6 hours) or Coaches CPR update (2 hours))</span></td>
<td valign="bottom" align="center" width="85"><span class="small"><strong>Training<br>Expiration Date</strong></span></td>
</tr>
<?php
// --- 4. Trained Responders (SQLi and XSS Mitigation) ---
// SQLi Mitigation: Use safe, filtered IDs list
$responder_rows = db_query_rows("select * from responders_esi where clientid in ( ". $ids_list ." ) and deleted=0 order by lastname");

// Assuming $class_names is safely defined elsewhere
global $class_names;

foreach( $responder_rows as $r )
{
    $responderid_int = (int)$r['responderid'];
    
    // Query 2: Use sanitized integer ID
$mostcurrent = db_query_first( "Select responder_training_dates.*, class.code from responder_training_dates left join class on classid = class.id where responderid = {$responderid_int} order by trainingdate desc" );
    
$twoyears = 2 * 365 * 24 * 60 * 60; // Calculate 2 years in seconds
$thedt = 0;
    
    // Check if training date exists before processing
    if (isset($mostcurrent['trainingdate'])) {
        $thedt = strtotime( $mostcurrent['trainingdate']) + $twoyears;
    }
    
if( $thedt > 0 && $thedt < time() )
continue; // Only show valid certifications

    $mostcurrentdt2 = ($mostcurrent && $thedt > 0) ? date( "m/d/y", $thedt ) : "N/A";
    $classtype = "N/A";

    if( isset($mostcurrent["code"]) && $mostcurrent["code"] )
    {
        $classtype = $class_names[$mostcurrent["code"]] ?? $mostcurrent["code"];
    }
    else if( isset($mostcurrent["program"]) && $mostcurrent["program"] )
    {
        $classtype = $class_names[$mostcurrent["program"]] ?? $mostcurrent["program"];
    }
    
    // Sanitize responder details
    $lastname_disp = htmlspecialchars($r["lastname"] ?? '');
    $firstname_disp = htmlspecialchars($r["firstname"] ?? '');
    $clientname_disp = htmlspecialchars(getCompanyName( (int)$r["clientid"] ) ?? '');
    $apt_disp = htmlspecialchars($r["apt"] ?? '');
    $dayphone_disp = htmlspecialchars($r["dayphone"] ?? '');
    $classtype_disp = htmlspecialchars($classtype);
    $mostcurrentdt2_disp = htmlspecialchars($mostcurrentdt2);
?>

<tr bgcolor="#ffffff">
<td valign="top"><span class="small">
<strong><?=$lastname_disp?>, <?=$firstname_disp?></strong><br>
<strong><font color="#525151">School Name:</font></strong> <?=$clientname_disp?><br>
<strong><font color="#525151">Location in School:</font></strong> <?=$apt_disp?> Floor<br>
<strong><font color="#525151">Contact Number:</font></strong> <?=$dayphone_disp?></span></td>
<td valign="top" align="center"><span class="small"><?=$classtype_disp?></span></td>
<td valign="top" align="center"><span class="small"><?=$mostcurrentdt2_disp?></span></td>
</tr> 
<?php } ?>
</table>
<p>

<strong>EMERGENCY PROCEDURES</strong>
<ul>
<li>Upon arrival, assess for scene safety; use universal precautions
<li>Assess patient for unresponsiveness
<li>If unresponsive, activate EMS and in-house emergency plan
</li><p>
<strong>Call 911</strong> - Procedure to call 911 at this school:<br>
_______________________________________
</ul>



When calling 911, you must state: "We have a defibrillator." If 911 caller is not the AED responder stay on the phone until the 911 dispatcher hangs up.<p> 
<ul><strong>Announce Code Blue</strong> - Method to announce Code Blue:<br>
_______________________________________<p>
<?php 
// Query 3: Use sanitized integer ID
$lastdrill = db_query_first("select drill.* from drill left join drill_to_companyid dtc on drill.drillid = dtc.drillid where ( dtc.companyid = '{$id}' or drill.companyid ='{$id}') order by drilldate desc limit 1");

$codeblue_pa = ($lastdrill["codeblue"] ?? '') == "PA";
$codeblue_cell = ($lastdrill["codeblue"] ?? '') == "Cell Phone";
$codeblue_radio = ($lastdrill["codeblue"] ?? '') == "Radio";
$codeblue_other = ($lastdrill["codeblue"] ?? '') == "Other";
$codeblueother_disp = htmlspecialchars($lastdrill["codeblueother"] ?? '');
?> 
<input <?=$codeblue_pa?"CHECKED":""?> type="checkbox">&nbsp;Public Address System (Announce Code Blue (3 times) and location<br>
<input <?=$codeblue_cell?"CHECKED":""?> type="checkbox">&nbsp;Cell Phones<br>
<input <?=$codeblue_radio?"CHECKED":""?> type="checkbox">&nbsp;Radio<br>
<input <?=$codeblue_other?"CHECKED":""?> type="checkbox">&nbsp;Other <?=$codeblueother_disp?>
</ul>

<strong>Upon hearing PA announcement, any staff member retrieves the AED and responds to location.</strong> <p>

<strong>Once trained responders have arrived at the scene, they shall perform the following: </strong>
<ul>

<li>Assess the scene is safe</li>
<li>Check for a response-Tap and Shout</li>
<li>Yell for Help. Have someone call 911 and obtained the AED Administer chest compressions</li>
<li>Check for no normal breathing or only gasping. (Minimum 5 sec, Maximum 10 sec)</li>
<li>Perform CPR (30 compressions followed by 2 breaths until AED arrives)</li>
<li>Apply the AED as soon as it is available.</li>
<li>For victims equal to or less than 55lbs or under 8 years of age, Perform 2 minutes of CPR before applying AED. If needed and available, use pediatric defibrillation pads.</li>
<li>Turn AED ON</li>
<li>Following AED instructions, apply defibrillation pads in the proper locations.</li>
<li>Do not place the AED pads over the nipple, medication patches, or implanted devices.</li>
<li>When advised, deliver a shock to the patient after first clearing the patient area.</li>
<li>Begin CPR for 2 minutes.</li>
<li>Continue to monitor patient's breathing and perform 1 shock followed by 2 minutes of CPR until otherwise prompted by the AED or EMS personnel.</li>
<li>Leave AED ON and attached to the patient until EMS instructs you otherwise.</li>

</ul>
<p>

<strong>TRANSFER OF CARE TO EMS</strong><br>
<ul><li>Follow instructions of EMS</li>
<li>Document and communicate important information such as victim's name, age and history to EMS.</li>
<li>AED screen will display elapsed time and number of shocks delivered.</li>
<li>Assist as requested by EMS.</li></ul>
<p>

<strong>POST USE PROCEDURES</strong><br>
Immediately following the use of the AED, contact <strong>Celeste McGee (718-391-8566) or Husain Thompson (718-391-8227)</strong>. A member of the Emergency Skills, Inc. staff will be dispatched to your site to retrieve data, review AED response, replenish supplies and complete NYS required quality assurance report. <p>

<strong>ONGOING MAINTENANCE</strong><br>
Emergency Skills, Inc.'s staff will perform semi-annual drills and inspections of your defibrillators. The AED site contact must perform monthly maintenance of AED to include the following: 
<ul>

<li> Verify green status indicator is blinking.</li>
<li> Ensure all supplies, accessories and spares are present and are in operating condition.</li>
<li> Inspect the exterior and pads for signs of damage.</li>
<li> Ensure accuracy of trained responder list and certification expiration dates.</li>
</ul>

</td>
</tr>
</table>

</body>
</html>
<?php
$contents = ob_get_contents();
ob_end_clean();
echo( $contents );

// Use a safe, unique filename
$safe_id = (int)$id;
$dt = date("Y-m-d");
file_put_contents( "savedsafetyplans/{$safe_id}-{$dt}.html", $contents );
?>