<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");


include 'database.php';
include '../class/Bus.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

$bus = new Bus($db);

// Get the HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Get request body for PUT/POST methods
$inputData = null;
if ($method === 'POST' || $method === 'PUT') {
    $inputJSON = file_get_contents('php://input');
    $inputData = json_decode($inputJSON, true);
    
    if ($inputData === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON: " . json_last_error_msg()]);
        exit;
    }
}

switch ($method) {
    case 'GET':
        // Handle filters
        $filters = [];
        if (isset($_GET['location'])) $filters['location'] = $_GET['location'];
        if (isset($_GET['destination'])) $filters['destination'] = $_GET['destination'];
        if (isset($_GET['bus_type'])) $filters['bus_type'] = $_GET['bus_type'];
        
        if (isset($_GET['id'])) {
            // Get specific bus
            $busData = $bus->getBusById($_GET['id']);
            if ($busData) {
                echo json_encode(["status" => "success", "bus" => $busData]);
            } else {
                echo json_encode(["status" => "error", "message" => "Bus not found"]);
            }
        } else {
            // Get all buses or filtered buses
            $result = empty($filters) ? $bus->getAllBusDetails() : $bus->getFilteredBuses($filters);
            echo json_encode(["status" => "success", "buses" => $result]);
        }
        break;
    
    case 'POST':
        // Read and decode input JSON
        $inputJSON = file_get_contents('php://input');
        $inputData = json_decode($inputJSON, true);
    
        // Check if input data is valid JSON
        if (!$inputData) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }
    
        // Check for required fields
        if (!isset($inputData['bus_number'], $inputData['location'], $inputData['destination'], $inputData['bus_type'], $inputData['date'], $inputData['departure_time'], 
                  $inputData['arrival_time'], $inputData['available_seats'], $inputData['price'], 
                  $inputData['status'])) {
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            break;
        }
    
        // Assuming $bus is an instance of a class with a createNewBus method
        $result = $bus->createNewBus(
            $inputData['bus_number'],
            $inputData['location'],
            $inputData['destination'],
            $inputData['bus_type'],
            $inputData['date'],
            $inputData['departure_time'],
            $inputData['arrival_time'],
            $inputData['available_seats'],
            $inputData['price'],
            $inputData['status']
        );
    
        if ($result) {
            echo json_encode(["status" => "success", "message" => "Bus created successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create bus"]);
        }
        break;
        
    case 'PUT':
        // Update bus
        if (!isset($inputData['bus_id'], $inputData['bus_number'], $inputData['location'], $inputData['destination'], $inputData['bus_type'], $inputData['date'],
                  $inputData['departure_time'], $inputData['arrival_time'], $inputData['available_seats'], 
                  $inputData['price'], $inputData['status'])) {
            echo json_encode(["status" => "error", "message" => "Missing required fields for update"]);
            break;
        }
        
        $result = $bus->updateBus(
            $inputData['bus_id'],
            $inputData['bus_number'],
            $inputData['location'],
            $inputData['destination'],
            $inputData['bus_type'],
            $inputData['date'],
            $inputData['departure_time'],
            $inputData['arrival_time'],
            $inputData['available_seats'],
            $inputData['price'],
            $inputData['status']
        );
        
        if ($result) {
            echo json_encode(["status" => "success", "message" => "Bus updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update bus"]);
        }
        break;
    
    case 'DELETE':
        // Delete bus
        if (!isset($_GET['id'])) {
            echo json_encode(["status" => "error", "message" => "Bus ID is required for deletion"]);
            break;
        }
        
        $result = $bus->deleteBus($_GET['id']);
        if ($result) {
            echo json_encode(["status" => "success", "message" => "Bus deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete bus"]);
        }
        break;
    
    default:
        echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>