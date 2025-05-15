document.addEventListener("DOMContentLoaded", function() {
    loadBus();
    
    // Set up modal close buttons
    const closeButtons = document.querySelectorAll(".close");
    closeButtons.forEach(button => {
        button.addEventListener("click", function() {
            document.getElementById("createBusModal").style.display = "none";
            document.getElementById("editBusModal").style.display = "none";
        });
    });
});

function loadBus() {
    fetch("../api/bus_api.php")
    .then(response => response.json())
    .then(data => {
        let BusTableBody = document.getElementById("BusTableBody");
        BusTableBody.innerHTML = "";

        if (data.status === "success" && data.buses) {
            data.buses.forEach(bus => {
                // Format dates for better readability
                const formattedDate = bus.date ? new Date(bus.date).toLocaleDateString() : '';
                const formattedDeparture = bus.departure_time ? formatTimeDisplay(bus.departure_time) : '';
                const formattedArrival = bus.arrival_time ? formatTimeDisplay(bus.arrival_time) : '';
                
                let row = document.createElement('tr');
                row.innerHTML = `
                    <td>${bus.bus_number || ''}</td>
                    <td>${bus.location || ''}</td>
                    <td>${bus.destination || ''}</td>
                    <td>${bus.bus_type || ''}</td>
                    <td>${formattedDate}</td>
                    <td>${formattedDeparture}</td>
                    <td>${formattedArrival}</td>
                    <td>${bus.available_seats || ''}</td>
                    <td>${bus.price || ''}</td>
                    <td>${bus.status || ''}</td>
                `;
                
                // Make the row clickable
                row.style.cursor = "pointer";
                row.addEventListener("click", function() {
                    openUpdateModal(
                        bus.bus_id, 
                        bus.bus_number,
                        bus.location, 
                        bus.destination,
                        bus.bus_type,
                        bus.date,
                        bus.departure_time,
                        bus.arrival_time, 
                        bus.available_seats, 
                        bus.price, 
                        bus.status
                    );
                });
                
                BusTableBody.appendChild(row);
            });
        } else {
            console.error("Error loading buses:", data.message || "Unknown error");
        }
    })
    .catch(error => {
        console.error("Error fetching data:", error);
    });
}

// Helper function to format time display
function formatTimeDisplay(timeString) {
    try {
        const date = new Date(timeString);
        if (isNaN(date.getTime())) return timeString; // Return original if invalid
        return date.toLocaleTimeString();
    } catch (e) {
        return timeString; // Return original on error
    }
}

// Helper function to format date for datetime-local input
function formatDateTimeForInput(dateTimeString) {
    try {
        const date = new Date(dateTimeString);
        if (isNaN(date.getTime())) return ''; // Return empty if invalid
        
        // Format: YYYY-MM-DDThh:mm
        return date.getFullYear() + '-' + 
               String(date.getMonth() + 1).padStart(2, '0') + '-' + 
               String(date.getDate()).padStart(2, '0') + 'T' + 
               String(date.getHours()).padStart(2, '0') + ':' + 
               String(date.getMinutes()).padStart(2, '0');
    } catch (e) {
        return ''; // Return empty on error
    }
}

function openCreateBusModal() {
    document.getElementById("createBusModal").style.display = "block";
}

function closeCreateModal() {
    document.getElementById("createBusModal").style.display = "none";
}

function openUpdateModal(bus_id, bus_number, location, destination, bus_type, date, departure_time, arrival_time, available_seats, price, status) {
    // Store bus_id in a hidden field for update/delete operations
    document.getElementById("editBusId").value = bus_id;
    
    // Set form values
    document.getElementById("editBusNumber").value = bus_number || '';
    document.getElementById("editlocation-select").value = location || '';
    document.getElementById("editdestination-select").value = destination || '';
    
    // Format date for input
    if (date) {
        try {
            const dateObj = new Date(date);
            if (!isNaN(dateObj.getTime())) {
                const formattedDate = dateObj.toISOString().split('T')[0];
                document.getElementById("editDate").value = formattedDate;
            } else {
                document.getElementById("editDate").value = date;
            }
        } catch (e) {
            document.getElementById("editDate").value = date;
        }
    } else {
        document.getElementById("editDate").value = '';
    }
    
    // Handle departure and arrival times
    document.getElementById("editdepartureTime").value = formatDateTimeForInput(departure_time);
    document.getElementById("editarrivalTime").value = formatDateTimeForInput(arrival_time);
    
    document.getElementById("editbusType").value = bus_type || '';
    document.getElementById("editprice").value = price || '';
    document.getElementById("editavailableSeats").value = available_seats || '';
    document.getElementById("editStatus").value = status || '';

    // Show the modal
    document.getElementById("editBusModal").style.display = "block";
}

function closeEditModal() {
    document.getElementById("editBusModal").style.display = "none";
}

