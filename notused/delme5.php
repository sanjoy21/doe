<? 
include "mysql.php" ;

$dt = "2017-09-05"; 
$dt2 = "2018-05-01"; 

require_once "Spreadsheet/Excel/Writer.php";
$xls = new Spreadsheet_Excel_Writer( "fordoe.xls" );
$sheet =& $xls->addWorksheet("Current Responders");

$colnum = 0; $rownum = 0;
$sheet->write( $rownum, $colnum++, "Name" );
$sheet->write( $rownum, $colnum++, "PMS ID" );
$sheet->write( $rownum, $colnum++, "Address" );
$sheet->write( $rownum, $colnum++, "DBN" );
$sheet->write( $rownum, $colnum++, "Building Code" );
$sheet->write( $rownum, $colnum++, "Most Recent Training Date" );

$addys = array();
function getBuildingCodeAddress( $code, $companyid )
{
    global $addys;
	if( $code && isset( $addys[$code] ) )
	    return $addys[$code];
	if( !$code && $companyid && isset( $addys["c_{$companyid}"] ) )
	    return $addys["c_{$companyid}"];
	
	if( $code )
	    {
		$brow = mysql_fetch_array( mysql_query ( "select * from buildings where buildingcode = '$code' order by address desc limit 1" ) ) ;
		if( $brow[address] ) 
		    $ad = $brow[address] . ", " . $brow[city] . ", " . $brow[zip];
		else
		    {
			$brow = mysql_fetch_array( mysql_query ( "select * from company_esi where id = $companyid" ) ) ;
			if( $brow[address] ) 
			    $ad = $brow[address] . ", " . $brow[city] . ", " . $brow[zip];
		    }
	    }
	else
	    {
		$brow = mysql_fetch_array( mysql_query ( "select * from company_esi where id = $companyid" ) ) ;
		if( $brow[address] ) 
		    $ad = $brow[address] . ", " . $brow[city] . ", " . $brow[zip];
		
	    }
	if( $code )
	    $addys[$code] = $ad;
	else
	    $addys["c_{$companyid}"] = $ad;
	    
	return $ad;
}
$existing = array();
$alreadydone = array();

$rows = mysql_query( "select concat( r.firstname, ' ', r.lastname ) as name, pmsid, r.responderid, rtd.trainingdate, buildingcode, c.id as companyid, c.schoolcode from responders_esi r, responder_training_dates rtd, company_esi c where c.iscorp = 0 and r.responderid = rtd.responderid and r.clientid = c.id and rtd.trainingdate >= '$dt2' order by rtd.trainingdate desc" ) ;
while( $r = mysql_fetch_array( $rows ) )
{
    if( $alreadydone[$r[responderid]] ) continue;
    $colnum = 0; $rownum++;
    $sheet->write( $rownum, $colnum++, trim( $r[name] ) );
    $sheet->write( $rownum, $colnum++, "$r[pmsid]" );
    $sheet->write( $rownum, $colnum++, getBuildingCodeAddress( $r[buildingcode], $r[companyid] ) );
    $sheet->write( $rownum, $colnum++, "$r[schoolcode]" );
    $sheet->write( $rownum, $colnum++, "$r[buildingcode]" );
    $sheet->write( $rownum, $colnum++, "$r[trainingdate]" );
    $existing[$r[responderid]] = $r[responderid];
    $alreadydone[$r[responderid]] = $r[responderid];
}


$sheet =& $xls->addWorksheet("Expired Responders");



$rows = mysql_query( "select concat( r.firstname, ' ', r.lastname ) as name, pmsid, r.responderid, rtd.trainingdate, buildingcode, c.id as companyid, c.schoolcode from responders_esi r, responder_training_dates rtd, company_esi c where c.iscorp = 0 and r.responderid = rtd.responderid and r.clientid = c.id and rtd.trainingdate >= '$dt' and rtd.trainingdate < '$dt2' order by rtd.trainingdate desc" ) ;

$alreadydone = array();

$colnum = 0; $rownum = 0;
$sheet->write( $rownum, $colnum++, "Name" );
$sheet->write( $rownum, $colnum++, "PMS ID" );
$sheet->write( $rownum, $colnum++, "Address" );
$sheet->write( $rownum, $colnum++, "DBN" );
$sheet->write( $rownum, $colnum++, "Building Code" );
$sheet->write( $rownum, $colnum++, "Most Recent Training Date" );

while( $r = mysql_fetch_array( $rows ) )
{
    if( $existing[$r[responderid]] ) continue;
    if( $alreadydone[$r[responderid]] ) continue;
    $colnum = 0; $rownum++;
    $sheet->write( $rownum, $colnum++, trim( $r[name] ) );
    $sheet->write( $rownum, $colnum++, "$r[pmsid]" );
    $sheet->write( $rownum, $colnum++, getBuildingCodeAddress( $r[buildingcode], $r[companyid] ) );
    $sheet->write( $rownum, $colnum++, "$r[schoolcode]" );
    $sheet->write( $rownum, $colnum++, "$r[buildingcode]" );
    $sheet->write( $rownum, $colnum++, "$r[trainingdate]" );
    $alreadydone[$r[responderid]] = $r[responderid];
}

$xls->close();



?>
