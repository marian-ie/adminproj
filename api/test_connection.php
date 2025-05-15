<?php
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");

    include 'database.php';
    include 'dbtest.php';

    $database = new Database();
    $db = $database->getConnection();

    $test = new DbTest($db);

    $response = $test->checkConnection();

    echo json_encode($response);

?>