<?php
include "mysql.php";
require_once('services.php');


$responderid = $_REQUEST['responderid'] ?? 0;
$id = $_REQUEST['id'] ?? 0;
$hasjobtitleautocomplete = 1;
// ------------------------------------

if( $delrdate )
{
    db_query( "delete from responder_training_dates where id = $delrdate" );
}
if( $update|| $updatereturn )
{
    if( $responderid )
    {
        $responder_row = getResponderRow( $responderid );
        $id = $responder_row['clientid'];
        $company_row = getCompanyRow( $id );

        $extpms = "";
        if( empty($company_row['iscorp']) )
        {
            if( $pmsid )
            {
                $pmsidvalidated = validateEmployee( $pmsid, $lastname, "editresponder" );
                $extpms = ", pmsidvalidated = '$pmsidvalidated', lastpmsvalidated = now()";
            }
            else
                $extpms = ", pmsidvalidated = '0'";
                
        }
        
        $fixed_date = fixdatefordb( $cardrenewaldate, true );
        db_query( "update responders_esi set filenumber='$filenumber', approvalcode='$approvalcode', pmsidinactive='$pmsidinactive', buildingcode='$buildingcode', pmsid='$pmsid' $extpms, firstname='$firstname', notes='$notes', maidenname='$maidenname', schoolno='$schoolno', lastname='$lastname',title='$title',floor='$floor',dayphone='$dayphone',homeaddress='$homeaddress',apt='$apt',city='$city',state='$state',zip='$zip',email='$email',date=Now(),cardrenewaldate='$fixed_date',dayphoneExtension='$dayphoneExtension' where responderid=$responderid");
        if( $newschoolno )
        {
            $schoolid = db_query_first_cell( "select clientid from responders_esi where responderid = $responderid" );
            db_query( "insert into oldresponderschools ( responderid, clientid, movedate ) values ( '$responderid', '$schoolid', Now()) " );
            $bcode = db_query_first_cell( "select buildingcode from location_to_building ltb, company_esi c where c.locationcode = ltb.locationcode and id = '$newschoolno'" );
            db_query( "update responders_esi set clientid = '$newschoolno', buildingcode = '$bcode' where responderid=$responderid" );
        }
        
    }
    else
    {
        // Removed pmsidvalidated variable if not set in insert scope (assuming it's calculated or defaulted)
        $pmsidvalidated = isset($pmsidvalidated) ? $pmsidvalidated : 0;
        $responderid = db_query_insert_id( "insert into responders_esi (pmsidinactive, clientid, buildingcode, approvalcode, filenumber, pmsid, pmsidvalidated, firstname, maidenname, notes, lastname, title, schoolno, floor, dayphone, homeaddress, apt, city, state, zip, email, date, deleted, dayphoneExtension, raddedby, raddeddate) values ('$pmsidinactive', '$id','$buildingcode','$approvalcode','$filenumber','$pmsid','$pmsidvalidated','$firstname','$maidenname','$notes','$lastname','$title','$schoolno','$floor','$dayphone','$homeaddress','$apt','$city','$state','$zip','$email',Now(),'$deleted','$dayphoneExtension', '$session_id', now()) ");
    }
    $sql = "update responders_esi set busbldg = '$busbldg', emptype='$emptype',  busroom = '$busroom' , busfloor = '$busfloor' , buszip = '$buszip' , buscity = '$buscity' , busstate = '$busstate' , busaddress = '$busaddress', iscoach = '$iscoach' where responderid=$responderid";
    db_query( $sql );

    if( $newtrainingdate )
    {
        $tm = date( "Y-m-d", strtotime( $newtrainingdate ) );
        db_query( "insert into responder_training_dates ( responderid, trainingdate, program ) values ( '$responderid', '$tm', '$newclasstype' )" );
    }

    $responder_row = getResponderRow( $responderid );
    $id = $responder_row["clientid"];
    $company_row= getCompanyRow( $id );

    if( empty($company_row["iscorp"]) && $pmsid )
        {
        $arow = getResponderRow( $responderid );
        updateResponder( $arow );
        //        exit;
        }

//    if( $redirect )
    if( $updatereturn )
        {
        header( "location: $redirect " );
        exit;
        }
}



