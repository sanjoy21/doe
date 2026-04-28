<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
require_once('mysql.php');

function removeQuotes( $str )
{
$str =  str_replace( "'", "", $str );
$str = str_replace( '"', "", $str );
return $str;
}

//$date_str = "Friday, June 23, 2006";
$starttime = $s;
//echo $starttime;
$hour = substr($starttime, 0, 2);
$min = substr($starttime, 2, 4);
//echo "$hour, $min";exit;
if( !$hour )$hour = "08";
if( !$min )$min = "00";


$date_str = date("l, F j, Y", mktime($hour, $min, 0, $m, $d, $yr));
$date_str2 = date("g:i A", mktime($hour, $min, 0, $m, $d, $yr));
$startdate = date("Y-m-d H:i", mktime($hour, $min, 0, $m, $d, $yr));
//echo( $yr );
//echo( $startdate );
//exit;

// companyid is sent in as form variable from previous page
$school = getCompanyRow( $companyid );

if( !$companyid )
{
Header( "Location: schedule_class.php" );
exit;
}


if( !$school["iscorp"] && !isOverallAdmin() )
{
    $res = db_query_first_cell( "select count(*) from class where deleted = 0 and startdate = '$startdate' and companyid = $companyid" );
    if( $res )
    {
        Header( "Location: schedule_class.php" );
        exit;
    }

}

if( $companyid == 14801 ) $isconferenceroom = 1;

foreach ($school as $key => $val) {
  ${$key} = $val;
}

if( $school["retired"] || $school["deleted"] )
{
    Header( "Location: /index.php" );	
    exit;
}
// FIX: Unquoted array key $school[iscorp] changed to $school["iscorp"]
$ext = $school["iscorp"]?"":" and ( (pmsid > '' and pmsidvalidated = 1) or emptype in ( 'Charter School Employee', 'SSA', 'Custodial Staff', 'Non DOE' ) )";

$ischarter = strpos($school["schoolcode"] , "84-" ) !== false && !strpos($school["schoolcode"] , "84-" );
// FIX: Unquoted array key $school[iscorp] changed to $school["iscorp"]
if( !$school["iscorp"] && $ischarter )
    $ext = "";
if( !$school["iscorp"] )
{
    $ext .= " and responderid not in ( select responderid from responder_to_class r, class c where c.id = r.classid and startdate > now() )";
}
$sql = "select responderid, lastname, firstname, filenumber, pmsid from responders_esi where clientid = '$companyid' and deleted = 0 $ext ORDER BY lastname";
//echo( $sql );
$teachers = db_query_rows($sql);

$isups = $school["isups"];
foreach ($teachers as $teacher) {
    // FIX: Replaced interpolated unquoted array keys with explicit concatenation and quoted keys
    $teacher_options .= "<option value='" . $teacher["responderid"] . "'>" . $teacher["lastname"] . ", " . $teacher["firstname"] . " (#".getIdentifier( $teacher ).")</option>";
}

// Name of the class based on class code.
$name = $class_names[$c];

if ($newteacher) {
  $newteacher = "<div id='success'>A new teacher has been added.  You may select them from the drop down</div>";
}

$needspermit = 0;
$dow = date( "w", strtotime( $startdate ) );
$weekend = false;
if( $dow == 0 || $dow == 6 )
{
    $needspermit = 1;
    $weekend = true;
}
$h = date( "G", strtotime( $startdate ) );
$m = date( "i", strtotime( $startdate ) );
if( $h > 14 || ( $h == 14 && $m >= 30 ) )
    $needspermit = 1;

if( !$training_city && ( $borough == "Staten Island" || $borough == "Brooklyn" || $borough == "Bronx" ) )
    $training_city  = $borough;
if( !$training_city && ( $borough == "Manhattan" ) )
    		        $training_city  = "New York";

if( $borough == "Staten Island" || $borough == "Brooklyn" || $borough == "Bronx" || $borough == "Queens" || $borough == "Manhattan" )
    $training_state  = "NY";

?>
        <BASE target="_top">

<?php include "ssi/top.php"; ?>		
<form name="myform" action="do_schedule_class.php" method="post" target="_top" onSubmit="return checkSubmit( this )">
		
