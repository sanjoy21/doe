<?php
require "mysql.php";

// Initialize variables
$crow = array();
$thanks = false;
$go = isset($_POST['go']) ? $_POST['go'] : '';
$iswithinhalfmile = isset($_POST['iswithinhalfmile']) ? $_POST['iswithinhalfmile'] : '';

// Check if user has a company
if( isset($thisusersrow["companyid"]) && $thisusersrow["companyid"] > 0 ) {
    $crow = getCompanyRow( $thisusersrow["companyid"] );
    
    // Handle session variable - using $_SESSION array instead of session_register
    if( session_id() ) {
        if( isset($crow['iscorp']) ) {
            $_SESSION['session_iscorp'] = $crow['iscorp'];
        }
    }
}

// Check for redirect
if( isset($thisusersrow["redirectURL"]) && $thisusersrow["redirectURL"] == "/dashboard.php" ) {
    Header( "Location: " . $thisusersrow["redirectURL"] );
    exit;
}

// Process form submission
if( $go && $iswithinhalfmile > -1 ) {
    if( isset($thisusersrow["companyid"]) ) {
        db_query( "update company_esi set iswithinhalfmile = '$iswithinhalfmile' where id = " . intval($thisusersrow["companyid"]) );
        if( isset($crow) ) {
            $crow["iswithinhalfmile"] = $iswithinhalfmile;
        }
        $thanks = true;
    }
}
?>

<?php
if( !isset($mobile_browser) || !$mobile_browser ) {
    include "ssi/top.php";
}
?>
<!--start center content-->
<p>
<?php 
if( function_exists('getcurrentusercompany') ) {
    $currentCompany = getcurrentusercompany();
    if( $currentCompany > 0 ) { 
?>
<a href='viewcompany.php?id=<?php echo $currentCompany; ?>'>View your <?php echo getSchoolStr( "School" ); ?></a><br><br>
<?php 
    } else { 
?>
<a href='schools.php'>View <?php echo getSchoolStr( "Schools" ); ?></a><br><br>
<?php 
    }
} 
?>

<?php
if( !isset($noreportsorcalendar) || (isset($session_id) && !in_array( $session_id, $noreportsorcalendar )) ) {
?>
<a href='schedule_class.php'>Schedule a Class at your <?php echo getSchoolStr( "school" ); ?></a>
<?php 
if( isset($_SESSION['session_iscorp']) && $_SESSION['session_iscorp'] == AGING ) { 
?><font color='red'>An individual senior center may register a maximum of 4 staff members per center. Centers are encourage to train a minimum of 3 staff members.</font><?php 
} 
?>
<br><br>
<a href='calendar.php'>View already Scheduled Classes</a><br><br>
<?php 
} 
?>

<?php 
if( isset($thisusersrow["companyid"]) && $thisusersrow["companyid"] ) { 
    $companyId = $thisusersrow["companyid"];
    $session_iscorp = isset($_SESSION['session_iscorp']) ? $_SESSION['session_iscorp'] : '';
?>
<a href="#" onClick="MyWindow=window.open('response_plan<?php echo $session_iscorp ? "_corp" : ""; ?>.php?id=<?php echo $companyId; ?>','MyWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=500'); return false;">Generate Cardiac Emergency Response Plan</a><br><br>
<a href='editpsals.php'>Manage PSAL AEDs</a><br><br>
<a href='printaedsign.php'><font size=+1>Print AED Sign</font></a><br><br>

<?php 
if( !$session_iscorp ){ 
?>
<a href='pdfs/Replacement_Procedure_for_Stolen_AED_units.pdf'>Is Your AED Missing? Click here.</a><br><br>
<div id="TICKER" style="overflow:hidden; width:520px">  
<b><font size=+1>To provide proper social distancing, you must have a DOE approved space to hold CPR/AED Training in your school.</b></font>
</div>
<?php 
} 
} 
?>

<?php 
if( function_exists('getcurrentusercompany') ) {
    $currentCompany = getcurrentusercompany();
    if( $currentCompany > 0 && (!isset($_SESSION['session_iscorp']) || !$_SESSION['session_iscorp']) ) { 
?>
<br><a href='http://www.emergencyskills.com/frx/index.html'><b><font size=+1>New AED Model FRx Demo</font></b></a><br><br>
<?php 
    }
} 
?>

<?php 
if( isset($crow["iswithinhalfmile"]) && $crow["iswithinhalfmile"] < 0 ) { 
?>
<form method='post'>
<font color='red'><b>
Is your <?php echo getSchoolStr( "school" ); ?> within a half mile walk of a subway stop?</b></font>
<select class='copy' name='iswithinhalfmile'>
    <option value='-1'></option>
    <option <?php echo (isset($crow["iswithinhalfmile"]) && $crow["iswithinhalfmile"]=="1")?"SELECTED":""; ?> value="1">Yes</option>
    <option <?php echo (!isset($crow["iswithinhalfmile"]) || !$crow["iswithinhalfmile"])?"SELECTED":""; ?> value="0">No</option>
</select>
<input type='submit' name='go' value='Save'>
</form>
<?php 
}

if( $thanks ) {
    echo( "<font color='red'>Thanks! Your response has been recorded.</font>" );
}
?>
<br><br><br><br><br><br><br><br>
<!--end center content-->

<?php 
if( !isset($mobile_browser) || !$mobile_browser ) {
    include "ssi/footer.php";
}
?>
<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="../images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>

<?php 
if( !isset($_SESSION['session_iscorp']) || !$_SESSION['session_iscorp'] ){ 
?>
<script type="text/javascript" src="webticker_lib.js" language="javascript"></script>
<?php 
} 
?>