if( $delete )
{
    db_query( "update responders_esi set deleted = 1, deletiondate = Now() where responderid = $responderid " );
//    if( $redirect )
        header( "location: $redirect " );
        exit;
}

//get info for the form
if( $responderid )
{
    $responder_row = getResponderRow( $responderid );
    $id = $responder_row["clientid"];
}
if( empty($responder_row) )
{
    // Initialize array keys to empty string to avoid undefined index warnings in HTML values
    $responder_row = array_fill_keys([
        'responderid', 'firstname', 'maidenname', 'emptype', 'approvalcode', 'lastname', 'filenumber', 'pmsid', 
        'pmsidvalidated', 'lastpmsvalidated', 'lastupdateresult', 'lastupdatedate', 'title', 'iscoach', 
        'dayphone', 'dayphoneExtension', 'homeaddress', 'apt', 'city', 'state', 'zip', 'busaddress', 
        'busbldg', 'busfloor', 'busroom', 'buscity', 'busstate', 'buszip', 'email', 'buildingcode', 'pmsidinactive', 'notes'
    ], '');
}

$company_row = getCompanyRow( $id );
$session_iscorp = $company_row["iscorp"];
?>

<?php
$noleftnav = 1;
 include "ssi/top.php"; ?>      

<script LANGUAGE="JavaScript1.1" SRC="/FormChek.js"></script>

<script language="JavaScript">
   
    function validateUSPersonalInfo(form) {
        if (checkString(form.elements["firstname"], "First name") == false) {
            return false
        }
        if (checkString(form.elements["lastname"], "Last name") == false) {
            return false
        }
        if (form.elements["dayphone"].value.length > 1) {
            if (checkUSPhone(form.elements["dayphone"], "Phone number") == false) {
                return false
            }
            return true;
        }
    }

</script>

<script language="JavaScript">

function validRequired(formField,fieldLabel)
{
    var result = true;
    
    if (formField.value == "")
    {
        alert('Please enter a value for the "' + fieldLabel +'" field.');
        formField.focus();
        result = false;
    }
    
    return result;
}

function allDigits(str)
{
    return inValidCharSet(str,"0123456789");
}

function inValidCharSet(str,charset)
{
    var result = true;
    
    for (var i=0;i<str.length;i++)
        if (charset.indexOf(str.substr(i,1))<0)
        {
            result = false;
            break;
        }
    
    return result;
}

function isValidShortDate(formField,fieldLabel,required)
{
    if (required && (formField.value.length>7))
    {
        alert('Please enter a date in the format MM/YYYY for the "' + fieldLabel +'" field.');
        formField.focus();
        return false;
    }
    var result = true;
    var formValue = formField.value;

    if (required && !validRequired(formField,fieldLabel))
        result = false;
  
    if (result && (formField.value.length>0))
    {
        var elems = formValue.split("/");
        
        result = (elems.length == 2); // should be two components
        var expired = false;
        
        if (result)
        {
            var month = parseInt(elems[0],10);
            var year = parseInt(elems[1],10);
            
            if (elems[1].length == 2)
                year += 2000;
            
            var now = new Date();
            
            var nowMonth = now.getMonth() + 1;
            var nowYear = now.getFullYear();
            
            
            
            result = allDigits(elems[0]) && (month > 0) && (month < 13) &&
                     allDigits(elems[1]) && ((elems[1].length == 2) || (elems[1].length == 4));
        }
        
        if (!result)
        {
            alert('Please enter a date in the format MM/YYYY for the "' + fieldLabel +'" field.');
            formField.focus();
        }
    } 
    return result;
}
</script>





