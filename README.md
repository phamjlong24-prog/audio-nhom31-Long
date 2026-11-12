# Helpdesk CNTT va co so vat chat - CSE485 TLU

## Thong tin nhom
- **Thuc hien**: Long (1 thanh vien)
- **De tai**: De tai 13 - Helpdesk CNTT va co so vat chat
- **Muc ky thuat**: Level 1 - PHP thuan + MySQLi (prepared statement)

## Mo ta
He thong tiep nhan phan anh loi wifi, tai khoan, may chieu, phong may;
phan cong nhan vien xu ly; trao doi qua comment; theo doi trang thai
va khao sat su hai long sau khi dong ticket.

## Stack
- PHP thuan (khong framework) + MySQLi
- MySQL 8
- HTML/CSS thuan

## Cach chay
1. Tao database va import schema:
   ```
   mysql -u root -p < sql/schema.sql
   mysql -u root -p < sql/seed_data.sql
   ```
2. Cau hinh ket noi trong `config/db.php` (mac dinh: root, khong mat khau, db `helpdesk`)
3. Dat thu muc `helpdesk/` vao thu muc goc web server (vd: `htdocs/` cua XAMPP)
4. Truy cap: `http://localhost/helpdesk/modules/tickets/list.php`

## Phan cong module (8 bang)

| Thanh vien | Bang phu trach | Module |
|---|---|---|
| Long | `users`, `ticket_categories`, `sla_rules`, `satisfaction_surveys` | Nen tang, danh muc, khao sat |
| Long | `tickets`, `ticket_assignments`, `ticket_comments`, `ticket_status_logs` | Xu ly ticket (loi) |

## Tien do den 07/08/2026
- [x] Chot de tai, ERD, schema.sql, seed_data.sql
- [x] Long: List + Create cho `ticket_categories`
- [x] Long: List + Create cho `tickets` (co ghi log trang thai)
- [ ] Edit/Delete cho tat ca module (den 19/08)
- [ ] Module SLA, assignments, comments, surveys (den 19/08)
- [ ] Auth that + phan quyen (diem cong, den 19/08)

## Tai khoan demo (seed data)
- long@tlu.edu.vn (staff)
- binh@tlu.edu.vn (staff)
- mai@tlu.edu.vn (requester)
- huong@tlu.edu.vn (manager)
