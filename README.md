# e-POS Terminal: Premium Point-of-Sale Solution

A high-performance, enterprise-grade Point of Sale (POS) system built on **Laravel 11**, designed for multi-outlet retail environments. This system features a reactive frontend, API-first architecture, and advanced inventory controls with full tax compliance.

---

## 🚀 Key Modules & Capabilities

### 💻 Point of Sale (POS) Terminal
- **Reactive Interface**: Built with Livewire 3 and Alpine.js for a desktop-class experience.
- **Smart Catalog**: Real-time searching, barcode scanning, and category-based navigation.
- **Flexible Checkout**: supports multiple payment methods (Cash, Card, DuitNow QR) and split payments.
- **Offline Reliability**: Local draft storage with automatic background synchronization.
- **Hardware Ready**: Direct thermal printing (ESC/POS) and cash drawer integration.

### 🏪 Multi-Outlet Infrastructure
- **Data Isolation**: Strict scoping of users, sales, and inventory data per business location.
- **Outlet Customization**: Independent branding, receipt headers, and printer configurations.
- **Admin Visibility**: Global dashboard for Super Admins to monitor performance across all locations.

### 🧾 LHDN MyInvois & Compliance
- **E-Invoicing**: Automatic generation and submission to the Malaysian LHDN MyInvois API.
- **Compliance Ready**: SST support, zero-rated handling, and mandatory QR codes for validation.
- **Audit Trails**: Detailed logs for every financial transaction and sensitive management action.

### � Inventory & Stock Control
- **Real-Time Tracking**: Every sale instantly updates stock levels across outlets.
- **Valuation Methods**: Support for **FIFO** (First-In, First-Out) and **AVCO** (Average Cost) methods.
- **Stock Ledger**: Complete movement history for every SKU (Stock Keeping Unit).

---

## 🛠 Technical Architecture

- **Backend**: Laravel 11.x (PHP 8.2+)
- **Architecture**: Domain-Driven Design (DDD) with a clean `Domains/` layer.
- **Patterns**: Action Pattern (Service Classes), Repository Pattern, and DTOs.
- **API First**: Versioned API (v1) powering the core POS logic (Ready for Mobile/Kiosk).
- **Frontend**: Tailwind CSS v4, Livewire v3, Alpine.js.

---

## 📦 Installation & Setup Guide

### 1. Prerequisites
Ensure your development environment meets these requirements:
- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 20.x or higher
- **Extension**: SQLite, GD, BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML.

### 2. Initial Setup
```bash
# Clone the repository
git clone https://github.com/mralif93/e-pos.git
cd e-pos

# Install backend & frontend dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate
```

### 3. Database & Seeding
By default, the system uses SQLite. To initialize:
```bash
# Create the database file (Windows: use Explorer, Mac/Linux: use touch)
touch database/database.sqlite

# Run migrations and seed the system with demo data
php artisan migrate --seed
```

### 4. Build Assets
Run the production build to compile Tailwind CSS and JavaScript. This project uses a **Tailwind Safelist** to ensure dynamic theme colors render correctly.
```bash
npm run build
```

---

## ⚙️ Environment Configuration

| Variable | Default | Description |
| :--- | :--- | :--- |
| `DB_CONNECTION` | `sqlite` | Database driver |
| `LOYALTY_POINTS_PER_RINGGIT` | `1` | Earning rate for loyalty program |
| `ENABLE_AUDIT_LOGGING` | `true` | Toggle manager audit trail |
| `INVENTORY_VALUATION_METHOD` | `FIFO` | `FIFO` or `AVCO` |
| `LHDN_PRODUCTION` | `false` | LHDN MyInvois Sandbox/Prod toggle |

---

## 📖 Available Documentation Guides

For more specific information, check the `documentation/` folder:
- [API Architecture Guide](documentation/API_FIRST_ARCHITECTURE.md)
- [Environment Variable Reference](documentation/ENV_CONFIGURATION.md)
- [Improvement Roadmap](documentation/PROJECT_IMPROVEMENT_ROADMAP.md)

---

## 🧪 Testing
We maintain a suite of Feature and Unit tests focusing on financial accuracy and stock logic.
```bash
# Run the test suite
php artisan test
```

---

## 📄 License
This system is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