<script language="JavaScript">
function FormatDate(InpDate,field)
{
if(InpDate == "")
{
     return true;
}
else if(InpDate.length<10)
{
    alert("Enter the "+field+" field as MM/DD/YYYY");
    return false;
}
else
   {
      if(InpDate.length < 6) 
    {
     alert("Enter Correct "+field)
     return false;
    }
      else
      {
        InpVal = InpDate;
        SendNext = "False";
        PrevSlash = "";
        LastSlash = "";
        for(i = 0;i <= InpDate.length-1;i++)
        {
          PrevSlash = LastSlash;
          LastSlash = InpDate.substring(i,i+1);
        if((LastSlash == '/' && i == 0 ))
            {
                alert("Your "+field+" Format is incorrect");
                return false;
                 break;    
            }
          if ((LastSlash == '/' || LastSlash == '.') && (PrevSlash == '.' || PrevSlash == '/'))
            {
                alert("Your "+field+" Format is incorrect");
                return false;
                 break;
            }
          else 
            {
                if (LastSlash == '/' || LastSlash == '.') 
                SendNext = "True";
            } 
       } 
    if (SendNext == "True") 
        {
            lBool="False";
            LMonth="False";
            LDate="False";
            var OutVal;
            var InVal;
            var RoundYear;
            var Mon;
            var LastSlashNumber;
            var j;
            var k;
            var x;
            x = InpDate;
            OutVal="";
            TotVal="";
            LastSlashNumber=0;
            for(i = 0;i <= x.length-1;i++) 
               {
                  LastSlash = x.substring(i,i+1);
                  if (LastSlash != 0 && LastSlash != 1 && LastSlash != 2 && LastSlash != 3 && LastSlash != 4 && LastSlash != 5 && LastSlash != 6 && LastSlash != 7 && LastSlash != 8 && LastSlash != 9 && LastSlash != '/' && LastSlash != '.') //fifth if
                    {
                        lBool="True";
                        break;
                    }
                  else 
                    {
                      if (LastSlash == '/' || LastSlash == '.' || LastSlashNumber == 2 ) //sixth if
                          {
                            InVal = OutVal;
                            if (InVal == '0' || InVal == '00' || InVal == '0000') //seventh if
                                {
                                    alert("You entered Some Zero's in the field(Month/Date/Year)")
                                    return false;
                                    break;
                                }
                                TotVal = TotVal+InVal
                                OutVal="";
                                   if (LastSlashNumber == 0  )
                                    {
                                        LastSlashNumber  = LastSlashNumber + 1;
                                        Mon = InVal;
                                        if (InVal > 12)
                                            {
                                                LMonth ="True";
                                                InVal="";
                                                break;
                                            }
                                    }
                               else
                                    {
                                           if(LastSlashNumber == 1)
                                            {
                                                Dat = InVal;
                                                LastSlashNumber  = LastSlashNumber + 1;
                                                if(Mon == '01' || Mon == '1' || Mon == '03' || Mon == '3' || Mon == '05' || Mon == '5' || Mon == '07' || Mon == '7' || Mon == '08' || Mon == '8' || Mon == '10' || Mon == '12')
                                                    {
                                                          if (InVal > 31)
                                                            {
                                                                  LDate ="True";
                                                                   InVal="";
                                                                  break;
                                                            }
                                                    }
                                                else
                                                       {
                                                        if(Mon == '04' || Mon == '4' || Mon == '06' || Mon == '6' || Mon == '09' || Mon == '9' || Mon == '11')
                                                           {
                                                                if (InVal > 30)
                                                                    {
                                                                          LDate ="True";
                                                                           InVal="";
                                                                          break;
                                                                    }
                                                            }        
                                                          }
                                            }
                                    else
                                        {
                                            if(LastSlashNumber == 2 )
                                                   {
                                                    LastSlashNumber = LastSlashNumber + 1;
                                                    lYear = x.substring(TotVal.length+2,x.length);
                                                    if(lYear == '0000')
                                                        {
                                                            alert("You entered Some Zero's in the field(Month/Date/Year)")
                                                            return false;                        
                                                        }
                                                    else
                                                        {
                                                            if(lYear.length <= 3 )
                                                                {
                                                                    alert("Enter Four digits for "+field+" Year");
                                                                    return false;
                                                                    break;
                                                                }
                                                            else
                                                                {
                                                                       if(lYear.length > 4 )
                                                                        {
                                                                            alert("Enter Four digits for "+field+" Year");
                                                                            return false;
                                                                            break;
                                                                        }
                                                                       else
                                                                        {
                                                                             RoundYear = Math.round(lYear/4); 
                                                                             if (lYear/4 != RoundYear) 
                                                                                {
                                                                                    if(Mon == 2 && Dat > 28 )
                                                                                        {
                                                                                            alert("You entered More than 28 in the "+field+" field (it's not a leap year)");
                                                                                            return false;
                                                                                            break;
                                                                                        }
                                                                                    else
                                                                                    {
                                                                                        return true;
                                                                                    }
                                                                                   }
                                                                            else
                                                                                   {
                                                                                    if(Mon == 2 && Dat > 29)
                                                                                        {
                                                                                            alert("You entered More than 28 in the "+field+" field/month is febraury");
                                                                                            return false;
                                                                                            break;
                                                                                        }
                                                                                    else
                                                                                        {
                                                                                         return true;
                                                                                        }
                                                                                   }
                                                                        }
                                                                }
                                                           }
                                                }    
                                                else
                                                {
                                                    return true;
                                                }
                                            }
                                       }
                                }
                                       else
                                        OutVal=OutVal + LastSlash;
                            }
                        }
                    }
            else
                {
                    alert("You "+field+" format is incorrect");
                    return false;
                }
             }
    }
    if (lBool == "True")
        {
            alert("You entered some alpha value in the "+field+" field");
            return false;
        }
    else
        {
            if (LMonth == "True")
                {
                    alert("Your "+field+" is invalid");
                    return false;
                }
            else
                {
                    if (LDate == "True")
                        {
                            alert("Your "+field+" is invalid");
                            return false;
                        }
                    else
                        {
                        return true;
                        }
                }
        }
}
</script>


