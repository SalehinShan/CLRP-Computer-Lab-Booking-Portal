-- Computer Laboratory Resource Portal (CLRP) Database Schema

CREATE DATABASE IF NOT EXISTS clrp_db;
USE clrp_db;

-- 1. Department Table
CREATE TABLE IF NOT EXISTS Department (
    dept_id VARCHAR(10) PRIMARY KEY,
    dept_name VARCHAR(100) NOT NULL
);

-- 2. Student Table
CREATE TABLE IF NOT EXISTS Student (
    student_id VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    dept_id VARCHAR(10),
    FOREIGN KEY (dept_id) REFERENCES Department(dept_id)
        ON UPDATE CASCADE ON DELETE SET NULL
);

-- 3. Admin Table
CREATE TABLE IF NOT EXISTS Admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- 4. Technician Table
CREATE TABLE IF NOT EXISTS Technician (
    technician_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    specialization VARCHAR(50)
);

-- 5. Lab Table
CREATE TABLE IF NOT EXISTS Lab (
    lab_id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) NOT NULL,
    capacity INT NOT NULL
);

-- 6. Computer Table
CREATE TABLE IF NOT EXISTS Computer (
    computer_id INT AUTO_INCREMENT PRIMARY KEY,
    pc_label VARCHAR(20) NOT NULL,
    ip_address VARCHAR(45),
    status VARCHAR(20) NOT NULL DEFAULT 'Available',
    lab_id INT,
    FOREIGN KEY (lab_id) REFERENCES Lab(lab_id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- 7. Software Table
CREATE TABLE IF NOT EXISTS Software (
    software_id INT AUTO_INCREMENT PRIMARY KEY,
    software_name VARCHAR(100) NOT NULL,
    version VARCHAR(20),
    license_type VARCHAR(50)
);

-- 8. Computer_Software Table
CREATE TABLE IF NOT EXISTS Computer_Software (
    computer_id INT NOT NULL,
    software_id INT NOT NULL,
    installation_date DATE,
    PRIMARY KEY (computer_id, software_id),
    FOREIGN KEY (computer_id) REFERENCES Computer(computer_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (software_id) REFERENCES Software(software_id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- 9. Reservation Table
CREATE TABLE IF NOT EXISTS Reservation (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    computer_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    FOREIGN KEY (student_id) REFERENCES Student(student_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (computer_id) REFERENCES Computer(computer_id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- 10. Maintenance Table
CREATE TABLE IF NOT EXISTS Maintenance (
    maintenance_id INT AUTO_INCREMENT PRIMARY KEY,
    computer_id INT NOT NULL,
    student_id VARCHAR(20) NULL,
    technician_id INT NULL,
    issue_description TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (computer_id) REFERENCES Computer(computer_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES Student(student_id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (technician_id) REFERENCES Technician(technician_id)
        ON UPDATE CASCADE ON DELETE SET NULL
);

-- ========================================================
-- SAMPLE DATA INSERTION
-- (Inserted in correct order to satisfy Foreign Keys)
-- ========================================================

-- 1. Department Data (5 departments)
INSERT INTO Department (dept_id, dept_name) VALUES
('CSE', 'Computer Science and Engineering'),
('EEE', 'Electrical and Electronic Engineering'),
('BBA', 'School of Business and Economics'),
('Civil', 'Civil and Environmental Engineering'),
('English', 'English and Modern Languages'),
('BBT', 'Biotechnology');


-- 2. Admin Data (2 admins)
INSERT INTO Admin (admin_id, name, email, password) VALUES
(1, 'System Administrator', 'admin.sys@northsouth.edu', 'password123'),
(2, 'Lab Manager Admin', 'admin.lab@northsouth.edu', 'password123');

-- 3. Technician Data (4 technicians)
INSERT INTO Technician (technician_id, name, email, password, specialization) VALUES
(1, 'Kamrul Hasan', 'kamrul.hasan@northsouth.edu', 'password123', 'Hardware'),
(2, 'Tariqul Islam', 'tariqul.islam@northsouth.edu', 'password123', 'Networking'),
(3, 'Biplob Hossain', 'biplob.hossain@northsouth.edu', 'password123', 'Software'),
(4, 'Mostafa Kamal', 'mostafa.kamal@northsouth.edu', 'password123', 'Operating Systems');

-- 4. Student Data (20 students)
INSERT INTO Student (student_id, name, email, password, dept_id) VALUES
('2111001042', 'Md. Abu Salehin Shan', 'abu.shan.241@northsouth.edu', 'password123', 'CSE'),
('2111002042', 'Md. Sufian Alam Safin', 'sufin.safin.241@northsouth.edu', 'password123', 'CSE'),
('2111003042', 'Deep Barua', 'deep.barua@northsouth.edu', 'password123', 'CSE'),
('2411252642', 'Abdullah Talha', 'abdullah.talha@northsouth.edu', 'password123', 'CSE'),
('2411252042', 'Munira Momo', 'munira.momo@northsouth.edu', 'password123', 'CSE'),
('2411252842', 'Mahamudul Tamim', 'mahamudul.tamim@northsouth.edu', 'password123', 'CSE'),
('2111005042', 'Sabbir Hasan', 'sabbir.hasan@northsouth.edu', 'password123', 'EEE'),
('2111006042', 'Fariha Islam', 'fariha.islam@northsouth.edu', 'password123', 'EEE'),
('2111007042', 'Tahmid Khan', 'tahmid.khan@northsouth.edu', 'password123', 'EEE'),
('2111008042', 'Maisha Maliha', 'maisha.maliha@northsouth.edu', 'password123', 'EEE'),
('2111009042', 'Rezwanul Huq', 'rezwanul.huq@northsouth.edu', 'password123', 'BBA'),
('2111010042', 'Samia Akter', 'samia.akter@northsouth.edu', 'password123', 'BBA'),
('2111011042', 'Farhan Ishraq', 'farhan.ishraq@northsouth.edu', 'password123', 'BBA'),
('2111012042', 'Sadia Jahan', 'sadia.jahan@northsouth.edu', 'password123', 'BBA'),
('2111013042', 'Zubayer Ahmed', 'zubayer.ahmed@northsouth.edu', 'password123', 'Civil'),
('2111014042', 'Tasnim Ferdous', 'tasnim.ferdous@northsouth.edu', 'password123', 'Civil'),
('2111015042', 'Adnan Kabir', 'adnan.kabir@northsouth.edu', 'password123', 'Civil'),
('2111016042', 'Humaira Yasmin', 'humaira.yasmin@northsouth.edu', 'password123', 'Civil'),
('2111017042', 'Rayhan Chowdhury', 'rayhan.chowdhury@northsouth.edu', 'password123', 'English'),
('2111018042', 'Nusrat Jahan', 'nusrat.jahan@northsouth.edu', 'password123', 'English'),
('2111019042', 'Asif Mahmud', 'asif.mahmud@northsouth.edu', 'password123', 'English'),
('2111020042', 'Kazi Shahed', 'kazi.shahed@northsouth.edu', 'password123', 'English'),
('2111021042', 'Nazihat Jahan Supto ', 'nazihat.supto@northsouth.edu', 'password123', 'BBT'),
('2111022042', 'Tawsif Alam', 'tawsif.alam@northsouth.edu', 'password123', 'BBT');


-- 5. Lab Data (4 labs)
INSERT INTO Lab (lab_id, room_number, capacity) VALUES
(1, 'Room 601', 35),
(2, 'Room 602', 40),
(3, 'Room 603', 30),
(4, 'Room 604', 36);

-- 6. Computer Data (24 computers, 6 per lab)
INSERT INTO Computer (computer_id, pc_label, ip_address, status, lab_id) VALUES
-- Room 601
(1, 'PC-601-01', '192.168.1.101', 'Available', 1),
(2, 'PC-601-02', '192.168.1.102', 'In Use', 1),
(3, 'PC-601-03', '192.168.1.103', 'Available', 1),
(4, 'PC-601-04', '192.168.1.104', 'Under Maintenance', 1),
(5, 'PC-601-05', '192.168.1.105', 'Available', 1),
(6, 'PC-601-06', '192.168.1.106', 'Reserved', 1),
-- Room 602
(7, 'PC-602-01', '192.168.1.107', 'Available', 2),
(8, 'PC-602-02', '192.168.1.108', 'In Use', 2),
(9, 'PC-602-03', '192.168.1.109', 'Available', 2),
(10, 'PC-602-04', '192.168.1.110', 'Available', 2),
(11, 'PC-602-05', '192.168.1.111', 'Under Maintenance', 2),
(12, 'PC-602-06', '192.168.1.112', 'Reserved', 2),
-- Room 603
(13, 'PC-603-01', '192.168.1.113', 'Available', 3),
(14, 'PC-603-02', '192.168.1.114', 'In Use', 3),
(15, 'PC-603-03', '192.168.1.115', 'Available', 3),
(16, 'PC-603-04', '192.168.1.116', 'Under Maintenance', 3),
(17, 'PC-603-05', '192.168.1.117', 'Available', 3),
(18, 'PC-603-06', '192.168.1.118', 'Available', 3),
-- Room 604
(19, 'PC-604-01', '192.168.1.119', 'Available', 4),
(20, 'PC-604-02', '192.168.1.120', 'In Use', 4),
(21, 'PC-604-03', '192.168.1.121', 'Available', 4),
(22, 'PC-604-04', '192.168.1.122', 'Reserved', 4),
(23, 'PC-604-05', '192.168.1.123', 'Under Maintenance', 4),
(24, 'PC-604-06', '192.168.1.124', 'Available', 4);

-- 7. Software Data (10 records)
INSERT INTO Software (software_id, software_name, version, license_type) VALUES
(1, 'Windows 11', '23H2', 'Proprietary'),
(2, 'Ubuntu', '24.04 LTS', 'Open Source'),
(3, 'Visual Studio Code', '1.91', 'Open Source'),
(4, 'Code::Blocks', '20.03', 'Open Source'),
(5, 'MySQL Workbench', '8.0.36', 'Open Source'),
(6, 'Google Chrome', '126.0', 'Freeware'),
(7, 'Mozilla Firefox', '127.0', 'Open Source'),
(8, 'Python', '3.12.2', 'Open Source'),
(9, 'Java JDK', '21.0.2', 'Proprietary'),
(10, 'Microsoft Office', '2021', 'Volume License'),
(11, 'PyMOL', '2.4', 'Proprietary'),
(12, 'GraphPad Prism', '14', 'Proprietary');

-- 8. Computer_Software Data (151 records: 5-7 packages per computer across 24 computers)
INSERT INTO Computer_Software (computer_id, software_id, installation_date) VALUES
-- Computer 1 (6 software)
(1, 1, '2024-01-10'), (1, 3, '2024-01-10'), (1, 4, '2024-01-11'), (1, 5, '2024-01-12'), (1, 6, '2024-01-10'), (1, 8, '2024-01-12'),
-- Computer 2 (7 software)
(2, 1, '2024-01-10'), (2, 3, '2024-01-10'), (2, 5, '2024-01-11'), (2, 6, '2024-01-10'), (2, 7, '2024-01-10'), (2, 8, '2024-01-12'), (2, 9, '2024-01-15'),
-- Computer 3 (6 software)
(3, 2, '2024-01-15'), (3, 3, '2024-01-15'), (3, 4, '2024-01-16'), (3, 5, '2024-01-16'), (3, 6, '2024-01-15'), (3, 8, '2024-01-17'),
-- Computer 4 (6 software)
(4, 1, '2024-01-18'), (4, 3, '2024-01-18'), (4, 4, '2024-01-19'), (4, 6, '2024-01-18'), (4, 8, '2024-01-20'), (4, 10, '2024-01-20'),
-- Computer 5 (6 software)
(5, 1, '2024-01-22'), (5, 3, '2024-01-22'), (5, 5, '2024-01-23'), (5, 6, '2024-01-22'), (5, 7, '2024-01-22'), (5, 8, '2024-01-24'),
-- Computer 6 (6 software)
(6, 2, '2024-01-25'), (6, 3, '2024-01-25'), (6, 4, '2024-01-26'), (6, 6, '2024-01-25'), (6, 8, '2024-01-26'), (6, 9, '2024-01-27'),
-- Computer 7 (7 software)
(7, 1, '2024-02-01'), (7, 3, '2024-02-01'), (7, 4, '2024-02-02'), (7, 5, '2024-02-02'), (7, 6, '2024-02-01'), (7, 8, '2024-02-03'), (7, 10, '2024-02-03'),
-- Computer 8 (6 software)
(8, 1, '2024-02-05'), (8, 3, '2024-02-05'), (8, 5, '2024-02-06'), (8, 6, '2024-02-05'), (8, 7, '2024-02-05'), (8, 9, '2024-02-07'),
-- Computer 9 (6 software)
(9, 2, '2024-02-10'), (9, 3, '2024-02-10'), (9, 4, '2024-02-11'), (9, 5, '2024-02-11'), (9, 6, '2024-02-10'), (9, 8, '2024-02-12'),
-- Computer 10 (6 software)
(10, 1, '2024-02-15'), (10, 3, '2024-02-15'), (10, 4, '2024-02-16'), (10, 6, '2024-02-15'), (10, 8, '2024-02-17'), (10, 10, '2024-02-17'),
-- Computer 11 (6 software)
(11, 1, '2024-02-20'), (11, 3, '2024-02-20'), (11, 5, '2024-02-21'), (11, 6, '2024-02-20'), (11, 7, '2024-02-20'), (11, 8, '2024-02-22'),
-- Computer 12 (7 software)
(12, 2, '2024-02-25'), (12, 3, '2024-02-25'), (12, 4, '2024-02-26'), (12, 6, '2024-02-25'), (12, 8, '2024-02-26'), (12, 9, '2024-02-27'), (12, 10, '2024-02-28'),
-- Computer 13 (6 software)
(13, 1, '2024-03-01'), (13, 3, '2024-03-01'), (13, 4, '2024-03-02'), (13, 5, '2024-03-02'), (13, 6, '2024-03-01'), (13, 8, '2024-03-03'),
-- Computer 14 (7 software)
(14, 1, '2024-03-05'), (14, 3, '2024-03-05'), (14, 5, '2024-03-06'), (14, 6, '2024-03-05'), (14, 7, '2024-03-05'), (14, 8, '2024-03-07'), (14, 9, '2024-03-08'),
-- Computer 15 (6 software)
(15, 2, '2024-03-10'), (15, 3, '2024-03-10'), (15, 4, '2024-03-11'), (15, 5, '2024-03-11'), (15, 6, '2024-03-10'), (15, 8, '2024-03-12'),
-- Computer 16 (6 software)
(16, 1, '2024-03-15'), (16, 3, '2024-03-15'), (16, 4, '2024-03-16'), (16, 6, '2024-03-15'), (16, 8, '2024-03-17'), (16, 10, '2024-03-17'),
-- Computer 17 (6 software)
(17, 1, '2024-03-20'), (17, 3, '2024-03-20'), (17, 5, '2024-03-21'), (17, 6, '2024-03-20'), (17, 7, '2024-03-20'), (17, 8, '2024-03-22'),
-- Computer 18 (6 software)
(18, 2, '2024-03-25'), (18, 3, '2024-03-25'), (18, 4, '2024-03-26'), (18, 6, '2024-03-25'), (18, 8, '2024-03-26'), (18, 9, '2024-03-27'),
-- Computer 19 (7 software)
(19, 1, '2024-04-01'), (19, 3, '2024-04-01'), (19, 4, '2024-04-02'), (19, 5, '2024-04-02'), (19, 6, '2024-04-01'), (19, 8, '2024-04-03'), (19, 10, '2024-04-03'),
-- Computer 20 (6 software)
(20, 1, '2024-04-05'), (20, 3, '2024-04-05'), (20, 5, '2024-04-06'), (20, 6, '2024-04-05'), (20, 7, '2024-04-05'), (20, 9, '2024-04-07'),
-- Computer 21 (6 software)
(21, 2, '2024-04-10'), (21, 3, '2024-04-10'), (21, 4, '2024-04-11'), (21, 5, '2024-04-11'), (21, 6, '2024-04-10'), (21, 8, '2024-04-12'),
-- Computer 22 (6 software)
(22, 1, '2024-04-15'), (22, 3, '2024-04-15'), (22, 4, '2024-04-16'), (22, 6, '2024-04-15'), (22, 8, '2024-04-17'), (22, 10, '2024-04-17'),
-- Computer 23 (6 software)
(23, 1, '2024-04-20'), (23, 3, '2024-04-20'), (23, 5, '2024-04-21'), (23, 6, '2024-04-20'), (23, 7, '2024-04-20'), (23, 8, '2024-04-22'),
-- Computer 24 (7 software)
(24, 2, '2024-04-25'), (24, 3, '2024-04-25'), (24, 4, '2024-04-26'), (24, 5, '2024-04-26'), (24, 6, '2024-04-25'), (24, 8, '2024-04-27'), (24, 9, '2024-04-28'),
-- BBT Software installations (3 records)
(23, 11, '2024-05-01'), (24, 11, '2024-05-01'), (24, 12, '2024-05-02');

-- 9. Reservation Data (25 reservations)
INSERT INTO Reservation (reservation_id, student_id, computer_id, reservation_date, time_slot, status) VALUES
(1, '2111001042', 1, '2024-07-01', '08:00 - 09:30', 'Approved'),
(2, '2111002042', 2, '2024-07-01', '09:40 - 11:10', 'Completed'),
(3, '2111003042', 3, '2024-07-02', '11:20 - 12:50', 'Pending'),
(5, '2111005042', 5, '2024-07-03', '02:40 - 04:10', 'Cancelled'),
(6, '2111006042', 6, '2024-07-03', '08:00 - 09:30', 'Completed'),
(7, '2111007042', 7, '2024-07-04', '09:40 - 11:10', 'Approved'),
(8, '2111008042', 8, '2024-07-04', '11:20 - 12:50', 'Pending'),
(9, '2111009042', 9, '2024-07-05', '01:00 - 02:30', 'Completed'),
(10, '2111010042', 10, '2024-07-05', '02:40 - 04:10', 'Approved'),
(11, '2111011042', 11, '2024-07-08', '08:00 - 09:30', 'Pending'),
(12, '2111012042', 12, '2024-07-08', '09:40 - 11:10', 'Cancelled'),
(13, '2111013042', 13, '2024-07-09', '11:20 - 12:50', 'Approved'),
(14, '2111014042', 14, '2024-07-09', '01:00 - 02:30', 'Completed'),
(15, '2111015042', 15, '2024-07-10', '02:40 - 04:10', 'Pending'),
(16, '2111016042', 16, '2024-07-10', '08:00 - 09:30', 'Approved'),
(17, '2111017042', 17, '2024-07-11', '09:40 - 11:10', 'Completed'),
(18, '2111018042', 18, '2024-07-11', '11:20 - 12:50', 'Cancelled'),
(19, '2111019042', 19, '2024-07-12', '01:00 - 02:30', 'Approved'),
(20, '2111020042', 20, '2024-07-12', '02:40 - 04:10', 'Pending'),
(21, '2111001042', 21, '2024-07-15', '08:00 - 09:30', 'Approved'),
(22, '2111002042', 22, '2024-07-15', '09:40 - 11:10', 'Completed'),
(23, '2111003042', 23, '2024-07-16', '11:20 - 12:50', 'Approved'),
(25, '2111005042', 1, '2024-07-17', '02:40 - 04:10', 'Completed');

-- 10. Maintenance Data (15 records)
INSERT INTO Maintenance (maintenance_id, computer_id, student_id, technician_id, issue_description, status, reported_at) VALUES
(2, 8, '2111008042', 1, 'Mouse malfunction', 'Resolved', '2024-06-12 10:30:00'),
(3, 11, '2111011042', 1, 'Monitor flickering', 'Pending', '2024-06-15 14:20:00'),
(4, 16, '2111016042', 4, 'Windows boot failure', 'In Progress', '2024-06-18 11:45:00'),
(5, 2, '2111002042', 2, 'Network cable disconnected', 'Resolved', '2024-06-20 08:50:00'),
(6, 23, '2111019042', 4, 'Blue screen error', 'Pending', '2024-06-22 16:10:00'),
(7, 7, '2111007042', 1, 'SSD failure', 'In Progress', '2024-06-25 13:05:00'),
(8, 12, '2111012042', 2, 'Printer connection issue', 'Resolved', '2024-06-28 15:40:00'),
(9, 14, NULL, 3, 'Overheating issue', 'Resolved', '2024-07-01 10:00:00'),
(10, 18, '2111018042', 1, 'RAM failure', 'Pending', '2024-07-03 11:30:00'),
(11, 20, NULL, 1, 'Audio jack damaged', 'Cancelled', '2024-07-05 09:25:00'),
(12, 22, '2111020042', 3, 'USB port not responding', 'Resolved', '2024-07-08 14:50:00'),
(13, 5, '2111001042', 3, 'PyMOL license validation error', 'Pending', '2024-07-10 11:00:00'),
(14, 9, '2111003042', 2, 'GraphPad Prism crash on startup', 'In Progress', '2024-07-12 14:30:00'),
(15, 15, '2111005042', 4, 'GPU driver crash during 3D modeling', 'Resolved', '2024-07-14 16:20:00');

