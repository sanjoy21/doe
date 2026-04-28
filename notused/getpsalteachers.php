	<?php
require_once('mysql.php');

	$q = strtolower($_GET["q"]);
	if (!$q || strlen( $q ) < 2 ) return;
	 
	 $sql = "select concat( lastname, ', ', firstname, ' (', responderid, ')' ) as name from responders_esi r, company_esi c where concat( lastname, ', ', firstname ) LIKE '$q%' and c.id = r.clientid and iscorp = 0 and r.deleted = 0 and c.deleted = 0 ";
	 $rsd = mysql_query($sql);
	 while($rs = mysql_fetch_array($rsd)) {
	      $cname = $rs['name'];
	         echo "$cname\n";
		 }
		 ?>