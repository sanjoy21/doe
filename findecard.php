<?php
$nologinrequired = true;
$donewui = true;
require_once "mysql.php";

if( !$mobile_browser ) {
    include "ssi/top.php";
}
?>
<style>
/* CSS styles for the instructional pop-up boxes */
.messagepop {
    background-color:#FFFFFF;
    border:1px solid #999999;
    cursor:default;
    display:none;
    margin-top: 15px;
    position:absolute;
    text-align:left;
    width:394px;
    z-index:50;
    padding: 25px 25px 20px;
}

label {
    display: block;
    margin-bottom: 3px;
    padding-left: 15px;
    text-indent: -15px;
}

.messagepop p, .messagepop div {
    border-bottom: 1px solid #EFEFEF;
    margin: 8px 0;
    padding-bottom: 8px;
}
</style>
<br>
<h3>Find your eCard</h3>
<br><br>
Within 30 days of completing the course you will receive an email from the American Heart Association (<a href='mailto:ecards@heart.org'>ecards@heart.org</a>) with instructions on claiming your official certification eCard. This email will be sent to the email address provided on your registration form, or sign-in sheet, the day of the training. The subject of the email will be "Your AHA eCard." Please search your inbox and spam folder. Follow the link in the email to claim and print your certification card. 

<br><br>
If you do not see the email, click here: <a href='http://heart.org/cpr/mycards'>http://heart.org/cpr/mycards</a>. If you have any trouble searching for your eCard, please call ESI at 212-564-6833. Or email <a href='mailto:cards@emergencyskills.com'>cards@emergencyskills.com</a>.
<br><br>

<br><br>
<h3>Claiming Your AHA eCard (Once it has been issued)</h3>

<ul style='padding-left: 30px'>
<li><a href="#" id="mobile">By Mobile Phone</a></li>
<li><a href="#" id="email">By Email</a></li>
<li><a href="#" id="direct">Directly on the AHA Website</a></li>
</ul>

<div class="messagepop emailpop">
    <h4>By Email</h4>

    <ol>
        <li>You will receive an email from ecards@heart.org with the subject "Your AHA eCard" with a link to the American Heart Association Website. If you do not see the email in your inbox, check your spam/junk folder. You will be prompted to:
            <ol style="padding-left: 20px" type="a">
                <li>Update communication preferences</li>
                <li>Rate your class</li>
                <li>Create an account by choosing a security question</li>
            </ol>
        </li>
        <li>Once you've created an account, you will be able to claim your card.
            <ol style="padding-left: 20px" type="a">
                <li>Once claimed, you can download, save and/or print a full size or wallet size certification card</li>
            </ol>
        </li>
    </ol>
    <center><a href='#' id="closeemail">Close</a></center> 
</div>

<div class="messagepop mobilepop">
    <h4>By Mobile Phone</h4><br>
    <i>**Claiming by mobile phone can only be done if you have provided your mobile number at the time of registration**</i>
    <br><br>
    <ol>
        <li>Text "eCard" to 51736</li>
        <li>You will receive a text back with a link to the American Heart Association Website where you will be prompted to:
            <ol style="padding-left: 20px" type="a">
                <li>Update communication preferences</li>
                <li>Rate your class</li>
                <li>Create an account by choosing a security question</li>
            </ol>
        </li>
        <li>Once you've created an account, you will be able to claim your card.
            <ol style="padding-left: 20px" type="a">
                <li>Once claimed, you can download, save and/or print a full size or wallet size certification card</li>
            </ol>
        </li>
    </ol>
    <center><a href='#' id="closemobile">Close</a></center> 
</div>

<div class="messagepop directpop">
    <h4>Directly on the AHA Website</h4>

    <ol>
        <li>Go to: <a target=_blank href='https://heart.org/cpr/mycards'>https://heart.org/cpr/mycards</a></li>
        <li>On the "Student" tab: enter your name and email</li>
        <li>You will be prompted to:
            <ol style="padding-left: 20px" type="a">
                <li>Update communication preferences</li>
                <li>Rate your class</li>
                <li>Create an account by choosing a security question</li>
            </ol>
        </li>
        <li>Once you've created an account, you will be able to claim your card.
            <ol style="padding-left: 20px" type="a">
                <li>Once claimed, you can download, save and/or print a full size or wallet size certification card</li>
            </ol>
        </li>
    </ol>
    <center><a href='#' id="closedirect">Close</a></center> 
</div>


<br><br><br><br>
<h3>Finding your eBook</h3>
Within 30 days of completing the course you will receive an email from the American Heart Association (donotreply@heart.org on behalf of eLearning.heart.org) with a link to activate your eBook. This email will be sent to the email address provided on your registration form, or sign-in sheet, the day of the training. The subject of the email will be "Enroll for the eBook". Please search your inbox and spam folder.
<br><br>
Following the "Activate your key here" link in the email will take you to the American Heart Association site where it will ask for your access code (This code can be found in the first line of the email you received). New users will need to complete the registration form. Returning users may be prompted to log in.
<br><br>
If you have any trouble searching for your eBook, please call ESI at **212-564-6833**. Or email <a href='mailto:cards@emergencyskills.com'>cards@emergencyskills.com</a>.


<br><br>
 <p>&nbsp;</p>
 <br><br><br><br><br><br><br><br>
 <br><br><br><br><br><br><br><br>

<?php if( !$mobile_browser ) {
    include "ssi/footer.php";
}?>
</span>
</td>
<td valign="top" width="15"><img src="<?php echo WEB_ROOT ; ?>/images/dotclear.gif" width="10" alt=""></td>
</tr>
</table>
<br><br>
</div>
<script>
$(function() {
    // Toggles the visibility of the mobile pop-up
    $('#mobile').on('click', function() {
        $(".mobilepop").toggle();
        return false;
    });

    // Closes the mobile pop-up
    $('#closemobile').on('click', function() {
        $(".mobilepop").hide();
        return false;
    });

    // Toggles the visibility of the email pop-up
    $('#email').on('click', function() {
        $(".emailpop").toggle();
        return false;
    });

    // Closes the email pop-up
    $('#closeemail').on('click', function() {
        $(".emailpop").hide();
        return false;
    });

    // Toggles the visibility of the direct pop-up
    $('#direct').on('click', function() {
        $(".directpop").toggle();
        return false;
    });

    // Closes the direct pop-up
    $('#closedirect').on('click', function() {
        $(".directpop").hide();
        return false;
    });
});
</script>
</body>
</html>