<?php
if( !$redirect )
    $redirect="/viewcompany.php?id=$id#resps";
?>

<form onsubmit="return validateUSPersonalInfo(this)"  method="post">
<input type="hidden" name="redirect" value="<?=$redirect;?>">
<input type="hidden" name="responderid" value="<?=$responder_row['responderid'];?>">
<input type="hidden" name="id" value="<?=$id;?>">
        
<?php if( $specialadmin ) { ?>
        <table cellpadding="5" cellspacing="1" border="0" width="100%">
            <tr>
                <td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="schools.php">&laquo; Back to Admin Main</a></strong></span></td>                
            </tr>
        </table>
<?php } ?>      
        <strong>THIS RESPONDER IS FOR:</strong><br>
<a href='viewcompany.php?id=<?=$company_row["id"]?>'><?=$company_row['companyname']."</a><br>".$company_row['address']."<br>".$company_row['floor']."<br>".$company_row['city'].", ".$company_row['state']." ".$company_row['zip']?>
        <?php if( $specialadmin && $responderid ) { ?>
    <a href='editresponder.php?setnew=1&responderid=<?=$responderid?>'>Set New <?=getSchoolStr( "School" )?></a>
<?php if( $setnew ) { ?>
Search: <input type='text' name='substr' class='copy' value="<?=$substr?>"><input type='submit' name='search' class=copy value='Search'><br>
                                                 <select name='newschoolno' class=copy>
                                 <option value=''></option>
                                 <?php 
        $whr = "";
        if( $substr )
            $whr = " and ( companyname like '%$substr%' or schoolno like '%$substr%'  or schoolcode like '%$substr%' ) ";

$sql = "SELECT id, companyname, borough, schoolcode, concat( schoolcode, companyname ) as longname FROM company_esi where iscorp = '{$company_row['iscorp']}' and deleted = 0 $whr order by longname";
$schools = db_query_rows($sql);
foreach ($schools as $school) {
  $companyid = $school["id"];
  $school_name = $school["schoolcode"] . " (". $school["companyname"] . ")";
  // PHP 8.2 fix: avoid dynamic variables if not set, assume empty
  $sel_attr = isset(${"selected_".$companyid}) ? ${"selected_".$companyid} : '';
  echo '<option '.$sel_attr.' value="'.$companyid.'">'.htmlentities( $school_name, ENT_QUOTES ).'</option>';
}
                                 
                                 ?>
                                 </select>
                                 <?php } ?>
                                 <?php } ?>
        <br>
        <br>
        <table cellpadding="5" cellspacing="1" border="0" width="100%">
            <tr>
                <td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><strong>Responder Information</strong></span></td>                
            </tr>   
            <tr>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>First Name*:</strong><br><input type="text" size="40" VALUE="<?=$responder_row['firstname'];?>" maxlength="50" name="firstname" style="font-size: 10px;  font-family: verdana;"></span>
