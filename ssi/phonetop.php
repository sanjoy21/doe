<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
    <title>ALIVE!net :: A Product of Emergency Skills, Inc.</title>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=yes,width=350" >
<meta name="viewport" content="width=device-width; height=device-height;">
    <link rel="stylesheet" href="newcss/phonestyle.css?<?php echo date('l jS \of F Y h:i:s A'); ?>">
<script language="JavaScript" src="calendar2.js"></script><link href="newcss/calendar.css" rel="stylesheet" type="text/css">
<script src="//code.jquery.com/jquery-1.9.1.js"></script>
      <script src="//code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/black-tie/jquery-ui.css" type="text/css" />
      <script>
    $().ready(function() {
                $(".topsearch").autocomplete({
                      source: function ( request, response ) {
                              var radioval = $('input[name=searchtype]:checked', '#searchform').val();
                              var searchboxval = $('input[name=searchbox]', '#searchform').val();
                              // PHP 8.2 Conversion: Use null coalescing for variables inside the string
                              url = "/livesearch.php?corp=<?= $session_iscorp ?>&id=<?= $session_id ?>&q="+searchboxval + "&type="+radioval;
//                              alert( url );
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
            });
</script>
</head>

<STYLE TYPE="text/css">
</STYLE>

<body id="p-<?php if( $session_iscorp == 1 ) { ?>corporate<?php } else if( $session_iscorp == 2 ) { ?>prospects<?php } else { ?>doe<?php } ?>">

<div id="header-wrapper">
<div id="header-container">
    
    <div id="logo">
        <a href='http://www.<?php echo URL_WITHOUT_SUBDOMAIN; ?>'><img src="newimages/Alivenet-Logo.gif" width='300' border=0></a>
    </div>
<?php if( isOverallAdmin() || ($thisusersrow["companyid"]) == "0" ) { ?> 
    <div id="search-box">
<form method='post' id="searchform" onSubmit="return dosearchsubmit( this )">
<input type='hidden' name='hidden1'> 
<input type='hidden' name='hidden2'> 
        <table cellpadding="4" cellspacing="4" border="0">
            <tr>
                <td valign="middle"><span class="subtitle">Search</span></td>
                <td valign="middle"><input id="search-box" name="searchbox" value="" size="20" class="textbox topsearch" type="text" /></td>
                <td valign="middle"><input id="search-box" name="dosearch" value="SEARCH" size="10" class="button-submit" type="submit" /></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                <span class="copysm">
                <input checked type="radio" name="searchtype" value="company"></input><?= getSchoolStr( "School" ) ?>&nbsp;
                <input type="radio" name="searchtype" value="responder"></input>Responder&nbsp;
<?php if(isOverallAdmin() ){ ?>
                <input type="radio" name="searchtype" value="contact"></input>Contact&nbsp;
                <input type="radio" name="searchtype" value="class"></input>Class&nbsp;
                <input type="radio" name="searchtype" value="user"></input>User
<?php } ?>
                </span>
                </td>
            </tr>
        </table>
</form>
    </div><?php } ?>
</div></div><?php if( ($thisusersrow["usertype"]) == "principal" && isOverallAdmin()) { ?>
    <ul class="nav-main">
        <li class="corporate"><a href="https://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/home.php?setcorp=1">CORPORATE</a></li>
        <li class="prospects"><a href="https://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/home.php?setcorp=2">PROSPECTS</a></li>
        <li class="doe"><a href="https://<?= getUrlPrefix( 0 ) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/home.php?setcorp=0">DOE</a></li>


    </ul>
<?php } ?>
<?php if( $session_userid ) { ?> 
<Br><br>
<div class='search-box' id="login" style="padding-left: 10px; font-size: 11px; color: #000000;"><b><?= ($thisusersrow["first_name"]) ?> <?= ($thisusersrow["last_name"]) ?></b><br><a href="http://<?php echo SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN; ?>/login.php" style="color: #6ab3fb;">Log Out</a></div>
<?php } ?>
</div><div id="content">


    <div id="main-content">
        <div id="content-center<?= ($isdashboard) ? "" : "2" ?>" style="">