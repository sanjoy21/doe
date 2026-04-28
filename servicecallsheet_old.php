<?php

include "mysql.php";

// Use isset() to check if the variable exists (e.g., from a form submission)
if (isset($newservicecall) && $newservicecall) {
    // Correcting SQL string interpolation and ensuring keys are quoted in array access.
    // WARNING: This remains highly vulnerable to SQL Injection. Use prepared statements!
    $newid = db_query_insert_id( 
        "insert into servicecall ( companyid, singleaedid ) values ( '{$companyid}', '{$aedid}' )" 
    );
    
    // Using header() in lowercase and braces for clear interpolation
    header("Location: servicecallsheet.php?companyid={$companyid}&servicecallid={$newid}");
    exit;
}

// Assume these helper functions return arrays/objects
$crow = getCompanyRow($companyid);
$sc = getServiceCallRow($servicecallid);

// --- HTML Output Starts Below ---
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Servicecall Inspection</title>

<style type="text/css">

/* P.breakhere {page-break-before: always}
body,td,th {
  font-family: Verdana, Arial, Helvetica, sans-serif;
  font-size: 11px;
}
.style1 {
  color: #5a179e;
  font-weight: bold;
  font-size: 12;
}
.style3 {font-size: 10px}
body {
  margin-top: 0px;
  margin-bottom: 0px;
} */

</style>

</head>

<body>

<table width="612" border="0"  cellspacing="0" cellpadding="0">
  <tr>
    <td><table width="600" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td><img src="images/esi_logo.gif" width="74" height="52" /></td>
        <td valign="bottom"><div align="left"><span class="style1">Emergency Skills, Inc.
          <?php if( !$crow['iscorp'] ) { ?>
          /NYC Department of Education<br /><?php } ?>
          AED Service Call <br />
        </span></div></td>
        <td width="120" valign="bottom"><div align="right"><span class="style1 style3">Service Call<br />
          # </span>
                <input name="Input3423" type="text" id="Input3423" size="10" maxlength="5" style="font-family: verdana; font-size: 11px; line-height: 13px" value='S<?= htmlspecialchars($servicecallid) ?>' />
        </div></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><img src="images/1x1.gif" width="1" height="10" /></td>
  </tr>
  <tr>
        <td><table width="600" cellpadding="0" cellspacing="1" border="0" bgcolor="#999999">
            <tr bgcolor="#e1e1f6">
              <td width="120" valign="top" bgcolor="#e1e1f6"><span class="small style3"><strong>Date/Time:</strong></span></td>
              <td valign="top" bgcolor="#e1e1f6"><span class="small style3"></span></td>
              <td width="120" valign="top" bgcolor="#e1e1f6"><span class="small style3"><strong><?= getSchoolStr("School") ?> Code:</strong></span></td>
              <td valign="top" bgcolor="#e1e1f6"><span class="small style3"><?= htmlspecialchars($crow["schoolcode"]) ?></span></td>
              <td width="85" valign="top" bgcolor="#e1e1f6"><span class="small style3"><strong>Phone #:</strong></span></td>
              <td width="210" valign="top" bgcolor="#e1e1f6"><span class="small style3"><?= $crow['iscorp'] ? htmlspecialchars($crow["contactphone"]) : htmlspecialchars($crow["schoolphone"]) ?></span></td>
            </tr>
            <tr bgcolor="#e1e1f6">
              <td width="120" valign="top" bgcolor="#e1e1f6"><span class="small style3"><strong><?= getSchoolStr("School") ?> Name:</strong></span></td>
              <td width="330" valign="top" bgcolor="#e1e1f6"><span class="small style3"><?= htmlspecialchars($crow["companyname"]) ?></span></td>
              <td width="85" valign="top" bgcolor="#e1e1f6"><span class="style3"><strong>Address:</strong></span></td>
              <td width="210" valign="top" bgcolor="#e1e1f6"><span class="small style3"><?= htmlspecialchars($crow["address"]) ?><br />
          <?= htmlspecialchars($crow["city"]) ?> <?= htmlspecialchars($crow["state"]) ?>, <?= htmlspecialchars($crow["zip"]) ?></span></td>