<strong><span class="title">SCHEDULE A CLASS</span></strong> &nbsp;&nbsp;<span class="copy"><em>(Step 2 of 2)</em></span>

		<br><hr><br>
		The class you are requesting is:<p>
		<table cellpadding="0" cellspacing="0" border="0" >
          <tr>
        	<td valign="top">
			<table cellpadding="0" cellspacing="4" border="0" width=100%>
            	<tr>
            		<td valign="top" align="right"><span class="copy"><strong>Class:</strong></span></td>					
					<td valign="top"><span class="copy"><?=$name?></span></td>
            	</tr>	
				<tr>
            		<td valign="top" align="right"><span class="copy"><strong>Date:</strong></span></td>					
					<td valign="top"><span class="copy"><?=$date_str?></span></td>
            	</tr>
				<tr>
            		<td valign="top" align="right"><span class="copy"><strong>Start Time:</strong></span></td>					
					<td valign="top"><span class="copy"><?=$date_str2?></span></td>
            	</tr>
<?php if( $school["iscorp"] ) { ?>
				<tr>
            		<td  align="right"><span class="copy"><strong>End Time:</strong></span></td>					
					<td valign="top"><span class="copy">
<select class=copy name='endhour'>
                           <option value='12'>12</option>
                           <option value='01'>01</option>
                           <option value='02'>02</option>
                           <option value='03'>03</option>
                           <option value='04'>04</option>
                           <option value='05'>05</option>
                           <option value='06'>06</option>
                           <option value='07'>07</option>
                           <option value='08'>08</option>
                           <option value='09'>09</option>
                           <option value='10'>10</option>
                           <option value='11'>11</option>
                           </select>
<select class=copy name='endminute'>
                           <option value='00'>00</option>
                           <option value='15'>15</option>
                           <option value='30'>30</option>
                           <option value='45'>45</option>
                           </select>
<select class=copy name='endampm'>
                           <option value='AM'>AM</option>
                           <option value='PM'>PM</option>
                           </select>
<input type='hidden' name='enddate' value='-1'>
                           </span></td>
            	</tr>
                           <?php  } ?>
<input type='hidden' name='numtrainers' value='<?=$numtrainers?>'>
<input type='hidden' name='maxattendees' value='<?=$maxattendees?>'>
<input type='hidden' name='approvednum' value='<?=$approvednum?>'>
           		<tr><td colspan="2"><br></td></tr>
<?php if( isOverallAdmin() ) { 

if( $school["iscorp"] )
    $islocked = 1;
?>				
			    <tr>
            		<td valign="top" align="right"><span class="copy"><strong><font color='red'>LOCK CLASS</font>:</strong></span></td>					
			    <td valign="top"><input id="islocked" type='checkbox' name='islocked' value='1' <?php echo ( !$islocked )?"onClick='if( this.checked ) alert( \"Once saved, this class will not show up on individual registration.\" );'":"" ?><?php echo $islocked?"CHECKED":""?>> Reason: <input type='text' name='lockreason' size='10' value="<?=$lockreason?>"></td>
            	</tr>
<?php } ?>
<?php if( isOverallAdmin() ) { ?>
				<tr>
            		<td valign="top" colspan='2' align="left">
			<span class="copy"><strong><font color='red'>Remote</font>:</strong></span>
					<span class="copy"><input type='checkbox' name='remote' value="1" <?=$remote?"CHECKED":""?>><br>
			<span class="copy"><strong>Is Conference Room:</strong></span>
					<span class="copy"><input type='checkbox' name='isconferenceroom' value="1" <?=$isconferenceroom?"CHECKED":""?>><br>
<?php if( $school["iscorp"] ) { ?>
<strong>Is National:</strong></span> <input type='checkbox' name='isnational' value="1" <?=$isnational?"CHECKED":""?>><br>
<?php } ?>
<strong>Is UPS:</strong></span> <input type='checkbox' name='isups' value="1" <?=$isups?"CHECKED":""?>><br>
<strong>Blended Learning?:</strong></span> <input type='checkbox' name='blendedlearning' value="1" <?=$blendedlearning?"CHECKED":""?>><br>
<input type='hidden' name='getsecards' value="1" >
<strong>Gets eBooks:</strong></span> <input type='checkbox' name='getsebooks' value="1" <?=$getsebooks?"CHECKED":""?>>

</span></td> 
            	</tr>
<?php } ?>
<?php if( isOverallAdmin() && $school["iscorp"] ){ ?>
				<tr>
            		<td valign="top" align="right"><span class="copy"><strong>Course Fee:</strong></span></td> 
					<td valign="top"><span class="copy"><input type='text' name='coursefee' size='8' value="<?=$coursefee?>"></span></td> 
            	</tr>

   <?php } ?>
            	<tr>
            		<td valign="top" align="right"><span class="copy"><strong><?=getSchoolStr( "School" )?>:</strong></span></td>					
					<td valign="top"><span class="copy"><?=$companyname?></span></td>
            	</tr>
				<tr>
            		<td valign="top" align="right"><span class="copy"><strong><?=getSchoolStr( "Location" )?>:</strong></span></td> 
					<td valign="top"><span class="copy"><span id='afaddress'><?=$address?> <?=$floor?></span>, <span id='afcity'><?=$city?></span>, <span id='afstate'><?=$state?></span> <span id='afzip'><?=$zip?></span></td>
            	</tr>
