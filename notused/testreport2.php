<?
include "mysql.php";


//$ext = " and id = 2150";
    $sql =  ( "select distinct( c.id ) as id, c.companyname  from company_esi c,aed_esi where iscorp = '1' and c.deleted = 0 and (clientid = c.id and directorname like '%dore Lebeau%')  group by c.id, c.companyname order by companyname" );
//echo( $sql );
$result = mysql_query( $sql ) or die( mysql_error() . $sql );

if(  $xls )
{
require_once "Spreadsheet/Excel/Writer.php";
$xls = new Spreadsheet_Excel_Writer( );
$sheet =& $xls->addWorksheet("Report");
$xls->send( $filename );
     $rownum = 0;
     $colnum = 0;
$sheet->write( $rownum, $colnum++, "Company" );
}
else
{
    echo( "<Table>" );
}
$allwith = db_query_array( "select distinct( companyid ) as companyid from class where accepted = 1 and deleted = 0  and code in ( 'hsaed','chld', 'AEDBBP', 'hsdapbbp', 'hsaedO2','hsaedalive', 'hsaedapalive', 'hsaedfa', 'hsfaaedaci','hsfacpraed' ) ", "companyid", "companyid" );
//echo( "all:".$allwith[11884] );
while($row = mysql_fetch_array( $result ) ) 
{
	if( !$row[companyname] )
        continue;
//    $sql = ( " select count(*) from class where accepted = 1 and deleted = 0 and companyid = '$row[id]' and code in ( 'hsaed','chld', 'AEDBBP', 'hsdapbbp', 'hsaedO2','hsaedalive', 'hsaedapalive', 'hsaedfa', 'hsfaaedaci','hsfacpraed' ) " );
//     $num = db_query_first_cell( $sql );
//     if( $num )
//        continue;
    if( $allwith[$row[id]] )
        continue;

    $rownum++;
    $colnum = 0;
    if( $xls )
    {
        $sheet->write( $rownum, $colnum++, $row["companyname"] );
//        $sheet->write( $rownum, $colnum++, $num );
    }
    else
    {
        echo( "<tr><td>$rownum. <A href='classhistory.php?id=$row[id]'>$row[companyname]</a></td></tr>" );
    }
}
if( $xls )
    $xls->close();
else
    echo( "</table>" );

?>
