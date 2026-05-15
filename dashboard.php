<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$role = $_SESSION['role'];

// Fetch Tasks for this volunteer
$tasks_query = "SELECT * FROM tasks WHERE volunteer_id = '$user_id' ORDER BY due_date ASC";
$tasks_result = $conn->query($tasks_query);

// Fetch stats for this volunteer
$completed_tasks = $conn->query("SELECT COUNT(*) as total FROM tasks WHERE volunteer_id = '$user_id' AND status = 'Completed'")->fetch_assoc()['total'];
$pending_tasks = $conn->query("SELECT COUNT(*) as total FROM tasks WHERE volunteer_id = '$user_id' AND status = 'Pending'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Volunteer Dashboard | Disaster Relief Network</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-logo">
        <span>🛟</span> Relief Network
      </div>
      
      <nav class="nav-menu">
        <a href="#overview" class="nav-item active" onclick="showSection('overview', this)">
          <i class="fa-solid fa-house"></i> Overview
        </a>
        <a href="#tasks" class="nav-item" onclick="showSection('tasks', this)">
          <i class="fa-solid fa-list-check"></i> My Tasks
        </a>
        <a href="#supplies" class="nav-item" onclick="showSection('supplies', this)">
          <i class="fa-solid fa-box-open"></i> Record Supplies
        </a>
        <a href="#reports" class="nav-item" onclick="showSection('reports', this)">
          <i class="fa-solid fa-triangle-exclamation"></i> Field Issues
        </a>
        <a href="#chat" class="nav-item" onclick="showSection('chat', this)">
          <i class="fa-solid fa-message"></i> Messages
        </a>
        <a href="logout.php" class="nav-item" style="margin-top: 20px; border-top: 1px solid var(--line); padding-top: 20px;">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
      </nav>
      
      <div class="user-profile">
        <div class="avatar"><?php echo substr($full_name, 0, 1); ?></div>
        <div class="user-info">
          <h4><?php echo htmlspecialchars($full_name); ?></h4>
          <p><?php echo htmlspecialchars($role); ?></p>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      
      <!-- Overview Section -->
      <section id="overview" class="content-section">
        <div class="header">
          <div>
            <h1>Volunteer Overview</h1>
            <p>Welcome back, <?php echo htmlspecialchars($full_name); ?>. Here's your impact today.</p>
          </div>
        </div>

        <div class="grid">
          <div class="card">
            <div class="card-icon" style="color: var(--teal);"><i class="fa-solid fa-check-double"></i></div>
            <h3><?php echo $completed_tasks; ?> Tasks</h3>
            <p>Total tasks completed by you.</p>
          </div>
          <div class="card">
            <div class="card-icon" style="color: var(--amber);"><i class="fa-solid fa-clock"></i></div>
            <h3><?php echo $pending_tasks; ?> Pending</h3>
            <p>Assigned tasks awaiting your attention.</p>
          </div>
          <div class="card">
            <div class="card-icon" style="color: var(--rose);"><i class="fa-solid fa-heart"></i></div>
            <h3>Active</h3>
            <p>You are currently contributing to the relief efforts.</p>
          </div>
        </div>

        <div style="margin-top: 40px;">
          <h2 style="margin-bottom: 24px;">Your Tasks</h2>
          <div class="task-list">
            <?php if ($tasks_result && $tasks_result->num_rows > 0): ?>
                <?php while($task = $tasks_result->fetch_assoc()): ?>
                    <div class="task-item">
                      <div class="task-info">
                        <h4><?php echo htmlspecialchars($task['title']); ?></h4>
                        <p><?php echo htmlspecialchars($task['description']); ?> • Due: <?php echo $task['due_date']; ?></p>
                      </div>
                      <span class="status-badge status-<?php echo strtolower($task['status']); ?>"><?php echo $task['status']; ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No tasks assigned to you yet.</p>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- Tasks Section -->
      <section id="tasks" class="content-section" style="display: none;">
        <div class="header">
          <h1>Assigned Relief Tasks</h1>
        </div>
        <div class="task-list">
          <?php 
          // Reset result pointer
          if($tasks_result) $tasks_result->data_seek(0);
          if ($tasks_result && $tasks_result->num_rows > 0): 
          ?>
            <?php while($task = $tasks_result->fetch_assoc()): ?>
                <div class="task-item">
                  <div class="task-info">
                    <h4><?php echo htmlspecialchars($task['title']); ?></h4>
                    <p><?php echo htmlspecialchars($task['description']); ?></p>
                  </div>
                  <?php if($task['status'] != 'Completed'): ?>
                    <button class="btn btn-outline">Mark Done</button>
                  <?php else: ?>
                    <span class="status-badge status-completed">Completed</span>
                  <?php endif; ?>
                </div>
            <?php endwhile; ?>
          <?php endif; ?>
        </div>
      </section>

      <!-- Supplies Section -->
      <section id="supplies" class="content-section" style="display: none;">
        <div class="header">
          <h1>Record Delivered Supplies</h1>
        </div>
        <div class="card" style="max-width: 600px;">
          <div class="form-group">
            <label>Item Name</label>
            <input type="text" placeholder="e.g. Rice (5kg Bag)">
          </div>
          <div class="form-group">
            <label>Quantity</label>
            <input type="number" placeholder="0">
          </div>
          <div class="form-group">
            <label>Recipient Family ID / Name</label>
            <input type="text" placeholder="REF-1092 / Rahim Uddin">
          </div>
          <button class="btn btn-primary" style="width: 100%;">Log Distribution</button>
        </div>
      </section>

      <!-- Field Issues Section -->
      <section id="reports" class="content-section" style="display: none;">
        <div class="header">
          <h1>Report Field Issues</h1>
        </div>
        <div class="card" style="max-width: 600px;">
          <div class="form-group">
            <label>Issue Type</label>
            <select>
              <option>Supply Shortage</option>
              <option>Medical Emergency</option>
              <option>Infrastructure Damage</option>
              <option>Other</option>
            </select>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea rows="4" placeholder="Describe the urgent issue here..."></textarea>
          </div>
          <div class="form-group">
            <label>Location</label>
            <input type="text" placeholder="Sector / Block / Coordinates">
          </div>
          <button class="btn btn-primary" style="background: var(--rose); color: white; width: 100%;">Submit Urgent Report</button>
        </div>
      </section>

      <!-- Chat Section -->
      <section id="chat" class="content-section" style="display: none;">
        <div class="header">
          <h1>Messages with Manager</h1>
        </div>
        <div class="chat-box">
          <div class="chat-header">
            <div class="avatar">M</div>
            <div>
              <strong>Camp Manager</strong>
              <p style="font-size: 12px; color: var(--emerald);">● Online</p>
            </div>
          </div>
          <div class="chat-messages" id="chatMessages">
            <div class="message received">
              Welcome to the team. Please check your assigned tasks.
            </div>
          </div>
          <div class="chat-input">
            <input type="text" id="chatInput" placeholder="Type a message...">
            <button class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
          </div>
        </div>
      </section>

    </main>
  </div>

  <script src="script.js"></script>
</body>
</html>
