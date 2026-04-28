<div id="col-left" style="margin-bottom: 0px">
        <a href="/dashboard.php"><h2 style="font-size: 28px; padding-top: 10px">Dashboard</h2></a><br>
        <ul class="subnav">
            
<?php  if( !in_array( $session_id , $noreportsorcalendar) ) { ?> 
            <li class="subnav-title">Scheduling</li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/calendar.php">Calendar</a></li>
<?php if( isOverallAdmin() ) { ?>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/purchasedclasses.php">Purchased Classes</a></li>
            <li><a href="http://www.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/individual_registration.html">Credit Card Payments</a></li>
<?php } ?>

<?php if( isOverallAdmin() || strtolower( $session_userid ) == "cmcgee3@schools.nyc.gov" || strtolower( $session_userid ) == "dlauthe@schools.nyc.gov" ) { ?>
    <li><a href="freeregistrants.php" class="doenav">Individual Approval Requests</a></li>
<?php } ?>
<?php if( $thisusersrow["companyid"] > 0 && $thisusersrow["companyid"] != "%" && !$session_iscorp ) { ?>
<?php } // The original snippet had an empty PHP block here ?>
<?php if( $thisusersrow["canschedule"] || isOverallAdmin() ) { ?>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/schedule_class.php">Schedule Online</a></li>
<?php } ?>
<?php if( !$session_iscorp && !$thisusersrow["healthdirector"] ) { ?>
<?php if( $thisusersrow["onlyoneclasstype"] ){ ?>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/individual_registration2.php">Individual Registration</a></li>
<?php } else { ?>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/individual_registration1.php">Individual Registration</a></li>
<?php } ?>
            <li class="subnav-last"><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/fax_registration.php">Schedule by Fax</a></li>
<?php } ?>
<?php if( $session_iscorp == DFTA ) { ?>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/individual_registration2.php?region=aging">Individual Registration</a></li>
<?php } ?>
<?php } ?>

<?php if( !$specialadmin && $thisusersrow["companyid"] != "0" ) { ?>
<li class="subnav-last"><a href='monthlyaedchecklist.php' class='doenav'><b>Monthly AED Inspection Checklist</b></a></li>
<li class="subnav-last"><a target=_blank href="viewcompany.php?id=<?= $thisusersrow["companyid"] ?>" class="doenav">View My <?= getSchoolStr( "School" ) ?></a></li>
<?php } ?>
            
<?php if( isOverallAdmin() ) { ?>
            <li class="subnav-title">Class/Instructor Management</li>
<?php if( in_array( strtolower( $session_userid ), array( "sarahg@emergencyskills.com", "barbara@emergencyskills.com" ) ) ) { ?>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/covidchecklistresults.php">COVID Checklist Results</a></li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/covidchecklistresultsindividual.php">Trainee COVID Checklist Results</a></li>
            <?php } ?>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/emailtrainers.php">Email/Text Trainers</a></li>
<?php $cnt = db_query_first_cell( "Select count(*) from requesttotrain where done = 0" );
$bld = $cnt ? "style='font-weight: bold'" : "";

?>
            <li><a <?= $bld ?> href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/trainerrequests.php">Open Trainer Requests (<?= $cnt ?>)</a></li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/trainers.php">View Trainers</a></li>
            <li class="subnav"><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/managetrainerinfo.php">Information for Trainers</a></li>
<?php if( in_array( strtolower( $session_userid ), array( "sarahg@emergencyskills.com","dzamos@emergencyskills.com", "barbara@emergencyskills.com" ) ) ) { ?>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/sostrainers.php">View SOS History</a></li>
<?php } ?>
<li class="subnav"><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/manageclasses.php">Class Management</a></li>
            <li class="subnav"><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/codeblueavail.php">Code Blue Drill Availability</a></li>
            <li class="subnav-last"><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/upsclicked.php">Shipping Log</a></li>
<?php } ?>

<?php if( !in_array( $session_id, $noreportsorcalendar) && !$thisusersrow["healthdirector"] ) { ?> 
    <?php if( isOverallAdmin() ) { ?>
            <li class="subnav-title">DOE Management</li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/freeregistrants.php">Individual Approval Requests</a></li>
<?php $cnt = db_query_first_cell( "Select count(*) from user where approved = 0" );?>
<?php $bld = $cnt ? "style='font-weight: bold'" : "";?>
            <li><a <?= $bld ?> href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/approvals.php">Unapproved Accounts (<?= $cnt ?>)</a></li>
            <li class="subnav-last"><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/accessoryrequests.php">AED/Accessory Request</a></li>
<?php } else if( $thisusersrow["companyid"] == "0" && !$thisusersrow["iscorp"] && $thisusersrow["visibleregion"] == ""){ ?>
            <li class="subnav-title">DOE Management</li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/accessoryrequests.php">AED/Accessory Request</a></li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/freeregistrants.php">Individual Approval Requests</a></li>
            <li class="subnav-last"><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/reports.php">Reports</a></li>
<?php } ?>
<?php } ?>

