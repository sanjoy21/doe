<?php

require_once('mysql.php');

if( !$specialadmin && $thisusersrow["companyid"] > 0 )
{
    header( "location: login.php" );
    exit;
}

$extra = "";

if( $lname )
{
    // Filter by Request ID (after removing 'AR' prefix)
    $extra .= " and id = '".str_replace( "AR","", strtoupper( $lname ) )."'";
}

if( !$viewcompleted )
{
    // Filter out completed requests unless explicitly requested
    $extra .= " and completed = '0'";
}


if( $year )
{
    // Filter by school year (July 1st to June 30th of the following year)
    $extra .= " and ( ( requestdate > '$year-07-01' and requestdate < '".($year+1)."-07-01' ) or requestdate is null or requestdate = '0000-00-00' )";
}

// Fetch accessory requests
$order_clause = $sortby ? "$sortby, " : "";
$trainers = db_query_rows( "select accessoryrequests.* from accessoryrequests {$extrajoin} where 1 {$extra} order by {$order_clause} id" ); 
?>
<?php include "ssi/top.php"; ?>        
<!--start center content-->
        <p>
        
            <strong><span class="title">MANAGE ACCESSORY REQUESTS</span></strong>
        
        <p>
        <form method='post'>
        <span class='copy'>
            Search (school year): 
            <select name='year'>
                <option value=''></option>
                <?php for( $i = 2011; $i <= date( "Y" ); $i++ ){ ?>
                <option value='<?php echo $i; ?>' <?php echo $year == $i ? "SELECTED" : ""; ?>><?php echo $i; ?> - <?php echo $i+1; ?></option>
                <?php } ?>
            </select> 
            
            <input type='text' name='lname' class='copy' value="<?php echo $lname; ?>"> 
            
            <select name='sortby'>
                <option value='id'>Sort By ID</option>
                <option value='itemtype' <?php echo $sortby == "itemtype" ? "SELECTED" : ""; ?>>Items</option>
            </select> 
            
            <input class='copy' type='submit' name='search' value='Search'> 
            <input type='checkbox' name='viewcompleted' value='1' <?php echo $viewcompleted ? "CHECKED" : ""; ?>> View Completed?
            <br><br>
            
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3" >
            <tr bgcolor="#e1e1f6">
                <th class='copy'>Name</th>
                <th class='copy'>Request Date</th>
                <th class='copy'><?php echo getSchoolStr( "School" ); ?></th>
                <th class='copy'>Items</th>
            </tr>
<?php 
foreach( $trainers as $t )
{
    // Use quoted array keys
    $crow = getCompanyRow( $t['companyid'] );

    // Apply corporate filters
    // Use quoted array keys
    if( $session_iscorp && !$crow['iscorp'] ) continue;
    if( !$session_iscorp && $crow['iscorp'] ) continue;

    // Output row
    // Use quoted array keys for deleted check and all array access
    $bgcolor = $crow["deleted"] ? "#FFccccc" : "#FFFFFF";
    
    echo( "<tr bgcolor='{$bgcolor}'><td class='copy' valign='top'><a href='editaccessoryrequest.php?accessoryrequestid={$t['id']}'>AR{$t['id']}</a></td><td class='copy'>{$t['requestdate']}</td> " );
    echo( "<td class='copy'><a href='viewcompany.php?id={$t['companyid']}'>".getCompanyName( $t["companyid"] )  ."</a></td><td class='copy'>{$t['itemtype']}</td>" );
    echo( "</tr>" );
}
?>
</table><p>
<!--<input type='submit' name='update' value='Update'><br><br><br>-->
        <!--end center content-->
        
                    <?php include "ssi/footer.php" ; ?>
        
        <!--end footer-->
        </span>
        </td>
        <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
    </tr>
</table>
<br><br>
</div>
</body>
</html>