<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Smart Parking Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>

    /* ... existing styles ... */

    #statusPanel .status-box {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 15px;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }

    #parkingSlots .parking-slot {
        display: inline-block;
        width: 80px;
        height: 80px;
        background-color: #28a745;
        margin: 5px;
        text-align: center;
        line-height: 80px;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
    }

    #parkingSlots .occupied {
        background-color: #dc3545;
        cursor: not-allowed;
    }

    #parkingForm {
        margin-bottom: 30px;
    }

    #parkingSlots .reserved {
    background-color: #00008B; /* Blue color for reserved slots */
}



</style>


</head>
<body>
    <div class="container">

        <h1 class="text-center my-4">Real-Time Monitoring Dashboard</h1>
        <div class="d-flex justify-content-between align-items-center">
            <a href="/listing" class="btn btn-primary">Listing</a>

            <a href="{{ route('reservations.list') }}" class="btn btn-secondary float-right">Reservations</a>

</div>
        <div id="statusPanel" class="mb-4">
            <!-- Status boxes will be inserted here via JavaScript -->
        </div>
        <form id="parkingForm">
            <div class="form-group">
                <label for="licensePlateInput">License Plate Number:</label>
                <input type="text" class="form-control" id="licensePlateInput" required>
            </div>

            <div class="form-group">
                <label for="slotSelect">Select Parking Slot:</label>
                <select class="form-control" id="slotSelect" required>
                    <!-- Options will be inserted here via JavaScript -->
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Rent</button>
            <button type="button" class="btn btn-info" id="reserveButton">Reserve</button>


        </form>
        <div id="parkingSlots" class="my-4">
            <!-- Parking slots will be inserted here via JavaScript -->
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>


    const apiUrl = 'http://127.0.0.1:8000/api'; // Use this for all API requests


    $('#parkingForm').on('submit', function(e) {
        e.preventDefault();
        const licensePlate = $('#licensePlateInput').val();
        const selectedSlot = $('#slotSelect').val();
        parkVehicle(licensePlate, selectedSlot);
    });
    $(document).ready(function () {
            fetchDashboardStatus();
            fetchParkingSlots();

            $('#parkingForm').on('submit', function(e) {
                e.preventDefault();
                const licensePlate = $('#licensePlateInput').val();
                const selectedSlot = $('#slotSelect').val();
                parkVehicle(licensePlate, selectedSlot);
            });

        });


        $(document).ready(function() {
    // Reserve button click event
    $('#reserveButton').on('click', function(e) {
        e.preventDefault(); // Prevent the default form submit action
        const licensePlate = $('#licensePlateInput').val();
        const selectedSlot = $('#slotSelect').val();

        // Call the reserveVehicle function
        reserveVehicle(licensePlate, selectedSlot);
    })
});


    function fetchDashboardStatus() {
    $.ajax({
        url: `${apiUrl}/dashboard`,
        type: 'GET',
        success: function (data) {
            updateStatusPanel(data.total_slots, data.occupied_slots, data.reserved_slots, data.available_slots); // Add reserved slots here
            renderParkingSlots(data.recent_activity);
        },
        error: function (xhr, status, error) {
            console.error("Error fetching dashboard data:", error);
        }
    });
}

function updateStatusPanel(total, occupied, reserved, available) {
    const statusPanel = $('#statusPanel');
    statusPanel.empty(); // Clear existing status

    const totalSlotsElement = `<div class="status-box">Total Parking Spaces: ${total}</div>`;
    const occupiedSlotsElement = `<div class="status-box">Rented Parking Spaces: ${occupied}</div>`;
    const reservedSlotsElement = `<div class="status-box">Reserved Parking Spaces: ${reserved}</div>`; // Add this line
    const availableSlotsElement = `<div class="status-box">Available Parking Spaces: ${available}</div>`;

    statusPanel.append(totalSlotsElement, occupiedSlotsElement, reservedSlotsElement, availableSlotsElement); // Add reservedSlotsElement to the append
}


