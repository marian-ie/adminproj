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
    <link rel="stylesheet" href="../style/style.css">
    <style>
        /* Add some basic styles for clickable rows */
        #BusTableBody tr {
            cursor: pointer;
        }
        #BusTableBody tr:hover {
            background-color: #C7AFF7;
        }
        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 60px;
            border: 1px solid #888;
            width: 100%;
            max-width: 900px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .modal-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
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
                <div class="page-title">Bus Management</div>
                <button class="create-btn" onclick="openCreateBusModal()">Add Bus</button>
            </div>
            <div class="search-filter">
                <select id="filterLocation" onchange="filterBus()">
                    <option value="">Filter By Location</option>
                    <option value="Zamboanga">Zamboanga</option>
                    <option value="Pagadian">Pagadian</option>
                    <option value="Dipolog">Dipolog</option>
                    <option value="Cagayan">Cagayan</option>
                </select>
                 <select id="filterDestination" onchange="filterBus()">
                    <option value="">Filter By Destination</option>
                    <option value="Zamboanga">Zamboanga</option>
                    <option value="Pagadian">Pagadian</option>
                    <option value="Dipolog">Dipolog</option>
                    <option value="Cagayan">Cagayan</option>
                </select>
            
                <select id="filterBusType" onchange="filterBus()">
                    <option value="">Filter By Bus Type</option>
                    <option value="Air-Conditioned">Air-Conditioned</option>
                    <option value="Regular">Regular</option>
                </select>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Bus Number</th>
                        <th>Location</th>
                        <th>Destination</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Available Seats</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="BusTableBody">
                    <!-- Dynamic rows will be added here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Bus Modal -->
    <div id="createBusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCreateModal()">&times;</span>
            <h3>Add Bus Details</h3>


            <div class="form-grid">
                <div class="form-group">
                    <input type="text" id="busNumber" placeholder="Bus Number">
                </div>
                <div class="form-group">
                    <select id="location-select">
                        <option value="">Select Location</option>
                        <option value="Zamboanga">Zamboanga</option>
                        <option value="Pagadian">Pagadian</option>
                        <option value="Dipolog">Dipolog</option>
                        <option value="Cagayan">Cagayan</option>
                    </select>

                    <select id="destination-select">
                        <option value="">Select Destination</option>
                        <option value="Zamboanga">Zamboanga</option>
                        <option value="Pagadian">Pagadian</option>
                        <option value="Dipolog">Dipolog</option>
                        <option value="Cagayan">Cagayan</option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="date" id="date" placeholder="Date">
                    <input type="datetime-local" id="departureTime" placeholder="Departure Time">
                    <input type="datetime-local" id="arrivalTime" placeholder="Arrival Time">
                </div>

                <div class="form-group">
                    <select id="busType">
                        <option value="">Select Bus Type</option>
                        <option value="Air-Conditioned">Air-Conditioned</option>
                        <option value="Regular">Regular</option>
                    </select>

                    <input type="number" id="price" placeholder="Price">
                    <input type="number" id="availableSeats" placeholder="Available Seats">
                </div>
                <div class="form-group">
                    <select id="Status">
                        <option value="">Status</option>
                        <option value="Available">Available</option>
                        <option value="Unavailable">Unavailable</option>
                    </select>
                </div>
            </div>
            <button class="save-btn" onclick="createNewBus()">Save Details</button>
        </div>
    </div>

    <!-- Edit Bus Modal -->
    <div id="editBusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>Update Bus Details</h3>
            
            <!-- Hidden field for bus ID -->
            <input type="hidden" id="editBusId">

            <div class="form-grid">
                <div class="form-group">
                    <input type="text" id="editBusNumber" placeholder="Bus Number">
                </div>

                <div class="form-group">
                    <select id="editlocation-select">
                        <option value="">Select Location</option>
                        <option value="Zamboanga">Zamboanga</option>
                        <option value="Pagadian">Pagadian</option>
                        <option value="Dipolog">Dipolog</option>
                        <option value="Cagayan">Cagayan</option>
                    </select>

                    <select id="editdestination-select">
                        <option value="">Select Destination</option>
                        <option value="Zamboanga">Zamboanga</option>
                        <option value="Pagadian">Pagadian</option>
                        <option value="Dipolog">Dipolog</option>
                        <option value="Cagayan">Cagayan</option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="date" id="editDate" placeholder="Date">
                    <input type="datetime-local" id="editdepartureTime" placeholder="Departure Time">
                    <input type="datetime-local" id="editarrivalTime" placeholder="Arrival Time">
                </div>

                <div class="form-group">
                    <select id="editbusType">
                        <option value="">Select Bus Type</option>
                        <option value="Air-Conditioned">Air-Conditioned</option>
                        <option value="Regular">Regular</option>
                    </select>

                    <input type="number" id="editprice" placeholder="Price">
                    <input type="number" id="editavailableSeats" placeholder="Available Seats">
                </div>
                <div class="form-group">
                    <select id="editStatus">
                        <option value="">Status</option>
                        <option value="Available">Available</option>
                        <option value="Unavailable">Unavailable</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button class="save-btn" onclick="updateBus()">Save Changes</button>
                <button class="delete-btn" onclick="deleteBus()">Delete Bus</button>
            </div>
        </div>
    </div>

    <script src="bus.js"></script>
</body>
</html>