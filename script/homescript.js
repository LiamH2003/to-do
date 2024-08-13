function openRightNav(taskTitle, taskDate) {
    // Add the 'active' class to show the rightNavDiv
    var rightNavDiv = document.querySelector('.rightNavDiv');
    rightNavDiv.classList.add('active');

    // You can also dynamically set content in the rightNavDiv if needed
    rightNavDiv.innerHTML = `
        <span class="closeBtn" onclick="closeRightNav()">x</span>
        <div class="rightNavContent">
            <h3>${taskTitle}</h3>
            <p>Due date: ${taskDate}</p>
            <!-- Add more content as needed -->
        </div>
    `;
}

function closeRightNav() {
    // Remove the 'active' class to hide the rightNavDiv
    var rightNavDiv = document.querySelector('.rightNavDiv');
    rightNavDiv.classList.remove('active');
}
