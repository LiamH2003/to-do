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
$user_id = isset($_SESSION["id"]) ? intval($_SESSION["id"]) : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/homestyle15.css">
    <link rel="icon" href="../images/to-do_icon.png" type="image/icon type">
    <script type="text/javascript" src="../script/homescript.js"></script>
    <script type="text/javascript">
        var userId = <?php echo json_encode($user_id); ?>;
        document.addEventListener('DOMContentLoaded', function() {
            let currentListId = null; // Variable to keep track of the current list ID
            let currentTaskId = null; // Variable to keep track of the current task ID
            const taskContainer = document.querySelector('.taskContainer');
            const rightNavDiv = document.querySelector('.rightNavDiv');
            const taskTitleElement = document.getElementById('taskTitle');
            const taskDeadlineElement = document.getElementById('taskDeadline');
            const taskDescriptionElement = document.getElementById('taskDescription');

            // Function to update the task status
            // Update the existing updateTaskStatus function to include console log
            function updateTaskStatus(isDone) {
                console.log("Updating task status. Task ID:", currentTaskId, "Is Done:", isDone);
                fetch('../functions/update_task_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ task_id: currentTaskId, status: isDone ? 'done' : 'todo' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Task status updated successfully to:', data.new_status);
                    } else {
                        console.error('Error updating task status:', data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
            }


           // Set the checkbox state based on the task status
            function setTaskStatus(checked) {
                const statusCheckbox = document.querySelector(`input[id^="task${currentTaskId}"]`);
                if (statusCheckbox) {
                    statusCheckbox.checked = checked;
                    statusCheckbox.addEventListener('change', function() {
                        updateTaskStatus(this.checked);
                    });
                } else {
                    console.error('Checkbox not found for task ID:', currentTaskId);
                }
            }

            document.getElementById('addCommentButton').addEventListener('click', function() {
                const commentTextarea = document.getElementById('newComment');
                const comment = commentTextarea.value.trim();
                
                if (comment) {
                    fetch('../functions/add_comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'task_id': currentTaskId,
                            'user_id': userId, // Replace with the actual user ID (securely managed)
                            'comment': comment
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            // Add new comment to the list
                            const taskCommentsList = document.getElementById('taskCommentsList');
                            const newCommentItem = document.createElement('li');
                            newCommentItem.textContent = `You: ${comment} (posted just now)`;
                            taskCommentsList.appendChild(newCommentItem);

                            // Clear the textarea
                            commentTextarea.value = '';
                        } else {
                            console.error('Failed to add comment:', result.error);
                        }
                    })
                    .catch(error => console.error('Error adding comment:', error));
                } else {
                    console.error('Comment cannot be empty');
                }
            });


            function deleteTaskFile(fileId) {
                console.log("Deleting file with ID:", fileId);
                fetch('../functions/delete_task_file.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded' // Ensure the content type is correct for form data
                    },
                    body: new URLSearchParams({ 'file_id': fileId })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        console.log('File deleted successfully');
                        // Remove the file item from the DOM
                        document.querySelector(`button[data-file-id="${fileId}"]`).closest('li').remove();
                    } else {
                        console.error('Failed to delete file:', result.error);
                    }
                })
                .catch(error => console.error('Error deleting file:', error));
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



            document.getElementById('taskFilesList').addEventListener('click', function(event) {
                if (event.target.tagName === 'BUTTON') {
                    const fileId = event.target.dataset.fileId;
                    console.log('Button clicked, fileId:', fileId); // Log the fileId for debugging
                    if (fileId) {
                        deleteTaskFile(fileId);
                    } else {
                        console.error('File ID is missing.');
                    }
                }
            });






            document.getElementById('uploadFileButton').addEventListener('click', function() {
                const fileInput = document.getElementById('fileUpload');
                const file = fileInput.files[0];
                if (file) {
                    const formData = new FormData();
                    formData.append('task_id', currentTaskId);
                    formData.append('file', file);

                    fetch('../functions/upload_task_file.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('File uploaded successfully');
                            fetchTaskDetails(currentTaskId); // Refresh task details
                        } else {
                            console.error('Error uploading file:', data.error);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });


            // Update the fetchTaskDetails function to include setting the task status
            // Add this within the fetchTaskDetails function
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

                            // Initialize checkbox state
                            const statusCheckbox = document.querySelector('.rightNavDiv input[type="checkbox"]');
                            if (statusCheckbox) {
                                statusCheckbox.checked = task.status === 'done';  // 'done' means checkbox is checked
                                statusCheckbox.addEventListener('change', function() {
                                    updateTaskStatus(this.checked);
                                });
                            }

                            // Display task files
                            const taskFilesList = document.getElementById('taskFilesList');
                            taskFilesList.innerHTML = ''; // Clear existing files

                            const files = task.files || [];
                            files.forEach(file => {
                                const listItem = document.createElement('li');
                                listItem.classList.add("deleteFileItem");
                                listItem.innerHTML = `
                                    <a href="${file.filepath}" target="_blank">${file.filename}</a>
                                    <button data-file-id="${file.id}">Delete</button>
                                `;
                                taskFilesList.appendChild(listItem);
                            });

                            // Display existing comments
                            const taskCommentsList = document.getElementById('taskCommentsList');
                            taskCommentsList.innerHTML = ''; // Clear existing comments

                            const comments = task.comments || [];
                            comments.forEach(comment => {
                                const commentItem = document.createElement('li');
                                commentItem.textContent = `${comment.username}: ${comment.comment} (posted on ${comment.created_at})`;
                                taskCommentsList.appendChild(commentItem);
                            });

                            // Show file upload and upload button
                            document.getElementById('fileUpload').style.display = 'block';
                            document.getElementById('uploadFileButton').style.display = 'block';

                        } else {
                            console.error('Task not found or error:', task.error);
                        }
                    })
                    .catch(error => console.error('Error fetching task details:', error));
            }









            // Function to fetch and display comments for the selected task
            function fetchComments(taskId) {
                fetch(`../functions/fetch_comments.php?task_id=${taskId}`)
                    .then(response => response.json())
                    .then(comments => {
                        const commentsList = document.getElementById('taskCommentsList');
                        commentsList.innerHTML = ''; // Clear existing comments

                        comments.forEach(comment => {
                            const commentItem = document.createElement('li');
                            commentItem.textContent = comment.comment; // Display comment text
                            commentsList.appendChild(commentItem);
                        });
                    })
                    .catch(error => console.error('Error fetching comments:', error));
            }


            taskDeadlineElement.addEventListener('change', function() {
                const newDeadline = this.value.trim();
                if (newDeadline && currentTaskId) {
                    updateTaskDeadline(newDeadline);
                }
            });

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
                        // Update task description in the contentDiv as well
                        const taskDescriptionInContentDiv = document.querySelector(`.taskItem[data-task-id="${currentTaskId}"] .taskDescription`);
                        if (taskDescriptionInContentDiv) {
                            taskDescriptionInContentDiv.textContent = newDescription || 'No Description';
                        }
                    } else {
                        console.error('Error updating task description:', data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            taskDescriptionElement.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Prevent the default ENTER key behavior (e.g., adding a newline)
                    const newDescription = taskDescriptionElement.value.trim();
                    if (newDescription) {
                        updateTaskDescription(newDescription);
                    }
                }
            });

            function updateTaskDeadline(newDeadline) {
                console.log('Updating task deadline. Task ID:', currentTaskId, 'New Deadline:', newDeadline); // Log task ID and new deadline

                fetch('../functions/update_task_deadline.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ task_id: currentTaskId, deadline: newDeadline })
                })
                .then(response => {
                    console.log('Response received:', response); // Log the raw response
                    return response.text(); // Read the response as text
                })
                .then(text => {
                    console.log('Response text:', text); // Log the raw response text

                    try {
                        const data = JSON.parse(text); // Attempt to parse the response as JSON
                        console.log('Response data:', data); // Log the parsed response data

                        if (data.success) {
                            console.log('Task deadline updated successfully.');
                            // Update task deadline in the task list as well
                            const taskDateElement = document.querySelector(`.taskItem[data-task-id="${currentTaskId}"] .taskDate`);
                            if (taskDateElement) {
                                const deadline = new Date(newDeadline);
                                taskDateElement.textContent = deadline.toLocaleDateString('en-US', {
                                    weekday: 'short',
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                });
                            }
                        } else {
                            console.error('Error updating task deadline:', data.error);
                        }
                    } catch (error) {
                        console.error('Failed to parse JSON:', error); // Handle JSON parsing errors
                    }
                })
                .catch(error => console.error('Error:', error));
            }



            function toggleRightNav() {
                rightNavDiv.classList.toggle('active');
                document.querySelector("#fileUpload").classList.toggle('active');
            }

            

            document.addEventListener('click', function(event) {
                const isClickInsideRightNav = rightNavDiv.contains(event.target);
                const isClickInsideTaskContainer = document.querySelector('.taskContainer') && document.querySelector('.taskContainer').contains(event.target);
                const isClickInsideLeftNav = document.querySelector('.leftNavDiv').contains(event.target);

                if (!isClickInsideRightNav && !isClickInsideTaskContainer && !isClickInsideLeftNav) {
                    rightNavDiv.classList.remove('active');
                }
            });


            function updateTaskTitle(newTitle) {
                fetch('../functions/update_task_title.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ task_id: currentTaskId, title: newTitle })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Task title updated successfully.');
                        // Update task title in the contentDiv as well
                        const taskTitleInContentDiv = document.querySelector(`.taskItem[data-task-id="${currentTaskId}"] .taskTitle`);
                        if (taskTitleInContentDiv) {
                            taskTitleInContentDiv.textContent = newTitle;
                        }
                    } else {
                        console.error('Error updating task title:', data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
            }

            taskTitleElement.addEventListener('click', function() {
                const currentTitle = taskTitleElement.textContent;
                const inputField = document.createElement('input');
                inputField.type = 'text';
                inputField.value = currentTitle;
                inputField.className = 'editTitleInput';

                taskTitleElement.textContent = '';
                taskTitleElement.appendChild(inputField);
                inputField.focus();

                inputField.addEventListener('blur', function() {
                    const newTitle = inputField.value.trim();
                    if (newTitle !== currentTitle) {
                        updateTaskTitle(newTitle);
                    }
                    taskTitleElement.textContent = newTitle || currentTitle;
                });

                inputField.addEventListener('keypress', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        const newTitle = inputField.value.trim();
                        if (newTitle !== currentTitle) {
                            updateTaskTitle(newTitle);
                        }
                        taskTitleElement.textContent = newTitle || currentTitle;
                    }
                });
            });


            taskDescriptionElement.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Prevent the default Enter key behavior
                    const newDescription = this.value.trim();
                    if (newDescription) {
                        updateTaskDescription(newDescription);
                    }
                    this.blur(); // Remove focus from the textarea
                }
            });

            taskDeadlineElement.addEventListener('blur', function() {
                console.log("Made it here");
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

            function setupRightNavCheckboxListener() {
                const statusCheckbox = document.getElementById('taskStatus');
                if (statusCheckbox) {
                    statusCheckbox.addEventListener('change', function() {
                        updateTaskStatus(this.checked);
                    });
                } else {
                    console.error('Checkbox not found in rightNavDiv');
                }
            }

            // JavaScript Function to Set Up Click Listener
            function setupTaskItemClickListener(taskItem) {
                taskItem.addEventListener('click', function(event) {
                    if (event.target.tagName.toLowerCase() !== 'input') {
                        const taskId = taskItem.dataset.taskId;
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
                    // Check if the element is still in the DOM before removing it
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

                        // Add event listener to the checkbox only
                        const checkbox = taskItem.querySelector('input[type="checkbox"]');
                        checkbox.addEventListener('change', function() {
                            if (this.checked) {
                                deleteTask(task.id);
                            }
                        });

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

                    // Set the currentListId to the new list
                    currentListId = data.list_id;

                    // Fetch tasks for the new list
                    fetchTasks(currentListId);

                    // Ensure task container is visible
                    taskContainer.style.display = 'block';

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

                    <div class="taskStatusSection">
                        <label for="taskStatus">Status:</label>
                        <input type="checkbox" id="taskStatus">
                    </div>

                    <div class="taskFilesSection">
                        <h3>Attached Files</h3>
                        <ul id="taskFilesList">
                            <!-- Files will be populated here dynamically -->
                        </ul>
                        <div class="taskFileLowerDiv">
                            <input type="file" id="fileUpload" />
                            <button id="uploadFileButton">Upload File</button>
                        </div>
                    </div>

                    <div class="taskCommentsSection">
                        <h3>Comments</h3>
                        <ul id="taskCommentsList">
                            <!-- Comments will be populated here dynamically -->
                        </ul>
                        <textarea id="newComment" placeholder="Add a comment..."></textarea>
                        <button id="addCommentButton">Add Comment</button>
                    </div>
                
            </div>


        </div>
    </div>
</body>
</html>
