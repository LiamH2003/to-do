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
    <link rel="stylesheet" href="../styles/homestyle12.css">
    <link rel="icon" href="../images/to-do_icon.png" type="image/icon type">
    <script type="text/javascript" src="../script/homescript.js"></script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            let currentListId = null; // Variable to keep track of the current list ID
            let currentTaskId = null; // Variable to keep track of the current task ID
            const taskContainer = document.querySelector('.taskContainer');
            const rightNavDiv = document.querySelector('.rightNavDiv');
            const taskTitleElement = document.getElementById('taskTitle');
            const taskDeadlineElement = document.getElementById('taskDeadline');
            const taskDescriptionElement = document.getElementById('taskDescription');

            function toggleRightNav() {
                rightNavDiv.classList.toggle('active');
            }

            document.addEventListener('click', function(event) {
                const isClickInsideRightNav = rightNavDiv.contains(event.target);
                const isClickInsideTaskContainer = document.querySelector('.taskContainer') && document.querySelector('.taskContainer').contains(event.target);
                const isClickInsideLeftNav = document.querySelector('.leftNavDiv').contains(event.target);

                if (!isClickInsideRightNav && !isClickInsideTaskContainer && !isClickInsideLeftNav) {
                    rightNavDiv.classList.remove('active');
                }
            });

            function fetchTaskDetails(taskId) {
                console.log("Fetching task details for ID:", taskId);
                fetch(`../functions/fetch_task_details.php?task_id=${taskId}`)
                    .then(response => response.json())
                    .then(task => {
                        console.log('Task data:', task);  // Log the task data
                        if (task && !task.error) {
                            // Update the DOM elements with task details
                            document.getElementById('taskTitle').textContent = task.title || 'No Title';
                            document.getElementById('taskDeadline').value = task.deadline || '';
                            document.getElementById('taskDescription').value = task.description || '';
                            currentTaskId = taskId;
                            toggleRightNav();
                        } else {
                            console.error('Task not found or error:', task.error);
                        }
                    })
                    .catch(error => console.error('Error fetching task details:', error));
            }



            function updateTaskDescription(newDescription) {
                fetch('../functions/update_task_description.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ task_id: currentTaskId, description: newDescription })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Task description updated successfully.');
                    } else {
                        console.error('Error updating task description:', data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
            }

            taskDescriptionElement.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    updateTaskDescription(this.value.trim());
                }
            });

            taskDeadlineElement.addEventListener('blur', function() {
                const newDeadline = this.value.trim();
                if (newDeadline) {
                    fetch('../functions/update_task_deadline.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ task_id: currentTaskId, deadline: newDeadline })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Task deadline updated successfully.');
                        } else {
                            console.error('Error updating task deadline:', data.error);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });

            // JavaScript Function to Set Up Click Listener
            function setupTaskItemClickListener(taskItem) {
                console.log("Setting up click listener");
                console.log('Task item data-task-id:', taskItem.dataset.taskId); // Debugging line

                taskItem.addEventListener('click', function(event) {
                    // Check if the click is not on the checkbox
                    if (event.target.tagName.toLowerCase() !== 'input') {
                        const taskId = taskItem.dataset.taskId;
                        console.log('Task ID from dataset:', taskId); // Debugging line
                        if (taskId) {
                            fetchTaskDetails(taskId);
                        } else {
                            console.error('Task ID is missing.');
                        }
                    }
                });
            }




            

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

        function fetchTasks(listId, sortOption = 'deadline-ascending') {
            rightNavDiv.classList.remove('active'); // Make sure the rightNavDiv is not active
            fetch(`../functions/fetch_tasks.php?list_id=${listId}&sort=${sortOption}`)
                .then(response => response.json())
                .then(tasks => {
                    const taskList = document.querySelector('.taskList');
                    taskList.innerHTML = ''; // Clear the task list

                    // Sort tasks based on the sort option
                    tasks.sort((a, b) => {
                        const deadlineA = a.deadline ? new Date(a.deadline) : null;
                        const deadlineB = b.deadline ? new Date(b.deadline) : null;

                        switch (sortOption) {
                            case 'title-ascending':
                                return a.title.localeCompare(b.title);
                            case 'title-descending':
                                return b.title.localeCompare(a.title);
                            case 'deadline-ascending':
                                if (!deadlineA && deadlineB) return -1;
                                if (deadlineA && !deadlineB) return 1;
                                if (!deadlineA && !deadlineB) return 0;
                                return deadlineA - deadlineB;
                            case 'deadline-descending':
                                if (!deadlineA && deadlineB) return 1;
                                if (deadlineA && !deadlineB) return -1;
                                if (!deadlineA && !deadlineB) return 0;
                                return deadlineB - deadlineA;
                            default:
                                return 0;
                        }
                    });

                    tasks.forEach(task => {
                        const taskItem = document.createElement('li');
                        taskItem.className = 'taskItem';
                        taskItem.dataset.taskId = task.id; // Set the data-task-id attribute

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

                        // Set up click listener for taskItem
                        setupTaskItemClickListener(taskItem);

                        // Prevent clicks on taskItem from toggling the checkbox
                        taskItem.addEventListener('click', function(event) {
                            if (event.target.tagName.toLowerCase() !== 'input') {
                                event.preventDefault(); // Prevent default behavior if not clicking checkbox
                            }
                        });
                    });

                })
                .catch(error => console.error('Error fetching tasks:', error));
        }


        // Add event listener to the sorting select element
        document.getElementById('sortingOptions').addEventListener('change', function() {
            if (currentListId) {
                fetchTasks(currentListId, this.value);
            }
        });




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
                    alert(data.error); // Display error message
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
                        
                            <select class="sorting" id="sortingOptions">
                                <option value="title-ascending">Title (A-Z)</option>
                                <option value="title-descending">Title (Z-A)</option>
                                <option value="deadline-ascending">Deadline (Earliest First)</option>
                                <option value="deadline-descending">Deadline (Latest First)</option>
                            </select>

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
                <div class="taskDetails">
                    <!-- Task Title Section -->
                    <div class="taskTitleSection">
                        <h2 id="taskTitle">Task Title</h2>
                    </div>

                    <!-- Task Deadline Section -->
                    <div class="taskDeadlineSection">
                        <label for="taskDeadline">Deadline:</label>
                        <input type="date" id="taskDeadline" value="">
                    </div>

                    <!-- Task Description Section -->
                    <div class="taskDescriptionSection">
                        <label for="taskDescription">Description:</label>
                        <textarea id="taskDescription" placeholder="Enter task description..."></textarea>
                    </div>
                </div>
            </div>


        </div>
    </div>
</body>
</html>
