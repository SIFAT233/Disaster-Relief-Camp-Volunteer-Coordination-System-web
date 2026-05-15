<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Make a Donation | Disaster Relief Network</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .donation-tabs {
      display: flex;
      gap: 12px;
      margin-bottom: 32px;
      background: var(--surface-2);
      padding: 8px;
      border-radius: 14px;
      border: 1px solid var(--line);
    }
    .tab-btn {
      flex: 1;
      padding: 12px;
      border-radius: 10px;
      text-align: center;
      cursor: pointer;
      font-weight: 600;
      transition: .3s var(--ease);
      color: var(--muted);
    }
    .tab-btn.active {
      background: var(--primary);
      color: #fff;
    }
    .payment-methods {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      margin-bottom: 24px;
    }
    .payment-card {
      padding: 20px;
      border: 1px solid var(--line);
      border-radius: 12px;
      text-align: center;
      cursor: pointer;
      transition: .3s var(--ease);
    }
    .payment-card:hover, .payment-card.active {
      border-color: var(--gold);
      background: var(--surface-glass);
    }
    .payment-card i {
      font-size: 24px;
      margin-bottom: 8px;
      display: block;
    }
  </style>
</head>
<body class="auth-page">

  <div class="auth-card" style="max-width: 650px;">
    <a href="index.php" class="sidebar-logo" style="justify-content: center; margin-bottom: 24px; text-decoration: none; color: var(--gold);">
      <span>🤝</span> Contribution Portal
    </a>
    
    <div style="position: absolute; top: 24px; left: 24px;">
      <a href="index.php" style="color: var(--muted); font-size: 14px; display: flex; align-items: center; gap: 8px;">
        ← Back to Home
      </a>
    </div>

    <div class="donation-tabs">
      <div id="onlineTab" class="tab-btn active" onclick="switchDonation('online')">Donate Online</div>
      <div id="goodsTab" class="tab-btn" onclick="switchDonation('goods')">📦 Donate Goods</div>
    </div>

    <!-- Online Donation Form -->
    <div id="onlineForm">
      <h2>Support Financially</h2>
      <p>Your monetary contribution helps us purchase urgent supplies and logistics.</p>

      <form id="financialForm">
        <div class="form-group">
          <label>Select Amount (TK)</label>
          <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 8px;">
            <button type="button" class="btn btn-outline" style="padding: 10px;" onclick="setAmount(500)">500</button>
            <button type="button" class="btn btn-outline" style="padding: 10px;" onclick="setAmount(1000)">1000</button>
            <button type="button" class="btn btn-outline" style="padding: 10px;" onclick="setAmount(5000)">5000</button>
            <button type="button" class="btn btn-outline" style="padding: 10px;" onclick="setAmount(10000)">10000</button>
          </div>
          <input type="number" name="amount" id="customAmount" placeholder="Custom Amount" style="margin-top: 12px;" required>
        </div>

        <div class="form-group">
          <label>Payment Method</label>
          <input type="hidden" name="payment_method" id="paymentMethod" value="Bkash">
          <div class="payment-methods">
            <div class="payment-card active" onclick="setPayment('Bank', this)">
              <i class="fa-brands fa-cc-visa"></i>
              <span>Bank</span>
            </div>
            <div class="payment-card" onclick="setPayment('Cash', this)">
              <i class="fa-solid fa-money-bill"></i>
              <span>Cash</span>
            </div>
            <div class="payment-card" onclick="setPayment('Bkash', this)">
              <i class="fa-solid fa-mobile-screen-button"></i>
              <span>bKash</span>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px;">Complete Donation</button>
      </form>
    </div>

    <!-- Goods Donation Form -->
    <div id="goodsForm" style="display: none;">
      <h2>Donate Physical Goods</h2>
      <p>Provide dry food, medicine, or clothing for affected families.</p>

      <div class="form-group">
        <label>Select Items to Donate</label>
        <select multiple style="height: 120px;">
          <option>Dry Food (Rice, Lentils, Biscuits)</option>
          <option>Clean Drinking Water</option>
          <option>Medical Kits / First Aid</option>
          <option>Warm Clothing / Blankets</option>
          <option>Hygiene / Sanitation Kits</option>
        </select>
        <small style="color: var(--muted);">Hold Ctrl to select multiple</small>
      </div>

      <div class="form-group">
        <label>Estimated Quantity</label>
        <input type="text" placeholder="e.g. 50kg Rice, 10 Blankets">
      </div>

      <div class="form-group">
        <label>Pickup Location</label>
        <input type="text" placeholder="Your Address for pickup">
      </div>

      <button class="btn btn-primary" style="width: 100%; padding: 16px;" onclick="submitDonation()">Schedule Pickup</button>
    </div>

    <div id="thankYouMsg" style="display: none; margin-top: 24px; text-align: center; color: var(--emerald);">
      <i class="fa-solid fa-circle-check" style="font-size: 40px; margin-bottom: 16px; display: block;"></i>
      <h3>Thank You for Your Generosity!</h3>
      <p>Your contribution has been logged. Transaction ID: <strong id="txnId"></strong></p>
      <a href="index.php" class="btn btn-outline" style="margin-top: 20px;">Back to Home</a>
    </div>
  </div>

  <script>
    function switchDonation(type) {
      const online = document.getElementById('onlineForm');
      const goods = document.getElementById('goodsForm');
      const onlineTab = document.getElementById('onlineTab');
      const goodsTab = document.getElementById('goodsTab');

      if (type === 'online') {
        online.style.display = 'block';
        goods.style.display = 'none';
        onlineTab.classList.add('active');
        goodsTab.classList.remove('active');
      } else {
        online.style.display = 'none';
        goods.style.display = 'block';
        onlineTab.classList.remove('active');
        goodsTab.classList.add('active');
      }
    }

    function setAmount(val) {
      document.getElementById('customAmount').value = val;
    }

    function setPayment(method, el) {
      document.getElementById('paymentMethod').value = method;
      document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('active'));
      el.classList.add('active');
    }

    document.getElementById('financialForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      fetch('process_donation.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if(data.status === 'success') {
          document.getElementById('onlineForm').style.display = 'none';
          document.getElementById('goodsForm').style.display = 'none';
          document.querySelector('.donation-tabs').style.display = 'none';
          document.getElementById('thankYouMsg').style.display = 'block';
          document.getElementById('txnId').innerText = data.transaction_id;
        } else {
          alert('Error: ' + data.message);
        }
      });
    });

    function submitDonation() {
      // For goods donation, just show thank you for now
      document.getElementById('onlineForm').style.display = 'none';
      document.getElementById('goodsForm').style.display = 'none';
      document.querySelector('.donation-tabs').style.display = 'none';
      document.getElementById('thankYouMsg').style.display = 'block';
      document.getElementById('txnId').innerText = "PHYSICAL-GOODS";
    }

    // Check for hash on load
    window.onload = () => {
      if (window.location.hash === '#goods') {
        switchDonation('goods');
      }
    };
  </script>
</body>
</html>
