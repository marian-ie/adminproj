document.addEventListener("DOMContentLoaded", function() {
    loadBookings();
    
    // Set up modal close buttons
    const closeButtons = document.querySelectorAll(".close");
    closeButtons.forEach(button => {
        button.addEventListener("click", function() {
            document.getElementById("editBookingModal").style.display = "none";
        });
    });

    // Set up search by reference
    document.getElementById("search-reference").addEventListener("keyup", function() {
        searchBookings();
    });
});

function loadBookings() {
    fetch("../api/booking_api.php")
    .then(response => response.json())
    .then(data => {
        let BookingTableBody = document.getElementById("BookingTableBody");
        BookingTableBody.innerHTML = "";

        if (data.status === "success" && data.bookings) {
            data.bookings.forEach(booking => {
                let row = document.createElement('tr');
                
                // Create status button class based on current status
                const statusClass = booking.status === 'Confirmed' ? 'status-confirmed' : 'status-pending';
                
                row.innerHTML = `
                    <td>${booking.reference || ''}</td>
                    <td>${booking.passenger_type || ''}</td>
                    <td>${booking.seat_number || ''}</td>
                    <td>₱${booking.price || '0'}</td>
                    <td>
                        <button class="status-btn ${statusClass}" 
                                onclick="updateStatus('${booking.reference}', '${booking.status === 'Confirmed' ? 'Pending' : 'Confirmed'}')">
                            ${booking.status || 'Pending'}
                        </button>
                    </td>
                `;

                // Make the row clickable
                row.classList.add("clickable-row");
                row.addEventListener("click", function(e) {
                    // Don't open modal if clicking on the status button
                    if (!e.target.classList.contains('status-btn')) {
                        openEditModal(
                            booking.reference, 
                            booking.passenger_type,
                            booking.seat_number, 
                            booking.price,
                            booking.status
                        );
                    }
                });

                BookingTableBody.appendChild(row);
            });
        } else {
            console.error("Error loading bookings:", data.message || "Unknown error");
        }
    })
    .catch(error => {
        console.error("Error fetching data:", error);
    });
}

function openEditModal(reference, passengerType, seatNumber, price, status) {
    // Set the form values
    document.getElementById("editReference").value = reference;
    document.getElementById("editPassengerType").value = passengerType;
    document.getElementById("editSeatNumber").value = seatNumber;
    document.getElementById("editPrice").value = price;
    document.getElementById("editStatus").value = status;
    
    // Display the modal
    document.getElementById("editBookingModal").style.display = "block";
}

function closeEditModal() {
    document.getElementById("editBookingModal").style.display = "none";
}

function updateBooking() {
    const formData = {
        reference: document.getElementById("editReference").value,
        passenger_type: document.getElementById("editPassengerType").value,
        seat_number: document.getElementById("editSeatNumber").value,
        price: document.getElementById("editPrice").value,
        status: document.getElementById("editStatus").value
    };

    // Validate data
    for (const key in formData) {
        if (!formData[key]) {
            alert(`${key.replace(/_/g, ' ')} is required!`);
            return;
        }
    }

    fetch("../api/booking_api.php", {
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
            loadBookings();
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}

function updateStatus(reference, newStatus) {
    // Prevent event propagation to avoid opening the edit modal
    event.stopPropagation();
    
    // Get the original booking data first
    fetch(`../api/booking_api.php?booking_id=${reference}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === "success" && data.booking) {
            const booking = data.booking;
            
            // Create the updated data
            const formData = {
                reference: reference,
                passenger_type: booking.passenger_type,
                seat_number: booking.seat_number,
                price: booking.price,
                status: newStatus
            };

            // Update the booking with new status
            fetch("../api/booking_api.php", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    loadBookings(); // Refresh the table
                } else {
                    alert("Failed to update status: " + data.message);
                }
            });
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}

function filterPassengerType() {
    const filterValue = document.getElementById("filterPassType").value;
    
    if (filterValue === "") {
        loadBookings(); // Reset to show all bookings
    } else {
        fetch(`../api/booking_api.php?passenger_type=${encodeURIComponent(filterValue)}`)
        .then(response => response.json())
        .then(data => {
            let BookingTableBody = document.getElementById("BookingTableBody");
            BookingTableBody.innerHTML = "";

            if (data.status === "success" && data.bookings) {
                data.bookings.forEach(booking => {
                    let row = document.createElement('tr');
                    
                    // Create status button class based on current status
                    const statusClass = booking.status === 'Confirmed' ? 'status-confirmed' : 'status-pending';
                    
                    row.innerHTML = `
                        <td>${booking.reference || ''}</td>
                        <td>${booking.passenger_type || ''}</td>
                        <td>${booking.seat_number || ''}</td>
                        <td>₱${booking.price || '0'}</td>
                        <td>
                            <button class="status-btn ${statusClass}" 
                                    onclick="updateStatus('${booking.reference}', '${booking.status === 'Confirmed' ? 'Pending' : 'Confirmed'}')">
                                ${booking.status || 'Pending'}
                            </button>
                        </td>
                    `;

                    row.classList.add("clickable-row");
                    row.addEventListener("click", function(e) {
                        if (!e.target.classList.contains('status-btn')) {
                            openEditModal(
                                booking.reference, 
                                booking.passenger_type,
                                booking.seat_number, 
                                booking.price,
                                booking.status
                            );
                        }
                    });

                    BookingTableBody.appendChild(row);
                });
            }
        });
    }
}

function searchBookings() {
    const searchValue = document.getElementById("search-reference").value.toLowerCase();
    const rows = document.getElementById("BookingTableBody").getElementsByTagName("tr");
    
    for (let i = 0; i < rows.length; i++) {
        const referenceCell = rows[i].getElementsByTagName("td")[0];
        if (referenceCell) {
            const reference = referenceCell.textContent || referenceCell.innerText;
            if (reference.toLowerCase().indexOf(searchValue) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
}
