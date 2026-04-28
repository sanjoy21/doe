<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

include "mysql.php";

// Declare variables that might be used before being defined
// $id = $_GET['id'] ?? $_POST['id'] ?? null;
// $row = [];
$removephoto = $_GET['removephoto'] ?? $_POST['removephoto'] ?? null;
$merge = $_POST['merge'] ?? null;
$otherschool = $_POST['otherschool'] ?? null;
$retired = $_POST['retired'] ?? null;
$markascorp = $_GET['markascorp'] ?? $_POST['markascorp'] ?? null;
$unretire = $_POST['unretire'] ?? null;
$retire = $_POST['retire'] ?? null;
$update = $_POST['update'] ?? null;
$SaveStay = $_POST['SaveStay'] ?? null;
$delete = $_POST['delete'] ?? null;
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? null;
// $session_iscorp = $_SESSION['iscorp'] ?? null;
// $session_userid = $_SESSION['userid'] ?? null;
$readonly = $_GET['readonly'] ?? $_POST['readonly'] ?? null;

// Variables from form submission
$filingexpirationdate = $_POST['filingexpirationdate'] ?? '';
$programexpirationdate = $_POST['programexpirationdate'] ?? '';
$nextservicecalldate = $_POST['nextservicecalldate'] ?? '';
$medicalinvoicedate = $_POST['medicalinvoicedate'] ?? '';
$schoolcode1 = $_POST['schoolcode1'] ?? '';
$schoolcode2 = $_POST['schoolcode2'] ?? '';
$schoolcode3 = $_POST['schoolcode3'] ?? '';
$schoolcode = $_POST['schoolcode'] ?? '';
$companyname = $_POST['companyname'] ?? '';
$notes = $_POST['notes'] ?? '';
$campusid = $_POST['campusid'] ?? '';
$isups = $_POST['isups'] ?? '';
$sendmonthlyaedchecklist = $_POST['sendmonthlyaedchecklist'] ?? '';
$municipalna = $_POST['municipalna'] ?? '';
$programna = $_POST['programna'] ?? '';
$mdina = $_POST['mdina'] ?? '';
$directorname = $_POST['directorname'] ?? '';
$mdaddress = $_POST['mdaddress'] ?? '';
$typeofentity = $_POST['typeofentity'] ?? '';
$nycentity = $_POST['nycentity'] ?? '';
$ambulance = $_POST['ambulance'] ?? '';
$county = $_POST['county'] ?? '';
$mdfax = $_POST['mdfax'] ?? '';
$mdphone = $_POST['mdphone'] ?? '';
$bic = $_POST['bic'] ?? '';
$locationcode = $_POST['locationcode'] ?? '';
$principalname = $_POST['principalname'] ?? '';
$mdresignedname = $_POST['mdresignedname'] ?? '';
$mdresigneddate = $_POST['mdresigneddate'] ?? '';
$mdresigned = $_POST['mdresigned'] ?? '';
$principalemail = $_POST['principalemail'] ?? '';
$customernumber = $_POST['customernumber'] ?? '';
$schoolno = $_POST['schoolno'] ?? '';
$schoolphone = $_POST['schoolphone'] ?? '';
$cfn = $_POST['cfn'] ?? '';
$emailtype = $_POST['emailtype'] ?? '';
$displayname = $_POST['displayname'] ?? '';
$accountmanager = $_POST['accountmanager'] ?? '';
$buildingno = $_POST['buildingno'] ?? '';
$officeid = $_POST['officeid'] ?? '';
$address = $_POST['address'] ?? '';
$floor = $_POST['floor'] ?? '';
$city = $_POST['city'] ?? '';
$state = $_POST['state'] ?? '';
$zip = $_POST['zip'] ?? '';
$isheadquarter = $_POST['isheadquarter'] ?? '';
$psalprincipalname = $_POST['psalprincipalname'] ?? '';
$psalprincipalphone = $_POST['psalprincipalphone'] ?? '';
$psalprincipalemail = $_POST['psalprincipalemail'] ?? '';
$contactname = $_POST['contactname'] ?? '';
$contacttitle = $_POST['contacttitle'] ?? '';
$contact2title = $_POST['contact2title'] ?? '';
$contact3title = $_POST['contact3title'] ?? '';
$contactphone = $_POST['contactphone'] ?? '';
$contactemail = $_POST['contactemail'] ?? '';
$contactcell = $_POST['contactcell'] ?? '';
$contact2name = $_POST['contact2name'] ?? '';
$contact2phone = $_POST['contact2phone'] ?? '';
$contact2email = $_POST['contact2email'] ?? '';
$contact2cell = $_POST['contact2cell'] ?? '';
$contact3name = $_POST['contact3name'] ?? '';
$contact3phone = $_POST['contact3phone'] ?? '';
$contact3email = $_POST['contact3email'] ?? '';
$contact3cell = $_POST['contact3cell'] ?? '';
$isprimarycontact = $_POST['isprimarycontact'] ?? '';
$canlogin = $_POST['canlogin'] ?? '';
$companynotes = $_POST['companynotes'] ?? '';
$clientrequests = $_POST['clientrequests'] ?? '';
$parkinginfo = $_POST['parkinginfo'] ?? '';
$contactphoneExtension = $_POST['contactphoneExtension'] ?? '';
$contact2phoneExtension = $_POST['contact2phoneExtension'] ?? '';
$contact3phoneExtension = $_POST['contact3phoneExtension'] ?? '';
$related_company = $_POST['related_company'] ?? '';
$isactive = $_POST['isactive'] ?? '';
$isala = $_POST['isala'] ?? '';
$iscoolingcenter = $_POST['iscoolingcenter'] ?? '';
$region = $_POST['region'] ?? '';
$borough = $_POST['borough'] ?? '';
$iswithinhalfmile = $_POST['iswithinhalfmile'] ?? '';
$showsondrillreports = $_POST['showsondrillreports'] ?? '';
$excludereporting = $_POST['excludereporting'] ?? '';
$inspectionfrequency = $_POST['inspectionfrequency'] ?? '';
$excludetraining = $_POST['excludetraining'] ?? '';
$shownotrained = $_POST['shownotrained'] ?? '';
$pendingcoo = $_POST['pendingcoo'] ?? '';
$showlasttraining = $_POST['showlasttraining'] ?? '';
$showcardsexp = $_POST['showcardsexp'] ?? '';
$inclusion = $_POST['inclusion'] ?? '';
$donotinclude = $_POST['donotinclude'] ?? '';
$summer = $_POST['summer'] ?? '';
$ssp = $_POST['ssp'] ?? '';
$nopmsidrequired = $_POST['nopmsidrequired'] ?? '';
$esinotes = $_POST['esinotes'] ?? '';
$deleted = $_POST['deleted'] ?? '';

if( isset($_FILES["schoolphoto"]["tmp_name"]) && $_FILES["schoolphoto"]["tmp_name"] ) 
{
move_uploaded_file( $_FILES["schoolphoto"]["tmp_name"], "schoolimages/{$id}.jpg" );
}

if( $removephoto ) 
{
shell_exec( "rm schoolimages/{$id}.jpg" );
Header( "Location: editcompany.php?id=$id&photoremoved=1" );    
exit;
}

if( $merge && $otherschool )
{
// Note: mysql_escape_string is deprecated, should use mysqli_real_escape_string
db_query( "update responders_esi set clientid = $otherschool where clientid = $id" );
db_query( "update aed_esi set clientid = $otherschool where clientid = $id" );
db_query( "update user set companyid = $otherschool where companyid = $id" );

if( !$retired )
    {
        db_query( "update class set companyid = $otherschool where companyid = $id" );
        db_query( "update drill set companyid = $otherschool where companyid = $id" );
        db_query( "update servicecall set companyid = $otherschool where companyid = $id" );
    }
    $comname = db_query_first_cell( "select companyname from company_esi where id = '$id'" );
    $newname = $comname . " (R)";
    // Note: mysql_escape_string is deprecated
    db_query( "update company_esi set deleted = 1, retired = '$retired', companyname = '" . mysql_escape_string( $newname ) . "', mergedinto = '$otherschool' where id = $id " );
Header( "Location: viewcompany.php?id=$otherschool" );    
exit;
}

