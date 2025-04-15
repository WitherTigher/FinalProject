<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Project Proposal – CPSC 3750</title>
    <style>
/* Global Styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    color: #333;
}

/* Header Styling */
h1 {
    background-color: #14495d;
    color: #ffffff;
    padding: 20px;
    text-align: center;
    margin: 0;
}

/* Container Styling */
    #datePicker, #myCal {
    margin: 20px auto;
    max-width: 600px;
}

/* Table Styling */
table {
    border: 1px solid black;
    border-collapse: collapse;
    margin-top: 1rem;
    width: 100%;
    background-color: #ffffff;
}

th {
    border: 1px solid black;
    padding: 6px;
    font-weight: bold;
    background: #ccc;
}

td {
    border: 1px solid black;
    padding: 6px;
    vertical-align: top;
    width: 100px;
}
    </style>
</head>
<body>

    <h1>Team Project Proposal</h1>
    <h2>CPSC 3750 – Web App Development</h2>
    <div>
      <a href="about.html" title="About the team">About the Team</a>
      <a href="proposal.php" title="Proposal for the final">Proposal for the final</a>
      <a href="showcalendar_withevent.php" title="Calendar">Calendar</a>
    </div>
    <div class="section">
        <h3>Team Members</h3>
        <ul>
            <li>Connor Cromer</li>
            <li>Jason Lin</li>
            <li>Jeffrey Moon</li>
        </ul>
    </div>

    <div class="section">
        <h3>Chosen Base App</h3>
        <p>We selected the <strong>Calander
        </strong> from the textbook. It includes features such as 
        1:Select date by year, month, day.
        2:Dynamically add new events with description and start time.
        3.Pulls up a summery of all events on the chosen day.</p>
    </div>

    <div class="section">
        <h3>Project Overview</h3>
        <p>Our application will allow users to 
        Our application allows users to track, add, and modify their events, as well as add durations, 
        set reminders, and categorize their events with tags and color codes. It also supports recurring 
        events with daily, weekly, monthly, or custom intervals, provides reminders and notifications via 
        push, email, or SMS, integrates a to-do list for attaching tasks to 
        specific events, and offers custom themes including dark mode for a personalized user experience.</p>
    </div>

    <div class="section">
        <h3>Proposed Enhancements</h3>
        <p>In addition to implementing the base app, we plan to make the following improvements and changes:</p>
        <ul>
            <li><strong>HTML:</strong> 
            [e.g., Added new forms/pages, improved layout/structure, included semantic HTML]
            1. Add pages for the login
            2. Make sure all events are in a list format
            </li>
            <li><strong>CSS:</strong> 
            [e.g., Responsive design using Flexbox or Grid, dark mode toggle, custom styling]
            1. Dark Mode toggle 
            2. Responsive design
            </li>
            <li><strong>JavaScript:</strong> 
            [e.g., Form validation, AJAX, interactive components like sliders, modals, etc.]
            1.Add deleting, adding, and editing without page reloads
            2.Use AJAX to communicate with the backend
            3.Implement animations
            </li>
            <li><strong>PHP:</strong> 
            [e.g., New logic for handling users/posts/comments, added admin dashboard]
            1.Backend logic for database
            2.CRUD operations
            3.Authentication handling
            </li>
            <li><strong>Database:</strong> 
            [e.g., Added tables, extra fields, user roles, search functionality]
            1.Store all users, events, tasks, settings
            2.Use MySQL for querying information
            </li>
            <li><strong>Authentication:</strong> 
            Login/Logout with password hashing and optional user roles (e.g., user/admin)
            1.Add login with username and login
            2. Add logout functionality 
            3.Add a forgot my password option for authentication
        </li>
        </ul>
    </div>

    <div class="section">
        <h3>Division of Labor</h3>
        <ul>
            <li><strong>Connor Cromer:</strong> authentication, Scheduling, Implementing features</li>
            <li><strong>Jason Lin:</strong> Theme toggle, Database/query, Javascript Animations, Css, Implementing features</li>
            <li><strong>Jeffrey Moon:</strong> Html Page Formating, DOM Tree Managment, Implementing features. </li>
        </ul>
    </div>

    <div class="section">
        <h3>Timeline</h3>
        <ul>
            <li>Week 1 (due April 08 2PM): Base app implemented, proposal presented</li>
            <li>Week 3 (due April 22 2PM): Complete enhancements, demo to instructor</li>
            <li>Week 4 (due April 27 EOD): Polish UI and prepare for class demo</li>
            <li>Week 5 (due Final Exam): Give a polished demo of your team's app</li>
        </ul>
    </div>

    <div class="section">
        <h3>Questions or Risks</h3>
        <p>Is it Ok if we add more features later to increase our grade?</p>
        <p>Is there anything we could add for extra credit?</p>
    </div>

</body>
</html>