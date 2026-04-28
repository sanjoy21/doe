<? 
mysql_connect( "localhost", "vision", "earthy" );
mysql_select_db( "vision_shop" );

$startdate = mktime( 0,0,0,date( "m" ), date( "d" ), date( "Y" ) );
if( $type == "month" )
{
  $enddate = mktime( 0,0,0,date( "m" ) - 1, date( "d" ), date( "Y" ) );
  $filename = "monthly.csv";
  $subject = "Your monthly report for : " . date( "Y-m-d", $enddate ) . " to " . date( "Y-m-d", $startdate ) ;
}
else if( $type == "week" )
{
  $enddate = mktime( 0,0,0,date( "m" ), date( "d" ) - 7, date( "Y" ) );
  $filename = "weekly.csv";
  $subject = "Your weekly report for : " . date( "Y-m-d", $enddate ) . " to " . date( "Y-m-d", $startdate ) ;
}
else
{
  $enddate = mktime( 0,0,0,date( "m" ), date( "d" ) - 1, date( "Y" ) );
  $filename = "daily.csv";
  $subject = "Your daily report for : " . date( "Y-m-d", $enddate ) . " to " . date( "Y-m-d", $startdate ) ;
}

$plainemailbody = "Report attached.";
$arr = array();
$tmparr = array("Order Number", "Item ", "Quantity", "Price", "Shipping", "Tax", "Payment Type", "Date", "Name", "Address", "E-mail" );
$arr[] = $tmparr;
$res = mysql_query( "select pr.weight, oi.product_id, oi.price, oi.amount, p.billing_firstname, p.billing_lastname, p.login, p.billing_address, p.billing_city, s.state, p.billing_zipcode, p.billing_country, o.*, pr.name from xlite_orders o, xlite_profiles p, xlite_states s, xlite_products pr, xlite_order_items oi where oi.order_id = o.order_id and oi.product_id = pr.product_id and s.state_id = p.billing_state and p.profile_id = o.profile_id and o.date < $startdate and o.date >= $enddate and ( o.status = 'P' or o.status = 'C' ) order by o.date" );
while( $r = mysql_fetch_array( $res ) )
{
  $tmparr = array();
  $tmparr[] = $r["order_id"];
  $tmparr[] = $r["name"];
  $tmparr[] = $r["amount"];
  $tmparr[] = $r["price"];
  $scost = getShippingCost( $r["shipping_cost"],$r["amount"], $r["order_id"], $r["weight"] );
  $tmparr[] = $scost;
  $tax = getTax( $r["tax"], $scost + $r["amount"]* $r["price"], $r["subtotal"] + $r["shipping_cost"] );
  //  echo( $tax .  " - $r[tax] <br>" );
  $tmparr[] = $tax;
  $tp = $r["cc_type"]?$r["cc_type"]:$r["payment_method"];
  $tmparr[] = $tp;
  $tmparr[] = date( "Y-m-d h:i:s", $r["date"] );
  $tmparr[] = $r["billing_firstname"] . " " . $r["billing_lastname"];
  $tmparr[] = $r["billing_address"] . ", " . $r["billing_city"] . ", " . $r["state"] . " " . $r["billing_zipcode"] . ", " . $r["billing_country"] ;
  $tmparr[] = $r["login"];
  $arr[] = $tmparr;
}

function getTax( $tax, $amount, $total )
{
  return number_format( $tax * $amount / $total, 2 ); 
}

function getShippingCost( $shippingcost, $amount, $orderid, $prodweight )
{
  if( $prodweight <= 0 )
    {
      return 0;
    }
  $totalnum = array_pop( mysql_fetch_array( mysql_query( "select sum( amount ) from xlite_order_items where order_id = $orderid " ) ) );
  if( $totalnum == $amount )
    return $shippingcost;

  $totalnumwnoweight = array_pop( mysql_fetch_array( mysql_query( "select sum( amount ) from xlite_order_items, xlite_products where xlite_order_items.product_id = xlite_products.product_id and order_id = $orderid and weight = 0" ) ) );
  
  return ( $shippingcost / ($totalnum - $totalnumwnoweight )) * $amount ;
}

$f = fopen( "/tmp/" . $filename, "w+" );
fputcsv( $f, $arr );
fclose( $f );

require "class.phpmailer.php";
$mail = new PHPMailer();

$mail->IsSMTP();                                      // set mailer to use SMTP
$mail->Host = "localhost";  // specify main and backup server
$mail->SMTPAuth = false;     // turn on SMTP authentication
    
$mail->From = "info@visionaireworld.com";
$mail->FromName = "Visionaireworld.com";
$mail->AddReplyTo( "info@visionaireworld.com", "Visionaireworld.com" );
$mail->WordWrap = 50;                                 // set word wrap to 50 characters
$mail->IsHTML(true);                                  // set email format to HTML

$mail->Subject = "$subject";
$mail->IsHTML(false);                                  // set email format to HTML
$mail->Body    = $plainemailbody;
$mail->addAttachment( "/tmp/" . $filename );

$mail->AddAddress("cox@vireo.org,smeriam@visionaireworld.com");
if(!$mail->Send())
{	
  echo "Message could not be sent. <p>";
  //	    echo "Mailer Error: " . $mail->ErrorInfo;
  //	    exit;
}
function fputcsv($filePointer,$dataArray2,$delimiter=',',$enclosure='"')
{
  // Write a line to a file
  // $filePointer = the file resource to write to
  // $dataArray = the data to write out
  // $delimeter = the field separator
  foreach( $dataArray2 as $dataArray )
    {
      // Build the string
      $string = "";
      
  // No leading delimiter
      $writeDelimiter = FALSE;
      foreach($dataArray as $dataElement)
	{
	  // Replaces a double quote with two double quotes
	  $dataElement=str_replace("\"", "\"\"", $dataElement);
	  
	  // Adds a delimiter before each field (except the first)
	  if($writeDelimiter) $string .= $delimiter;
	  
	  // Encloses each field with $enclosure and adds it to the string
	  $string .= $enclosure . $dataElement . $enclosure;
	  
	  // Delimiters are used every time except the first.
	  $writeDelimiter = TRUE;
	} // end foreach($dataArray as $dataElement)
      
      // Append new line
      $string .= "\n";
      
      // Write the string to the file
      fwrite($filePointer,$string);
    }
}
?>