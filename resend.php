<?php 

include "mysql.php";
include "services.php";

$sql = "SELECT * FROM responders_esi 
        WHERE lastupdateresult = 'Success' 
        AND lastupdatedate IS NULL 
        AND pmsid IS NOT NULL";

$rows = db_query_rows($sql);

foreach ($rows as $r) {
    updateResponder($r);
}

?>