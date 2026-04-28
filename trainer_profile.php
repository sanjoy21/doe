<?php 
$nologinrequired = true;
require_once('mysql.php');

if( $tid && !isOverallAdmin() && $tid != $session_id )
{
    Header( "Location: login.php" );
        exit;
}

//(reverse-i-search)`create': create table instructornotes ( id integer primary key auto_increment, trainerid integer, description text, dateadded datetime, addedby integer );

if( $delin )
{
 db_query( "delete from instructornotes where id = $delin" );
}
if( $createin )
{
 db_query( "insert into instructornotes ( trainerid, dateadded, description, addedby ) values ( $tid, now(), '$createinnote', '$session_id' )" );
}

if( !$tid && $currentusertype == "trainer" )
    $tid = $session_id;

if( $update_x || $update || $updateandreturn )
{
    if( $tid )
    {
        $pass = db_query_first_cell( "select password from user where id = '$tid'" );
        if( $oldpass && $pass != $oldpass )
        {
            $error = "Sorry, your password did not match your old password.";
            $row = array();
            foreach( $_POST as $p=>$v )
                {
                    $row[$p] = $v;
                }
        }
        else
        {
            $fpr = ", fingerprinted = '$fingerprinted'";
            $fpr .= ", corporate = '$corporate'";
            $fpr .= ", 2020update = '$u2020update'";
            $fpr .= ", hascorponapp = '$hascorponapp'";
            $fpr .= ", tcfaculty = '$tcfaculty'";
            $fpr .= ", isstaff = '$isstaff'";
            $fpr .= ", isfieldrep = '$isfieldrep'";
            $fpr .= ", ashi = '$ashi'";
            $fpr .= ", paused = '$paused'";
            $fpr .= ", bls = '$bls'";
            $fpr .= ", hascar = '$hascar'";
            $fpr .= ", assignedtcfacultyid = '$assignedtcfacultyid'";
            $fpr .= ", instructorstage = '$instructorstage'";
            $fpr .= ", extraschools = '". $extraschools."' ";
            $fpr .= ", trainingsites = '$trainingsites'";
            $fpr .= ", trainingsite = '$trainingsite'";
     if( $birthday )
  $birthdate = "'" . date( "Y-m-d", strtotime( $birthday )). "'";
     else
  $birthdate = "null";
            $fpr .= ", birthday = $birthdate";
            $fpr .= ", monitoringquarter = '$monitoringquarter'";
            $fpr .= ", lastrenewaldate = '$lastrenewaldate'";
            $fpr .= ", firstaid = '$firstaid'";
            $fpr .= ", alivefa = '$alivefa'";
            $fpr .= ", cellprovider = '$cellprovider'";
              $fpr .= ", ucp = '$ucp'";
     
            $fpr .= ", newui = '1'";
//     echo( $fpr );exit;
            db_query( "update user set userid = '$login', inactive = '$inactive', ashiid = '$ashiid', first_name = '$first_name', last_name = '$last_name', preferredpronouns = '$preferredpronouns', viewschools = '$viewschools', national = '$national', address1 = '$address1', address2 = '$address2', city = '$city', state = '$state', zip = '$zip', phone = '$phone', phone_ext = '$phone_ext', cell = '$cell', otherphone = '$otherphone' $fpr, otherphoneext = '$otherphoneext', salutation = '$salutation', ahaid = '$ahaid', mi = '$mi' where id = '$tid' " );

          $visiblezips = str_replace( " ", "", $visiblezips );
            $vis = explode( ",", $visiblezips );
     sort( $vis );
//     print_r( $vis );
//     exit;
            db_query( "delete from user_to_zip where userid = $tid" );
            foreach( $vis as $e )
            {
                $e = trim( $e );
                if( $e )
                    db_query( "insert into user_to_zip values( $tid, $e )" );
            }

            db_query( "delete from uservalues where userid = $tid" );
            if (is_array($extras)) {
                foreach( $extras as $e=>$val )
                {
                    db_query( "insert into uservalues ( userid, name, value ) values ( $tid, '$e', '$val' ) " );
                }
            }
            
            if( $oldpass && $password1 )
                db_query( "update user set password = '$password1' where id = '$tid'" );
            
            if( isOverallAdmin() )
            {
                db_query( "update user set notes = '$notes' where id = '$tid'" );
            }
            $groups = array( "aha", "coreinst", "cpr", "other" );
            foreach( $groups as $g )
                {
                    $mostr = getCurrentTrainerExpRow( $g, $tid );
                    // Variable variables: $ahaexp, $ahatype, $ahasite etc.
                    // Need to ensure these exist in scope or $_POST
                    $sitekey_var = $g."site";
                    $sitekey = $_POST[$sitekey_var];
                    
                    $type_var = $g."type";
                    $type = $_POST[$type_var];
                    
                    $expdate_var = $g."exp";
                    $expdate = $_POST[$expdate_var];

                    if( $expdate )
                        $expdate = date( "Y-m-d", strtotime( $expdate ));
                    if( $g == "coreinst" )
                        $type = "coreinst";
                    
                    // Ensure $mostr isn't null before array access
                    $mostr_site = $mostr['site'];
                    $mostr_type = $mostr['type'];
                    $mostr_exp = $mostr['expdate'];

                    if( ($expdate && $type && $expdate != "NULL" ) && ( $mostr_site != $sitekey ||  $mostr_type != $type ||  $mostr_exp != $expdate ) )
                    {
                        if( !$expdate  )
                            $expdate = "NULL";
                        else
                            $expdate = "'$expdate'";
                        db_query( "update trainer_exp_dates set current = 0 where trainerid = '$tid' and expgroup = '$g'" );
                        db_query( "insert into trainer_exp_dates ( trainerid, expgroup, type, site, expdate, current ) values ( '$tid', '$g', '".$type."', '".$sitekey."', ".$expdate.", 1 )" );
                    }
                }
            $mostr = getCurrentTrainerExp( "tc", "type", $tid );
            if( $mostr != $tctype )
            {
                db_query( "update trainer_exp_dates set current = 0 where trainerid = '$tid' and expgroup = 'tc'" );
                db_query( "insert into trainer_exp_dates ( trainerid, expgroup, type, current ) values ( '$tid', 'tc', '$tctype', 1 )" );
            }
        }
    }
    else
    {
        $already = db_query_first_cell( "select userid from user where userid = '$login'" );
        if( $already )
        {
            $error = "Sorry, there is already a user with this email address.";
            $row = array();
            foreach( $_POST as $p=>$v )
                {
                    $row[$p] = $v;
                }
        }
        else
        {
            // session_register is deprecated. Use $_SESSION directly.
            // session_register( 'session_id' ); 
            $session_id = db_query_insert_id( "insert into user ( signupdate, redirectURL, password, first_name, last_name, preferredpronouns, address1, address2, city, state, zip, phone, phone_ext, cell, userid,  otherphone, otherphoneext, salutation, mi, usertype ) values ( now(), '/tcalendar.php', '$password1', '$first_name', '$last_name', '$preferredpronouns', '$address1', '$address2', '$city', '$state', '$zip', '$phone', '$phone_ext', '$cell', '$login', '$otherphone', '$otherphoneext', '$salutation', '$mi', 'trainer' ) " );
            $_SESSION['session_id'] = $session_id;
            
            $tid = $session_id;
            if( isOverallAdmin() )
            {
                $fpr = ", fingerprinted = '$fingerprinted'";
                $fpr .= ", corporate = '$corporate'";
                $fpr .= ", 2020update = '$u2020update'";
                $fpr .= ", extraschools = '". $extraschools."' ";
                $fpr .= ", assignedtcfacultyid = '$assignedtcfacultyid'";
                $fpr .= ", tcfaculty = '$tcfaculty'";
                $fpr .= ", hascorponapp = '$hascorponapp'";
                $fpr .= ", hascar = '$hascar'";
                $fpr .= ", monitoringquarter = '$monitoringquarter'";
     if( $birthday )
  $birthdate = "'" . date( "Y-m-d", strtotime( $birthday )). "'";
     else
  $birthdate = "null";
  $fpr .= ", birthday = $birthdate";
  $fpr .= ", trainingsites = '$trainingsites'";
  $fpr .= ", lastrenewaldate = '$lastrenewaldate'";
  $fpr .= ", trainingsite = '$trainingsite'";
  $fpr .= ", ahaid = '$ahaid'";
  $fpr .= ", ashiid = '$ashiid'";
  $fpr .= ", bls = '$bls'";
                $fpr .= ", firstaid = '$firstaid'";
                $fpr .= ", alivefa = '$alivefa'";
                $fpr .= ", cellprovider = '$cellprovider'";
                $fpr .= ", ucp = '$ucp'";
                
                $fpr .= ", newui = '1'";
                
                db_query( "update user set notes = '$notes' $fpr  where id = '$tid'" );
            }
            $session_userid = $login;
        }
    }
    //    print_r( $boroughs );exit;
    db_query( "delete from trainer_to_borough where trainerid = '$tid'" );
    if (is_array($boroughs)) {
        foreach( $boroughs as $b )
            {
                db_query( "insert into trainer_to_borough ( trainerid , borough ) values ( '$tid', '$b' )" );
            }
    }
    $boroughs = db_query_array( "select borough from  trainer_to_borough where trainerid = '$tid'", "borough", "borough" );
//    $boroughs = db_query_array( "select borough from  trainer_to_borough where trainerid = '$tid'", "borough", "borough" );
      if( $updateandreturn )
{
        Header( "Location: trainer_view.php?tid=$tid" );
        exit;
}
    if( !$error )
    {
        Header( "Location: trainer_profile".($isold?"":"_create_thanks").".php?error=".urlencode("Saved.")."&tid=$tid" );
        exit;
    }
}
else
{
    if( $tid )
    {
        $row = db_query_first( "select * from user where id = '$tid'" );
        if( $row["usertype"] != "trainer" )
        {
            Header( "Location: {$row['redirectURL']}?tiaaa" );
        exit;
        }
        $boroughs = db_query_array( "select borough from  trainer_to_borough where trainerid = '$tid'", "borough", "borough" );
    }
    
}
//echo( $tid );
if( $tid )
{
        $row = db_query_first( "select * from user where id = '$tid'" );
        if( $row["usertype"] != "trainer" )
        {
            Header( "Location: {$row['redirectURL']}?tiaaa" );
        exit;
        }
}

