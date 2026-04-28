<?php 
require_once('mysql.php');

$deleted = 1;

$session_iscorp_safe = $session_iscorp ?? 0;

$sql = "Select * from company_esi where iscorp = '" . $session_iscorp_safe . "' and ( deleted = 1 or retired = 1 )  order by borough, region, companyname";
$rep = db_query_rows( $sql );

if( !($onscreen ?? false) )
{    
    include "schoolreportxls.php";
}
else
{    
    include "schoolreport.php";
}
        
?>