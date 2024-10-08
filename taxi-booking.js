jQuery(document).ready(function($) {
    console.log("Taxi Fare Calculation Script Loaded");

    $('#taxi_type').change(function() {
        var taxiRate = $('#taxi_type').find(':selected').data('rate'); // Get selected taxi rate
        var kms = $('#tour_kms_value').val(); // Fetch the kilometers from the hidden field

        console.log("Taxi Rate:", taxiRate);
        console.log("Kilometers:", kms);
    });
});
