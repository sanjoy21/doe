<?php include "ssi/top.php"; ?>

<?php
$thisusersrow = $thisusersrow ?? [];
 if( !($thisusersrow["approved"]) ) { ?>
Please wait for approval from the Emergency Skills administrators.<Br>
<?php } ?>

<?php if (($thisusersrow["approved"] ?? 0) < 0) { ?>
Sorry, your account has not been approved. You must use your official DOE email address to create a profile.<Br>
<a href='https://<?php echo SUB_DOE . ".". URL_WITHOUT_SUBDOMAIN; ?>/login.php'>Click here to create a new account.</a>
<?php } ?>

<?php if (!($thisusersrow["emailconfirmed"])) { ?>
Please confirm your email address.
<br><br>
When you created your ALIVE!net profile an email was sent to you with a link
to confirm your email address. Our records indicate that this link has not
been clicked. If you need another copy of the confirmation email, please
<a href='home.php?reconfirm=1'>click here</a>, and follow the instructions in the email. If you have additional
questions, please call Emergency Skills, Inc. at 212-564-6833.
<?php } ?>

<br><br><br><br> <br><br><br><br> <br><br><br><br> <br><br><br><br> <br><br><br><br> <br><br><br><br>
<?php include "ssi/footer.php" ; ?>

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>