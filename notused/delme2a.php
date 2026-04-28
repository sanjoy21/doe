<? 
include "mysql.php" ;

echo( "<table>" );
$classcards = scandir( "classcards" );
foreach( $classcards as $c )
{
	if( is_dir( "classcards/$c" ) ) continue;
	$row = db_query_first( " select * from class where id = '" . str_replace( ".pdf", "", $c ) . "'" );
	if( !$row["cardsmaileddate"]  )
	echo( "<Tr><td><a href='class_detail.php?id=$row[id]'>$row[id]</a></td><td>$row[startdate]</td></tr>" );
}
?>
</table>