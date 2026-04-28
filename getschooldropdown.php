<?php
$extra = "";
if( !$specialadmin ) {
    $extra = "and id <> 2858";
}
if( isset($thisusersrow["visibleregion"]) && $thisusersrow["visibleregion"] ) {
    $extra .= " and region in (" . getRegionDisp( $thisusersrow["visibleregion"] )." )";
}

if( isset($thisusersrow["districts"]) && $thisusersrow["districts"] ) {
    $extra .= getDistrictString( $thisusersrow["districts"]);
}

if( $specialgroup ) {
    $extra .= " and campusid = $specialgroup ";
}

if( isset( $overrideiscorp ) ) {
    $sql = "SELECT id, companyname, borough, schoolcode, concat( schoolcode, companyname ) as longname FROM company_esi where iscorp = '$overrideiscorp' and deleted = 0 $extra order by longname";
} else if( isset($session_iscorp) && $session_iscorp ) {
    $sql = "SELECT id, companyname, borough, schoolcode, concat( companyname, schoolcode ) as longname FROM company_esi where iscorp = '$session_iscorp' and deleted = 0 $extra order by longname";
} else {
    $iscorp_value = isset($session_iscorp) ? $session_iscorp : '';
    $sql = "SELECT id, companyname, borough, schoolcode, concat( schoolcode, companyname ) as longname FROM company_esi where iscorp = '$iscorp_value' and deleted = 0 $extra order by longname";
}

//echo( $sql );
$tmpschools = db_query_rows($sql);
//print_r($tmpschools);exit;

$school_options = array();
foreach ($tmpschools as $tmpschool) {
    $tmpcompanyid = $tmpschool["id"];
    if( isset($tmpschool["schoolcode"]) && $tmpschool["schoolcode"] ) {
        if( isset($session_iscorp) && $session_iscorp ) {
            $school_name = $tmpschool["companyname"] . " (". $tmpschool["schoolcode"] . ")";
        } else {
            $school_name = str_replace( "-", "", $tmpschool["schoolcode"] ) . " (". $tmpschool["companyname"] . ")";
        }
    } else {
        $school_name = $tmpschool["companyname"];
    }
    
    $borough = isset($tmpschool["borough"]) ? $tmpschool["borough"] : "other";
    $selected_var = "selected_" . $tmpcompanyid;
    $selected_value = isset(${$selected_var}) ? ${$selected_var} : '';
    
    $school_options[$borough][strtolower( $tmpschool["longname"] )] = '<option ' . $selected_value . ' value="' . $tmpcompanyid . '">' . htmlentities( $school_name, ENT_QUOTES ) . '</option>';
}
//print_r($school_options);exit;
//$school_select .= '</select>';
$nonewschool = true;
?>
<script type="text/javascript">
<!--
function changeBorough() {
    var val = '';
    var tmp = document.getElementById( 'tmpschoolname' );
    if( tmp ) {
        val = tmp.value.toLowerCase();
    }
    while( val.indexOf( "\"" ) != -1 ) {
        val =  val.replace( "\"", "" );
    }
    var s = document.getElementById('school_select');
    var bor = document.getElementById( "borough" );
    var b = '';
    if( bor && bor.options ) {
        b = bor.options[ bor.selectedIndex].value;
    } else if( bor ) {
        b = bor.value;
    }
    
    <?php if( !$nonewschool ) { ?>
    var newschool  = " <br><span class=copy>or enter new school: <input type='text' style='font-size: 10px;  font-family: verdana;' name='newschool'></span>";
    <?php } else { ?>
    var newschool = "";
    <?php } ?>
    
    // Use AJAX for both school and company modes
    var strURL = "ajaxschools.php?borough=" + encodeURIComponent(b) + "&name=" + encodeURIComponent(val) + "&fieldname=<?php echo isset($overridecname) ? $overridecname : 'companyid'; ?>";
    
    <?php if(isset($overrideiscorp) && $overrideiscorp) { ?>
    strURL += "&iscorp=1";
    <?php } ?>
    
    var req = getXMLHTTP(); // function to get xmlhttp object
    if (req)
    {
        req.onreadystatechange = function()
        {
            if (req.readyState == 4) { //data is retrieved from server
                if (req.status == 200) { // which represents ok status
                    s.innerHTML = req.responseText + newschool;
                }
                else
                {
                    alert("There was a problem while using XMLHTTP:\n");
                }
            }
        }
        req.open("GET", strURL, true); //open url using get method
        req.send(null);
    }
}

function updateBuildings( schoolele )
{
    var bid = schoolele.options[schoolele.selectedIndex].value;
    if( !document.getElementById( "building_div" ) ) {
        return;
    }

    var s = document.getElementById( "building_div" );
    strURL = "ajaxbuildings.php?id=" + bid;
    var req = getXMLHTTP(); // function to get xmlhttp object
    if (req)
    {
        req.onreadystatechange = function()
        {
            if (req.readyState == 4) { //data is retrieved from server
                if (req.status == 200) { // which represents ok status
                    s.innerHTML=req.responseText;
                }
                else
                {
                    alert("There was a problem while using XMLHTTP:\n");
                }
            }
        }
        req.open("GET", strURL, true); //open url using get method
        req.send(null);
    }
}
-->
</script>