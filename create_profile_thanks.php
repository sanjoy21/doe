<?php

//echo $salutation;exit;
require_once('mysql.php');
$nologinrequired = true;

$session_iscorp_safe = $session_iscorp;
$approved_safe = $thisusersrow["approved"];

// NOTE: AGING is assumed to be a defined constant.

if( $session_iscorp_safe == AGING ) {
    Header( "Location: index.php" );
    exit;
}

?>

<?php include "ssi/top.php"; ?>
<strong><span class="title">Thank you.</span></strong><p>
<?php if( !$approved_safe ) { ?> 
Your profile has been created. You will receive an activation email from ESI.
<?php } else { ?>
Your profile has been created. Please click on the link in your inbox to confirm your email address.
<?php } ?>
<?php if( $session_iscorp_safe == AGING ) { ?>

<?php } else { ?>
Please call ESI at 212-564-6833 if you do not receive the activation email.
<?php } ?>

<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>


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