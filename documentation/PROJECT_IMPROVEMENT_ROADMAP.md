# e-POS Project Improvement Roadmap (Updated)

This document provides a comprehensive strategy for modernizing, scaling, and hardening the e-POS system.

---

## Pillar 1: Core Architecture (Maintainability & Scale)

### 1.1 The Action Pattern (COMPLETED)
Shift logic away from "Fat Controllers" into single-purpose Action classes.
- **Status**: Implemented `ProcessSaleAction`, `RecordStockMovementAction`, and `LogAuditAction`.

### 1.2 Event-Driven Side Effects (COMPLETED)
Use Laravel's Event system to decouple primary transactions from secondary tasks.
- **Status**: Implemented `SaleCompleted` event with async listeners for Loyalty points and E-Invoicing.

### 1.3 Data Transfer Objects - DTOs (COMPLETED)
Pass structured objects between layers instead of raw arrays.
- **Status**: Implemented `SaleData` DTO for type-safe transaction handling.

### 1.4 Domain-Based Organization (COMPLETED)
Group code by business domain (Sales, Inventory, Customers).
- **Status**: Logic migrated to `app/Domains`. Namespaces updated across the application. All tests verified.

### 1.5 Repository Pattern (COMPLETED)
Centralize data access to ensure multi-outlet security and caching.
- **Status**: Implemented `ProductRepository` and `SaleRepository`.

---

## Pillar 2: Performance & Scalability

### 2.1 Concurrency Control (COMPLETED)
Prevent "overselling" in high-traffic environments.
- **Status**: Implemented `lockForUpdate()` (Pessimistic Locking) in `ProcessSaleAction`.

### 2.2 Database Optimization (COMPLETED)
- **Status**: Added composite indexes on `sales`, `sale_items`, and `stock_ledger` for faster multi-outlet reporting.

### 2.3 PWA & Asset Caching (COMPLETED)
Improve load times and reliability for terminals with spotty internet.
- **Status**: Implemented `serviceworker.js` with Stale-While-Revalidate strategy for assets and Network First for navigation. Explicit pre-caching for POS routes.

---

## Pillar 3: Reliability & Security

### 3.1 Robust Offline Synchronization (COMPLETED)
- **Status**: Implemented UUIDs across Sales, Customers, and Drafts to prevent ID collisions. Updated `OfflineSaleService` to use `ProcessSaleAction`.

### 3.2 Comprehensive Audit Logging (COMPLETED)
The "Manager's Trail" for retail security.
- **Status**: Implemented `AuditLog` model and `LogAuditAction` to track sensitive actions (Voids, PIN failures).

### 3.3 Financial Logic Testing (COMPLETED)
- **Status**: Implemented `StockValuationTest` and `LoyaltyCalculationTest` verifying FIFO, AVCO, and loyalty reward/redemption logic. Created missing model factories.

---

## Pillar 4: Feature Robustness (Vertical Growth)

### 4.1 Native Hardware Integration (COMPLETED)
- **Status**: Developed `pos_print.js` utilizing WebUSB for direct ESC/POS thermal printing.

### 4.2 Inventory Ledger & Valuation (COMPLETED)
- **Status**: Implemented `StockLedger` to track every movement with cost. Added `StockValuationService` supporting FIFO and Average Cost (AVCO).

### 4.3 E-Invoicing Compliance (COMPLETED)
- **Status**: Implemented `LHDNService` and queued submission listener for Malaysian MyInvois compliance.

---

## Pillar 5: Frontend Strategy

### 5.1 Hybrid or SPA Approach (COMPLETED)
- **Status**: Transitioned core catalog selection to **Laravel Livewire v3**. Integrated Livewire events with Alpine.js cart state. Removed legacy JS DOM manipulation.

### 5.2 API-First Design (COMPLETED)
- **Status**: Implemented `AuthApiController`, `PosApiController`, `ReportApiController`, and `ManagerApiController` under `/api/v1`.
