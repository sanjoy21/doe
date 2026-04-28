<?php 
$nologinrequired = true;
require_once('mysql.php');

$id_safe = $id ?? null;
$tcf_safe = $tcf ?? false; 

if( !$tcf_safe )
{
   
    db_query( "update trainer_to_class set trainerconfirmeddate = now(), trainerconfirmedby = 'email' where id = '" . $id_safe . "'" );
   
    $classid = db_query_first_cell( "select classid from trainer_to_class where id = " . (int)$id_safe );
}
else
{
    
    db_query( "update class set tcfacultyconfirmeddate = now() where id = '" . $id_safe . "'" ); 
    $classid = $id_safe; 
}

if( $classid )
{
    $crow = getClassRow( $classid );
}

$noleftnav = true;
include "ssi/top.php";
?>
<span class='copy'> Thanks! You have successfully confirmed yourself for training on <?= fixdatefordisplay( $crow["startdate"] ?? '', true ) ?>.

<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>

<?php include "ssi/footer.php";?>
</span>
</td>
<td valign="top" width="15"><img src="<?= WEB_ROOT ?? '' ?>/images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>

</body>
</html>