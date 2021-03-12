<?php
// Set headers.
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Equivalency.php';

// Establish a database connection.
$db = new Database();
$conn = $db->connect();

// If request was GET.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $equivalency = new Equivalency($conn); // Create new equivalency object.

    // Go through all possible query string parameters and assign values to the equivalency based on them.
    $equivalency->search_term = isset($_GET['search']) && !empty($_GET['search']) ? $_GET['search'] : '';
    $equivalency->id = isset($_GET['id']) && !empty($_GET['id']) ? $_GET['id'] : null;
    $equivalency->province_code = isset($_GET['province_code']) && !empty($_GET['province_code']) ? $_GET['province_code'] : null;
    $equivalency->province_name = isset($_GET['province_name']) && !empty($_GET['province_name']) ? $_GET['province_name'] : null;
    $equivalency->transfer_inst_code = isset($_GET['transfer_inst_code']) && !empty($_GET['transfer_inst_code']) ? $_GET['transfer_inst_code'] : null;
    $equivalency->transfer_inst_name = isset($_GET['transfer_inst_name']) && !empty($_GET['transfer_inst_name']) ? $_GET['transfer_inst_name'] : null;
    $equivalency->subject_code = isset($_GET['subject_code']) && !empty($_GET['subject_code']) ? $_GET['subject_code'] : null;
    $equivalency->subject_name = isset($_GET['subject_name']) && !empty($_GET['subject_name']) ? $_GET['subject_name'] : null;
    $equivalency->transfer_inst_course = isset($_GET['transfer_inst_course']) && !empty($_GET['transfer_inst_course']) ? $_GET['transfer_inst_course'] : null;
    $equivalency->transfer_credits = isset($_GET['transfer_credits']) && !empty($_GET['transfer_credits']) ? $_GET['transfer_credits'] : null;
    $equivalency->dal_course = isset($_GET['dal_course']) && !empty($_GET['dal_course']) ? $_GET['dal_course'] : null;
    $equivalency->dal_credits = isset($_GET['dal_credits']) && !empty($_GET['dal_credits']) ? $_GET['dal_credits'] : null;
    $equivalency->last_assessed_year = isset($_GET['last_assessed_year']) && !empty($_GET['last_assessed_year']) ? $_GET['last_assessed_year'] : null;
    $equivalency->last_assessed_semester = isset($_GET['last_assessed_semester']) && !empty($_GET['last_assessed_semester']) ? $_GET['last_assessed_semester'] : null;

    // Query the database based on search term and filters.
    $result = $equivalency->get();

    // If a start index and count were specified, return only a subset of results.
    if (isset($_GET['start_index']) && (!empty($_GET['start_index']) || $_GET['start_index'] == 0) && isset($_GET['count']) && !empty($_GET['count'])) {
        echo json_encode(array_slice($result->fetchAll(PDO::FETCH_ASSOC), $_GET['start_index'], $_GET['count']));
    } else { // Else return all results.
        echo json_encode($result->fetchAll(PDO::FETCH_ASSOC));
    }
}
