<?php
include "mysql.php";
$nonewschool = 1;

if( $return )
{
    Header( "Location: freeregistrants.php" );
    exit;
}

if( $save || $saveandsend )
{
    db_query( "update free_registrants set firstname = '$firstname', lastname = '$lastname', email = '$email' where id = $id " );
    if( $companyid )
        db_query( "update free_registrants set schoolid = '$companyid' where id = $id " );
        
    if( $saveandsend )
    {
    $body = "Thank you for your inquiry on how to complete a CPR/AED training program at no charge through the New York City Department of Education (NYCDOE). Since you are not a full time employee of the NYCDOE, a school official such as Athletic Director or Principal must submit a request in writing to Celeste McGee and Husain Thompson at the NYCDOE for approval. 
<br> <br> 
Your request must meet the following criteria in order to be considered:
<ul><li>Written on School Letterhead</li></ul>
Letter must:
<ul>
<li>Include Name and title or status of person to be trained</li>
<li>Clearly indicate request for individual to complete CPR/AED course</li>
<li>Be signed by school official with their full name and title clearly indicated below the signature</li>
<li>Provide the email address the approval should be sent to</li>
</ul>
<br><br> 
Please send your request to one of the following:<br> 
Celeste McGee: <a href='mailto:CMcGee3@schools.nyc.gov'>CMcGee3@schools.nyc.gov</a> Ph: (718) 391-8566  Fax: (718) 391-8128<br>
Husain Thompson: <a href='mailto:hthomps@schools.nyc.gov'>HThomps@schools.nyc.gov</a> Ph: (718) 391-8227  Fax: (718) 391-8128<br> 
<br>
If approved, an email will be sent to the address indicated with instructions on how to register. You must follow those instructions to complete the registration process. <br>
";
    sendFormattedHTMLMail( $email, "CERTIFICATION REQUEST APPROVAL PROCESS" , $body, "info@emergencyskills.com", "", false );
    db_query( "update free_registrants set sentemail = '1', dateemailsent = now() where id = $id " );
    
    }
}

$row = db_query_first( "select * from free_registrants where id = $id" );
?>
<?php
include "ssi/top.php"; ?>
<?php include "getschooldropdown_ajax.php"; ?>
<!--start center content-->

<form method="post">
<input type="hidden" name="id" value="<?=$id?>">
<table cellpadding="5" cellspacing="1" border="0" width="100%"  class="table3">
<tr>
<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="freeregistrants.php">&laquo; Back to List</a></strong></span></td>
</tr>
</table>
<table cellpadding="5" cellspacing="1" border="0" width="100%"  class="table3">
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><strong>Registrant Information</strong></span></td>
</tr>


<tr>
<td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>First Name*:</strong><br><input type="text" size="40" VALUE="<?=$row['firstname']?>" name="firstname" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Last Name*:</strong><br><input type="text" size="40" VALUE="<?=$row['lastname']?>" name="lastname" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Email*:</strong><br><input type="text" size="40" VALUE="<?=$row['email']?>" name="email" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<!--<tr>
<td valign="top" bgcolor="#E2DFDF">
                                                                                                                                                   <table  class="table3"><tr><td>
                                                                                                                                                   <span class="copy"><strong>School:</strong> <?=!empty($row['schoolid']) ? getCompanyName( $row['schoolid'] ) : ""?> <br><br>
                    Choose a Borough:
                                                                                                                                                   </td>
                                                                                                                                                   <td valign='bottom'>
<b>School Name/Code:</b>
</td>                                                                                                                                                   </tr>
                                                                                                                                                   <tr><td>
 <select id=borough name="borough" style="font-size: 10px;  font-family: verdana;">
    <option value=""></option>
                                        <option value="Bronx">The Bronx</option>
                                        <option value="Brooklyn">Brooklyn</option>
                            <option value="Manhattan">Manhattan</option>
   <option value="Queens">Queens</option>
     <option value="Staten Island">Staten Island</option>
                    </select>
</td><td>
                            <input type='text' id='tmpschoolname' name='tmpschoolname' class='copy' onChange='updateCompanies()'>
                                                                                                                                                   </td></tr>
                                                                                                                                                   <tr><td colspan='2'>
<input type='button' value='Search' class=copy onClick='updateCompanies()'>
                                                                                                                                                   </td></tr>
                                                                                                                                                   <tr><td colspan='2'>                                                                                                                                                   
<span id='school_select'>
</span>
        </span></td>
</tr></table>
</td>
</tr>
-->
<tr>
<td valign="top" bgcolor="#E2DFDF">
                    <span class="copy"><strong>Email Sent:</strong> <?=!empty($row["sentemail"])?"Yes (Last Sent: ".getFormattedDateWTime( $row['dateemailsent'] ).")":"No"?></td>
</tr>
                                                                                                                                                   <tr><td>
                <div align="center">
                    <input type="submit" name='save' value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="submit" name='saveandsend' value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save & Send Initial Email&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="submit" name='return' value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Return&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">

                </div>
                </td>
</tr>
</table>
<br><br>
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