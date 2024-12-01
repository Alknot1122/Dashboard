<?php
$servername = getenv('DB_SERVER') ?: 'localhost';  // Default to localhost if not set
$username = getenv('MYSQL_USER') ?: 'root';       // Default to root if not set
$password = getenv('MYSQL_PASSWORD') ?: '';            // Use empty string if not set
$dbname = getenv('MYSQL_DATABASE') ?: 'admin_dashboard';   // Default to admin_dashboard if not set


header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['sensor_id']) && isset($_GET['page'])) {
        $sensor_id = $_GET['sensor_id'];
        $page = (int) $_GET['page'];
        $perPage = 10; 
        $start = ($page - 1) * $perPage;

        $stmt = $conn->prepare("SELECT * FROM update_log WHERE sensor_id = :sensor_id ORDER BY datetime DESC LIMIT :start, :perPage");
        $stmt->bindParam(':sensor_id', $sensor_id, PDO::PARAM_INT);
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($logs as &$log) {
            $datetime = new DateTime($log['datetime'], new DateTimeZone('UTC'));
            $datetime->setTimezone(new DateTimeZone('Asia/Bangkok'));
            $log['datetime'] = $datetime->format('Y-m-d H:i:s');  
        }

        $countStmt = $conn->prepare("SELECT COUNT(*) FROM update_log WHERE sensor_id = :sensor_id");
        $countStmt->bindParam(':sensor_id', $sensor_id, PDO::PARAM_INT);
        $countStmt->execute();
        $totalLogs = $countStmt->fetchColumn();

        $totalPages = ceil(min($totalLogs, 30) / $perPage); 

        echo json_encode([
            'status' => 'success',
            'data' => $logs,
            'pages' => $totalPages
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Sensor ID or page not provided"]);
    }
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>