<?php if( $school["iscorp"] ){ ?>
<tr><td colspan='2'><hr>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<h3><i><u>Course</u></i> Location</h3></td></tr>
<tr><td class='copy' align='right'><b>Address</b>:</td><td class='copy'><input class='copy' name='training_location' value="<?=$training_location?>" size='40' ><br>
                             
<tr><td class='copy' align='right'><b>Training Room Number</b>:</td><td class='copy'><input class='copy' name='training_room_number' value="<?=$training_room_number?>" size='10' ><br>
<tr><td class='copy' align='right' valign='top'><b>City, State Zip:</b></td><td class='copy'> <input class='copy' name='training_city' value="<?=$training_city?>" size='15' >, <input class='copy' name='training_state' value="<?=$training_state?>" size='2' > <input class='copy' name='training_zip' value="<?=$training_zip?>" size='5' ><br>
<input type='checkbox' onClick='document.forms["myform"].training_location.value="<?=removeQuotes( $address )?>"; document.forms["myform"].training_room_number.value="<?=removeQuotes( $floor )?>"; document.forms["myform"].training_city.value="<?=removeQuotes( $city )?>"; document.forms["myform"].training_state.value="<?=removeQuotes( $state )?>";document.forms["myform"].training_zip.value="<?=removeQuotes( $zip )?>"; '> Same as headquarters
</td></tr>
<tr><td class='copy' align='right'><b><?=getSchoolStr( "School Entrance" )?></b>:</td><td class='copy'><input class='copy' name='school_entrance' size='40' ></td></tr>
<?php } ?>
<?php if( !$school["iscorp"] ) { ?>
				<tr>
            		<td valign="top" align="right"><span class="copy"><strong>Borough:</strong></span></td> 
					<td valign="top"><span class="copy"><?=$borough?></span></td>
            	</tr>	
<?php } ?>
       		<td valign="top" align="right"><span class="copy"><strong>Instructor Request:</strong></span></td> 
<td class=copy>Requests are honored on an as available basis. Accepting your class request
in no way guarantees instructor selection. Thank you.<br><br>
OPTIONAL: Please choose your instructor by clicking on the drop down arrow.<Br>
<?php
	echo "<select class=copy size=4 name='trainerreq'>";
	echo "<option value='-1'></option>";
if( $school["iscorp"] == TRAININGSITES )
	$trainers = getAllTrainers( "", 1);
else if( $school["iscorp"] )
	$trainers = getAllTrainers();
else
	$trainers = getTrainersForBorough( $borough, false, $remote );

if( $session_id == 14088 )
{
	$trainers = getAllTrainers( " and trainingsite = 'AHRC' ", 1 );
}

	foreach( $trainers as $trow )
        {
            if( availableOn( $startdate, $trow["id"] )  ||  $session_id == 14088 )
            {
	echo "<option value='".$trow["id"]."'>".$trow["first_name"] . " " . $trow["last_name"] . "</option>";
            }
        }
	echo "</select>";
?>
</td></tr>			
            </table>
			</td>
			
          </tr>
        </table>
			
		<p><BR>
<font color='red'><b>
<?php if( !$school["iscorp"] ) { ?>
***Please Note: A Code Blue Drill will be performed at your school as part
of the training program. Please accommodate the instructor appropriately.
                            <?php } ?>
		</b></font>
<br><br>		
		<strong><span class="COPY">ATTENDEES</span></strong>
		<hr>
<?php if( !$school["iscorp"] ) { ?>
		<strong>You must list the names of 12 participants to host a closed class.</strong><br>
<font color='red'>Please note: at least 7 participants are required to request a class.</font><br>
		<font color='red'><b>If fewer than 12 are listed, the remaining slots will be posted online for additional staff registration.</b></font>
<br><input type='checkbox' name='acknowledgeattendance' id='acknowledgeattendance' value='1'> <i style="background-color: yellow">Please check to acknowledge the above</a><br> 
<p><Br><br>

                            <?php } ?>
		
		<?=$newteacher?>
		
<script language='javascript'>
function classfull()
{
    return classfullinner( true );
}
function classfullinner( dopopup )
{
    var frm = document.forms["myform"].elements;
    for( i = 0; i < frm.length; i ++ )
    {
        if( frm[i].name.indexOf( "attendee" ) != -1 && frm[i].selectedIndex == 0 )
        {
	return false;
        }
    }
    if( dopopup )
        alert( "Sorry, this class is now full." );	
	return true;
}

