-- =========================================================
-- HELPDESK CNTT & CO SO VAT CHAT - TLU CSE485
-- Thuc hien: Long | Level 1 - PHP thuan + MySQLi
-- =========================================================

DROP DATABASE IF EXISTS helpdesk;
CREATE DATABASE helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE helpdesk;

-- ---------------------------------------------------------
-- 1. USERS (phu trach: LONG)
-- role: 'requester' | 'staff' | 'manager'
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('requester','staff','manager') NOT NULL DEFAULT 'requester',
    phone VARCHAR(20) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 2. TICKET_CATEGORIES (phu trach: LONG)
-- ---------------------------------------------------------
CREATE TABLE ticket_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 3. SLA_RULES (phu trach: LONG)
-- moi category co dung 1 SLA
-- ---------------------------------------------------------
CREATE TABLE sla_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL UNIQUE,
    response_hours INT NOT NULL DEFAULT 4,
    resolve_hours INT NOT NULL DEFAULT 24,
    CONSTRAINT fk_sla_category
        FOREIGN KEY (category_id) REFERENCES ticket_categories(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4. TICKETS (phu trach: LONG) - bang loi
-- status: open -> assigned -> in_progress -> resolved -> closed (hoac cancelled)
-- ---------------------------------------------------------
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open','assigned','in_progress','resolved','closed','cancelled')
        NOT NULL DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ticket_requester
        FOREIGN KEY (requester_id) REFERENCES users(id),
    CONSTRAINT fk_ticket_category
        FOREIGN KEY (category_id) REFERENCES ticket_categories(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 5. TICKET_ASSIGNMENTS (phu trach: LONG)
-- is_active = 1 nghia la phan cong dang hoat dong (chi 1 active/ticket)
-- ---------------------------------------------------------
CREATE TABLE ticket_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    staff_id INT NOT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_assign_ticket
        FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_assign_staff
        FOREIGN KEY (staff_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 6. TICKET_COMMENTS (phu trach: LONG)
-- ---------------------------------------------------------
CREATE TABLE ticket_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comment_ticket
        FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_user
        FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 7. TICKET_STATUS_LOGS (phu trach: LONG) - bang bat bien, chi INSERT
-- ---------------------------------------------------------
CREATE TABLE ticket_status_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    old_status VARCHAR(20) DEFAULT NULL,
    new_status VARCHAR(20) NOT NULL,
    changed_by INT NOT NULL,
    changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_ticket
        FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_log_user
        FOREIGN KEY (changed_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 8. SATISFACTION_SURVEYS (phu trach: LONG)
-- chi tao khi ticket da closed (kiem tra o tang controller)
-- ---------------------------------------------------------
CREATE TABLE satisfaction_surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL UNIQUE,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    feedback VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_survey_ticket
        FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB;
