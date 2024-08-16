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
    <link rel="stylesheet" href="../styles/homestyle11.css">
    <link rel="icon" href="../images/to-do_icon.png" type="image/icon type">
    <script type="text/javascript" src="../script/homescript.js"></script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            let currentListId = null; // Variable to keep track of the current list ID
            const taskContainer = document.querySelector('.taskContainer'); // Get the task container div

            // Fetch and display lists on page load
            fetch('../functions/fetch_lists.php')
                .then(response => response.json())
                .then(lists => {
                    const navCategories = document.querySelector('.navCategories');
                    navCategories.innerHTML = ''; // Clear existing items

                    lists.forEach(list => {
                        const listItem = document.createElement('li');
                        listItem.className = 'navItem';
                        listItem.dataset.category = list.id;

                        listItem.innerHTML = 
                            `<span class="listTitle">${list.title}</span>
                            <img src="../images/thrash.png" class="deleteIcon" alt="Delete List" title="Delete List">`;

                        navCategories.appendChild(listItem);

                        // Add click event listener to each list item to fetch tasks
                        listItem.querySelector('.listTitle').addEventListener('click', function() {
                            currentListId = list.id; // Update currentListId
                            taskContainer.style.display = 'block'; // Show the task container
                            fetchTasks(currentListId);
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
            const newListInput = document.createElement('input');
            newListInput.className = 'newListInput';
            newListInput.placeholder = 'Enter list name';
            newListText.style.display = 'none'; // Hide the text
            newListButton.appendChild(newListInput);
            newListInput.focus();

            function handleNewListInput(event) {
                if (event.key === 'Enter') {
                    const listName = newListInput.value.trim();
                    if (listName) {
                        createNewList(listName);
                        cleanupNewListInput();
                    }
                }
            }

            function cleanupNewListInput() {
                if (newListInput.parentNode) {
                    newListInput.remove(); // Remove the input field
                }
                newListText.style.display = 'block'; // Show the original text
            }

            newListInput.addEventListener('keypress', handleNewListInput);
            newListInput.addEventListener('blur', cleanupNewListInput);
        });

        // Handle Add Task functionality
        const addTaskButton = document.querySelector('.addTask');
        const addTaskText = addTaskButton.querySelector('.text');

        addTaskButton.addEventListener('click', function() {
            const addTaskInput = document.createElement('input');
            addTaskInput.className = 'addTaskInput';
            addTaskInput.placeholder = 'Enter task name';
            addTaskText.style.display = 'none'; // Hide the text
            addTaskButton.appendChild(addTaskInput);
            addTaskInput.focus();

            function handleAddTaskInput(event) {
                if (event.key === 'Enter') {
                    const taskName = addTaskInput.value.trim();
                    if (taskName && currentListId) {
                        createNewTask(taskName, currentListId);
                        cleanupAddTaskInput();
                    }
                }
            }

            function cleanupAddTaskInput() {
                if (addTaskInput && addTaskInput.parentNode) {
                    addTaskInput.remove(); // Remove the input field
                }
                addTaskText.style.display = 'block'; // Show the original text
            }

            addTaskInput.addEventListener('keypress', handleAddTaskInput);
            addTaskInput.addEventListener('blur', cleanupAddTaskInput);
        });

        function fetchTasks(listId) {
            fetch(`../functions/fetch_tasks.php?list_id=${listId}`)
                .then(response => response.json())
                .then(tasks => {
                    const taskList = document.querySelector('.taskList');
                    taskList.innerHTML = ''; // Clear the task list

                    // Sort tasks by deadline (earliest first)
                    tasks.sort((a, b) => {
                        const deadlineA = a.deadline ? new Date(a.deadline) : null;
                        const deadlineB = b.deadline ? new Date(b.deadline) : null;

                        if (!deadlineA && deadlineB) return 1;  // If task A has no deadline, it comes after task B
                        if (deadlineA && !deadlineB) return -1; // If task B has no deadline, it comes after task A
                        if (!deadlineA && !deadlineB) return 0; // If both tasks have no deadline, keep their order

                        return deadlineA - deadlineB; // Sort by deadline
                    });

                    tasks.forEach(task => {
                        const taskItem = document.createElement('li');
                        taskItem.className = 'taskItem';

                        const deadline = task.deadline ? new Date(task.deadline) : null;
                        const currentDateTime = new Date();
                        let deadlineDisplay = '';

                        if (deadline) {
                            deadlineDisplay = deadline.toLocaleDateString('en-US', {
                                weekday: 'short',
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        }

                        // Apply red text color if the deadline has passed
                        const deadlineClass = deadline && deadline < currentDateTime ? 'deadline-passed' : '';

                        taskItem.innerHTML = `
                            <div class="taskCheckbox">
                                <input type="checkbox" id="task${task.id}" ${task.status === 'Completed' ? 'checked' : ''}>
                            </div>
                            <div class="taskContent">
                                <label for="task${task.id}">
                                    <span class="taskTitle">${task.title}</span>
                                    <span class="taskDate ${deadlineClass}">${deadlineDisplay}</span>
                                </label>
                            </div>
                        `;

                        taskList.appendChild(taskItem);

                        // Add event listener to the checkbox
                        taskItem.querySelector('input[type="checkbox"]').addEventListener('change', function() {
                            if (this.checked) {
                                deleteTask(task.id);
                            }
                        });
                    });
                })
                .catch(error => console.error('Error fetching tasks:', error));
        }



        function createNewList(listName) {
            fetch('../functions/create_list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ title: listName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const navCategories = document.querySelector('.navCategories');
                    const listItem = document.createElement('li');
                    listItem.className = 'navItem';
                    listItem.dataset.category = data.list_id; // New list ID from the response
                    listItem.innerHTML = 
                        `<span class="listTitle">${listName}</span>
                        <img src="../images/thrash.png" class="deleteIcon" alt="Delete List" title="Delete List">`;
                    navCategories.appendChild(listItem);

                    listItem.querySelector('.listTitle').addEventListener('click', function() {
                        currentListId = data.list_id; // Update currentListId
                        fetchTasks(currentListId);
                    });

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

        function createNewTask(taskName, listId) {
            fetch('../functions/create_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ title: taskName, list_id: listId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetchTasks(listId); // Refresh task list
                } else {
                    console.error('Error creating task:', data.error);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function deleteTask(taskId) {
            fetch(`../functions/delete_task.php?task_id=${taskId}`, {
                method: 'GET',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove task from the UI
                    const taskItem = document.querySelector(`.taskItem input[type="checkbox"][id="task${taskId}"]`).closest('.taskItem');
                    if (taskItem) {
                        taskItem.remove();
                    }
                } else {
                    console.error('Error deleting task:', data.error);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function confirmDeleteList(listId, listName) {
            const confirmation = confirm(`Are you sure you want to delete the list "${listName}"?`);
            if (confirmation) {
                deleteList(listId);
            }
        }

        function deleteList(listId) {
            fetch(`../functions/delete_list.php?list_id=${listId}`, {
                method: 'GET',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const listItem = document.querySelector(`.navItem[data-category="${listId}"]`);
                    if (listItem) {
                        listItem.remove();
                    }
                } else {
                    console.error('Error deleting list:', data.error);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });

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
                    <!-- This is the new wrapper div -->
                    <div class="taskContainer" style="display: none;">
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
            </div>

            <div class="rightNavDiv">
                <span class="closeBtn" onclick="closeRightNav()">x</span>
            </div>
        </div>
    </div>
</body>
</html>
