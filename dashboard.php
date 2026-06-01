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

// Fetch full user details from database
$user_info = [];
$user_stmt = $conn->prepare("SELECT u.full_name, u.email, u.phone, u.account_status, u.created_at, r.role_name 
                             FROM users u 
                             JOIN roles r ON u.role_id = r.role_id 
                             WHERE u.user_id = ?");
if ($user_stmt) {
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_res = $user_stmt->get_result();
    $user_info = $user_res->fetch_assoc();
    $user_stmt->close();
}

// Fetch Tasks for this volunteer
$tasks_query = "SELECT * FROM volunteer_tasks WHERE volunteer_id = '$user_id' ORDER BY due_date ASC";
$tasks_result = $conn->query($tasks_query);

// Fetch stats for this volunteer
$completed_tasks = $conn->query("SELECT COUNT(*) as total FROM volunteer_tasks WHERE volunteer_id = '$user_id' AND task_status = 'Completed'")->fetch_assoc()['total'];
$pending_tasks = $conn->query("SELECT COUNT(*) as total FROM volunteer_tasks WHERE volunteer_id = '$user_id' AND task_status = 'Pending'")->fetch_assoc()['total'];
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
      <div class="sidebar-logo" style="flex-direction: column; align-items: flex-start; gap: 4px; font-size: 14px; line-height: 1.3;">
        <div style="display: flex; align-items: center; gap: 8px; font-size: 20px; font-weight: 700; color: var(--primary); margin-bottom: 4px;">
          <span>🤝</span> Disaster Relief Network
        </div>
        <span style="font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px; color: var(--ink);">Disaster Relief Network</span>
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
        <a href="#profile" class="nav-item" onclick="showSection('profile', this)">
          <i class="fa-solid fa-user-gear"></i> My Profile
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
                        <h4><?php echo htmlspecialchars($task['task_title']); ?></h4>
                        <p><?php echo htmlspecialchars($task['task_description']); ?> • Due: <?php echo $task['due_date']; ?></p>
                      </div>
                      <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $task['task_status'])); ?>"><?php echo $task['task_status']; ?></span>
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

        <!-- Task Progress Card -->
        <div class="card progress-card" style="margin-bottom: 32px; padding: 24px 32px; background: rgba(255, 255, 255, 0.9);">
          <div class="progress-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-weight: 600; font-size: 15px; color: var(--ink);"><i class="fa-solid fa-chart-line" style="color: var(--primary); margin-right: 8px;"></i>Completion Progress</span>
            <span id="taskProgressPercent" style="font-weight: 700; font-size: 16px; color: var(--primary);">0%</span>
          </div>
          <div class="progress-bar-bg" style="width: 100%; height: 10px; background: var(--line-strong); border-radius: 99px; overflow: hidden;">
            <div id="taskProgressBar" style="width: 0%; height: 100%; background: linear-gradient(90deg, var(--primary), var(--primary-deep)); border-radius: 99px; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);"></div>
          </div>
          <p id="taskProgressStats" style="font-size: 13px; color: var(--muted); margin-top: 10px; margin-bottom: 0;">0 of 0 tasks completed</p>
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
                    <h4><?php echo htmlspecialchars($task['task_title']); ?></h4>
                    <p><?php echo htmlspecialchars($task['task_description']); ?></p>
                  </div>
                  <?php if($task['task_status'] != 'Completed'): ?>
                    <button class="btn btn-outline" onclick="markComplete(this, <?php echo $task['task_id']; ?>)">Mark Done</button>
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
        <div class="supplies-container">
          <div class="card supplies-form-card" style="margin-bottom: 0;">
            <h3 style="margin-bottom: 8px;"><i class="fa-solid fa-file-invoice" style="color: var(--primary); margin-right: 8px;"></i>Log Distribution</h3>
            <p style="margin-bottom: 24px; color: var(--muted); font-size: 14px;">Enter the details of the supplies delivered to camp residents.</p>
            <form id="suppliesForm" onsubmit="event.preventDefault(); logSupply();">
              <div class="form-group">
                <label for="supplyItemName">Item Name</label>
                <input type="text" id="supplyItemName" placeholder="e.g. Rice (5kg Bag)" required>
              </div>
              <div class="form-group">
                <label for="supplyQuantity">Quantity</label>
                <input type="number" id="supplyQuantity" placeholder="0" min="1" required>
              </div>
              <div class="form-group">
                <label for="supplyRecipient">Recipient Family ID / Name</label>
                <input type="text" id="supplyRecipient" placeholder="REF-1092 / Rahim Uddin" required>
              </div>
              <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; gap: 8px;">
                <i class="fa-solid fa-circle-check"></i> Log Distribution
              </button>
            </form>
          </div>

          <div class="card supplies-list-card" style="margin-bottom: 0;">
            <div class="supplies-list-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
              <h3><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary); margin-right: 8px;"></i>Recent Supplies Logged</h3>
              <button class="btn btn-outline" onclick="clearSupplyLogs()" style="padding: 6px 12px; font-size: 12px; border-radius: 8px; border-color: var(--line-strong);">
                <i class="fa-solid fa-trash-can" style="color: var(--primary);"></i> Clear All
              </button>
            </div>
            <p style="margin-bottom: 24px; color: var(--muted); font-size: 14px;">Running log of your logged distributions in this session.</p>
            <div class="logged-supplies-list" id="loggedSuppliesList">
              <!-- Dynamic content populated by JS -->
            </div>
          </div>
        </div>
      </section>

      <!-- Field Issues Section -->
      <section id="reports" class="content-section" style="display: none;">
        <div class="header">
          <h1>Report Field Issues</h1>
        </div>
        <div class="reports-container">
          <div class="card reports-form-card" style="margin-bottom: 0;">
            <h3 style="margin-bottom: 8px;"><i class="fa-solid fa-triangle-exclamation" style="color: var(--primary); margin-right: 8px;"></i>Report Field Issue</h3>
            <p style="margin-bottom: 24px; color: var(--muted); font-size: 14px;">Submit urgent problems, hazards, or resource shortages in the camp.</p>
            <form id="reportsForm" onsubmit="event.preventDefault(); logIssue();">
              <div class="form-group">
                <label for="issueType">Issue Type</label>
                <select id="issueType" required>
                  <option value="Supply Shortage">Supply Shortage</option>
                  <option value="Medical Emergency">Medical Emergency</option>
                  <option value="Infrastructure Damage">Infrastructure Damage</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="form-group">
                <label for="issueDescription">Description</label>
                <textarea id="issueDescription" rows="4" placeholder="Describe the urgent issue here..." required></textarea>
              </div>
              <div class="form-group">
                <label for="issueLocation">Location</label>
                <input type="text" id="issueLocation" placeholder="Sector / Block / Coordinates" required>
              </div>
              <button type="submit" class="btn btn-primary" style="background: var(--primary); color: white; width: 100%; justify-content: center; gap: 8px;">
                <i class="fa-solid fa-paper-plane"></i> Submit Urgent Report
              </button>
            </form>
          </div>

          <div class="card reports-list-card" style="margin-bottom: 0;">
            <div class="reports-list-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
              <h3><i class="fa-solid fa-list-ul" style="color: var(--primary); margin-right: 8px;"></i>Reported Field Issues</h3>
              <button class="btn btn-outline" onclick="clearIssueLogs()" style="padding: 6px 12px; font-size: 12px; border-radius: 8px; border-color: var(--line-strong);">
                <i class="fa-solid fa-trash-can" style="color: var(--primary);"></i> Clear All
              </button>
            </div>
            <p style="margin-bottom: 24px; color: var(--muted); font-size: 14px;">Recent active issues reported by you or other field volunteers.</p>
            <div class="logged-reports-list" id="loggedReportsList">
              <!-- Dynamic content populated by JS -->
            </div>
          </div>
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

      <!-- Profile Section -->
      <section id="profile" class="content-section" style="display: none;">
        <div class="header">
          <h1>Volunteer Profile</h1>
        </div>

        <div class="profile-container" style="display: grid; grid-template-columns: 1fr 2fr; gap: 32px; align-items: start;">
          
          <!-- Profile Sidebar Card -->
          <div class="card profile-card" style="text-align: center; padding: 32px 24px; display: flex; flex-direction: column; align-items: center; gap: 16px;">
            <div class="profile-avatar-wrapper" style="position: relative; width: 110px; height: 110px; border-radius: 50%; border: 3px solid var(--primary); padding: 4px; background: #fff;">
              <div class="profile-avatar-inner" style="width: 100%; height: 100%; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-deep)); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 36px; font-weight: 700;">
                <?php 
                  $initials = '';
                  if (!empty($user_info['full_name'])) {
                      $words = explode(" ", $user_info['full_name']);
                      $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                  } else {
                      $initials = 'V';
                  }
                  echo $initials; 
                ?>
              </div>
            </div>
            <div>
              <h3 style="font-size: 18px; font-weight: 700; color: var(--ink); margin-bottom: 4px;"><?php echo htmlspecialchars($user_info['full_name'] ?? 'Volunteer Name'); ?></h3>
              <p style="font-size: 13px; color: var(--muted); margin-bottom: 12px;"><i class="fa-solid fa-shield-halved" style="color: var(--primary); margin-right: 6px;"></i><?php echo htmlspecialchars($user_info['role_name'] ?? 'Volunteer'); ?></p>
              <span class="status-badge status-completed" style="background: rgba(16, 185, 129, 0.08); color: var(--emerald); border: 1px solid rgba(16, 185, 129, 0.15); font-size: 11px; padding: 4px 12px; border-radius: 99px;">
                <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i><?php echo htmlspecialchars($user_info['account_status'] ?? 'Approved'); ?> Account
              </span>
            </div>
          </div>

          <!-- Profile Details Card -->
          <div class="card profile-details-card" style="padding: 32px;">
            <h3 style="font-size: 18px; font-weight: 600; color: var(--ink); margin-bottom: 24px; border-bottom: 1px solid var(--line-strong); padding-bottom: 12px;">Account Details</h3>
            
            <div class="profile-details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
              <div class="detail-item">
                <label style="font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Full Name</label>
                <div style="font-size: 15px; color: var(--ink); font-weight: 500; padding: 10px 14px; background: var(--surface-2); border-radius: var(--radius-md); border: 1px solid var(--line-strong);">
                  <?php echo htmlspecialchars($user_info['full_name'] ?? 'Volunteer User'); ?>
                </div>
              </div>

              <div class="detail-item">
                <label style="font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Email Address</label>
                <div style="font-size: 15px; color: var(--ink); font-weight: 500; padding: 10px 14px; background: var(--surface-2); border-radius: var(--radius-md); border: 1px solid var(--line-strong);">
                  <?php echo htmlspecialchars($user_info['email'] ?? 'volunteer@reliefcamp.org'); ?>
                </div>
              </div>

              <div class="detail-item">
                <label style="font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Phone Number</label>
                <div style="font-size: 15px; color: var(--ink); font-weight: 500; padding: 10px 14px; background: var(--surface-2); border-radius: var(--radius-md); border: 1px solid var(--line-strong);">
                  <?php echo htmlspecialchars($user_info['phone'] ?? '+8801XXXXXXXXX'); ?>
                </div>
              </div>

              <div class="detail-item">
                <label style="font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Registered On</label>
                <div style="font-size: 15px; color: var(--ink); font-weight: 500; padding: 10px 14px; background: var(--surface-2); border-radius: var(--radius-md); border: 1px solid var(--line-strong);">
                  <?php echo htmlspecialchars($user_info['created_at'] ?? date("Y-m-d")); ?>
                </div>
              </div>
            </div>

            <!-- Optional status and information -->
            <div style="margin-top: 32px; padding: 16px; background: rgba(239, 68, 68, 0.04); border-left: 4px solid var(--primary); border-radius: var(--radius-md);">
              <h4 style="font-size: 14px; font-weight: 600; color: var(--primary); margin-bottom: 6px;"><i class="fa-solid fa-circle-info" style="margin-right: 8px;"></i>Volunteer Notice</h4>
              <p style="font-size: 13px; color: var(--muted); margin-bottom: 0; line-height: 1.5;">As a registered volunteer of Disaster Relief Network, you are authorized to update distributed inventory stocks and report emergency camps issues directly to administration. Keep your credentials secure.</p>
            </div>
          </div>
        </div>
      </section>

    </main>
  </div>

  <script src="script.js"></script>
</body>
</html>
