<?php 
require_once('mysql.php');

// Safely initialize external variables
$tid = $tid ?? null;
$session_id = $session_id ?? null;

// Determine the target user ID (default to current session user ID)
if( !$tid )
{
    $tid = $session_id;
}

// Fetch user profile data
$safe_tid = (int)$tid; // Ensure ID is an integer for safe SQL injection
$row = db_query_first( "SELECT * FROM user WHERE id = '{$safe_tid}'" );

// Fetch boroughs this trainer can teach in
$safe_session_id = (int)$session_id; // Ensure ID is an integer for safe SQL injection
$boroughs = db_query_array( "SELECT borough FROM trainer_to_borough WHERE trainerid = '{$safe_session_id}'", "borough", "borough" );
?>

<?php include "ssi/top.php"; ?>

<strong><span class="title">MY PROFILE</span></strong>

<br><hr>
<br>

<table cellpadding="0" cellspacing="5" border="0">
<?php 
            // Helper function to safely output row data
            function safe_output($row, $key) {
                return htmlspecialchars($row[$key] ?? '');
            }
            ?>
            
<tr>
 <td valign="top"><span class="copy"><strong>Name:</strong></span></td>
<td valign="top"><span class="copy">
                    <?= safe_output($row, "salutation") ?> 
                    <?= safe_output($row, "first_name") ?> 
                    <?= safe_output($row, "mi") ?> 
                    <?= safe_output($row, "last_name") ?>
                </span></td>
</tr>

<tr>
<td valign="top"><span class="copy"><strong>Street Address:</strong></span></td>
<td valign="top"><span class="copy">
                    <?= safe_output($row, "address1") ?>, 
                    <?= safe_output($row, "address2") ?><br>
                    <?= safe_output($row, "city") ?>, 
                    <?= safe_output($row, "state") ?> 
                    <?= safe_output($row, "zip") ?>
                </span></td>
</tr>
<tr>
<td valign="top"><span class="copy"><strong>Phone Number:</strong></span></td>
<td valign="top"><span class="copy">
                    <?= safe_output($row, "phone") ?>, Ext. 
                    <?= safe_output($row, "phone_ext") ?>
                </span></td>
</tr>
<tr>
<td valign="top"><span class="copy"><strong>Cell Number:</strong></span></td>
<td valign="top"><span class="copy">
                    <?= safe_output($row, "cell") ?>
                </span></td>
</tr>
<tr>
<td valign="top"><span class="copy"><strong>Other Phone:</strong></span></td>
<td valign="top"><span class="copy">
                    <?= safe_output($row, "otherphone") ?> Ext. 
                    <?= safe_output($row, "otherphoneext") ?>
                </span></td>
</tr>
<tr>
<td valign="top"><span class="copy"><strong>Email Address:</strong></span></td>
<td valign="top"><span class="copy">
                    <?= safe_output($row, "userid") ?>
                </span></td>
</tr>
<tr><td><br></td></tr>
<tr>
<td valign="top" colspan="2"><span class="copy"><strong>Boroughs in which I can teach:</strong> <?= htmlspecialchars(join( ", ", $boroughs )) ?></span></td>
</tr>
</table>
<p><?php if( $session_id == $tid ) { ?>
<a href='trainer_profile.php'><img src="images/button_editprofile.gif" border="0"></a>
<?php } ?>
<br><br><br><br>


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