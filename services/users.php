<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: GET, POST");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");


    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === "OPTIONS") {
        die();
    }

    if (!in_array($method, ['GET', 'POST'])) :
        http_response_code(405);
        echo json_encode([
            'success' => 0,
            'message' => 'Bad Request!. Method is not allowed',
        ]);
        exit;
    endif;


    require 'dbConn.php';
    $database = new Operations();
    $conn = $database->dbConnection();

    try {

        if(isset($_GET['_id'])){

            $emp_id = $_GET['_id'];

            $sql = "SELECT user_role FROM `employees_tbl` WHERE _id = :emp_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':emp_id', $emp_id);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_role = $row['user_role'];

            // Check the user's role
            if ($user_role === 'superadmin') {
                // Query to retrieve data for superadmin
                $sql = "SELECT * FROM `employees_tbl`";
            } elseif ($user_role === 'admin') {
                // Query to retrieve data for admin
                $sql = "SELECT * FROM `employees_tbl` WHERE user_role != 'superadmin'";
            } else {
                // No data found for other roles
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'No data found for this role.',
                ]);
                exit;
            }

            // Prepare and execute the SQL query
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            // Fetch the data
            $usersRow = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT * FROM `logs_tbl`";
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $logsRow = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Initialize an empty array to store the combined data
            $combinedData = [];

            // Loop through each user row
            foreach ($usersRow as $user) {
                // Initialize an array to hold user data with log status
                $userDataWithLogStatus = $user;
                // Loop through each log row to find a match with user ID
                foreach ($logsRow as $log) {
                    if ($log['_id'] === $user['_id']) {
                        // Add log status to the user data
                        $userDataWithLogStatus['log_status'] = $log['log_status'];
                        // Add user data with log status to the combined data array
                        $combinedData[] = $userDataWithLogStatus;
                        // Break the loop as we found a match for the user ID
                        break;
                    }
                }
                // If no log status found for the user, add 'out' as default log status
                if (!isset($userDataWithLogStatus['log_status'])) {
                    $userDataWithLogStatus['log_status'] = 'out';
                    // Add user data with 'out' log status to the combined data array
                    $combinedData[] = $userDataWithLogStatus;
                }
            }

            // Return the combined data as JSON response
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $combinedData,
                'message' => 'Successfully loaded data!',
            ]);


        } elseif(isset($_POST)){
            // code for post
        } else{
            // 
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
