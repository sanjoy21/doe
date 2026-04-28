<?

function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

if( !$_POST )
{
    exit;
}

$amts = array();
$amts["US Individual"] = 30;
$amts["Int Individual"] = 40;
$amts["US Student"] = 18;
$amts["Int Student"] = 30;
$amts["US Family"] = 45;
$amts["Int Family"] = 50;
$amts["US Sustaining"] = 60;
$amts["Int Sustaining"] = 70;
$amts["US Life"] = 350;
$amts["Int Life"] = 400;
$amts["Convert US Family Life"] = 150;
$amts["Convert Int Family Life"] = 175;
$amts["US Family Life"] = 500;
$amts["Int Family Life"] = 575;


$fullname = stripslashes( $_POST["Full-Name"] );
$spl = split( " " , $fullname );
$firstname = array_shift( $spl );
$lastname = join( " ", $spl );
$address = stripslashes( $_POST[Address] );
$city = stripslashes( $_POST[City] );
$state = stripslashes( $_POST["State-Province"] );
$zip = stripslashes( $_POST["Zip-Postal"] );
$phone = stripslashes( $_POST["Primary-Telephone"] );
$phone2 = stripslashes( $_POST["Secondary-Telephone"] );
$email = stripslashes( $_POST["Email"] );

$jasnacontribution = stripslashes( $_POST["JASNA-Contribution"] );
$contributionchurches = stripslashes( $_POST["Contribution-Churches"] );
$contributionaustenhouse = stripslashes( $_POST["Contribution-Austen-House"] );
$contributionchawton = stripslashes( $_POST["Contribution-CHL"] );
$membership = stripslashes( $_POST["Membership"] );

$amtsdisplay = array();
$amtsdisplay["US Individual"] = "Individual Annual (US\$30)";
$amtsdisplay["Int Individual"] = "Individual Annual (US\$40)";
$amtsdisplay["US Student"] = "Student annual (fulltime) (US\$18)";
$amtsdisplay["Int Student"] = "Student annual (fulltime) (US\$30)";
$amtsdisplay["US Family"] = "Family Annual** (US\$45)";
$amtsdisplay["Int Family"] = "Family Annual** (US\$50)";
$amtsdisplay["US Sustaining"] = "Sustaining Annual*** (US\$60)";
$amtsdisplay["Int Sustaining"] = "Sustaining Annual*** (US\$70)";
$amtsdisplay["US Life"] = "Life (US\$350)";
$amtsdisplay["Int Life"] = "Life (US\$400)";
$amtsdisplay["Convert US Family Life"] = "Convert to Family Life**** (US\$150)";
$amtsdisplay["Convert Int Family Life"] = "Convert to Family Life**** (US\$175)";
$amtsdisplay["US Family Life"] = "Family Life (US\$500)";
$amtsdisplay["Int Family Life"] = "Family Life (US\$575)";

$total = $amts[$membership];
$otherdisplay .= " Membership Type: $membership<br>";

if( $jasnacontribution )
{
    $otherdisplay .= " JASNA Contribution: \$".number_format( $jasnacontribution, 2 )."<br>";
    $total += $jasnacontribution;
}
if( $contributionchurches )
{
    $otherdisplay .= " Contribution - Jane Austen-related Churches: \$".number_format( $contributionchurches, 2 )."<br>";
    $total += $contributionchurches;
}
if( $contributionaustenhouse )
{
    $otherdisplay .= " Contribution - Jane Austen's House Museum: \$".number_format( $contributionaustenhouse, 2 )."<br>";
    $total += $contributionaustenhouse;
}

if( $contributionchawton )
{
    $otherdisplay .= " Contribution - Chawton House Library: \$".number_format( $contributionchawton, 2 )."<br>";
    $total += $contributionchawton;
}

if( $_POST["New-Member"] )
{
    $otherinfo  .= "New Member<br>";
}
if( $_POST["Renewal"] )
{
    $otherinfo  .= "Renewal<br>";
}
if( $_POST["Address-Change"] )
{
    $otherinfo  .= "Address Change<br>";
}
if( $_POST["Contribution"] )
{
    $otherinfo  .= "Contribution<br>";
}
$gaddress = "";
if( $_POST["Gift"] )
{
    $otherinfo  .= "Gift<br>";
    $otherinfo  .= "&nbsp;&nbsp;&nbsp;&nbsp;Gift Name: " . stripslashes( $_POST["Gift-From-Name"] )." <br>";
    $otherinfo  .= "&nbsp;&nbsp;&nbsp;&nbsp;Gift Email: " . stripslashes( $_POST["Gift-From-Email"] )." <br>";
}    

$gaddress .= "&nbsp;&nbsp;&nbsp;&nbsp;Name : " . stripslashes( $_POST["Full-Name"] )." <br>";
$gaddress .= "&nbsp;&nbsp;&nbsp;&nbsp;Second Name : " . stripslashes( $_POST["Second-Name"] )." <br>";
$gaddress .= "&nbsp;&nbsp;&nbsp;&nbsp;Address : " . stripslashes( $_POST["Address"] )." <br>";
$gaddress .= "&nbsp;&nbsp;&nbsp;&nbsp;City : " . stripslashes( $_POST["City"] )." <br>";
$gaddress .= "&nbsp;&nbsp;&nbsp;&nbsp;State : " . stripslashes( $_POST["State-Province"] )." <br>";
$gaddress .= "&nbsp;&nbsp;&nbsp;&nbsp;Zip : " . stripslashes( $_POST["Zip-Postal"] )." <br>";
$gaddress .= "&nbsp;&nbsp;&nbsp;&nbsp;Country : " . stripslashes( $_POST["Country"] )." <br>";
$gaddress .= "&nbsp;&nbsp;&nbsp;&nbsp;Phone : " . stripslashes( $_POST["Primary-Telephone"] )." <br>";
$gaddress .= "&nbsp;&nbsp;&nbsp;&nbsp;Secondary Phone : " . stripslashes( $_POST["Secondary-Telephone"] )." <br>";
$gaddress .= "&nbsp;&nbsp;&nbsp;&nbsp;Email : " . stripslashes( $_POST["Email"] )." <br>";
// $address = stripslashes( $_POST[Address] );
// $city = stripslashes( $_POST[City] );
// $state = stripslashes( $_POST["State-Province"] );
// $zip = stripslashes( $_POST["Zip-Postal"] );
// $phone = stripslashes( $_POST["Primary-Telephone"] );
// $phone2 = stripslashes( $_POST["Secondary-Telephone"] );
// $email = stripslashes( $_POST["Email"] );

