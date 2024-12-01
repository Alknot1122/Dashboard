<?php
    $servername = getenv('DB_SERVER') ?: 'localhost';  // Default to localhost if not set
    $username = getenv('MYSQL_USER') ?: 'root';       // Default to root if not set
    $password = getenv('MYSQL_PASSWORD') ?: '';            // Use empty string if not set
    $dbname = getenv('MYSQL_DATABASE') ?: 'admin_dashboard';   // Default to admin_dashboard if not set


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch sensor data
$stmt = $conn->prepare("SELECT sensor_id, name, data_kwh, datetime, gateway_id FROM sensor_data");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- External Stylesheets and Scripts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside id="sidebar" class="js-sidebar">
            <div class="sidebar-logo">
                <a href="#">Dashboard</a>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="graph.php">
                        <i class="fas fa-chart-line"></i> Graphs
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main">
            <nav class="navbar navbar-expand px-3 border-bottom">
                <button class="btn" id="sidebar-toggle" type="button">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </nav>

            <main class="content px-3 py-2">
                <div class="container-fluid">
                    <div class="mb-3">
                        <h4>Dashboard</h4>
                    </div>
                    <div class="row">
                        <?php foreach ($results as $row): ?>
                        <?php
                            $formatted_kwh = is_float($row['data_kwh']) && floor($row['data_kwh']) != $row['data_kwh']
                                ? number_format($row['data_kwh'], 2)
                                : number_format($row['data_kwh']);
                            
                            $formatted_id = sprintf("ID %02d: %s", $row['sensor_id'], $row['name']);
                            ?>
                        <div class="col-12 col-md-3 d-flex">
                            <div class="card flex-fill border-0 shadow-lg"
                                data-sensor-id="<?php echo $row['sensor_id']; ?>">
                                <div class="card-body py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <p class="text-muted mb-2"><?php echo $formatted_id; ?></p>
                                        <div class="divider"></div>

                                        <h4 class="mb-3 display-4">
                                            <?php echo $formatted_kwh; ?>
                                            <span class="kwh-text">kWh</span>
                                        </h4>
                                        <div class="divider"></div>

                                        <p class="text-muted small">Last Updated:
                                            <strong><?php echo htmlspecialchars($row['datetime']); ?></strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Log History Modal -->
    <div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logModalLabel">Log History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul id="logList" class="list-group"></ul>
                    <div id="logMessage"></div>
                    <ul id="pagination" class="pagination"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- External Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>