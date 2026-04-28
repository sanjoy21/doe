<?php
require_once('mysql.php');
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">Thank you.</span></strong><p>
Your class request has been received.  You will receive an email confirmation from ESI when your class has been scheduled.

<?php if( isOverallAdmin() && isset($ins) && $ins ) { ?>
<A href='class_detail.php?id=<?php echo intval($ins); ?>'>View Class</a>
<?php } ?>

<br><br>

<?php if( (!isset($session_iscorp) || !$session_iscorp) ) { ?>
<font color='red'><b>
***Please Note: A Code Blue Drill will be performed at your school as part
of the training program. Please accommodate the instructor appropriately.
</b></font>
<br><br><br><br>

NOTE - CLASSES WITH FEWER THAN 10 PARTICIPANTS: If your class has fewer than
10 participants it will be posted for open registration. New names will
appear on this site, please return to add your own names, remove names of
individuals who have cancelled, to view the new additions, and to print a
roster the day of the program. If this class does not meet the minimum of 7
participants 5 business days prior to the program it may be cancelled. 
<?php } ?>

<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<!--end center content-->

<?php include "ssi/footer.php"; ?>
<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>