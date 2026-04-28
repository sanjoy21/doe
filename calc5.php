<?php 
include "mysql.php";

$xls_safe = $xls ?? false;

if( $xls_safe )
{
    Header( "Content-type: application/vnd.ms-excel" );
    header("Content-Transfer-Encoding: binary");

    $user_agent = strtolower ($_SERVER["HTTP_USER_AGENT"] ?? ''); 
    $filename = "classes.xls";
    header("Content-Disposition: attachment; filename=" . $filename);
}

$arr = array();
echo( "<table border=1><th>schoolcode</th><th>name</th><th>id</th><th>drills</th><th>servicecalls</th><th>classes</th><th>trainers</th>" ) ;

$rows = db_query_rows( "select * from company_esi where deleted = 0 and showsondrillreports = 1" );

foreach( $rows as $r )
{
    $key = $r["id"];
    $schoolcode_safe = $r["schoolcode"] ?? '';
    $companyname_safe = $r["companyname"] ?? '';
    $id_safe = $r["id"] ?? 0;
    $zip_safe = $r["zip"] ?? '';
    
    echo( "<tr><td>" . $schoolcode_safe . "</td><td>" . $companyname_safe . "</td><td>" . $id_safe . "</td>" );
    
    $numdrills = db_query_first_cell( "select count(*) from drill where ( companyid = " . (int)$id_safe . " or otherschools like '%," . (int)$id_safe . ",%' ) and completed = 1" );
    
    $numservicecalls = db_query_first_cell( "select count(*) from drill where ( companyid = " . (int)$id_safe . " or otherschools like '%," . (int)$id_safe . ",%' ) and completed = 1" );
    
    $numclasses = db_query_first_cell( "select count(*) from class where ( companyid = " . (int)$id_safe . " ) and canceldate is null and startdate > now()" );
    
    $trainers = "no zip";

    if( $zip_safe )
        $trainers = db_query_first_cell( "select group_concat( userid ) from user where visiblezips like '%" . $zip_safe . "%' " );

    echo( "<td>" . ($numdrills ?? 0) . "</td>" );
    echo( "<td>" . ($numservicecalls ?? 0) . "</td>" );
    echo( "<td>" . ($numclasses ?? 0) . "</td>" );
    echo( "<td>" . ($trainers ?? 'N/A') . "</td>" );
    echo( "</tr>" );
}
echo( "</table>" ) ;
?>