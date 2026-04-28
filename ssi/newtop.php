<?php

if (isset($_GET['setcorp'])) {
session_start();
$_SESSION['iscorp'] = $_GET['setcorp'];

// Remove setcorp from the URL so it doesn't loop, then redirect
$clean_url = strtok($_SERVER["REQUEST_URI"], '?'); 
parse_str($_SERVER['QUERY_STRING'], $params);
unset($params['setcorp']);
$query = http_build_query($params);
$final_url = $clean_url . ($query ? '?' . $query : '');

header("Location: " . $final_url);
exit();
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
<title>ALIVE!net :: A Product of Emergency Skills, Inc.</title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<link rel="stylesheet" href="newcss/style.css">
<script language="JavaScript" src="calendar2.js"></script><link href="newcss/calendar.css" rel="stylesheet" type="text/css">
<script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script> 
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

<script>
$().ready(function() {
$(".topsearch").autocomplete({
source: function ( request, response ) {
var radioval = $('input[name=searchtype]:checked', '#searchform').val();
var searchboxval = $('input[name=searchbox]', '#searchform').val();
// PHP 8.2 Conversion: Use null coalescing
url = "/livesearch.php?corp=<?= $session_iscorp ?>&id=<?= $session_id ?>&q="+searchboxval + "&type="+radioval;
//alert( url );
$.getJSON(url + '&callback=?', function(data) {
response(data);
});

},
width: 260,
minLength: 3,
matchContains: true,
selectFirst: false,
select: function( event, ui ) {
document.location.href = "/viewcompany.php?id=" + ui.item.id;
}
});
<?php if( $hasjobtitleautocomplete ) {
$arr = db_query_rows( "select distinct( trim( name ) ) as name from jobtitles order by name" );
?>
var availableTagsJTs = [
<?php foreach( $arr as $row ) { echo( "\"" . ($row["name"]) . "\", "); } ?>
];

$( "#autocompletetitle" ).autocomplete({
source: availableTagsJTs
});
<?php } ?>
});
<?php
if( isset( $_GET["tryit"]))
{
$tryit = $_GET["tryit"];
// PHP 8.2 Safe alternative to session_register
$_SESSION["tryit"] = $tryit; 
}
//isset( $_GET["tryit"])|| $_SESSION["tryit"]
if( 1 )
{
?>

window.onscroll = function() {scrollFunction()};

function scrollFunction() {
if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
document.getElementById("myBtn").style.display = "block";
} else {
document.getElementById("myBtn").style.display = "none";
}
}

// When the user clicks on the button, scroll to the top of the document
function topFunction() {
document.body.scrollTop = 0; // For Safari
document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
}

<?php
}
?>

</script>
</head>

<STYLE TYPE="text/css">
<?php
// $_GET["tryit"] || $_SESSION["tryit"]
if( 1 ) {
?>
#header-total-wrapper {
width:100%;
<?php if( !($headernotfixed) ) { ?>
position:fixed;
<?php } ?> top : 0px;}
<?php if( !($headernotfixed) ) { ?>
#main-content{
margin-top: 120px;
}
<?php } ?>

<?php if( !($headernotfixed) ) { ?>

#col-left{
margin-top: 120px;
}

<?php } ?>

#myBtn {
display: none; /* Hidden by default */
position: fixed; /* Fixed/sticky position */
bottom: 20px; /* Place the button at the bottom of the page */
right: 30px; /* Place the button 30px from the right */
z-index: 99; /* Make sure it does not overlap */
border: none; /* Remove borders */
outline: none; /* Remove outline */
background-color: #e1e1f6; /* Set a background color */
color: white; /* Text color */
cursor: pointer; /* Add a mouse pointer on hover */
padding: 15px; /* Some padding */
border-radius: 10px; /* Rounded corners */
font-size: 18px; /* Increase font size */
}

#myBtn:hover {
background-color: #555; /* Add a dark-grey background on hover */
}

<?php } ?>

</STYLE>
<button onclick="topFunction()" id="myBtn" title="Go to top">Top</button>

<?php
// Map the setcorp values to the CSS IDs you defined
$body_id = 'p-doe'; // Default

if (isset($_SESSION['iscorp'])) {
if ($_SESSION['iscorp'] == 0) $body_id = 'p-doe';
if ($_SESSION['iscorp'] == 1) $body_id = 'p-corporate';
if ($_SESSION['iscorp'] == 3) $body_id = 'p-training';
if ($_SESSION['iscorp'] == 4) $body_id = 'p-dfta';
}
?>

<body id="<?= $body_id ?>">

