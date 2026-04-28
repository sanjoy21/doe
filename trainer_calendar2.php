<?php
// require_once('mysql.php');

// --- Security Helper Functions ---
// Helper for HTML escaping (XSS mitigation)
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
// ---------------------------------

// Assuming trainer ID is retrieved via session
// **IMPORTANT:** Replace 'get_trainerid' and session logic with your application's actual user retrieval.
// Assuming get_trainerid($session_id) returns a safe, non-malicious string or integer.
$trainerid = get_trainerid($session_id);

// Get current date components
$today_month = (int)date("n");
$today_year = (int)date("Y");

// --- SQLi Mitigation: Sanitize User Input ---
// Get month and year from request, default to today's date
$month = $_REQUEST['month'] ?? $today_month;
$year = $_REQUEST['year'] ?? $today_year;

// Explicitly cast user inputs to integers for safe database usage
$month = (int)$month;
$year = (int)$year;
// ---------------------------------------------

// Calculate date for the calendar display
$display_date = date("F Y", mktime(0, 0, 0, $month, 1, $year));
$prev_month_url = "trainer_calendar.php?month=" . ($month == 1 ? 12 : $month - 1) . "&year=" . ($month == 1 ? $year - 1 : $year);
$next_month_url = "trainer_calendar.php?month=" . ($month == 12 ? 1 : $month + 1) . "&year=" . ($month == 12 ? $year + 1 : $year);

// --- Calendar Data Retrieval ---
// SQLi is mitigated because $trainerid, $year, and $month are all sanitized/cast to safe types.
$sql = "
SELECT 
    id, 
    date_format(startdate, '%e') as startday,
    date_format(startdate, '%I:%i %p') as starttime
FROM class
WHERE trainerid = '{$trainerid}'
AND deleted = 0
AND date_format( startdate, '%Y-%c' ) = '{$year}-{$month}'
ORDER BY startdate
";
$classes = db_query_rows($sql); // Assuming this function executes the query

// Create an associative array mapping day number to class information
$daily_classes = [];
if (!empty($classes)) {
    foreach ($classes as $class) {
        $day = (int)$class['startday'];
        if (!isset($daily_classes[$day])) {
            $daily_classes[$day] = [];
        }
        // XSS Mitigation: Ensure class ID and time are safely output
        $daily_classes[$day][] = '<span class="small"><a href="class_detail.php?id=' . h($class['id']) . '">' . h($class['starttime']) . '</a></span>';
    }
}

?>
<?php include "ssi/top.php"; ?>

<strong><span class="title">TRAINER CALENDAR</span></strong>

<form method='post' action="trainer_calendar.php">
<p>
<table cellpadding="0" cellspacing="2" border="0" width="100%">
<tr>
<td valign="top"><a href="trainer_calendar.php?month=<?=h($today_month)?>&year=<?=h($today_year)?>" class="copy"><strong>Go to Today</strong></a></td>
<td valign="top"><span class="copy">| Go to: </span></td>
<td valign="top">
<select name="month" style="font-size: 10px; font-family: verdana;">
<option value=""></option>
<option value="1" <?=($month === 1 ? 'SELECTED' : '')?>>JAN</option>
<option value="2" <?=($month === 2 ? 'SELECTED' : '')?>>FEB</option>
<option value="3" <?=($month === 3 ? 'SELECTED' : '')?>>MAR</option>
<option value="4" <?=($month === 4 ? 'SELECTED' : '')?>>APR</option>
<option value="5" <?=($month === 5 ? 'SELECTED' : '')?>>MAY</option>
<option value="6" <?=($month === 6 ? 'SELECTED' : '')?>>JUNE</option>
<option value="7" <?=($month === 7 ? 'SELECTED' : '')?>>JULY</option>
<option value="8" <?=($month === 8 ? 'SELECTED' : '')?>>AUG</option>
<option value="9" <?=($month === 9 ? 'SELECTED' : '')?>>SEPT</option>
<option value="10" <?=($month === 10 ? 'SELECTED' : '')?>>OCT</option>
<option value="11" <?=($month === 11 ? 'SELECTED' : '')?>>NOV</option>
<option value="12" <?=($month === 12 ? 'SELECTED' : '')?>>DEC</option>
</select>
</td>
<td valign="top">
<select name="" style="font-size: 10px; font-family: verdana;">
<option value=""></option>
                                        <?php for ($d = 1; $d <= 31; $d++): ?>
<option value="<?=h(str_pad($d, 2, '0', STR_PAD_LEFT))?>"><?=h(str_pad($d, 2, '0', STR_PAD_LEFT))?></option>
                                        <?php endfor; ?>
