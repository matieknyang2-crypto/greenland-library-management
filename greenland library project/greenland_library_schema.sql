

-- Create database
CREATE DATABASE IF NOT EXISTS greenland_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE greenland_library;

-- ============================================================================
-- TABLE: users
-- Stores all system users (students, teachers, librarians, admins)
-- ============================================================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    user_type ENUM('student', 'teacher', 'librarian', 'admin') NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    phone_number VARCHAR(20) DEFAULT NULL,
    admission_number VARCHAR(50) UNIQUE DEFAULT NULL, -- For students
    staff_number VARCHAR(50) UNIQUE DEFAULT NULL,     -- For staff
    form_level ENUM('Form 1', 'Form 2', 'Form 3', 'Form 4') DEFAULT NULL,
    stream ENUM('A', 'B', 'C', 'D') DEFAULT NULL,
    address TEXT DEFAULT NULL,
    registration_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_type (user_type),
    INDEX idx_admission (admission_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: categories
-- Book classification categories
-- ============================================================================
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    parent_category_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    INDEX idx_parent (parent_category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: donors
-- Track book donors for accountability and reporting
-- ============================================================================
CREATE TABLE donors (
    donor_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_name VARCHAR(200) NOT NULL,
    donor_type ENUM('individual', 'organization', 'foundation', 'government') DEFAULT 'individual',
    contact_person VARCHAR(150) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    total_books_donated INT DEFAULT 0,
    first_donation_date DATE DEFAULT NULL,
    last_donation_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_donor_name (donor_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: materials (books)
-- Main catalog of all library resources
-- ============================================================================
CREATE TABLE materials (
    material_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(200) NOT NULL,
    publisher VARCHAR(200) DEFAULT NULL,
    publication_year YEAR DEFAULT NULL,
    isbn VARCHAR(20) DEFAULT NULL,
    edition VARCHAR(50) DEFAULT NULL,
    category_id INT NOT NULL,
    subject_area VARCHAR(100) DEFAULT NULL,
    call_number VARCHAR(50) DEFAULT NULL,
    barcode VARCHAR(50) UNIQUE DEFAULT NULL,
    copies_total INT NOT NULL DEFAULT 1,
    copies_available INT NOT NULL DEFAULT 1,
    location VARCHAR(100) DEFAULT NULL, -- Shelf location (e.g., "A-03")
    acquisition_date DATE DEFAULT NULL,
    donor_id INT DEFAULT NULL,
    price DECIMAL(10,2) DEFAULT NULL,
    status ENUM('available', 'partial', 'unavailable', 'damaged', 'lost') DEFAULT 'available',
    description TEXT DEFAULT NULL,
    cover_color VARCHAR(20) DEFAULT NULL, -- For UI display
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT,
    FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE SET NULL,
    INDEX idx_title (title),
    INDEX idx_author (author),
    INDEX idx_isbn (isbn),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_donor (donor_id),
    FULLTEXT idx_search (title, author, subject_area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: transactions
-- Records of all book borrowing/returning activities
-- ============================================================================
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    material_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    issued_by INT NOT NULL, -- Librarian who issued the book
    returned_to INT DEFAULT NULL, -- Librarian who processed return
    renewal_count INT DEFAULT 0,
    status ENUM('active', 'returned', 'overdue', 'lost') DEFAULT 'active',
    book_condition ENUM('good', 'fair', 'damaged', 'lost') DEFAULT 'good',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(material_id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (returned_to) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_user (user_id),
    INDEX idx_material (material_id),
    INDEX idx_status (status),
    INDEX idx_dates (issue_date, due_date, return_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: reservations
-- Queue for books that are currently borrowed
-- ============================================================================
CREATE TABLE reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    material_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    notification_sent BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'ready', 'fulfilled', 'cancelled', 'expired') DEFAULT 'active',
    expiry_date DATE DEFAULT NULL,
    fulfilled_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(material_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_material (material_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: fines
-- Monetary penalties for overdue books
-- ============================================================================
CREATE TABLE fines (
    fine_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    user_id INT NOT NULL,
    fine_amount DECIMAL(10,2) NOT NULL,
    fine_date DATE NOT NULL,
    days_overdue INT NOT NULL,
    payment_date DATE DEFAULT NULL,
    payment_mode ENUM('cash', 'mobile_money', 'bank_transfer', 'waived') DEFAULT NULL,
    status ENUM('pending', 'partial', 'paid', 'waived') DEFAULT 'pending',
    amount_paid DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT DEFAULT NULL,
    processed_by INT DEFAULT NULL, -- Staff who processed payment
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: system_settings
-- Library configuration and rules
-- ============================================================================
CREATE TABLE system_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value VARCHAR(500) NOT NULL,
    setting_type ENUM('text', 'number', 'boolean', 'date') DEFAULT 'text',
    description TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE: activity_log
-- Audit trail of all system activities
-- ============================================================================
CREATE TABLE activity_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    activity_type ENUM('login', 'logout', 'book_issue', 'book_return', 'book_add', 'book_edit', 
                       'user_add', 'user_edit', 'fine_payment', 'reservation', 'settings_change') NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_activity (activity_type),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INSERT SAMPLE DATA
-- ============================================================================

-- Insert default categories
INSERT INTO categories (category_name, description) VALUES
('Sciences', 'Biology, Chemistry, Physics, and General Science'),
('Mathematics', 'Pure Mathematics, Applied Mathematics, and Statistics'),
('Literature', 'Novels, Poetry, Drama, and Literary Analysis'),
('History', 'World History, African History, and South Sudan History'),
('English Language', 'Grammar, Composition, Comprehension, and Communication'),
('Geography', 'Physical and Human Geography'),
('Religious Studies', 'Christianity, Islam, and Comparative Religion'),
('Computer Science', 'Programming, IT, and Digital Literacy'),
('Business Studies', 'Commerce, Accounting, and Economics'),
('Languages', 'Arabic, French, and other foreign languages'),
('Reference', 'Dictionaries, Encyclopedias, and Atlases'),
('General', 'Other educational materials');

-- Insert default donors
INSERT INTO donors (donor_name, donor_type, total_books_donated, first_donation_date, last_donation_date) VALUES
('Greenland Foundation', 'foundation', 48, '2024-01-15', '2026-01-10'),
('Local Church Community', 'organization', 22, '2024-06-20', '2025-08-15'),
('UNICEF South Sudan', 'organization', 35, '2024-09-10', '2025-12-05'),
('Parent-Teacher Association', 'organization', 12, '2025-03-22', '2025-11-30');

-- Insert default admin user
INSERT INTO users (first_name, last_name, user_type, staff_number, registration_date, status) VALUES
('Head', 'Librarian', 'admin', 'LIB-001', '2024-01-01', 'active'),
('Jane', 'Kiden', 'librarian', 'LIB-002', '2024-02-01', 'active'),
('Fredrick', 'Ogore', 'teacher', 'TCH-001', '2024-01-01', 'active');

-- Insert sample students
INSERT INTO users (first_name, last_name, user_type, admission_number, form_level, stream, registration_date, status) VALUES
('John', 'Deng', 'student', 'S-1042', 'Form 3', 'A', '2024-01-15', 'active'),
('Grace', 'Akol', 'student', 'S-0987', 'Form 4', 'B', '2023-09-01', 'active'),
('Peter', 'Lual', 'student', 'S-0731', 'Form 2', 'C', '2025-01-10', 'active'),
('Mary', 'Lado', 'student', 'S-0456', 'Form 4', 'A', '2022-09-01', 'active'),
('Samuel', 'Wani', 'student', 'S-0331', 'Form 1', 'D', '2026-01-12', 'active'),
('Anna', 'Amara', 'student', 'S-0888', 'Form 3', 'B', '2024-01-15', 'active'),
('Thomas', 'Ayik', 'student', 'S-1120', 'Form 2', 'A', '2025-01-10', 'active'),
('Rose', 'Dak', 'student', 'S-1055', 'Form 3', 'C', '2024-01-15', 'active'),
('James', 'Bol', 'student', 'S-0621', 'Form 4', 'D', '2023-09-01', 'active');

-- Insert sample books
INSERT INTO materials (title, author, publisher, publication_year, isbn, category_id, subject_area, 
                      call_number, copies_total, copies_available, location, donor_id, status, cover_color) VALUES
('Things Fall Apart', 'Chinua Achebe', 'Heinemann', 1958, '978-0-435-90536-4', 3, 'African Literature', 
 'LIT-A-03', 5, 2, 'Literature Section - Shelf A3', 1, 'partial', '#1a3a2a'),
('Biology Form 3', 'Kenya Ministry of Education', 'KLB', 2020, '978-9966-025-11-3', 1, 'Biology', 
 'SCI-B-12', 8, 5, 'Science Section - Shelf B12', NULL, 'partial', '#1a3a50'),
('Mathematics Form 2', 'Various Authors', 'Oxford University Press', 2019, '978-0-19-863855-0', 2, 'Mathematics', 
 'MAT-C-05', 12, 9, 'Mathematics Section - Shelf C5', NULL, 'available', '#7c2d12'),
('Chemistry Practical', 'J. B. Owolabi', 'Longman Nigeria', 2018, '978-978-8113-58-6', 1, 'Chemistry', 
 'SCI-B-08', 6, 0, 'Science Section - Shelf B8', NULL, 'unavailable', '#4a1d96'),
('History of South Sudan', 'James Lual', 'Local Publisher', 2021, '978-0-000-00123-4', 4, 'History', 
 'HIS-D-02', 4, 3, 'History Section - Shelf D2', NULL, 'available', '#065f46'),
('English Comprehension', 'R. G. Ward', 'Pearson', 2017, '978-0-582-21000-2', 5, 'English', 
 'ENG-E-07', 10, 7, 'English Section - Shelf E7', NULL, 'available', '#78350f'),
('Physics Form 4', 'Peter Kariuki', 'Longhorn Publishers', 2019, '978-9966-36-085-5', 1, 'Physics', 
 'SCI-B-15', 7, 4, 'Science Section - Shelf B15', 2, 'partial', '#1e3a8a'),
('Mathematics Form 3', 'David Njoroge', 'Moran Publishers', 2020, '978-9966-56-201-8', 2, 'Advanced Math', 
 'MAT-C-08', 10, 3, 'Mathematics Section - Shelf C8', NULL, 'partial', '#92400e'),
('English Grammar', 'Martin Hewings', 'Cambridge', 2015, '978-0-521-53290-0', 5, 'Grammar', 
 'ENG-E-03', 8, 8, 'English Section - Shelf E3', 3, 'available', '#115e59'),
('Geography Form 2', 'Samuel Otieno', 'Oxford East Africa', 2018, '978-0-19-854682-4', 6, 'Geography', 
 'GEO-F-01', 6, 6, 'Geography Section - Shelf F1', NULL, 'available', '#4c1d95');

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('library_name', 'Greenland Secondary School Library', 'text', 'Official library name'),
('max_borrow_period_days', '14', 'number', 'Maximum borrowing period in days'),
('max_books_per_student', '3', 'number', 'Maximum books a student can borrow'),
('fine_per_day_ssp', '50', 'number', 'Fine amount per day in South Sudanese Pounds'),
('reservation_hold_days', '3', 'number', 'Days to hold reserved book before expiry'),
('allow_renewals', 'true', 'boolean', 'Allow students to renew books'),
('max_renewals', '2', 'number', 'Maximum renewal times per book'),
('library_email', 'library@greenlandsecondary.edu.ss', 'text', 'Library contact email'),
('library_phone', '+211 123 456 789', 'text', 'Library contact phone');

-- Insert some sample transactions
INSERT INTO transactions (user_id, material_id, issue_date, due_date, issued_by, status) VALUES
(4, 1, '2026-02-07', '2026-02-21', 1, 'overdue'), -- John Deng borrowed Things Fall Apart (overdue)
(5, 3, '2026-02-10', '2026-02-24', 1, 'active'),  -- Grace Akol borrowed Mathematics Form 2
(6, 6, '2026-02-12', '2026-03-03', 1, 'active'),  -- Peter Lual borrowed English Comprehension
(8, 4, '2026-02-15', '2026-03-01', 1, 'active'),  -- Samuel Wani borrowed Chemistry Practical
(9, 5, '2026-02-01', '2026-02-15', 1, 'overdue'); -- Anna Amara borrowed History (overdue)

-- Insert some fines for overdue books
INSERT INTO fines (transaction_id, user_id, fine_amount, fine_date, days_overdue, status) VALUES
(1, 4, 150.00, '2026-02-18', 3, 'pending'),  -- John Deng: 3 days × 50 SSP
(5, 9, 100.00, '2026-02-18', 2, 'pending');  -- Anna Amara: 2 days × 50 SSP

-- Insert sample reservations
INSERT INTO reservations (user_id, material_id, reservation_date, status) VALUES
(5, 4, '2026-02-14', 'ready'),    -- Grace Akol reserved Chemistry (now ready)
(10, 1, '2026-02-16', 'active'),  -- Thomas Ayik reserved Things Fall Apart
(11, 2, '2026-02-15', 'ready');   -- Rose Dak reserved Biology Form 3

-- ============================================================================
-- CREATE VIEWS FOR REPORTING
-- ============================================================================

-- View: Current active borrows
CREATE OR REPLACE VIEW vw_active_borrows AS
SELECT 
    t.transaction_id,
    u.user_id,
    CONCAT(u.first_name, ' ', u.last_name) AS student_name,
    u.admission_number,
    u.form_level,
    m.material_id,
    m.title AS book_title,
    m.author,
    t.issue_date,
    t.due_date,
    DATEDIFF(CURDATE(), t.due_date) AS days_overdue,
    CASE 
        WHEN CURDATE() > t.due_date THEN 'overdue'
        WHEN DATEDIFF(t.due_date, CURDATE()) <= 3 THEN 'due_soon'
        ELSE 'on_time'
    END AS borrow_status
FROM transactions t
JOIN users u ON t.user_id = u.user_id
JOIN materials m ON t.material_id = m.material_id
WHERE t.status = 'active' OR t.status = 'overdue';

-- View: Student borrowing statistics
CREATE OR REPLACE VIEW vw_student_stats AS
SELECT 
    u.user_id,
    CONCAT(u.first_name, ' ', u.last_name) AS student_name,
    u.admission_number,
    u.form_level,
    COUNT(DISTINCT t.transaction_id) AS total_borrows,
    COUNT(DISTINCT CASE WHEN t.status = 'active' THEN t.transaction_id END) AS current_borrows,
    COUNT(DISTINCT CASE WHEN t.status = 'overdue' THEN t.transaction_id END) AS overdue_count,
    COALESCE(SUM(f.fine_amount - f.amount_paid), 0) AS outstanding_fines
FROM users u
LEFT JOIN transactions t ON u.user_id = t.user_id
LEFT JOIN fines f ON u.user_id = f.user_id AND f.status IN ('pending', 'partial')
WHERE u.user_type = 'student'
GROUP BY u.user_id, u.first_name, u.last_name, u.admission_number, u.form_level;

-- View: Most borrowed books
CREATE OR REPLACE VIEW vw_popular_books AS
SELECT 
    m.material_id,
    m.title,
    m.author,
    c.category_name,
    COUNT(t.transaction_id) AS times_borrowed,
    m.copies_total,
    m.copies_available
FROM materials m
JOIN categories c ON m.category_id = c.category_id
LEFT JOIN transactions t ON m.material_id = t.material_id
GROUP BY m.material_id, m.title, m.author, c.category_name, m.copies_total, m.copies_available
ORDER BY times_borrowed DESC;

-- View: Donor impact report
CREATE OR REPLACE VIEW vw_donor_impact AS
SELECT 
    d.donor_id,
    d.donor_name,
    d.donor_type,
    COUNT(DISTINCT m.material_id) AS books_donated,
    COUNT(t.transaction_id) AS total_borrows,
    ROUND(COUNT(t.transaction_id) / COUNT(DISTINCT m.material_id), 1) AS avg_borrows_per_book
FROM donors d
LEFT JOIN materials m ON d.donor_id = m.donor_id
LEFT JOIN transactions t ON m.material_id = t.material_id
GROUP BY d.donor_id, d.donor_name, d.donor_type
ORDER BY books_donated DESC;

-- ============================================================================
-- CREATE STORED PROCEDURES
-- ============================================================================

DELIMITER //

-- Procedure: Issue a book to a student
CREATE PROCEDURE sp_issue_book(
    IN p_user_id INT,
    IN p_material_id INT,
    IN p_librarian_id INT,
    OUT p_result VARCHAR(200)
)
BEGIN
    DECLARE v_available INT;
    DECLARE v_current_borrows INT;
    DECLARE v_max_books INT;
    DECLARE v_borrow_days INT;
    DECLARE v_has_fines BOOLEAN;
    
    -- Check if material is available
    SELECT copies_available INTO v_available FROM materials WHERE material_id = p_material_id;
    
    IF v_available <= 0 THEN
        SET p_result = 'ERROR: Book not available';
    ELSE
        -- Check student's current borrows
        SELECT COUNT(*) INTO v_current_borrows 
        FROM transactions 
        WHERE user_id = p_user_id AND status = 'active';
        
        -- Get max books allowed
        SELECT setting_value INTO v_max_books 
        FROM system_settings 
        WHERE setting_key = 'max_books_per_student';
        
        IF v_current_borrows >= v_max_books THEN
            SET p_result = 'ERROR: Student has reached maximum borrow limit';
        ELSE
            -- Check for outstanding fines
            SELECT EXISTS(
                SELECT 1 FROM fines 
                WHERE user_id = p_user_id AND status IN ('pending', 'partial')
            ) INTO v_has_fines;
            
            IF v_has_fines THEN
                SET p_result = 'WARNING: Student has outstanding fines but book issued';
            END IF;
            
            -- Get borrow period
            SELECT setting_value INTO v_borrow_days 
            FROM system_settings 
            WHERE setting_key = 'max_borrow_period_days';
            
            -- Create transaction
            INSERT INTO transactions (user_id, material_id, issue_date, due_date, issued_by, status)
            VALUES (p_user_id, p_material_id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL v_borrow_days DAY), 
                    p_librarian_id, 'active');
            
            -- Update material availability
            UPDATE materials 
            SET copies_available = copies_available - 1,
                status = CASE 
                    WHEN copies_available - 1 = 0 THEN 'unavailable'
                    WHEN copies_available - 1 < copies_total THEN 'partial'
                    ELSE 'available'
                END
            WHERE material_id = p_material_id;
            
            SET p_result = 'SUCCESS: Book issued successfully';
        END IF;
    END IF;
END //

-- Procedure: Return a book
CREATE PROCEDURE sp_return_book(
    IN p_transaction_id INT,
    IN p_librarian_id INT,
    IN p_condition ENUM('good', 'fair', 'damaged', 'lost'),
    OUT p_result VARCHAR(200)
)
BEGIN
    DECLARE v_material_id INT;
    DECLARE v_user_id INT;
    DECLARE v_due_date DATE;
    DECLARE v_days_overdue INT;
    DECLARE v_fine_amount DECIMAL(10,2);
    DECLARE v_fine_per_day DECIMAL(10,2);
    
    -- Get transaction details
    SELECT material_id, user_id, due_date 
    INTO v_material_id, v_user_id, v_due_date
    FROM transactions 
    WHERE transaction_id = p_transaction_id;
    
    -- Calculate overdue days
    SET v_days_overdue = GREATEST(0, DATEDIFF(CURDATE(), v_due_date));
    
    -- Update transaction
    UPDATE transactions 
    SET return_date = CURDATE(),
        returned_to = p_librarian_id,
        status = 'returned',
        book_condition = p_condition
    WHERE transaction_id = p_transaction_id;
    
    -- Update material availability
    UPDATE materials 
    SET copies_available = copies_available + 1,
        status = CASE 
            WHEN copies_available + 1 = copies_total THEN 'available'
            ELSE 'partial'
        END
    WHERE material_id = v_material_id;
    
    -- Create fine if overdue
    IF v_days_overdue > 0 THEN
        SELECT setting_value INTO v_fine_per_day 
        FROM system_settings 
        WHERE setting_key = 'fine_per_day_ssp';
        
        SET v_fine_amount = v_days_overdue * v_fine_per_day;
        
        INSERT INTO fines (transaction_id, user_id, fine_amount, fine_date, days_overdue, status)
        VALUES (p_transaction_id, v_user_id, v_fine_amount, CURDATE(), v_days_overdue, 'pending');
        
        SET p_result = CONCAT('SUCCESS: Book returned. Fine: SSP ', v_fine_amount);
    ELSE
        SET p_result = 'SUCCESS: Book returned on time';
    END IF;
    
    -- Check for reservations
    UPDATE reservations 
    SET status = 'ready', 
        notification_sent = FALSE 
    WHERE material_id = v_material_id 
    AND status = 'active' 
    ORDER BY reservation_date 
    LIMIT 1;
END //

DELIMITER ;

-- ============================================================================
-- CREATE TRIGGERS
-- ============================================================================

DELIMITER //

-- Trigger: Update donor statistics when book is added
CREATE TRIGGER trg_after_material_insert
AFTER INSERT ON materials
FOR EACH ROW
BEGIN
    IF NEW.donor_id IS NOT NULL THEN
        UPDATE donors 
        SET total_books_donated = total_books_donated + 1,
            last_donation_date = NEW.acquisition_date
        WHERE donor_id = NEW.donor_id;
    END IF;
END //

-- Trigger: Automatically check for overdue books daily
CREATE EVENT evt_check_overdue
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    UPDATE transactions 
    SET status = 'overdue' 
    WHERE status = 'active' 
    AND due_date < CURDATE();
END //

DELIMITER ;

-- ============================================================================
-- GRANT PERMISSIONS (Run as root or admin user)
-- ============================================================================
-- CREATE USER 'greenland_user'@'localhost' IDENTIFIED BY 'secure_password_here';
-- GRANT ALL PRIVILEGES ON greenland_library.* TO 'greenland_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
