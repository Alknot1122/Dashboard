<?php
    $servername = getenv('DB_SERVER') ?: 'localhost';  // Default to localhost if not set
    $username = getenv('MYSQL_USER') ?: 'root';       // Default to root if not set
    $password = getenv('MYSQL_PASSWORD') ?: '';            // Use empty string if not set
    $dbname = getenv('MYSQL_DATABASE') ?: 'admin_dashboard';   // Default to admin_dashboard if not set

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $e->getMessage()]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['sensor_id'], $input['gateway_id'], $input['data_kwh'])) {
        $sensor_id = $input['sensor_id'];
        $gateway_id = $input['gateway_id'];
        $data_kwh = $input['data_kwh'];
        $datetime = date("Y-m-d H:i:s");
        $name = isset($input['name']) ? $input['name'] : null;  // Check if 'name' is provided

        try {
            $conn->beginTransaction();

            // Check if the sensor_id already exists in sensor_data
            $checkStmt = $conn->prepare("
                SELECT id FROM sensor_data 
                WHERE sensor_id = :sensor_id AND gateway_id = :gateway_id
            ");
            $checkStmt->bindParam(':sensor_id', $sensor_id);
            $checkStmt->bindParam(':gateway_id', $gateway_id);
            $checkStmt->execute();
            $existingSensor = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingSensor) {
                // Update the existing sensor data
                $stmt = $conn->prepare("
                    UPDATE sensor_data 
                    SET data_kwh = :data_kwh, datetime = :datetime 
                    WHERE sensor_id = :sensor_id AND gateway_id = :gateway_id
                ");
                $stmt->bindParam(':sensor_id', $sensor_id);
                $stmt->bindParam(':gateway_id', $gateway_id);
                $stmt->bindParam(':data_kwh', $data_kwh);
                $stmt->bindParam(':datetime', $datetime);

                if (!$stmt->execute()) {
                    throw new Exception("Failed to update the data");
                }
                $sensorIdForLog = $existingSensor['id'];  // Use the ID from the sensor_data table
            } else {
                // Insert a new sensor data record
                $stmt = $conn->prepare("
                    INSERT INTO sensor_data (sensor_id, gateway_id, data_kwh, datetime, name) 
                    VALUES (:sensor_id, :gateway_id, :data_kwh, :datetime, :name)
                ");
                $stmt->bindParam(':sensor_id', $sensor_id);
                $stmt->bindParam(':gateway_id', $gateway_id);
                $stmt->bindParam(':data_kwh', $data_kwh);
                $stmt->bindParam(':datetime', $datetime);

                // Only bind the 'name' parameter if it's provided
                if ($name !== null) {
                    $stmt->bindParam(':name', $name);
                } else {
                    // Bind a null value if 'name' is not provided
                    $nullValue = null;
                    $stmt->bindParam(':name', $nullValue, PDO::PARAM_NULL);
                }

                if (!$stmt->execute()) {
                    throw new Exception("Failed to insert new data");
                }

                // Get the last inserted ID from the sensor_data table
                $sensorIdForLog = $conn->lastInsertId();  
            }

            // Log the update or insert action using the correct sensor_id
            $logStmt = $conn->prepare("
                INSERT INTO update_log (sensor_id, data_kwh, datetime) 
                VALUES (:sensor_id, :data_kwh, :datetime)
            ");
            $logStmt->bindParam(':sensor_id', $sensorIdForLog);  // Use the ID from sensor_data
            $logStmt->bindParam(':data_kwh', $data_kwh);
            $logStmt->bindParam(':datetime', $datetime);

            if ($logStmt->execute()) {
                $conn->commit();
                echo json_encode(["status" => "success", "message" => "Data updated or inserted and logged successfully"]);
            } else {
                throw new Exception("Failed to log the update");
            }
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>