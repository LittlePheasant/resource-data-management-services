<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");


    // Start the session (if not already started)
    session_start();

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method !== 'POST') :
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Request!.Only POST method is allowed',
        ]);
        exit;
    endif;

    require 'dbConn.php';
    $database = new Operations();
    $conn = $database->dbConnection();

    var_dump($_POST);

    // Check if the user is already logged in
    // if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {


       
    //     // Get the qc_id sent from the logout.html page
    //     // if (isset($_POST["qc_id"])) {

    //     //     try {
    //     //         // Update the logs_tbl table with the qc_id and current timestamp
    //     //         $sql = "UPDATE `logs_tbl` SET log_out = NOW(), log_status = 'out' WHERE qc_id = :qc_id AND log_out IS NULL";
    //     //         $stmt = $conn->prepare($sql);
    //     //         $stmt->bindParam(':qc_id', $qc_id);
    //     //         $stmt->execute();
    
    //     //         // Clear all session variables and destroy the session
    //     //         $_SESSION = array();
    //     //         session_destroy();
    
    //     //         // Redirect the user to the login page
    //     //         header("Location: http://localhost:8080/RDMSPinoy/components/login.html");
    //     //         exit;
    //     //     } catch (PDOException $e) {
    //     //         // Handle database errors
    //     //         echo "Error: " . $e->getMessage();
    //     //     }
            
    //     // } else {

    //     //     // User is already logged in, redirect to the home page or another appropriate page
    //     //     header("Location: http://localhost:8080/RDMSPinoy/components/index.html");
    //     //     exit;

    //     // }

    // } 
    // else {

    //     // User is already logged in, redirect to the home page or another appropriate page
    //     header("Location: http://localhost:8080/RDMSPinoy/components/index.html");
    //     exit;

    // }


?>