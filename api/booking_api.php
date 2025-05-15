<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include 'database.php';
include '../class/booking.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

$booking = new Bookings($db);

// Handle preflight requests for CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
        $filters = [];
        if (isset($_GET['passenger_type'])) {
            $filters['passenger_type'] = $_GET['passenger_type'];
        }

        if (isset($_GET['booking_id'])) {
            $bookingData = $booking->getBusById($_GET['booking_id']);
            if ($bookingData) {
                echo json_encode(["status" => "success", "booking" => $bookingData]);
            } else {
                echo json_encode(["status" => "error", "message" => "Booking not found"]);
            }
        } else {
            $result = empty($filters) ? $booking->getAllBusDetails() : $booking->getFilteredBuses($filters);
            echo json_encode(["status" => "success", "bookings" => $result]);
        }
        break;

    case 'POST':
        if (!isset($inputData['reference'], $inputData['passenger_type'], $inputData['seat_number'], $inputData['price'], $inputData['status'])) {
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            break;
        }

        $result = $booking->createBooking(
            $inputData['reference'],
            $inputData['passenger_type'],
            $inputData['seat_number'],
            $inputData['price'],
            $inputData['status']
        );

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Booking created successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create booking"]);
        }
        break;

    case 'PUT':
        if (!isset($inputData['reference'], $inputData['passenger_type'], $inputData['seat_number'], $inputData['price'], $inputData['status'])) {
            echo json_encode(["status" => "error", "message" => "Missing required fields for update"]);
            break;
        }

        $result = $booking->updateBooking(
            $inputData['reference'],
            $inputData['passenger_type'],
            $inputData['seat_number'],
            $inputData['price'],
            $inputData['status']
        );

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Booking updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update booking"]);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['booking_id'])) {
            echo json_encode(["status" => "error", "message" => "Booking ID is required for deletion"]);
            break;
        }

        $result = $booking->deleteBus($_GET['booking_id']);
        if ($result) {
            echo json_encode(["status" => "success", "message" => "Booking deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete booking"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>