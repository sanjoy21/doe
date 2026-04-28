<?php 
$nologinrequired = true;
include "mysql.php";
include "services.php";

// 12/28 - > 2/1

$t = time();
$count_query = "SELECT 
                    COUNT(*) 
                FROM 
                    responders_esi r, 
                    responder_to_class cl, 
                    company_esi, 
                    class c 
                WHERE 
                    c.id = cl.classid 
                    AND r.responderid = cl.responderid 
                    AND c.startdate > '2022-12-28' 
                    AND r.lastupdatedate < '2022-12-31' 
                    AND company_esi.iscorp = 0 
                    AND company_esi.id = c.companyid";

$val = db_query_first_cell( $count_query );
$t_end = time();
echo( htmlspecialchars($val) . "<br>");
// echo( "Initial count query took: " . ($t_end - $t) . " seconds<br>" ); 
// exit; // Original code had an 'exit' commented out here.


$t = time();
$fetch_query = "SELECT 
                    r.* FROM 
                    responders_esi r, 
                    responder_to_class cl, 
                    company_esi, 
                    class c 
                WHERE 
                    c.id = cl.classid 
                    AND r.responderid = cl.responderid 
                    AND c.startdate > '2022-12-28' 
                    AND r.lastupdatedate < '2022-12-31' 
                    AND company_esi.iscorp = 0 
                    AND company_esi.id = c.companyid 
                LIMIT 300";

$val = db_query_rows( $fetch_query );

foreach( $val as $row )
{
    echo( "updating one<Br>" );
    // The $row array contains all columns of the responder (r.*) needed by updateResponder()
    updateResponder( $row );
}


$val = db_query_first_cell( $count_query ); // Re-run the count query
$t_end_final = time();
echo( htmlspecialchars($val) );
exit;

// echo( "updating 20 took: " . (time() - $t ) );
?>