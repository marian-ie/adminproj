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
        
        /* Error message styling */
        .error-message {
            background-color: #ffeeee;
            border: 1px solid #ffaaaa;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            color: #cc0000;
        }
        
        /* Loading indicator */
        .loading {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
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
            
            <!-- Optional: Display error messages here -->
            <div id="errorMessages"></div>
            
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
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="BookingTableBody">
                    <tr>
                        <td colspan="4" class="loading">Loading bookings...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Make sure path to JS is correct -->
    <script src="booking.js"></script>
</body>
</html>