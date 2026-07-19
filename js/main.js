/**
 * Hotel Management System - Main JavaScript & jQuery Handlers
 */

$(document).ready(function () {
    // 1. Dynamic Booking Price & Day Calculator
    function calculateBookingTotal() {
        const checkInVal = $('#check_in').val();
        const checkOutVal = $('#check_out').val();
        const pricePerNight = parseFloat($('#room_price').val()) || 0;

        if (checkInVal && checkOutVal) {
            const checkInDate = new Date(checkInVal);
            const checkOutDate = new Date(checkOutVal);

            // Validate that check-out is after check-in
            if (checkOutDate > checkInDate) {
                const diffTime = Math.abs(checkOutDate - checkInDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                const totalPrice = (diffDays * pricePerNight).toFixed(2);

                $('#total_days_display').text(diffDays + ' Night(s)');
                $('#total_days_input').val(diffDays);

                $('#total_price_display').text('$' + totalPrice);
                $('#total_price_input').val(totalPrice);

                $('#date_error_msg').addClass('d-none');
                $('#btn_submit_booking').prop('disabled', false);

                // Perform AJAX availability check
                checkRoomAvailability();
            } else {
                $('#date_error_msg').removeClass('d-none').text('Check-out date must be after Check-in date.');
                $('#btn_submit_booking').prop('disabled', true);
                $('#total_days_display').text('0 Nights');
                $('#total_price_display').text('$0.00');
            }
        }
    }

    // Trigger calculation on date change
    $('#check_in, #check_out').on('change', function () {
        calculateBookingTotal();
    });

    // 2. AJAX Room Availability Check
    function checkRoomAvailability() {
        const roomId = $('#room_id').val();
        const checkIn = $('#check_in').val();
        const checkOut = $('#check_out').val();

        if (roomId && checkIn && checkOut) {
            $('#availability_status').html('<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Checking room availability...</span>');

            $.ajax({
                url: '../customer/check_availability_ajax.php',
                type: 'POST',
                data: {
                    room_id: roomId,
                    check_in: checkIn,
                    check_out: checkOut
                },
                dataType: 'json',
                success: function (response) {
                    if (response.available) {
                        $('#availability_status').html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle me-1"></i> Room is available for selected dates!</span>');
                        $('#btn_submit_booking').prop('disabled', false);
                    } else {
                        $('#availability_status').html('<span class="text-danger font-weight-bold"><i class="fas fa-times-circle me-1"></i> ' + response.message + '</span>');
                        $('#btn_submit_booking').prop('disabled', true);
                    }
                },
                error: function () {
                    $('#availability_status').html('<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Unable to verify availability via AJAX. Proceeding...</span>');
                }
            });
        }
    }

    // 3. Auto dismiss alert messages after 5 seconds
    setTimeout(function () {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
});