if( $markascorp )
{
db_query( "update company_esi set iscorp = 1 where id = $id" );
Header( "Location: /editcompany.php?id=$id" );
    exit;
}

if( $unretire )
{
    db_query( "update company_esi set deleted = 0, retired = '0' where id = $id " );
}

if( $retire )
{
    $comname = db_query_first_cell( "select companyname from company_esi where id = '$id'" );
    $newname = $comname . " (R)";
    // Note: mysql_escape_string is deprecated
    db_query( "update company_esi set deleted = 1, retired = '1', retiredate = now(), deletiondate = now(), companyname = '" . mysql_escape_string( $newname ) . "' where id = $id " );
}

if( $update )
{
    $mycorp = $session_iscorp;
    if( $id )
    {
        $row = db_query_first( "select * from company_esi where id = $id" );
        $mycorp = $row["iscorp"] ?? $session_iscorp;
    }
    
    if($mycorp && ($row["region"] ?? '') != "Aging" ) {
        $schoolcode = $companyname;
    } else if( ($row["region"] ?? '') == "Aging" ) {
        // it's just school code
    } else {
        $schoolcode = $schoolcode1 . "-" . $schoolcode2 . "-" . $schoolcode3;
    }
    
    if( $id )
    {
        $currschoolcode = db_query_first_cell( "select schoolcode from company_esi where id = $id" );
        if( $schoolcode && $currschoolcode && $currschoolcode != stripslashes( $schoolcode ) )
        {
            // Note: mysql_escape_string is deprecated
            db_query( "insert into oldschoolcodes ( companyid, schoolcode, movedate, whomoved ) values ( '$id', '" . mysql_escape_string( $currschoolcode ) . "', now(), '$session_userid' )" );
        }
        $currnotes = db_query_first_cell( "select esinotes from company_esi where id = $id" );
        if( $notes && $currnotes && $currnotes != stripslashes( $notes ))
        {
            // Note: mysql_escape_string is deprecated
            db_query( "insert into oldesinotes ( companyid, notes, movedate, whomoved ) values ( '$id', '" . mysql_escape_string( $currnotes ) . "', now(), '$session_userid' )" );
        }
    }
    
    // Fix dates
    $filingexpirationdate = fixdate( $filingexpirationdate );
    $programexpirationdate = fixdate( $programexpirationdate );
    $nextservicecalldate = fixdate( $nextservicecalldate );
    $medicalinvoicedate = fixdate( $medicalinvoicedate );

    if( $id )
    {
        if( !$officeid )
            $officeid = $id;
            
        // Note: All variables should be escaped properly before using in SQL
        $update_sql = "update company_esi set 
            campusid = '" . addslashes($campusid) . "', 
            isups = '" . addslashes($isups) . "', 
            sendmonthlyaedchecklist = '" . addslashes($sendmonthlyaedchecklist) . "', 
            municipalna = '" . addslashes($municipalna) . "', 
            programna = '" . addslashes($programna) . "', 
            mdina = '" . addslashes($mdina) . "', 
            directorname = '" . addslashes($directorname) . "', 
            mdaddress = '" . addslashes($mdaddress) . "', 
            typeofentity = '" . addslashes($typeofentity) . "', 
            nycentity = '" . addslashes($nycentity) . "', 
            ambulance = '" . addslashes($ambulance) . "', 
            county = '" . addslashes($county) . "', 
            mdfax = '" . addslashes($mdfax) . "', 
            mdphone = '" . addslashes($mdphone) . "', 
            bic = '" . addslashes($bic) . "', 
            filingexpirationdate = '" . addslashes($filingexpirationdate) . "', 
            programexpirationdate = '" . addslashes($programexpirationdate) . "', 
            medicalinvoicedate = '" . addslashes($medicalinvoicedate) . "', 
            locationcode = '" . addslashes($locationcode) . "', 
            principalname = '" . addslashes($principalname) . "', 
            mdresignedname = '" . addslashes($mdresignedname) . "', 
            mdresigneddate = '" . addslashes($mdresigneddate) . "', 
            mdresigned = '" . addslashes($mdresigned) . "', 
            principalemail = '" . addslashes($principalemail) . "', 
            customernumber = '" . addslashes($customernumber) . "', 
            schoolcode = '" . addslashes($schoolcode) . "', 
            schoolphone = '" . addslashes($schoolphone) . "', 
            cfn = '" . addslashes($cfn) . "', 
            companyname = '" . addslashes($companyname) . "', 
            emailtype = '" . addslashes($emailtype) . "', 
            displayname = '" . addslashes($displayname) . "', 
            accountmanager = '" . addslashes($accountmanager) . "', 
            buildingno = '" . addslashes($buildingno) . "', 
            officeid = '" . addslashes($officeid) . "', 
            address = '" . addslashes($address) . "', 
            floor = '" . addslashes($floor) . "', 
            city = '" . addslashes($city) . "', 
            state = '" . addslashes($state) . "', 
            zip = '" . addslashes($zip) . "', 
            isheadquarter = '" . addslashes($isheadquarter) . "', 
            psalprincipalname = '" . addslashes($psalprincipalname) . "', 
            psalprincipalphone = '" . addslashes($psalprincipalphone) . "', 
            psalprincipalemail = '" . addslashes($psalprincipalemail) . "', 
            contactname = '" . addslashes($contactname) . "', 
            contacttitle = '" . addslashes($contacttitle) . "', 
            contact2title = '" . addslashes($contact2title) . "', 
            contact3title = '" . addslashes($contact3title) . "', 
            contactphone = '" . addslashes($contactphone) . "', 
            contactemail = '" . addslashes($contactemail) . "', 
            contactcell = '" . addslashes($contactcell) . "', 
            contact2name = '" . addslashes($contact2name) . "', 
            contact2phone = '" . addslashes($contact2phone) . "', 
            contact2email = '" . addslashes($contact2email) . "', 
            contact2cell = '" . addslashes($contact2cell) . "', 
            contact3name = '" . addslashes($contact3name) . "', 
            contact3phone = '" . addslashes($contact3phone) . "', 
            contact3email = '" . addslashes($contact3email) . "', 
            contact3cell = '" . addslashes($contact3cell) . "', 
            isprimarycontact = '" . addslashes($isprimarycontact) . "', 
            canlogin = '" . addslashes($canlogin) . "', 
            companynotes = '" . addslashes($companynotes) . "', 
            clientrequests = '" . addslashes($clientrequests) . "', 
            parkinginfo = '" . addslashes($parkinginfo) . "', 
            contactphoneExtension = '" . addslashes($contactphoneExtension) . "', 
            contact2phoneExtension = '" . addslashes($contact2phoneExtension) . "', 
            contact3phoneExtension = '" . addslashes($contact3phoneExtension) . "', 
            related_company = '" . addslashes($related_company) . "', 
            isactive = '" . addslashes($isactive) . "', 
            isala = '" . addslashes($isala) . "', 
            iscoolingcenter = '" . addslashes($iscoolingcenter) . "', 
            region = '" . addslashes($region) . "', 
            borough = '" . addslashes($borough) . "', 
            iswithinhalfmile = '" . addslashes($iswithinhalfmile) . "', 
            showsondrillreports = '" . addslashes($showsondrillreports) . "', 
            excludereporting = '" . addslashes($excludereporting) . "', 
            inspectionfrequency = '" . addslashes($inspectionfrequency) . "', 
            excludetraining = '" . addslashes($excludetraining) . "', 
            shownotrained = '" . addslashes($shownotrained) . "', 
            pendingcoo = '" . addslashes($pendingcoo) . "', 
            showlasttraining = '" . addslashes($showlasttraining) . "', 
            showcardsexp = '" . addslashes($showcardsexp) . "', 
            inclusion = '" . addslashes($inclusion) . "', 
            donotinclude = '" . addslashes($donotinclude) . "', 
            summer = '" . addslashes($summer) . "', 
            ssp = '" . addslashes($ssp) . "', 
            nopmsidrequired = '" . addslashes($nopmsidrequired) . "', 
            esinotes = '" . addslashes($esinotes) . "' 
            where id = $id ";
        
        db_query( $update_sql );

        include "updatebuildings.php";
        db_query( "insert into companychanges ( sessionid, dateadded, companyid ) values ( '$session_userid', now(), '$id' )" );
        
        if( !$SaveStay )
        {
            Header( "location: $redirect " );
            exit;
        }
    }
    else
    {
        // Note: All variables should be escaped properly before using in SQL
        $insert_sql = "insert into company_esi ( 
            iscorp, campusid, isups, sendmonthlyaedchecklist, principalname, principalemail, 
            mdresigned, mdresignedname, mdresigneddate, customernumber, schoolcode, schoolphone, 
            cfn, companyname, emailtype, schoolno, buildingno, displayname, accountmanager, 
            officeid, address, floor, city, state, zip, isheadquarter, contactname, contacttitle, 
            contact2title, contact3title, contactphone, contactemail, contactcell, contact2name, 
            contact2phone, contact2email, contact2cell, contact3name, contact3phone, contact3email, 
            contact3cell, psalprincipalname, psalprincipalphone, psalprincipalemail, isprimarycontact, 
            canlogin, date, clientrequests, companynotes, deleted, contactphoneExtension, 
            contact2phoneExtension, contact3phoneExtension, related_company, isactive, isala, 
            iscoolingcenter, region, borough, iswithinhalfmile, showsondrillreports, excludereporting, 
            excludetraining, showlasttraining, shownotrained, pendingcoo, showcardsexp, inspectionfrequency, 
            inclusion, donotinclude, summer, nopmsidrequired, ssp, esinotes, municipalna, programna, 
            mdina, directorname, mdaddress, mdphone, bic, typeofentity, nycentity, ambulance, county, 
            mdfax, filingexpirationdate, programexpirationdate, medicalinvoicedate, locationcode 
            ) values ( 
            '$session_iscorp', '" . addslashes($campusid) . "', '" . addslashes($isups) . "', 
            '" . addslashes($sendmonthlyaedchecklist) . "', '" . addslashes($principalname) . "', 
            '" . addslashes($principalemail) . "', '" . addslashes($mdresigned) . "', 
            '" . addslashes($mdresignedname) . "', '" . addslashes($mdresigneddate) . "', 
            '" . addslashes($customernumber) . "', '" . addslashes($schoolcode) . "', 
            '" . addslashes($schoolphone) . "', '" . addslashes($cfn) . "', 
            '" . addslashes($companyname) . "', '" . addslashes($emailtype) . "', 
            '" . addslashes($schoolno) . "', '" . addslashes($buildingno) . "', 
            '" . addslashes($displayname) . "', '" . addslashes($accountmanager) . "', 
            '" . addslashes($officeid) . "', '" . addslashes($address) . "', 
            '" . addslashes($floor) . "', '" . addslashes($city) . "', 
            '" . addslashes($state) . "', '" . addslashes($zip) . "', 
            '" . addslashes($isheadquarter) . "', '" . addslashes($contactname) . "', 
            '" . addslashes($contacttitle) . "', '" . addslashes($contact2title) . "', 
            '" . addslashes($contact3title) . "', '" . addslashes($contactphone) . "', 
            '" . addslashes($contactemail) . "', '" . addslashes($contactcell) . "', 
            '" . addslashes($contact2name) . "', '" . addslashes($contact2phone) . "', 
            '" . addslashes($contact2email) . "', '" . addslashes($contact2cell) . "', 
            '" . addslashes($contact3name) . "', '" . addslashes($contact3phone) . "', 
            '" . addslashes($contact3email) . "', '" . addslashes($contact3cell) . "', 
            '" . addslashes($psalprincipalname) . "', '" . addslashes($psalprincipalphone) . "', 
            '" . addslashes($psalprincipalemail) . "', '" . addslashes($isprimarycontact) . "', 
            '" . addslashes($canlogin) . "', Now(), '" . addslashes($clientrequests) . "', 
            '" . addslashes($companynotes) . "', '" . addslashes($deleted) . "', 
            '" . addslashes($contactphoneExtension) . "', '" . addslashes($contact2phoneExtension) . "', 
            '" . addslashes($contact3phoneExtension) . "', '" . addslashes($related_company) . "', 
            '" . addslashes($isactive) . "', '" . addslashes($isala) . "', 
            '" . addslashes($iscoolingcenter) . "', '" . addslashes($region) . "', 
            '" . addslashes($borough) . "', '" . addslashes($iswithinhalfmile) . "', 
            '" . addslashes($showsondrillreports) . "', '" . addslashes($excludereporting) . "', 
            '" . addslashes($excludetraining) . "', '" . addslashes($showlasttraining) . "', 
            '" . addslashes($shownotrained) . "', '" . addslashes($pendingcoo) . "', 
            '" . addslashes($showcardsexp) . "', '" . addslashes($inspectionfrequency) . "', 
            '" . addslashes($inclusion) . "', '" . addslashes($donotinclude) . "', 
            '" . addslashes($summer) . "', '" . addslashes($nopmsidrequired) . "', 
            '" . addslashes($ssp) . "', '" . addslashes($esinotes) . "', 
            '" . addslashes($municipalna) . "', '" . addslashes($programna) . "', 
            '" . addslashes($mdina) . "', '" . addslashes($directorname) . "', 
            '" . addslashes($mdaddress) . "', '" . addslashes($mdphone) . "', 
            '" . addslashes($bic) . "', '" . addslashes($typeofentity) . "', 
            '" . addslashes($nycentity) . "', '" . addslashes($ambulance) . "', 
            '" . addslashes($county) . "', '" . addslashes($mdfax) . "', 
            '" . addslashes($filingexpirationdate) . "', '" . addslashes($programexpirationdate) . "', 
            '" . addslashes($medicalinvoicedate) . "', '" . addslashes($locationcode) . "' )";
        
        $new_id = db_query_insert_id( $insert_sql );
        
        if( !$officeid )
            db_query( "update company_esi set officeid = '$new_id' where id = $new_id" );

        $id = $new_id;
        include "updatebuildings.php";
        
        if (!$related_company) {
            db_query ("update company_esi set related_company=$new_id where id=$new_id and related_company=0");
        }
        $companyid=$new_id;
        
        db_query( "insert into companychanges ( sessionid, dateadded, companyid ) values ( '$session_userid', now(), '$companyid' )" );
        
        if( !$SaveStay )
        {
            Header( "location: /viewcompany.php?id=$companyid " );
            exit;
        }
    }
}

