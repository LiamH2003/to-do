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
    <script type="text/javascript">
        // Fetch and display lists on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetch('../functions/fetch_lists.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(lists => {
                    const navCategories = document.querySelector('.navCategories');
                    navCategories.innerHTML = ''; // Clear existing items

                    lists.forEach(list => {
                        const listItem = document.createElement('li');
                        listItem.className = 'navItem';
                        listItem.dataset.category = list.id;
                        listItem.textContent = list.title;
                        navCategories.appendChild(listItem);

                        // Add click event listener to each list item
                        listItem.addEventListener('click', function() {
                            fetchTasks(list.id);
                        });
                    });
                })
                .catch(error => console.error('Error fetching lists:', error));
        });

        // Function to fetch and display tasks for a specific list
        function fetchTasks(listId) {
            fetch(`../functions/fetch_tasks.php?list_id=${listId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(tasks => {
                    const taskList = document.querySelector('.taskList');
                    taskList.innerHTML = ''; // Clear existing tasks

                    tasks.forEach(task => {
                        const taskItem = document.createElement('li');
                        taskItem.className = 'taskItem';

                        taskItem.innerHTML = `
                            <div class="taskCheckbox">
                                <input type="checkbox" id="task${task.id}" ${task.status === 'Completed' ? 'checked' : ''}>
                            </div>
                            <div class="taskContent">
                                <label for="task${task.id}">
                                    <span class="taskTitle">${task.title}</span>
                                    <span class="taskDate">${new Date(task.deadline).toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })}</span>
                                </label>
                            </div>
                        `;
                        taskList.appendChild(taskItem);
                    });
                })
                .catch(error => console.error('Error fetching tasks:', error));
        }
    </script>
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