$hiddeninput = "Total: \$" . number_format( $total, 2 ) . "<br>".
    $otherdisplay .
    $otherinfo . 
    $gaddress;


?>

<HTML>

<HEAD> <TITLE>JASNA Membership Form - Confirm</TITLE> </HEAD>

<BODY>
	<H1 align="center"><img border="0" src="../images/JASNA-logo-2.gif" width="451" height="77"></H1>

    <H1 align="center"><font face="Times New Roman" size="4">
   <b>JASNA Membership Form</b></font></H1>


 <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_ext-enter">
<input type="hidden" name="redirect_cmd" value="_xclick">
         <input type="hidden" name="business" value="jasnadues@yahoo.com">
    <input type='hidden' name='item_number' value="1">
    <input type='hidden' name='item_name' value="JASNA Membership Total">
    <input type="hidden" name="return" value="http://jasna.org/membership/thanks.html">
    <input type="hidden" name="shipping" value="0">
    <input type='hidden' name='amount' value="<?=$total?>">
    <input type="hidden" name="lc" value="US">
    <input type="hidden" name="bn" value="PP-BuyNowBF">

<!--<input type=hidden name='first_name' value="<?=$firstname?>">
<input type=hidden name='last_name' value="<?=$lastname?>">
<input type=hidden name='address1' value="<?=$address?>">
<input type=hidden name='email' value="<?=$email?>">
<input type=hidden name='city' value="<?=$city?>">
<input type=hidden name='state' value="<?=$state?>">
<input type=hidden name='zip' value="<?=$zip?>">
-->
    <?
    $spl = split( "<br>", $hiddeninput );
$i = 0;
foreach(  $spl as $s )
{
    if( !$s )
        continue;
    $spl2 = split( ":", str_replace( "&nbsp;", "", $s) );
    $val = trim( $spl2[1] );
    if( !$val && strpos( $spl2[0], "Gift" ) === false )
        $val = "Yes";
    else if( !$val )
    {
        $val = "N/A";
    }
    echo( "<input type='hidden' name='on$i' value=\"".trim( $spl2[0] )."\">\n" );
    echo( "<input type='hidden' name='os$i' value=\"".trim( $val )."\">\n" );
    $i++;
}
    ?>

    <font color="#990000"><b>Please review membership information and submit. (To make a correction, return to the previous page.)</b></font>

        <table border="0">


          <tr>
            <td>Membership:</td>
            <td><?=$amtsdisplay[$membership]?></td>
          </tr>
 <? if( $otherdisplay ) { ?>
          <tr>
            <td valign='top'>Other Donations:</td>
            <td><?=$otherdisplay?></td>
          </tr>
 <? } ?>
          <tr>
            <td valign='top'>Total:</td>
            <td>$<?=number_format( $total, 2 )?></td>
          </tr>
          <tr>
            <td valign='top'>Other information:</td>
            <td><?=$otherinfo?></td>
          </tr>


          <tr>
            <td>Name:</td>
    <td><?=stripslashes( $_POST["Full-Name"] )?></td>
          </tr>
          <tr>
            <td>Second Name (if family membership):&nbsp;&nbsp;</td>
            <td><?=stripslashes( $_POST["Second-Name"] )?></td>
          </tr>
          <tr>
            <td>Address:</td>
                                                             <td><?=stripslashes( $_POST["Address"] )?></td>
          </tr>
          <tr>
            <td>City:&nbsp;</td>
            <td><?=stripslashes( $_POST["City"] )?></td>
          </tr>
          <tr>
            <td>State or Province:</td>
                                                             <td><?=stripslashes( $_POST["State-Province"] )?></td>
          </tr>
          <tr>
            <td>Zip or Postal Code:</td>
                                                             <td><?=stripslashes( $_POST["Zip-Postal"] )?></td>
          </tr>
          <tr>
            <td>Country:</td>
                                                             <td><?=stripslashes( $_POST["Country"] )?></td>
          </tr>
          <tr>
            <td>Primary Telephone Number:</td>
                                                             <td><?=stripslashes( $_POST["Primary-Telephone"] )?></td>
          </tr>
          <tr>
            <td>Secondary Telephone Number:</td>
                                                             <td><?=stripslashes( $_POST["Secondary-Telephone"] )?></td>
          </tr>
          <tr>
            <td>E-mail:</td>
                                                             <td><?=stripslashes( $_POST["Email"] )?></td>
</tr>

        </table>


		<INPUT type="submit" name='submit' value="Proceed to PayPal">
	</FORM>

<p><p>Clicking on the above button will take you to the PayPal payment step.  If you don't already have a PayPal account, 
you will be asked to open one.  PayPal accounts can be linked to either your credit card or your checking account.
Please note that JASNA cannot accept credit cards directly.</p>


<p>If you have any questions, contact <a href="mailto:carolestokes@cableone.net">carolestokes@cableone.net</a>.</p>


</BODY>

</HTML>

