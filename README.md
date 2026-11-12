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

Bang `users` la bang nen tang dung chung cho toan he thong (khong thuoc rieng
module nao) - ca 2 module deu tham chieu toi qua khoa ngoai. Ca 2 module do Long thuc hien.

| Nguoi thuc hien | Module | Bang phu trach |
|---|---|---|
| Long | Cau hinh & Danh gia dich vu | `ticket_categories`, `sla_rules`, `satisfaction_surveys` |
| Long | Xu ly ticket (loi nghiep vu chinh) | `tickets`, `ticket_assignments`, `ticket_comments`, `ticket_status_logs` |

## Tien do

- [x] Chot de tai, ERD (`docs/erd.png`), `schema.sql`, `seed_data.sql`
- [x] Long: CRUD day du cho `ticket_categories` (List/Create/Edit/Delete)
- [x] Long: CRUD day du cho `sla_rules`, dam bao quan he 1-1 voi category
- [x] Long: CRUD day du cho `satisfaction_surveys`, chi tao duoc khi ticket `closed`
- [x] Long: List/Create/Edit/Delete cho `tickets`
- [x] Long: Trang chi tiet ticket (`detail.php`) gop doi trang thai, phan cong, binh luan, xem log
- [x] Long: Workflow phan cong (`ticket_assignments`) dam bao chi 1 assignment active/ticket
- [x] Long: Workflow binh luan (`ticket_comments`)
- [x] Long: Ghi log trang thai tu dong, khong the sua/xoa (`ticket_status_logs`)
- [ ] Auth that (login/logout, hash password) - diem cong B1
- [ ] Phan quyen requester/staff/manager - diem cong B2

## Luong su dung chinh
1. Long tao danh muc (`modules/categories`) va cau hinh SLA (`modules/sla`)
2. Nguoi dung tao ticket (`modules/tickets/create.php`), chon danh muc co san
3. Vao trang chi tiet ticket (`modules/tickets/detail.php`) de: phan cong nhan vien,
   doi trang thai, trao doi binh luan - moi thao tac deu tu dong ghi log
4. Khi ticket chuyen `closed`, Long co the tao khao sat (`modules/surveys/create.php`)

## Tai khoan demo (seed data)
- long@tlu.edu.vn (staff)
- binh@tlu.edu.vn (staff)
- mai@tlu.edu.vn (requester)
- huong@tlu.edu.vn (manager)
