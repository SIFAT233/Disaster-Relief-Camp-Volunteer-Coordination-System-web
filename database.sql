-- Disaster Relief Camp & Volunteer Coordination System
-- MySQL / MariaDB database setup

DROP DATABASE IF EXISTS disaster_relief_db;
CREATE DATABASE disaster_relief_db;
USE disaster_relief_db;

CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (role_name) VALUES
('Admin'), ('Camp Manager'), ('Volunteer'), ('Donor'), ('Affected Person');

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(30),
    password_hash VARCHAR(255) NOT NULL,
    account_status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

CREATE TABLE disaster_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(80) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE camp_locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(120) NOT NULL,
    district VARCHAR(80),
    address TEXT
);

CREATE TABLE relief_camps (
    camp_id INT AUTO_INCREMENT PRIMARY KEY,
    camp_name VARCHAR(120) NOT NULL,
    category_id INT,
    location_id INT,
    manager_id INT,
    capacity INT DEFAULT 0,
    current_population INT DEFAULT 0,
    status ENUM('Active','Closed','Standby') DEFAULT 'Active',
    FOREIGN KEY (category_id) REFERENCES disaster_categories(category_id),
    FOREIGN KEY (location_id) REFERENCES camp_locations(location_id),
    FOREIGN KEY (manager_id) REFERENCES users(user_id)
);

CREATE TABLE affected_families (
    family_id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT,
    head_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30),
    address TEXT,
    total_members INT DEFAULT 1,
    registration_date DATE,
    status ENUM('Registered','Receiving Aid','Released') DEFAULT 'Registered',
    FOREIGN KEY (camp_id) REFERENCES relief_camps(camp_id)
);

CREATE TABLE family_members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    family_id INT NOT NULL,
    member_name VARCHAR(120) NOT NULL,
    age INT,
    gender ENUM('Male','Female','Other'),
    relation_to_head VARCHAR(80),
    health_note TEXT,
    FOREIGN KEY (family_id) REFERENCES affected_families(family_id)
);

CREATE TABLE supply_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    item_category ENUM('Food','Medicine','Shelter','Water','Clothing','Other') NOT NULL,
    unit VARCHAR(30) DEFAULT 'pcs'
);

CREATE TABLE camp_stock (
    stock_id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT DEFAULT 0,
    minimum_required INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES relief_camps(camp_id),
    FOREIGN KEY (item_id) REFERENCES supply_items(item_id)
);

CREATE TABLE stock_alerts (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL,
    item_id INT NOT NULL,
    alert_message VARCHAR(255),
    alert_status ENUM('Open','Resolved') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES relief_camps(camp_id),
    FOREIGN KEY (item_id) REFERENCES supply_items(item_id)
);

CREATE TABLE volunteer_tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT,
    volunteer_id INT,
    assigned_by INT,
    task_title VARCHAR(150) NOT NULL,
    task_description TEXT,
    task_status ENUM('Pending','In Progress','Completed') DEFAULT 'Pending',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (camp_id) REFERENCES relief_camps(camp_id),
    FOREIGN KEY (volunteer_id) REFERENCES users(user_id),
    FOREIGN KEY (assigned_by) REFERENCES users(user_id)
);

CREATE TABLE aid_distribution (
    distribution_id INT AUTO_INCREMENT PRIMARY KEY,
    camp_id INT NOT NULL,
    family_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    distributed_by INT,
    distribution_date DATE NOT NULL,
    note TEXT,
    FOREIGN KEY (camp_id) REFERENCES relief_camps(camp_id),
    FOREIGN KEY (family_id) REFERENCES affected_families(family_id),
    FOREIGN KEY (item_id) REFERENCES supply_items(item_id),
    FOREIGN KEY (distributed_by) REFERENCES users(user_id)
);

CREATE TABLE donations (
    donation_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT,
    donation_type ENUM('Money','Supply') NOT NULL,
    amount DECIMAL(12,2),
    item_id INT,
    quantity INT,
    payment_method VARCHAR(50),
    donation_status ENUM('Pending','Received','Distributed') DEFAULT 'Pending',
    donated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(user_id),
    FOREIGN KEY (item_id) REFERENCES supply_items(item_id)
);

CREATE TABLE donation_usage (
    usage_id INT AUTO_INCREMENT PRIMARY KEY,
    donation_id INT NOT NULL,
    camp_id INT,
    item_id INT,
    quantity_used INT,
    used_for TEXT,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id) REFERENCES donations(donation_id),
    FOREIGN KEY (camp_id) REFERENCES relief_camps(camp_id),
    FOREIGN KEY (item_id) REFERENCES supply_items(item_id)
);

CREATE TABLE help_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    affected_user_id INT,
    family_id INT,
    need_type ENUM('Food','Medicine','Shelter','Rescue','Other') NOT NULL,
    urgency ENUM('Normal','Urgent','Critical') DEFAULT 'Normal',
    details TEXT,
    request_status ENUM('Submitted','Approved','In Progress','Resolved','Rejected') DEFAULT 'Submitted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (affected_user_id) REFERENCES users(user_id),
    FOREIGN KEY (family_id) REFERENCES affected_families(family_id)
);

CREATE TABLE announcements (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    message TEXT NOT NULL,
    audience ENUM('All','Admins','Camp Managers','Volunteers','Donors','Affected People') DEFAULT 'All',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

CREATE TABLE chat_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);

CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    report_type ENUM('Camp Summary','Aid Distribution','Donation Receipt','Emergency Dashboard') NOT NULL,
    generated_by INT,
    camp_id INT,
    file_path VARCHAR(255),
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(user_id),
    FOREIGN KEY (camp_id) REFERENCES relief_camps(camp_id)
);