// Ensure row exists to prevent display errors
if (!isset($row) || !is_array($row)) {
    $row = array(
        'userid' => '', 'password' => '', 'first_name' => '', 'last_name' => '', 
        'salutation' => '', 'mi' => '', 'preferredpronouns' => '', 'address1' => '', 
        'address2' => '', 'ahaid' => '', 'ashiid' => '', 'city' => '', 'state' => '', 
        'zip' => '', 'phone' => '', 'birthday' => '', 'phone_ext' => '', 'cell' => '', 
        'cellprovider' => '', 'otherphone' => '', 'otherphoneext' => '', 'login' => '', 
        'instructorstage' => '', 'monitoringquarter' => '', 'lastrenewaldate' => '', 
        'trainingsite' => '', 'paused' => 0, 'viewschools' => 0, 'national' => 0, 
        'ashi' => 0, 'bls' => 0, 'fingerprinted' => 0, 'firstaid' => 0, 'alivefa' => 0, 
        'ucp' => 0, 'corporate' => 0, '2020update' => 0, 'tcfaculty' => 0, 
        'hascorponapp' => 0, 'hascar' => 0, 'isstaff' => 0, 'isfieldrep' => 0, 
        'assignedtcfacultyid' => 0, 'trainingsites' => 0, 'extraschools' => '', 
        'notes' => '', 'inactive' => 0
    );
}

