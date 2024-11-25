<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "admin_dashboard";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['sensor_id']) && isset($input['gateway_id']) && isset($input['data_kwh'])) {
        $sensor_id = $input['sensor_id'];
        $gateway_id = $input['gateway_id'];  
        $data_kwh = $input['data_kwh'];

        $datetime = date("Y-m-d H:i:s"); 

        $stmt = $conn->prepare("UPDATE sensor_data SET data_kwh = :data_kwh, datetime = :datetime WHERE sensor_id = :sensor_id AND gateway_id = :gateway_id");
        $stmt->bindParam(':sensor_id', $sensor_id);
        $stmt->bindParam(':gateway_id', $gateway_id);
        $stmt->bindParam(':data_kwh', $data_kwh);
        $stmt->bindParam(':datetime', $datetime);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Data updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update data"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid data"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