<td></td><td></td>
            </tr>
<?php if( !$crow["iscorp"] ) { ?>
            <tr bgcolor="#e1e1f6">
              <td width="120" valign="top" bgcolor="#e1e1f6"><span class="small style3"><strong>Principal Name: </strong></span></td>
              <td valign="top" bgcolor="#e1e1f6"><span class="small"><span class="style3"><?= htmlspecialchars($crow["principalname"]) ?></span></span></td>
              <td width="50" valign="top" bgcolor="#e1e1f6"><span class="style3"><strong> Email:</strong></span></td>
              <td width="100" valign="top" bgcolor="#e1e1f6"><?= htmlspecialchars($crow["principalemail"]) ?></td>
              <td width="50" valign="top" bgcolor="#e1e1f6"></td>
              <td width="100" valign="top" bgcolor="#e1e1f6">&nbsp;</td>
            </tr>
<?php } ?>
            <tr bgcolor="#e1e1f6">
              <td width="85" valign="top" bgcolor="#e1e1f6"><span class="small style3"><strong>AED Contact:</strong></span></td>
              <td width="210" valign="top" bgcolor="#e1e1f6"><?= htmlspecialchars($crow["contactname"]) ?></td>
              <td width="85" valign="top" bgcolor="#e1e1f6"><span class="small style3"><strong>Email:</strong></span></td>
              <td width="210" valign="top" bgcolor="#e1e1f6"><?= htmlspecialchars($crow["contactemail"]) ?></td>
              <td width="50" valign="top" bgcolor="#e1e1f6"></td>
              <td width="100" valign="top" bgcolor="#e1e1f6">&nbsp;</td>
            </tr>             
        </table></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>   
  <tr>
    <td>
<?php 
$allaeds = getAedRows($companyid);

// Using correct array key access: $sc['singleaedid']
if (isset($sc['singleaedid']) && $sc['singleaedid']) {
    $aeds = array( getAedRow($sc['singleaedid']) );
} else {
    $aeds = $allaeds;
}

$chunks = array_chunk($aeds, 5);
$cnt = 0;

foreach ($chunks as $myaeds) {
    $cnt++;
?>
  <table cellpadding="2" cellspacing="0" border="1" bgcolor="#999999">
      <tr>
        <td valign="top"><span class="small"><strong>Serial #:</strong></span></td>
<?php foreach($myaeds as $arow) { ?>
        <td bgcolor='#FFFFFF'>
          <?php 
            // Using correct array key access: $arow['aedmissing']
            if ($arow['aedmissing'] || $arow['outofservice']) { 
                echo "<font color='red'>"; 
            }
            echo htmlspecialchars($arow["serial"]);
            
            // Using correct array key access: $arow['newinstall']
            if ($arow['newinstall']) { 
                echo "(N)"; 
            }
          ?>
          </font>
        </td>
<?php } ?>
      </tr>
      <tr bgcolor="#e1e1f6">
        <td valign="top" width='150'><span class="style3">AED Unit <br />
          (no damage present)</span></td>
<?php foreach($myaeds as $arow) { ?>
        <td width='80' valign="top" bgcolor="#FFFFFF"><?= $arow['aedmissing'] ? "MISSING" : "" ?></td>
<?php } ?>
        </tr>
      <tr bgcolor="#e1e1f6">
        <td rowspan="4" valign="top"><span class="style3">Adult Pads <br />
          Expiration Date <br />
          Two Sets</span></td>
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#FFFFFF"><table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><span class="style3">Exp  Date:</span></td>
          </tr>
          <tr>
            <td><?= fixdatefordisplay($arow["padaexpiration"]) ?></td>
          </tr>
        </table></td>
<?php } ?>
      </tr>
      <tr bgcolor="#e1e1f6">
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#FFFFFF"><input type="checkbox" name="checkbox22172222" value="checkbox" />
          <span class="style3">Replaced</span></td>