if( $delete )
{
    db_query( "update company_esi set deleted = 1, deletiondate = now() where id = $id " );
    Header( "location: $redirect " );
    exit;
}
?>
<?php include "ssi/top.php"; ?>
<script LANGUAGE="JavaScript">

function confirmDelete()
{
var agree=confirm("Are you sure you wish to delete?");
if (agree)
return true ;
else
return false ;
}

function confirmRetire()
{
var agree=confirm("Are you sure you wish to retire?");
if (agree)
return true ;
else
return false ;
}

</script>
<script LANGUAGE="JavaScript1.1" SRC="/FormChek.js"></script>
<script language="JavaScript">

function validateUSPersonalInfo(ele)
{
    form = document.forms["comform"];
     if(checkString(form.elements["companyname"],"<?=getSchoolStr( "School" )?> name")==false)
     {
        return false
     }
// add everything but : office id, building, floor, contact phone/email/name, customer number, notes
     if(checkString(form.elements["address"],"Address")==false)
     {
        return false
     }
     if(checkString(form.elements["city"],"City")==false)
     {
        return false
     }
     <?php if( !$id ) { ?>
     if(form.elements["state"].selectedIndex < 1)
     {
         alert( "State is required." );
        return false
     }
     <?php if( !$session_iscorp ) { ?>
     if(form.elements["showsondrillreports"].checked == false )
     {
          if( !confirm( "Are you sure this school doesn't get drills?" ) ) {
          return false;
          }
     }
     <?php } ?>
     <?php } ?>
    if(checkString(form.elements["zip"],"Zip")==false)
     {
        return false
     }
//      if(form.elements["region"].selectedIndex < 1)
//      {
//          alert( "Region is required." );
//         return false
//      }
     if(form.elements["borough"].selectedIndex < 1)
     {
         alert( "Borough is required." );
        return false
     }
     if(checkString(form.elements["principalname"],"Principal name")==false)
     {
        return false
     }
     if(checkString(form.elements["schoolphone"],"<?=getSchoolStr( "School" )?> Phone")==false)
     {
        return false
     }
     if(checkString(form.elements["principalname"],"Principal name")==false)
     {
        return false
     }
     if(checkString(form.elements["schoolcode1"],"<?=getSchoolStr( "School" )?> Number")==false)
     {
        return false
     }
<?php if( $session_userid == "rebekah@emergencyskills.com" || $session_userid == "noah@emergencyskills.com" || !$id ) { ?>
     if(form.elements["schoolcode2"].selectedIndex < 1)
     {
         alert( "<?=getSchoolStr( "School" )?> code is required." );
        return false
     }
<?php } ?>
     if(checkString(form.elements["schoolcode3"],"<?=getSchoolStr( "School" )?> Number")==false)
     {
        return false
     }
     
//      if(checkString(form.elements["officeid"], "Office ID")==false)
//  {
//     return false
//  }
//      if(checkString(form.elements["contactname"],"Contact name")==false)
//  {
//  return false
//  }     
}
//-->
</script>


