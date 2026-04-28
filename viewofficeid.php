<?php
include "mysql.php";

$row = getCompanyRow($id ?? null);
?>
<?php include "ssi/top.php"; ?>

<?php if ($specialadmin ?? false) { ?>
	<table cellpadding="5" cellspacing="1" border="0" width="100%">
		<tr>
			<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="viewcompany.php?id=<?= $id ?? '' ?>">&laquo; Back to Admin Main</a></strong></span></td>
		</tr>
	</table>
<?php } ?>
<br>
<table cellpadding="8" cellspacing="1" border="0" width="100%">
	<tr>
		<td valign="top" bgcolor="#5a179e" colspan="2">
			<table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td><span class="white"><strong>Trained Responders</strong></span></td>
					<td valign="top" bgcolor="#5a179e" align="right">
						<?php if (!($readonly ?? false) && ($specialadmin ?? false)) { ?>
							<a href="editresponder.php?id=<?= $row['id'] ?? '' ?>&redirect=<?= urlencode(($_SERVER['PHP_SELF'] ?? '') . '?id=' . ($row['id'] ?? '')) ?>"><span class="white">[Add Responder]</span></a>
						<?php } ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td valign="top" bgcolor="#E2DFDF">
			<span class="copy">The following persons are responders at<br><br><strong><?= ($row['companyname'] ?? '') . "<br>" . ($row['address'] ?? '') . "<br>" . ($row['floor'] ?? '') . "<br>" . ($row['city'] ?? '') . ", " . ($row['state'] ?? '') . " " . ($row['zip'] ?? ''); ?></strong>
				<br><br>Click on a name to view responder details:</span><br>
			<?php $pagename = "viewofficeid.php"; ?>

			<?php include "viewresponders.php"; ?>
		</td>
	</tr>
</table>
<br>
<br>
<?php include "ssi/footer.php"; ?>
</td>
</tr>
</table>
<br><br>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>

</body>

</html>