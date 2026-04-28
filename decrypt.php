<?php

$key192 = 'qzmoJToI3IS02LYoqIfCcyyi';
$iv = '12345678';
$cleartext = 'SMITH';
$cipher_method = 'des-ede3-cbc';

$cipherText = openssl_encrypt(
    $cleartext,
    $cipher_method,
    $key192,
    OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
    $iv
);

if ($cipherText !== false) {
    printf("3DES encrypted : %s to %s<br>", htmlspecialchars($cleartext), bin2hex($cipherText));
} else {
    echo "Encryption failed.<br>";
}

echo( "should be: 929A732C51BBFD2E<br><br><br>" );

?>