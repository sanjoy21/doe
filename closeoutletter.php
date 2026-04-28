<?php

include "mysql.php";

$class = getClassRow( $id );
//$attendees = db_query_rows( "select lastname, firstname, r.responderid from responder_to_class rtc, responders_esi r where rtc.responderid = r.responderid and classid = $id order by lastname, firstname " );
$comrow= getCompanyRow( $class['companyid'] );
$companyname = array_shift( explode( "[", $comrow['companyname'] ) );
$first = true;

// --------------------------------------------------------------------------
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
<!-- ======================================================= -->
<!-- Created by AbiWord, a free, Open Source wordprocessor.  -->
<!-- For more information visit http://www.abisource.com.  -->
<!-- ======================================================= -->
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<title>closeoutletter.html</title>

<!-- <style type="text/css">

P.breakhere {page-break-before: always}

#toc,
.toc,
.mw-warning {
border: 1px solid #aaa;
background-color: #f9f9f9;
padding: 5px;
font-size: 95%;
}
#toc h2,
.toc h2 {
display: inline;
border: none;
padding: 0;
font-size: 100%;
font-weight: bold;
}
#toc #toctitle,
.toc #toctitle,
#toc .toctitle,
.toc .toctitle {
text-align: center;
}
#toc ul,
.toc ul {
list-style-type: none;
list-style-image: none;
margin-left: 0;
padding-left: 0;
text-align: left;
}
#toc ul ul,
.toc ul ul {
margin: 0 0 0 2em;
}
#toc .toctoggle,
.toc .toctoggle {
font-size: 94%;
}@media print, projection, embossed {
body {
padding-top:1in;
padding-left:1in;
padding-right:1in;
}
}
#header {
position: relative;
width: 100%;
height: auto;
top: 0;
bottom: auto;
right: 0;
left: 0;
}
#footer {
position: relative;
width: 100%;
height: auto;
top: auto;
bottom: 0;
right: 0;
left: 0;
}
body {
font-family:'Times New Roman';
}
table {
}
td {
border-collapse:collapse;
text-align:left;
vertical-align:top;
}
p, h1, h2, h3, li {
font-family:'Times New Roman';
}
*._normal {
font-size:12.000000pt;
}
*.address {
font-family:'Verdana';
font-size:9.000000pt;
margin-left:28.800000pt;
}
*.footer {
}
*.header {
}
*.salutation {
}
</style> -->
</head>
<body>
<div id="main">
<div>
<table width='100%'>
<tr><td>
<span style="font-size:10pt;font-family:'Arial';color:#000000" xml:lang="-none-" lang="-none-"><?=date( "F j, Y" )?></span>
<p></p>
<span style="font-size:10pt;font-family:'Arial'">
<?=$class['firstname']?> <?=$class['lastname']?><br><?=$companyname?><br /><?=$comrow['address']?> <br /><?=$comrow['city']?>, <?=$comrow['state']?> <?=$comrow['zip']?></span>
</td><td align='right'><img src='images/closeout.jpg' align='right'>
</td></tr></table>
<p class="salutation _normal"><span style="font-size:10pt;font-family:'Arial'">Dear <?=$class['firstname']?>:</span></p>
<p></p>
<p><span style="font-size:10pt;font-family:'Arial'">Thank you for choosing Emergency Skills, Inc. for your training program. We are delighted to enclose the certification cards for the individuals who have successfully completed the American Heart Association (AHA) Heartsaver Automated External Defibrillator (AED) program. They are prepared to respond to a colleague or a loved one with the life saving skills of Cardiopulmonary Resuscitation (CPR) and Defibrillation.</span></p>
<p></p>
<p><span style="font-size:10pt;font-family:'Arial'">There are a few important points that we would like you to keep in mind:</span></p>
<ul>
<li >&nbsp;<span style="font-size:10pt;font-family:'Arial'">Annual Training: While the CPR and AED certification is valid for 2 years, </span><span style="font-style:italic;font-size:10pt;font-family:'Arial'">annual training is required</span><span style="font-size:10pt;font-family:'Arial'"> when using our Medical Director. &nbsp;</span></li>
<li>&nbsp;<span style="font-size:10pt;font-family:'Arial'">Post Event Procedures: If you use your AED during an emergency, please call us after the ambulance has left. ESI will coordinate the ECG data download and update your AED supplies.</span></li>
<li>&nbsp;<span style="font-size:10pt;font-family:'Arial'">AED Servicing: If your AED is chirping please call us at 212-564-6833. Please have the AED with you when you call. &nbsp;</span></li>
<li>&nbsp;<span style="font-size:10pt;font-family:'Arial'">Additional Training: ESI will contact you next year to schedule training. In the meantime, if you have new employees who require training, feel free to call us. ESI offers individual training at our facility or additional training can be scheduled at your company. </span> </li>
</ul>
<p></p>
<p><span style="font-size:10pt;font-family:'Arial'">Emergency Skills, Inc. offers a complete line of emergency products and services for your company including the following: </span><span style="font-style:italic;font-size:10pt;font-family:'Arial'">Philips Healthcare Automated External Defibrillators and accessories, AED Medical Direction and Public Health Law Compliance, ALIVE!net AED Management, ALIVE! First Aid training, and CPR Barrier Devices.</span></p>
<p style="margin-left:18.000000pt"></p>
<p><span style="font-size:10pt;font-family:'Arial'">If you have questions or would like additional information please call our office at 212-564-6833 or visit our website at www.emergencyskills.com. &nbsp;</span></p>
<p class="salutation _normal"></p>
<p><span style="font-size:10pt;font-family:'Arial'">It is a pleasure working with you and training your staff to be prepared to TAKE ACTION in an emergency. &nbsp;We, at Emergency Skills, Inc., look forward to working with you again. </span></p>
<p></p>
<p><span style="font-size:10pt;font-family:'Arial'">Sincerely,</span></p>
<p></p>
<br>
<p>
<table width='700'>
<tr><td width='50%'><span style="font-size:10pt;font-family:'Arial'">Sarah Gillen</span>
<td><span style="font-size:10pt;font-family:'Arial'">Barbara Kinter</td></tr>
<tr><td width='60%'><span style="font-size:10pt;font-family:'Arial'">President</span>
<td><span style="font-size:10pt;font-family:'Arial'">Program Manager</td></tr>
</table>
<p></p>
<p><span style="font-style:italic;font-size:9pt;font-family:'Arial'">Note: There is a $25 fee to replace an American Heart Association certification card.</span>
</p>
</div>
<div id="footer">
<p class="footer _normal"></p>
</div>
</div>
</body>
<p class='breakhere'></p>
</html>