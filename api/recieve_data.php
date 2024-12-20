<?php
    $servername = getenv('DB_SERVER') ?: 'localhost';  // Default to localhost if not set
    $username = getenv('MYSQL_USER') ?: 'root';       // Default to root if not set
    $password = getenv('MYSQL_PASSWORD') ?: '';            // Use empty string if not set
    $dbname = getenv('MYSQL_DATABASE') ?: 'admin_dashboard';   // Default to admin_dashboard if not set


    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = file_get_contents("php://input");
        $decodedData = json_decode($data, true); 

        if ($decodedData 
            && isset($decodedData['sensor_id']) 
            && isset($decodedData['name']) 
            && isset($decodedData['data_kwh']) 
            && isset($decodedData['datetime']) 
            && isset($decodedData['gateway_id'])) {

            $stmt = $conn->prepare("
                INSERT INTO sensor_data (sensor_id, name, data_kwh, datetime, gateway_id) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $decodedData['sensor_id'],
                $decodedData['name'],
                $decodedData['data_kwh'],
                $decodedData['datetime'],
                $decodedData['gateway_id']
            ]);

            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid data format"]);
        }
    } else {
        // http_response_code(405); 
        echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    }
?>