<?php if( isOverallAdmin() ) { ?>
<li class="subnav-title">HR</li>
    <li class='subnav-last' ><a href="/humanresources.php">Human Resources</a></li>
<li class="subnav-title"><?= getSchoolStr( "Schools" ) ?> Management</li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/editcompany.php">Add New</a></li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/aeds.php">AEDs</a></li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/emailcompanies.php">Email <?= getSchoolStr( "Schools" ) ?> </a></li>
<?php if( !$session_iscorp ) { ?>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/schoolswithnotrainer.php">Schools with No Trainer</a></li>
<?php } ?>
            <li class="subnav-last"><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/campuses.php">Search/Edit <?= getSchoolStr( "Campuses" ) ?> </a></li>
<?php } else if( $thisusersrow["companyid"] == "0" ){ ?>
            <li class="subnav-title"><?= getSchoolStr( "Schools" ) ?> Management</li>
<?php if( !in_array( $session_id, $noreportsorcalendar ) && !$thisusersrow["healthdirector"] ) { ?> 
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/aeds.php">AEDs</a></li>
<?php } ?>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/schools.php">Search <?= getSchoolStr( "Schools" ) ?></a></li>
<?php if( !in_array( $session_id, $noreportsorcalendar) && !$thisusersrow["healthdirector"] ) { ?> 
            <li class="subnav-last"><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/allschools.php"><?= getSchoolStr( "Schools" ) ?> Report</a></li>
<?php } ?>
<?php } ?>
<?php if( $showaedlink ) { ?>
<?php if( !isOverallAdmin() ) { ?> 
<li class='subnav-title'>Information</li>
<?php } ?>
<img src='fr.jpg' height='60'><br>
<li>Check your own AED. <br>
<?php if( $session_iscorp ) { ?>
<a href='pdfs/monthly<?= $session_iscorp ? "_corp" : "" ?>.pdf' target=_blank><font class='body'>Print Monthly Checklist</font></a>
<?php } else { ?>
<a href='fr2.php' target=_blank><font class='body'>Print Monthly Checklist</font></a><br>
<?php } ?>
</li>
<?php if( $id ?? null ) { ?>
<li>         <a href='printaedsign.php?id=<?= $id ?>'><font class='body'>Print AED Sign</font></a></li>
<?php if( !$session_iscorp ) { ?>
<li><a href='https://<?php echo URL_WITHOUT_SUBDOMAIN; ?>/index.php/resources/'><b><font class='body'><font style='font-size:14px'>New AED Model FRx Demo</font></font></b></a></li>
<?php if( $thisusersrow["companyid"] ) { ?>
<li><a href='monthlyaedchecklist.php'><b><font class='body'><font style='font-size:14px'>Monthly AED Inspection Checklist</font></font></b></a></li>
<?php } ?>
<?php } ?>
<?php } ?>
<?php } ?>
            
<?php if( isOverallAdmin() ) { ?>
            <li class="subnav-title">ALIVE!net App</li>
<li><a href='http://<?php echo SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN; ?>/apiuploads.php'>App Uploads</a></li>
<li><a href='http://<?php echo SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN; ?>/apicalls.php'>API Calls</a><br><br></li>

<?php } ?>
<?php if( isOverallAdmin() ) { ?>
            <li class="subnav-title">ALIVE!net Administration</li>
        <li><a href='http://<?php echo SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN; ?>/uploadedpdfs/ESI%20Handbook%20-%20FINAL.pdf'>ESI Handbook</a></li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/editlists.php">Dropdown Menus</a></li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/exports.php">Exports</a></li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/recertnotesreport.php">Phone Calls <?= getSchoolStr( "School" ) ?></a></li>
<?php if( $session_userid == "sarahg@emergencyskills.com" ) { ?>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/allrecertnotes.php">All Phone Calls Report</a> (<a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/allrecertnotes.php?xls=true">xls</a>)</li>
<?php } ?>
<?php if( $specialadmin || $thisusersrow["companyid"] == "0" ) { ?>
<?php if( $thisusersrow["visibleregion"] == "" && !in_array( $session_id, $noreportsorcalendar)) {?>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/reports.php">Reports</a></li>
<?php } ?>
<?php } ?>

            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/settings.php">Settings</a></li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/uploadinvoices.php">Upload Invoices</a></li>
<?php } ?>
            <li><a href="https://emergencyskills.com/index.php/contact/">Contact Us</a></li>
            <li><a href="http://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/login.php">Log out</a></li>

            
            </ul>


    </div>