<?php } ?>
      </tr>
      <tr bgcolor="#e1e1f6">
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#FFFFFF"><table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><span class="style3">New  Date:</span></td>
          </tr>
          <tr>
            <td>________</td>
          </tr>
        </table></td>
<?php } ?>
      </tr>
      <tr bgcolor="#e1e1f6">
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#FFFFFF"><nobr>Lot#: _________</nobr></td>
<?php } ?>
      </tr>
      <tr bgcolor="#e1e1f6">
        <td rowspan="<?= $crow['iscorp'] ? 5 : 4 ?>" valign="top"><span class="style3">Pediatric Pads <br />
          Expiration Date <br />
          One Set</span></td>
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#FFFFFF"><table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><span class="style3">Exp  Date:</span></td>
          </tr>
          <tr>
            <td><?= fixdatefordisplay($arow["pediatricpads"]) ?></td>
          </tr>
        </table></td>
<?php } ?>
      </tr>
<?php if( $crow['iscorp'] ) { ?>
      <tr bgcolor="#e1e1f6">
<?php foreach($myaeds as $arow) { ?>
            <td valign="top" bgcolor="#FFFFFF"><span class="style3"><input type='checkbox' <?= $arow['pediatrickey'] ? "CHECKED" : "" ?> name='pedkey'> Key?</span></td>
<?php } ?>
      </tr>
<?php } ?>
      <tr bgcolor="#e1e1f6">
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#FFFFFF"><input type="checkbox" name="checkbox22172222" value="checkbox" />
          <span class="style3">Replaced</span></td>
<?php } ?>
      </tr>
      <tr bgcolor="#e1e1f6">
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#FFFFFF"><table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><span class="style3">New  Date:</span></td>
          </tr>
          <tr>
            <td>________</td>
          </tr>
        </table></td>
<?php } ?>
      </tr>
      <tr bgcolor="#e1e1f6">
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#FFFFFF"><nobr>Lot#: _________</nobr></td>
<?php } ?>
      </tr>
      <tr bgcolor="#e1e1f6">
        <td valign="top"><table border="0" cellspacing="0" cellpadding="4">
            <tr>
              <td><span class="style3">Ancillary  supplies:</span></td>
            </tr>
            <tr>
              <td><ul class="style3">
                  <li>Towel</li>
                <li>Scissors</li>
                <li>Razor</li>
                <li>CPR mask</li>
                <li>Gloves</li>
              </ul></td>
            </tr>
        </table></td>
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#FFFFFF"><input type="checkbox" name="checkbox22172" value="checkbox" />
             <span class="style3">Replaced</span></td>
<?php } ?>
              </tr>
      <tr bgcolor="#e1e1f6">
        <td valign="top"><span class="style3">Store unopened <br />
          battery within <br />
          install before  date</span></td>
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#FFFFFF">
<?php if( $crow['iscorp'] ) { ?>
          Inst Date: <?= fixdatefordisplay($arow['sparedate']) ?><br>
<?php } ?>
          <input type="checkbox" name="checkbox22172" value="checkbox" />
          <span class="style3">Replaced</span></td>
<?php } ?>

        </tr>
      <tr bgcolor="#e1e1f6">
        <td valign="top"><span class="style3">PC data card undamaged</span></td>
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#FFFFFF"><input type="checkbox" name="checkbox221722227" value="checkbox" /></td>
<?php } ?>

        </tr>
      <tr bgcolor="#e1e1f6">
        <td valign="top" bgcolor="#e1e1f6"><span class="style3">Status Indicator:</span></td>
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#FFFFFF"><input type="checkbox" name="checkbox2217222212" value="checkbox" /></td>
<?php } ?>

        </tr>
      <tr bgcolor="#ffffff">
        <td valign="top" bgcolor="#e1e1f6"><span class="style3">Remarks, Problems, <br />
          Corrective             Actions:</span></td>
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#ffffff">&nbsp;</td>
<?php } ?>

        </tr>
      <tr bgcolor="#ffffff">
        <td valign="top" bgcolor="#e1e1f6"><span class="style3">Physical Location</span></td>
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#ffffff"><?= htmlspecialchars($arow["location"]) ?></td>
<?php } ?>

        </tr>
