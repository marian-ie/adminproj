document.addEventListener("DOMContentLoaded", function() {
    loadBookings();
    loadPassengerOptions();
    loadBusOptions();
    
    document.getElementById("search-reference").addEventListener("keyup", searchBookings);
    
    document.getElementById("bookingForm").addEventListener("submit", function(e) {
        e.preventDefault();
        createBooking();
    });
});

function loadBookings() {
    console.log("Fetching bookings...");
    
    fetch("/api/booking_api.php")
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        const BookingTableBody = document.getElementById("BookingTableBody");
        BookingTableBody.innerHTML = "";

        if (data.status === "success" && data.bookings) {
            if (data.bookings.length === 0) {
                BookingTableBody.innerHTML = `<tr><td colspan="7">No bookings found.</td></tr>`;
                return;
            }

            data.bookings.forEach(booking => {
                const statusClass = booking.status === 'confirmed' ? 'status-confirmed' : 'status-pending';
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${booking.reference || ''}</td>
                    <td>${booking.reserve_name || 'N/A'}</td>
                    <td>${booking.bus_route || 'N/A'}</td>
                    <td>${booking.passenger_type || ''}</td>
                    <td>${booking.seat_number || ''}</td>
                    <td>${booking.remarks || ''}</td>
                    <td>
                        <button class="status-btn ${statusClass}" 
                                onclick="updateStatus('${booking.reference}', '${booking.status === 'confirmed' ? 'pending' : 'confirmed'}')">
                            ${booking.status || 'pending'}
                        </button>
                        <button class="delete-btn" onclick="deleteBooking('${booking.reference}')">Delete</button>
                    </td>
                `;
                BookingTableBody.appendChild(row);
            });
        } else {
            BookingTableBody.innerHTML = `<tr><td colspan="7">Error loading bookings: ${data.message || "Unknown error"}</td></tr>`;
        }
    })
    .catch(error => {
        console.error("Error fetching bookings:", error);
        document.getElementById("BookingTableBody").innerHTML = 
            `<tr><td colspan="7">Failed to load bookings.</td></tr>`;
    });
}

function loadPassengerOptions() {
    fetch("/api/passenger_api.php")
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        const passengerSelect = document.getElementById("passenger_id");
        passengerSelect.innerHTML = '<option value="">Select a passenger</option>';
        
        if (data.status === "success" && data.passengers) {
            data.passengers.forEach(passenger => {
                const option = document.createElement('option');
                option.value = passenger.passenger_id;
                option.textContent = `${passenger.first_name} ${passenger.last_name}`;
                passengerSelect.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error("Error loading passengers:", error);
        document.getElementById("passenger_id").innerHTML = 
            '<option value="">Error loading passengers</option>';
    });
}

function loadBusOptions() {
    fetch("/api/bus_api.php")
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        const busSelect = document.getElementById("bus_id");
        busSelect.innerHTML = '<option value="">Select a bus</option>';
        
        if (data.status === "success" && data.buses) {
            data.buses.forEach(bus => {
                const option = document.createElement('option');
                option.value = bus.bus_id;
                option.textContent = `${bus.location} to ${bus.destination} (${bus.bus_number})`;
                busSelect.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error("Error loading buses:", error);
        document.getElementById("bus_id").innerHTML = 
            '<option value="">Error loading buses</option>';
    });
}

function createBooking() {
    const formData = {
        reference: document.getElementById("reference").value,
        passenger_id: document.getElementById("passenger_id").value,
        bus_id: document.getElementById("bus_id").value,
        reserve_name: document.getElementById("reserve_name").value,
        passenger_type: document.getElementById("passenger_type").value,
        seat_number: document.getElementById("seat_number").value,
        remarks: document.getElementById("remarks").value,
        status: "pending"
    };

    if (!formData.reference || !formData.passenger_id || !formData.bus_id || 
        !formData.reserve_name || !formData.passenger_type || !formData.seat_number) {
        alert("Please fill in all required fields");
        return;
    }

    fetch("/api/booking_api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.status === "success") {
            alert("Booking created successfully!");
            document.getElementById("bookingForm").reset();
            loadBookings();
        } else {
            alert("Failed to create booking: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error creating booking:", error);
        alert("Failed to create booking.");
    });
}

function updateStatus(reference, newStatus) {
    fetch(`/api/booking_api.php?reference=${encodeURIComponent(reference)}`)
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.status === "success" && data.booking) {
            const formData = {
                reference: reference,
                passenger_type: data.booking.passenger_type,
                seat_number: data.booking.seat_number,
                reserve_name: data.booking.reserve_name,
                remarks: data.booking.remarks,
                status: newStatus
            };

            fetch("/api/booking_api.php", {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.status === "success") {
                    loadBookings();
                } else {
                    alert("Failed to update status: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error updating booking:", error);
                alert("Failed to update booking status.");
            });
        }
    })
    .catch(error => {
        console.error("Error fetching booking details:", error);
        alert("Failed to fetch booking details.");
    });
}

function deleteBooking(reference) {
    if (!confirm("Are you sure you want to delete this booking?")) return;

    fetch(`/api/booking_api.php?reference=${encodeURIComponent(reference)}`, {
        method: "DELETE"
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.status === "success") {
            alert("Booking deleted successfully!");
            loadBookings();
        } else {
            alert("Failed to delete booking: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error deleting booking:", error);
        alert("Failed to delete booking.");
    });
}

function filterPassengerType() {
    const filterValue = document.getElementById("filterPassType").value;
    
    fetch(`/api/booking_api.php${filterValue ? `?passenger_type=${encodeURIComponent(filterValue)}` : ''}`)
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        const BookingTableBody = document.getElementById("BookingTableBody");
        BookingTableBody.innerHTML = "";

        if (data.status === "success" && data.bookings) {
            if (data.bookings.length === 0) {
                BookingTableBody.innerHTML = `<tr><td colspan="7">No bookings found.</td></tr>`;
                return;
            }

            data.bookings.forEach(booking => {
                const statusClass = booking.status === 'confirmed' ? 'status-confirmed' : 'status-pending';
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${booking.reference || ''}</td>
                    <td>${booking.reserve_name || 'N/A'}</td>
                    <td>${booking.bus_route || 'N/A'}</td>
                    <td>${booking.passenger_type || ''}</td>
                    <td>${booking.seat_number || ''}</td>
                    <td>${booking.remarks || ''}</td>
                    <td>
                        <button class="status-btn ${statusClass}" 
                                onclick="updateStatus('${booking.reference}', '${booking.status === 'confirmed' ? 'pending' : 'confirmed'}')">
                            ${booking.status || 'pending'}
                        </button>
                        <button class="delete-btn" onclick="deleteBooking('${booking.reference}')">Delete</button>
                    </td>
                `;
                BookingTableBody.appendChild(row);
            });
        } else {
            BookingTableBody.innerHTML = `<tr><td colspan="7">Error loading bookings: ${data.message || "Unknown error"}</td></tr>`;
        }
    })
    .catch(error => {
        console.error("Error fetching filtered bookings:", error);
        document.getElementById("BookingTableBody").innerHTML = 
            `<tr><td colspan="7">Failed to load filtered bookings.</td></tr>`;
    });
}

function searchBookings() {
    const searchValue = document.getElementById("search-reference").value.toLowerCase();
    const rows = document.getElementById("BookingTableBody").getElementsByTagName("tr");
    
    for (let row of rows) {
        const referenceCell = row.getElementsByTagName("td")[0];
        if (referenceCell) {
            const reference = referenceCell.textContent.toLowerCase();
            row.style.display = reference.includes(searchValue) ? "" : "none";
        }
    }
}