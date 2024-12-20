<?php
date_default_timezone_set('Asia/Bangkok');

// Database connection
$servername = getenv('DB_SERVER') ?: 'localhost';  // Default to localhost if not set
$username = getenv('MYSQL_USER') ?: 'root';       // Default to root if not set
$password = getenv('MYSQL_PASSWORD') ?: '';            // Use empty string if not set
$dbname = getenv('MYSQL_DATABASE') ?: 'admin_dashboard';   // Default to admin_dashboard if not set

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->exec("SET time_zone = 'Asia/Bangkok';");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$currentDate = date("Y-m-d");

$stmt = $conn->prepare("
    SELECT sensor_id, 
           CONVERT_TZ(datetime, '+00:00', '+07:00') AS datetime, 
           data_kwh
    FROM update_log
    WHERE DATE(CONVERT_TZ(datetime, '+00:00', '+07:00')) = :currentDate
    ORDER BY datetime ASC
");
$stmt->bindParam(':currentDate', $currentDate);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$graphData = [];
$timeLabels = [];

foreach ($logs as $log) {
    $sensorId = $log['sensor_id'];
    $time = date("H:i:s", strtotime($log['datetime']));
    $dataKwh = $log['data_kwh'];

    if (!isset($graphData[$sensorId])) {
        $graphData[$sensorId] = [];
    }

    $graphData[$sensorId][$time] = $dataKwh;

    if (!in_array($time, $timeLabels)) {
        $timeLabels[] = $time;
    }
}

$datasets = [];
$colors = [
    'rgba(75, 192, 192, 1)',
    'rgba(255, 99, 132, 1)',
    'rgba(54, 162, 235, 1)',
    'rgba(255, 159, 64, 1)',
    'rgba(153, 102, 255, 1)',
];

$colorIndex = 0;
foreach ($graphData as $sensorId => $data) {
    $datasets[] = [
        'label' => 'Sensor ' . $sensorId,
        'data' => array_values($data),
        'borderColor' => $colors[$colorIndex % count($colors)],
        'backgroundColor' => 'rgba(0, 0, 0, 0)',
        'fill' => false
    ];
    $colorIndex++;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphs - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="wrapper">
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

        <div class="main">
            <nav class="navbar navbar-expand px-3 border-bottom">
                <button class="btn" id="sidebar-toggle" type="button">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </nav>

            <main class="content px-3 py-2">
                <div class="container-fluid">
                    <div class="mb-3">
                        <h4>Sensor Data (Real-Time Updates)</h4>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <canvas id="sensorGraph" width="400" height="200"></canvas>
                            <script>
                            var ctx = document.getElementById('sensorGraph').getContext('2d');
                            var sensorGraph = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: <?php echo json_encode($timeLabels); ?>,
                                    datasets: <?php echo json_encode($datasets); ?>
                                },
                                options: {
                                    responsive: true,
                                    scales: {
                                        x: {
                                            title: {
                                                display: true,
                                                text: 'Time (HH:mm:ss)'
                                            }
                                        },
                                        y: {
                                            title: {
                                                display: true,
                                                text: 'kWh'
                                            }
                                        }
                                    }
                                }
                            });
                            </script>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>