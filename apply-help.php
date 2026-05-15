<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apply for Aid | Disaster Relief Network</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

  <div class="auth-card" style="max-width: 600px;">
    <a href="index.php" class="sidebar-logo" style="justify-content: center; margin-bottom: 24px; text-decoration: none; color: var(--gold);">
      <span>🆘</span> Relief Application
    </a>
    
    <div style="position: absolute; top: 24px; left: 24px;">
      <a href="index.php" style="color: var(--muted); font-size: 14px; display: flex; align-items: center; gap: 8px;">
        ← Back to Home
      </a>
    </div>

    <h2>Apply for Assistance</h2>
    <p>Please provide your details so we can coordinate aid delivery to your location.</p>

    <form id="applyForm">
      <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" placeholder="Your Name" required>
        </div>
        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" name="phone" placeholder="01XXX-XXXXXX" required>
        </div>
      </div>

      <div class="form-group">
        <label>Current Location / Address</label>
        <textarea name="address" rows="3" placeholder="Block, Sector, or Landmark in the relief zone" required></textarea>
      </div>

      <div class="form-group">
        <label>Immediate Needs</label>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
          <label style="display: flex; align-items: center; gap: 10px; color: var(--ink-2); cursor: pointer;">
            <input type="checkbox" name="needs[]" value="Dry Food" style="width: auto;"> Dry Food
          </label>
          <label style="display: flex; align-items: center; gap: 10px; color: var(--ink-2); cursor: pointer;">
            <input type="checkbox" name="needs[]" value="Clean Water" style="width: auto;"> Clean Water
          </label>
          <label style="display: flex; align-items: center; gap: 10px; color: var(--ink-2); cursor: pointer;">
            <input type="checkbox" name="needs[]" value="Medical Help" style="width: auto;"> Medical Help
          </label>
          <label style="display: flex; align-items: center; gap: 10px; color: var(--ink-2); cursor: pointer;">
            <input type="checkbox" name="needs[]" value="Baby Supplies" style="width: auto;"> Baby Supplies
          </label>
        </div>
      </div>

      <div class="form-group">
        <label>Family Size</label>
        <input type="number" name="member_count" min="1" placeholder="Number of people" required>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px;">Submit Application</button>
    </form>

    <div id="successMsg" style="display: none; margin-top: 24px; text-align: center; color: var(--emerald);">
      <i class="fa-solid fa-circle-check"></i> Application submitted! Our team will contact you soon.
    </div>
  </div>

  <script>
    document.getElementById('applyForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      
      fetch('process_aid.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if(data.status === 'success') {
          this.style.opacity = '0.5';
          this.style.pointerEvents = 'none';
          document.getElementById('successMsg').style.display = 'block';
          setTimeout(() => {
            window.location.href = 'index.php';
          }, 3000);
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong. Please try again.');
      });
    });
  </script>
</body>
</html>
