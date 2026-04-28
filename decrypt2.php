<?php

/**
 * Decrypts a value using 3DES-EDE3 in ECB mode.
 * @param string $value The ciphertext to decrypt.
 * @return string The plaintext.
 */
function decrypt($value) { 
    // The key used for 3DES-EDE3 must be 24 bytes (192 bits).
    $key = 'qzmoJToI3IS02LYoqIfCcyyi';
    // The cipher method equivalent to MCRYPT_3DES / MCRYPT_MODE_ECB is 'des-ede3'.
    $cipher_method = 'des-ede3';

    // The IV is not used in ECB mode, but openssl_decrypt requires it to be set to an empty string.
    $iv = ''; 

    // Replaced mcrypt_ecb with openssl_decrypt
    $cleartext = openssl_decrypt(
        $value,
        $cipher_method,
        $key,
        OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
        $iv
    );
    
    // openssl_decrypt may include padding. This ensures we return the raw value if decryption fails or is raw.
    return $cleartext !== false ? rtrim($cleartext, "\x00") : $value;
}
   
/**
 * Encrypts a value using 3DES-EDE3 in ECB mode.
 * @param string $value The plaintext to encrypt.
 * @return string The ciphertext.
 */
function encrypt($value)
{
    // The key used for 3DES-EDE3 must be 24 bytes (192 bits).
    $key = 'qzmoJToI3IS02LYoqIfCcyyi';
    // The cipher method equivalent to MCRYPT_3DES / MCRYPT_MODE_ECB is 'des-ede3'.
    $cipher_method = 'des-ede3';

    // The IV is not used in ECB mode, but openssl_encrypt requires it to be set to an empty string.
    $iv = ''; 
    
    // Replaced mcrypt_ecb with openssl_encrypt
    $cipherText = openssl_encrypt(
        $value,
        $cipher_method,
        $key,
        OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, // Added OPENSSL_ZERO_PADDING to mimic mcrypt_ecb behavior
        $iv
    );
    
    return $cipherText !== false ? $cipherText : $value;
}

// Display the hexadecimal representation of the encrypted values
echo( bin2hex( encrypt( '621223' ) ) . "<br><br>" );
echo( bin2hex( encrypt( 'SMITH' )) . "<br><br>" );

?>