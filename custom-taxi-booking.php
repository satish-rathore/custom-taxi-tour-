<?php
/*
Plugin Name: Custom Taxi Booking for Tours
Description: Adds taxi booking options and fare calculation for WooCommerce bookable products in the "local-tour-package" category.
Version: 1.0
Author: Your Name
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register the Taxi post type
function register_taxi_post_type() {
    $labels = array(
        'name' => 'Taxis',
        'singular_name' => 'Taxi',
        'menu_name' => 'Taxis',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => false,
        'supports' => array('title', 'custom-fields'),
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-car',
    );

    register_post_type('taxi', $args);
}
add_action('init', 'register_taxi_post_type');
function enqueue_jquery_for_taxi_booking() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'enqueue_jquery_for_taxi_booking');

function enqueue_taxi_booking_script() {
    if (is_product()) { // Only enqueue on product pages
        wp_enqueue_script('jquery');
        wp_enqueue_script('taxi-booking-script', plugin_dir_url(__FILE__) . 'js/taxi-booking.js', array('jquery'), '1.0', true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_taxi_booking_script');

// Add custom fields for Taxi details
function taxi_custom_fields() {
    add_meta_box(
        'taxi_details_meta_box',
        'Taxi Details',
        'display_taxi_meta_box',
        'taxi',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'taxi_custom_fields');

function display_taxi_meta_box($post) {
    $rate_per_km = get_post_meta($post->ID, 'rate_per_km', true);
    $min_km_per_day = get_post_meta($post->ID, 'min_km_per_day', true);

    ?>
    <label>Rate per Kilometer:</label>
    <input type="number" name="rate_per_km" value="<?php echo esc_attr($rate_per_km); ?>" />
    <br><br>
    <label>Minimum Chargeable Kilometers per Day:</label>
    <input type="number" name="min_km_per_day" value="<?php echo esc_attr($min_km_per_day); ?>" />
    <?php
}

function save_taxi_custom_fields($post_id) {
    if (isset($_POST['rate_per_km'])) {
        update_post_meta($post_id, 'rate_per_km', sanitize_text_field($_POST['rate_per_km']));
    }
    if (isset($_POST['min_km_per_day'])) {
        update_post_meta($post_id, 'min_km_per_day', sanitize_text_field($_POST['min_km_per_day']));
    }
}
add_action('save_post', 'save_taxi_custom_fields');

// Add Taxi selection to bookable product if it's in the "local-tour-package" category
// Add Taxi selection to bookable product if it's in the "local-tour-package" category
// Add Taxi selection to bookable product if it's in the "local-tour-package" category
function display_taxi_selection() {
    global $post;

    // Get the product categories
    $product_cats = wp_get_post_terms($post->ID, 'product_cat');
    $category_slugs = wp_list_pluck($product_cats, 'slug');

    // Only show this for products in the "local-tour-package" category
    if (in_array('local-tour-package', $category_slugs)) {
        $taxis = get_posts(array(
            'post_type' => 'taxi',
            'posts_per_page' => -1,
        ));

        if (!empty($taxis)) {
            echo '<label for="taxi_type">Select Taxi:</label>';
            echo '<select id="taxi_type" name="taxi_type">';
            echo '<option value="">-- Select Taxi --</option>';

            foreach ($taxis as $taxi) {
                $rate = get_post_meta($taxi->ID, 'rate_per_km', true);
                $min_kms_per_day = get_post_meta($taxi->ID, 'min_km_per_day', true);
                echo '<option value="' . $taxi->ID . '" data-rate="' . esc_attr($rate) . '" data-min-kms="' . esc_attr($min_kms_per_day) . '">' . $taxi->post_title . ' (Rate: ' . $rate . '/km)</option>';
            }

            echo '</select>';
            echo '<div id="taxi_fare">Taxi Fare: </div>';
        }
    }
}
add_action('woocommerce_before_add_to_cart_button', 'display_taxi_selection');

// Add custom field for kilometers in the tour product (only for "local-tour-package" category)
function add_kms_field_to_product() {
    global $post;

    // Get the product categories
    $product_cats = wp_get_post_terms($post->ID, 'product_cat');
    $category_slugs = wp_list_pluck($product_cats, 'slug');

    // Only show this for products in the "local-tour-package" category
    if (in_array('local-tour-package', $category_slugs)) {
        woocommerce_wp_text_input(array(
            'id' => '_tour_kms',
            'label' => __('Tour Kilometers', 'woocommerce'),
            'desc_tip' => 'true',
            'description' => __('Enter the total kilometers for the tour.', 'woocommerce'),
            'type' => 'number',
        ));
    }
}
add_action('woocommerce_product_options_general_product_data', 'add_kms_field_to_product');

// Save the custom field value for kilometers
function save_kms_field_to_product($post_id) {
    $kms = isset($_POST['_tour_kms']) ? sanitize_text_field($_POST['_tour_kms']) : '';
    update_post_meta($post_id, '_tour_kms', $kms);
}
add_action('woocommerce_process_product_meta', 'save_kms_field_to_product');

// Taxi fare calculation using AJAX (JavaScript code)
// Taxi fare calculation using AJAX (JavaScript code)
// Taxi fare calculation using AJAX (JavaScript code)
// Taxi fare calculation using JavaScript
function custom_taxi_booking_scripts() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log("Taxi Fare Calculation Script Loaded");

            // Calculate the fare when the taxi type is changed or when the kilometers input is changed
            function calculateFare() {
                var taxiRate = $('#taxi_type').find(':selected').data('rate'); // Get selected taxi rate
                var minKmsPerDay = $('#taxi_type').find(':selected').data('min-kms'); // Get selected taxi minimum chargeable kilometers
                var numDays = $('input[name="tour_days"]').val(); // Get the number of tour days
                var kms = $('#tour_kms_value').val(); // Fetch the kilometers from the hidden field
                var fare = 0;

                // Debugging logs
                console.log('Taxi Rate: ', taxiRate);  
                console.log('Kilometers: ', kms);  
                console.log('Minimum Chargeable KMs per Day: ', minKmsPerDay);  
                console.log('Number of Tour Days: ', numDays);  

                // Calculate the minimum fare based on the minimum chargeable kilometers per day
                var minimumFare = minKmsPerDay * taxiRate * numDays;
                console.log('Minimum Fare: ', minimumFare);

                if (kms && taxiRate) {
                    var calculatedFare = kms * taxiRate; // Calculate fare based on actual kilometers
                    console.log('Calculated Fare (based on actual kilometers): ', calculatedFare);

                    // Apply the minimum fare rule
                    fare = Math.max(calculatedFare, minimumFare); // Use the higher of the calculated fare or minimum fare
                    console.log('Final Fare (after applying minimum fare rule): ', fare);

                    $('#taxi_fare').html('Taxi Fare: ' + fare); // Display the final fare
                } else {
                    $('#taxi_fare').html(''); // Clear fare if no valid inputs
                }

                // Store the calculated fare and selected taxi in hidden fields for WooCommerce cart submission
                $('input[name="taxi_fare"]').val(fare);
                $('input[name="taxi_id"]').val($('#taxi_type').val());

                console.log('Hidden Fields Updated');
            }

            // Trigger fare calculation when taxi type is changed
            $('#taxi_type').change(function() {
                calculateFare();
            });

            // Trigger fare calculation when kilometers input is changed
            $('#tour_kms_value').on('input', function() {
                calculateFare();
            });

            // Initialize hidden fields for taxi fare and taxi ID
            if (!$('input[name="taxi_fare"]').length) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'taxi_fare',
                    value: 0
                }).appendTo('form.cart');
            }

            if (!$('input[name="taxi_id"]').length) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'taxi_id',
                    value: ''
                }).appendTo('form.cart');
            }
        });
    </script>
    <?php
}
add_action('wp_footer', 'custom_taxi_booking_scripts');
// Output the kilometers value as a data attribute in the product page
function output_kilometers_on_product_page() {
    global $post;

    // Get the kilometers value for this product
    $kms = get_post_meta($post->ID, '_tour_kms', true);

    // Output the kilometers as a data attribute for JavaScript to access
    if ($kms) {
        echo '<input type="hidden" id="tour_kms_value" value="' . esc_attr($kms) . '">';
    }
}
add_action('woocommerce_single_product_summary', 'output_kilometers_on_product_page', 25);


// Capture the taxi fare and taxi ID when the product is added to the cart
// Capture the taxi fare and taxi ID when the product is added to the cart
// Capture the taxi fare and taxi ID when the product is added to the cart
function add_taxi_fare_to_cart_item_data($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['taxi_fare']) && isset($_POST['taxi_id'])) {
        // Save taxi fare and taxi ID in the cart item
        $cart_item_data['taxi_fare'] = sanitize_text_field($_POST['taxi_fare']);
        $cart_item_data['taxi_id'] = sanitize_text_field($_POST['taxi_id']);
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_taxi_fare_to_cart_item_data', 10, 3);
// Add the taxi fare to the product price in the cart
// Add the taxi fare to the product price in the cart
function add_taxi_fare_to_cart_total($cart_object) {
    foreach ($cart_object->get_cart() as $cart_item) {
        if (isset($cart_item['taxi_fare']) && !empty($cart_item['taxi_fare'])) {
            // Debugging: Log the fare being added to the cart total
            error_log('Adding Taxi Fare to Cart Total: ' . $cart_item['taxi_fare']);
            
            // Add taxi fare to the product price
            $cart_item['data']->set_price($cart_item['data']->get_price() + floatval($cart_item['taxi_fare']));
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'add_taxi_fare_to_cart_total', 10, 1);
// Display the taxi fare in the cart and checkout
function display_taxi_fare_in_cart($item_data, $cart_item) {
    if (isset($cart_item['taxi_fare'])) {
        $item_data[] = array(
            'name' => 'Taxi Fare',
            'value' => wc_price($cart_item['taxi_fare']),
        );
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'display_taxi_fare_in_cart', 10, 2);
// Output the number of days as a hidden field
function output_tour_days_on_product_page() {
    // Assuming your tour has a fixed number of days, you can output it like this
    echo '<input type="hidden" name="tour_days" value="1">';  // Replace '1' with the actual number of days
}
add_action('woocommerce_single_product_summary', 'output_tour_days_on_product_page', 25);