function addMyOption( name, val )
{
    var frm = document.forms["myform"].elements;
    for( i = 0; i < frm.length; i ++ )
    {
        if( frm[i].name.indexOf( "attendee" ) != -1 && frm[i].selectedIndex == 0 )
        {
            var ele = frm[i];
//            alert( val + ", " + ele.options[1].value );
            for( j = 0; j < ele.options.length; j++ )
            {
                if( ""+ele.options[j].value == ""+val )
                {
                    ele.selectedIndex = j;
                    return;
                }
            }
            
            var o = new Option( name, val );
            frm[i].options[ frm[i].options.length ] = o;
            frm[i].selectedIndex = frm[i].options.length-1;
            break;
        }
    }
}
function checkSubmit( frm )
{
    <?php if( $school["iscorp"] == AGING ) { ?>
	if( frm.firstname.value == "" || frm.lastname.value == "" || frm.alt_firstname.value == "" || frm.alt_lastname.value == "")
    {
        alert( "Primary and alternate contact names are required." );
        return false;
    }
return true;
    <?php } else if( $school["iscorp"] ) { ?>
return true;
<?php } else { ?>
if( !jQuery( "#acknowledgeattendance" ).is( ":checked" ) )
{
	alert( "Please acknowlede the attendee policy." );
	return false; 
}


	var already = new Array();
	if( frm.firstname.value == "" || frm.lastname.value == "" || frm.alt_firstname.value == "" || frm.alt_lastname.value == "" || frm.emergency_name.value == "" || frm.principalname.value == "")
    {
        alert( "Primary, alternate, principal and emergency contact names are required." );
        return false;
    }
	if( frm.phone.value == "" || frm.alt_phone.value == "" || frm.emergency_cell.value == "" || frm.principalphone.value == "")
    {
        alert( "Primary, alternate, principal and emergency contact phone numbers are required." );
        return false;
    }
    if( frm.alt_phone.value == frm.emergency_cell.value  || frm.phone.value == frm.emergency_cell.value  || frm.principalphone.value == frm.emergency_cell.value )
    {
        alert( "Valid Emergency Cell Phone Number Required." );
        return false;
    }

    var num = 0;
    <?php if( !$specialadmin ) { 
        for( $i = 1; $i <= $maxattendees; $i++ ) { ?>
if( frm.attendee<?=$i?>.selectedIndex > 0 )
{
    var tmpval = frm.attendee<?=$i?>.options[frm.attendee<?=$i?>.selectedIndex].value;
    if( !already[tmpval] )
    {
    already[ tmpval] = tmpval;
    num++;
    }
    else if( tmpval > "" )
    {	
alert( "You have chosen the same participant in at least two slots. Please correct this." );
return false;
    }
}
<?php } ?>
//                                                     alert( num );
                                        if( num < 7 )
                                        {
                                            alert( "At least 7 unique participants are required. You have only chosen " + num + "." );
                                            return false;
                                        }
   <?php } ?>
    
	if( frm.phone.value == "")
    {
        alert( "Both primary and alternate contact phone numbers are required." );
        return false;
    }
	if( frm.alt_phone.value == "" )
    {
        alert( "Both primary and alternate contact phone numbers are required." );
        return false;
    }
	if( frm.principalname.value == "" )
    {
        alert( "Principal name is required." );
        return false;
    }
	if( frm.principalphone.value == "" )
    {
        alert( "Principal phone number is required." );
        return false;
    }
	if( frm.principalemail.value == "" )
    {
        alert( "Principal email is required." );
        return false;
    }
    if( frm.email.value == "" )
    {
        alert( "Contact email is required." );
        return false;
    }
    if( frm.alt_email.value == "" )
    {
        alert( "Alternate email is required." );
        return false;
    }
	if( frm.emergency_cell.value == "" )
    {
        alert( "Emergency cell phone number is required." );
        return false;
    }
    var num = 0;
                                if( frm.elements["parking_reserved"].selectedIndex == 0 )
                                {
                                    alert( "Please specify if there is a parking space reserved." );
                                    return false;
                                }
                                if( frm.elements["reserved_class_adequate"].selectedIndex == 0 )
                                {
                                    alert( "Please specify if a classroom has been reserved." );
                                    return false;
                                }
                                if( frm.elements["room_permit"].selectedIndex == 0 )
                                {
                                    alert( "Please specify if there is a building permit." );
                                    return false;
                                }
                                if( frm.elements["room_permit"].selectedIndex == 1 && frm.elements["room_permit_no"].value == "" )
                                {
                                    alert( "Please enter your building permit number." );
                                    return false;
                                }
<?php if( $weekend ) { ?>
if( frm.elements["custodian"].value == "" || frm.elements["custodianphone"].value == "" )
{
    alert( "Custodian name and phone is required." );
    return false;
}
<?php } ?>
<?php if( !$school["iscorp"] ) { ?>
if( frm.elements["school_entrance"].value == "" )
{
    alert( "<?=getSchoolStr( "School Entrance", $school["iscorp"] )?> is required." );
    return false;
}
<?php if( !isOverallAdmin() ) { ?>
if( frm.elements["training_room_number"].value == "" )
{
    alert( "Trainign Room Number is required." );
    return false;
}
<?php } ?>
<?php } ?>
if( frm.elements["training_location"].value == "" )
{
    alert( "<?=getSchoolStr( "Training Location", $school["iscorp"] )?> is required." );
    return false;
}
if( frm.elements["training_city"].value == "" || frm.elements["training_state"].value == "" || frm.elements["training_zip"].value == "" )
{
    alert( "<?=getSchoolStr( "Training Location", $school["iscorp"] )?> is required." );
    return false;
}
    if( frm.equipdelivinstr.value == "")
    {
        alert( "CPR Training Equipment Return Instructions are required." );
        return false;
    }
    if( frm.terms.checked == false )
    {
        alert("Please agree to our terms and conditions." );
        return false;
    }
                                
    return true;
    <?php } ?>
}
</script>
		<input type="hidden" name="startdate" value="<?=$startdate?>">
		<input type="hidden" name="code" value="<?=$c?>">
		<input type="hidden" name="companyid" value="<?=$companyid?>">
		<input type="hidden" name="addedby" value="<?=$session_id?>">
		<table cellpadding="0" cellspacing="0" border="0" width="470" >
        	<tr>
        		<td valign="top">
		<table cellpadding="0" cellspacing="4" border="0">

		<?php for ($i = 1; $i <= $maxattendees; $i++) { ?>
			<tr>
				<td valign="top"><span class="copy"><?=$i?>. <?php echo $i<8?"*":""?></span></td>
        		<td valign="top">
				<select name="attendee<?=$i?>" style="font-size: 10px;  font-family: verdana;">
				 	<option value="">Please select</option>
		    <?=$teacher_options?>
				</select>	
				</td>
<?php if( isOverallAdmin() ) { ?>
<td valign='top' >Timeslot: <input type='text' name='timeslot<?=$i?>' size='10'></td>
<?php } ?>
        	</tr>
        	<?php } ?>

        </table>
		</td>
			<td valign="top" width="150">
				<table cellpadding="0" cellspacing="7" border="0" width="150">
					<tr>
						<td valign="top" width="1" bgcolor="#000000"><img src="images/dotclear.gif" width="1" height="1"></td>
						<td valign="top"><span class="copy">If the name of your attendee is not in the dropdown lists, you can add them by clicking here:</span><p>

