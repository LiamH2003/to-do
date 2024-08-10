<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do - Register</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/registerstyle.css">
    <link rel="icon" href="../images/to-do_icon.png" type="image/icon type">
</head>
<body>
    <div class="bodyDiv">
        <div class="mainDiv">
            <div class="contentDiv">
                <div class="innerContentDiv">
                    <h2>Create Your Account</h2>
                    <form action="register_process.php" method="POST">
                        <div class="inputGroup">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="inputGroup">
                            <label for="email">Email</label>
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
