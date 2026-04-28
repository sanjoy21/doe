<?php 

include "mysql.php";

$rows = db_query_rows( "select schoolcode, class.id, startdate, companyname, companyid 
                       from class, company_esi 
                       where startdate > '2016-03-01' 
                         and companyid = company_esi.id 
                         and iscorp = 0 
                         and bic = 1 
                         and company_esi.deleted = 0 
                         and class.deleted = 0" );

if($xls)
{
    // Generate CSV instead of Excel
    $filename = "report_bic_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write header row
    $header = [
        "Class ID",
        "Date",
        "Company",
        "School Code"
    ];
    
    fputcsv($output, $header);
    
    foreach( $rows as $r ) 
    {
        // Prepare data row
        $rowData = [
            $r["id"] ?? '',
            $r["startdate"] ?? '',
            $r["companyname"] ?? '',
            $r["schoolcode"] ?? ''
        ];
        
        // Escape any formulas that might start with =, +, - or @ to prevent CSV injection
        foreach($rowData as &$value) {
            if($value !== null && $value !== '') {
                $firstChar = substr($value, 0, 1);
                if(in_array($firstChar, array('=', '+', '-', '@'))) {
                    $value = "'" . $value;
                }
            }
        }
        
        fputcsv($output, $rowData);
    }
    
    fclose($output);
    exit;
}

echo( "<table border=1 cellpadding=2 cellspacing=0>" );

foreach( $rows as $r ) 
{

    $id_safe = $r["id"];
    $startdate_safe = $r["startdate"];
    $companyid_safe = $r["companyid"];
    $companyname_safe = $r["companyname"];
    $schoolcode_safe = $r["schoolcode"];
?>
<tr>
<td><a href='class_detail.php?id=<?php echo $id_safe; ?>'><?php echo $id_safe; ?></a></td>
<td><?php echo $startdate_safe; ?></td>
<td><a href='viewcompany.php?id=<?php echo $companyid_safe; ?>'><?php echo $companyname_safe; ?></a></td>
<td><?php echo $schoolcode_safe; ?></td>
</tr> 
<?php } ?>
</table>