function fetchParkingSlots() {
    $.ajax({
        url: `${apiUrl}/slots`,
        type: 'GET',
        success: function (slots) {
            const slotSelect = $('#slotSelect');
            slotSelect.empty(); // Clear existing options

            slots.forEach(slot => {
                // Append slot to dropdown if it's not occupied or reserved
                if (!slot.is_occupied && !slot.is_reserved) {
                    slotSelect.append(new Option(`Slot ${slot.slot_number}`, slot.slot_number));
                }

                // Update the slot color based on its status
                let slotClass = slot.is_occupied ? 'occupied' : (slot.is_reserved ? 'reserved' : 'available');
                $(`.parking-slot[data-slot-number="${slot.slot_number}"]`).attr('class', `parking-slot ${slotClass}`);
            });
        },
        error: function (xhr, status, error) {
            console.error("Error fetching parking slots:", error);
        }
    });
}

// Trigger the function to fetch slots on document ready
$(document).ready(function () {
    fetchParkingSlots();
});


function reserveVehicle(licensePlate, slotNumber) {
    $.ajax({
        url: `${apiUrl}/reserve`,
        type: 'POST',
        data: {
            license_plate: licensePlate,
            slot_number: slotNumber
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            alert(response.message);

            // Update the UI to reflect the reservation
            let slotElement = $(`.parking-slot[data-slot-number="${slotNumber}"]`);
            slotElement.removeClass('available').addClass('reserved').off('click');

            // Update the counts
            let reservedCount = parseInt($('#statusPanel .reserved').text().split(' ')[2]);
            let availableCount = parseInt($('#statusPanel .available').text().split(' ')[2]);
            $('#statusPanel .reserved').text(`Reserved Parking Spaces: ${reservedCount + 1}`);
            $('#statusPanel .available').text(`Available Parking Spaces: ${availableCount - 1}`);

            // Re-fetch the slots to update the display
            fetchParkingSlots();
        },
        error: function(xhr) {
            alert('Error: ' + xhr.responseText);
        }
    });
}



function parkVehicle(licensePlate, slotNumber) {
    $.ajax({
        url: `${apiUrl}/park`,
        type: 'POST',
        data: {
            license_plate: licensePlate,
            slot_number: slotNumber
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            alert(response.message);

            // Update the slot to show it's occupied
            let slotElement = $(`.parking-slot[data-slot-number="${slotNumber}"]`);
            slotElement.removeClass('available reserved').addClass('occupied');

            // Update the occupied and available counts directly
            let occupiedCount = parseInt($('#statusPanel .occupied').text().split(' ')[2]);
            let availableCount = parseInt($('#statusPanel .available').text().split(' ')[2]);
            $('#statusPanel .occupied').text(`Occupied Parking Spaces: ${occupiedCount + 1}`);
            $('#statusPanel .available').text(`Available Parking Spaces: ${availableCount - 1}`);

            // Optionally, refresh the slots to ensure consistency
            fetchParkingSlots();
        },
        error: function (xhr) {
            alert('Error: ' + xhr.responseText);
        }
    });
}


function renderParkingSlots(slots) {
    const parkingSlotsContainer = $('#parkingSlots');
    parkingSlotsContainer.empty(); // Clear existing slots

    for (let i = 1; i <= 50; i++) {
        const slot = slots.find(s => s.slot_number === i);
        const slotClass = slot?.is_occupied ? 'occupied' : (slot?.reservation ? 'reserved' : 'available');
        const slotElement = `<div class="parking-slot ${slotClass}" data-slot-number="${i}">Slot ${i}</div>`;
        parkingSlotsContainer.append(slotElement);
    }

    // Add click event for parking slots
    $('.parking-slot').not('.occupied, .reserved').on('click', function () {
        const selectedSlot = $(this).data('slot-number');
        $('#slotSelect').val(selectedSlot).change();
    });
}











</script>




</body>
</html>
