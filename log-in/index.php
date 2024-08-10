<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do - Log in</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/loginstyle.css">
    <link rel="icon" href="../images/to-do_icon.png" type="image/icon type">
</head>
<body>
    <div class="bodyDiv">
        <div class="mainDiv">
            <div class="contentDiv">
                <div class="innerContentDiv">
                    <h2>Login to Your Account</h2>
                    <form action="/login" method="POST">
                        <div class="inputGroup">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="inputGroup">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="inputGroup">
                            <input type="submit" value="Login">
                        </div>
                    </form>
                    <p>Don't have an account? <a href="../register/index.php">Register here</a>.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>