<?php
$myiscorp = $session_iscorp;
if( $id )
{
    $row = db_query_first( "select * from company_esi where id = $id" );
    $myiscorp = $row["iscorp"] ?? $session_iscorp;
}
if( !$row )
{
    $row = array();
    $row['related_company'] = $related_company ?? ($companyid ?? 0);
    $row["companyname"] = $companyname ?? '';
}
$director_rows = db_query_rows("select value from esioptionvalues where datatype='director' order by value");
$mdaddress_rows = db_query_rows("select value from esioptionvalues where datatype='mdaddress' order by value");
$mdphone_rows = db_query_rows("select value from esioptionvalues where datatype='mdphone' order by value");
$mdfax_rows = db_query_rows("select value from esioptionvalues where datatype='mdfax' order by value");
?>


<?php
if( !$redirect )
{
if( $id )
$redirect="/viewcompany.php?id=$id";
else
$redirect="/editcompany.php";
}
?>

<form method="post" name="comform" enctype='multipart/form-data'>
<input type="hidden" name ="update" value="true">
<input type="hidden" name ="password" value="<?=$row["officeid"] ?? ''?>">
<input type="hidden" name ="redirect" value="<?=$redirect?>">
<input type="hidden" name ="id" value="<?=$id?>">
<input type="hidden" name ="related_company" value="<?=$row["related_company"] ?? ''?>">
<!--start center content-->
<p>

<strong><span class="title">EDIT <?=strtoupper( getSchoolStr( "School", $myiscorp ))?></span></strong>

<p>

<br>
<?php if( ($row["iscorp"] ?? 0) == PROSPECTS ) { ?>
<A onclick='return confirm( "Are you sure you want to mark this prospect as CORPORATE?" )' href='editcompany.php?id=<?=$id?>&markascorp=1'>Mark as Corporate</a>
<?php } ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%"  class="table3">
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><strong><?=getSchoolStr( "School" )?> Information</strong></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong><?=getSchoolStr( "School" )?> Name*:</strong><br><input type="text" size="40" VALUE="<?=htmlspecialchars($row["companyname"] ?? '')?>" name="companyname" style="font-size: 10px;  font-family: verdana;">
<?php if( isOverallAdmin() && $myiscorp ) { ?>
<select name='emailtype'>
<option value=''></option>
<?php 
$all_emailtypes = $all_emailtypes ?? [];
foreach( $all_emailtypes as $et ) { 
   echo( "<option value='$et' ".(($row["emailtype"] ?? '')==$et?"SELECTED":"").">$et</option>" );
 } ?>
</select>
<?php } else { ?>
<input type='hidden' name='emailtype' value='<?=$row["emailtype"] ?? ''?>'>
<?php }?>
<?php if( isOverallAdmin() && !$myiscorp ) { ?>
<input type='checkbox' name='donotinclude' value='1' <?=!empty($row["donotinclude"])?"CHECKED":""?>> Do not include on trained responder reports?
<?php } else { ?>
<input type='hidden' name='donotinclude' value='<?=$row["donotinclude"] ?? ''?>'>
<?php }?>
</span></td>
<td bgcolor="#E2DFDF" class='copy'>
<strong><?=getSchoolStr( "Campus" )?> :</strong><br><select class='copy' name='campusid'>
<option value=''></option>
<?php 
$campuses = getCampuses( $row["zip"] ?? '', $myiscorp );
foreach( $campuses as $cid=>$cname )
{
echo( "<option value='$cid' ".(($cid==($row["campusid"] ?? ''))?"SELECTED":"").">$cname</option>" );
}
?>
</select> <a target=_blank href='editcampus.php?zip=<?=$row["zip"] ?? ''?>'>Add new </a>
</td>
</tr>
<tr>
<td bgcolor="#E2DFDF"> 
<?php if( $myiscorp ) { ?>
<nobr>
<span class="copy"><strong>Headquarters:</strong><input type='radio' name='isheadquarter' value="1" <?=!empty($row["isheadquarter"])?"CHECKED":""?>>
<span class="copy"><strong>Branch:</strong><input type='radio' name='isheadquarter' value="0" <?=empty($row["isheadquarter"])?"CHECKED":""?>>
</nobr>
<br>
<?php } ?>
<span class="copy"><strong>Building No:</strong><br><input type='text' name='buildingno' value="<?=htmlspecialchars($row["buildingno"] ?? '')?>" style="font-size: 10px;  font-family: verdana;"></tD>


<td valign="top" bgcolor="#E2DFDF">
<table cellspacing="0"  cellpadding=0 border="0" width="100%"><tr>
<td>&nbsp;</td>
<?php if( !$myiscorp ) { ?>
<td>
<span class="copy"><strong>Drill<br> Reports?</strong></span>
</td>
<td>
<span class="copy"><strong>Inclusion?</strong></span>
</td>
<td>
<span class="copy"><strong>Summer<br> School?</strong></span>
</td>
<td>
<span class="copy"><strong>No <?=getSchoolStr( "PMS ID" )?></strong></span>
</td>
<td>
<span class="copy"><strong>SSP</strong></span>
</td>
<td>
<span class="copy"><strong>BIC</strong></span>
</td>
<?php } else { ?>
<td><strong>Display Name:</strong> <input type='text' name='displayname' value="<?=htmlspecialchars($row["displayname"] ?? '')?>" size='30'>
<br><strong>Account Manager:</strong> <select name='accountmanager'>
<option value=''></option>
<?php
$over = db_query_rows( "Select * from user where overalladmin = 1 order by last_name, first_name" );
 foreach( $over as $o ) { ?>
<option value='<?=$o["id"] ?? ''?>' <?=(($o["id"] ?? '') == ($row["accountmanager"] ?? ''))?"SELECTED":""?>><?=($o["first_name"] ?? '') . " " . ($o["last_name"] ?? '')?></option>
<?php } ?>
</select>
</td>
<?php } ?>
</tr><tr>
<td>&nbsp;</td>
<?php if( !$myiscorp ) { ?>
<td><input type='checkbox' name='showsondrillreports' size='1' value="1" <?=!empty($row["showsondrillreports"])?"CHECKED":""?>></td>
<td><input type='checkbox' name='inclusion' size='1' value="1" <?=!empty($row["inclusion"])?"CHECKED":""?>></td>
<td><input type='checkbox' name='summer' size='1' value="1" <?=!empty($row["summer"])?"CHECKED":""?>></td>
<td><input type='checkbox' name='nopmsidrequired' size='1' value="1" <?=!empty($row["nopmsidrequired"])?"CHECKED":""?>></td>
<td><input type='checkbox' name='ssp' size='1' value="1" <?=!empty($row["ssp"])?"CHECKED":""?>></td>
<td><input type='checkbox' name='bic' size='1' value="1" <?=!empty($row["bic"])?"CHECKED":""?>></td>
<?php } ?>
</tr></table>
</td>


