<?php
$nologinrequired = true;
require_once('mysql.php');

$host = $_SERVER["HTTP_HOST"];
if( $host != SUB_DOE. "." .URL_WITHOUT_SUBDOMAIN ) {
    $overrideiscorp = 1;
}

if ($action == "create") {
    $_POST['redirectURL'] = "/home.php";
    $fields = array(
        "salutation",
        "first_name",
        "mi",
        "last_name",
        "companyid",
        "title",
        "department",
        "phone",
        "phone_ext",
        "fax",
        "userid",
        "password",
        "redirectURL"
    );
    
    $fields[] = "iscorp";
    $_POST['iscorp'] = $session_iscorp ? $session_iscorp : 0;
    
    if ($password != $confirm_password) {
        $err="password";
    }    
    
    if( !$companyid && $newschool ) {
        $companyid = db_query_insert_id( "insert into company_esi ( companyname, isheadquarter, isprimarycontact, canlogin, date, borough, iscorp ) values ( '$newschool', 1, 1, 1, now(), '$borough', '$session_iscorp' )" );
        $_POST['companyid'] = $companyid;
    }
    
    if (!$companyid) {
        $err = "nocompany";
    }    
    
    $us = $_POST['userid'].$emailsuffix;
    $sql = "select userid from user where userid = '$us'";
    $already_exists = db_query_first($sql);
    if (!empty($already_exists)) {
        $err = "duplicate";
    }

    if( !$err ) {
        $_POST['userid'] = $us;
        $sql = get_sql_insert("user", $fields, $_POST, "userid");
        
        session_unregister( 'session_userid' );
        session_unregister( 'session_id' );
        session_unregister( 'session_iscorp' );
        $session_userid = $userid;
        $session_id = db_query_insert_id($sql);
        
        if( !$session_iscorp && strpos( strtolower( $userid ), "schools.nyc.gov" ) === false && strpos( strtolower( $userid ), "victoryschools.com" ) === false ) {
            $ext = ", approved = 0";
            mail( "sarahg@emergencyskills.com, rebekah@emergencyskills.com", "New person to confirm: $session_userid", "https://". SUB_DOE.".".URL_WITHOUT_SUBDOMAIN."/approvals.php", "From: info@emergencyskills.com" );
        }
        
        if( $session_iscorp ) {
            mail( "sarahg@emergencyskills.com", "New person registered: $session_userid", "https://". SUB_DOE.".".URL_WITHOUT_SUBDOMAIN, "From: info@emergencyskills.com" );
        }
        
        db_query("update user set emailconfirmed = 0 $ext where id = '$session_id'" );
        $session_iscorp = $_POST["iscorp"];
        
        session_register( 'session_userid' );
        session_register( 'spession_iscorp' );
        session_register( 'session_id' );
        
        $subject = "Confirm your registration with Emergency Skills";
        $body = "
Please click the below link to confirm your email address:
https://".getUrlPrefix().".".URL_WITHOUT_SUBDOMAIN."/confirmemail.php?id=$session_id 
";
        sendHTMLMail( $userid, $subject, $body, "info@emergencyskills.com" );
        Header( "Location: create_profile_thanks.php" );
        exit;
    }
}

$host = $_SERVER["HTTP_HOST"];
if( $host == "dfta." .URL_WITHOUT_SUBDOMAIN ) {
    $overrideiscorp = AGING;
} else if( $host == "training." .URL_WITHOUT_SUBDOMAIN) {
    $overrideiscorp = TRAININGSITES;
} else if( $host != SUB_DOE. "." .URL_WITHOUT_SUBDOMAIN ) {
    $overrideiscorp = 1;
}

if ($err == "duplicate") {
    $confirm = "<div id='error'>The email address you specified already exists.  Click <a href='mailpass.php?userid=$userid'>here</a> to have your password emailed to you. </div>";
}

if ($err == "password") {
    $confirm = "<div id='error'>The password and confirmation password did not match.</div>";
}

if ($err == "nocompany") {
    $confirm = "<div id='error'>You did not select the ".getSchoolStr( "school", $overrideiscorp )." that you belong to.</div>";
}

${"selected_".$salutation} = "SELECTED";
${"selected_".$borough} = "SELECTED";
${"selected_".$companyid} = "SELECTED";
?>

<?php include "ssi/top.php"; ?>
<?php include "getschooldropdown2.php"; ?>

<strong><span class="title">CREATE YOUR PROFILE</span></strong>
<font color='red'><?=$confirm?></font>
<BR><BR><hr>
<strong>Contact Information:</strong><BR><BR>

