<?php
include "mysql.php";

// This query finds non-corporate companies that share a ZIP code with user ID 11457
$sql = "select c.id, c.zip, c.companyname from company_esi c 
        where zip in ( select zip from user_to_zip where userid = 11457 ) 
        and deleted = 0 and iscorp = 0 and showsondrillreports = 1";

$r = db_query_rows( $sql );

echo( "<table border=1>" );
echo( "<tr><th>ID</th><th>ZIP</th><th>Company Name</th></tr>" ); 

foreach( $r as $row )
{
    // PHP 8.2 Compliance and Security: Quoting array keys and using htmlspecialchars()
    $id = htmlspecialchars($row['id'] ?? '');
    $zip = htmlspecialchars($row['zip'] ?? '');
    $companyname = htmlspecialchars($row['companyname'] ?? '');

    echo( "<tr>
              <td>" . $id . "</td>
              <td>" . $zip . "</td>
              <td>" . $companyname . "</td>
          </tr>" );
}
echo( "</table>" );
?>