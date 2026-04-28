<?php

/**
 * Attempts to retrieve the AHA eCard search results page using retries.
 * * NOTE: This function attempts to scrape the AHA website which is highly unreliable 
 * due to dynamic tokens and session handling. Using an official API is the only 
 * stable way to integrate this data.
 *
 * @param string $cardid The eCard code to check (currently unused in the URL).
 * @return void This function echoes output and writes files, it does not return a value.
 */
function findCardStatus(string $cardid): void
{
    // The URL contains hardcoded data for a specific card ID and dynamic tokens.
    // This is the source of the unreliability.
    $url = "https://ahainstructornetwork.americanheart.org/AHAECARD/ecard.jsp?_dyncharset=ISO-8859-1&Enter+eCard+Code=152001608351&_D%3AEnter+eCard+Code=+&_DARGS=%2FAHAECARD%2Fsearch%2FecertificateEmployerStudentSearch.jsp.2";
    
    $max_attempts = 10;
    $agent = 'Mozilla/5.0 (X11; Linux x86_64; rv:45.0) Gecko/20100101 Firefox/45.0';

    echo "Starting attempt to fetch AHA card status...<br>";

    for ($i = 1; $i <= $max_attempts; $i++) {
        
        $ch = curl_init();

        // Use curl_setopt_array for cleaner configuration
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => $agent,
            
            // Cookie management using a temp file
            CURLOPT_COOKIEJAR => sys_get_temp_dir() . '/cookies.txt',
            CURLOPT_COOKIEFILE => sys_get_temp_dir() . '/cookies.txt',
            
            // Security/debugging options
            CURLOPT_SSL_VERIFYPEER => false, // Set to true in production if you verify SSL manually
            CURLOPT_VERBOSE => false,        // Set to true for debugging cURL
        ]);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "Attempt {$i} failed with cURL error: " . htmlspecialchars($error) . "<br>";
            continue;
        }

        // Write the result to the local folder 'tmpcards'
        if (!is_dir('tmpcards')) {
            mkdir('tmpcards', 0777, true);
        }
        file_put_contents("tmpcards/results{$i}.txt", $result);
        
        echo "Attempt {$i} completed. File saved: tmpcards/results{$i}.txt<br>";

        // Check for the success string
        if (strpos($result, "You can search") !== false) {
            echo "Success condition met on attempt {$i}. Stopping retries.<br>";
            break;
        }

        if ($i === $max_attempts) {
             echo "Failed to find success string after {$max_attempts} attempts.<br>";
        }
    }
}

findCardStatus("aaa");

?>