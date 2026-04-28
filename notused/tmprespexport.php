<? 
include "mysql.php";
require_once "services.php";
require_once "Spreadsheet/Excel/Writer.php";

$xls = new Spreadsheet_Excel_Writer();
$filename = "responderexport.xls";
$sheet =& $xls->addWorksheet("Report");
$xls->send( $filename );
     $rownum = 0;
     $colnum = 0;

$handle = fopen("/tmp/responders2.csv", "r");
$rowcnt= 0;
$stob = array();
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    $d0 = $data[0];
    if( !$stob[$data[0]] )
    {
        $strob[$data[0]] = array();
        
    }
    $stob[$data[0]][] = $data[1];
}

    $sheet->write( $rownum, $colnum++, "School" );
    $sheet->write( $rownum, $colnum++, "Code" );
    $sheet->write( $rownum, $colnum++, "Address" );
    $sheet->write( $rownum, $colnum++, "Responder Name" );
    $sheet->write( $rownum, $colnum++, getSchoolStr( "PMS ID" ) );
    $sheet->write( $rownum, $colnum++, "Responder Building Code" );
    $sheet->write( $rownum, $colnum++, "Responder Validation Result" );

foreach($stob as $bcode=>$bcodes ){
    $crow = db_query_first(" select * from company_esi where schoolcode = '$bcode' and deleted = 0 " );
    if( $crow[id] )
    {
        $aeds = db_query_rows( "select * from responders_esi where deleted = 0 and clientid = $crow[id]" );
        $cnt = 0;
        foreach( $aeds as $arow )
        {
            $aeddate = db_query_first_cell( "select max( trainingdate ) from responder_training_dates where responderid = $arow[responderid] and program in ( 'aed', 'dd', 'reg', 'Non ESI' )" );
            $aeddate = strtotime( $aeddate );
            $aeddate = mktime( 0,0,0,date( "m", $aeddate ),date( "d", $aeddate ),date( "Y", $aeddate )+2 );
            if( $aeddate < time() )
                continue;
            $cnt++;
        }
        if( $morethan && $cnt < 2 )
            continue;
        if( !$morethan && $cnt >= 2 )
            continue;

        $rownum++;
        $colnum = 0;
        $sheet->write( $rownum, $colnum++, $crow[companyname] );
        $sheet->write( $rownum, $colnum++, $crow[locationcode] );
        $sheet->write( $rownum, $colnum++, $crow[address] );
        $savecol = $colnum;
            
        foreach( $aeds as $arow )
        {
            $colnum = $savecol;
            $aeddate = db_query_first_cell( "select max( trainingdate ) from responder_training_dates where responderid = $arow[responderid] and program in ( 'aed', 'dd', 'reg', 'Non ESI' )" );
            $aeddate = strtotime( $aeddate );
            $aeddate = mktime( 0,0,0,date( "m", $aeddate ),date( "d", $aeddate ),date( "Y", $aeddate )+2 );
            if( $aeddate < time() )
                continue;

            $sheet->write( $rownum, $colnum++, $arow[firstname] . " ". $arow[lastname] );
            $sheet->write( $rownum, $colnum++, $arow[pmsid] );
            $sheet->write( $rownum, $colnum++, $arow[buildingcode] );
            $sheet->write( $rownum, $colnum++, $arow[lastupdateresult] );
            $rownum++;
        }
    }
}
    $xls->close();
    
    exit;

?>