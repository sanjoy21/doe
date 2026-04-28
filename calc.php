<?php 
include "mysql.php";

$arr = array();

$session_iscorp_safe = $session_iscorp ?? 0;

$rows = db_query_rows( "select drill.* from drill, company_esi 
                       where iscorp = '" . $session_iscorp_safe . "' 
                       and companyid = company_esi.id 
                       and completed = 1 
                       and showsondrillreports = 1" );

foreach( $rows as $r )
{
    $key = $r["companyid"];
    
    if( !isset($arr[$key]) )
        $arr[$key] = 1;
    else
        $arr[$key]++;
        
    $oth = $r["otherschools"] ?? '';
    $spl = explode(",", $oth );
    
    foreach( $spl as $s )
    {
        $s = trim($s);
        if( $s )
        {
            $key = $s;
            
            if( !isset($arr[$key]) )
                $arr[$key] = 1;
            else
                $arr[$key]++;
        }
    }
}

$one = 0;
$more = 0;
foreach( $arr as $val )
{
    if( $val > 1 )
        $more++;
    else
        $one++;
}

echo( $one . " one<br>" );
echo( $more . " more<br>" );

?>