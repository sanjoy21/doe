<?php
require_once('mysql.php');

db_query( "delete from zip_to_territory where territoryid not in ( select id from territory )" );

if( isset($addnew) && $addnew )
{
    $new_safe = isset($new) ? $new : '';
    db_query( "insert into territory (name) values( '$new_safe' )" );
}

if( isset($del) && $del )
{
    $del_safe = $del;
    db_query( "delete from zip_to_territory where territoryid = '". $del_safe ."' " );
    db_query( "delete from territory where id = '". $del_safe ."' " );
}

if( !isset($specialadmin) || !$specialadmin )
{
    Header( "location: login.php" );
    exit;
}

if( isset($update) && $update )
{
    if( isset($tarr) && is_array($tarr) ) {
        foreach( $tarr as $terrid=>$trainerid )
        {
            $trainerid_safe = $trainerid;
            $terrid_safe = $terrid;
            $sql = "update territory set trainerid = '$trainerid_safe' where id = $terrid_safe";
            db_query( $sql );
//                echo( $sql . "<br>" );                
        }
    }
}

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">Territories</span></strong>

<p>

<span class="copy">

<table cellpadding="0" cellspacing="0" border="0" width="100%"  class="table3">
<tr>
<td valign="top">
<form method='post' action='territories.php'>
<!--start center content-->
<?php

$zipswithout = db_query_array( "select company_esi.zip from company_esi where iscorp = 0 and deleted = 0  and showsondrillreports = 1 and zip not in ( select zip from user_to_zip )", "zip", "zip" );

echo( "<h3>Zips without a trainer: " . (isset($zipswithout) && is_array($zipswithout) ? implode( ", ", $zipswithout ) : '') . "</h3>" );

$zipswithout = db_query_array( "select company_esi.zip from company_esi where iscorp = 0 and deleted = 0  and showsondrillreports = 1 and zip not in ( select zip from zip_to_territory )", "zip", "zip" );

$alltrainers = getAllTrainers( "" );
echo( "<h3>Zips without a territory: " . (isset($zipswithout) && is_array($zipswithout) ? implode( ", ", $zipswithout ) : '') . "</h3>" );
?>
<table>
<tr><th class='copy'>Name</th><th class='copy'>Trainer</th><th class='copy'>Zips</th><th class='copy'>Action</th></tr>
<?php 
$terri = db_query_rows( "select * from territory order by name" );
if( isset($terri) && is_array($terri) ) {
    foreach( $terri as $t )
    {
        $id = isset($t["id"]) ? $t["id"] : '';
        $name = isset($t["name"]) ? $t["name"] : '';
        $trainerid = isset($t["trainerid"]) ? $t["trainerid"] : '';
        echo( "<tr><td class='copy'><a href='editterritory.php?id=$id'>$name</a></td><td class='copy'><select name='tarr[$id]'>" );
        if( isset($alltrainers) && is_array($alltrainers) ) {
            foreach( $alltrainers as $arow )
            {
                $arow_id = isset($arow["id"]) ? $arow["id"] : '';
                $arow_first_name = isset($arow["first_name"]) ? $arow["first_name"] : '';
                $arow_last_name = isset($arow["last_name"]) ? $arow["last_name"] : '';
                $sel = $trainerid == $arow_id ? "SELECTED" : "";
                echo( "<option $sel value='$arow_id'>$arow_first_name $arow_last_name</option>\n" );
            }
        }
        echo( "</td>" );
        $zones = getZipsForTerritory( $id );
        if( !isset($zones) || !count( $zones ) ) {
            $zones = array( "'FIXME'" );
        }
        if( isset($zones) && is_array($zones) ) {
            $posstrainers = db_query_array( "select user_to_zip.userid, concat( user.first_name, ' ', user.last_name ) as fullname from user_to_zip, user where user_to_zip.zip in (  " . implode( ", ", $zones ) . " ) and inactive = 0 and user.id = user_to_zip.userid", "userid", "fullname" );
        } else {
            $posstrainers = array();
        }

        $err = "";
        if( (isset($posstrainers) && count( $posstrainers ) > 1) || (isset($posstrainers[$trainerid]) && !$posstrainers[$trainerid]) ) {
            $err = "<br><font color='red'>Incorrect trainer? " . (isset($posstrainers) ? implode( ", ", $posstrainers ) : '') . "</font>";
        }
        echo( "<td class='copy'>" );
        $any = false;
        if( isset($zones) && is_array($zones) ) {
            foreach( $zones as $z )
            {
                if( $any ) echo( ", " );
                $any = true;
                if( isset($bannedzips) && is_array($bannedzips) && in_array( $z, $bannedzips ) ) {
                    echo( "<font color='red'>$z</font>" );
                } else {
                    echo( $z );
                }
            }
        }
        echo( "$err</td>" );
        echo( "<td class='copy' valign='top'><a href='territories.php?del=$id'>Del</a></td>" );
        echo( "</tr>" );
    }
}
?>
</table>
<input type='submit' name='update' value='Update'>

<span class="copy">
Name: <input type='text' name='new' class='copy' value=''> <input type='submit' name='addnew' value='Add'>
</span>
</td>
</tr>
</table>

<br><br><br><br><br><br><br>

<!--end center content-->

<?php include "ssi/footer.php" ; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>