<?php 
$nologinrequired = true;
include "mysql.php";

?>

<?php 
include "ssi/top.php";
?>

<br>
<span class='copy'>
<b>Check your own AED (Monthly AED Maintenance Checklist)</b><br>

<?php if( $session_iscorp == AGING ){ ?>
<a href='pdfs/dfta-InspectionForm.pdf'><img src='images/dfta-AED.jpg'></a>
<?php } else { ?>
<a href='pdfs/fr2.pdf'><img src='fr.jpg'></a>
<a href='pdfs/frx.pdf'><img src='fr2.jpg'></a>
<?php } ?>
<Br><Br>
<?php if( !$session_iscorp ) { ?>
<a href='http://www.emergencyskills.com/demo2.html'><b><font size='+1'>New AED Model FRx Demo</font></b></a><br><br>
<a href='http://schools.nyc.gov/NR/rdonlyres/19F5557C-A2BC-4E92-BA62-D12A2447FCBE/0/AEDProgramChecklist.pdf'>AED Program Checklist</a><br><br>
<?php } ?>
<a href='individual_registration1.php'>Individual Registration</a><br><br>

<a href='login.php'>Schedule a Class At Your School</b></a><br><br>

<a href='login.php'>View School Information</a><br><br>

<a href='pdfs/Replacement_Procedure_for_Stolen_AED_units.pdf'>Is Your AED Missing? Click here.</a><br><br>

<?php if( !$session_iscorp ) { ?>
<a href='pdfs/eCardsStdntHndtClaimingeCard.pdf'>How to Claim Your eCard</a><br><br>
<a href='http://www.psal.org'><img src='images/psalleft.gif' border='0'></a>
<?php } ?>
 <?php include "ssi/footer.php";?>
</span>
</td>
 <td valign="top" width="15"><img src="<?=WEB_ROOT?>/images/dotclear.gif" width="10"></td>
</tr>
</table>

<br><br>
</div>

</body>
</html>s