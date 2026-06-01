<?php
require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>Registration Fix Tool</title>
<style>
  body { font-family: Arial, sans-serif; background: #0f172a; color: #e2e8f0; padding: 30px; }
  h1 { color: #f59e0b; }
  .box { padding: 15px 20px; border-radius: 8px; margin: 10px 0; font-size: 15px; }
  .ok   { background: #14532d; border-left: 4px solid #22c55e; }
  .fail { background: #7f1d1d; border-left: 4px solid #ef4444; }
  .info { background: #1e3a5f; border-left: 4px solid #3b82f6; }
  .warn { background: #78350f; border-left: 4px solid #f59e0b; }
  table { border-collapse: collapse; width: 100%; margin-top: 10px; }
  th { background: #334155; padding: 10px; text-align: left; }
  td { padding: 8px 10px; border-bottom: 1px solid #334155; }
  a.btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #f59e0b; color: #000; border-radius: 6px; text-decoration: none; font-weight: bold; }
  code { background: #334155; padding: 2px 6px; border-radius: 4px; font-size: 13px; }
</style>
</head>
<body>
<h1>🔧 Registration Diagnostic & Fix Tool</h1>

<?php

$all_ok = true;

// ── STEP 1: Connection check ──────────────────────────────────
if ($conn->connect_error) {
    echo "<div class='box fail'>❌ <strong>Database সংযোগ ব্যর্থ!</strong><br>Error: " . $conn->connect_error . "
    <br><br>সমাধান: XAMPP এ <strong>Apache</strong> এবং <strong>MySQL</strong> চালু করুন।</div>";
    exit;
}
echo "<div class='box ok'>✅ Database সংযোগ সফল — <code>disaster_relief_db</code></div>";


// ── STEP 2: roles table check ─────────────────────────────────
$r = $conn->query("SELECT role_id, role_name FROM roles ORDER BY role_id");
if (!$r || $r->num_rows === 0) {
    $all_ok = false;
    echo "<div class='box fail'>❌ <strong>roles টেবিল খালি বা নেই!</strong><br>
    সমাধান: নিচের বাটনে ক্লিক করুন অথবা phpMyAdmin এ <code>database.sql</code> import করুন।</div>";

    // Auto-fix: insert roles
    if (isset($_GET['fix'])) {
        $conn->query("INSERT IGNORE INTO roles (role_name) VALUES ('Admin'),('Camp Manager'),('Volunteer'),('Donor'),('Affected Person')");
        echo "<div class='box ok'>✅ roles টেবিল fix করা হয়েছে! পেজ রিলোড করুন।</div>";
    }
} else {
    echo "<div class='box ok'>✅ roles টেবিল ঠিক আছে:<br><table><tr><th>role_id</th><th>role_name</th></tr>";
    while ($row = $r->fetch_assoc()) {
        $hi = ($row['role_name'] === 'Volunteer') ? " style='background:#1a3a1a'" : "";
        echo "<tr$hi><td>{$row['role_id']}</td><td>{$row['role_name']}</td></tr>";
    }
    echo "</table></div>";
}


// ── STEP 3: Volunteer role specifically ───────────────────────
$vr = $conn->query("SELECT role_id FROM roles WHERE role_name='Volunteer' LIMIT 1");
if (!$vr || $vr->num_rows === 0) {
    $all_ok = false;
    echo "<div class='box fail'>❌ <strong>'Volunteer' role পাওয়া যায়নি!</strong><br>Fix বাটনে ক্লিক করুন।</div>";
} else {
    $vid = $vr->fetch_assoc()['role_id'];
    echo "<div class='box ok'>✅ Volunteer role_id = <strong>$vid</strong></div>";
}


// ── STEP 4: users table structure ────────────────────────────
$cols = $conn->query("SHOW COLUMNS FROM users");
if (!$cols) {
    $all_ok = false;
    echo "<div class='box fail'>❌ <strong>users টেবিল নেই!</strong> database.sql import করুন।</div>";
} else {
    $col_names = [];
    while ($c = $cols->fetch_assoc()) $col_names[] = $c['Field'];
    $required = ['user_id','role_id','full_name','email','password_hash','account_status'];
    $missing = array_diff($required, $col_names);
    if ($missing) {
        $all_ok = false;
        echo "<div class='box fail'>❌ users টেবিলে এই কলামগুলো নেই: <code>" . implode(', ', $missing) . "</code></div>";
    } else {
        echo "<div class='box ok'>✅ users টেবিল structure ঠিক আছে। কলাম: <code>" . implode(', ', $col_names) . "</code></div>";
    }
}


// ── STEP 5: Test INSERT (rollback) ────────────────────────────
$conn->begin_transaction();
$test = $conn->query("INSERT INTO users (role_id, full_name, email, password_hash, account_status) 
                      VALUES (3, 'Diag Test', 'diag_delete_me@test.com', 'x', 'Pending')");
if ($test) {
    $conn->rollback();
    echo "<div class='box ok'>✅ Test INSERT সফল! Registration কাজ করবে।</div>";
} else {
    $all_ok = false;
    $conn->rollback();
    echo "<div class='box fail'>❌ <strong>Test INSERT ব্যর্থ!</strong><br>
    MySQL Error: <code>" . $conn->error . "</code><br><br>
    <strong>এটাই মূল সমস্যা।</strong> নিচের সমাধান দেখুন।</div>";

    // Interpret common errors
    $err = $conn->error;
    if (stripos($err, 'foreign key') !== false) {
        echo "<div class='box warn'>⚠️ <strong>Foreign Key Error:</strong> role_id=3 roles টেবিলে নেই। Fix বাটনে ক্লিক করুন।</div>";
    } elseif (stripos($err, 'Duplicate entry') !== false) {
        echo "<div class='box warn'>⚠️ এই email ইতিমধ্যে registered আছে।</div>";
    } elseif (stripos($err, 'doesn\'t exist') !== false) {
        echo "<div class='box warn'>⚠️ users টেবিল নেই। database.sql import করুন।</div>";
    }
}


// ── STEP 6: Total users count ─────────────────────────────────
$uc = $conn->query("SELECT COUNT(*) as c FROM users");
if ($uc) {
    $cnt = $uc->fetch_assoc()['c'];
    echo "<div class='box info'>👥 বর্তমানে users টেবিলে <strong>$cnt</strong> জন user আছেন।</div>";
}


// ── Final status ─────────────────────────────────────────────
echo "<hr style='border-color:#334155;margin:20px 0'>";
if ($all_ok) {
    echo "<div class='box ok' style='font-size:18px'>🎉 <strong>সব ঠিক আছে!</strong> এখন Registration চেষ্টা করুন।</div>";
    echo "<p style='color:#94a3b8'>যদি এরপরও error হয়, Login.php তে registration করার পর যে error দেখায় সেটার screenshot নিন।</p>";
} else {
    echo "<div class='box fail' style='font-size:18px'>🚨 <strong>সমস্যা পাওয়া গেছে!</strong> উপরের Fix বাটনে ক্লিক করুন।</div>";
    echo "<a class='btn' href='?fix=1'>⚡ Auto Fix করুন</a>";
}
?>

<br><br>
<a class="btn" href="Login.php" style="background:#334155;color:white">← Login পেজে ফিরে যান</a>
<p style="color:#64748b;font-size:12px;margin-top:30px">⚠️ Debug শেষ হলে এই ফাইল (test_db.php) delete করুন।</p>
</body>
</html>
