<?php
    include '../api/database.php';
    include '../class/DbTest.php';

    $database = new Database();
    $conn = $database->getConnection();

    $test = new DbTest($conn);
    $connectionStatus = $test->checkConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HopStop Admin</title>
    <link rel="stylesheet" href="../style/styles2.css">
    <style>
        /* Additional styles for booking management */
        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .clickable-row:hover {
            background-color: #f5f5f5;
        }
        
        .status-btn {
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            font-weight: bold;
        }
        
        .status-pending {
            background-color: #ffaa00;
            color: white;
        }
        
        .status-confirmed {
            background-color: #4CAF50;
            color: white;
        }
        
        .search-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-filter select, 
        .search-filter input {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        /* Ensure the edit modal looks good */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .form-group input,
        .form-group select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            width: 100%;
        }
        
        .modal button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .modal button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="navbar">
            <div class="logo">HopStop Admin</div>
        </div>
    </div>

    <div class="container">
        <div class="table-container">
            <div class="page-header">
                <div class="page-title">Booking Management</div>
            </div>
            <div class="search-filter">
                <select id="filterPassType" onchange="filterPassengerType()">
                    <option value="">Filter By Type</option>
                    <option value="Regular">Regular</option>
                    <option value="PWD/Senior Citizen">PWD/Senior Citizen</option>
                    <option value="Student">Student</option>
                </select>
                <input type="text" id="search-reference" placeholder="Search By Reference">
            </div>
          
            <table>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Passenger Type</th>
                        <th>Seat Number</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="BookingTableBody">
                    <!-- Booking data will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Booking Modal -->
    <div id="editBookingModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>Update Booking Details</h3>

            <div class="form-grid">
                <div class="form-group">
                    <label for="editReference">Reference</label>
                    <input type="text" id="editReference" readonly>
                    
                    <label for="editPassengerType">Passenger Type</label>
                    <select id="editPassengerType">
                        <option value="">Select Type</option>
                        <option value="Regular">Regular</option>
                        <option value="PWD/Senior Citizen">PWD/Senior Citizen</option>
                        <option value="Student">Student</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="editSeatNumber">Seat Number</label>
                    <input type="number" id="editSeatNumber" placeholder="Seat Number">
                    
                    <label for="editPrice">Price</label>
                    <input type="number" id="editPrice" placeholder="Price">
                </div>
                
                <div class="form-group">
                    <label for="editStatus">Status</label>
                    <select id="editStatus">
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                    </select>
                </div>
            </div>
            <button onclick="updateBooking()">Save Changes</button>
        </div>
    </div>

    <script src="booking.js"></script>
</body>
</html>