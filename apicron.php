<?php 
$nologinrequired = true;
require_once('mysql.php');

$res = db_query_rows( "select * from appuploads where archived in ( 1,0) and dateinupload > '" . date( "Y-m-d", strtotime( "3 days ago" ) ) . "' order by id" );

foreach( $res as $r )
{

    db_query( "update appuploads set archived = -2 
               where dateinupload = '" . ($r["dateinupload"] ?? '') . "' 
               and id > " . (int)($r["id"] ?? 0) . " 
               and schoolid = '" . ($r["schoolid"] ?? '') . "' 
               and type = '" . ($r["type"] ?? '') . "'" );
}

$tofix = db_query_rows( "select appuploads.id, userid, esi_repname, uploader, dateinupload 
                       from appuploads, user 
                       where uploader = '' 
                         and esi_repname > '' 
                         and esi_repname = concat( first_name, ' ', last_name ) 
                         and usertype = 'trainer'" );

foreach( $tofix as $t )
{
    db_query( "update appuploads set uploader = '" . ($t["userid"] ?? '') . "' where id = '" . ($t["id"] ?? '') . "'" );
}

?>