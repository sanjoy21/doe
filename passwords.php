<?php
require_once('mysql.php');

if( isset($update) && $update )
{
    db_query( "delete from passwords " );
    if( isset($us) && is_array($us) ) {
        foreach( $us as $uid=>$u )
        {
            $val = isset($vals[$uid]) ? escMe( $vals[$uid] ) : '';
            $desc = isset($descs[$uid]) ? escMe( $descs[$uid] ) : '';
            if( !$desc ) continue;
            $u_safe = $u;
            db_query( "insert into passwords ( name, value, description ) values ( '$u_safe', '$val', '$desc' )" );
        }
    }
}

$us = db_query_rows( "select * from passwords order by name" );
?>
<?php include "ssi/top.php"; ?>
<p>

<strong><span class="title">MANAGE Passwords</span></strong>

<p>
<form method='post'>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999"  class="table3">
        <tr bgcolor="#e1e1f6"><th class='copy'>Agency/Website</th><th class='copy'>Username</th><th class='copy'>Password</th></tr>
<?php 
// Initialize $us as array if not set
if( !isset($us) || !is_array($us) ) {
    $us = array();
}

// Add empty rows for new entries
$us[] = array();
$us[] = array();
$us[] = array();

foreach( $us as $t )
{
    echo( "<tr bgcolor='white'><td  align='center' valign='top'><input size='50' type='text' name='descs[]' value='" . (isset($t["description"]) ? htmlspecialchars($t["description"]) : '') . "' ></td>\n" );
    echo( "<td class='copy' valign='top'><input type='text' name='us[]' value=\"" . (isset($t["name"]) ? htmlspecialchars($t["name"]) : '') . "\"></td>" );
    echo( "<td  align='center' valign='top'><input type='text' name='vals[]' value='" . (isset($t["value"]) ? htmlspecialchars($t["value"]) : '') . "' ></td></tr>\n" );
}
?>
</table><p>
<input type='submit' name='update' value='Update'><br><br><br>
</form>
<!--end center content-->

<?php include "ssi/footer.php" ; ?>

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