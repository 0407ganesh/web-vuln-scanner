<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Vulnerability Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            min-height: 100vh;
        }
        .navbar { background: rgba(30, 41, 59, 0.9) !important; backdrop-filter: blur(10px); }
        .card {
            background: rgba(30, 41, 59, 0.85);
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
            backdrop-filter: blur(8px);
        }
        .card-header {
            background: linear-gradient(to right, #6366f1, #8b5cf6);
            border-radius: 16px 16px 0 0;
            color: white;
            font-weight: bold;
        }
        .high { color: #f87171; }
        .medium { color: #fbbf24; }
        .low { color: #34d399; }
        canvas { max-height: 350px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">VulnScanner</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Scan</a></li>
                <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="text-center mb-5 fw-bold">Security Analytics Dashboard</h1>

    <?php
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'vuln_scanner';

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        echo '<p class="error">Database connection failed.</p>';
        exit;
    }

    // 1. Total scans
    $total_scans = $conn->query("SELECT COUNT(*) as count FROM scans")->fetch_assoc()['count'];

    // 2. Severity counts
    $severity_query = "SELECT 
                        SUM(CASE WHEN severity = 'High' THEN 1 ELSE 0 END) AS high,
                        SUM(CASE WHEN severity = 'Medium' THEN 1 ELSE 0 END) AS medium,
                        SUM(CASE WHEN severity = 'Low' THEN 1 ELSE 0 END) AS low
                      FROM vulnerabilities";
    $severity = $conn->query($severity_query)->fetch_assoc();

    // 3. Most common vulnerabilities
    $common_query = "SELECT vuln_type, COUNT(*) as count 
                     FROM vulnerabilities 
                     GROUP BY vuln_type 
                     ORDER BY count DESC 
                     LIMIT 5";
    $common_result = $conn->query($common_query);
    $common_labels = [];
    $common_data = [];
    while ($row = $common_result->fetch_assoc()) {
        $common_labels[] = $row['vuln_type'];
        $common_data[] = $row['count'];
    }

    $conn->close();
    ?>

    <div class="row g-4">
        <!-- Severity Pie Chart -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header text-center">Severity Distribution</div>
                <div class="card-body">
                    <canvas id="severityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Common Vulnerabilities Bar Chart -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header text-center">Most Common Vulnerabilities</div>
                <div class="card-body">
                    <canvas id="commonChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Scans</h5>
                    <h2 class="fw-bold"><?php echo $total_scans; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="high">High Severity</h5>
                    <h2 class="fw-bold"><?php echo $severity['high'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="medium">Medium Severity</h5>
                    <h2 class="fw-bold"><?php echo $severity['medium'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Severity Pie Chart
    const severityCtx = document.getElementById('severityChart').getContext('2d');
    new Chart(severityCtx, {
        type: 'doughnut',
        data: {
            labels: ['High', 'Medium', 'Low'],
            datasets: [{
                data: [<?php echo $severity['high'] ?? 0; ?>, <?php echo $severity['medium'] ?? 0; ?>, <?php echo $severity['low'] ?? 0; ?>],
                backgroundColor: ['#f87171', '#fbbf24', '#34d399'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#fff' } }
            }
        }
    });

    // Common Vulnerabilities Bar Chart
    const commonCtx = document.getElementById('commonChart').getContext('2d');
    new Chart(commonCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($common_labels); ?>,
            datasets: [{
                label: 'Occurrences',
                data: <?php echo json_encode($common_data); ?>,
                backgroundColor: '#6366f1',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { color: '#fff' } },
                x: { ticks: { color: '#fff' } }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>