<br><Br>
<span class="copy"><strong>Maiden Name (if applicable):</strong><br><input type="text" size="40" VALUE="<?=$responder_row['maidenname'];?>" maxlength="50" name="maidenname" style="font-size: 10px;  font-family: verdana;"></span><Br><b>Emp Type:</b> <input type='text' name='emptype' value='<?=$responder_row['emptype']?>'><br>
<?php if( empty($company_row["iscorp"]) ) { ?><b>Approval Code:</b><input type='text' name='approvalcode' value='<?=$responder_row['approvalcode']?>' size=8 style="file-size: 10px; font-family: verdana;"><br>
<?php } ?>
</td>
    <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Last Name*:</strong><br><input type="text" size="30" VALUE="<?=$responder_row['lastname'];?>" maxlength="50" name="lastname" style="font-size: 10px;  font-family: verdana;"> <b>File Number</b>: <input type='text' name='filenumber' value='<?=$responder_row['filenumber']?>' size=5 style="file-size: 10px; font-family: verdana;"><br>
<b><?=getSchoolStr( "PMS ID", $company_row["iscorp"])?></b>: <input type='text' name='pmsid' value='<?=$responder_row['pmsid']?>' size=8 style="file-size: 10px; font-family: verdana;"></span>    <?php if( empty($company_row["iscorp"]) ) { ?> <b>Validated:</b> <?=$responder_row['pmsidvalidated']?"Yes":"No"?> <br><b>Last PRN Validation Date:</b> <?=$responder_row['lastpmsvalidated']?>
<?php if( isOverallAdmin() ) { ?><br>
<b>Last PRN Update Result</b>: <?=getEmployeeLog( "pms".$responder_row['pmsid'] )?><br>
<br><b>Last Update Of Expiration Date</b>: <?=$responder_row['lastupdateresult']?> at <?=$responder_row['lastupdatedate']?>
<?php } ?>
<?php } ?>
</td>
            </tr>
            
            
            <tr>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Title/Dept:</strong><br><input type="text" size="40" VALUE="<?=$responder_row['title'];?>" id="autocompletetitle" maxlength="50" name="title" style="font-size: 10px;  font-family: verdana;">
    <?php if( empty($company_row["iscorp"]) ) { ?>
<?php if( isOverallAdmin() ) { ?>
<br><b>Is Coach: <input type='checkbox' name='iscoach' value='1' <?=$responder_row['iscoach']?"CHECKED":""?>>
                            <?php } else { ?>
<input type="hidden" name="iscoach" value="<?=$responder_row['iscoach']?>">
                                <?php } ?>
                                <?php } ?>
    </span>
    </td>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Daytime Phone:</strong><br><span class="small"><em>Please enter as 111-222-3333</em></span><br><input type="text" size="40" VALUE="<?=$responder_row['dayphone'];?>" maxlength="50" name="dayphone" style="font-size: 10px;  font-family: verdana;"> Ext. <input type="text" size="4" VALUE="<?=$responder_row['dayphoneExtension'];?>" maxlength="50" name="dayphoneExtension" style="font-size: 10px;  font-family: verdana;"></span></td>
            </tr>
            
            <tr>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Home Address:</strong><br><input type="text" size="40" VALUE="<?=$responder_row['homeaddress'];?>" maxlength="50" name="homeaddress" style="font-size: 10px;  font-family: verdana;"></span></td>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Floor/Apt #:</strong><br><input type="text" size="40" VALUE="<?=$responder_row['apt'];?>" maxlength="50" name="apt" style="font-size: 10px;  font-family: verdana;"></span></td>
            </tr>
            
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan="2">
                
                    <table border="0" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td valign="top"><span class="copy"><strong>City:</strong><br><input type="text" size="40" VALUE="<?=$responder_row['city'];?>" maxlength="50" name="city" style="font-size: 10px;  font-family: verdana;"></span></td>
                            <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>State:</strong><br>
                
                <select name="state" style="font-size: 10px;  font-family: verdana;">
                <option value="<?=$responder_row['state'];?>"><?=$responder_row['state'];?></option>
                        <option value="Other">Other</option>
                <option value="Alabama">Alabama</option>
                <option value="Alaska">Alaska</option>
                <option value="Arizona">Arizona</option>
                <option value="Arkansas">Arkansas</option>
                <option value="California">California</option>
                <option value="Colorado">Colorado</option>
                <option value="Connecticut">Connecticut</option>
                <option value="Delaware">Delaware</option>
                <option value="DC">District of Columbia</option>
                <option value="Florida">Florida</option>
                <option value="Georgia">Georgia</option>
                <option value="Hawaii">Hawaii</option>
                <option value="Idaho">Idaho</option>
                <option value="Illinois">Illinois</option>
                <option value="Indiana">Indiana</option>
                <option value="Iowa">Iowa</option>
                <option value="Kansas">Kansas</option>
                <option value="Kentucky">Kentucky</option>
                <option value="Louisiana">Louisiana</option>
                <option value="Maine">Maine</option>
                <option value="Maryland">Maryland</option>
                <option value="Massachusetts">Massachusetts</option>
                <option value="Michigan">Michigan</option>
                <option value="Minnesota">Minnesota</option>
                <option value="Mississippi">Mississippi</option>
                <option value="Missouri">Missouri</option>
                <option value="Montana">Montana</option>
                <option value="Nebraska">Nebraska</option>
                <option value="Nevada">Nevada</option>
                <option value="New Hampshire">New Hampshire</option>
                <option value="New Jersey">New Jersey</option>
                <option value="New Mexico">New Mexico</option>
                <option value="New York">New York</option>
                <option value="North Carolina">North Carolina</option>
                <option value="North Dakota">North Dakota</option>
                <option value="Ohio">Ohio</option>
                <option value="Oklahoma">Oklahoma</option>
                <option value="Oregon">Oregon</option>
                <option value="Pennsylvania">Pennsylvania</option>
                <option value="Rhode Island">Rhode Island</option>
                <option value="South Carolina">South Carolina</option>
                <option value="South Dakota">South Dakota</option>
                <option value="Tennessee">Tennessee</option>
                <option value="Texas">Texas</option>
                <option value="Utah">Utah</option>
                <option value="Vermont">Vermont</option>
                <option value="Virginia">Virginia</option>
                <option value="Washington">Washington</option>
                <option value="West Virginia">West Virginia</option>
                <option value="Wisconsin">Wisconsin</option>
                <option value="Wyoming">Wyoming</option>
                <option value="Alberta">Alberta</option>
                <option value="British Columbia">British Columbia</option>
                <option value="Manitoba">Manitoba</option>
                <option value="New Brunswick">New Brunswick</option>
                <option value="Newfoundland">Newfoundland</option>
                <option value="Northwest Territories">Northwest Territories</option>
                <option value="Nova Scotia">Nova Scotia</option>
                <option value="Nunavut">Nunavut</option>
                <option value="Ontario">Ontario</option>
                <option value="Prince Edward Island">Prince Edward Island</option>
                <option value="Quebec">Quebec</option>
                <option value="Saskatchewan">Saskatchewan</option>
                <option value="Yukon">Yukon</option>
            </select></span></td>                                                      
            <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Zip:</strong><br><input type="text" size="10" VALUE="<?=$responder_row['zip'];?>" maxlength="50" name="zip" style="font-size: 10px;  font-family: verdana;"></span>
            </td>
            </tr>
            </table>
            </td>
            </tr>

