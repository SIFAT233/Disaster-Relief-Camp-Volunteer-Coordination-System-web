-- ডাটাবেজ তৈরি
CREATE DATABASE IF NOT EXISTS disaster_relief_pro_db;
USE disaster_relief_pro_db;

-- ১. লোকেশন ম্যানেজমেন্ট (লোকেশন ডাইনামিক করার জন্য)
CREATE TABLE IF NOT EXISTS districts (
    district_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS upazilas (
    upazila_id INT PRIMARY KEY AUTO_INCREMENT,
    district_id INT,
    name VARCHAR(50) NOT NULL,
    FOREIGN KEY (district_id) REFERENCES districts(district_id)
);

-- ২. ইউজার এবং রোলস
CREATE TABLE IF NOT EXISTS roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL
);

-- Insert roles if not exists
INSERT INTO roles (role_name) 
SELECT * FROM (SELECT 'Admin') AS tmp WHERE NOT EXISTS (SELECT role_name FROM roles WHERE role_name = 'Admin') LIMIT 1;
INSERT INTO roles (role_name) 
SELECT * FROM (SELECT 'Camp Manager') AS tmp WHERE NOT EXISTS (SELECT role_name FROM roles WHERE role_name = 'Camp Manager') LIMIT 1;
INSERT INTO roles (role_name) 
SELECT * FROM (SELECT 'Volunteer') AS tmp WHERE NOT EXISTS (SELECT role_name FROM roles WHERE role_name = 'Volunteer') LIMIT 1;
INSERT INTO roles (role_name) 
SELECT * FROM (SELECT 'Donor') AS tmp WHERE NOT EXISTS (SELECT role_name FROM roles WHERE role_name = 'Donor') LIMIT 1;
INSERT INTO roles (role_name) 
SELECT * FROM (SELECT 'Affected') AS tmp WHERE NOT EXISTS (SELECT role_name FROM roles WHERE role_name = 'Affected') LIMIT 1;
INSERT INTO roles (role_name) 
SELECT * FROM (SELECT 'Guest') AS tmp WHERE NOT EXISTS (SELECT role_name FROM roles WHERE role_name = 'Guest') LIMIT 1;

CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role_id INT,
    profile_image VARCHAR(255),
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

-- ৩. ডিজাস্টার ক্যাটাগরি এবং ইভেন্ট
CREATE TABLE IF NOT EXISTS disaster_events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    event_name VARCHAR(100) NOT NULL, -- যেমন: 'Flood 2026', 'Cyclone Remal'
    category ENUM('Flood', 'Earthquake', 'Fire', 'Cyclone', 'Other'),
    start_date DATE,
    description TEXT
);

-- ৪. ক্যাম্প ইনফরমেশন (লোকেশন টেবিলের সাথে কানেক্টেড)
CREATE TABLE IF NOT EXISTS camps (
    camp_id INT PRIMARY KEY AUTO_INCREMENT,
    camp_name VARCHAR(100) NOT NULL,
    upazila_id INT,
    address TEXT NOT NULL,
    capacity INT DEFAULT 0,
    manager_id INT,
    event_id INT,
    FOREIGN KEY (upazila_id) REFERENCES upazilas(upazila_id),
    FOREIGN KEY (manager_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (event_id) REFERENCES disaster_events(event_id)
);

-- ৫. সাপ্লাই এবং ইনভেন্টরি ট্র্যাকিং
CREATE TABLE IF NOT EXISTS supplies (
    supply_id INT PRIMARY KEY AUTO_INCREMENT,
    item_name VARCHAR(100) NOT NULL,
    unit VARCHAR(20) -- KG, Liters, Pcs
);

CREATE TABLE IF NOT EXISTS camp_inventory (
    inv_id INT PRIMARY KEY AUTO_INCREMENT,
    camp_id INT,
    supply_id INT,
    current_stock DECIMAL(10,2) DEFAULT 0,
    min_threshold INT DEFAULT 10, -- স্টক কতর নিচে নামলে এলার্ট দিবে
    FOREIGN KEY (camp_id) REFERENCES camps(camp_id),
    FOREIGN KEY (supply_id) REFERENCES supplies(supply_id)
);

-- স্টক ইন/আউট হিস্ট্রি (অডিট করার জন্য)
CREATE TABLE IF NOT EXISTS inventory_movements (
    movement_id INT PRIMARY KEY AUTO_INCREMENT,
    camp_id INT,
    supply_id INT,
    quantity DECIMAL(10,2),
    type ENUM('In', 'Out'), -- স্টক আসলো নাকি গেল
    remarks VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES camps(camp_id),
    FOREIGN KEY (supply_id) REFERENCES supplies(supply_id)
);

-- ৬. এইড ডিস্ট্রিবিউশন এবং ফ্যামিলি ডিটেইলস
CREATE TABLE IF NOT EXISTS affected_families (
    family_id INT PRIMARY KEY AUTO_INCREMENT,
    head_name VARCHAR(100),
    nid_no VARCHAR(20) UNIQUE,
    member_count INT,
    camp_id INT,
    FOREIGN KEY (camp_id) REFERENCES camps(camp_id)
);

-- মেডিকেল রেকর্ড (জরুরি ঔষধের প্রয়োজনে)
CREATE TABLE IF NOT EXISTS medical_records (
    record_id INT PRIMARY KEY AUTO_INCREMENT,
    family_member_name VARCHAR(100),
    family_id INT,
    health_condition TEXT,
    required_medicine TEXT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (family_id) REFERENCES affected_families(family_id)
);

-- ৭. ডোনেশন এবং এক্সপেন্স (টাকা কোথায় খরচ হচ্ছে)
CREATE TABLE IF NOT EXISTS donations (
    donation_id INT PRIMARY KEY AUTO_INCREMENT,
    donor_id INT,
    amount DECIMAL(15,2),
    payment_method ENUM('Bkash', 'Nagad', 'Bank', 'Cash'),
    transaction_id VARCHAR(100),
    status ENUM('Pending', 'Verified') DEFAULT 'Pending',
    donation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS camp_expenses (
    expense_id INT PRIMARY KEY AUTO_INCREMENT,
    camp_id INT,
    amount DECIMAL(15,2),
    expense_purpose TEXT, -- যেমন: 'Buying clean water'
    spent_by INT,
    expense_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES camps(camp_id),
    FOREIGN KEY (spent_by) REFERENCES users(user_id)
);

-- ৮. চ্যাট, টাস্ক এবং এলার্টস
CREATE TABLE IF NOT EXISTS tasks (
    task_id INT PRIMARY KEY AUTO_INCREMENT,
    camp_id INT,
    volunteer_id INT,
    title VARCHAR(255),
    description TEXT,
    status ENUM('Pending', 'Ongoing', 'Completed'),
    due_date DATE,
    FOREIGN KEY (camp_id) REFERENCES camps(camp_id),
    FOREIGN KEY (volunteer_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS messages (
    msg_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT,
    receiver_id INT,
    message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS emergency_alerts (
    alert_id INT PRIMARY KEY AUTO_INCREMENT,
    posted_by INT,
    title VARCHAR(100),
    message TEXT,
    urgency ENUM('Low', 'Medium', 'High', 'Critical'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(user_id)
);
