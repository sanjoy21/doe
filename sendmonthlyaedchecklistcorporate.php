<?php
// Initialize external variables safely
$nologinrequired = 1;
$sendanyway = $sendanyway ?? false;
$session_iscorp = $session_iscorp ?? 0;

include "mysql.php";
// Assumed external functions: db_query_array, getCompanyRow, getOrCreateEmailId, sendHTMLMail

// --- SQL Query for Corporate Clients with Uninspected AEDs ---
// Select companies (clientid) that:
// 1. Have active (deleted=0) AEDs.
// 2. Have AEDs not yet inspected this month (check against aedinspections table).
// 3. Have AEDs not marked as stolen.
// 4. Have opted into the monthly checklist reminder (sendmonthlyaedchecklist = 1).
// 5. Are corporate clients (iscorp = 1).
// 6. Have a contact email defined.
$sql = "
    SELECT clientid, COUNT(*) 
    FROM aed_esi a, company_esi c 
    WHERE a.deleted = 0 
    AND c.deleted = 0 
    AND c.id = a.clientid 
    AND aedid NOT IN (
        SELECT aedid FROM aedinspections ai 
        WHERE a.aedid = ai.aedid 
        AND thedate LIKE '" . date("Y-m") . "%' 
    ) 
    AND aedstolen = 0 
    AND sendmonthlyaedchecklist = 1 
    AND iscorp = 1 
    AND contactemail > '' 
    GROUP BY clientid
";


// --- Check Execution Condition ---
$current_date = date("Y-m-d");
$first_tuesday = date("Y-m-d", strtotime("First Tuesday of this month"));
$second_wednesday = date("Y-m-d", strtotime("Second Wednesday of this month"));

if ($current_date == $first_tuesday || $current_date == $second_wednesday || $sendanyway) {

    // Fetch client IDs (company IDs) that match the criteria
    $res = db_query_array($sql, "clientid", "clientid");
    
    $count = 0;
    
    // --- Loop Through Companies and Send Emails ---
    foreach ($res as $clientid) {
        $count++;
        $companyrow = getCompanyRow($clientid);
        
        $toemail = [];
        $contact_email = $companyrow['contactemail'] ?? '';
        
        if (!empty($contact_email)) {
            $toemail[] = $contact_email;
        }

        foreach ($toemail as $email) {
            // Generate encrypted link for the monthly checklist, pointing to the 'clients' subdomain
            $encrypted_id = $clientid * 1440;
            $email_id = getOrCreateEmailId($email); // Assumed function
            $tmplink = "http://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/monthlyaedchecklist.php?encryptedid={$encrypted_id}_{$email_id}";

            $subject = strtoupper(date("F Y")) . " - ACTION REQUIRED! Ensure your AED is ready in an Emergency";
            
            $body = "Dear AED Contact:\n\n" . 
                    "<a href='{$tmplink}'><b>Monthly Inspection</b></a>\n\n" . 
                    "This email, sent courtesy of Emergency Skills, your AED vendor, serves as a reminder to conduct your Monthly AED Inspection. \n\n" . 
                    "CLICK BELOW to view list of AEDs, locations and quick maintenance checklist:\n\n" . 
                    "<a href='{$tmplink}'><b>Monthly Inspection</b></a>\n\n" . 
                    "MONTHLY INSPECTIONS ensure the AEDs are ready for use during a medical emergency.\n\n" . 
                    "After clicking the link, you may select FORWARD and enter the contact information for whomever is responsible for inspecting the AED(s).\n\n" . 
                    "As always, if your AED is chirping, please call Emergency Skills at 212-564-6833 as soon as possible.\n\n" . 
                    "Thank you again for attention to this life saving matter!\n\n" . 
                    "Note: this is an automated email. Please do not reply to this email.";

            // Send the email
            sendHTMLMail($email, $subject, nl2br($body), "sarahg@emergencyskills.com");
        }
        
        // Output confirmation message (for debug/logging)
        echo "would email " . htmlspecialchars($companyrow['contactemail'] ?? 'N/A') . "<br>";
    }

} else {
    echo "not sending";
}
?>