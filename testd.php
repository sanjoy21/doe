<?php 
require_once('mysql.php');
require_once('services.php');

$lastname = $lastname ?? null;
$pmsid = $pmsid ?? null;

if( $lastname ) 
{
    $pmsidvalidated = validateEmployee( $pmsid, $lastname, "testing", 0 ); 
}

?>

<form id="form1" name="form1" method="post" action="">
    <h3>Employee Validation</h3>
    Last Name: <input name="lastname" type="text" value="<?= htmlspecialchars($lastname ?? '') ?>" />
    <br>
    PMS ID: <input name="pmsid" type="text" value="<?= htmlspecialchars($pmsid ?? '') ?>" />
    <br>
    <input type="submit" name="validate" value="Validate" />
</form>

<hr style="margin: 20px 0;">

<form id="form2" name="form2" method="post" action="">
    <h3>Data Decryption</h3>
    Enter Encrypted Text:
    <input name="data" type="text" />
    <input type="submit" name="decrypt" value="Decrypt" />
</form>

<?php

/*
//     if($validate)
// {
//     require_once( "soap/lib/nusoap.php" );
// //     $s = new soapclient('https://165.155.112.27/WebServices/Safety/wsemployees.asmx?WSDL' );
// //     $s->setCredentials( 'c3ntral\Service.Safety','!W3b.$af3tY%' );


// //    $body = "    
// //         <GetEmployeeInfo>
// //           <EncryptedPMSID>".( $pmsid )."</EncryptedPMSID>
// //           <EncryptedLastName>".( $lastname )."</EncryptedLastName>
// //         </GetEmployeeInfo>
// // ";
// //      $msg = $s->serializeEnvelope($body);
// //      $s->send($msg, 'GetEmployeeInfo');
// //     if(!$err = $s->getError()){
// //         echo 'Result Code '.$s->document.'<br><br>';
// //     }else{
// //         echo '<strong>Error:</strong> '.$err.'<br><br>';
// //     }

//  //    try
// //     {
//             //staging
//          $client = new SoapClient2('https://165.155.112.27/WebServices/Safety/wsemployees.asmx?WSDL',array('trace'=>true));
//  //    }
// //     catch (SoapFault $sf)
// //     {
// //         echo $sf->getMessage(), "\n";
// //     }

//     $client->setCredentials('c3ntral\Service.Safety', '!W3b.$af3tY%');
//     $param1  = array('parameters' =>
//                      array('EncryptedPMSID'=>( $pmsid ), 'EncryptedLastName' =>
//                            ( $lastname )));

//     $result = $client->call('GetEmployeeInfo', $param1);

//     print_r($result);
    
// }

// //967300
// if( $decrypt )
// {
//     echo( decrypt( $data ) );
// }
*/
?>