if( $sendtext ) 
{
 $error = "Sent.";
 sendText( "ALIVE!net Reminder!", "test provider", $row );
}

?>
<?php 
if( $tid )
$specialtnav = 1;
include "ssi/top.php"; ?>  
<script language='javascript'>
function checkSubmit()
            {
                if( document.forms["myform"].login && document.forms["myform"].login.value == "" )
                {
                    alert( "Email address is required." );
                    return false;
                }
                if( document.forms["myform"].password1.value == "" && document.forms["myform"].password2.value != "" )
                {
                    alert( "Password is required." );
                    return false;
                }
                if( document.forms["myform"].oldpass.value != '' && document.forms["myform"].password1.value != document.forms["myform"].password2.value  )
                {
                    alert( "Passwords do not match." );
                    return false;
                }

                return true;
                
            }
</script>
<form method='post' onSubmit="return checkSubmit()"  name="myform">
<input type='hidden' name='tid' value='<?=$tid?>'>

<strong><span class="title"><?=$tid?"EDIT":"CREATE"?> YOUR PROFILE</span></strong> <?php if( $tid ) { ?><a href='trainer_view.php?tid=<?=$tid?>'>View</a>
<?php if( isOverallAdmin() ) { ?>
    <a href='login.php?dologin=1&userid=<?=$row['userid']?>&password=<?=$row['password']?>&Submit=true'>Log In As Trainer</a><br>
    <?php } ?>

<?php } ?>
  <BR><font color='red'><?=$error?></font>