<a href="#" onClick="if( !classfull() ) { MyWindow=window.open('add_attendee.php?companyid=<?=$companyid?>&c=<?=$c?>&s=<?=$s?>&m=<?=$m?>&d=<?=$d?>&yr=<?=$y?>','attendee','toolbar=yes,location=yes,directories=no,status=yes,menubar=yes,scrollbars=yes,resizable=yes,width=400,height=700'); MyWindow.focus(); } return false;"><img src="images/button_addattendee.gif" border="0"></A><br><br>

        <span class=copy>        or </span>
<a href="#" class=copy onClick="if( !classfull() ) { MyWindow=window.open('add_attendee_other.php?companyid=<?=$companyid?>&c=<?=$c?>&s=<?=$s?>&m=<?=$m?>&d=<?=$d?>&yr=<?=$y?>','attendee','toolbar=yes,location=yes,directories=no,status=yes,menubar=yes,scrollbars=yes,resizable=yes,width=400,height=700'); MyWindow.focus(); } return false;">Add attendee from other  <?php echo ($session_iscorp?"location":"school" )?></A><br><br>
        </td>
					</tr>
				</table>
			</td>			
          </tr>
        </table>
		
	<p><BR>
	
		<strong><span class="COPY">ON-SITE CONTACT:</span></strong>
		
		<table cellpadding="0" cellspacing="0" border="0" width="100%">					
			<tr>
        		<td valign="top">
					<table cellpadding="0" cellspacing="6" border="0">
						<tr>
							<td>
					<span class="copy">First Name:</span><br>
