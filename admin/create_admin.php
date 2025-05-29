<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADD A RECORD</title>
    <link rel="stylesheet" href="../assets/stylesheets/create_admin.css">
</head>
<body>
    <div class="container">
            
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <!-- <li><a href="#" class="active">Create Admin Account</a></li> -->
                <li><a href="admin_dashboard.php" class="active">Manage Users</a></li>
                <li><a href="#">Organizations</a></li>
                <li><a href="#">Events</a></li>
                <li><a href="#">Settings</a></li>
                <li><a href="../functions/logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
      <div id="addRecordSection">
        <div class="card mb-4">
          <div class="card-header">
            <h3>Create an Admin</h3>
          </div>
          <div class="card-body">
            <form action="../api/create_admin.php" method="POST">
              <div class="form-group">
                <label for="fullName">Fullname:</label>
                <input type="text" id="fullName" name="fullName" required />
              </div>

              <div class="form-group">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" required />
              </div>

              <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required />
              </div>

              <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required />
              </div>

              <input type="hidden" name="role" value="admin" />
              <input type="hidden" name="is_approved" value="approved" />

              <button type="submit" class="btn btn-success">Add Record</button>
              <button type="button" onclick="history.back()" class="btn-cancel">Cancel</button>
            </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>