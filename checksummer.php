<?php
include "mysql.php";

echo( "<table>" );
$handle = fopen("/tmp/summer.csv", "r");
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { 
    
    $bc = $data[0] ?? ''; 
    
    echo( "<tr><td>$bc</td>" );
 
    $res = db_query_array( "Select a.* from aed_esi a, company_esi c  
                            where clientid = c.id 
                            and showsondrillreports = 1 
                            and buildingcode = '$bc' 
                            and aedmissing = 0 
                            and a.deleted = 0 
                            and aedinactive = 0 
                            and aedstolen = 0 
                            and serial not like 'B%'", 
                            "aedid", "serial" );
    
    echo( "<td>" );
    
    foreach($res as $id=>$v )
    {
        echo( "<a href='editaed.php?aedid=$id'>$v</a>; " );
    }
    
    echo( "</td>" ); 
    echo( "</tr>" );
}
echo( "</table>" );
?>