<?php 
$spl = explode( " ", $contactname );
$first = array_shift( $spl );
$last = join( " ", $spl );
?>
				<input name="firstname" type="text" id="" value="<?=$first?>" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
				<td valign="top"><span class="copy">MI:</span><br>
				<input name="mi" type="text" id="" size="1" maxlength="1" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
				<td valign="top"><span class="copy">Last Name:</span><br>
				<input name="lastname" type="text" id="" value="<?=$last?>" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px">
							</td>
				<td valign="top"><span class="copy">Title:</span><br>
				<input name="contacttitle" type="text" id="" value="<?=$contacttitle?>" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px">
							</td>
                    	</tr>
                    </table>				
				</td>
           	</tr>

			<tr>	
				<td valign="top">
					<table cellpadding="0" cellspacing="6" border="0">
                    	<tr>
				<td valign="top"><span class="copy">Phone Number:</span><br>
				<input name="phone" type="text" id="" value="<?=$contactphone?>" size="12" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Ext:</span><br>
				<input name="phone_ext" type="text" id="" size="4" maxlength="6" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
				<td valign="top"><span class="copy">Fax:</span><br>
				<input name="fax" type="text" id="" size="12" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
				<td valign="top"><span class="copy">Email:</span><br>
				<input name="email" type="text" id="" size="25" value="<?=$contactemail?>" maxlength="100" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
						</tr>
        <?php if( $school["iscorp"] ) { ?>
                    	<tr>
				<td valign="top"><span class="copy">Cell Number:</span><br>
				<input name="cellphone" type="text" id="" size="12" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>

                                   <?php } ?>
					</table>
				</td>
			</tr>			
		</table>
		
		
		<p><BR>
	
		<strong><span class="COPY">ALTERNATE CONTACT:</span></strong>
		<hr>
			
		<table cellpadding="0" cellspacing="0" border="0" width="100%">					
			<tr>
        		<td valign="top">
					<table cellpadding="0" cellspacing="6" border="0">
						<tr>
							<td>
					<span class="copy">First Name:</span><br>
				<input name="alt_firstname" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
				<td valign="top"><span class="copy">MI:</span><br>
				<input name="alt_mi" type="text" id="" size="1" maxlength="1" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
				<td valign="top"><span class="copy">Last Name:</span><br>
				<input name="alt_lastname" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px">
							</td>
				<td valign="top"><span class="copy">Title:</span><br>
				<input name="altcontacttitle" type="text" id="" value="<?=$altcontacttitle?>" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px">
							</td>
                    	</tr>
                    </table>				
				</td>
           	</tr>

			<tr>	
				<td valign="top">
					<table cellpadding="0" cellspacing="6" border="0">
                    	<tr>
				
				
				<td valign="top"><span class="copy">Phone Number:</span><br>
				<input name="alt_phone" type="text" id="" size="12" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Ext:</span><br>
				<input name="alt_phone_ext" type="text" id="" size="4" maxlength="6" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
				<td valign="top"><span class="copy">Fax:</span><br>
				<input name="alt_fax" type="text" id="" size="12" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
				<td valign="top"><span class="copy">Email:</span><br>
				<input name="alt_email" type="text" id="" size="25" maxlength="100" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
						</tr>
        <?php if( $school["iscorp"] ) { ?>
                    	<tr>
				<td valign="top"><span class="copy">Cell Number:</span><br>
				<input name="altcellphone" type="text" id="" size="15" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>

                                   <?php } ?>
					</table>
				</td>
			</tr>			
		</table>
		