<BR><hr>
  <strong>Contact Information:</strong><BR><BR>
  
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
   <tr> 
    <td valign="top">
                  <?php if( !$tid ) { ?>
     <input type='image' name='update' src="images/button_createprofile.gif">
<?php } else { ?>
     <input type='image' name='update' src="images/button_savechanges.gif">
<?php } ?>
            <?php if( $specialadmin ) { ?>
<span class='copy'><a href='trainer_availability.php?theid=<?=$tid?>'>Edit Availability</a></span>
<?php } ?>
    </td>
            </tr>
         <tr> 
    <td valign="top">
     <table cellpadding="0" cellspacing="6" border="0">
                     <tr>
                      <td valign="top"><span class="copy">Salutation:</span><br>
       <select name="salutation" style="font-size: 10px;  font-family: verdana;">
          <option <?=$row["salutation"]=="Mr."?"SELECTED":""?> value="Mr.">Mr.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
                            <option <?=$row["salutation"]=="Mrs."?"SELECTED":""?> value="Mrs.">Mrs.</option>
       <option <?=$row["salutation"]=="Ms."?"SELECTED":""?> value="Ms.">Ms.</option>
       <option <?=$row["salutation"]=="Miss"?"SELECTED":""?> value="Miss">Miss</option>
       <option <?=$row["salutation"]=="Dr."?"SELECTED":""?> value="Dr.">Dr.</option>
       </select>
     </td>
          <td valign="top"><span class="copy">First Name:</span><br>
    <input name="first_name"  value="<?=$row["first_name"]?>" type="text" id="" size="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
    <td valign="top"><span class="copy">MI:</span><br>
    <input name="mi"  value="<?=$row["mi"]?>" type="text" id="" size="1" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
    <td valign="top"><span class="copy">Last Name:</span><br>
    <input name="last_name"  value="<?=$row["last_name"]?>" type="text" id="" size="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
    <td valign="top"><span class="copy">Preferred Pronouns:</span><br>
    <input name="preferredpronouns"  value="<?=$row["preferredpronouns"]?>" type="text" id="" size="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                     </tr>
                    </table>
    </td>
            </tr>
   <tr> 
    <td valign="top">
      <table cellpadding="0" cellspacing="6" border="0"><tr>
  <td valign="middle"><span class="copy">Street Address 1:<br><input name="address1"  value="<?=$row["address1"]?>" type="text" id="" size="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></span></td>
  <td valign="middle"><span class="copy">Street Address 2:<br><input name="address2"  value="<?=$row["address2"]?>" type="text" id="" size="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></span></td>
  <td valign="middle"><span class="copy">AHA ID #:<br><input name="ahaid"  value="<?=$row["ahaid"]?>" type="text" id="" size="15" style="font-family: verdana; font-size: 11px; line-height: 13px"></span></td>
  <td valign="middle"><span class="copy">ASHI ID #:<br><input name="ashiid"  value="<?=$row["ashiid"]?>" type="text" id="" size="15" style="font-family: verdana; font-size: 11px; line-height: 13px"></span></td>
 </tr>

 <tr>
  <td valign="top" colspan="2">
   <table cellpadding="0" cellspacing="0" border="0">
               <tr>
                   <td valign="middle"><span class="copy">City:<br><input name="city"  value="<?=$row["city"]?>" type="text" id="" size="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></span></td>
     <td>&nbsp;</td>
     <td valign="middle"><span class="copy">State:<br><input name="state"  value="<?=$row["state"]?>" type="text" id="" size="4" maxlength="2" style="font-family: verdana; font-size: 11px; line-height: 13px"></span></td>
     <td>&nbsp;</td>
     <td valign="middle"><span class="copy">Zip:<br><input name="zip"  value="<?=$row["zip"]?>" type="text" id="" size="10" maxlength="10" style="font-family: verdana; font-size: 11px; line-height: 13px"></span></td>
              </tr>
            </table>
  </td>
 </tr>
    </td>
 </table>
   </tr>
   
   <tr> 
    <td valign="top">
     <table cellpadding="0" cellspacing="6" border="0">
                     <tr>
    <td valign="top"><span class="copy">Phone Number:</span><br>
    <input name="phone"  value="<?=$row["phone"]?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px">
<br><span class="copy">Birthday: (YYYY-MM-DD)</span><br>
    <input name="birthday"  value="<?=$row["birthday"]?>" type="text" id="" size="12" maxlength="10" style="font-family: verdana; font-size: 11px; line-height: 13px">
</td>
<td valign="top"><span class="copy">Ext:</span><br>
    <input name="phone_ext"  value="<?=$row["phone_ext"]?>" type="text" id="" size="4" maxlength="6" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
      <td width="6"><img src="images/dotclear.gif" height="10" width="6"></td>
      <td width="1" bgcolor="#999999"><img src="images/dotclear.gif" height="10" width="1"></td>
      <td width="6"><img src="images/dotclear.gif" height="10" width="6"></td>
      
      <td valign="top"><span class="copy">Cell Number:</span><br>
    <input name="cell"  value="<?=$row["cell"]?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
      <!-- <td valign="top"><span class="copy">Cell Provider:</span><br>
    <select name="cellprovider"><option value=''>Please Choose</option> -->
<?php


// Alltel
// [10-digit phone number]@message.alltel.com Example: 2125551212@message.alltel.com

// AT&T (formerly Cingular)
// [10-digit-number]@mms.att.net Example: 2125551212@mms.att.net

// Boost Mobile
// [10-digit phone number]@myboostmobile.com Example: 2125551212@myboostmobile.com

// Cricket Communications
// [10-digit phone number]@mms.mycricket.com Example: 1234567890@mms.mycricket.com

// Metro PCS
// [10-digit telephone number]@mymetropcs.com Example: 5552228888@mymetropcs.com

// Nextel (now part of Sprint Nextel)
// [10-digit telephone number]@messaging.nextel.com Example: 7035551234@messaging.nextel.com

// Sprint (now Sprint Nextel)
// [10-digit phone number]@messaging.sprintpcs.com Example: 2125551234@messaging.sprintpcs.com

// T-Mobile
// [10-digit phone number]@tmomail.net Example: 4251234567@tmomail.net

// Verizon
// [10-digit phone number]@vtext.com Example: 5552223333@vtext.com

// Virgin Mobile USA
// [10-digit phone number]@vmobl.com Example: 5551234567@vmobl.com 

// $providers = array( "T-Mobile", "Sprint", "Verizon", "AT&T", "Nextel","Alltel","Google Fi", "Boost Mobile", "Simple Mobile", "Cricket Communications", "Metro PCS", "Virgin Mobile USA" );

