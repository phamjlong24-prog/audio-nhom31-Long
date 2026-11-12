
# Minh chung nop truoc gio kiem tra (07/08/2026)

## 3.1. Thong tin
- **Nguoi thuc hien**: Long
- **De tai**: De tai 13 - Quan ly Helpdesk CNTT va co so vat chat (rut gon con 8 bang)
- **Link repository GitHub**: (dien link repo cua ban vao day)
- **Link ERD / duong dan file trong repo**: `/docs/erd.png` hoac xem truc tiep trong `sql/schema.sql`
- **Ngay dang ky / duyet de tai**: (dien ngay chot de tai)

## 3.2. Bang cong viec hien tai

Bang `users` la bang nen tang dung chung cho toan he thong (phuc vu Auth,
khong thuoc rieng module nao) - ca 2 module deu tham chieu toi qua khoa ngoai.

| Ho ten | Ma SV | Module | Viec da lam den 07/08 | Viec se lam den 19/08 |
|---|---|---|---|---|
| Long | (dien MSSV) | Module "Cau hinh & Danh gia dich vu": `ticket_categories`, `sla_rules`, `satisfaction_surveys` | Da viet schema.sql + seed data; hoan thien List + Create cho `ticket_categories` (co validation, chong trung ten, CSRF) | Hoan thien Edit/Delete cho ca 3 bang; lam Auth (B1) |
| Long | (dien MSSV) | Module "Xu ly ticket": `tickets`, `ticket_assignments`, `ticket_comments`, `ticket_status_logs` | Da hoan thien List (JOIN hien thi ten category/nguoi gui thay vi ID) + Create ticket (co transaction, tu dong ghi status log) | Hoan thien trang detail.php, module phan cong (assignments), comment, doi trang thai; lam phan quyen (B2) |

## 3.2b. Cap nhat tien do den 19/08/2026 (bo sung sau checkpoint)

| Phan viec | Viec da hoan thien them | Minh chung |
|---|---|---|
| Cau hinh & Danh gia dich vu | CRUD day du (List/Create/Edit/Delete) cho ca 3 bang; xay dung `includes/auth_check.php` (middleware `require_login()`, `require_role()`); trang `modules/auth/login.php`, `modules/auth/logout.php`; tao bcrypt hash that cho mat khau demo | Commit "hoan thien CRUD 8 bang" + "them Auth login/logout" |
| Xu ly ticket | Hoan thien `detail.php` (trung tam doi trang thai/phan cong/binh luan/xem log); `edit.php`, `delete.php` xoa an toan cho tickets; ap dung `require_role(['staff','manager'])` cho cac thao tac quan tri (doi trang thai, phan cong, sua/xoa ticket) | Commit "hoan thien workflow ticket detail" + "ap dung phan quyen" |

**Diem cong da hoan thanh:**
- **B1 (Auth)**: Login/logout that qua session, mat khau hash bang bcrypt
  (`password_hash`/`password_verify`), guest bi chan boi `require_login()`,
  co tai khoan demo trong README.
- **B2 (Phan quyen)**: 2 vai tro co y nghia (`staff`/`manager` vs `requester`),
  requester dang nhap nhung sai quyen van bi chan (ca backend qua
  `require_role()` lan an nut tren giao dien), demo duoc ca allow va deny.

## 3.3. Minh chung ky thuat toi thieu
- [x] Repository GitHub - can push code nay len va cap quyen cho giang vien
- [x] Lich su commit day du theo tung phan viec
- [x] Co `schema.sql` day du 8 bang + FK + constraint
- [x] Co README ngan: de tai, stack, cach chay, tai khoan demo, ma tran quyen

## 3.4. Minh chung ca nhan

**Long:**
- Da thiet ke ERD (users, categories, sla_rules, surveys, tickets, assignments, comments, status_logs) va giai thich duoc quan he: `sla_rules.category_id` la FK 1-1 toi `ticket_categories`; `satisfaction_surveys.ticket_id` la FK 1-1 toi `tickets`; `tickets` la bang trung tam, 1 ticket co nhieu assignment/comment/log (1-N).
- Da viet SQL cho ca 8 bang trong `schema.sql`.
- Co trang List va Create chay duoc that cho `ticket_categories` (co validation backend, chong trung ten, CSRF token).
- Co trang List ticket (JOIN 3 bang, hien thi ten thay vi ID) va Create ticket chay duoc that, co dung transaction (`mysqli_begin_transaction`) de dam bao tao ticket va ghi log status cung luc.
- Da seed du lieu mau co du lieu that (4 category + 4 SLA rule tuong ung) va quan he cha-con hop le.

---

# Phieu tu khai nhanh

## Long
| Truong | Noi dung |
|---|---|
| Ho ten | Long |
| Ma sinh vien | (dien) |
| De tai | De tai 13 - Helpdesk CNTT |
| Module hoac bang phu trach | Toan bo 8 bang: users, ticket_categories, sla_rules, satisfaction_surveys, tickets, ticket_assignments, ticket_comments, ticket_status_logs |
| Viec da hoan thanh den 07/08 | Schema 8 bang, seed data, List + Create cho ticket_categories, List (JOIN hien thi ten) + Create ticket (co transaction + tu ghi log) |
| Link commit / file minh chung | (dien link commit repo) |
| Viec se lam tiep den 19/08 | Edit/Delete category, module sla_rules va satisfaction_surveys, Auth (login/logout, hash password); trang detail.php, module assignments va comments, xu ly doi trang thai + ghi log, phan quyen staff/manager |
| Phan chua chac / can mentoring | Cach kiem tra ticket da closed truoc khi cho tao survey; cach lam Auth dung chuan (session + hash); cach dam bao chi 1 assignment active/ticket khi phan cong lai |

---

# Cau hoi van dap co the gap - chuan bi truoc

**Phan categories/SLA/survey:**
1. Bang nao quan he voi bang nao? -> `ticket_categories` 1-N voi `tickets`; `ticket_categories` 1-1 voi `sla_rules`; `tickets` 1-1 voi `satisfaction_surveys`.
2. Khoa ngoai nam o dau? -> `sla_rules.category_id` va `satisfaction_surveys.ticket_id`.
3. Tai sao category_id trong sla_rules la UNIQUE? -> vi moi danh muc chi co dung 1 quy tac SLA, dam bao quan he 1-1.
4. Neu xoa 1 category dang co ticket thi sao? -> hien tai chua chan (can them kiem tra "con ticket dang dung thi khong cho xoa" truoc 19/08).

**Phan tickets/assignments/comments/logs:**
1. Vi sao dung transaction khi tao ticket? -> vi phai insert vao ca bang `tickets` va `ticket_status_logs` cung luc; neu 1 buoc loi thi phai rollback ca 2, tranh du lieu khong dong bo.
2. `ticket_status_logs` co cho sua/xoa khong? -> Khong, day la bang bat bien, chi duoc INSERT them dong moi moi khi doi trang thai.
3. Lam sao dam bao 1 ticket chi co 1 assignment dang hoat dong? -> dung cot `is_active`, khi phan cong lai thi update dong cu thanh `is_active = 0` roi insert dong moi.
4. Trong List, tai sao dung JOIN thay vi hien ID? -> de nguoi dung thay ten danh muc/nguoi gui thay vi so ID, la yeu cau bat buoc trong barem (A5).
