# e-POS Terminal

A premium, high-performance Point of Sale (POS) system built with **Laravel 11**, **Livewire**, **Alpine.js**, and **Tailwind CSS**. Designed for speed, reliability, and a professional user experience.

![POS Preview](https://github.com/mralif93/e-pos-management-system/raw/main/public/assets/preview.png) *(Placeholder for your actual preview image)*

## 🚀 Key Features

### 💻 Modern POS Interface
- **Redesigned Cart & Checkout**: Polished two-column layout with intuitive controls.
- **Dynamic Theming**: Multi-outlet theme support (Indigo, Orange, Rose, etc.) with consistent branding.
- **Shift Management**: Full shift lifecycle management (Open Shift with float, Close Shift with Manager PIN).
- **Smooth Interactions**: Powered by Alpine.js and SweetAlert2 for a responsive, desktop-like feel.

### 🔌 API-First Architecture
- **Versioned API (v1)**: Robust API layer for POS operations, customer management, and reporting.
- **Domain Driven Design**: Clean project structure with `Domains/`, `Repositories/`, and `Services/`.
- **Offline Ready**: Draft system for handling intermittent connectivity.

### 📊 Business Intelligence & Controls
- **Audit Logging**: Comprehensive activity tracking for all critical user actions.
- **Stock Ledger**: Detailed history of inventory movements and adjustments.
- **User Roles**: Granular access control for Admins, Managers, and Cashiers.

---

## 🛠 Technology Stack

- **Framework**: Laravel 11.x
- **Frontend**: Livewire 3 (Reactive components)
- **Interactivity**: Alpine.js (Lightweight JS framework)
- **Styling**: Tailwind CSS (Utility-first CSS)
- **Build Tool**: Vite
- **Database**: SQLite (Default) / MySQL / PostgreSQL

---

## 📦 Installation & Setup

### 1. Requirements
- PHP 8.2+
- Composer
- Node.js & NPM

### 2. Initialization
```bash
# Clone the repository
git clone https://github.com/mralif93/e-pos.git
cd e-pos

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Build SQLite database (if using default)
touch database/database.sqlite
php artisan migrate --seed
```

### 3. Build & Run
```bash
# Compile assets (Tailwind Safelist enabled)
npm run build

# Start the server
php artisan serve
```

---

## 📖 Related Documentation

Detailed technical guides are available in the `documentation/` directory:
- [API Architecture](documentation/API_FIRST_ARCHITECTURE.md)
- [Environment Configuration](documentation/ENV_CONFIGURATION.md)
- [Project Roadmap](documentation/PROJECT_IMPROVEMENT_ROADMAP.md)

---

## 🧪 Testing & Development

- **Run Tests**: `php artisan test`
- **Quality Control**: `./vendor/bin/pint` (Laravel Pint linter)
- **Vite Dev Server**: `npm run dev`

---

## 📄 License
The e-POS system is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