<?php if( $crow['iscorp'] ) { ?>
      <tr bgcolor="#ffffff">
        <td valign="top" bgcolor="#e1e1f6"><span class="style3">AED Floor</span></td>
<?php foreach($myaeds as $arow) { ?>
        <td valign="top" bgcolor="#ffffff"><?= htmlspecialchars($arow["floor"]) ?></td>
<?php } ?>

        </tr>
<?php } ?>
      <tr bgcolor="#ffffff">
        <td height="30" valign="top" bgcolor="#e1e1f6"><span class="style3">G2005 Update</span></td>
<?php foreach($myaeds as $arow) { ?>
        <td height="30" valign="top" bgcolor="#ffffff"><input type="checkbox" name="checkbox2217222217" value="checkbox"  <?= $arow['hasbeenupdated'] ? "CHECKED" : "" ?> /></td>
<?php } ?>
        </tr>
    </table>
</td>
  </tr>
<?php if( $cnt == count($chunks) ) { ?>
  <tr>
    <td><img src="images/1x1.gif" width="1" height="10" /></td>
  </tr>
  <tr>
    <td><table width="600" cellpadding="0" cellspacing="0" border="0" >
      <tr>
        <td height="30"><img src="images/1x1.gif" width="1" height="30" /></td>
        <td height="30">&nbsp;</td>
        <td height="30">&nbsp;</td>
      </tr>
      <tr>
        <td height="1"><img src="images/1x1black.gif" width="200" height="1" /></td>
        <td height="1"><img src="images/1x1black.gif" width="200" height="1" /></td>
        <td height="1"><img src="images/1x1black.gif" width="80" height="1" /></td>
      </tr>
      <tr>
        <td class="copy"><?= getSchoolStr("School") ?> Rep Signature</td>
        <td class="copy">Print Name</td>
        <td class="copy">Date</td>
      </tr>
      <tr>
        <td height="30" class="copy"><img src="images/1x1.gif" width="1" height="30" /></td>
        <td height="30" class="copy">&nbsp;</td>
        <td height="30" class="copy">&nbsp;</td>
      </tr>
      <tr>
        <td class="copy"><img src="images/1x1black.gif" width="200" height="1" /></td>
        <td class="copy"><img src="images/1x1black.gif" width="200" height="1" /></td>
        <td class="copy"><img src="images/1x1black.gif" width="80" height="1" /></td>
      </tr>
      <tr>
        <td class="copy">ESI Rep Signature</td>
        <td class="copy">Print  Name</td>
        <td class="copy">Date</td>
      </tr>
    </table>
<?php } else { ?>
  <tr>
    <td><img src="images/1x1.gif" width="1" height="10" /></td>
  </tr>
  <tr>
    <td><table width="600" cellpadding="0" cellspacing="0" border="0" >
      <tr>
        <td height="30"><img src="images/1x1.gif" width="1" height="30" /></td>
        <td height="30">&nbsp;</td>
        <td height="30">&nbsp;</td>
      </tr>
</table>
</td></tr>
<tr><td>      <p class='breakhere'></p></td></tr>
<?php } ?>
<?php } ?>
                                                                           </td>
  </tr>
  <tr>
    <td><img src="images/1x1.gif" width="1" height="10" /></td>
  </tr>
  <tr>
    <td><table cellpadding="4" cellspacing="0" border="1" width="300" bgcolor="#999999">
      <tr bgcolor="#ffffff">
        <td valign="top" bgcolor="#e1e1f6">Total # of AEDs at <?= getSchoolStr("school") ?>:</td>
        <td width="80" valign="top"><?= count($allaeds) ?> </td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top" bgcolor="#e1e1f6"><p>Total  # inspected: </p></td>
        <td width="80" valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top" bgcolor="#e1e1f6">Total # of sheets attached:</td>
        <td width="80" valign="top">&nbsp;</td>
      </tr>
    </table></td>
  </tr>
</table>
</body>
</html>
<?php 
// Add a closing PHP tag for completeness, although not strictly required at the end of a file.
?>