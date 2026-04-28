<?php 
include "mysql.php";
?>
<?php include "ssi/top.php"; ?>
<h3>Old School Codes</h3>
<table border="1" cellpadding="2" cellspacing="0">
<tr><th>School</th><th>Old School Code</th><th>Date Moved</th><th>Moved By</th></tr>
<?php 

$res = db_query_rows( "select * from oldschoolcodes order by companyid, movedate" );

foreach( $res as $row )
{
    $company_id = $row['companyid'] ?? null;
    $crow = getCompanyRow( $company_id );
    
    if( $crow['iscorp'] ?? false ) continue;

    $company_name = htmlspecialchars($crow['companyname'] ?? 'N/A');
    $school_code = htmlspecialchars($row['schoolcode'] ?? 'N/A');
    $move_date = htmlspecialchars($row['movedate'] ?? 'N/A');
    $who_moved = htmlspecialchars($row['whomoved'] ?? 'N/A');
    $safe_id = htmlspecialchars($company_id);
    
    echo( "<tr><td><a href='viewcompany.php?id=" . $safe_id . "'><nobr>" . $company_name . "</nobr></a></td><td>" . $school_code . "</td><td><nobr>" . $move_date . "</nobr></td><td>" . $who_moved . "</td></tr>" );
}


?>
</table>

<div style="height: 100px;"></div>

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