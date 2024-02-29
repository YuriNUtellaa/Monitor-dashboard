    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Reserved Parking Slots</title>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <div class="container mt-4">
            <h2>Reserved Parking Slots</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Slot Number</th>
                        <th>License Plate</th>
                        <th>Reservation Time</th>
                        <th>Delete</th>
                    </tr>
                </thead>

                <tbody>
                    {{-- Reservations will be injected here --}}
                    @foreach($reservations as $reservation)
                        <tr>
                            <td>{{ $reservation->parking_slot_id }}</td>
                            <td>{{ $reservation->license_plate }}</td>
                            <td>{{ $reservation->created_at }}</td>

                            <td>
                                <button class="btn btn-primary update-reservation" data-id="{{ $reservation->id }}" data-slot="{{ $reservation->parking_slot_id }}" data-license="{{ $reservation->license_plate }}">Update</button>
                                <button class="btn btn-danger delete-reservation" data-id="{{ $reservation->id }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <a href="/monitor" class="btn btn-primary">Back to Dashboard</a>
        </div>






        <script>
        jQuery(document).on('click', '.delete-reservation', function(e) {
            e.preventDefault();
            var reservationId = jQuery(this).data('id');

            if (confirm('Are you sure you want to delete this reservation?')) {
                jQuery.ajax({
                    url: '/api/delete-reservation/' + reservationId,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        alert('Reservation deleted successfully');
                        location.reload(); // Reload the page to reflect the changes
                    },
                    error: function(xhr) {
                        alert('Error occurred: ' + xhr.responseText);
                    }
                });
            }
        });

        $(document).on('click', '.update-reservation', function() {
    var reservationId = $(this).data('id');
    var currentSlot = $(this).data('slot');
    var currentLicense = $(this).data('license');

    // Prompt for new values (you could use a modal or a form instead)
    var newSlot = prompt('Enter new slot number', currentSlot);
    var newLicense = prompt('Enter new license plate', currentLicense);

    if(newSlot !== null && newLicense !== null) {
        $.ajax({
            url: '/api/update-reservation/' + reservationId,
            type: 'PUT',
            data: {
                'slot_number': newSlot,
                'license_plate': newLicense,
                '_token': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert('Reservation updated successfully');
                location.reload(); // Reload the page to reflect the changes
            },
            error: function(xhr) {
                alert('Error occurred: ' + xhr.responseText);
            }
        });
    }
});

            </script>


    </body>
    </html>