</tr>


<tr>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Address *:</strong><br><input type="text" size="30" VALUE="<?=htmlspecialchars($row["address"] ?? '')?>" maxlength="50" name="address" style="font-size: 10px;  font-family: verdana;"></span></td>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Floor/Room:</strong><br><input type="text" size="30" VALUE="<?=htmlspecialchars($row["floor"] ?? '')?>" maxlength="50" name="floor" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>


<tr>
<td valign="top" bgcolor="#E2DFDF" colspan="2">

<table border="0" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td valign="top"><span class="copy"><strong>City* :</strong><br><input type="text" size="30" VALUE="<?=htmlspecialchars($row["city"] ?? '')?>" maxlength="50" name="city" style="font-size: 10px;  font-family: verdana;"></span></td>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>State* :</strong><br>

<select name="state" style="font-size: 10px;  font-family: verdana;">
<option value="<?=htmlspecialchars($row["state"] ?? '')?>"><?=htmlspecialchars($row["state"] ?? '')?></option>
<option value="Other">Other</option>
<option value="Alabama">Alabama</option>
<option value="Alaska">Alaska</option>
<option value="Arizona">Arizona</option>
<option value="Arkansas">Arkansas</option>
<option value="California">California</option>
<option value="Colorado">Colorado</option>
<option value="Connecticut">Connecticut</option>
<option value="Delaware">Delaware</option>
<option value="DC">District of Columbia</option>
<option value="Florida">Florida</option>
<option value="Georgia">Georgia</option>
<option value="Hawaii">Hawaii</option>
<option value="Idaho">Idaho</option>
<option value="Illinois">Illinois</option>
<option value="Indiana">Indiana</option>
<option value="Iowa">Iowa</option>
<option value="Kansas">Kansas</option>
<option value="Kentucky">Kentucky</option>
<option value="Louisiana">Louisiana</option>
<option value="Maine">Maine</option>
<option value="Maryland">Maryland</option>
<option value="Massachusetts">Massachusetts</option>
<option value="Michigan">Michigan</option>
<option value="Minnesota">Minnesota</option>
<option value="Mississippi">Mississippi</option>
<option value="Missouri">Missouri</option>
<option value="Montana">Montana</option>
<option value="Nebraska">Nebraska</option>
<option value="Nevada">Nevada</option>
<option value="New Hampshire">New Hampshire</option>
<option value="New Jersey">New Jersey</option>
<option value="New Mexico">New Mexico</option>
<option value="New York">New York</option>
<option value="North Carolina">North Carolina</option>
<option value="North Dakota">North Dakota</option>
<option value="Ohio">Ohio</option>
<option value="Oklahoma">Oklahoma</option>
<option value="Oregon">Oregon</option>
<option value="Pennsylvania">Pennsylvania</option>
<option value="Rhode Island">Rhode Island</option>
<option value="South Carolina">South Carolina</option>
<option value="South Dakota">South Dakota</option>
<option value="Tennessee">Tennessee</option>
<option value="Texas">Texas</option>
<option value="Utah">Utah</option>
<option value="Vermont">Vermont</option>
<option value="Virginia">Virginia</option>
<option value="Washington">Washington</option>
<option value="West Virginia">West Virginia</option>
<option value="Wisconsin">Wisconsin</option>
<option value="Wyoming">Wyoming</option>
<option value="Alberta">Alberta</option>
<option value="British Columbia">British Columbia</option>
<option value="Manitoba">Manitoba</option>
<option value="New Brunswick">New Brunswick</option>
<option value="Newfoundland">Newfoundland</option>
<option value="Northwest Territories">Northwest Territories</option>
<option value="Nova Scotia">Nova Scotia</option>
<option value="Nunavut">Nunavut</option>
<option value="Ontario">Ontario</option>
<option value="Prince Edward Island">Prince Edward Island</option>
<option value="Queenc">Quebec</option>
<option value="Saskatchewan">Saskatchewan</option>
<option value="Yukon">Yukon</option>
</select></span>
            </td>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Zip* :</strong><br><input type="text" size="10" VALUE="<?=htmlspecialchars($row["zip"] ?? '')?>" maxlength="50" name="zip" style="font-size: 10px;  font-family: verdana;" ></span>
</td>
</tr>
</table>
</td>
</tr>


<tr>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Region* :</strong><br>
<input type='text' name='region' value='<?=htmlspecialchars($row["region"] ?? '')?>' size='8' style="font-size: 10px;  font-family: verdana;">
    <?php

if( ($row["region"] ?? '') == "Aging" || ($row["region"] ?? '') == "OAC" || (!$id && $session_iscorp ) )
{
?>
<Br><span class="copy"><strong>Company Code:</strong><br><input type='text'  name="schoolcode" size=20 value="<?=htmlspecialchars($row["schoolcode"] ?? '')?>"></span>
<?php 
}

if( !($row["iscorp"] ?? false) && !$session_iscorp ) { ?>&nbsp;&nbsp;&nbsp;&nbsp;
<span class="copy"><strong>CFN* :</strong>
<input type='text' name='cfn' value='<?=htmlspecialchars($row["cfn"] ?? '')?>' size='5' style="font-size: 10px;  font-family: verdana;">
<?php } ?>
<br><span class="copy"><strong>Is Cooling Center? :</strong>
<input type='checkbox' name='iscoolingcenter' value='1' <?=!empty($row["iscoolingcenter"])?"CHECKED":""?>>
<br><span class="copy"><strong>ALA? :</strong>
<input type='checkbox' name='isala' value='1' <?=!empty($row["isala"])?"CHECKED":""?>>
</span></td>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Borough* :</strong><br><select class='copy' name='borough'>
<option value=''></option>
<?php if( $myiscorp ){ ?>
<option <?=($row["borough"] ?? '')=="other"?"SELECTED":""?> value="other">Other</option>
<option <?=($row["borough"] ?? '')=="New Jersey"?"SELECTED":""?> value="New Jersey">New Jersey</option>
<?php } else { ?>
<option <?=($row["borough"] ?? '')=="Bronx"?"SELECTED":""?> value="Bronx">The Bronx</option>
<option <?=($row["borough"] ?? '')=="Brooklyn"?"SELECTED":""?> value="Brooklyn">Brooklyn</option>
<option <?=($row["borough"] ?? '')=="Manhattan"?"SELECTED":""?> value="Manhattan">Manhattan</option>
<option <?=($row["borough"] ?? '')=="Queens"?"SELECTED":""?> value="Queens">Queens</option>
<option <?=($row["borough"] ?? '')=="Staten Island"?"SELECTED":""?> value="Staten Island">Staten Island</option>
<?php } ?>
</select><br>
<b>Is this <?=getSchoolStr( "school" )?> within a half mile walk of the subway?</b> <br>
<select class='copy' name='iswithinhalfmile'>
<option <?=($row["iswithinhalfmile"] ?? -1)=="-1"?"SELECTED":""?> value="-1">Not Set</option>
<option <?=($row["iswithinhalfmile"] ?? 0)=="1"?"SELECTED":""?> value="1">Yes</option>
<option <?=($row["iswithinhalfmile"] ?? -1)=="0"?"SELECTED":""?> value="0">No</option>
</select>
</span></td>
</tr>
<tr><td valign="top" bgcolor="#E2DFDF" >
<b><?=getSchoolStr( "School" )?> Photo:</b> <input type='file' name='schoolphoto'>
<?php if( file_exists( "schoolimages/{$id}.jpg" ) ) { ?>
    <a href="schoolimages/<?=$id?>.jpg" target=_blank><img src="schoolimages/<?=$id?>.jpg" height=30></a>
   <a href="editcompany.php?id=<?=$id?>&removephoto=1" onClick="return confirm( 'Are you sure you want to remove this photo? This action cannot be undone.' ) ">Remove?</a>
<br><br>    <?php } ?>
    </td><td valign="top" bgcolor="#E2DFDF"><b>Is UPS?</b> <input type='checkbox' name='isups' value='1' <?=!empty($row["isups"])?"CHECKED":""?>>