-- View for stock shortage dashboard
CREATE VIEW v_stock_shortage AS
SELECT 
    c.camp_name,
    s.item_name,
    cs.quantity,
    cs.minimum_required,
    CASE
        WHEN cs.quantity < cs.minimum_required THEN 'Shortage'
        ELSE 'OK'
    END AS stock_status
FROM camp_stock cs
JOIN relief_camps c ON cs.camp_id = c.camp_id
JOIN supply_items s ON cs.item_id = s.item_id;

-- Seed lookup data
INSERT INTO disaster_categories (category_name, description) VALUES
('Flood', 'Flood affected area support'),
('Cyclone', 'Cyclone affected area support'),
('Fire', 'Fire incident support');

INSERT INTO supply_items (item_name, item_category, unit) VALUES
('Rice', 'Food', 'kg'),
('Oral Saline', 'Medicine', 'packet'),
('Blanket', 'Shelter', 'pcs'),
('Drinking Water', 'Water', 'litre');

-- Seed Camp Locations
INSERT INTO camp_locations (location_name, district, address) VALUES
('Sylhet Sadar High School', 'Sylhet', 'Sylhet Sadar, Sylhet, Bangladesh'),
('Cox\'s Bazar Primary School', 'Cox\'s Bazar', 'Kutupalong Camp, Cox\'s Bazar, Bangladesh'),
('Bandarban Govt College', 'Bandarban', 'Bandarban Sadar, Bandarban, Bangladesh');

-- Seed Users
-- 1. Admin (admin@relief.org / admin123)
-- 2. Camp Manager (manager@relief.org / manager123)
-- 3. Volunteer (volunteer@relief.org / volunteer123)
-- 4. Donor (donor@relief.org / donor123)
INSERT INTO users (role_id, full_name, email, phone, password_hash, account_status) VALUES
(1, 'DRNS Admin', 'admin@relief.org', '+8801700000001', '$2y$10$e37a9CLOFMSIfXSrqX9RM.thg2vGrOlW5cpxcFsSVF/L8aV18ossK', 'Approved'),
(2, 'Sifat Manager', 'manager@relief.org', '+8801700000002', '$2y$10$nlSf/x6OAxpEjAJ7zz1LWehbbJy4ykXOrLOjdklKk1x0qLHU5qLrq', 'Approved'),
(3, 'Rahim Volunteer', 'volunteer@relief.org', '+8801700000003', '$2y$10$4KIxv794EsbXD8UharK5OOP1Dy9ojf8t5fHJ609DshAfvj9zg71e2', 'Approved'),
(4, 'Karim Donor', 'donor@relief.org', '+8801700000004', '$2y$10$ZvSPeQ98biNem77gwK6ihOghuWnOr3vBsnjXj8VordADfPYKqMLWq', 'Approved');

-- Seed Relief Camps
INSERT INTO relief_camps (camp_name, category_id, location_id, manager_id, capacity, current_population, status) VALUES
('Sylhet Sadar Relief Camp', 1, 1, 2, 500, 350, 'Active'),
('Cox\'s Bazar Shelter', 2, 2, 2, 800, 120, 'Active'),
('Bandarban Landslide Camp', 3, 3, 2, 300, 0, 'Standby');

-- Seed Affected Families
INSERT INTO affected_families (camp_id, head_name, phone, address, total_members, registration_date, status) VALUES
(1, 'Karim Uddin', '+8801811111111', 'Block-A, Sylhet High School', 5, '2026-05-25', 'Receiving Aid'),
(1, 'Rahima Begum', '+8801822222222', 'Block-B, Sylhet High School', 4, '2026-05-26', 'Registered'),
(2, 'Abul Kashem', '+8801833333333', 'Room 102, Kutupalong School', 6, '2026-05-28', 'Registered');

-- Seed Camp Stock
INSERT INTO camp_stock (camp_id, item_id, quantity, minimum_required) VALUES
(1, 1, 1200, 500), -- Rice: 1200kg (Min: 500kg)
(1, 2, 80, 200),   -- Oral Saline: 80 packets (Min: 200 packets) -> Shortage!
(1, 3, 350, 100),  -- Blanket: 350 pcs (Min: 100 pcs)
(1, 4, 1500, 1000);-- Water: 1500 litres (Min: 1000 litres)

-- Seed Stock Alerts
INSERT INTO stock_alerts (camp_id, item_id, alert_message, alert_status) VALUES
(1, 2, 'Oral Saline level is extremely low! Current stock: 80 packets.', 'Open');

-- Seed Volunteer Tasks
INSERT INTO volunteer_tasks (camp_id, volunteer_id, assigned_by, task_title, task_description, task_status, due_date) VALUES
(1, 3, 2, 'Distribute Dry Food packets', 'Distribute dry food items to residents of Block-A.', 'Pending', '2026-06-02'),
(1, 3, 2, 'Conduct Medical Checkup Assist', 'Assist the medical team in distributing oral saline and medicines in Block-B.', 'In Progress', '2026-06-03'),
(1, 3, 2, 'Inventory Count', 'Count the stock of blankets and reporting details to manager.', 'Completed', '2026-05-30');

-- Seed Help Requests
INSERT INTO help_requests (affected_user_id, family_id, need_type, urgency, details, request_status) VALUES
(3, 1, 'Medicine', 'Critical', 'Location: Block-B, Room 3\nDescription: An elderly patient needs immediate oral saline and first aid kits.', 'In Progress');
