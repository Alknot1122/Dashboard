<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "admin_dashboard";

header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['sensor_id']) && isset($_GET['page'])) {
        $sensor_id = $_GET['sensor_id'];
        $page = (int) $_GET['page'];
        $perPage = 10; 
        $start = ($page - 1) * $perPage;

        $stmt = $conn->prepare("SELECT * FROM update_log WHERE sensor_id = :sensor_id ORDER BY datetime DESC LIMIT 30");
        $stmt->bindParam(':sensor_id', $sensor_id);
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $logsOnPage = array_slice($logs, $start, $perPage);

        $totalPages = ceil(count($logs) / $perPage);

        echo json_encode([
            'status' => 'success',
            'data' => $logsOnPage,
            'pages' => $totalPages
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Sensor ID or page not provided"]);
    }
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
