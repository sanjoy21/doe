<?php 
include "mysql.php";
?>
<?php include "ssi/top.php"; ?>
<h3> Schools with no trainer</h3>
<table>
<tr><th>School</th><th>Zip</th></tr>
<?php 

$res = db_query_rows( "SELECT zip, id, schoolcode, companyname FROM company_esi WHERE deleted = 0 AND iscorp = 0 ORDER BY zip", "id" );
$already = array();

foreach( $res as $row )
{
    $zip_code = $row['zip'];

    if( isset( $already[$zip_code] ) ) {
        $r = $already[$zip_code];
    } else {

        $r = getTrainersForZip( $zip_code );
    }
    
    $already[$zip_code] = $r;
    
    if( !$r || !$zip_code )
    {
        $company_id = htmlspecialchars($row['id']);
        $company_name = htmlspecialchars($row['companyname']);
        $zip_output = htmlspecialchars($zip_code);

        echo( "<tr>
                  <td><a href='viewcompany.php?id={$company_id}'>{$company_name}</a></td>
                  <td>{$zip_output}</td>
              </tr>" );
    }
}
?>
</table>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
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