<form name="myform" method="post" onSubmit='return checkSubmit()' >
<input type="hidden" name="action" value="create">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
        <td valign="top">
            <table cellpadding="0" cellspacing="6" border="0">
                <tr>
                    <td valign="top"><span class="copy">Salutation:</span><br>
                        <select name="salutation" style="font-size: 10px;  font-family: verdana;">
                            <option <?=$selected_Mr?> value="Mr">Mr.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
                            <option <?=$selected_Mrs?> value="Mrs">Mrs.</option>
                            <option <?=$selected_Ms?> value="Ms">Ms.</option>
                            <option <?=$selected_Miss?> value="Miss">Miss</option>
                            <option <?=$selected_Dr?> value="Dr">Dr.</option>
                        </select>
                    </td>
                    <td valign="top"><span class="copy">First Name:</span><br>
                        <input name="first_name" value="<?=$first_name?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                    <td valign="top"><span class="copy">MI:</span><br>
                        <input name="mi" value="<?=$mi?>" type="text" id="" size="1" maxlength="1" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                    <td valign="top"><span class="copy">Last Name:</span><br>
                        <input name="last_name" value="<?=$last_name?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <table cellpadding="0" cellspacing="6" border="0">
                <tr>
                    <td <?=( $overrideiscorp == AGING || $overrideiscorp == TRAININGSITES )?"style=\"display:none\"":""?> valign="top"><span class="copy">Your Borough:</span><br>
                        <select name="borough" id='borough' onChange="changeBorough();" style="font-size: 10px; font-family: verdana;">
                            <?php if( $overrideiscorp != AGING && $overrideiscorp != TRAININGSITES ) { ?>
                                <option value=""></option>
                            <?php } ?>
                            <?php if( $overrideiscorp ) { ?>
                                <option value='other'>Any</option>
                            <?php } else { ?>
                                <option <?=$selected_Bronx?> value="Bronx">The Bronx</option>
                                <option <?=$selected_Brooklyn?> value="Brooklyn">Brooklyn</option>
                                <option <?=$selected_Manhattan?> value="Manhattan">Manhattan</option>
                                <option <?=$selected_Queens?> value="Queens">Queens</option>
                                <option <?=${'selected_Staten Island'}?> value="Staten Island">Staten Island</option>
                            <?php } ?>
                        </select>
                    </td>
                    <td> <span class='copy'><?=getSchoolStr( "School", $overrideiscorp )?> Name<?=$session_iscorp==AGING?" or Number":""?>: </span><br> <input type='text' id='tmpschoolname' name='tmpschoolname' class='copy' onChange='changeBorough()'> <input type='button' value='Search' class=copy onClick='changeBorough()'>
                    <td valign="top" id='school_select'></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <table border="0" cellpadding="0" cellspacing="6">
                <tbody><tr>
                    <?php if( $session_iscorp != AGING ) { ?>
                        <td valign="top"><span class="copy">Your Title:</span><br>
                            <input name="title" value="<?=$title?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                        <td valign="top"><span class="copy">Department:</span><br>
                            <input name="department" value="<?=$department?>" id="" size="20" maxlength="30" style="font-family: verdana; font-size: 11px; line-height: 13px;" type="text"></td>
                    <?php } ?>
                    <td valign="top"><span class="copy">Phone Number:</span><br>
                        <input name="phone" type="text" value="<?=$phone?>" id="" size="15" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                    <td valign="top"><span class="copy">Ext:</span><br>
                        <input name="phone_ext" value="<?=$phone_ext?>" id="" size="4" maxlength="6" style="font-family: verdana; font-size: 11px; line-height: 13px;" type="text"></td>
                </tr></tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table border="0" cellpadding="0" cellspacing="6">
                <tbody><tr>
                    <?php if( $session_iscorp != AGING ) { ?>
                        <td valign="bottom"><span class="copy">Fax Number:</span><br>
                            <input name="fax" value="<?=$fax?>" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px;" type="text"></td>
                    <?php } ?>
                    <td valign="bottom"><span class="copy">Valid <?=getSchoolStr( "School" )?> Email Address:</span><br>
                        <input name="userid" value="<?=$userid?>" type="text" id="" size="20" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                    <td valign="bottom"><span class="copy">Re-type Email Address:</span><br>
                        <input name="email2" value="<?=$email2?>" type="text" id="" size="20" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                </tr></tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td valign="top"><hr></td>
    </tr>
    <tr>
        <td valign="top">
            <span class="copy"><strong>Log In Information:</strong><br>
            Please create a password to log in on the Emergency Skills website.</span><BR><BR>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <table cellpadding="0" cellspacing="6" border="0">
                <tr>
                    <td valign="top" align="right"><span class="copy">Password:</span></td>
                    <td><input name="password" type="password" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                </tr>
                <tr>
                    <td valign="top" align="right"><span class="copy">Re-type Password:</span></td>
                    <td><input name="confirm_password" type="password" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td valign="top"><hr></td>
    </tr>
    <tr>
        <td valign="top" class='copy' >
            <input type='checkbox' name='terms' value='1'> I agree to the ESI <a href='#' onClick='javascript:window.open( "terms.php", "_blank", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=600" )'>Terms and conditions</a><br><br>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <input type="image" src="images/button_createprofile.gif">
        </td>
    </tr>
</table>
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

<script type="text/javascript">
function isPhoneNumber(s) 
{
    return true;
}
var b = document.myform.borough.value;
if (b) {
    changeBorough();
}

function checkSubmit()
{
    if( document.myform.terms.checked == false )
    {
        alert("Please agree to our terms and conditions." );
        return false;
    }
    if( document.myform.first_name.value == "" )
    {
        alert("First name is required." );
        return false;
    }
    if( document.myform.last_name.value == "" )
    {
        alert("Last name is required." );
        return false;
    }
    if( document.myform.userid.value == "" )
    {
        alert("Email is required." );
        return false;
    }
    if( document.myform.userid.value != document.myform.email2.value )
    {
        alert("Email addresses do not match." );
        return false;
    }
    if( document.myform.phone.value == "" )
    {
        alert( "Please provide a valid phone number." );
        return false;
    }
    if( document.myform.password.value == "" )
    {
        alert( "Please provide a password." );
        return false;
    }
    if( document.myform.confirm_password.value == "" )
    {
        alert( "Please provide a confirmation password." );
        return false;
    }
    if( document.myform.confirm_password.value != document.myform.password.value )
    {
        alert( "Your passwords do not match." );
        return false;
    }
    <?php if( $overrideiscorp != AGING && $overrideiscorp != TRAININGSITES ) { ?>
    if( document.myform.borough.selectedIndex <= 0  )
    {
        alert( "Please provide your borough." );
        return false;
    }
    <?php } ?>
}
</script>

</body>
</html>