</td></tr>

<input type='hidden' name='isactive' value='1'>

<tr>
<td valign="top" bgcolor="#E2DFDF" colspan="2"><span class="copy"><strong>Contact 1:</strong> Title: <input type="text" size="20" VALUE="<?=htmlspecialchars($row["contacttitle"] ?? '')?>" maxlength="40" name="contacttitle" style="font-size: 10px;  font-family: verdana;"><br>Name: <input type="text" size="50" VALUE="<?=htmlspecialchars($row["contactname"] ?? '')?>" maxlength="50" name="contactname" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Contact Phone:</strong><br><span class="small"><em>Please enter as 111-222-3333</em></span><br><input type="text" size="15" VALUE="<?=htmlspecialchars($row["contactphone"] ?? '')?>" maxlength="50" name="contactphone" style="font-size: 10px;  font-family: verdana;"> Ext. <input type="text" size="4" VALUE="<?=htmlspecialchars($row["contactphoneExtension"] ?? '')?>" maxlength="50" name="contactphoneExtension" style="font-size: 10px;  font-family: verdana;"></span></td>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Contact Email:</strong><br><input type="text" size="30" VALUE="<?=htmlspecialchars($row["contactemail"] ?? '')?>" maxlength="50" name="contactemail" style="font-size: 10px;  font-family: verdana;"></span><Br>
<span class="copy"><strong>Contact Cell:</strong><br><input type="text" size="30" VALUE="<?=htmlspecialchars($row["contactcell"] ?? '')?>" maxlength="50" name="contactcell" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" colspan="2"><span class="copy"><strong>Contact 2:</strong> Title: <input type="text" size="20" VALUE="<?=htmlspecialchars($row["contact2title"] ?? '')?>" maxlength="40" name="contact2title" style="font-size: 10px;  font-family: verdana;"><br>Name: <input type="text" size="50" VALUE="<?=htmlspecialchars($row["contact2name"] ?? '')?>" maxlength="50" name="contact2name" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Contact 2 Phone:</strong><br><span class="small"><em>Please enter as 111-222-3333</em></span><br><input type="text" size="15" VALUE="<?=htmlspecialchars($row["contact2phone"] ?? '')?>" maxlength="50" name="contact2phone" style="font-size: 10px;  font-family: verdana;"> Ext. <input type="text" size="4" VALUE="<?=htmlspecialchars($row["contact2phoneExtension"] ?? '')?>" maxlength="50" name="contact2phoneExtension" style="font-size: 10px;  font-family: verdana;"></span></td>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Contact 2 Email:</strong><br><input type="text" size="30" VALUE="<?=htmlspecialchars($row["contact2email"] ?? '')?>" maxlength="50" name="contact2email" style="font-size: 10px;  font-family: verdana;"></span><Br><span class="copy"><strong>Contact 2 Cell:</strong><br><input type="text" size="30" VALUE="<?=htmlspecialchars($row["contact2cell"] ?? '')?>" maxlength="50" name="contact2cell" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" colspan="2"><span class="copy"><strong>Contact 3:</strong> Title: <input type="text" size="20" VALUE="<?=htmlspecialchars($row["contact3title"] ?? '')?>" maxlength="40" name="contact3title" style="font-size: 10px;  font-family: verdana;"><br>Name: <input type="text" size="50" VALUE="<?=htmlspecialchars($row["contact3name"] ?? '')?>" maxlength="50" name="contact3name" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Contact 3 Phone:</strong><br><span class="small"><em>Please enter as 111-222-3333</em></span><br><input type="text" size="15" VALUE="<?=htmlspecialchars($row["contact3phone"] ?? '')?>" maxlength="50" name="contact3phone" style="font-size: 10px;  font-family: verdana;"> Ext. <input type="text" size="4" VALUE="<?=htmlspecialchars($row["contact3phoneExtension"] ?? '')?>" maxlength="50" name="contact3phoneExtension" style="font-size: 10px;  font-family: verdana;"></span></td>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Contact 3 Email:</strong><br><input type="text" size="30" VALUE="<?=htmlspecialchars($row["contact3email"] ?? '')?>" maxlength="50" name="contact3email" style="font-size: 10px;  font-family: verdana;"></span><br><span class="copy"><strong>Contact 3 Cell:</strong><br><input type="text" size="30" VALUE="<?=htmlspecialchars($row["contact3cell"] ?? '')?>" maxlength="50" name="contact3cell" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Medical Direction Invoice Date:</strong><br><span class="small"><em>Please enter as MM/YYYY</em></span><br><input type="text" size="40" VALUE="<?=fixdatefordisplay( $row['medicalinvoicedate'] ?? '' )?>" maxlength="50" name="medicalinvoicedate" style="font-size: 10px;  font-family: verdana;">
<br><input type='checkbox' name='mdina' value='1' <?=!empty($row["mdina"])?"CHECKED":""?>> N/A?
</span></td>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Program Management Expiration Date:</strong><br><span class="small"><em>Please enter as MM/YYYY</em></span><br><input type="text" size="40" VALUE="<?=fixdatefordisplay( $row['programexpirationdate'] ?? '' )?>" maxlength="50" name="programexpirationdate" style="font-size: 10px;  font-family: verdana;"><br>
<input type='checkbox' name='programna' value='1' <?=!empty($row["programna"])?"CHECKED":""?>> N/A?
</span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">

<span class="copy"><strong>Medical Director Name*:</strong><br>

<select name="directorname" style="font-size: 10px;  font-family: verdana;">
<option value='<?=$row['directorname'] ?? '';?>'><?=$row['directorname'] ?? '';?></option>
<?php foreach ($director_rows as $director) { ?>
<option value="<?=$director['value'] ?? '';?>"><?=$director['value'] ?? '';?></option>
<?php } ?>
</select>
</span>
<br><span class="copy"><strong>Medical Director Address*:</strong><br>

<select name="mdaddress" style="font-size: 10px;  font-family: verdana;">
<option value='<?=$row['mdaddress'] ?? '';?>'><?=$row['mdaddress'] ?? '';?></option>
<?php foreach ($mdaddress_rows as $mdaddress) { ?>
<option value="<?=$mdaddress['value'] ?? '';?>"><?=$mdaddress['value'] ?? '';?></option>
<?php } ?>
</select>
</span>
<br><span class="copy"><strong>Medical Director Phone*:</strong><br>

