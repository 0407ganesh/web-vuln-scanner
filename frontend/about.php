<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Vulnerability Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #1e293b;
            min-height: 100vh;
            margin: 0;
        }
        .navbar {
            background: rgba(30, 41, 59, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .navbar-brand { font-weight: bold; color: #c7d2fe !important; }
        .nav-link { color: #c7d2fe !important; transition: color 0.3s; }
        .nav-link:hover, .nav-link.active { color: #a5b4fc !important; }
        .container {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            padding: 50px;
            margin-top: 80px;
        }
        h1 { color: #4f46e5; font-weight: 800; }
        .card { background: rgba(255, 255, 255, 0.9); border: none; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.15); }
    </style>
</head>
<body>

<!-- Navbar - Same as index.php -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">VulnScanner</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Scan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h1>About This Project</h1>
    <div class="card p-5 mt-4">
        <p class="lead">This is an academic project developed as part of our curriculum to demonstrate basic web security scanning, database integration, and data analytics.</p>
        
        <h4 class="mt-4">Team Members</h4>
        <ul>
            <li>Ganesh M (U18IW23S0087)</li>
            <li>Thejas R (U18IW23S0172)</li>
        </ul>

        <h4 class="mt-4">Purpose</h4>
        <p>To promote security awareness by checking common web vulnerabilities (HTTPS, headers, cookies) and visualizing results.</p>

        <h4 class="mt-4">Technologies Used</h4>
        <ul>
            <li>Frontend: HTML, CSS, Bootstrap</li>
            <li>Backend: PHP</li>
            <li>Scanner: Python (requests library)</li>
            <li>Database: MySQL</li>
            <li>Analytics: CSV export for Excel/Power BI</li>
        </ul>

        <h4 class="mt-4">Disclaimer</h4>
        <p class="text-danger">This is for educational purposes only. Not intended for real-world security audits or penetration testing.</p>
    </div>
</div>

</body>
</html>