<?php 

require_once "mysql.php"; 
$ids = array();

$mainrow = getCompanyRow( $id );
$ids[] = $id;

// if( $mainrow["campusid"] )
// {
// $others = getSchoolsInCampus( $mainrow["campusid"], $mainrow[id] );
// foreach( $others as $o )
// {
//  $ids[] = $o[id];
// }
// }

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
    <title>Emergency Skills Inc. -- Optimize Your Response Time</title>

<META NAME="Keywords" CONTENT="CPR, CPR Classes, AED, AED training, AED Sales, Defib Sales, Defibrillator, Defibrillators, Defibrillation, Defib, Automatic External Defibrillator, Automatic External Defibrillator, Emergency Skills, Emergency Services, New York, New York City, NYC, NY, emergency, emergencies, urgent, 911, help, cardiac arrest, heart attack, stroke, public defibrillation, public access defibrillator, defib vendor, defib sales, defib training, Cardio Pulmonary Recussitation, Recussitate, Heimlich, Heimlich manuever, choking, pocket mask, ventilation, rescue breathing, manikin, manakin">

<META NAME="Description" CONTENT="EMERGENCY SKILLS, Inc., a corporate safety training company, provides AED sales and CPR training the New York City metro area.">

<link rel="stylesheet" href="https://<?php echo SUB_DOE. "." .URL_WITHOUT_SUBDOMAIN; ?>/css/style.css">

</head>
<body>
<table cellpadding="0" cellspacing="0" border="0" width="460">
            <tr>
<td valign="top">

<div align="right"><a href='javascript:window.print()'><img border=0 src="images/button_print.gif"></a></div>

<!--start center content-->
            <strong><span class="title">SITE RESPONSE PLAN</span></strong>
        
<?php foreach( $ids as $tmpid ) { 
    $row = getCompanyRow( $tmpid ); 
?>       
        <p>
        
            <table cellpadding="4" cellspacing="1" border="0" width="450" bgcolor="#999999">
<tr bgcolor="#e1e1f6">
    <td valign="top" width="120"><span class="small"><strong>Company Name:</strong></span></td>
    <td valign="top" width="330"><span class="small"><?php echo $row["companyname"]; ?></span></td>
</tr>
<tr bgcolor="#e1e1f6">
    <td valign="top"><span class="small"><strong>Phone Number:</strong></span></td>
    <td valign="top"><span class="small"><?php echo $row["contactphone"]; ?></span></td>
</tr>
<tr bgcolor="#e1e1f6">
    <td valign="top"><span class="small"><strong>Address:</strong></span></td>
    <td valign="top"><span class="small"><?php echo $row["address"]; ?><br>
    <?php echo $row["city"]; ?> <?php echo $row["state"]; ?>, <?php echo $row["zip"]; ?></span></td>
</tr>
<tr bgcolor="#e1e1f6">
    <td valign="top"><span class="small"><strong>Email Address:</strong></span></td>
    <td valign="top"><span class="small"><?php echo $row["contactemail"]; ?></span></td>
</tr>
<tr bgcolor="#e1e1f6">
    <td valign="top"><span class="small"><strong>Contact Name:</strong></span></td>
    <td valign="top"><span class="small"><?php echo $row["contactname"]; ?></span></td>
</tr>
<tr bgcolor="#e1e1f6">
    <td valign="top"><span class="small"><strong>Signature:</strong></span></td>
    <td valign="top"><span class="small"><br></span></td>
</tr>
            </table>
<?php } ?>
            <p>
<span class="copy">
    
Locations with Automated External Defibrillators (AED) must have a site specific response plan. This plan summarizes your response during an emergency. Please post this plan in a conspicuous location, distribute to all trained responders and update as need. All staff should review periodically to ensure readiness during an emergency.<p>

<strong>PROGRAM MANAGEMENT</strong><br>
Emergency Skills, Inc. is the company contracted to provide system-wide program management of an Automated External Defibrillator program. Questions can be directed to either the site contact listed below or ESI, 212-564-6833 or esialive@emergencyskills.com. <p>