<?php if( !$school["iscorp"] ) { ?>
<br>		
		<strong><span class="COPY">EMERGENCY CONTACT:</span></strong>
		<hr>
		<table cellpadding="0" cellspacing="0" border="0" width="100%">					
			<tr>
        		<td valign="top">
					<table cellpadding="0" cellspacing="6" border="0">
						<tr>
							<td>
					<span class="copy">Name:</span><br>
        <input name="emergency_name" value="" type="text" id="" size="40" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                    	</tr>
                    	<tr>
				<td valign="top"><span class="copy">Cell *:</span><br>
				<input name="emergency_cell" value="" type="text" id="" size="30" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
						</tr>
					</table>
				</td>
			</tr>			
		</table>
<br>		<strong><span class="COPY">PRINCIPAL:</span></strong>
		<hr>
		<table cellpadding="0" cellspacing="0" border="0" width="100%">					
			<tr>
        		<td valign="top">
					<table cellpadding="0" cellspacing="6" border="0">
						<tr>
							<td>
					<span class="copy">Name *:</span><br>
        <input name="principalname" value="" type="text" id="" size="40" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                    	</tr>
						<tr>
							<td>
					<span class="copy">Email *:</span><br>
        <input name="principalemail" value="" type="text" id="" size="40" maxlength="100" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                    	</tr>
                    	<tr>
        <td valign="top"><span class="copy">Phone *:
				<input name="principalphone" value="" type="text" id="" size="15" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"> </td>
						</tr>
					</table>
				</td>
			</tr>			
		</table>
		
<br>		<strong><span class="COPY">CUSTODIAN:</span></strong>
		<hr>
		<table cellpadding="0" cellspacing="0" border="0" width="100%">					
			<tr>
        		<td valign="top">
					<table cellpadding="0" cellspacing="6" border="0">
						<tr>
							<td>
					<span class="copy">Name:</span><br>
        <input name="custodian" value="" type="text" id="" size="40" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                    	</tr>
                    	<tr>
        <td valign="top"><span class="copy">Phone:
				<input name="custodianphone" value="" type="text" id="" size="15" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"> Ext: <input name="custodianext" value="" type="text" id="" size="5" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></span></td>
						</tr>
					</table>
				</td>
			</tr>			
		</table>
		
<?php  } ?>	
		<p><BR>
		<strong><span class="COPY">TRANSPORTATION INFO:</span></strong>
		<hr>
		
		
		<table cellpadding="0" cellspacing="0" border="0" width="100%">					
			<tr>
        		<td valign="top">
					<table cellpadding="0" cellspacing="6" border="0">
						<tr>
							<td>
					<span class="copy">Is there parking security?</span><br>
				<textarea name="parking_security" cols=50 style="font-family: verdana; font-size: 11px; line-height: 13px"></textarea></td>
						</tr>
						<tr>
				<td valign="top"><span class="copy">Nearest Subway Line / Station:</span><br>
				<input name="nearest_subway" type="text" id="" size="60" maxlength="60" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
				
                    	</tr>
                    </table>				
				</td>
           	</tr>				
		</table>
		
		<p><BR>
		
		<strong><span class="COPY">ADDITIONAL ITEMS:</span></strong>
		<hr>
		
<?php if( !$school["iscorp"] ){ ?>
<input type='button' style='color: red' value='SAME AS SCHOOL ADDRESS' onclick='afAddress()'>
        <table>
         <tr><td class='copy' align='right'><b>Training Address</b>:</td><td class='copy'><input class='copy' name='training_location' value="<?=$training_location?>" size='40' ></td></tr>
         <tr><td class='copy' align='right'><b>Training Room Number *</b>:</td><td class='copy'><input class='copy' name='training_room_number' value="<?=$training_room_number?>" size='10' ><br>
	 <font color='red'> Reminder: Room must have a minimum of 540 square feet of usable space.</font></td></tr>
<tr><td class='copy' align='right'><b>Training City, State Zip:</b></td><td class='copy'><input class='copy' name='training_city' value="<?=$training_city?>" size='15' >, <input class='copy' name='training_state' value="<?=$training_state?>" size='2' > <input class='copy' name='training_zip' value="<?=$training_zip?>" size='5' >
</td></tr>
        </table>
        <table><tr><td class='copy'><?=getSchoolStr( "School Entrance", $school["iscorp"] )?> *: </td><td><textarea class='copy' name='school_entrance' cols='50' ></textarea></td></tr>
        </table>
<?php } ?>
		<table cellpadding="0" cellspacing="6" border="0" width="100%">					
			<tr>
        		<td valign="middle">
					<table cellpadding="0" cellspacing="2" border="0">
						<tr>
							<td valign="middle" colspan='4'><span class="copy">							 The AHA curriculum is video driven and is accessible via DVD or internet streaming. Please select the audio/visual equipment you have available for the training program. Select all that apply.</em></span></td>
					</tr>
					  <tr>
							<td valign="middle"><input name="available_streaming" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" <?=$available_streaming?"CHECKED":""?>></td>
							<td valign="middle"><span class="copy">Computer/Projector with access to Wi-Fi and permission to stream</span></td>
					</tr>
					  <tr>
							<td valign="middle"><input name="hasanydvd" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" <?=$checked_hasanydvd?"CHECKED":""?>></td>
							<td valign="middle"><span class="copy">DVD player with remote control capabilities </span></td>
					</tr>
					  <tr>
							<td valign="middle"><input <?=$checked_computer?> name="available_computer" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"  ></td>
							<td valign="middle" colspan='4'><span class="copy">Computer running Windows Media Player with DVD drive and projector/monitor</span></td>			
                                          <?php if( $company["iscorp"] ) { ?>
					</tr>
					  <tr>
							<td valign="middle"><span class="copy">Power Point Available (for ALIVE! First Aid only)</span>
							<input <?=$checked_powerpoint?> name="available_powerpoint" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" ></td>
                                                                   <?php } ?>
						</tr>
					</table><br><br>
					<table cellpadding="0" cellspacing="2" border="0" style='display:none'>
						<tr>
							<td rowspan='2' valign="middle"><span class="copy">Available Equipment <em>(check all that apply)</em></span></td>
							<td>&nbsp;&nbsp;</td>
							<td valign="middle"><input name="available_tvdvd" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
							<td valign="middle"><span class="copy">TV with DVD Player</span></td>			
							<td>&nbsp;&nbsp;</td>
							<td valign="middle"><input name="available_tvvcr" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
							<td valign="middle"><span class="copy">TV ONLY</span></td>
