<?
require_once('mysql.php');

if( $addnew )
{
    db_query( "insert into badschoolids values( '" . trim( $new ) . "', now(), '$newcol' )" );
}

if( $del )
{
    db_query( "delete from badschoolids where schoolid = '". $del."' " );
}

if( $updatecolors )
{
	foreach( $colors as $id=>$val )
	{
		db_query( "update badschoolids set color = '$val' where schoolid  = '$id'" );
	}
}

if( !$specialadmin )
{
	Header( "location: login.php" );
        exit;
}

    $okavail = db_query_rows( "select * from badschoolids order by schoolid", "schoolid" );
?>
<? include "ssi/top.php"; ?>		
<!--start center content-->
		
		<strong><span class="title">BANNED SCHOOLIDS</span></strong>
		
		<p>
		
		
		<span class="copy">
	
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
    	<tr>			
		<td valign="top">
	<form method='post' action='badschoolids.php'>
		<!--start center content-->
<table width='500' cellpadding=2 cellspacing=0 border=1>
<tr><th class='copy'>School ID</th><th class='copy'>Date Added</th> <th class='copy'>Color</th> <th class='copy'>Delete</th></tr>
<? 
foreach( $okavail as $t=>$row )
{
    echo( "<tr><td class='copy'><a target=_blank href='viewcompany.php?id=$t'>$t</a></td><td>$row[dateadded]</td><td><select name='colors[$t]'>" );
    foreach( array( "Red"=>"Red", "Orange"=>"Orange", "Yellow"=>"Yellow" ) as $col=>$displ )
    {
	printOption( $col, $displ, $row[color] );
    }
echo( "</select></td><td class='copy' valign='top'><a href='badschoolids.php?del=".$t."'>Del</a></td></tr>" );
}
?>
</table>
<input type='submit' name='updatecolors' value='Update Colors'><br><br>

<span class="copy">
<input type='text' name='new' class='copy' value=''>
<select name='newcol'>
<?
foreach( array( "red"=>"Red", "orange"=>"Orange", "yellow"=>"Yellow" ) as $col=>$displ )
    {
	printOption( $col, $displ, "" );
    }
?>
</select>
<input type='submit' name='addnew' value='Add'>
</span>
			</td>
    	</tr>
    </table>
	
<a href='loadbanned.php'>Load New File</a>	
<br><br><br><br><br><br><br>

		<!--end center content-->
		
                    <? include "ssi/footer.php" ; ?>
		
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
