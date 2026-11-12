USE helpdesk;

-- Mat khau demo cho tat ca: "123456" (da hash bang password_hash() PHP, bcrypt)
-- Hash mau duoi day tuong ung "123456" - dung de test login sau nay
INSERT INTO users (name, email, password_hash, role, phone) VALUES
('Nguyen Van Long', 'long@tlu.edu.vn', '$2y$10$examplehashonly1234567890abcdefghijk', 'staff', '0900000001'),
('Tran Van Binh', 'binh@tlu.edu.vn', '$2y$10$examplehashonly1234567890abcdefghijk', 'staff', '0900000002'),
('Le Thi Mai', 'mai@tlu.edu.vn', '$2y$10$examplehashonly1234567890abcdefghijk', 'requester', '0900000003'),
('Pham Van Nam', 'nam@tlu.edu.vn', '$2y$10$examplehashonly1234567890abcdefghijk', 'requester', '0900000004'),
('Do Thi Huong', 'huong@tlu.edu.vn', '$2y$10$examplehashonly1234567890abcdefghijk', 'manager', '0900000005');

INSERT INTO ticket_categories (name, description) VALUES
('Mang - Wifi', 'Su co ket noi mang, wifi khong vao duoc'),
('Tai khoan', 'Quen mat khau, khoa tai khoan, khong dang nhap duoc'),
('May chieu - Phong hoc', 'Loi thiet bi trinh chieu trong phong hoc'),
('Phong may', 'Loi may tinh, phan mem trong phong thuc hanh');

INSERT INTO sla_rules (category_id, response_hours, resolve_hours) VALUES
(1, 2, 8),
(2, 1, 4),
(3, 4, 24),
(4, 4, 24);

INSERT INTO tickets (requester_id, category_id, title, description, status) VALUES
(3, 1, 'Wifi phong hoc B302 khong vao duoc', 'Sinh vien phan anh wifi mat ket noi lien tuc tu sang', 'open'),
(4, 2, 'Khong dang nhap duoc email truong', 'Bao loi sai mat khau du da doi lai', 'assigned'),
(3, 3, 'May chieu phong A105 khong len hinh', 'Bat may chieu nhung khong co tin hieu', 'in_progress'),
(4, 4, 'May tinh so 12 phong may khong khoi dong', 'Man hinh den khi bat nguon', 'closed');

INSERT INTO ticket_assignments (ticket_id, staff_id, assigned_at, is_active) VALUES
(2, 1, NOW(), 1),
(3, 2, NOW(), 1),
(4, 1, NOW(), 0);

INSERT INTO ticket_comments (ticket_id, user_id, content) VALUES
(2, 1, 'Da kiem tra, se reset lai tai khoan cho sinh vien'),
(3, 2, 'Dang cu nhan vien ky thuat len kiem tra day cap'),
(4, 1, 'Da thay nguon may, hoat dong binh thuong');

INSERT INTO ticket_status_logs (ticket_id, old_status, new_status, changed_by) VALUES
(2, 'open', 'assigned', 1),
(3, 'open', 'assigned', 2),
(3, 'assigned', 'in_progress', 2),
(4, 'open', 'assigned', 1),
(4, 'assigned', 'in_progress', 1),
(4, 'in_progress', 'resolved', 1),
(4, 'resolved', 'closed', 1);

INSERT INTO satisfaction_surveys (ticket_id, rating, feedback) VALUES
(4, 5, 'Xu ly nhanh, nhan vien nhiet tinh');