<?php if( $session_iscorp ) { ?>
							<td>&nbsp;&nbsp;</td>
							<td valign="middle"><input name="available_powerpoint" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
							<td valign="middle"><span class="copy">Power Point (for ALIVE! First Aid only)</span></td>			
<?php } ?>
							<td>&nbsp;&nbsp;</td>
							<td valign="middle"><input name="available_smartboard" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
							<td valign="middle"><span class="copy">Smartboard</span></td>			
<tr>
							<td>&nbsp;&nbsp;</td>
							<td valign="middle"><input name="available_computer" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
							<td valign="middle"><span class="copy">Computer (or DVD Player) with Projector</span></td>			
							<td>&nbsp;&nbsp;</td>
							<td valign="middle"><input name="noavavailable" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
							<td valign="middle"><span class="copy">None available</span></td>			
						</tr>
					</table>
				</td>
			</tr>			
			<tr>
				<td valign="middle" colspan="3"><span class="copy">Parking space reserved for the educator *:</span> <select name="parking_reserved" style="font-size: 10px;  font-family: verdana;">
                    <option value=''></option>
				 	<option value="1">Yes</option>
                    <option value="0">No</option>
					</select>								
				</td>
           	</tr>
                <?php if( !$school["iscorp"] ) { ?>
			<tr>
				<td valign="middle" colspan="3"><span class="copy">Reserved classroom of adequate size *:</span> <select name="reserved_class_adequate" style="font-size: 10px;  font-family: verdana;">
                    <option value=''></option>
				 	<option value="1">Yes</option>
                    <option value="0">No</option>
					</select>								
				</td>
           	</tr>	
			<tr>
				<td valign="middle" colspan="3"><span class="copy">Building permit complete *:</span> <select name="room_permit" style="font-size: 10px;  font-family: verdana;">
                    <option value=''></option>
				 	<option value="1">Yes</option>
<?php
                if( !$needspermit ) { 
                ?>                    <option value="0">No</option>
                <?php } ?>
					</select>
<span class='copy'>                If yes, what is the number? <input type='text' name='room_permit_no' size='7' class='copy'></span>
				</td>
           	</tr>
                                           <?php } ?>
</table>
		
		<p><BR>
		
		
		<strong><span class="COPY">NOTES:</span></strong>
		<hr>
		Please give us any notes or additional information you feel is important.<br>
		<table cellpadding="0" cellspacing="0" border="0" width="476">
          <tr>
        	<td valign="top">
			<table cellpadding="0" cellspacing="4" border="0">
            	<tr>
            		<td valign="top"><textarea name="notes" cols="70" rows="8" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"></textarea></td>
            	</tr>
            </table>
			</td>			
          </tr>
          <tr>
        	<td valign="top">
<br><br>		<strong><span class="COPY">CPR Training Equipment Return Instructions:</span></strong>
		<hr>
<span class='copy'>		ESI's courier will pick up equipment from your site after training program.  <br>Where will this equipment be stored?<br></span>
			<table cellpadding="0" cellspacing="4" border="0">
            	<tr>
            		<td valign="top"><textarea name="equipdelivinstr" cols="70" rows="3" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"></textarea></td>
            	</tr>
            </table>
			</td>			
          </tr>
<?php if( !$school["iscorp"] ) { ?>
			<tr>	
                            <td valign="top" class='copy' ><input type='checkbox' name='terms' value='1'> I agree to the ESI <a href='#' onClick='javascript:window.open( "terms.php", "_blank", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=600" )'>Terms and conditions</a>
				</td>
           	</tr>
	<?php } ?>
        </table>
		<P>	
		
		<input type="image" name='save' src="images/button_submitrequest.gif">

<?php if( $quicksched )
{
	echo( "<input type='hidden' name='quicksched' value='1'><script lanugage='javascript'>document.forms[\"myform\"].submit();</script> " );
}
?>
		</form>
			
			
		<br><br><br><br>
		
		<?php include "ssi/footer.php" ; ?>
		
		</span>
		</td>
		<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
	</tr>
</table>
<br><br>
</div>
</body>
</html>