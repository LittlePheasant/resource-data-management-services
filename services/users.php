<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");


    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === "OPTIONS") {
        die();
    }

    if ($method !== 'GET') :
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Request!. Only GET method is allowed',
        ]);
        exit;
    endif;


    require 'dbConn.php';
    $database = new Operations();
    $conn = $database->dbConnection();

    try {
        
        $sql = "SELECT * FROM `employees_tbl`";
        $stmt = $conn->prepare($sql);


        if ($stmt->execute()) {
            
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $row,
                'message' => 'Successfully loaded data!'
            ]);



        } else {
            // No data found
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'No data',
            ]);

            exit;
        }

    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode([
            'success'=> false,
            'message' => $e->getMessage()
        ]);
        exit;
    }

    
    



?>