// foreach( $providers as $p ) { 
// echo( "<option value='$p' " . ($row["cellprovider"]==$p?"SELECTED":"") . ">$p</option>" );
// }
?>
<!-- </select> <i>Please email <a href='mailto:sarahg@emergencyskills.com'>sarahg@emergencyskills.com</a> if your provider is not listed.</i> -->
<?php
// if( $session_userid == "sarahg@emergencyskills.com" ) { 
    ?>
<!-- <input type='submit' name='sendtext' value='Send Test Text' onClick='return confirm( "Are you sure you want to send a test text?" )'> -->
<?php 
// } 
?>
<!-- </td> -->

      </tr>
     </table>
    </td>
   </tr>
   
   <tr> 
    <td valign="top">
     <table cellpadding="0" cellspacing="6" border="0">
<tr>
    <td valign="top">
<table><tr><td>
    <span class="copy">Other Phone <em>(if any)</em>:</span></td><td><span class="copy">Ext:</span></td></tr>
<tr><td>    <input name="otherphone"  value="<?=$row["otherphone"]?>" type="text" id="" size="20" style="font-family: verdana; font-size: 11px; line-height: 13px"> </td><td>
    <input name="otherphoneext"  value="<?=$row["otherphoneext"]?>" type="text" id="" size="4" maxlength="6" style="font-family: verdana; font-size: 11px; line-height: 13px"></td></tr></table>
    </td>
      
      <td valign="top"><span class="copy">Email Address:</span><br>
                  <?php if( $tid ) { ?>
<?php if( !$specialadmin ) { ?>
<span class='copy'><?=$row["userid"] ?></span>
<input type='hidden' name='login' value='<?=$row["userid"] ?>'>

<?php } else { ?>
<input type='text' class='copy' size='30' name='login' value='<?=$row["userid"] ?>'>
<A href='mailto:<?=$row["userid"] ?>'>Email</a>
<?php } ?>
<?php } else { ?>
<input name="login"  value="<?=$row["login"]?>" type="text" id="" size="20" style="font-family: verdana; font-size: 11px; line-height: 13px">
<?php } ?>
</td>
</tr>
<?php
$zips = getVisibleZips( $row['id'] ); 
$zips = str_replace( ",1", ", 1", $zips );
$zips = str_replace( ",2", ", 2", $zips );
if( $specialadmin ) {
?>
<tr><td colspan=6><b>Instructor Comments</b><br>
New Note: <input type='text' size='40' name='createinnote'> <input type='submit' name='createin' value='Create'><br>
<?php 
$r = db_query_rows("select * from instructornotes where trainerid = '$tid' order by dateadded desc" );
foreach ( $r as $ir )
{
 echo( getFormattedDateWTime( $ir["dateadded"] ) . ": " . $ir["description"] . "" );
 if( in_array( strtolower( $session_userid ), array( "sarahg@emergencyskills.com", "barbara@emergencyskills.com" ) ) )
 echo( "&nbsp;&nbsp;&nbsp;<a href='trainer_profile.php?tid=$tid&delin={$ir['id']}'>Del?</a>" );
echo( "<br>" );
}
?>
</td></tr>
<tr><td class='copy'>
<b>Instruction Stage: <select name='instructorstage'>
<?php foreach( array( "1"=>"Stage 1", 2=>"Stage 2", 3=>"Stage 3", 4=>"Stage 4", "Completed"=>"Completed" ) as $stage=>$displ  )
{
 $sel = $stage == $row["instructorstage"]?"SELECTED":"";
echo( "<option $sel value='$stage'>$displ</option>" );
}
?>
</select>
</b></td></tr>
<tr><td class='copy'>
<b>Monitoring Quarter:</b> <select name='monitoringquarter'>
<option value=''>Please Choose</option>
<?php foreach( array( "1"=>"1", 2=>"2", 3=>"3", 4=>"4" ) as $stage=>$displ  )
{
 $sel = $stage == $row["monitoringquarter"]?"SELECTED":"";
echo( "<option $sel value='$stage'>$displ</option>" );
}
?>
</select>
</td></tr>
<tr><td class='copy' colspan='4'><b>Core Instructor</b><br>
Last Renewal: <input type='text' name='lastrenewaldate' value='<?=$row['lastrenewaldate']?>'><br>
Training Site: <select name='trainingsite'>
    <option value=''>Please Choose</option>
    <?php 
$training_rows = db_query_array("select value from esioptionvalues where datatype='training site' order by value", "value", "value");

foreach( array( "AHRC", "Catholic Charities", "Concourse", "CP of NYS", "ESI", "Gotham", "Guardian Life", "Hilton", "Independent", "Isabella", "Securitas", "Smithsonian" ) as $s )
 {
    $sel = $s == $row['trainingsite']?"SELECTED":"";
    echo( "<option value='$s' $sel>$s</option>" );
} 
foreach( $training_rows as $s )
{
    $sel = $s == $row['trainingsite']?"SELECTED":"";
    echo( "<option value='$s' $sel>$s</option>" );
}
?>
</select>
</td></tr>
<tr><td class='copy' colspan='4'>
<b>Privileges</b><br>
     <input type='checkbox' name='paused' value='1' <?=$row["paused"]?"CHECKED":""?>> <font color='red'>Paused?</font><br>
     <input type='checkbox' name='viewschools' value='1' <?=$row["viewschools"]?"CHECKED":""?>> View <?=getSchoolStr( "Schools" )?><br>
     <input type='checkbox' name='national' value='1' <?=$row["national"]?"CHECKED":""?>> National<br>
     <input type='checkbox' name='ashi' value='1' <?=$row["ashi"]?"CHECKED":""?>> ASHI?<br>
     <input type='checkbox' name='bls' value='1' <?=$row["bls"]?"CHECKED":""?>> BLS?<br>

     <input type='checkbox' name='fingerprinted' value='1' <?=$row["fingerprinted"]?"CHECKED":""?>> Fingerprinted?<br>
     <input type='checkbox' name='firstaid' value='1' <?=$row["firstaid"]?"CHECKED":""?>> First Aid?<br>
     <input type='checkbox' name='alivefa' value='1' <?=$row["alivefa"]?"CHECKED":""?>> Alive! FA?<br>
     
     <input type='checkbox' name='corporate' value='1' <?=$row["corporate"]?"CHECKED":""?>> Corporate?<br>
    <input type='checkbox' name='u2020update' value='1' <?=$row["2020update"]?"CHECKED":""?>> 2020 Update?<br>
     <input type='checkbox' name='tcfaculty' value='1' <?=$row["tcfaculty"]?"CHECKED":""?>> TC Faculty?<br>
     <input type='checkbox' name='hascorponapp' value='1' <?=$row["hascorponapp"]?"CHECKED":""?>> <b>Has Corp On App?</b><br>
     <input type='checkbox' name='hascar' value='1' <?=$row["hascar"]?"CHECKED":""?>> Has Car?<br>
     <input type='checkbox' name='isstaff' value='1' <?=$row["isstaff"]?"CHECKED":""?>> Is Staff?<br>
     <input type='checkbox' name='isfieldrep' value='1' <?=$row["isfieldrep"]?"CHECKED":""?>> Is Field Rep?<br>
    Assigned TC Faculty: <select name='assignedtcfacultyid'>
<option value=''></option>
    <?php foreach( db_query_rows( "select * from user where tcfaculty = 1 and inactive = 0" ) as $at ) { ?>
    <option value='<?=$at['id']?>' <?=$row["assignedtcfacultyid"]==$at['id']?"SELECTED":""?>> <?=$at['first_name']?> <?=$at['last_name']?></option>
    <?php } ?>
    </select><br>
     <input type='checkbox' name='trainingsites' value='1' <?=$row["trainingsites"]?"CHECKED":""?>> Training Sites?<br>
</td></tr>
    <tr><td class='copy'>Additional Schools (for app only, comma separated):</td><td><input type='text'  class='copy' name='extraschools' value="<?=$row["extraschools"]?>"></td></tr>
<?php } else { ?>
<input type='hidden' name='viewschools' value='<?=$row["viewschools"]?>'>
<input type='hidden' name='national' value='<?=$row["national"]?>'>
<input type='hidden' name='fingerprinted' value='<?=$row["fingerprinted"]?>'>
<input type='hidden' name='firstaid' value='<?=$row["firstaid"]?>'>
<input type='hidden' name='alivefa' value='<?=$row["alivefa"]?>'>
<input type='hidden' name='ucp' value='<?=$row["ucp"]?>'>
<input type='hidden' name='corporate' value='<?=$row["corporate"]?>'>
<input type='hidden' name='bls' value='<?=$row["bls"]?>'>
<input type='hidden' name='isstaff' value='<?=$row["isstaff"]?>'>
<input type='hidden' name='isfieldrep' value='<?=$row["isfieldrep"]?>'>
<input type='hidden' name='ashi' value='<?=$row["ashi"]?>'>
<input type='hidden' name='u2020update' value='<?=$row["2020update"]?>'>
<input type='hidden' name='tcfaculty' value='<?=$row["tcfaculty"]?>'>
<input type='hidden' name='hascar' value='<?=$row["hascar"]?>'>
<input type='hidden' name='assignedtcfacultyid' value='<?=$row["assignedtcfacultyid"]?>'>
<input type='hidden' name='trainingsites' value='<?=$row["trainingsites"]?>'>
<input type='hidden' name='trainingsite' value='<?=$row["trainingsite"]?>'>
<input type='hidden' name='lastrenewaldate' value='<?=$row["lastrenewaldate"]?>'>
<input type='hidden' name='hascorponapp' value='<?=$row["hascorponapp"]?>'>

<?php } ?>
<?php if( $specialadmin || $session_id == 2297 ) {
?>
<tr>
     <td class='copy'> Visible Zips: (comma separated)</td><td>
    <textarea name="visiblezips" cols=60 ><?=$zips?></textarea>
     </td>
</tr>
    <?php } else { ?>
<input type='hidden' name='visiblezips' value='<?=$zips?>'>
    <?php } ?>

     </table>
    </td>
   </tr>
