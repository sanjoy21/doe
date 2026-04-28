<?php
require_once('mysql.php');

// Fetch HR documents, ordered by 'orderby' column
$notavail = db_query_rows( "select * from esidocuments where type='HR' order by orderby" );
?>
<?php include "ssi/top.php"; ?>
<strong><span class="title">HUMAN RESOURCES</span></strong>
<p>

<span class="copy">

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table3">
<tr>
<td valign="top">
<form method='post' enctype='multipart/form-data' >
<table class="table3">
<?php 
foreach( $notavail as $t )
{
    // Safely access quoted array keys
    $path_safe = $t["path"];
    $displayname_safe = $t["displayname"];
    
    echo( "<tr><td class='copy'><a target=_blank href='uploadedpdfs/" . htmlspecialchars($path_safe) . "'>" . htmlspecialchars($displayname_safe) . "</a></td></tr>" );
}
?>
</table>

</td>
</tr>
</table>

<br><br><br><br><br><br><br>

<?php include "ssi/footer.php" ; ?>

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>