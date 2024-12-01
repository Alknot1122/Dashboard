-- Create the database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS admin_dashboard;

-- Use the database
USE admin_dashboard;

-- Create the sensor_data table
CREATE TABLE IF NOT EXISTS sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,      -- Primary key to uniquely identify each entry
    sensor_id INT NOT NULL,                 -- Sensor ID (e.g., reference to the sensor)
    name VARCHAR(255) NOT NULL DEFAULT 'Unnamed Sensor', -- Default value for sensor name
    data_kwh DECIMAL(10, 2) NOT NULL,       -- Power consumption in kWh (stored as a decimal)
    datetime DATETIME NOT NULL,             -- Date and time of the data recording
    gateway_id INT NOT NULL                 -- Gateway ID (e.g., reference to the gateway)
);

-- Create the update_log table
CREATE TABLE IF NOT EXISTS update_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_id INT NOT NULL,
    data_kwh DECIMAL(10, 2) NOT NULL,
    datetime DATETIME NOT NULL,
    FOREIGN KEY (sensor_id) REFERENCES sensor_data(id) ON DELETE CASCADE
);

-- Optionally, create an index for faster search operations
CREATE INDEX idx_sensor_id ON sensor_data(sensor_id);
CREATE INDEX idx_gateway_id ON sensor_data(gateway_id);
