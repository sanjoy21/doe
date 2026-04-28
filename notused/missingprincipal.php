<?
include "mysql.php";
$sql =  ( "select id, companyname, schoolcode, campusid  from company_esi c  where iscorp = '$session_iscorp' and c.deleted = 0 and ( principalemail is null or principalemail = '' )  order by companyname" );
$result = mysql_query( $sql ) or die( mysql_error() . $sql );

echo( "<table>" );
while($row = mysql_fetch_array( $result ) ) 
{
echo ("<tr><td><a  target=_blank href='editcompany.php?id=$row[id]'>$row[schoolcode]</a></td><td>$row[companyname]</td></tr>" );
}
echo( "</table>" );

?>