<?php if( !empty($company_row["iscorp"]) ) { ?>
            <tr>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Business Address:</strong><br><input type="text" size="40" VALUE="<?=$responder_row['busaddress'];?>" maxlength="50" name="busaddress" style="font-size: 10px;  font-family: verdana;"></span></td>
                <td valign="middle" bgcolor="#E2DFDF"><span class="copy"><strong>Bldg #:</strong><input type="text" size="5" VALUE="<?=$responder_row['busbldg'];?>" maxlength="50" name="busbldg" style="font-size: 10px;  font-family: verdana;">
<span class="copy"><strong>Floor #:</strong><input type="text" size="5" VALUE="<?=$responder_row['busfloor'];?>" maxlength="50" name="busfloor" style="font-size: 10px;  font-family: verdana;"></span>
<span class="copy"><strong>Room #:</strong><input type="text" size="5" VALUE="<?=$responder_row['busroom'];?>" maxlength="50" name="busroom" style="font-size: 10px;  font-family: verdana;"></span>
</td>
            </tr>
            
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan="2">
                
                    <table border="0" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td valign="top"><span class="copy"><strong>City:</strong><br><input type="text" size="40" VALUE="<?=$responder_row['buscity'];?>" maxlength="50" name="buscity" style="font-size: 10px;  font-family: verdana;"></span></td>
                            <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>State:</strong><br>
                
                <select name="busstate" style="font-size: 10px;  font-family: verdana;">
                <option value="<?=$responder_row['busstate'];?>"><?=$responder_row['busstate'];?></option>
                        <option value="Other">Other</option>
                <option value="Alabama">Alabama</option>
                <option value="Alaska">Alaska</option>
                <option value="Arizona">Arizona</option>
                <option value="Arkansas">Arkansas</option>
                <option value="California">California</option>
                <option value="Colorado">Colorado</option>
                <option value="Connecticut">Connecticut</option>
                <option value="Delaware">Delaware</option>
                <option value="DC">District of Columbia</option>
                <option value="Florida">Florida</option>
                <option value="Georgia">Georgia</option>
                <option value="Hawaii">Hawaii</option>
                <option value="Idaho">Idaho</option>
                <option value="Illinois">Illinois</option>
                <option value="Indiana">Indiana</option>
                <option value="Iowa">Iowa</option>
                <option value="Kansas">Kansas</option>
                <option value="Kentucky">Kentucky</option>
                <option value="Louisiana">Louisiana</option>
                <option value="Maine">Maine</option>
                <option value="Maryland">Maryland</option>
                <option value="Massachusetts">Massachusetts</option>
                <option value="Michigan">Michigan</option>
                <option value="Minnesota">Minnesota</option>
                <option value="Mississippi">Mississippi</option>
                <option value="Missouri">Missouri</option>
                <option value="Montana">Montana</option>
                <option value="Nebraska">Nebraska</option>
                <option value="Nevada">Nevada</option>
                <option value="New Hampshire">New Hampshire</option>
                <option value="New Jersey">New Jersey</option>
                <option value="New Mexico">New Mexico</option>
                <option value="New York">New York</option>
                <option value="North Carolina">North Carolina</option>
                <option value="North Dakota">North Dakota</option>
                <option value="Ohio">Ohio</option>
                <option value="Oklahoma">Oklahoma</option>
                <option value="Oregon">Oregon</option>
                <option value="Pennsylvania">Pennsylvania</option>
                <option value="Rhode Island">Rhode Island</option>
                <option value="South Carolina">South Carolina</option>
                <option value="South Dakota">South Dakota</option>
                <option value="Tennessee">Tennessee</option>
                <option value="Texas">Texas</option>
                <option value="Utah">Utah</option>
                <option value="Vermont">Vermont</option>
                <option value="Virginia">Virginia</option>
                <option value="Washington">Washington</option>
                <option value="West Virginia">West Virginia</option>
                <option value="Wisconsin">Wisconsin</option>
                <option value="Wyoming">Wyoming</option>
                <option value="Alberta">Alberta</option>
                <option value="British Columbia">British Columbia</option>
                <option value="Manitoba">Manitoba</option>
                <option value="New Brunswick">New Brunswick</option>
                <option value="Newfoundland">Newfoundland</option>
                <option value="Northwest Territories">Northwest Territories</option>
                <option value="Nova Scotia">Nova Scotia</option>
                <option value="Nunavut">Nunavut</option>
                <option value="Ontario">Ontario</option>
                <option value="Prince Edward Island">Prince Edward Island</option>
                <option value="Quebec">Quebec</option>
                <option value="Saskatchewan">Saskatchewan</option>
                <option value="Yukon">Yukon</option>
            </select></span></td>                                                      
            <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Zip:</strong><br><input type="text" size="10" VALUE="<?=$responder_row['buszip'];?>" maxlength="50" name="buszip" style="font-size: 10px;  font-family: verdana;"></span>
            </td>
            </tr>
            </table>
            </td>
            </tr>
<?php } ?>
            
            <tr>
                <td valign="top" bgcolor="#E2DFDF"><span class="copy"><strong>Responder Email:</strong><br><input type="text" size="40" VALUE="<?=$responder_row['email'];?>" maxlength="50" name="email" style="font-size: 10px;  font-family: verdana;"></span></td>
