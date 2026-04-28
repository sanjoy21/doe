<?php 
include "mysql.php";
$handle = fopen("/tmp/all.csv", "r");
$i = 0;

function string_split_safe($str, $nr){
    return explode("-l-", chunk_split($str, $nr, '-l-'));
}

$allschoolcodes= array();
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
$i++;
if( $i == 1 )
 continue;

 $two = string_split_safe( $data[1] ?? '', 1 ); 
$dis = array_shift( $two );

$schoolcode = str_pad( $data[0] ?? '', 2, "0", STR_PAD_LEFT ) . "-".$dis . "-" . join( $two );
$allschoolcodes[$schoolcode] = $schoolcode;
}
echo( count( $allschoolcodes ) . " total codes in spreadsheet<br>");


$res = db_query_rows( "select id, schoolcode, companyname from company_esi where deleted = 0", "id" );

$count = 0;
$mcount = 0;
foreach($res as $rid=>$row )
{
 if( !isset($allschoolcodes[$row["schoolcode"]]) )
 {
echo( "No match in spreadscheet for: " . ($row["schoolcode"] ?? 'N/A') . " <a href='viewcompany.php?id=$rid'>" . ($row["companyname"] ?? 'N/A') . "</a><br>");

$aed_rows = db_query_rows("select * from drill where companyid = '" . ($row['id'] ?? '') . "' or otherschools like '%,".($row['id'] ?? '').",%' and completed = 1 order by drilldate");
 if( count( $aed_rows ) )
 {
echo( "did a drill here!<br>" );
}

$aed_rows = db_query_rows("select * from servicecall where companyid = '" . ($row['id'] ?? '') . "' or otherschools like '%,".($row['id'] ?? '').",%' and completed = 1 order by servicecalldate");
if( count( $aed_rows ) )
{
echo( "did service call here!<br>" );
 }
$count++;
}
else $mcount++;
}
echo( "$count total not matching<br>" );
echo( "$mcount total matching" );
?>