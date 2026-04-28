<?php

// The cipher method equivalent to MCRYPT_3DES / MCRYPT_MODE_CBC is 'des-ede3-cbc'.
const CIPHER_METHOD = 'des-ede3-cbc';
const KEY_192 = 'qzmoJToI3IS02LYoqIfCcyyi'; // 24-byte key for 3DES-EDE3
const IV_8 = '12345678';                   // 8-byte IV for 3DES

/**
 * Encrypts a value using 3DES-EDE3 in CBC mode with Zero Padding.
 * @param string $buffer The plaintext to encrypt.
 * @return string The hexadecimal representation of the ciphertext.
 */
function encrypt( $buffer )
{
    // The original code manually implements Zero Padding to a multiple of 8 bytes.
    $extra = 8 - (strlen($buffer) % 8);
    if($extra > 0) {
        for($i = 0; $i < $extra; $i++) {
            $buffer .= "\0";
        }
    }
    
    // Replaced mcrypt_cbc with openssl_encrypt
    $result_binary = openssl_encrypt(
        $buffer,
        CIPHER_METHOD,
        KEY_192,
        OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, // OPENSSL_ZERO_PADDING is required to replicate the manual padding/mcrypt behavior
        IV_8
    );
    
    // Convert binary result to hex for output
    return $result_binary !== false ? bin2hex($result_binary) : '';
}

/**
 * Decrypts a hex-encoded 3DES ciphertext.
 * @param string $buffer The hex-encoded ciphertext.
 * @return string The plaintext, with padding stripped.
 */
function decrypt( $buffer )
{
    // Convert hex input to binary (using standard PHP function)
    $binary_buffer = hex2bin($buffer);

    // Replaced mcrypt_cbc with openssl_decrypt
    $result_with_padding = openssl_decrypt(
        $binary_buffer,
        CIPHER_METHOD,
        KEY_192,
        OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, // Use same flags as encrypt
        IV_8
    );
    
    // Trim the null bytes (zero padding) added during encryption
    return $result_with_padding !== false ? rtrim($result_with_padding, "\x00") : '';
}
?>
<form id="form1" name="form1" method="post" action="">
enter text
<input name="data" type="text" />
<input type="submit" name="encrypt" value="Encrypt" />
</form>
<br><br>
<form id="form1" name="form1" method="post" action="">
enter text
<input name="data" type="text" />
<input type="submit" name="decrypt" value="Decrypt" />
</form>

<?php

// Safely access external variables
$encrypt_safe = $encrypt ?? null;
$decrypt_safe = $decrypt ?? null;
$post_data = $_POST['data'] ?? '';

if($encrypt_safe)
{
    // $buffer is already safe from $post_data
echo( htmlspecialchars($post_data) . " encrypted is : " . encrypt( $post_data ) );
}

if($decrypt_safe)
{
    // $buffer is already safe from $post_data
echo( htmlspecialchars($post_data) . " decrypted is : " . decrypt( $post_data ) );
}

?>