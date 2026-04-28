<?php

$extra = "";
if (!$specialadmin) {
    $extra = " AND id <> 2858";
}

// Filter by visible region (assumes getRegionDisp returns a quoted CSV list)
if ($thisusersrow["visibleregion"]) {
    // Note: getRegionDisp() is an assumed external function
    $extra .= " AND region IN (" . getRegionDisp($thisusersrow["visibleregion"]) . ")";
}

// Filter by districts (assumes getDistrictString returns an SQL WHERE clause fragment)
if ($thisusersrow["districts"]) {
    // Note: getDistrictString() is an assumed external function
    $extra .= getDistrictString($thisusersrow["districts"]);
}

// Filter by special group/campus
if ($specialgroup) {
    // Parameterized value is appended (assuming DB layer handles security)
    $extra .= " AND campusid = " . intval($specialgroup) . " ";
}

// Filter by special region
if ($specialregion) {
    $extra .= " AND region = '" . mysqli_real_escape_string($GLOBALS['link'], $specialregion) . "' ";
}

// URL-encode the final $extra string for safe transmission via AJAX URL
$extra_encoded = urlencode($extra);
?>
<script type="text/javascript">
<!--
/**
 * AJAX utility function (assumed external function)
 * function getXMLHTTP() { ... }
 */

/**
 * Sets the company selection list to display only a specific company ID.
 * Used for pre-selection or locking input.
 * @param {string} companyid - The ID of the company to display.
 */
function setCompanyToSpecific(companyid)
{
    var s = document.getElementById('school_select');
    // Using a dedicated AJAX endpoint for single ID lookup
    var strURL = "ajaxschools.php?companyid=" + encodeURIComponent(companyid);
    var req = getXMLHTTP(); // function to get xmlhttp object (assumed external)

    if (req)
    {
        req.onreadystatechange = function()
        {
            if (req.readyState == 4) { // Data is retrieved from server
                if (req.status == 200) { // OK status
                    s.innerHTML = req.responseText;
                } else {
                    console.error("AJAX Error in setCompanyToSpecific (Status: " + req.status + "): There was a problem while using XMLHTTP.");
                }
            }
        };
        req.open("GET", strURL, true); // Open URL using GET method
        req.send(null);
    }
}

/**
 * Updates the company selection list dynamically via AJAX based on borough and name input.
 */
function updateCompanies()
{
    var val = "";
    var tmp = document.getElementById('tmpschoolname');
    if (tmp) { val = tmp.value.toLowerCase(); }
    while (val.indexOf("\"") != -1) { val = val.replace("\"", ""); }

    var s = document.getElementById('school_select');
    var bor = document.getElementById("borough");
    var b = (bor && bor.options) ? bor.options[bor.selectedIndex].value : (bor ? bor.value : '');

    // PHP 8.2 Safety: Ensure these variables exist or use defaults
    <?php 
        $js_extra = $extra_encoded ?? ''; 
        $js_field = $overridecname ?? 'companyid';
        $js_nonew = $nonewschool ?? 1;
    ?>

    var newschool = "<?php echo !$js_nonew ? " <br><span class='copy'>or enter new school: <input type='text' name='newschool'></span>" : ""; ?>";
    
    // Build URL safely
    var strURL = "ajaxschools.php?borough=" + encodeURIComponent(b) + 
                 "&fieldname=<?php echo urlencode($js_field); ?>" + 
                 "&name=" + encodeURIComponent(val) + 
                 "&extra=<?php echo $js_extra; ?>";

    var req = getXMLHTTP(); 
    if (req) {
        req.onreadystatechange = function() {
            if (req.readyState == 4 && req.status == 200) {
                if(s) s.innerHTML = req.responseText + newschool;
                var co = document.getElementById("companyid");
                if (co && co.options.length == 2) { co.selectedIndex = 1; }
            }
        };
        req.open("GET", strURL, true);
        req.send(null);
    }
}

/**
 * Fetches and updates the building selection dropdown based on the selected school/company.
 * @param {HTMLElement} schoolele - The company select element.
 */
function updateBuildings(schoolele)
{
    var bid = schoolele.options[schoolele.selectedIndex].value;
    
    var buildingDiv = document.getElementById("building_div");
    if (!buildingDiv) {
        return;
    }

    var s = buildingDiv;
    var strURL = "ajaxbuildings.php?id=" + encodeURIComponent(bid);
    var req = getXMLHTTP(); // function to get xmlhttp object (assumed external)

    if (req)
    {
        req.onreadystatechange = function()
        {
            if (req.readyState == 4) { // Data is retrieved from server
                if (req.status == 200) { // OK status
                    s.innerHTML = req.responseText;
                } else {
                    console.error("AJAX Error in updateBuildings (Status: " + req.status + "): There was a problem while using XMLHTTP.");
                }
            }
        };
        req.open("GET", strURL, true); // Open URL using GET method
        req.send(null);
    }
}

</script>