<?php if( isOverallAdmin() ) { ?>   
<tR><td><table><tr><td valign='top'>Notes:</td><td> <textarea rows='5' name='notes' cols='40' ><?=$row['notes']?></textarea></td></tr>
</table>
</td></tr>
<?php } ?>
   <tr> 
    <td valign="top">
     <hr>  
    </td>
            </tr>
   
   <tr> 
    <td valign="top">
     <table cellpadding="0" cellspacing="6" border="0">
                     <tr>
       <td valign="top" colspan="2"><span class="copy">Select the boroughs in which you able to teach <em>(check all that apply)</em>:</span><br>

        <table cellpadding="5" cellspacing="0" border="0">
                                 <tr>
                                  <td valign="middle"><span class="copy">
          <input name="boroughs[]" value='Bronx' <?php echo !empty($boroughs["Bronx"])?"CHECKED":""; ?> type="checkbox" id="" style="font-family: verdana; font-size: 11px; line-height: 13px">The Bronx<br>
<input name="boroughs[]" type='checkbox' value='Brooklyn' <?php echo !empty($boroughs["Brooklyn"])?"CHECKED":""; ?>  style="font-family: verdana; font-size: 11px; line-height: 13px">Brooklyn<br>
<input name="boroughs[]" type='checkbox' value='Manhattan' <?php echo !empty($boroughs["Manhattan"])?"CHECKED":""; ?> style="font-family: verdana; font-size: 11px; line-height: 13px">Manhattan<br></span></td>
         <td>&nbsp;&nbsp;&nbsp;</td>
         <td valign="top"><span class="copy">
