<?php

$nologinrequired = true;
$donewui = true;

require_once "mysql.php";

// Replaced deprecated session_unregister() with unset()
unset($_SESSION['userid']);
unset($_SESSION['password']);
unset($_SESSION['session_userid']);
unset($_SESSION['session_iscorp']);
unset($_SESSION['session_id']);
unset($_SESSION['session_password']);
unset($_SESSION['session_usertype']);

$currentusertype = "";
$session_userid = "";
$session_iscorp = 0;
$session_usertype = "";

if( ($_SERVER["SERVER_NAME"]) == "dfta." . URL_WITHOUT_SUBDOMAIN  )
	$session_iscorp = DFTA; // Use constant DFTA (4)

if( !$mobile_browser )
    include "ssi/top.php";
//sendMail( "rachelc@gmail.com", "CPR/AED", "body", "rachel@emergencyskills.com" );
?>

                  <p class="Titles"><strong><br>
                  ALIVE!net (new)</strong></p>


		      
 <table width="100%" border="0" cellspacing="0" cellpadding="4" class="table3">
<?php if( !$session_iscorp || $session_iscorp == AGING ){ ?>
                    <tr>
                      <td width="36%"><div align="right">
                        <p class="TitlesCopy">INDIVIDUAL REGISTRATION:</p>
                        </div></td>
                      <td width="64%"><a href="individual_registration1.php"><img src="images/click_03.gif" width="114" height="24" border="0"></a></td>
                    </tr>
                    <tr>
                      <td><div align="right" class="TitlesCopy">NEW USER? SIGN UP:</div></td>
                      <td><a href="create_profile.php"><img src="images/click_03.gif" width="114" height="24" border="0"></a></td>
                    </tr>
                    
<?php } ?>
<?php if( !$session_iscorp ){ ?>
                    <tr>
                      <td><div align="right" class="TitlesCopy">SCHOOL NURSES:</div></td>
                      <td><a href="nurses.php"><img src="images/click_03.gif" width="114" height="24" border="0"></a></td>
                    </tr>
<?php } ?>
                    <tr>
                      <td><div align="right" class="TitlesCopy">COVID AND CPR INFO:</div></td>
                      <td><a href="covidinfo.php"><img src="images/click_03.gif" width="114" height="24" border="0"></a></td>
                    </tr>
		    <tr><td>&nbsp;</td></tr>
		    <?php if( $session_iscorp != AGING ) { ?>
                    <tr>
                      <td><div align="right" class="TitlesCopy">To find your eCard and eBook: </div></td>
                      <td><a href="findecard.php"><img src="images/click_03.gif" width="114" height="24" border="0"></a></td>
                    </tr>
		    <?php } ?>
                    <tr>
                      <td colspan="2">
<?php $homemess = getsetting( "homemess" ); if( $homemess ) {  ?>
<div align="left"><strong><br>
<br><br>			<div align='center'>
<b><?= $homemess ?>
</b>
<br><br></div>
<?php } ?>
    <br>
                        SCHEDULE CLASS AT YOUR <?=strtoupper( getSchoolStr( "School" ) )?><br>
                      OR VIEW <?=strtoupper( getSchoolStr( "School" ) )?> INFORMATION: </strong></div></td>
                    </tr>
                    <tr>
                      <td><div align="right" class="TitlesCopy"> PLEASE LOG IN:</div></td>
                      <td>&nbsp;</td>
                    </tr>
<form method="post">
                    <tr>
                      <td><div align="right" class="TitlesCopy2">E-MAIL:</div></td>
                      <td><input name="userid" type="text" id="userid" size="40"></td>
                    </tr>
                    <tr>
                      <td><div align="right" class="TitlesCopy2">PASSWORD:</div></td>
                      <td><input name="password" type="password" id="password" size="40"></td>
                    </tr>
                    <tr>
                      <td><div align="right"></div></td>
                      <td><input type="hidden" name="Submit" Value="true">
<input type='hidden' name='dologin' value='1'>		
                        <input name="imageField" type="image" src="images/go_06.gif" width="53" height="25" border="0"></td>
                    </tr>
</form>
                    <tr>
                      <td><div align="right"></div></td>
                      <td><p><strong><a href="password-reset-request.php"><b>Forgot Password?</b></a><br><br>
<?php if( !$session_iscorp ){ ?>(Note: Your email and password will only work if you have created a profile on ALIVEnet. This login is not connected to the DOE website.)</strong></p>
<?php } ?>
<?php if( !$session_iscorp || $session_iscorp == AGING ){ ?>
<br><p class="left2">&bull; <a href="general.php" class="bottom">FAQS &amp; GENERAL INFORMATION</a><br>
<?php } ?>
<?php if( !$session_iscorp ){ ?>
<br><a class='bottom' href='http://www.vireo.org/tmp/esi.apk'>Trainer App (Login required)</a><br><br>
<?php } ?>
</table>
<p>&nbsp;</p>
<?php if( !$mobile_browser )
include "ssi/footer.php";?>
</span>
</td>
</tr>
</table>
<br><br>
</div>
<script type="text/javascript" src="webticker_lib.js" language="javascript"></script>			
<script type='text/javascript'>
if (location.protocol != 'https:')
{
 location.href = 'https:' + window.location.href.substring(window.location.protocol.length);
 }
 </script>
</body>
</html>