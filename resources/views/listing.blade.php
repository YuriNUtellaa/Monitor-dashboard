    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Parked Vehicles Listing</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f7f7f7;
            }
            .container {
                background: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                margin-top: 40px;
            }
            .btn-back {
                background-color: #6c757d;
                color: white;
            }
            .btn-delete {
                background-color: #dc3545;
                color: white;
            }
            .btn-delete:hover {
                background-color: #c82333;
            }
            th, td {
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="text-center mb-4">
                <a href="/monitor" class="btn btn-back">Back to Dashboard</a>
            </div>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Slot Number</th> 
                        <th>License Plate</th>
                        <th>Parked At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($parkedVehicles as $vehicle)
                    <tr id="vehicle-{{ $vehicle->id }}">
                        <td>
                            <input type="number" class="form-control" value="{{ $vehicle->slot_number }}" id="slot_number_{{ $vehicle->id }}">
                        </td>
                        <td>
                            <input type="text" class="form-control" value="{{ $vehicle->license_plate }}" id="license_plate_{{ $vehicle->id }}">
                        </td>
                        <td>{{ $vehicle->parked_at->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <button class="btn btn-primary btn-update" 
                            data-id="{{ $vehicle->id }}" 
                            data-parking-slot-id="{{ $vehicle->parkingSlot->id }}">Update</button>

                        <button class="btn btn-danger btn-delete" data-parking-slot-id="{{ $vehicle->parkingSlot->id }}">Delete</button>
                    

                        </td>
                    </tr>
                @endforeach
                </tbody>
                
            </table>
        </div>

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script>
 $(document).on('click', '.btn-delete', function() {
            let parkingSlotId = $(this).data('parking-slot-id');
            let token = $('meta[name="csrf-token"]').attr('content');
            let button = $(this); // Capture the button that was clicked

            if (confirm('Are you sure you want to delete this vehicle?')) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    url: '/api/delete-vehicle/' + parkingSlotId,
                    type: 'DELETE',
                    success: function(result) {
                        alert('Vehicle deleted successfully');
                        // Remove the parent row of the clicked button from the table
                        button.closest('tr').remove();
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';
                        alert('Error: ' + errorMsg);
                    }
                });
            }
        });
        $(document).on('click', '.btn-update', function() {
    let parkingSlotId = $(this).data('parking-slot-id'); // Get the parking slot ID
    let newSlotNumber = $('#slot_number_' + parkingSlotId).val();
    let newLicensePlate = $('#license_plate_' + parkingSlotId).val();
    let token = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': token
        },
        url: '/api/update-vehicle/' + parkingSlotId, // Use parkingSlotId here
        type: 'PUT',
        data: {
            new_slot_number: newSlotNumber,
            new_license_plate: newLicensePlate
        },
        success: function(result) {
            alert('Vehicle updated successfully');
            window.location.reload();
        },
        error: function(xhr) {
            let errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';
            alert('Error: ' + errorMsg);
        }
    });
});




    
    


        </script>
    </body>
    </html>
