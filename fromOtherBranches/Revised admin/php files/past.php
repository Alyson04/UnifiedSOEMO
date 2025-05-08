<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Unified SOEMO Dashboard</title>
  <link rel="stylesheet" href="dashboard.css" />
  <link rel="stylesheet" href="past.css">
</head>
<body>

<div class="container">

 <!-- Sidebar -->
<aside class="sidebar">
  <div class="logo-container">
    <img src="logo.png" alt="Logo" class="logo"/>
  </div>
  <nav class="nav-menu">
    <a href="dashboard.html" class="nav-item active">
      <img src="dashboard-icon.png" alt="Dashboard Icon" />
      Dashboard
    </a>
    <a href="manage_user.html" class="nav-item">
      <img src="user-icon.png" alt="Users Icon" />
      Manage Users
    </a>
    <a href="manage_org.html" class="nav-item">
      <img src="org-icon.png" alt="Organizations Icon" />
      Organizations
    </a>
    <a href="manage_events.html" class="nav-item">
      <img src="event-icon.png" alt="Events Icon" />
      Events
    </a>
    <a href="settings.html" class="nav-item">
      <img src="settings-icon.png" alt="Settings Icon" />
      Settings
    </a>
  </nav>
</aside>

  <!-- Main Panel -->
<main class="main-content">
  <header class="top-bar">
    <section class="dashboard-header">
      <h1>UNIFIED SOEMO</h1>
      <p>DISCOVER, JOIN, ENGAGE</p>
    </section>  
    <div class="top-right">
      <img src="bell.png" alt="Notifications" class="bell"/>
      <div class="profile">
        <img src="profile.png" alt="Admin" />
        <div class="profile-info">
          <strong>Moni Roy</strong>
          <span>Admin</span>
        </div>
      </div>
    </div>
  </header>
 
  
  
    <!-- Stats -->
    <section class="stats">
        <a href="dashboard.html" class="card stat-card">
          <h2>1,000</h2><p>Total Users</p>
        </a>
        <a href="Active_org.html" class="card stat-card">
          <h2>88</h2><p>Active Organizations</p>
        </a>
        <a href="upcoming.html" class="card stat-card">
          <h2>6</h2><p>Upcoming Events</p>
        </a>
        <a href="past.html" class="card stat-card active">
          <h2>8</h2><p>Past Events</p>
        </a>
      </section>     

   
<section class="events-upcoming">
    <div class="list-events">
      <h3>Past Events</h3>
      <div>June 9</div>
      <div>June 16</div>
      <div>June 23</div>
      <div>June 30</div>
      <div>July 10</div>
      <div>July 14</div>
    </div>
  
    <div class="list-events">
        <h3>Events Name</h3>
        <div>Events Name</div>
        <div>Events Name</div>
        <div>Events Name</div>
        <div>Events Name</div>
        <div>Events Name</div>
        <div>Events Name</div>
    </div>
  </section>
      
  </main>

</div>
</body>
</html>
