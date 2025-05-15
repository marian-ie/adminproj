document.addEventListener("DOMContentLoaded", function() {
    loadBookings();
    
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

function updateStatus(reference, newStatus) {
    // Prevent event propagation
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