<td valign="top" bgcolor="#E2DFDF">
    <?php if( empty($company_row["iscorp"]) ) { ?>
<b>Building:</b>
                                     <?=getBuildingPulldown( $id, $responder_row["buildingcode"], "buildingcode", 'style="width:400px; font-size: 10px;  font-family: verdana;"', 1 )?>
<br><b>Inactive in LCGMS: <input type='checkbox' name='pmsidinactive' value='1' <?=$responder_row['pmsidinactive']?"CHECKED":""?>>
                                     <?php } else {?>&nbsp; <?php } ?>
</td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan='2'><span class="copy"><strong>Notes:</strong><br><textarea cols="80" name='notes'><?=$responder_row['notes']?></textarea></span></td>
            </tr>
            <tr><td colspan='2' bgcolor=#E2DFDF>
<span class='copy'><strong>Certification History</strong></span><br>
<span class='copy'>
<table border=1 width='50%'>
                <tr><Th class='copy'>Class date</th><th class='copy'>Class </th></tr>
    <?php
                $tdates = [];
                if( $responderid )
                    $tdates = getTrainingDates( $responder_row["responderid"] );
foreach( $tdates as $t )
{
    echo( "<tr><td class='copy'>{$t['trainingdate']}</td>" );
    if( $t["classid"] )
    {   
        echo( "<td class='copy'><a href='class_detail.php?id={$t['classid']}'>View Class</a></td>" );
    }
    else
    {
        echo( "<td class='copy'>Data History " );
        if( $t["tprogram"] )
        { 
            if( isset($class_names[$t["tprogram"]]) )
                echo( " (".$class_names[$t["tprogram"]].")" );
            else
                echo( " (".$t["tprogram"].")" );
        }
        echo( "</td>" );
    }
    // Assumes $session_userid is available globally or fetched via session
    if( isset($session_userid) && $session_userid == "sarahg@emergencyskills.com" )
        echo( "<td class='copy'><a href='editresponder.php?id=$id&responderid=$responderid&delrdate=".$t["tid"]."'>Remove</a></td>" );  
    echo( "</tr>" );
}
?>
</table>
<br>Add new Training Date: <input type='text' name='newtrainingdate' class='copy' size='12' value=''>
                <select name="newclasstype" class='copy'>
        <?php if (isset($class_names)) { 
            foreach ($class_names as $code => $name) { ?>
            <option value="<?=$code?>"><?=$name?></option>
                <?php } 
        } ?>
            <option value="Non ESI">Non ESI Training</option>
                </select>
