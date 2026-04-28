<?php

$nologinrequired = true;
require "mysql.php";
$nologinrequired = true; // Added later by Sanjoy Dey

$db_link = $GLOBALS['link']; // Get database link for escaping

// --- 1. Handle Registration Type and Region Setting (Deprecated session replacement) ---
if( $_GET["regtype"] )
{
    // Set cookie
    setcookie( "regtype", $_GET["regtype"], 0, "/" );

    // Set session_iscorp and register it in $_SESSION
    $_SESSION['session_iscorp'] = 1;
    $session_iscorp = 1; // Update local variable
}
if( $region == "Aging" )
{
    // Set session_iscorp to AGING (4)
    $_SESSION['session_iscorp'] = AGING;
    $session_iscorp = AGING; // Update local variable
}

// --- 2. Build SQL Query for Matching Classes ---
$t = date( "Y-m-d H:i" ); 
$b = "";

if( !$companyid )
{
    // Filtering by borough, region, and classname
    if ($borough) {
        $borough_safe = mysqli_real_escape_string($db_link, $borough);
        $b .= " AND borough = '{$borough_safe}'";
    }

    if( $session_iscorp && $session_id )
    {
        $b .= mysqli_real_escape_string($db_link, $visi);
    }

    if( $region )
    {
        $region_safe = mysqli_real_escape_string($db_link, $region);
        $b .= " AND company_esi.region = '{$region_safe}'";
    }

    if( $classname )
    {
        $classname_safe = mysqli_real_escape_string($db_link, $classname);
        $b .= " AND code = '{$classname_safe}'";
    }

    $session_iscorp_safe = $session_iscorp;

    // Base SQL for finding classes in a general search (Step 2)
    $sql = "SELECT class.* FROM class, company_esi 
            WHERE iscorp = '{$session_iscorp_safe}' 
              AND startdate > '{$t}' 
              AND company_esi.id = companyid 
              {$b} 
              AND accepted = 1 
              AND company_esi.deleted = 0 
              AND class.deleted = 0 
              AND class.islocked = 0 
            ORDER BY startdate";
}
else
{
    // Filtering when a specific company is selected
    $companyid_safe = $companyid;
    
    // The original script had this block commented out or incorrectly placed in the companyid block:
    // if( !$session_iscorp && !$region ) { $b .= " and isconferenceroom = 1"; } 

    $sql = "SELECT class.* FROM class, company_esi 
            WHERE companyid = '{$companyid_safe}' 
              AND startdate > '{$t}' 
              AND company_esi.id = companyid 
              AND accepted = 1 
              AND company_esi.deleted = 0 
              AND class.deleted = 0 
              AND class.islocked = 0 
              {$b} 
            ORDER BY startdate";
            
    $crow = getCompanyRow( $companyid_safe );
    
    // Redirect logic if corp status does not match
    if( $crow['iscorp'] != $session_iscorp )
    { 
        $prefix = getUrlPrefix( $crow['iscorp']);
        header( "Location: https://{$prefix}." . URL_WITHOUT_SUBDOMAIN . "/individual_registration2.php?companyid={$companyid_safe}" );
        exit;
    }
}

// --- 3. Execute Query and Fetch Matches ---
$matching = db_query_rows($sql);

?>

<?php include "ssi/top.php"; ?>

