<tr><td colspan="2" bgcolor="#E2DFDF">
    <?php
                 $showname = false;
         $showallincampus = false;
//if( $row["iscorp"] == AGING && $row["campusid"] )
//    $showallincampus = true;
                 if( isset($row["iscorp"]) && $row["iscorp"] && !$showallincampus )
                     $aed_rows = getAedRows( $row["id"] );
                 else
                 {
                     $aed_rows = getAedRows( $row["id"], "", isset($row["campusid"]) ? $row["campusid"] : '' );
                     if( isset($row["campusid"]) && $row["campusid"] )
                         $showname = true;
                 }

    ?>
            <div align="center">
            <table width="95%" border="0">
            <tr>
            <td>
        <table><tr>
                <?php
        $colnum = 0;
        if( isset($aed_rows) && is_array($aed_rows) ) {
            foreach( $aed_rows as $aed ) { 
        if( !$onlystolen && isset($aed["aedstolen"]) && $aed["aedstolen"] )
        continue;
        if( isset($onlystolen) && $onlystolen && (!isset($aed["aedstolen"]) || !$aed["aedstolen"]) )
        continue;
        $colnum++;
        $pval = (isset($aed["padaexpiration"]) && $aed["padaexpiration"]>'0000-00-00')?"Pad A: {$aed["padaexpiration"]}<br>":"";
        $pval .= (isset($aed["padbexpiration"]) && $aed["padbexpiration"]>'0000-00-00')?"Pad B: {$aed["padbexpiration"]}<br>":"";
        $pval .= (isset($aed["pediatricpads"]) && $aed["pediatricpads"]>'0000-00-00')?"Ped Pad: {$aed["pediatricpads"]}<br>":"";
        
                ?><tr><td>
                    <span class="copy">
<?php echo $colnum?>.
                    <strong>
<?php echo isset($aed["outofwarranty"]) && $aed["outofwarranty"]?"W":""?>
<?php echo isset($aed["isrecall"]) && $aed["isrecall"]?"R":""?>
<?php echo isset($aed["aedstolen"]) && $aed["aedstolen"]?"<font color='red'>S</font>":""?>
<?php  
// Added isset check for $thisusersrow
if( isset($thisusersrow) && !$thisusersrow["healthdirector"] ) {?>
                         <a <?php if( $pval ) { ?>onMouseover="popup('<?php echo htmlspecialchars($pval)?>', 'white')" onMouseout="kill()" <?php }?> href="viewserial.php?aedid=<?php echo $aed['aedid'];?>&id=<?php echo $row['id'];?>&redirect=<?php echo urlencode( $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'] )?>">
                            <?php if( isset($aed['aedmissing']) && $aed['aedmissing'] ) { ?><font color='red'>(M)<?php } ?>
                            <?php if( isset($aed['isrma']) && $aed['isrma'] ) { ?>(X)<?php } ?>
                            <?php if( isset($aed['aedretired']) && $aed['aedretired'] ) { ?><font color='red'>(RETIRED)<?php } ?>
                            <?php if( isset($aed['readytoreturn']) && $aed['readytoreturn'] ) { ?><font color='purple'>(RTR)<?php } ?>
                            <?php if( isset($aed['outofservice']) && $aed['outofservice'] ) { ?><font color='green'>(OOS)<?php } ?>
                            <strong><?php echo isset($aed['serial']) && $aed['serial']?$aed['serial']:"N/A";?></strong></a>
                                            <?php } else { ?>
        <?php if( isset($aed['aedmissing']) && $aed['aedmissing'] ) { ?><font color='red'>(M)<?php } ?>
        <?php if( isset($aed['readytoreturn']) && $aed['readytoreturn'] ) { ?><font color='purple'>(RTR)><?php } ?>
        <?php if( isset($aed['aedretired']) && $aed['aedretired'] ) { ?><font color='red'>(RETIRED)<?php } ?>
               <?php if( isset($aed['outofservice']) && $aed['outofservice'] ) { ?><font color='green'> (OOS)<?php } ?>
                  <strong><?php echo isset($aed['serial']) && $aed['serial']?$aed['serial']:"N/A";?></strong>
                                         <?php } ?>
                <?php echo isset($aed['newinstall']) && $aed['newinstall']?"(N)":"";?> <?php if( isset($currentusertype) && ($currentusertype == "trainer" || (isset($specialadmin) && $specialadmin)) ) { ?>  <?php if( isset($specialadmin) && $specialadmin ) { ?><a href="editaed.php?aedid=<?php echo $aed['aedid'];?>&id=<?php echo $row['id'];?>">Edit</a> <?php } ?><a href='servicecallsheet.php?companyid=<?php echo $id?>&aedid=<?php echo $aed['aedid']?>&newservicecall=true' >(new sc)</a><?php } ?></font></font></font></font>
<?php echo isset($aed["aedstolen"]) && $aed["aedstolen"]?"<font color='red'>" . (isset($aed["aedstolentext"]) ? $aed["aedstolentext"] : '') . "</font>":""?>

                                <?php if( $showname ) { ?><?php echo getCompanyName( isset($aed["clientid"]) ? $aed["clientid"] : '' )?> - <?php } ?>
<?php echo isset($aed["location"]) ? $aed["location"] : ''?> <?php echo isset($aed["floor"]) && $aed["floor"]?"- ".$aed["floor"]:""?> <br>
                    </span>
                    </strong>
            </td><td><?php echo isset($aed["buildingcode"]) && $aed["buildingcode"]?$aed["buildingcode"]:"<font color='red'>N/A</font>"?></tr>
                <?php
                }
        }
                ?>
            </td>
            </tr>
            </table>
            </td>
            </tr>
            </table>
            </div>
            </span>
            </td>
            </tr>          
<?php include "popupjs.php" ;?>