</td></tr>  
<?php                 
    $tdates = [];
    if( $responderid )
        $tdates = getPreviousSchools( $responder_row["responderid"] );
    
    if( count( $tdates ) ) 
{
?>
            <tr><td colspan='2' bgcolor=#E2DFDF>
<span class='copy'><strong>Previous Schools</strong></span><br>
<span class='copy'>
<table border=1 width='50%'>
                <tr><Th class='copy'><?=getSchoolStr( "School" )?></th><th class='copy'>Date Moved</th></tr>
    <?php
foreach( $tdates as $t )
{
    echo( "<tr><td class='copy'><a href='viewcompany.php?id={$t['clientid']}'>".getCompanyName( $t["clientid"] )."</a></td>" );
    echo( "<td class='copy'>{$t['movedate']}</td>" );  
    echo( "</tr>" );
}
?>
</table>
</td></tr>  
<?php } ?>
    
            <tr>
                <td valign="top" bgcolor="#FFFFFF" colspan="2">
                <br>
<?php if( !$readonly ) { ?>
                <div align="center">
                <input type="submit" name='updatereturn' value="&nbsp;&nbsp;&nbsp;&nbsp;Save and Return&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="submit" name='update' value="&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php if ($responder_row['responderid']) { ?>
                     <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
        <?php } ?>
                </div>
<?php } ?>
                </td>           
            </tr>   
        </table>
        
        <br><br>
        
        <?php include "ssi/footer.php"; ?>
        
        </span>
        </td>
        <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
    </tr>
</table>
<br><br>
</div>
</form>
</body>
</html>