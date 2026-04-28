<?php
$nologinrequired = true;
$donewui = true;
require_once "mysql.php";


if (!$mobile_browser)
  include "ssi/top.php";
//sendMail( "rachelc@gmail.com", "CPR/AED", "body", "rachel@emergencyskills.com" );
?>

<p class="Titles"><strong><br>
    ALIVE!net (new)</strong></p>


<table width="100%" border="0" cellspacing="0" cellpadding="4" class="table3">
  <tr>
    <td colspan="2">
  <tr>
    <td colspan=2 align='center'><br><img width='400' src='images/covid.jpg'><br>For more American Heart Association CPR Resources for Covid-19 click here:<br> <a href='https://cpr.heart.org/en/resources/coronavirus-covid19-resources-for-cpr-training' target=_blank>https://cpr.heart.org/en/resources/coronavirus-covid19-resources-for-cpr-training</a>
</table>
<p>&nbsp;</p>

<?php if (!$mobile_browser)
  include "ssi/footer.php"; ?>
</span>
</td>
</tr>
</table>
<br><br>
</div>
<script type="text/javascript" src="webticker_lib.js" language="javascript"></script>
<script type='text/javascript'>
  if (location.protocol != 'https:') {
    location.href = 'https:' + window.location.href.substring(window.location.protocol.length);
  }
</script>
</body>

</html>