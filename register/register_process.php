<?php
// Include database connection
include('../database/db_connection.php');

// Define variables and initialize with empty values
$username = $email = $password = $confirm_password = "";
$register_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username, email, and passwords are empty
    if (empty(trim($_POST["username"])) || empty(trim($_POST["email"])) || empty(trim($_POST["password"])) || empty(trim($_POST["confirm-password"]))) {
        $register_err = "Please fill in all fields.";
    } else {
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirm-password"]);

        // Validate password and confirm password match
        if ($password != $confirm_password) {
            $register_err = "Passwords do not match.";
        } else {
            // Prepare a SQL statement to prevent SQL injection
            $sql = "SELECT id FROM account WHERE username = ? OR email = ?";

            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement
                $stmt->bind_param("ss", $username, $email);

                // Execute the statement
                if ($stmt->execute()) {
                    $stmt->store_result();

                    // Check if username or email is already taken
                    if ($stmt->num_rows > 0) {
                        $register_err = "Username or email is already taken.";
                    } else {
                        // Password hashing
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        // Prepare an insert statement
                        $sql = "INSERT INTO account (username, email, password) VALUES (?, ?, ?)";

                        if ($stmt = $conn->prepare($sql)) {
                            // Bind variables to the prepared statement
                            $stmt->bind_param("sss", $username, $email, $hashed_password);

                            // Execute the statement
                            if ($stmt->execute()) {
                                // Redirect to login page
                                header("location: ../log-in/index.php");
                                exit();
                            } else {
                                $register_err = "Something went wrong. Please try again later.";
                            }
                        }
                    }

                    // Close the statement
                    $stmt->close();
                } else {
                    $register_err = "SQL execution error: " . $stmt->error;
                }
            } else {
                $register_err = "SQL preparation error: " . $conn->error;
            }
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
    <title>Registration Error</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body>
    <div class="bodyDiv">
        <div class="mainDiv">
            <div class="contentDiv">
                <div class="innerContentDiv">
                    <h2>Registration Error</h2>
                    <?php 
                    if (!empty($register_err)) {
                        echo '<div class="alert alert-danger">' . $register_err . '</div>';
                    }
                    ?>
                    <p>Go back to <a href="register.html">registration page</a>.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
