<?php
// Prevent any output before JSON response
ob_start();

// Connect to the database
$conn = mysqli_connect("localhost", "root", "", "rsvp_database");

// Check connection
if (!$conn) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Connection error: ' . mysqli_connect_error()]);
    exit;
}

$send = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rsvp_name']) && isset($_POST['rsvp_email']) && filter_var($_POST['rsvp_email'], FILTER_VALIDATE_EMAIL) && isset($_POST['rsvp_phone']) && isset($_POST['rsvp_coming']) && isset($_POST['rsvp_events']) && isset($_POST['rsvp_guests']) && isset($_POST['rsvp_access_card'])) {
    $rsvp_name = mysqli_real_escape_string($conn, $_POST['rsvp_name']);
    $rsvp_email = mysqli_real_escape_string($conn, $_POST['rsvp_email']);
    $rsvp_phone = mysqli_real_escape_string($conn, $_POST['rsvp_phone']);
    $rsvp_coming = mysqli_real_escape_string($conn, $_POST['rsvp_coming']);
    $rsvp_events = mysqli_real_escape_string($conn, $_POST['rsvp_events']);
    $rsvp_guests = mysqli_real_escape_string($conn, $_POST['rsvp_guests']);
    $rsvp_access_card = mysqli_real_escape_string($conn, $_POST['rsvp_access_card']);
    $rsvp_message = isset($_POST['rsvp_message']) && strlen($_POST['rsvp_message']) > 0 ? mysqli_real_escape_string($conn, $_POST['rsvp_message']) : '-';

    // Check for duplicate email or phone
    $check_query = "SELECT rsvp_email, rsvp_phone FROM rsvp_form WHERE rsvp_email = '$rsvp_email' OR rsvp_phone = '$rsvp_phone'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $existing = mysqli_fetch_assoc($check_result);
        if ($existing['rsvp_email'] === $rsvp_email) {
            $error_message = 'This email is already registered for the event.';
        } elseif ($existing['rsvp_phone'] === $rsvp_phone) {
            $error_message = 'This phone number is already registered for the event.';
        }
    } else {
        // Insert into database
        $insert_query = "INSERT INTO rsvp_form (rsvp_name, rsvp_email, rsvp_phone, rsvp_coming, rsvp_events, rsvp_guests, rsvp_access_card, rsvp_message) 
                        VALUES ('$rsvp_name', '$rsvp_email', '$rsvp_phone', '$rsvp_coming', '$rsvp_events', '$rsvp_guests', '$rsvp_access_card', '$rsvp_message')";
        
        if (mysqli_query($conn, $insert_query)) {
            $send = true;
        } else {
            $error_message = 'Failed to submit RSVP: ' . mysqli_error($conn);
        }
    }

    // Clear output buffer
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => $send, 'error' => $error_message]);
    exit;
} else {
    // Handle invalid or missing inputs
    $error_message = 'Invalid or missing form data.';
    if (!isset($_POST['rsvp_email']) || !filter_var($_POST['rsvp_email'], FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid or missing email address.';
    }
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $error_message]);
    exit;
}

// Ensure no output after this point
ob_end_clean();
?>