function createNewBus() {
    try {
        const formData = {
            bus_number: document.getElementById("busNumber")?.value,
            location: document.getElementById("location-select")?.value,
            destination: document.getElementById("destination-select")?.value,
            bus_type: document.getElementById("busType")?.value,
            date: document.getElementById("date")?.value,
            departure_time: document.getElementById("departureTime")?.value,
            arrival_time: document.getElementById("arrivalTime")?.value,
            available_seats: document.getElementById("availableSeats")?.value,
            price: document.getElementById("price")?.value,
            status: document.getElementById("Status")?.value
        };

       
        for (const key in formData) {
            if (formData[key] === "" || formData[key] == null) {
                alert(`${key.replace(/_/g, ' ')} is required!`);
                return;
            }
        }

        fetch("../api/bus_api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(formData)
        })
        
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                closeCreateModal();
                loadBus();
                // Reset form fields
                document.getElementById("busNumber").value = "";
                document.getElementById("location-select").value = "";
                document.getElementById("destination-select").value = "";
                document.getElementById("busType").value = "";
                document.getElementById("date").value = "";
                document.getElementById("departureTime").value = "";
                document.getElementById("arrivalTime").value = "";
                document.getElementById("availableSeats").value = "";
                document.getElementById("price").value = "";
                document.getElementById("Status").value = "";
            }
        })
        .catch(error => {
            console.error("Error adding details:", error);
            alert("Failed to create new bus. Please try again.");
        });
    } catch (err) {
        console.error("Unexpected error:", err);
        alert("An unexpected error occurred. Check the console for details.");
    }
}

function updateBus() {
    const formData = {
        bus_id: document.getElementById("editBusId").value,
        bus_number: document.getElementById("editBusNumber").value,
        location: document.getElementById("editlocation-select").value,
        destination: document.getElementById("editdestination-select").value,
        bus_type: document.getElementById("editbusType").value,
        date: document.getElementById("editDate").value,
        departure_time: document.getElementById("editdepartureTime").value,
        arrival_time: document.getElementById("editarrivalTime").value,
        available_seats: document.getElementById("editavailableSeats").value,
        price: document.getElementById("editprice").value,
        status: document.getElementById("editStatus").value
    };

    // Validate data
    for (const key in formData) {
        if (!formData[key]) {
            alert(`${key.replace(/_/g, ' ')} is required!`);
            return;
        }
    }

    fetch("../api/bus_api.php", {
        method: "PUT",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.status === "success") {
            closeEditModal();
            loadBus();
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}

function deleteBus() {
    const id = document.getElementById("editBusId").value;
    
    if (confirm("Are you sure you want to delete this bus?")) {
        fetch(`../api/bus_api.php?id=${id}`, {
            method: "DELETE"
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                closeEditModal();
                loadBus();
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    }
}

function filterBus() {
    const destination = document.getElementById("filterDestination").value;
    const location = document.getElementById("filterLocation").value;
    const busType = document.getElementById("filterBusType").value;
    
    let url = "../api/bus_api.php";
    const params = [];
    
    if (destination) params.push(`destination=${encodeURIComponent(destination)}`);
    if (location) params.push(`location=${encodeURIComponent(location)}`);
    if (busType) params.push(`bus_type=${encodeURIComponent(busType)}`);
    
    if (params.length > 0) {
        url += "?" + params.join("&");
    }
    
    fetch(url)
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            let BusTableBody = document.getElementById("BusTableBody");
            BusTableBody.innerHTML = "";
            
            data.buses.forEach(bus => {
                // Format dates for better readability
                const formattedDate = bus.date ? new Date(bus.date).toLocaleDateString() : '';
                const formattedDeparture = bus.departure_time ? formatTimeDisplay(bus.departure_time) : '';
                const formattedArrival = bus.arrival_time ? formatTimeDisplay(bus.arrival_time) : '';
                
                let row = document.createElement('tr');
                row.innerHTML = `
                    <td>${bus.bus_number || ''}</td>
                    <td>${bus.location || ''}</td>
                    <td>${bus.destination || ''}</td>
                    <td>${bus.bus_type || ''}</td>
                    <td>${formattedDate}</td>
                    <td>${formattedDeparture}</td>
                    <td>${formattedArrival}</td>
                    <td>${bus.available_seats || ''}</td>
                    <td>${bus.price || ''}</td>
                    <td>${bus.status || ''}</td>
                `;
                
                row.style.cursor = "pointer";
                row.addEventListener("click", function() {
                    openUpdateModal(
                        bus.bus_id, 
                        bus.bus_number,
                        bus.location, 
                        bus.destination, 
                        bus.bus_type,
                        bus.date,
                        bus.departure_time,
                        bus.arrival_time, 
                        bus.available_seats, 
                        bus.price, 
                        bus.status
                    );
                });
                
                BusTableBody.appendChild(row);
            });
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}