<strong>SITE CONTACT</strong><br>
As designated by the company, the AED contact person is<p>

<strong>Name:</strong> <?php echo $mainrow["contactname"]; ?><br>
<strong>Phone Number:</strong> <?php echo $mainrow["contactphone"]; ?><br>
<strong>Email Address:</strong> <?php echo $mainrow["contactemail"]; ?>
<p>
<strong><em>The responsibilities of the site contact include:</em></strong><br>
Scheduling training, drills, and service calls; Performing monthly maintenance of the AED and notifying Emergency Skills, Inc. (esialive@emergencyskills.com) of completion of maintenance or if the defibrillator is in need of service (see Maintenance Checklist); maintaining records of trained responders and informing Emergency Skills, Inc. of changes. 
<p>

<strong>AED PLACEMENT AND QUANTITY</strong><br>
<p>


            <table cellpadding="4" cellspacing="1" border="0" width="450" bgcolor="#999999">
<tr bgcolor="#e1e1f6">
    <td valign="top"><span class="small"><strong>Serial Number</strong></span></td>
    <td valign="top"><span class="small"><strong>Specific Location</strong></span></td>
</tr>   
<?php 
$aed_rows=db_query_rows("select * from aed_esi a where a.clientid in ( " . join( ",", $ids )." ) and a.deleted=0 order by serial");
foreach( $aed_rows as $a )
{
?>
<tr bgcolor="#ffffff">
    <td valign="top"><span class="small"><?php echo $a["serial"]; ?></span></td>
    <td valign="top"><span class="small"><?php echo $a["location"]; ?></span></td>
</tr>       
<?php } ?>
            </table>


        <p> 

<strong>TRAINED RESPONDERS</strong><br>
In accordance with Federal and State laws, currently trained responders are protected from legal liability when using the AED. The following individuals are currently certified in a nationally recognized program. <p>

        <table cellpadding="4" cellspacing="1" border="0" width="450" bgcolor="#999999" width="470">
<tr bgcolor="#e1e1f6">
    <td valign="bottom" width="300"><span class="small"><strong>Last Name, First Name</strong></span></td> 
    <td valign="bottom" width="300"><span class="small"><strong>Location in Building</strong></span></td> 
    <td valign="bottom" width="300"><span class="small"><strong>Contact Number</strong></span></td> 
<td valign="bottom" align="center" width="85"><span class="small"><strong>Cert Type</strong><br>(id AHA)</span></td>
<td valign="bottom" align="center" width="85"><span class="small"><strong>Training<br>Expiration Date</strong></span></td>
</tr>   
<?php $responder_rows = db_query_rows("select clientid, responderid, firstname, lastname, dayphone, floor from responders_esi where clientid in ( ".join( ",", $ids )." ) and deleted=0 order by lastname");

foreach( $responder_rows as $r )
{
    // Fix unquoted array keys and use safer array access
    $mostcurrent = db_query_first( "Select responder_training_dates.*, class.code from responder_training_dates left join class on classid = class.id where responderid = ".$r['responderid']." order by trainingdate desc" );
    $twoyears = 24*60*365*2*60;
    
    // Safely check for existence before using strtotime
    $training_date = $mostcurrent['trainingdate'];
    $thedt = $training_date ? (strtotime($training_date) + $twoyears) : 0;
    
    // Skip if expiration date is in the past
    if( $thedt < time() )
    continue;

    // Use safer array access for $mostcurrent
    $mostcurrentdt2 = $mostcurrent ? date( "m/d/y", $thedt ) : "N/A";
    
    // Determine class type safely
    $classtype = "N/A";
    if( !empty($mostcurrent["code"]) )
    {
        // Assumes $allclass_names is available and structured correctly
        $classtype = $allclass_names[$mainrow["iscorp"]][$mostcurrent["code"]];
    }
    else if( !empty($mostcurrent["program"]) )
    {
        // Assumes $allclass_names is available and structured correctly
        $classtype = $allclass_names[$mainrow["iscorp"]][$mostcurrent["program"]];
    }
?>

<tr bgcolor="#ffffff">
    <td valign="top"><span class="small">
    <strong><?php echo $r["lastname"]; ?>, <?php echo $r["firstname"]; ?></strong></td>
    <!-- Fixed unquoted key $r[Floor] to $r["floor"] assuming case consistency -->
    <td><span class="small"> <?php echo $r["floor"] ? $r["floor"] . " Floor" : "N/A"; ?></td>
    <td><span class="small"> <?php echo $r["dayphone"]; ?></td>
    <td valign="top" align="center"><span class="small"><?php echo $classtype; ?></span></td>
    <td valign="top" align="center"><span class="small"><?php echo $mostcurrentdt2; ?></span></td>     
</tr>   
<?php } ?>
            </table>
            <p>



