<?php
require_once('mysql.php');

// Assumed external functions:
// function isOverallAdmin(): bool;
// function db_query_safe(string $sql, array $params): void;
// function db_query_rows_safe(string $sql, array $params): array;
// function mysql_select_db(string $dbname, $link): bool;

$title = "Purchased Classes";

// Helper function definition
function getScheduleClassCode(string $code): string
{
    switch ($code) {
        case "BLS Online":
            return "Test";
        case "Heartsaver First Aid Only":
            return "HS Skills";
        case "Heartsaver CPR and AED":
            return "FA Test";
        default:
            return "";
    }
}

// --- 1. Access Control ---
if (!isOverallAdmin()) {
    header("Location: /index.php");
    exit;
}

// --- 2. Input Retrieval ---
$markme = $_POST['markme'] ?? [];

// --- 3. Database Selection (e-commerce DB) ---
// The original code uses a global $link for the connection
mysqli_select_db($link, "emergencyskills_tsi" );

// --- 4. Handle Mark Completed Action ---
if (!empty($markme) && is_array($markme)) {
    // Process each order_product_id submitted
    foreach ($markme as $orders_products_id => $throwaway) {
        $id_to_update = (int) $orders_products_id;
        
        // Use safe query function to update the database
        if ($id_to_update > 0) {
            $sql_update = "UPDATE orders_products SET completed = 1 WHERE orders_products_id = ?";
            // Assuming db_query_safe handles prepared statement execution
            db_query($sql_update, [$id_to_update]);
        }
    }
}

// --- 5. Main Data Query ---
// Fetch uncompleted purchases for specific product IDs where payment is confirmed.
$completed = "AND completed = 0";

// Final query including payment status check (left join to paypal history)
$sql = "
    SELECT 
        o.payment_id, op.orders_products_id, o.orders_id, op.products_name, 
        o.customers_name, o.customers_email_address, o.date_purchased, 
        o.orders_status, op.products_id
    FROM orders o
    JOIN orders_products op ON o.orders_id = op.orders_id
    LEFT JOIN paypal_payment_status_history ppsh ON ppsh.paypal_id = o.payment_id
    WHERE op.products_id IN (215, 54, 52, 53, 240, 57) 
    {$completed} 
    AND o.orders_status IN (1) 
    AND (ppsh.payment_status = 'Completed' OR o.payment_method = 'Pay by Credit Card') 
    ORDER BY o.date_purchased DESC
";

$res = db_query_rows($sql); // Assumes this uses the 'emergencyskills_tsi' connection

?>
<?php include "ssi/top.php"; ?> 	 	 	 	
<form method='post' action="">
	 	 	 	 	 	 
	 	 	 	 	 	 <span class="page-head"><?= htmlspecialchars($title) ?></span><br><br><br clear="all">

	 	 	 	 	 	 <table class="table2">
	 	 	 	 	 	  <tr> 	 	 	 	 	 	
	 	 	 	 	 	  <th class="left">Order Id</th>
	 	 	  <th class="left">Customer Name</th> 
	 	 	  <th class="left">Customer Email</th> 	 	
	 	 	  <th class="left">Date Purchased</th> 	 	 	 	 	 	 	 	
	 	 	  <th class="left">Class Purchased</th> 	 
	 	 	  <th class="left">Schedule Class</th>
	 	 	  <th class="left">Mark Completed</th>
	 	 	  </tr>
<?php
if (!empty($res)) {
    foreach ($res as $r) { 
        $product_name = htmlspecialchars($r["products_name"] ?? '');
        $order_product_id = (int) ($r["orders_products_id"] ?? 0);
?>
	 	 	  <tr>
	 	 	  <td class="left"><?= htmlspecialchars($r["orders_id"] ?? '') ?></td>
	 	 	  <td class="left"><?= htmlspecialchars($r["customers_name"] ?? '') ?></td>
	 	 	 <td class="left"><?= htmlspecialchars($r["customers_email_address"] ?? '') ?></td>
	 	 	  <td class="left"><?= htmlspecialchars($r["date_purchased"] ?? '') ?></td>
	 	 	  <td class="left"><?= $product_name ?></td>
	 	 	 <td class="left">
	 	 	  <a href='http://clients.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/schedule_class.php?catonly=1&companyid=10449&class=<?= htmlspecialchars(getScheduleClassCode($product_name)) ?>'>Schedule</a>
	 	 	 </td>
	 	 	 <td class="left">
	 	 	 <input type='submit' name='markme[<?= $order_product_id ?>]' 
	 	 	   onClick='return confirm("Are you sure you want to mark this completed?")' 
	 	 	     value='Mark Completed'>
	 	 	 </td>
	 	 	  </tr>
	 	 	 <?php 
    }
} else {
    echo "<tr><td colspan='7' class='left'>No uncompleted purchased classes found.</td></tr>";
}
?>
	 	</table>
</form>

<?php 
// --- 6. Database Selection (Switching back/to another DB) ---
// This part is preserved from the original logic, assuming the subsequent code 
// needs to run queries against 'emergencyskills_doe'.
mysqli_select_db($link, "emergencyskills_doe");

// The rest of the page (ssi/footer.php) is assumed to be included in top.php 
// or elsewhere, as the provided snippet ends abruptly.
// If ssi/footer.php is needed, it should be included here.
// include "ssi/footer.php"; 
?>
</body>
</html>