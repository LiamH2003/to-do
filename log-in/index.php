<?php
session_start(); // Start the session

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include('../database/db_connection.php'); 

// Define variables and initialize with empty values
$username = $password = "";
$login_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username and password are empty
    if (empty(trim($_POST["username"])) || empty(trim($_POST["password"]))) {
        $login_err = "Please enter username and password.";
    } else {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);

        // Prepare a SQL statement to prevent SQL injection
        $sql = "SELECT id, username, password FROM account WHERE username = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement
            $stmt->bind_param("s", $username);

            // Execute the statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();

                // Check if username exists
                if ($stmt->num_rows == 1) {
                    // Bind result variables
                    $stmt->bind_result($id, $username, $hashed_password);

                    if ($stmt->fetch()) {
                        // Verify password
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            // Fetch the user's email from the database
                            $email_sql = "SELECT email FROM account WHERE id = ?";
                            if ($email_stmt = $conn->prepare($email_sql)) {
                                $email_stmt->bind_param("i", $id);
                                $email_stmt->execute();
                                $email_stmt->bind_result($email);
                                if ($email_stmt->fetch()) {
                                    $_SESSION["email"] = $email;
                                }
                                $email_stmt->close();
                            }

                            // Redirect to the homepage
                            header("location: ../homepage/index.php");
                            exit(); // Ensure no further code runs after redirect
                        } else {
                            // Display an error message if password is not valid
                            $login_err = "Invalid password.";
                        }
                    }
                } else {
                    // Display an error message if username doesn't exist
                    $login_err = "Invalid username.";
                }

                // Close the statement
                $stmt->close();
            } else {
                // Output SQL error if the statement execution fails
                $login_err = "SQL execution error: " . $stmt->error;
            }
        } else {
            // Output SQL error if the statement preparation fails
            $login_err = "SQL preparation error: " . $conn->error;
        }
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do - Log in</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/loginstyle4.css">
    <link rel="icon" href="../images/to-do_icon.png" type="image/icon type">
</head>
<body>
    <div class="bodyDiv">
        <div class="mainDiv">
            <div class="contentDiv">
                <div class="innerContentDiv">
                    <h2>Login</h2>
                    <?php 
                    if (!empty($login_err)) {
                        echo '<div class="alert alert-danger">' . $login_err . '</div>';
                    }        
                    ?>
                    <form method="post">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>    
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <input type="submit" class="btn btn-primary" value="Login">
                        </div>
                        <p>Don't have an account? <a href="../register/">Sign up now</a>.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
