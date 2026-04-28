<?php

/**
 * Attempts to check the status of an AHA eCard ID via POST request.
 * NOTE: The data array uses hardcoded session tokens which must be dynamically 
 * fetched from the target website to function correctly in a real-world scenario.
 * * @param string $cardid The eCard code to check.
 * @return string The raw response from the server, or a cURL error message.
 */
function findCardStatus(string $cardid): string
{
    // The data array contains the hardcoded fields from the original script.
    $data = [
        "_dyncharset" => "ISO-8859-1",
        "_dynSessConf" => "-2921505537184134924",
        "/org/heart/ecc/handler/EcertSearchFormHandler.successURL" => "/AHAECARD/ecard.jsp?pid=ahaecard.ecardSearchResults",
        "_D:/org/heart/ecc/handler/EcertSearchFormHandler.successURL" => " ",
        "/org/heart/ecc/handler/EcertSearchFormHandler.errorURL" => "/AHAECARD/ecard.jsp?pid=ahaecard.employerStudentSearch",
        "_D:/org/heart/ecc/handler/EcertSearchFormHandler.errorURL" => " ",
        "_D:/org/heart/ecc/handler/EcertSearchFormHandler.validateCertificate" => " ",
        "Enter eCard Code" => "152001608351", // This is the hardcoded example card ID, not $cardid
        "_D:Enter eCard Code" => " ",
        "_DARGS" => "/AHAECARD/search/ecertificateEmployerStudentSearch.jsp.2",
    ];

    $url = "https://ahainstructornetwork.americanheart.org/AHAECARD/ecard.jsp?pid=ahaecard.employerStudentSearch";

    $ch = curl_init();
    
    // Set cURL options using an array for clarity
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
    ]);

    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return "cURL Error: " . $error;
    }

    return $result ?: "Failed to retrieve response.";
}

// Execute function and display result
echo findCardStatus("aaa");
?>