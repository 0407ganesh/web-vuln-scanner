<?php
// backend/export_csv.php - Dedicated CSV export file

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'vuln_scanner';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set CSV headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="vuln_scan_results_' . date('Y-m-d_H-i-s') . '.csv"');

// Open output as CSV
$output = fopen('php://output', 'w');

// CSV column headers
fputcsv($output, [
    'Scan ID',
    'URL',
    'Scan Time',
    'Status Code',
    'Vulnerability Type',
    'Severity',
    'Description'
]);

// Fetch data
$query = "SELECT 
            s.id AS scan_id,
            w.url,
            s.scan_time,
            s.status_code,
            v.vuln_type,
            v.severity,
            v.description
          FROM scans s
          JOIN websites w ON s.website_id = w.id
          LEFT JOIN vulnerabilities v ON s.id = v.scan_id
          ORDER BY s.scan_time DESC";

$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['scan_id'],
            $row['url'],
            $row['scan_time'],
            $row['status_code'],
            $row['vuln_type'] ?? 'No vulnerability',
            $row['severity'] ?? 'None',
            $row['description'] ?? 'N/A'
        ]);
    }
} else {
    fputcsv($output, ['Error', 'Query failed', $conn->error]);
}

fclose($output);
$conn->close();
exit;
?>