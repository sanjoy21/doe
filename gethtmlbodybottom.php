<?php 
require_once "functions.php"; 

// Safely retrieve assumed variables (often passed from an included file or global scope)
$overrideemailinbottom = $overrideemailinbottom ?? null;
$bottomright = $bottomright ?? null;
$comrow = $comrow ?? ['iscorp' => 0]; // Default to non-corporate if not set
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="top">
        <table width="700" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td style="padding: 15px;">
                    </td>
                <td valign="top" style="padding: 15px; border-left: 1px solid #cccccc;">
                <span style="font-family:arial; font-size: 11px; color: #333333;">
                
                <b>Questions? Please Contact:</b><br><br>
                
                <?php
                // Use the override email if available, otherwise use default
                $contact_email = $overrideemailinbottom ? htmlspecialchars($overrideemailinbottom) : "esialive@emergencyskills.com";
                $bottom_right_safe = htmlspecialchars($bottomright ?? '');
                ?>

                <a href="mailto:<?php echo $contact_email; ?>"><?php echo $contact_email; ?></a><br>
                or call 212-564-6833<br><br>
                <?php echo $bottom_right_safe; ?>
                ----------------------------------------------

                </span>
                </td>
            </tr>
        </table>
        
        <tr>
            <td valign="top" colspan="3" style="background-color: #e8ebe7; border-top: 1px solid #cccccc; padding-top: 10px; padding-bottom: 10px;"><div align="center"><span style="font-family: arial; font-size: 11px; color: #666666;"><b>Emergency Skills, Inc.</b><br>
            305 7th Avenue, Suite 1100, New York, NY 10001<br>
            Phone: (212) 564-6833 | Fax: (212) 564-6793<br>
            <a href="http://www.emergencyskills.com">www.emergencyskills.com</a>
            </span></div></td>
        </tr>
        
        <tr>
            <?php
            // Assuming getUrlPrefix() exists and takes the 'iscorp' status from $comrow
            $iscorp_status = (int)($comrow['iscorp'] ?? 0);
            $url_prefix = htmlspecialchars(getUrlPrefix( $iscorp_status ));
            ?>
            <td valign="top" colspan="3"><img src="https://<?php echo $url_prefix; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/email/Emergency-Skills-Footer.jpg" alt="Emergency Skills Footer Logo"></td>
        </tr>
    </table>
    </td>
    </tr>
</table>

</body>
</html>