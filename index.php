<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Vulnerability Scanner - Ganesh & Thejas</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 30px; color: #1e293b; }
        .container { max-width: 1100px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        h1 { color: #0f172a; text-align: center; margin-bottom: 10px; }
        h2 { color: #0f172a; margin-top: 60px; text-align: center; }
        .subtitle, .disclaimer { text-align: center; margin-bottom: 30px; }
        .disclaimer { color: #ef4444; font-weight: bold; }
        .team { color: #64748b; font-weight: bold; }
        form { text-align: center; margin: 30px 0; }
        input[type="url"] { width: 65%; padding: 14px 18px; font-size: 17px; border: 2px solid #cbd5e1; border-radius: 8px; outline: none; }
        input[type="url"]:focus { border-color: #10b981; }
        button { padding: 14px 50px; font-size: 17px; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; margin-left: 15px; transition: 0.2s; }
        button:hover { background: #059669; }
        .export-btn { background: #3b82f6; margin: 20px auto; display: block; padding: 12px 40px; font-size: 17px; }
        .export-btn:hover { background: #2563eb; }
        .result { margin-top: 50px; padding: 25px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0; }
        pre { background: #0f172a; color: #94a3b8; padding: 20px; border-radius: 8px; overflow-x: auto; font-size: 14px; line-height: 1.5; }
        .vuln-box { margin: 15px 0; padding: 15px; border-radius: 8px; font-weight: 600; }
        .high { background: #fee2e2; color: #b91c1c; border-left: 6px solid #ef4444; }
        .medium { background: #fef3c7; color: #b45309; border-left: 6px solid #f59e0b; }
        .low { background: #ecfdf5; color: #047857; border-left: 6px solid #10b981; }
        .success { color: #059669; font-size: 20px; text-align: center; margin: 30px 0; font-weight: bold; }
        .error { color: #ef4444; text-align: center; font-weight: bold; margin: 20px 0; }
        table { width:100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
        th, td { padding:12px; border:1px solid #e2e8f0; text-align: left; }
        th { background:#0f172a; color:white; }
    </style>
</head>
<body>

<div class="container">
    <h1>Web Application Vulnerability Scanner</h1>
    <p class="subtitle">Basic security check tool – HTTPS, Headers, Cookies (Educational Project)</p>

    <p class="disclaimer">Educational project only – NOT a professional penetration testing tool. For awareness and learning only. Does not replace tools like OWASP ZAP, Burp Suite, Nessus, Nikto.</p>
    <p class="team">Developed by: Ganesh M (U18IW23S0087) & Thejas R (U18IW23S0172)</p>

    <form method="POST">
        <input type="url" name="url" placeholder="https://example.com" required autofocus>
        <button type="submit" name="scan">Start Scan</button>
    </form>

    <?php
    // Database connection
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = ''; // empty for default XAMPP
    $db_name = 'vuln_scanner';

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        echo '<p class="error">Database connection failed: ' . $conn->connect_error . '</p>';
        $conn = null;
    }

    if (isset($_POST['scan'])) {
        $url = trim($_POST['url']);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            echo '<p class="error">Invalid URL! Please enter a valid web address.</p>';
        } else {
            echo '<div class="result"><h2>Results for: ' . htmlspecialchars($url) . '</h2>';

            // Run Python scanner
            $python_path = 'C:/Users/ganes/AppData/Local/Programs/Python/Python313/python.exe';
            $command = $python_path . ' scanner/scan.py ' . escapeshellarg($url);
            $output = shell_exec($command . ' 2>&1');

            if ($output === null || trim($output) === '') {
                echo '<p class="error">Error: Scanner failed to run. Check Python path.</p>';
                $results = null;
            } else {
                $results = json_decode($output, true);

                if ($results === null) {
                    echo '<p class="error">JSON decode error. Raw output:</p>';
                    echo '<pre>' . htmlspecialchars($output) . '</pre>';
                } else {
                    echo '<h3>Full Scan Details</h3>';
                    echo '<pre>' . json_encode($results, JSON_PRETTY_PRINT) . '</pre>';

                    // Show vulnerabilities
                    if (!empty($results['vulnerabilities'])) {
                        echo '<h3>Detected Vulnerabilities:</h3>';
                        foreach ($results['vulnerabilities'] as $v) {
                            $class = strtolower($v['severity']);
                            echo '<div class="vuln-box ' . $class . '">';
                            echo '<strong>' . htmlspecialchars($v['type']) . '</strong> – ' . $v['severity'];
                            echo '<br><span style="font-weight:normal;">' . htmlspecialchars($v['description']) . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="success">No vulnerabilities detected – good basic security!</p>';
                    }

                    // SAVE TO DATABASE
                    if ($conn !== null && $results !== null) {
                        try {
                            // 1. Get or create website
                            $stmt = $conn->prepare("SELECT id FROM websites WHERE url = ?");
                            $stmt->bind_param("s", $url);
                            $stmt->execute();
                            $res = $stmt->get_result();

                            if ($res->num_rows > 0) {
                                $row = $res->fetch_assoc();
                                $website_id = $row['id'];
                            } else {
                                $stmt = $conn->prepare("INSERT INTO websites (url, name, category) VALUES (?, ?, ?)");
                                $name = parse_url($url, PHP_URL_HOST) ?: 'Unknown';
                                $category = 'Uncategorized';
                                $stmt->bind_param("sss", $url, $name, $category);
                                $stmt->execute();
                                $website_id = $conn->insert_id;
                            }
                            $stmt->close();

                            // 2. Save scan record
                            $stmt = $conn->prepare("INSERT INTO scans (website_id, status_code) VALUES (?, ?)");
                            $status = $results['status_code'] ?? 0;
                            $stmt->bind_param("ii", $website_id, $status);
                            $stmt->execute();
                            $scan_id = $conn->insert_id;
                            $stmt->close();

                            // 3. Save vulnerabilities
                            if (!empty($results['vulnerabilities'])) {
                                $stmt = $conn->prepare("INSERT INTO vulnerabilities (scan_id, vuln_type, severity, description) VALUES (?, ?, ?, ?)");
                                foreach ($results['vulnerabilities'] as $v) {
                                    $stmt->bind_param("isss", $scan_id, $v['type'], $v['severity'], $v['description']);
                                    $stmt->execute();
                                }
                                $stmt->close();
                            }

                            echo '<p style="color:#059669; text-align:center; font-weight:bold; margin-top:20px;">Scan saved to database successfully!</p>';
                        } catch (Exception $e) {
                            echo '<p class="error">Database save failed: ' . $e->getMessage() . '</p>';
                        }
                    }
                }
            }
            echo '</div>';
        }
    }

    // Scan History Section
    ?>
    <h2 style="margin-top: 60px; text-align: center; color: #0f172a;">Scan History (Last 10 Scans)</h2>

    <?php
    // Re-open connection for history
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        echo '<p class="error">Cannot load history - DB connection failed.</p>';
    } else {
        $query = "SELECT s.id, w.url, s.scan_time, s.status_code, COUNT(v.id) AS total_vulns,
                  SUM(CASE WHEN v.severity = 'High' THEN 1 ELSE 0 END) AS high,
                  SUM(CASE WHEN v.severity = 'Medium' THEN 1 ELSE 0 END) AS medium,
                  SUM(CASE WHEN v.severity = 'Low' THEN 1 ELSE 0 END) AS low
                  FROM scans s
                  JOIN websites w ON s.website_id = w.id
                  LEFT JOIN vulnerabilities v ON s.id = v.scan_id
                  GROUP BY s.id
                  ORDER BY s.scan_time DESC
                  LIMIT 10";

        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            echo '<table>';
            echo '<tr>';
            echo '<th>URL</th>';
            echo '<th>Scan Date</th>';
            echo '<th>Status</th>';
            echo '<th>Total Issues</th>';
            echo '<th>High / Med / Low</th>';
            echo '</tr>';

            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['url']) . '</td>';
                echo '<td>' . $row['scan_time'] . '</td>';
                echo '<td>' . $row['status_code'] . '</td>';
                echo '<td>' . $row['total_vulns'] . '</td>';
                echo '<td>' . $row['high'] . ' / ' . $row['medium'] . ' / ' . $row['low'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p style="text-align:center; color:#64748b;">No previous scans yet. Scan a few sites to see history!</p>';
        }
    }
    ?>

    <!-- CSV Export Section -->
    <h2 style="margin-top: 40px; text-align: center; color: #0f172a;">Export Data for Excel / Power BI</h2>
    <p style="text-align:center; color:#64748b;">Download all scan results as CSV file</p>

    <form action="export_csv.php" method="POST">
        <button type="submit" name="export_csv" class="export-btn">Download CSV</button>
    </form>

    <?php
    // Close DB connection at the very end
    if ($conn !== null) {
        $conn->close();
    }
    ?>
</div>

</body>
</html>