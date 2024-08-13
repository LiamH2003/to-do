<?php
session_start(); // Start the session

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../start/index.php");
    exit;
}

// Check if the session variables are set before displaying them
$username = isset($_SESSION["username"]) ? htmlspecialchars($_SESSION["username"]) : "Guest";
$email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "No Email";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/homestyle6.css">
    <link rel="icon" href="../images/to-do_icon.png" type="image/icon type">
    <script type="text/javascript" src="../script/homescript.js"></script>
</head>
<body>
    <div class="bodyDiv">
        <div class="mainDiv">
            <div class="leftNavDiv">
                <a class="innerLeftDiv" href="../start/index.php">
                    <div class="iconDiv">
                        <img src="../images/profile.png" width="70" height="70" alt="">
                    </div>
                    <div class="profileText">
                        <!-- PHP Code to Display Username and Email -->
                        <h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4>
                        <span><?php echo htmlspecialchars($_SESSION["email"]); ?></span>
                    </div>
                    <div class="logoutDiv">
                        <img src="../images/logout.png" width="40" height="40" alt="">
                    </div>
                </a>
                <div class="searchbarDiv">
                    <form action="#" method="get" class="searchForm">
                        <input type="text" class="searchInput" placeholder="Search...">
                    </form>
                </div>
                <div class="navList">
                    <ul class="navCategories">
                        <li class="navItem" data-category="personal">Personal</li>
                        <li class="navItem" data-category="work">Work</li>
                        <li class="navItem" data-category="others">Others</li>
                    </ul>
                </div>
                <div class="newList">
                    <img src="../images/plus.png" class="plusIcon" alt="Add New List">
                    <span class="text">New List</span>
                </div>
            </div>
            <div class="contentDiv">
                <div class="innerContentDiv">
                    <h3>School</h3>
                    <button class="sorting">Sorted by due date</button>
                    <ul class="taskList">
                        <li class="taskItem">
                            <div class="taskCheckbox">
                                <input type="checkbox" id="task1">
                            </div>
                            <div class="taskContent">
                                <label for="task1">
                                    <span class="taskTitle">Design 4 - Tweede zit</span>
                                    <span class="taskDate">Sun, 25 Aug</span>
                                </label>
                            </div>
                        </li>
                        <li class="taskItem">
                            <div class="taskCheckbox">
                                <input type="checkbox" id="task2">
                            </div>
                            <div class="taskContent">
                                <label for="task2">
                                    <span class="taskTitle">Development 4 - Tweede zit</span>
                                    <span class="taskDate">Thu, 22 Aug</span>
                                </label>
                            </div>
                        </li>
                        <li class="taskItem">
                            <div class="taskCheckbox">
                                <input type="checkbox" id="task3">
                            </div>
                            <div class="taskContent">
                                <label for="task3">
                                    <span class="taskTitle">Lab 2 - Tweede zit</span>
                                    <span class="taskDate">Mon, 19 Aug</span>
                                </label>
                            </div>
                        </li>
                        <li class="taskItem">
                            <div class="taskCheckbox">
                                <input type="checkbox" id="task4">
                            </div>
                            <div class="taskContent">
                                <label for="task4">
                                    <span class="taskTitle">Design 2 - Tweede zit</span>
                                    <span class="taskDate">Mon, 19 Aug</span>
                                </label>
                            </div>
                        </li>
                        <li class="taskItem">
                            <div class="taskCheckbox">
                                <input type="checkbox" id="task5">
                            </div>
                            <div class="taskContent">
                                <label for="task5">
                                    <span class="taskTitle">Communicatie 3 - Voorbereiding</span>
                                    <span class="taskDate">Fri, 16 Aug</span>
                                </label>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="rightNavDiv">
                <span class="closeBtn" onclick="closeRightNav()">x</span>
            </div>
        </div>
    </div>
</body>
</html>
