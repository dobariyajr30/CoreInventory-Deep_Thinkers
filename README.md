# CoreInventory 🏭
### A Modular Inventory Management System

Built for **Deep Thinkers Hackathon** to digitize and streamline all stock-related operations — replacing manual registers and Excel sheets with a centralized real-time system.

---

## 👥 Team — Deep Thinkers
| Name | Role | GitHub |
|------|------|--------|
| Member 1 | Project Lead & Operations | @dobariyajr30 |
| Member 2 | Products & Warehouses | @vatsaldamakle07 |
| Member 3 | Receipts & Deliveries | @Khanjan2805  |
| Member 4 | Auth & OTP & Transfers | @HarshidaPolara |

---

## 🚀 Features
- ✅ Role-based access — Admin, Manager, Staff
- ✅ Receipts — incoming stock from suppliers
- ✅ Delivery Orders — outgoing stock to customers
- ✅ Internal Transfers — move stock between warehouses
- ✅ Stock Adjustments — fix physical count mismatches
- ✅ Real-time Stock Ledger — every movement logged
- ✅ Low Stock Alerts — dashboard warnings
- ✅ Multi-warehouse support
- ✅ OTP-based password reset
- ✅ AI Chatbot support

---

## 🛠️ Tech Stack
- PHP (Pure, no framework)
- MySQL
- Tailwind CSS
- XAMPP

---

## ⚙️ Setup Instructions
1. Import `database.sql` into phpMyAdmin
2. Copy project folder to `C:/xampp/htdocs/coreinventory/`
3. Open `http://localhost/coreinventory/`
4. Login with default credentials

---

## 🔐 Demo Credentials
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@coreinventory.com | Admin123 |
| Manager | manager@coreinventory.com | Manager123 |
| Staff | staff@coreinventory.com | password |

---

## 📁 Project Structure
```
coreinventory/
├── actions/       → Form handlers (login, products, receipts etc.)
├── config/        → Database connection
├── includes/      → Header, sidebar, auth helpers
├── pages/         → Dashboard, products, receipts, deliveries etc.
├── login.php      → Login page
├── register.php   → Register page
├── forgot_password.php → OTP password reset
└── database.sql   → Full database schema with seed data
```

---

## 📊 Inventory Flow
```
Receive Goods → Receipt → Validate → Stock +
Deliver Goods → Delivery → Validate → Stock -
Move Goods    → Transfer → Validate → Location updated
Fix Count     → Adjustment → Apply → Difference logged
```
```