</select>
</td>
<td valign="top">
<select name="year" style="font-size: 10px; font-family: verdana;">
<option value=""></option>
                                        <?php for ($y = 2006; $y <= 2012; $y++): ?>
<option value="<?=h($y)?>" <?=($year === $y ? 'SELECTED' : '')?>><?=h($y)?></option>
                                        <?php endfor; ?>
</select>
</td>
                <td><input type='submit' value='GO' style='font-size:8px; height:16px;'></td>

<td valign="top" align="right">
<table cellpadding="0" cellspacing="0" border="0">
<tr>
                            <td valign="top"><a href="trainer_calendar_day.php"><img src="images/button_day.gif" border="0"></a></td>
                            <td valign="top"><a href="trainer_calendar_week.php"><img src="images/button_week.gif" border="0"></a></td>
                            <td valign="top"><a href="trainer_calendar.php"><img src="images/button_month.gif" border="0"></a></td>
</tr>
</table>
</td>
</tr>
</table>
        </form>

<table cellpadding="2" cellspacing="1" border="0" bgcolor="#5a179e">
<tr height="21">
<td valign="top" colspan="7" align="center" bgcolor="#5a179e">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="bottom" width="5%"><a href="<?=h($prev_month_url)?>"><img src="images/arrow_left.gif" border="0"></a></td>
<td valign="bottom" align="center" width="90%"><span class="white"><strong><?=h($display_date)?></strong></span></td>
<td valign="bottom" align="right" width="5%"><a href="<?=h($next_month_url)?>"><img src="images/arrow_right.gif" border="0"></a></td>
</tr>
</table>
</td>
</tr>

            <?php
            // Calculate necessary date metrics
            $day_of_week = date("w", mktime(0, 0, 0, $month, 1, $year)); // 0 = Sunday, 6 = Saturday
            $days_in_month = date("t", mktime(0, 0, 0, $month, 1, $year));
            $day_counter = 1;
            $week_day_headers = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
            $today_day = ($month === $today_month && $year === $today_year) ? (int)date("j") : 0;
            
            // Output Weekday Headers
            echo '<tr bgcolor="#cccccc" height="25">';
            foreach ($week_day_headers as $header) {
                echo '<td align="center" class="copy"><strong>' . $header . '</strong></td>';
            }
            echo '</tr>';

            // Start first week row
            echo '<tr bgcolor="#ffffff" height="65">';
            
            // Output blank cells for days before the 1st
            for ($i = 0; $i < $day_of_week; $i++) {
                echo '<td valign="top" width="65" bgcolor="#cccccc"><br></td>';
            }
            
            // Loop through the days of the month
            while ($day_counter <= $days_in_month) {
                // Start a new row if we hit Sunday
                if ($day_of_week > 6) {
                    echo '</tr><tr bgcolor="#ffffff" height="65">';
                    $day_of_week = 0;
                }
                
                // Determine background color and content
                $cell_bgcolor = '';
                if ($day_counter === $today_day) {
                    $cell_bgcolor = '#ffcc99'; // Highlight for today
                } elseif (isset($daily_classes[$day_counter])) {
                    $cell_bgcolor = '#ccffcc'; // Highlight for days with classes
                } else {
                    $cell_bgcolor = ''; // Default white/transparent
                }
                
                // Output day cell
                echo '<td valign="top" width="65" ' . ($cell_bgcolor ? 'bgcolor="' . $cell_bgcolor . '"' : '') . '>';
                
                // Link to day view
                echo '<strong><a href="trainer_calendar_day.php?day=' . $day_counter . '&month=' . $month . '&year=' . $year . '" class="copy">' . $day_counter . '</a></strong>';
                
                // Output class times
                if (isset($daily_classes[$day_counter])) {
                    echo '<br>' . implode('<br>', $daily_classes[$day_counter]);
                } else {
                    echo '<br>'; // Keep cell height consistent
                }
                
                echo '</td>';
                
                $day_counter++;
                $day_of_week++;
            }
            
            // Output blank cells for remaining days of the last week
            while ($day_of_week <= 6) {
                echo '<td valign="top" width="65" bgcolor="#cccccc"><br></td>';
                $day_of_week++;
            }
            
            echo '</tr>';
            ?>
</table>
<p>
<table cellpadding="0" cellspacing="1" border="0" bgcolor="#b2b1b1" width="100%">
<tr>
<td valign="top">
<table cellpadding="0" cellspacing="3" border="0" bgcolor="#f1f1f4" width="100%">
<tr>
<td valign="top"><img src="images/clicking.gif"></td>
</tr>
</table>
</td>
</tr>
</table>

<br><br><br>

<?php include "ssi/footer.php" ; ?>

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>