<input name="boroughs[]" type='checkbox' value='Queens' <?php echo !empty($boroughs["Queens"])?"CHECKED":""; ?> style="font-family: verdana; font-size: 11px; line-height: 13px">Queens<br>
<input name="boroughs[]" type='checkbox' value='Staten Island' <?php echo !empty($boroughs["Staten Island"])?"CHECKED":""; ?> style="font-family: verdana; font-size: 11px; line-height: 13px">Staten Island<br>
<input name="boroughs[]" type='checkbox' value='New Jersey' <?php echo !empty($boroughs["New Jersey"])?"CHECKED":""; ?> style="font-family: verdana; font-size: 11px; line-height: 13px">New Jersey<br>
<input name="boroughs[]" type='checkbox' value='Remote' <?php echo !empty($boroughs["Remote"])?"CHECKED":""; ?> style="font-family: verdana; font-size: 11px; line-height: 13px">Remote<br>
</span></td>
</tr>
</table>

</td>
</tr>
</table>
    </td>
            </tr>
   <tr> 
    <td valign="top">
     <hr>  
    </td>
            </tr>
<?php if( $tid )
{?>
<?php if( $specialadmin ) { ?>
<tr><td class='copy'><a href='expdatehistory.php?trainerid=<?=$tid?>'>View History</a>
<tr><td class='copy'><b>AHA CPR Instructor</b><br>
Expiration Date: <input type='text' name='ahaexp' value='<?=getCurrentTrainerExp( "aha", "expdate", $tid )?>' size='12'><br>
Type: <input type='text' name='ahatype' value='<?=getCurrentTrainerExp( "aha", "type", $tid )?>'><br>
Site:  <input type='text' name='ahasite' value='<?=getCurrentTrainerExp( "aha", "site", $tid )?>' size='30'><br>
</td></tr>

<tr><td class='copy'><b>CPR Provider</b><br>
Expiration Date: <input type='text' name='cprexp' value='<?=getCurrentTrainerExp( "cpr", "expdate", $tid )?>' size='12'><br>
Type: <input type='text' name='cprtype' value='<?=getCurrentTrainerExp( "cpr", "type", $tid )?>'><br>
Site:  <input type='text' name='cprsite' value='<?=getCurrentTrainerExp( "cpr", "site", $tid )?>' size='30'><br>
</td></tr>

<tr><td class='copy'><b>Initial Certification</b><br>
Date: <input type='text' name='coreinstexp' value='<?=getCurrentTrainerExp( "coreinst", "expdate", $tid )?>' size='12'><br>
</td></tr>

<tr><td class='copy'><b>TC Affiliation</b><br>
<input type='text' name='tctype' value='<?=getCurrentTrainerExp( "tc", "type", $tid )?>' size='30'><br>
</td></tr>

<tr><td class='copy'><b>Other Credentials</b><br>
Expiration Date: <input type='text' name='otherexp' value='<?=getCurrentTrainerExp( "other", "expdate", $tid )?>' size='12'><br>
Type: <input type='text' name='othertype' value='<?=getCurrentTrainerExp( "other", "type", $tid )?>'><br>
Site:  <input type='text' name='othersite' value='<?=getCurrentTrainerExp( "other", "site", $tid )?>' size='30'><br>
</td></tr>

   <tr> 
    <td valign="top">
     <hr>  
    </td>
            </tr>
                         <?php } ?>
   <tr> 
    <td valign="top">
     <span class="copy"><strong>Emergency Contact Info:</strong><br><br>    
    </td>
            </tr>
   <tr> 
    <td valign="top">
     <table cellpadding="0" cellspacing="6" border="0">
<?php 
$mine = db_query_array( "select * from uservalues where userid = $tid", "name", "value" );
$totry = array();
$totry["Primary contact"] = "Primary contact";
$totry["Primary contact Relationship"] = "Relationship";
$totry["Primary contact Address"] = "Address";
$totry["Primary contact Work"] = "Work";
$totry["Primary contact Home"] = "Home";
$totry["Primary contact Cellular"] = "Cellular";
$totry["Secondary contact"] = "Secondary contact";
$totry["Secondary contact Relationship"] = "Relationship";
$totry["Secondary contact Address"] = "Address";
$totry["Secondary contact Work"] = "Work";
$totry["Secondary contact Home"] = "Home";
$totry["secCellular"] = "Cellular";
$totry["additional"] = "ADDITIONAL INFORMATION THAT MAY BE HELPFUL IN THE EVENT OF AN EMERGENCY";

foreach( $totry as $key=>$displ )
{  
    // Fixed array index warning
    $val = $mine[$key];
    if( $key == "additional" )
        echo( "<tr><td colspan='2'>$displ</td></tr><tr><td colspan=2><textarea cols='40' name='extras[$key]'>$val</textarea></td></tr>" );
else
echo( "<tr><td>$displ</td><td><input type='text' name='extras[$key]' value=\"$val\"></td></tr>" );

}
?>
</table>
</td></tr>
   <tr>
    <td valign="top">
     <span class="copy"><strong>Change Password:</strong><br><br>    
    </td>
            </tr>
            
            <!--New Password Generator by Sanjoy Dey-->
            
            <tr>
     <td><!-- Add this to your form -->
<div style="margin: 10px 0;">
    <label for="pass">Password:</label>
    <div style="display: flex;">
        <input type="text" 
               id="pass" 
               // name="pass"
               placeholder="Click generate button">
        <button type="button" 
                onclick="document.getElementById('pass').value = generatePassword()">
            Generate
        </button>
    </div>
</div>

<script>
function generatePassword() {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let pass= '';
    for (let i = 0; i < 12; i++) {
        pass += chars[Math.floor(Math.random() * chars.length)];
    }
    return pass;
}
</script></td></tr>

     <!--New Password Generator code ends here-->
   
   <tr>
    <td valign="top">
     <table cellpadding="0" cellspacing="6" border="0">
                     <tr>
    <td valign="top" align="right"><span class="copy">Old Password:</span></td>
<?php if( $session_id != $tid || isOverallAdmin() ) { ?>
                            <td><input name="oldpass" type="text" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                               <?php } else { ?>
                            <td><input name="oldpass" type="password" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                                           <?php } ?>
      </tr>
      <tr>
    <td valign="top" align="right"><span class="copy">New Password:</span></td>
    <td><input name="password1" type="password" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
      </tr>
      <tr>
    <td valign="top" align="right"><span class="copy">Re-type New Password:</span></td>
    <td><input name="password2" type="password" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
      </tr>
      <tr>
    <td align="right"><span class="copy">Inactive?:</span></td>
    <td><input name="inactive" type="checkbox" id="" value='1' <?=$row["inactive"]?"CHECKED":""?>></td>
      </tr>
                    </table>
    </td>
            </tr>
   <input type='hidden' name='isold' value='1'>
<?php } else { ?>
<input name="oldpass" type="hidden" value='1'>
<tr>
    <td valign="top">
     <span class="copy"><strong>Log In Information:</strong>
     <br>
     Please set a password to log in on the Emergency Skills website.</span><BR><BR>
    </td>
            </tr>
   
   <tr>
    <td valign="top">
     <table cellpadding="0" cellspacing="6" border="0">
<tr>
    <td valign="top" align="right"><span class="copy">Password:</span></td>
    <td><input name="password1" type="password" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
      </tr>
      <tr>
    <td valign="top" align="right"><span class="copy">Re-type Password:</span></td>
    <td><input name="password2" type="password" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
      </tr>
</table>
    </td>
            </tr>
   <?php } ?>   
   <tr> 
    <td valign="top">
<hr>  
</td>
</tr>

<tr>
<td valign="top">
<?php if( !$tid ) { ?>
<input type='image' name='update' src="images/button_createprofile.gif">
<?php } else { ?>
<input type='image' name='update' src="images/button_savechanges.gif">
<input type='submit' name='updateandreturn' value='Save And Return'>
<?php } ?>
<?php if( $specialadmin ) { ?>
<span class='copy'><a href='trainer_availability.php?theid=<?=$tid?>'>Edit Availability</a></span>
<?php } ?>
</td>
</tr>
</table>

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