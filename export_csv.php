<?php
// export_csv.php - Dedicated file for CSV download

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'vuln_scanner';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="vuln_scan_results_' . date('Y-m-d_H-i-s') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// CSV column headers
fputcsv($output, ['Scan ID', 'URL', 'Scan Time', 'Status Code', 'Vulnerability Type', 'Severity', 'Description']);

// Fetch all data
$query = "SELECT s.id, w.url, s.scan_time, s.status_code, v.vuln_type, v.severity, v.description
          FROM scans s
          JOIN websites w ON s.website_id = w.id
          LEFT JOIN vulnerabilities v ON s.id = v.scan_id
          ORDER BY s.scan_time DESC";

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['url'],
        $row['scan_time'],
        $row['status_code'],
        $row['vuln_type'] ?? 'No vulnerability',
        $row['severity'] ?? 'None',
        $row['description'] ?? 'N/A'
    ]);
}

fclose($output);
$conn->close();
exit;
?>