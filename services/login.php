<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");


    // Start the session (if not already started)
    session_start();


    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === "OPTIONS") {
        die();
    }

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


    // Check if successfully sent email and password
    if(isset($_POST['email']) && isset($_POST['password']) && !empty($_POST['email']) && !empty($_POST['password'])){

        $email = $_POST['email'];

        // Check if valid credentials
        try{

            $sql = "SELECT _id, qc_id, emp_name, department, emp_password FROM `employees_tbl` WHERE emp_email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $password = $_POST['password'];

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $emp_id = $row['_id'];

                if(password_verify($password, $row['emp_password'])){

                    $sql = "SELECT log_status FROM `logs_tbl` WHERE emp_id = :emp_id AND date = CURDATE() AND log_out IS NULL";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':emp_id', $emp_id);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {

                        http_response_code(200);
                        echo json_encode([
                            'success' => true,
                            'id' => $row['_id'],
                            'name' => $row['emp_name'],
                            'message' => 'User is already logged in!'
                        ]);
                        exit;

                    } else {

                        $sql = "INSERT INTO `logs_tbl` (emp_id, date, log_in) 
                                VALUES (:emp_id, CURDATE(), NOW())";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':emp_id', $emp_id);
                        
                        if($stmt->execute()){

                            http_response_code(200);
                            echo json_encode([
                                'success' => true,
                                'id' => $row['_id'],
                                'name' => $row['emp_name'],
                                'message' => 'Login Successfully!'
                            ]);
                        } else {

                            http_response_code(400);
                            echo json_encode([
                                'success' => false,
                                'message' => 'Unable to log in!'
                            ]);

                        }

                    }

                } else {
                    // Password mismatch
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid credentials!',
                    ]);
                    exit;

                }


            } else {
                // User not found
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'User not found',
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

        http_response_code(400);
        echo json_encode([
            'success'=> false,
            'message' => 'No credentials sent!'
        ]);
        exit;
    }



    

    // Check if the user is already logged in
    // if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {

    //     // Check if qc_id is passed successfully
    //     if(isset($_POST['email'])) {

    //         $email = $_POST['email'];

    //         // Check if the user's log_out status is NULL (i.e., they are still logged in)
    //         $sql = "SELECT log_status FROM logs_tbl WHERE emp_email = :email AND log_status IS NULL";
    //         $stmt = $conn->prepare($sql);
    //         $stmt->bindParam(':email', $email);
    //         $stmt->execute();

    //         if ($stmt->fetchColumn()) {
    //             // User is still logged in, redirect to the appropriate page
    //             http_response_code(200);
    //             header("Location: http://localhost:8080/RDMSPinoy/components/index.html");
    //             exit;
    //         } else {
    //             // User is logged out, proceed with the login process
    //             unset($_SESSION["loggedin"]);
    //         }
    //     }

    // }
    // else {

    //     // User is not logged in, attempt login
    //     $email = $_POST['email'];
    //     $password = $_POST['password'];

    //     try {
    //         $sql = "SELECT _id, qc_id, emp_name, department, emp_password FROM `employees_tbl` WHERE emp_email = :email";
    //         $stmt = $conn->prepare($sql);
    //         $stmt->bindParam(':email', $email);
    //         $stmt->execute();

    //         if ($stmt->rowCount() > 0) {
    //             $row = $stmt->fetch(PDO::FETCH_ASSOC);
    //             $hashedPasswordFromDb = password_hash($row['emp_password'],PASSWORD_DEFAULT);


    //             if (password_verify($password, $hashedPasswordFromDb)) {
    //                 // Password is correct, proceed with login
    //                 $fetchedData = $row;
    //                 $_id = $fetchedData['_id'];
    //                 $qc_id = $fetchedData['qc_id'];

    //                 $sql = "INSERT INTO `logs_tbl` (_id, qc_id, log_in) VALUES (:id, :qc_id, NOW())";
    //                 $stmt = $conn->prepare($sql);
    //                 $stmt->bindParam(':id', $_id);
    //                 $stmt->bindParam(':qc_id', $qc_id);
    //                 $stmt->execute();

    //                 // Set session variables to indicate user is logged in
    //                 $_SESSION["loggedin"] = true;
    //                 $_SESSION["qc_id"] = $qc_id;

    //                 http_response_code(200);
    //                 header("Location: http://localhost:8080/RDMSPinoy/components/index.html");

    //                 // // Return user data as JSON response
    //                 // echo json_encode([
    //                 //     'success' => true,
    //                 //     'data' => $fetchedData,
    //                 // ]);
    //                 exit;
    //             } else {
    //                 // Invalid password
    //                 http_response_code(401);
    //                 echo json_encode([
    //                     'success' => false,
    //                     'message' => 'Invalid credentials',
    //                 ]);
    //                 exit;
    //             }
    //         } else {
    //             // User not found
    //             http_response_code(404);
    //             echo json_encode([
    //                 'success' => false,
    //                 'message' => 'User not found',
    //             ]);
    //             exit;
    //         }
            
            
    //     } catch (PDOException $e) {
    //         http_response_code(500);
    //         echo json_encode([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ]);
    //         exit;
    //     }

    // }


?>
