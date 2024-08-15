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
    <link rel="stylesheet" href="../styles/homestyle10.css">
    <link rel="icon" href="../images/to-do_icon.png" type="image/icon type">
    <script type="text/javascript" src="../script/homescript.js"></script>
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch and display lists on page load
        fetch('../functions/fetch_lists.php')
            .then(response => {
                if (!response.ok) {
                    console.error('Failed to fetch lists. Status:', response.status);
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(lists => {
                console.log('Fetched lists:', lists);
                const navCategories = document.querySelector('.navCategories');
                navCategories.innerHTML = ''; // Clear existing items

                lists.forEach(list => {
                    const listItem = document.createElement('li');
                    listItem.className = 'navItem';
                    listItem.dataset.category = list.id;

                    listItem.innerHTML = `
                        <span class="listTitle">${list.title}</span>
                        <img src="../images/thrash.png" class="deleteIcon" alt="Delete List" title="Delete List">
                    `;
                    
                    navCategories.appendChild(listItem);

                    // Add click event listener to each list item to fetch tasks
                    listItem.querySelector('.listTitle').addEventListener('click', function() {
                        fetchTasks(list.id);
                    });

                    // Add click event listener to the delete icon
                    listItem.querySelector('.deleteIcon').addEventListener('click', function(event) {
                        event.stopPropagation(); // Prevent triggering the list item click event
                        confirmDeleteList(list.id, list.title);
                    });
                });
            })
            .catch(error => console.error('Error fetching lists:', error));

        // Handle New List creation
        const newListButton = document.querySelector('.newList');
        const newListText = newListButton.querySelector('.text');

        newListButton.addEventListener('click', function() {
            // Create an input field and replace the text
            const newListInput = document.createElement('input');
            newListInput.className = 'newListInput';
            newListInput.placeholder = 'Enter list name';
            newListInput.value = ''; // Clear any existing text
            newListText.style.display = 'none'; // Hide the text
            newListButton.appendChild(newListInput);
            newListInput.focus();

            newListInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    const listName = newListInput.value.trim();
                    if (listName) {
                        createNewList(listName);
                        newListInput.value = '';
                        newListInput.remove(); // Remove the input field
                        newListText.style.display = 'block'; // Show the original text
                    }
                }
            });

            // Hide the input and show the text if the user clicks away
            newListInput.addEventListener('blur', function() {
                newListInput.remove();
                newListText.style.display = 'block';
            });
        });
    });

    // Function to fetch and display tasks for a specific list
    function fetchTasks(listId) {
        console.log('Fetching tasks for list ID:', listId);
        fetch(`../functions/fetch_tasks.php?list_id=${listId}`)
            .then(response => {
                if (!response.ok) {
                    console.error('Failed to fetch tasks. Status:', response.status);
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(tasks => {
                console.log('Fetched tasks:', tasks);
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

    // Function to create a new list
    function createNewList(listName) {
        console.log('Creating new list with name:', listName);
        fetch('../functions/create_list.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ title: listName })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Create list response:', data);
            if (data.success) {
                // Add new list to the list of categories
                const navCategories = document.querySelector('.navCategories');
                const listItem = document.createElement('li');
                listItem.className = 'navItem';
                listItem.dataset.category = data.list_id; // New list ID from the response
                listItem.innerHTML = `
                    <span class="listTitle">${listName}</span>
                    <img src="../images/thrash.png" class="deleteIcon" alt="Delete List" title="Delete List">
                `;
                navCategories.appendChild(listItem);

                // Add click event listener to the new list item
                listItem.querySelector('.listTitle').addEventListener('click', function() {
                    fetchTasks(data.list_id);
                });

                // Add click event listener to the delete icon
                listItem.querySelector('.deleteIcon').addEventListener('click', function(event) {
                    event.stopPropagation();
                    confirmDeleteList(data.list_id, listName);
                });
            } else {
                console.error('Error creating list:', data.error);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Function to confirm and delete a list
    function confirmDeleteList(listId, listName) {
        console.log(`Confirming deletion for list ID: ${listId}, Name: ${listName}`);
        const confirmation = confirm(`Are you sure you want to delete the list "${listName}"?`);
        if (confirmation) {
            deleteList(listId);
        }
    }

    // Function to delete a list
    function deleteList(listId) {
        console.log('Deleting list with ID:', listId);
        fetch(`../functions/delete_list.php?list_id=${listId}`, {
            method: 'GET', // Use GET method for simplicity, or POST for more secure data transfer
        })
        .then(response => response.json())
        .then(data => {
            console.log('Delete list response:', data);
            if (data.success) {
                // Remove the deleted list from the UI
                const listItem = document.querySelector(`.navItem[data-category="${listId}"]`);
                if (listItem) {
                    listItem.remove();
                    console.log('List removed from UI');
                } else {
                    console.error('List item not found in UI');
                }
            } else {
                console.error('Error deleting list:', data.error);
            }
        })
        .catch(error => console.error('Error:', error));
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
                        <!-- List items will be dynamically populated here -->
                    </ul>
                </div>
                <div class="newList">
                    <img src="../images/plus.png" class="plusIcon" alt="Add New List">
                    <span class="text">New List</span>
                </div>
            </div>
            <div class="contentDiv">
                <div class="innerContentDiv">
                    <h3>Tasks</h3>
                    <button class="sorting">Sorted by due date</button>
                    <ul class="taskList">
                        <!-- Tasks will be populated here dynamically -->
                    </ul>
                    <div class="addTask">
                        <img src="../images/plus.png" class="plusIcon" alt="Add New Task">
                        <span class="text">Add a task</span>
                    </div>
                </div>
            </div>
            <div class="rightNavDiv">
                <span class="closeBtn" onclick="closeRightNav()">x</span>
            </div>
        </div>
    </div>
</body>
</html>
