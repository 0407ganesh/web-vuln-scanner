<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Vulnerability Scanner - Ganesh & Thejas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #1e293b; /* Dark text for readability */
            min-height: 100vh;
            margin: 0;
        }
        .navbar {
            background: rgba(30, 41, 59, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .navbar-brand {
            font-weight: bold;
            color: #c7d2fe !important;
        }
        .nav-link {
            color: #c7d2fe !important;
            transition: color 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            color: #a5b4fc !important;
        }
        .container {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            padding: 50px;
            margin-top: 80px; /* Space for fixed navbar */
        }
        h1 {
            color: #4f46e5;
            font-weight: 800;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #4f46e5;
            margin-top: 60px;
            text-align: center;
        }
        .subtitle {
            color: #4b5563;
            text-align: center;
            margin-bottom: 30px;
        }
        .disclaimer {
            color: #ef4444;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
        }
        .team {
            color: #4b5563;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-primary {
            background: #6366f1;
            border: none;
            padding: 14px 60px;
            font-size: 18px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #4f46e5;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.4);
        }
        .export-btn {
            background: #3b82f6;
            padding: 14px 50px;
            font-size: 18px;
            color: white;
        }
        .export-btn:hover {
            background: #2563eb;
        }
        .result {
            background: rgba(30, 41, 59, 0.03);
            border-radius: 16px;
            padding: 30px;
            margin-top: 40px;
            color: #1e293b;
        }
        pre {
            background: #1e293b;
            color: #c7d2fe;
            padding: 25px;
            border-radius: 12px;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.6);
        }
        .vuln-box {
            margin: 15px 0;
            padding: 18px;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            color: #1e293b;
        }
        .high { background: #fee2e2; color: #991b1b; border-left: 8px solid #ef4444; }
        .medium { background: #fef3c7; color: #92400e; border-left: 8px solid #f59e0b; }
        .success { color: #10b981; font-size: 22px; font-weight: bold; text-align: center; }
        .error { color: #ef4444; text-align: center; font-weight: bold; margin: 20px 0; background: rgba(239, 68, 68, 0.15); padding: 15px; border-radius: 8px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
            color: #1e293b;
        }
        table th {
            background: #4f46e5 !important;
            color: white;
            padding: 12px;
        }
        table td {
            padding: 12px;
            border: 1px solid #e5e7eb;
            color: #1e293b;
        }
        table tr:nth-child(even) {
            background: rgba(99, 102, 241, 0.05);
        }
    </style>
</head>
<body>

<!-- Navigation Bar - Correctly placed at the top -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-75 fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">VulnScanner</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Scan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h1>Web Application Vulnerability Scanner</h1>
    <p class="subtitle">Basic security check tool – HTTPS, Headers, Cookies (Educational Project)</p>

    <p class="disclaimer">Educational project only – NOT a professional penetration testing tool. For awareness and learning only. Does not replace tools like OWASP ZAP, Burp Suite, Nessus, Nikto.</p>
    <p class="team">Developed by: Ganesh M (U18IW23S0087) & Thejas R (U18IW23S0172)</p>

    <form method="POST">
        <div class="input-group mb-3 justify-content-center">
            <input type="url" name="url" class="form-control" style="max-width: 500px;" placeholder="https://example.com" required autofocus>
            <button class="btn btn-primary" type="submit" name="scan">Start Scan</button>
        </div>
    </form>

    <?php
    // Database connection
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
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

            $python_path = 'C:/Users/ganes/AppData/Local/Programs/Python/Python313/python.exe';
            $command = $python_path . ' ../scanner/scan.py ' . escapeshellarg($url);
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

                    if ($conn !== null && $results !== null) {
                        try {
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

                            $stmt = $conn->prepare("INSERT INTO scans (website_id, status_code) VALUES (?, ?)");
                            $status = $results['status_code'] ?? 0;
                            $stmt->bind_param("ii", $website_id, $status);
                            $stmt->execute();
                            $scan_id = $conn->insert_id;
                            $stmt->close();

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
                echo '<td style="color:#1e293b;">' . htmlspecialchars($row['url']) . '</td>';
                echo '<td style="color:#1e293b;">' . $row['scan_time'] . '</td>';
                echo '<td style="color:#1e293b;">' . $row['status_code'] . '</td>';
                echo '<td style="color:#1e293b;">' . $row['total_vulns'] . '</td>';
                echo '<td style="color:#1e293b;">' . $row['high'] . ' / ' . $row['medium'] . ' / ' . $row['low'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p style="text-align:center; color:#64748b;">No previous scans yet. Scan a few sites to see history!</p>';
        }

        $conn->close();
    }
    ?>

    <!-- CSV Export Section -->
    <h2 style="margin-top: 40px; text-align: center; color: #0f172a;">Export Data for Excel / Power BI</h2>
    <p style="text-align:center; color:#64748b;">Download all scan results as CSV file</p>

    <form action="../backend/export_csv.php" method="POST">
        <button type="submit" name="export_csv" class="export-btn btn btn-lg">Download CSV</button>
    </form>
</div>

</body>
</html>