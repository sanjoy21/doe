<?
require_once('mysql.php');

if( $addnew )
{
    db_query( "insert into badpmsids values( '" . trim( $new ) . "' )" );
}

if( $del )
{
    db_query( "delete from badpmsids where pmsid = '". $del."' " );
}

if( !$specialadmin )
{
	Header( "location: login.php" );
        exit;
}

    $okavail = db_query_array( "select * from badpmsids order by pmsid", "pmsid", "pmsid" );
?>
<? include "ssi/top.php"; ?>		
<!--start center content-->
		
		<strong><span class="title">BANNED PMSIDS</span></strong>
		
		<p>
		
		
		<span class="copy">
	
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
    	<tr>			
		<td valign="top">
	<form method='post' action='badpmsids.php'>
		<!--start center content-->
<table>
<tr><th class='copy'>Pms ID</th></tr>
<? 
foreach( $okavail as $t )
{
    echo( "<tr><td class='copy'>$t</td><td class='copy' valign='top'><a href='badpmsids.php?del=".$t."'>Del</a></td></tr>" );
}
?>
</table>

<span class="copy">
<input type='text' name='new' class='copy' value=''> <input type='submit' name='addnew' value='Add'>
</span>
			</td>
    	</tr>
    </table>
	
	
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
