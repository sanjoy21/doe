<?php
require_once('mysql.php');

if( isset($search) && $search ) {
    if( isset($overrideiscorp) && $overrideiscorp ) {
        $campusid = db_query_first_cell( "select campusid from company_esi where id = $companyid " );
        $extrajoin = ", company_esi ";
        $extrawhere = " and clientid = company_esi.id and campusid = $campusid";
    } else {
        $extrajoin = "";
        $extrawhere = "";
    }
    
    if( isset($overrideiscorp) && $overrideiscorp ) {
        // No additional condition for overrideiscorp
    } else {
        $extrawhere .= " and ( pmsidvalidated = 1 or emptype in ( 'Charter School Employee', 'SSA', 'Custodial Staff', 'Non DOE' ) )";
    }

    $sql = "select responders_esi.* from ( responders_esi $extrajoin ) where 1 $extrawhere" ;
    if( isset($lastname) && $lastname ) {
        $sql .= " and lastname like '%$lastname%'" ;
    }
    if( isset($firstname) && $firstname ) {
        $sql .= " and firstname like '%$firstname%'" ;
    }
    if( isset($filenumber) && $filenumber ) {
        $sql .= " and filenumber = '$filenumber'" ;
    }
    if( isset($pmsid) && $pmsid ) {
        $sql .= " and pmsid = '$pmsid'" ;
    }
    $sql .= " order by lastname, firstname";
//    echo( $sql );
    $rows = db_query_rows( $sql );
}

if( isset($add) && $add && isset($is_added) && $is_added ) {
    if( isset($session_iscorp) && $session_iscorp ) {
        $iscorp = $session_iscorp;
    } else {
        $iscorp = '';
    }
    
    $alreadyinclass = db_query_first_cell( " select responderid from responder_to_class r, class c where c.id = r.classid and startdate > now() and c.deleted = 0 and responderid = ".$is_added );
    if( $alreadyinclass && !$iscorp ) {
        $is_added = 0;
        $confirm = "<div id='error'>This person is already in an upcoming class.</div>";
    } else {
        $arow = getResponderRow( $is_added );
        $firstname = isset($arow["firstname"]) ? $arow["firstname"] : '';
        $lastname = isset($arow["lastname"]) ? $arow["lastname"] : '';
        $filenumber = isset($arow["filenumber"]) ? $arow["filenumber"] : '';
        $pmsid = isset($arow["pmsid"]) ? $arow["pmsid"] : '';
    }
}
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<title>ESI: Add Attendee</title>

<META NAME="Keywords" CONTENT="">

<META NAME="Description" CONTENT="">

<script LANGUAGE="JavaScript">
<!---------- JavaScript begins...

function ChangeImage (ImageName,FileName) {
document[ImageName].src = FileName;
}

// JavaScript ends ---------->
</script>

<style TYPE="text/css">

BODY {margin:20}

</style>

<link rel="stylesheet" href="css/style.css">

</head>

<body bgcolor="#ffffff" marginwidth="20" marginheight="20">
<form method="post">
<input type="hidden" name="action" value="add">
<span class="copy">
<strong><span class="title">Add Attendee</span></strong><p>
     <font color='red'><b><?php echo isset($confirm) ? $confirm : ''; ?></b></font>
<table cellpadding="0" cellspacing="4" border="0">        
<tr>
<td valign="middle"><span class="copy">Search:</span></td>
</tr>
<tr>
<td valign="middle"><span class="copy">Last Name:</span><br><input name="lastname" value="<?php echo isset($lastname) ? htmlentities($lastname, ENT_QUOTES) : ''; ?>" type="text" id="" size="40" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<tr>
<td valign="middle"><span class="copy">First Name:</span><br><input name="firstname" value="<?php echo isset($firstname) ? htmlentities($firstname, ENT_QUOTES) : ''; ?>" type="text" id="" size="40" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<?php if( isset($iscorp) && $iscorp ) { ?>
<tr>
<td valign="middle"><span class="copy">File Number:</span><br><input name="filenumber" value="<?php echo isset($filenumber) ? htmlentities($filenumber, ENT_QUOTES) : ''; ?>" type="text" id="" size="8" maxlength="8" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<?php } else { ?>
<tr>
<td valign="middle"><span class="copy"><?php echo getSchoolStr( "PMS ID" ); ?>:</span><br><input name="pmsid" value="<?php echo isset($pmsid) ? htmlentities($pmsid, ENT_QUOTES) : ''; ?>" type="text" id="" size="8" maxlength="8" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<?php } ?>
<tr>
<td valign="middle"><input type="submit" name="search" value="Search"></td>
</tr>
</table>
</span>

<?php if( isset($rows) && $rows ) { ?>
<table>
<?php foreach( $rows as $r ) {  ?>
                             <tr>
                             <td class='copy'><input type='radio' name='is_added' value='<?php echo isset($r["responderid"]) ? $r["responderid"] : ''; ?>'></td>
                             <td class='copy'><?php echo isset($r["lastname"]) ? htmlentities($r["lastname"], ENT_QUOTES) : ''; ?>, <?php echo isset($r["firstname"]) ? htmlentities($r["firstname"], ENT_QUOTES) : ''; ?> </td>
                             <td class='copy'><?php echo isset($r["clientid"]) ? getCompanyName( $r["clientid"] ) : ''; ?></td>
<!-- <td class='copy'><?php echo isset($r["filenumber"]) ? htmlentities($r["filenumber"], ENT_QUOTES) : ''; ?></td>
<td class='copy'><?php echo isset($r["pmsid"]) ? htmlentities($r["pmsid"], ENT_QUOTES) : ''; ?></td>-->
                    
</tr>                             
<?php } ?>
</table>
<input type='submit' name='add' value='Add Selected'  class='copy'>
<?php } else if( isset($search) && $search ) { ?>
No results matched your search.    <a href='add_attendee.php?<?php echo isset($_SERVER["QUERY_STRING"]) ? htmlentities($_SERVER["QUERY_STRING"], ENT_QUOTES) : ''; ?>'>Click here to add attendee</a>
<?php } ?>
<?php if (isset($is_added) && $is_added) { ?>
<script type="text/javascript">
window.opener.addMyOption( "<?php echo isset($lastname) ? addslashes($lastname) : ''; ?>, <?php echo isset($firstname) ? addslashes($firstname) : ''; ?> (#<?php echo isset($pmsid) && $pmsid ? addslashes($pmsid) : (isset($filenumber) ? addslashes($filenumber) : ''); ?>)",<?php echo $is_added; ?> );
setTimeout('window.close()', 1000);
</script>
<?php } ?>

</form>
</body>
</html>