<div id="header-total-wrapper">
<div id="header-wrapper">
<div id="header-container">

<div id="logo">
<a href='http://<?php echo SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN; ?>'><img src="https://<?php echo SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN; ?>/images/YellowESILogo800x183.png" style="height: 40px" border=0></a>
</div>
<?php if( isOverallAdmin() || ($thisusersrow["companyid"]) == "0" || ($thisusersrow["companyid"]) == PARKSMAINCOMPANY ) { ?>
<div id="search-box">
<form method='post' id="searchform" onSubmit="return dosearchsubmit( this )">
<input type='hidden' name='hidden1'>
<input type='hidden' name='hidden2'>
<table cellpadding="4" cellspacing="4" border="0">
<tr>
<td valign="middle"><span class="subtitle">Search</span></td>
<td valign="middle"><input id="search-box" name="searchbox" value="" size="60" class="textbox topsearch" type="text" /></td>
<td valign="middle"><input id="search-box" name="dosearch" value="SEARCH" size="10" class="button-submit" type="submit" /></td>
</tr>
<tr>
<td></td>
<td colspan="2">
<span class="copysm" style="color: #ffffff">
<input checked type="radio" name="searchtype" value="company"></input><?= getSchoolStr( "School" ) ?>&nbsp;
<?php if( !($thisusersrow["healthdirector"]) ) {?>
<input type="radio" name="searchtype" value="responder"></input>Responder&nbsp;
<?php } ?>
<?php if(isOverallAdmin() ){ ?>
<input type="radio" name="searchtype" value="contact"></input>Contact&nbsp;
<input type="radio" name="searchtype" value="class"></input>Class&nbsp;
<input type="radio" name="searchtype" value="user"></input>Users (New)
<?php }?>
</span>
</td>
</tr>
</table>
</form>
</div><?php } ?>
<?php if( !($thisusersrow["id"]) ) { ?>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font style="font-size:16px"><b>212-564-6833</b></font>
<?php } 

//print_r( $_SERVER );
$uri = remove_url_query( $_SERVER["REQUEST_URI"], "setcorp" );
if( strpos( $uri, "?" ) !== false )
$uri .= "&";
else
$uri .= "?";
?>
</div></div><div id="nav-wrapper">
<?php if( ($thisusersrow["usertype"]) == "principal" && isOverallAdmin() && $session_userid ) { ?>
<div id="nav-container">
<ul class="nav-main">

<li class="corporate"><a href="https://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?><?= $uri ?>setcorp=1">CORPORATE</a></li>
<li class="training"><a href="https://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?><?= $uri ?>setcorp=3">TRAINING SITES</a></li>
<li class="doe"><a href="https://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?><?= $uri ?>setcorp=0">DOE</a></li>
<li class="dfta"><a href="https://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?><?= $uri ?>setcorp=4">AGING</a></li>
</ul>
</div><?php } ?>
<?php if( $session_userid ) { ?>
<div id="login" style="float: left; padding:15px 0 0 50px; font-size: 11px; color: #ffffff;">Logged in as <b><?= $thisusersrow["first_name"] ?> <?= $thisusersrow["last_name"] ?>

<?php if( ($thisusersrow["usertype"]) == "trainer" ) echo( " | ASHI ID: " . ($thisusersrow["ashiid"]) ); ?>
<?php if( ($thisusersrow["usertype"]) == "trainer" ) echo( " | AHA ID: " . ($thisusersrow["ahaid"]) ); ?>
<?php
$assigned = $thisusersrow["assignedtcfacultyid"];
$tmpnotok =db_query_first_cell( "select notavail from trainer_notavail where notavail >= '". date( "Y-m-d" ) . "' and enddate <= '". date( "Y-m-d" ) . "' and trainerid = '$assigned'" );
//echo( "select notavail from trainer_notavail where notavail >= '". date( "Y-m-d" ) . "' and enddate <= '". date( "Y-m-d" ) . "' and trainerid = '$assigned'" );
if( $tmpnotok && $assigned )
$assigned = 0;
if( ($thisusersrow["usertype"]) == "trainer" && $assigned ) echo( " | TC Faculty: <a href='mailto:" . getUserEmail( $assigned ) . "'><font color='white'>" . getUserName( $assigned ) . "</font></a>" ); ?>
</b>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://<?php echo SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN; ?>/login.php" style="color: #6ab3fb;">Log Out</a></div>
<?php } ?>
</div></div><div id="content" >
<?php include "currentnav.php"; ?>
<div id="main-content" style="margin-bottom: 0px; <?= $contentextrastyle ?>">
<div id="content-center<?= ($isdashboard) ? "" : "2" ?>" >