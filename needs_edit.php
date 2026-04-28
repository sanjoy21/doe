<?php
$donewui = true;
$nologinrequired = true;
require_once "mysql.php";

// Safely retrieve assumed global/external variables
$id = $_REQUEST['id'] ?? null;
$mobile_browser = $mobile_browser ?? false; // Default to false if not defined

// Ensure $id is safe before database calls
$id_safe = (int)$id;

// Fetch class and company rows
$crow = getClassRow($id_safe);
$comrow = getCompanyRow($crow['companyid'] ?? 0);


/*
if( $comrow['iscorp'] ?? false ) {
    mail( "barbara@emergencyskills.com", "Course detail change for class $id_safe", "Recipient indicated that the details of this course were incorrect.", "From: info@emergencyskills.com" );
} else {
    mail( "rebekah@emergencyskills.com", "Course detail change for class $id_safe", "Recipient indicated that the details of this course were incorrect.", "From: info@emergencyskills.com" );
}
mail( "rcox@vireo.org", "Course detail change for class $id_safe", "Recipient indicated that the details of this class ($id_safe) were incorrect.", "From: info@emergencyskills.com" );
*/

include "ssi/top.php";
?>

<td valign="top" width="5"><img src="<?php echo htmlspecialchars(WEB_ROOT ?? ''); ?>/images/dotclear.gif" width="10" alt=""></td>
 <td valign="top" width="476"><br>

	<h1 style="color: #C00;">Your course is NOT yet confirmed.</h1>
	<br>
	You indicated that you need to change the details of your program. <br><br>Please contact
	<?php
	$iscorp_status = (int)($comrow['iscorp'] ?? 0);

	if ($iscorp_status === AGING) {
	?>
		<strong>Sarah Gillen</strong> at <a href="mailto:sarahg@emergencyskills.com">sarahg@emergencyskills.com</a>
	<?php
	} else if ($iscorp_status) {
	?>
		<strong>Barbara Kinter</strong> at <a href="mailto:barbara@emergencyskills.com">barbara@emergencyskills.com</a>
	<?php
	} else {
	?>
		<strong>Rebekah Carrow</strong> at <a href="mailto:rebekah@emergencyskills.com">rebekah@emergencyskills.com</a>
	<?php
	}
	?>
	or <strong>212-564-6833</strong> to change your
	program details. <br /><br />

	Thank you,<br /><br />

	Emergency Skills, Inc.

	<?php if (!$mobile_browser) {
		include "ssi/footer.php";
	} ?>
	</span>
</td>
<td valign="top" width="15"><img src="<?php echo htmlspecialchars(WEB_ROOT ?? ''); ?>/images/dotclear.gif" width="10" alt=""></td>
</tr>
</table>
<br><br>
</div>
<script type="text/javascript" src="webticker_lib.js" language="javascript"></script>

</body>

</html>