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
        $username = htmlspecialchars(trim($_POST["username"]));
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirm-password"]);

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $register_err = "Invalid email format.";
        } elseif ($password != $confirm_password) {
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
    <title>To Do - Register</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/registerstyle3.css">
    <link rel="icon" href="../images/to-do_icon.png" type="image/icon type">
</head>
<body>
    <div class="bodyDiv">
        <div class="mainDiv">
            <div class="contentDiv">
                <div class="innerContentDiv">
                    <h2>Create Your Account</h2>
                    <?php 
                    if (!empty($register_err)) {
                        echo '<div class="alert alert-danger">' . $register_err . '</div>';
                    }
                    ?>
                    <form method="POST">
                        <div class="inputGroup">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="inputGroup">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="inputGroup">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="inputGroup">
                            <label for="confirm-password">Confirm Password</label>
                            <input type="password" id="confirm-password" name="confirm-password" required>
                        </div>
                        <div class="inputGroup">
                            <input type="submit" value="Register">
                        </div>
                    </form>
                    <p>Already have an account? <a href="../log-in/index.php">Login here</a>.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