<select name="mdphone" style="font-size: 10px;  font-family: verdana;">
<option value='<?=$row['mdphone'] ?? '';?>'><?=$row['mdphone'] ?? '';?></option>
<?php foreach ($mdphone_rows as $mdphone) { ?>
<option value="<?=$mdphone['value'] ?? '';?>"><?=$mdphone['value'] ?? '';?></option>
<?php } ?>
</select>
</span>
<br><span class="copy"><strong>Medical Director Fax*:</strong><br>

<select name="mdfax" style="font-size: 10px;  font-family: verdana;">
<option value='<?=$row['mdfax'] ?? '';?>'><?=$row['mdfax'] ?? '';?></option>
<?php foreach ($mdfax_rows as $mdfax) { ?>
<option value="<?=$mdfax['value'] ?? '';?>"><?=$mdfax['value'] ?? '';?></option>
<?php } ?>
</select>
</span>
<br><span class="copy"><strong>Medical Director Resigned?:</strong><br>
<input type='checkbox' name='mdresigned' value='1' <?=!empty($row["mdresigned"])?"CHECKED":""?>>
Name: <input type="text" size="15" VALUE="<?=htmlspecialchars($row["mdresignedname"] ?? '')?>" maxlength="50" name="mdresignedname" style="font-size: 10px;  font-family: verdana;">
<Br>
Date: <?=printdates2( "mdresigneddate", ($row["mdresigneddate"] ?? '')>'0000-00-00'?($row["mdresigneddate"] ?? ''):"" )?></span>
<Br><br>
<br><span class="copy"><strong>Exclude From Reporting:</strong><br>
<input type='checkbox' name='excludereporting' size='1' value="1" <?=!empty($row["excludereporting"])?"CHECKED":""?>>
</span>
<br><span class="copy"><strong>Exclude From Training Reports:</strong><br>
<input type='checkbox' name='excludetraining' size='1' value="1" <?=!empty($row["excludetraining"])?"CHECKED":""?>>
</span>
<br><span class="copy"><strong>Show on "Last Training > 10 Mo Ago":</strong><br>
<input type='checkbox' name='showlasttraining' size='1' value="1" <?=!empty($row["showlasttraining"])?"CHECKED":""?>>
</span>
<br><span class="copy"><strong>Show on "Cards Exp in 60 Days":</strong><br>
<input type='checkbox' name='showcardsexp' size='1' value="1" <?=!empty($row["showcardsexp"])?"CHECKED":""?>>
</span>
<br><span class="copy"><strong>Show on "No Trained Resp.":</strong><br>
<input type='checkbox' name='shownotrained' size='1' value="1" <?=!empty($row["shownotrained"])?"CHECKED":""?>>
</span>
<br><span class="copy"><strong>Pending COO:</strong><br>
<input type='checkbox' name='pendingcoo' size='1' value="1" <?=!empty($row["pendingcoo"])?"CHECKED":""?>>
</span>

<?php if( $row["iscorp"] ?? false ) { ?>
<?php if( $row["iscorp"] ) { ?>
<br><b>Send Monthly AED Reminder?</b><Br> <input type='checkbox' name='sendmonthlyaedchecklist' value='1' <?=!empty($row["sendmonthlyaedchecklist"])?"CHECKED":""?>>
<br>
<?php } ?>
<br><span class="copy"><strong>Inspections Required:</strong><br>
<input type='radio' name='inspectionfrequency' size='1' value="0" <?=empty($row["inspectionfrequency"])?"CHECKED":""?>> Never 
<input type='radio' name='inspectionfrequency' size='1' value="1" <?=($row["inspectionfrequency"] ?? 0)==1?"CHECKED":""?>> 1/year 
<input type='radio' name='inspectionfrequency' size='1' value="2" <?=($row["inspectionfrequency"] ?? 0)==2?"CHECKED":""?>> 2/year 
<input type='radio' name='inspectionfrequency' size='1' value="3" <?=($row["inspectionfrequency"] ?? 0)==3?"CHECKED":""?>> 3/year <br>
<input type='radio' name='inspectionfrequency' size='1' value="4" <?=($row["inspectionfrequency"] ?? 0)==4?"CHECKED":""?>> Supply replacement Only
<?php } ?>
</td>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Municipal Filing Expiration Date:</strong><br><span class="small"><em>Please enter as MM/YYYY</em></span><br><input type="text" size="40" VALUE="<?=fixdatefordisplay( $row['filingexpirationdate'] ?? '' )?>" maxlength="50" name="filingexpirationdate" style="font-size: 10px;  font-family: verdana;"><br>
<input type='checkbox' name='municipalna' value='1' <?=!empty($row["municipalna"])?"CHECKED":""?>> N/A?
<br>

<?php $drop_rows = db_query_rows("select value from esioptionvalues where datatype='typeofentity' order by value"); ?>
<br><span class="copy"><strong>Type of Entity: </strong><br>
<select name="typeofentity" style="font-size: 10px;  font-family: verdana;">
<option value='<?=$row['typeofentity'] ?? '';?>'><?=$row['typeofentity'] ?? '';?></option>
<?php foreach ($drop_rows as $d) { ?>
<option value="<?=$d["value"] ?? '';?>"><?=$d["value"] ?? '';?></option>
<?php } ?>
</select>
<Br>
<?php $drop_rows = db_query_rows("select value from esioptionvalues where datatype='nycentity' order by value"); ?>
<br><span class="copy"><strong>NYC Local Law 20 Entity:</strong><br>
<select name="nycentity" style="font-size: 10px;  font-family: verdana;">
<option value='<?=$row['nycentity'] ?? '';?>'><?=$row['nycentity'] ?? '';?></option>
<?php foreach ($drop_rows as $d) { ?>
<option value="<?=$d["value"] ?? '';?>"><?=$d["value"] ?? '';?></option>
<?php } ?>
</select>
<Br>

<?php $drop_rows = db_query_rows("select value from esioptionvalues where datatype='ambulance' order by value"); ?>
<br><span class="copy"><strong>Ambulance/911 Center:</strong><br>
<select name="ambulance" style="font-size: 10px;  font-family: verdana;">
<option value='<?=$row['ambulance'] ?? '';?>'><?=$row['ambulance'] ?? '';?></option>
<?php foreach ($drop_rows as $d) { ?>
<option value="<?=$d["value"] ?? '';?>"><?=$d["value"] ?? '';?></option>
<?php } ?>
</select>
<Br>

<?php $drop_rows = db_query_rows("select value from esioptionvalues where datatype='county' order by value"); ?>
<br><span class="copy"><strong>County:</strong><br>
<select name="county" style="font-size: 10px;  font-family: verdana;">
<option value='<?=$row['county'] ?? '';?>'><?=$row['county'] ?? '';?></option>
<?php foreach ($drop_rows as $d) { ?>
<option value="<?=$d["value"] ?? '';?>"><?=$d["value"] ?? '';?></option>
<?php } ?>
</select>
<Br>

