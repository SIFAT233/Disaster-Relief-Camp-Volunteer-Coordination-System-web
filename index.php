<?php 
require_once 'db_connect.php'; 

// Fetch Stats
$camp_count = $conn->query("SELECT COUNT(*) as total FROM camps")->fetch_assoc()['total'] ?? 0;
$volunteer_count = $conn->query("SELECT COUNT(*) as total FROM users JOIN roles ON users.role_id = roles.role_id WHERE role_name = 'Volunteer'")->fetch_assoc()['total'] ?? 0;
$donation_sum = $conn->query("SELECT SUM(amount) as total FROM donations WHERE status = 'Verified'")->fetch_assoc()['total'] ?? 0;
$family_count = $conn->query("SELECT COUNT(*) as total FROM affected_families")->fetch_assoc()['total'] ?? 0;

// Fetch Active Camps
$camps_query = "SELECT camps.*, disaster_events.category, disaster_events.event_name 
                FROM camps 
                LEFT JOIN disaster_events ON camps.event_id = disaster_events.event_id 
                LIMIT 3";
$camps_result = $conn->query($camps_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Disaster Relief Camp & Volunteer Coordination System</title>
  <meta name="description"
    content="An integrated platform for disaster camp management, volunteer coordination, donation tracking, and aid delivery to affected communities." />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="Home.css" />
</head>

<body>

  <!-- Top Emergency Bar -->
  <div class="top-bar">
    <div class="container top-bar-inner">
      <span>🚨 Emergency Hotline: <strong>999</strong> | Fire Service: <strong>102</strong> | Red Crescent:
        <strong>02-9330188</strong></span>
      <span class="status">● System Active — 24/7</span>
    </div>
  </div>

  <!-- Navigation -->
  <header class="navbar">
    <div class="container nav-inner">
      <a href="index.php" class="logo">
        <span class="logo-mark">🛟</span>
        <span class="logo-text">Disaster<small>Relief Network</small></span>
      </a>
      <nav class="nav-links" id="navLinks">
        <a href="index.php#home">Home</a>
        <a href="index.php#about">About</a>
        <a href="index.php#stakeholders">Roles</a>
        <a href="index.php#features">Features</a>
        <a href="index.php#camps">Active Camps</a>
        <a href="#donate">Donate</a>
        <a href="#contact">Contact</a>
      </nav>
      <div class="nav-actions">
        <a href="Login.php" class="btn btn-ghost">Login</a>
        <a href="Login.php" class="btn btn-primary">Sign Up</a>
        <button class="hamburger" id="hamburger" aria-label="Menu">☰</button>
      </div>
    </div>
  </header>

  <!-- 1. HERO -->
  <section id="home" class="hero">
    <div class="hero-bg"></div>
    <div class="container hero-inner">
      <div class="hero-content">
        <span class="badge">🌍 An Integrated Disaster Coordination Platform</span>
        <h1>In Every Disaster, <span class="grad">We Stand </span><br />Together for Humanity</h1>
        <p>One unified system for camp management, volunteer coordination, donation tracking, and rapid aid delivery to
          affected families — all in real time.</p>
        <div class="hero-cta">
          <a href="apply-help.html" class="btn btn-primary btn-lg">Apply for Help</a>
          <a href="#donate" class="btn btn-outline btn-lg">Donate Now</a>
        </div>
        <div class="hero-stats">
          <div>
            <h3><?php echo number_format($camp_count); ?>+</h3>
            <p>Active Camps</p>
          </div>
          <div>
            <h3><?php echo number_format($volunteer_count); ?></h3>
            <p>Volunteers</p>
          </div>
          <div>
            <h3><?php echo number_format($donation_sum); ?> TK</h3>
            <p>Donations Raised</p>
          </div>
          <div>
            <h3><?php echo number_format($family_count); ?>+</h3>
            <p>Families Helped</p>
          </div>
        </div>
      </div>
      <div class="hero-card">
        <div class="card-head">
          <span class="pulse"></span>
          <h4>Live Situation</h4>
        </div>
        <ul class="live-list">
          <li><span class="dot red"></span> Flood — Sylhet Region <em>High Risk</em></li>
          <li><span class="dot orange"></span> Cyclone — Cox's Bazar <em>Warning</em></li>
          <li><span class="dot yellow"></span> Landslide — Bandarban <em>Monitoring</em></li>
          <li><span class="dot green"></span> Recovery — Khulna <em>Ongoing</em></li>
        </ul>
        <a href="dashboard.html" class="btn btn-dark btn-block">View Full Dashboard →</a>
      </div>
    </div>
  </section>

  <!-- 2. ACTIVE CAMPS -->
  <section id="camps" class="section camps">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Ongoing Operations</span>
        <h2>Active Relief Camps</h2>
      </div>
      <div class="camp-grid">
        <?php if ($camps_result && $camps_result->num_rows > 0): ?>
            <?php while($camp = $camps_result->fetch_assoc()): ?>
                <article class="camp-card">
                  <div class="camp-img" style="background-image: url('hh.jpg.png');"></div>
                  <div class="camp-body">
                    <span class="tag tag-red"><?php echo htmlspecialchars($camp['category'] ?? 'General'); ?></span>
                    <h3><?php echo htmlspecialchars($camp['camp_name']); ?></h3>
                    <p>📍 <?php echo htmlspecialchars($camp['address']); ?></p>
                    <div class="progress"><span style="width:70%"></span></div>
                    <small>Capacity: <?php echo $camp['capacity']; ?> families</small>
                  </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No active camps at the moment.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- 3. FEATURES -->
  <section id="features" class="section features">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">Additional Features</span>
        <h2>Powerful tools, simple to use</h2>
      </div>
      <div class="feature-grid">
        <div class="feature">
          <div class="f-ico">📦</div>
          <h4>Real-Time Stock Alerts</h4>
          <p>Automatic notifications when food, medicine, or shelter supplies run low.</p>
        </div>
        <div class="feature">
          <div class="f-ico">📊</div>
          <h4>Reporting Dashboard</h4>
          <p>Camp-wise statistics, charts, and one-click PDF export.</p>
        </div>
        <div class="feature">
          <div class="f-ico">💬</div>
          <h4>Integrated Chat</h4>
          <p>Secure messaging across all roles for faster coordination.</p>
        </div>
        <div class="feature">
          <div class="f-ico">🚨</div>
          <h4>Emergency Alerts</h4>
          <p>One-click broadcast from admin to every user on the platform.</p>
        </div>
        <div class="feature">
          <div class="f-ico">🗺️</div>
          <h4>Camp Location Map</h4>
          <p>Geographic view of camps and aid distribution in real time.</p>
        </div>
        <div class="feature">
          <div class="f-ico">🧾</div>
          <h4>Transparent Donation Tracking</h4>
          <p>See exactly where your donation goes — from contribution to delivery.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. STAKEHOLDERS (ROLES) -->
  <section id="stakeholders" class="section stakeholders">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow">6 Roles · One Platform</span>
        <h2>Which role fits you?</h2>
        <p>Each user gets a dedicated dashboard and feature set tailored to their responsibilities.</p>
      </div>

      <div class="role-grid">
        <a href="admin.html" class="role-card r1">
          <div class="role-icon">🛡️</div>
          <h3>Admin</h3>
          <p>Manage camp managers, monitor stock, generate reports, and broadcast emergencies.</p>
          <ul>
            <li>• Add / remove managers</li>
            <li>• Approve accounts</li>
            <li>• PDF distribution reports</li>
            <li>• Emergency broadcasts</li>
          </ul>
          <span class="role-link">Admin Panel →</span>
        </a>

        <a href="manager.html" class="role-card r2">
          <div class="role-icon">🏕️</div>
          <h3>Camp Manager</h3>
          <p>Run your camp, register affected families, manage stock, and assign work.</p>
          <ul>
            <li>• Family registration</li>
            <li>• Food / medicine / shelter stock</li>
            <li>• Volunteer task assignment</li>
            <li>• Camp reports (PDF)</li>
          </ul>
          <span class="role-link">Manager Dashboard →</span>
        </a>

        <a href="Login.php" class="role-card r3">
          <div class="role-icon">🤝</div>
          <h3>Volunteer</h3>
          <p>View assigned tasks, update status, and record relief distributions.</p>
          <ul>
            <li>• Task view & status</li>
            <li>• Distribution log</li>
            <li>• Report emergency issues</li>
            <li>• Chat with manager</li>
          </ul>
          <span class="role-link">Become a Volunteer →</span>
        </a>

        <a href="donate.html" class="role-card r4">
          <div class="role-icon">💰</div>
          <h3>Donor</h3>
          <p>Donate money or goods, track usage, and download receipts.</p>
          <ul>
            <li>• Donation history</li>
            <li>• Usage tracking</li>
            <li>• PDF receipts</li>
            <li>• Chat with admin</li>
          </ul>
          <span class="role-link">Start Donating →</span>
        </a>

        <a href="apply-help.html" class="role-card r5">
          <div class="role-icon">🆘</div>
          <h3>Affected People</h3>
          <p>Apply for help and track the status of your request.</p>
          <ul>
            <li>• Online application</li>
            <li>• Aid status tracking</li>
            <li>• Find nearest camp</li>
            <li>• Chat with camp</li>
          </ul>
          <span class="role-link">Apply Now →</span>
        </a>

        <a href="guest.html" class="role-card r6">
          <div class="role-icon">🌐</div>
          <h3>Guest</h3>
          <p>Explore awareness info, hotlines, and ongoing relief activities.</p>
          <ul>
            <li>• Emergency hotlines</li>
            <li>• Live updates</li>
            <li>• Donation page access</li>
            <li>• Contact info</li>
          </ul>
          <span class="role-link">Learn More →</span>
        </a>
      </div>
    </div>
  </section>

  <!-- 5. ABOUT US (Moved to last as requested) -->
  <section id="about" class="section about">
    <div class="container">
      <div class="about-card">
        <span class="eyebrow">About Us</span>
        <h2>Coordinated response to every disaster</h2>
        <p>Disaster Relief Network is a centralized platform that connects administrators, camp managers, volunteers,
          donors, and affected people in one place. We bring stock management, task assignment, reporting, and chat
          together — so help reaches people faster.</p>
        <ul class="check-list">
          <li>✅ Real-time stock and supply monitoring</li>
          <li>✅ Transparent donation tracking with PDF receipts</li>
          <li>✅ Automated emergency alert system</li>
          <li>✅ Multi-role chat and coordination</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- DONATE CTA -->
  <section id="donate" class="donate-cta">
    <div class="container donate-inner">
      <div>
        <h2>Your small help, <br />someone's biggest hope.</h2>
        <p>Every dollar reaches affected families directly — with full transparent tracking from start to finish.</p>
      </div>
      <div class="donate-actions">
        <a href="donate.html" class="btn btn-primary btn-lg">Donate Online</a>
        <a href="donate.html#goods" class="btn btn-primary btn-lg">📦 Donate Goods</a>
      </div>
    </div>
  </section>

  <!-- CONTACT / FOOTER -->
  <footer id="contact" class="footer">
    <div class="container footer-grid">
      <div>
        <a href="index.php" class="logo light">
          <span class="logo-mark">🛟</span>
          <span class="logo-text">Ashroy<small>Relief Network</small></span>
        </a>
        <p>The most integrated digital platform for disaster response and humanitarian coordination.</p>
      </div>
      <div>
        <h5>Quick Links</h5>
        <a href="#about">About Us</a>
        <a href="#stakeholders">Roles</a>
        <a href="#features">Features</a>
        <a href="#camps">Camps</a>
      </div>
      <div>
        <h5>Get Involved</h5>
        <a href="apply-help.html">Apply for Help</a>
        <a href="Login.php">Become a Volunteer</a>
        <a href="donate.html">Donate</a>
        <a href="Login.php">Login</a>
      </div>
      <div>
        <h5>Contact</h5>
        <p>📞 Hotline: <strong>999</strong></p>
        <p>✉️ help@ashroy.org</p>
        <p>📍 Dhaka, Bangladesh</p>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="container">
        <span>© 2026 Ashroy Relief Network · All rights reserved</span>
        <span>Made By SIFAT</span>
      </div>
    </div>
  </footer>

  <script>
    // Mobile Menu Toggle
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');

    if (hamburger && navLinks) {
      hamburger.addEventListener('click', () => {
        navLinks.classList.toggle('open');
        hamburger.innerText = navLinks.classList.contains('open') ? '✕' : '☰';
      });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth'
          });
          // Close mobile menu if open
          if (navLinks.classList.contains('open')) {
            navLinks.classList.remove('open');
            hamburger.innerText = '☰';
          }
        }
      });
    });
  </script>
</body>

</html>