<strong><span class="title">CLASS REGISTRATION</span></strong> &nbsp; &nbsp; &nbsp;<span class="copy"><em>(Step 2 of 3)</em></span>
<?php if ( isOverallAdmin()) { // Assumed function ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='index.php'>Back to admin main</a><?php }?>
<br><hr>
<?php if( $toomany ) { ?> <font color=red>Sorry, the class you selected has filled up. Please select another.</font><br><br><?php } ?>
<?php if( $region == "FRB2" ) { ?>

<br>
<b><font color='green'>This year, FRB will be following the American Heart Association recommendation and encouraging staff to receive training every two years. This year, therefore, we will certify staff whose last names begin with the letters A-L. Please register for both a CPR/AED class and a First Aid class.</font></b><br>
<br>
<?php }?>

The following <strong><?php echo htmlspecialchars($class_names_display[$classname]); ?></strong> classes are scheduled in <strong><?php echo htmlspecialchars($borough); ?></strong>:<p>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3">
<tr bgcolor="#e1e1f6">
<td valign="top"><span class="copy"><strong>Date &amp; Time</strong></span></td>
<td valign="top"><span class="copy"><strong>Location</strong></span></td>
<?php if( $session_iscorp ) { ?>
<td valign="top"><span class="copy"><strong>Class Type</strong></span></td>
<?php } ?>
<td valign="top"><br></td>
 </tr>
<?php
$any = 0;
foreach( $matching as $m ) {
$class_id = (int)$m["id"];
$company_id = (int)$m["companyid"];

    // Assumed function call
    $attendees = get_attendees( $class_id ); 
    $max_attendees = (int)($m["maxattendees"]);

if( count( $attendees ) >= $max_attendees )
{
continue;
}
 $any = 1;

    // Assumed function call
    $crow = getCompanyRow( $company_id );

    $numleft = $max_attendees - count( $attendees ) ;

    $start_timestamp = strtotime( $m["startdate"] );
    $start_date_display = date( "l, M. d, Y", $start_timestamp );
    $start_time_display = date( "h:ia", $start_timestamp );
    $end_time_display = htmlspecialchars($m["enddate"]);
?>
<tr bgcolor="#ffffff">
<td valign="top"><span class="copy"><?php echo $start_date_display; ?><br><?php echo $start_time_display; ?> - <?php echo $end_time_display; ?> <br>
<font color='red'><?php echo $numleft; ?> <?php echo $numleft>1?"slots":"slot"; ?> open</font></span></td>
<td valign="top"><span class="copy"><?php echo htmlspecialchars($crow["companyname"]); ?><br>
<?php 
if( $m["training_location"]) { 
    // Assumed function call
    echo nl2br( getTrainingAddress( $m ) );
} else {
?>

<?php echo htmlspecialchars($crow["address"]); ?><?php echo ($crow["floor"]) ? ", Floor " . htmlspecialchars($crow["floor"]) : ""; ?><br><?php echo htmlspecialchars($crow["city"]); ?>

<?php } ?>
</span></td>
<?php if( $session_iscorp ) { ?>
<td valign="top"><span class="copy"><?php echo htmlspecialchars($allclass_names[$session_iscorp][$m['code']]); ?></td>
<?php } ?>
<td valign="middle" align="center"><a href='individual_registration3.php?borough=<?php echo htmlspecialchars($borough); ?>&classid=<?php echo $class_id; ?>'><img border=0 src="images/button_select.gif" alt="Select"></a></td>
</tr>
<?php } ?>

</table>

<?php if( $session_iscorp == AGING ) { ?>
<br><br>Please ensure that staff from your organization attend scheduled AED/CPR classes. If for any reason staff cannot attend a training they are scheduled for, they must cancel 5 business days prior to the training date. If a staff member fails to attend a scheduled training session or does not notify Aging and our contracted training vendor Emergency Skills Inc. (ESI) within the allotted timeframe, the provider will be charged for the staff member’s seat. We recommend providers have a backup staff member to avoid paying for a no-show. To cancel, email <a href='mailto:emergencynotification@aging.nyc.gov, dfta@emergencyskills.com'>emergencynotification@aging.nyc.gov, aging@emergencyskills.com</a>, and copy your Program Officer.

<br><br>
An individual program is encouraged to train a minimum of 3 staff members.


<?php if( !$any ) { ?>
<font color='red'><br>
If you don’t see any classes scheduled, please contact Lenny James to become a program host.
<br>
Lenny James
<br>
Director, Strategic Operations & Administration<br>

Office of Emergency Preparedness & Response<br>

NYC Department for the Aging<br>

<a href='tel:+12126024439'>(212) 602-4439</a><br>
<a href='mailto:ljames@aging.nyc.gov'>ljames@aging.nyc.gov</a>
</font>
<?php } ?>
<?php } ?>


<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<?php include "ssi/footer.php" ; ?>

</span>
</td>
<td valign="top" width="15"><img src="../images/dotclear.gif" width="10" alt=""></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>