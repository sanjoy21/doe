<?php
// Enable error reporting for development (disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$nologinrequired = true;
require "mysql.php";

// Check if form was submitted or help was requested
if (isset($submit) || isset($help)) {
    if (isset($name) || isset($help)) {
        if (isset($name)) {
            
            // Escape input data - UPDATE THIS to use prepared statements
            $escaped_first_name = mysqli_real_escape_string($link, $first_name ?? '');
            $escaped_name = mysqli_real_escape_string($link, $name ?? '');
            $escaped_email = mysqli_real_escape_string($link, $email ?? '');
            $escaped_location = mysqli_real_escape_string($link, $location ?? '');
            $escaped_building = mysqli_real_escape_string($link, $building ?? '');
            $escaped_floor = mysqli_real_escape_string($link, $floor ?? '');
            
            $query = "INSERT INTO blackstonesignups (dateadded, ipaddress, first_name, name, email, location, building, floor) 
                      VALUES (NOW(), '" . mysqli_real_escape_string($link, $_SERVER['REMOTE_ADDR'] ?? '') . "', 
                      '$escaped_first_name', '$escaped_name', '$escaped_email', 
                      '$escaped_location', '$escaped_building', '$escaped_floor')";
            
            db_query($query);
        }
        
        $err = "<br><br><font style='font-size:15px !important'>Thank you for signing up for the Blackstone AED / CPR Training. The business day following your entry, you will receive an email from <a href='mailto:donotreply-heart.org@elearningemail.heart.org'>donotreply-heart.org@elearningemail.heart.org</a> with instructions to complete the virtual portion of the class. Once you complete the virtual portion of the course, you will be invited to schedule your hands on skills practice and testing. Successful skills testing is a requirement for the American Heart Association certification. 
<br><br>
<br><br>
If you have any questions, please email:
<br><br>

Peter Corrigan <a href='mailto:Peter.corrigan@blackstone.com'>Peter.corrigan@blackstone.com</a>
<br><br>
Barbara Kinter <a href='mailto:barbara@emergencyskills.com'>barbara@emergencyskills.com</a>
<br><br>

</font>";
    }
}

include "ssi/top.php";
?>
<script language='javascript'>
function checkSubmit()
{
if( document.forms[0].first_name.value == "" ) 
{
alert( "First name is required." );
return false;
}
if( document.forms[0].name.value == "" ) 
{
alert( "Last name is required." );
return false;
}
if( document.forms[0].email.value == "" ) 
{
alert( "Email is required." );
return false;
}
return true;
}
</script>
<form name="myform" id="myform" method="post" onSubmit='return checkSubmit()'>
<strong><span class="title"><h2>Welcome to the Blackstone AED / CPR Training</h2></span></strong>

<?php if (isset($err) && $err) { ?>
<?php echo $err; ?>
<?php } else { ?>
<BR><hr><Br>
<font style="font-size:18px">I am interested in completing the CPR/AED Training program:<br><br></font>
<table cellpadding="0" cellspacing="0" border="0" width="100%" id="tsiform">
        <!--row 1-->
<tr>
<td valign="top">
<table cellpadding="0" cellspacing="6" border="0">
                    <tr>
        <td valign="top"><span class="copy">First Name:</span><br>
<input required name="first_name" type="text" id="" value="<?php echo htmlspecialchars($first_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" size="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                    </tr>
                    <tr>
        <td valign="top"><span class="copy">Last Name:</span><br>
<input required name="name" type="text" id="" value="<?php echo htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8'); ?>" size="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                    </tr>
                    <tr>

<td valign="top"><span class="copy">
COMPLETE Email address (eg name.name@blackstone.com)<br>USE YOUR BLACKSTONE EMAIL IF YOU HAVE ONE
</span><br>
<input required name="email" type="text" id="" value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>" size="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<tr>
                    <tr>

<td valign="top"><span class="copy">Location: </span><br>
<select name='location' required>
<option value=''>Choose One</option>
<option <?php echo (($location ?? '') == '345 Park Avenue') ? 'selected' : ''; ?>>345 Park Avenue</option>
<option <?php echo (($location ?? '') == '601 Lexington Avenue') ? 'selected' : ''; ?>>601 Lexington Avenue</option>
<option <?php echo (($location ?? '') == 'Miami') ? 'selected' : ''; ?>>Miami</option>
<option <?php echo (($location ?? '') == 'San Francisco') ? 'selected' : ''; ?>>San Francisco</option>
<option <?php echo (($location ?? '') == 'Santa Monica, CA') ? 'selected' : ''; ?>>Santa Monica, CA</option>
<option <?php echo (($location ?? '') == 'Cambridge, MA') ? 'selected' : ''; ?>>Cambridge, MA</option>
<option <?php echo (($location ?? '') == 'Nashville, TN') ? 'selected' : ''; ?>>Nashville, TN</option>
<option <?php echo (($location ?? '') == 'Berkeley Heights, NJ') ? 'selected' : ''; ?>>Berkeley Heights, NJ</option>
<option <?php echo (($location ?? '') == 'Wayne, PA') ? 'selected' : ''; ?>>Wayne, PA</option>
<option <?php echo (($location ?? '') == 'Washington, DC') ? 'selected' : ''; ?>>Washington, DC</option>
<option <?php echo (($location ?? '') == 'Toronto, ON') ? 'selected' : ''; ?>>Toronto, ON</option>

</select>
</td>

</tr>
<tr>

                    </tr>
</tr>
           </table>
<!--end row 1-->

<!--<tr>
<td valign="top" class='copy' ><input type='checkbox' name='terms' value='1'> I agree to the ESI <a href='#' onClick='javascript:window.open( "terms.php", "_blank", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=600" )'>Terms and conditions</a>
</td>
           </tr>-->
<tr>
<td valign="top">
<input type='submit' name='submit' value='Sign Up' >
</td>
           </tr>
</table>
<?php } ?>
<br><br><br><br>

<!--end center content-->

                    <?php include "ssi/footer.php"; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>