<strong>EMERGENCY PROCEDURES</strong>
<ul>
    <li>Upon arrival, assess for scene safety; use universal precautions
    <li>Assess patient for unresponsiveness
    <li>If unresponsive, activate EMS and in-house emergency plan
    <ul><li>Call 911 (Insert procedure to call 911 at this company:          )</li>
<li>Notify security desk, if necessary.<br><br>
    <strong>When calling 911, you must state: "We have a defibrillator."</strong>
</ul></li>
</ul>


<strong>Upon hearing emergency announcement or wall cabinet alarm, any staff member retrieves the AED and responds to location.   Contact an AED responder if one has not arrived at the scene.</strong> <p>

<strong>Once trained responders have arrived at the scene, they shall perform the following: </strong>
<ul><li>Assess for responsiveness </li>
<li>Ensure 911 has been called.</li>
<li>Open airway, assess for breathing. Administer breaths, if needed</li>
<li>Administer chest compressions</li>
<li>Perform CPR (30 compressions followed by 2 breaths until AED arrives)</li>
<li>Apply the AED as soon as it is available.</li>
<li>For victims equal to or less than 55lbs or under 8 years of age, Perform 2 minutes of CPR before applying AED. If needed and available, use pediatric defibrillation pads.</li> 
<li>Turn AED ON</li>
<li>Following AED instructions, apply defibrillation pads in the proper locations. </li>
<li>Do not place the AED pads over the nipple, medication patches, or implanted devices.</li>
<li>When advised, deliver a shock to the patient after first clearing the patient area.</li>
<li>Begin CPR for 2 minutes.</li>
<li>Continue to monitor patient's breathing and perform 1 shock followed by 2 minutes of CPR until otherwise prompted by the AED or EMS personnel.</li>
<li><b>Leave AED ON and attached to the patient until EMS instructs you otherwise. </b>
</li>
</ul>
<p>

<strong>TRANSFER OF CARE TO EMS</strong><br>
<ul><li>Follow instructions of EMS.</li>
<li>Document and communicate important information such as victim's name, age and history to EMS.</li>
<li>Depending upon model, AED screen will display elapsed time and number of shocks delivered or Press blue i-button for an audible summary of event.</li>
<li>Assist as requested by EMS.</li></ul>
<p>

<strong>POST USE PROCEDURES</strong><br>
Immediately following the use of the AED, contact Emergency Skills, Inc. at 212-564-6833. A member of the ESI staff will be dispatched to your site to retrieve data, review AED response, replenish supplies and complete municipal required quality assurance report.   <p>

<strong>ONGOING MAINTENANCE</strong><br>
We recommend that the AED site contact must perform monthly maintenance of AED to include the following:
<ul><li>Verify status indicator is flashing an alternating hour glass and black figure. </li>
<li>Ensure all supplies, accessories and spares are present and are in operating condition.</li>
<li>Inspect the exterior and pads for signs of damage.</li>
<li>Ensure accuracy of trained responder list and certification expiration dates.
</li></ul>
    
<br><br>
Semi-annual drills and AED inspections of your defibrillators are additional services that are available.
 </td>
            </tr>
        </table>

</body>
</html>