</span></td>
</tr>
<?php if( !$myiscorp ) { ?>
<tr>
<td valign="top" bgcolor="#E2DFDF" colspan="2"><span class="copy"><strong>PSAL principal Name:</strong><br><input type="text" size="70" VALUE="<?=htmlspecialchars($row["psalprincipalname"] ?? '')?>" maxlength="50" name="psalprincipalname" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>PSAL principal Phone:</strong><br><input type="text" size="15" VALUE="<?=htmlspecialchars($row["psalprincipalphone"] ?? '')?>" maxlength="50" name="psalprincipalphone" style="font-size: 10px;  font-family: verdana;"> </span></td>
<td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>PSAL principal Email:</strong><br><input type="text" size="30" VALUE="<?=htmlspecialchars($row["psalprincipalemail"] ?? '')?>" maxlength="50" name="psalprincipalemail" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<?php } ?>
<input type='hidden' name='isprimarycontact' value='1'>
<input type='hidden' name='canlogin' value='1'>
<tr>
<td valign="top" bgcolor="#E2DFDF" colspan="2"><span class="copy"><strong>General <?=getSchoolStr( "School" )?> Notes:</strong><br><textarea cols="70" name="companynotes" rows="3" wrap="virtual" style="font-size: 10px;  font-family: verdana;"><?=htmlspecialchars(stripslashes($row["companynotes"] ?? ''))?></textarea></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" colspan="2"><span class="copy"><strong>Client Requests:</strong><br><textarea cols="70" name="clientrequests" rows="3" wrap="virtual" style="font-size: 10px;  font-family: verdana;"><?=htmlspecialchars($row["clientrequests"] ?? '')?></textarea></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" colspan="2"><span class="copy"><strong>Parking Info:</strong><br><textarea cols="70" name="parkinginfo" rows="3" wrap="virtual" style="font-size: 10px;  font-family: verdana;"><?=htmlspecialchars($row["parkinginfo"] ?? '')?></textarea></span></td>
</tr>
<?php
 if( !$myiscorp ) { ?>
<tr  bgcolor="#E2DFDF" ><td colspan='2'><span class='copy'><strong>DOE Supplied Data</strong></span>
<table>
<tr><td><span class='copy'>Principal Name* :</td><td span class='copy'><input class='copy' type='text' name='principalname' value="<?=htmlspecialchars($row["principalname"] ?? '')?>"></td></tr>
<tr><td><span class='copy'>Principal Email* :</td><td span class='copy'><input class='copy'  type='text' name='principalemail' value="<?=htmlspecialchars($row["principalemail"] ?? '')?>"></td></tr>
<!--<tr><td><span class='copy'>Customer #:</td><td span class='copy'><input type='text' class='copy' name='customernumber' value="<?=htmlspecialchars($row["customernumber"] ?? '')?>"></td></tr>-->
<input type='hidden' name='customernumber' value="<?=htmlspecialchars($row["customernumber"] ?? '')?>">
<tr><td><span class='copy'>School Number* :</td><td span class='copy'>
<?php $spl = explode( "-", $row["schoolcode"] ?? '' ); ?>
<input type='text' <?php if( $session_userid != "rebekah@emergencyskills.com" && $session_userid != "noah@emergencyskills.com" && $id ) { ?>READONLY<?php } ?> class='copy' name='schoolcode1' value="<?=htmlspecialchars($spl[0] ?? '')?>" size='2' maxlength='2'>
-<select class='copy' name='schoolcode2'  <?php if( $session_userid != "rebekah@emergencyskills.com" && $session_userid != "noah@emergencyskills.com" && $id ) { ?>disabled<?php } ?> >
<option value=''></option>
<option <?=($spl[1] ?? '')=="M"?"SELECTED":""?> value='M'>M</option>
<option <?=($spl[1] ?? '')=="Q"?"SELECTED":""?> value='Q'>Q</option>
<option <?=($spl[1] ?? '')=="R"?"SELECTED":""?> value='R'>R</option>
<option <?=($spl[1] ?? '')=="X"?"SELECTED":""?> value='X'>X</option>
<option <?=($spl[1] ?? '')=="K"?"SELECTED":""?> value='K'>K</option>
</select>-
<?php if( $session_userid != "rebekah@emergencyskills.com" && $session_userid != "noah@emergencyskills.com" && $id ) { ?><input type='hidden' name='schoolcode2' value='<?=htmlspecialchars($spl[1] ?? '')?>'><?php } ?>
<input type='text' class='copy' name='schoolcode3' value="<?=htmlspecialchars($spl[2] ?? '')?>" maxlength='3' size='3'  <?php if( $session_userid != "rebekah@emergencyskills.com" && $session_userid != "noah@emergencyskills.com" && $id ) { ?>READONLY<?php } ?> >
</td> </tr>
<tr><td><span class='copy'>School Phone* :</td><td span class='copy'><input class='copy' type='text' name='schoolphone' value="<?=htmlspecialchars($row["schoolphone"] ?? '')?>"></td></tr>
<tr><td><span class='copy'>Location Code* :</td><td span class='copy'><input class='copy' type='text' name='locationcode' value="<?=htmlspecialchars($row["locationcode"] ?? '')?>"></td></tr>
<tr><td><span class='copy'>Building Code(s)* :</td><td span class='copy'><table>
    <tr><th>Code</th><th>Name</th><th>Address</th><th>City</th><th>State</th><th>Zip</th></tr>
<?php 
$buildings = array();
if( $row["locationcode"] ?? '' )
    $buildings = getBuildingsForLocation( $row["locationcode"] ?? '' );
$buildings[] = array();    
$buildings[] = array();    
$i = 0;
foreach( $buildings as $brow )
{
echo( "<Tr><input type='hidden' name='bids[$i]' value='".($brow['id'] ?? '')."'>" );
    echo( "<td><input type='text' name='bcodes[$i]' size='8' value=\"".htmlspecialchars($brow['buildingcode'] ?? '')."\">" );
    echo( "<td><input type='text' name='bnames[$i]' size='8' value=\"".htmlspecialchars($brow['buildingname'] ?? '')."\">" );
    echo( "<td><input type='text' name='badds[$i]' value=\"".htmlspecialchars($brow['address'] ?? '')."\">" );
    echo( "<td><input type='text' name='bcitys[$i]' value=\"".htmlspecialchars($brow['city'] ?? '')."\">" );
    echo( "<td><input type='text' name='bstates[$i]' size='2' value=\"".htmlspecialchars($brow['state'] ?? '')."\">" );
    echo( "<td><input type='text' name='bzips[$i]' size='6' value=\"".htmlspecialchars($brow['zip'] ?? '')."\">" );
    echo( "</tr>" );
    $i++;
}
?>
</table>
</td></tr>
</table>
</span>
</td>
</tr>
<?php } ?>
<?php if( isOverallAdmin() ) { ?>
<tr  bgcolor="#E2DFDF"><td colspan='4'><b>ESI Notes:<br> <textarea cols='50' rows='5' name='esinotes'><?=htmlspecialchars($row["esinotes"] ?? '')?></textarea></td></tr>
<?php } ?>
<?php if( !$readonly ) { ?>

<tr>
<td valign="top" bgcolor="#FFFFFF" colspan="2"><br><div align="center"><input name="Save" type="submit" value="Save & Continue" onClick="return validateUSPersonalInfo(this)" > <input name="SaveStay" type="submit" value="Save" onClick="return validateUSPersonalInfo(this)" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if( $id && isOverallAdmin() ){ ?>
<?php if( empty($row["deleted"]) ) { ?>
<input onclick="return confirmDelete();return true" type="Submit" name="delete" value="Delete">
<input onclick="return confirmRetire();return true" type="Submit" name="retire" value="Retire">
<?php } else if( $row["retired"] ?? false ) { ?>
<input onclick="return confirmUnretire();return true" type="Submit" name="unretire" value="Unretire">
<?php } ?>

<br><br>
<input type="Submit" name="merge" value="Merge this <?=getSchoolStr( "school" )?> with:">
<select name='otherschool' class='copy'>
<option value=''></option>
<?php 
$res = db_query_array( "select id, concat( companyname, ' - ', schoolcode ) as companyname from company_esi where deleted = 0 and iscorp = '$myiscorp' and id <> $id order by companyname", "id", "companyname" );
foreach( $res as $r=>$name )
{
echo( "<option value='$r'>$name</option>" );
}
?>
</select><br> <input type='checkbox' name='retired' value='1'> Retire this school? <br><span class='copy'><br><i>Note: this <?=getSchoolStr( "school" )?>'s responders, drills, classes and aeds will be updated to belong to the selected <?=getSchoolStr( "school" )?>.<br>

<?php } ?>
</div>
</td>
</tr>
<?php } ?>
</table>
<br>
<br>
<!--end center content-->
<?php include "ssi/footer.php" ; ?>s
<!--end footer-->

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</form>
</body>
</html>