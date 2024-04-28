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

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    //Check out if decoded data passed is not empty
    if (!empty($data)) {

        $emp_id = $data['id'];

        try {
        
            $sql = "SELECT qc_id, emp_name FROM `employees_tbl` WHERE _id = :emp_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':emp_id', $emp_id);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $qc_id = $row['qc_id'];

                // Update the logs_tbl table with the qc_id and current timestamp
                $sql = "UPDATE `logs_tbl` SET log_out = NOW(), log_status = 'out' WHERE qc_id = :qc_id AND log_out IS NULL";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':qc_id', $qc_id);
                
                if($stmt->execute()) {

                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Successfully logged out!'
                    ]);
                    exit;

                } else {
                    
                    // Failed to update logs_tbl
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => "Failed to update logout status!"
                    ]);
                    exit;
                }

            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'No user found!'
                ]);
                exit;
            }


        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
            exit;
        }
        
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to log out!'
        ]);
        exit;
    }
    


    //Check if the user is already logged in
    // if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {


    //    // Get the qc_id sent from the logout.html page
    // if (isset($_POST["qc_id"])) {

    //     try {
    //         // Update the logs_tbl table with the qc_id and current timestamp
    //         $sql = "UPDATE `logs_tbl` SET log_out = NOW(), log_status = 'out' WHERE qc_id = :qc_id AND log_out IS NULL";
    //         $stmt = $conn->prepare($sql);
    //         $stmt->bindParam(':qc_id', $qc_id);
    //         $stmt->execute();

    //         // Clear all session variables and destroy the session
    //         $_SESSION = array();
    //         session_destroy();

    //         exit;
    //     } catch (PDOException $e) {
    //         // Handle database errors
    //         echo "Error: " . $e->getMessage();
    //     }
        
    // } else {

    //     // User is already logged in, redirect to the home page or another appropriate page
    //     header("Location: http://localhost:8080/RDMSPinoy/components/index.html");
    //     exit;

    // }


    // }         
    // else {

    // }


?>