-- =====================================================
-- Event Registration Module
-- Database Schema & Sample Data
-- =====================================================

-- -------------------------------
-- Table: event_config
-- Stores admin-created events
-- -------------------------------
CREATE TABLE event_config (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_name VARCHAR(255) NOT NULL,
  event_category VARCHAR(100) NOT NULL,
  event_date DATE NOT NULL,
  registration_start_date DATE NOT NULL,
  registration_end_date DATE NOT NULL,
  created INT NOT NULL
);

-- Indexes for faster filtering
CREATE INDEX idx_event_category ON event_config(event_category);
CREATE INDEX idx_event_date ON event_config(event_date);

-- -------------------------------
-- Table: event_registration
-- Stores user registrations
-- -------------------------------
CREATE TABLE event_registration (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  college_name VARCHAR(255) NOT NULL,
  department VARCHAR(255) NOT NULL,
  event_id INT NOT NULL,
  created INT NOT NULL,
  INDEX idx_email (email),
  INDEX idx_event_id (event_id)
);

-- -------------------------------
-- Sample Data (For Testing)
-- -------------------------------

INSERT INTO event_config 
(event_name, event_category, event_date, registration_start_date, registration_end_date, created)
VALUES
('Drupal Workshop 2026', 'online_workshop', '2026-02-15', '2026-01-01', '2026-02-14', 1738070400),
('Hackathon Event', 'hackathon', '2026-03-20', '2026-02-01', '2026-03-15', 1738070400);

INSERT INTO event_registration
(full_name, email, college_name, department, event_id, created)
VALUES
('John Doe', 'john@example.com', 'Example University', 'Computer Science', 1, 1738243200);
