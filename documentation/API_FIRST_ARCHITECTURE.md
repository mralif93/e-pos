# API-First Architecture: e-POS Terminal Integration

The e-POS system has been refactored to an **API-First Architecture**. This allows any terminal (Web, Mobile, Kiosk) to interact with the backend through a unified, secure, and versioned interface.

---

## 1. Why API-First?
- **Platform Agnostic**: Power a React/Vue frontend, a Flutter mobile app, or a native Android POS terminal with the same logic.
- **Decoupling**: The UI is completely separate from the business logic (Actions/DTOs).
- **Offline Sync**: Terminals can store data locally using UUIDs and sync via the `/api/v1/pos/sync` endpoint without ID collisions.
- **Security**: All requests are authenticated via Laravel Sanctum (Stateful or Token-based).

---

## 2. Authentication
All API requests must include a `Bearer` token in the header:
`Authorization: Bearer <your_api_token>`

---

## 3. Core POS Endpoints (v1)

### Products & Inventory
- `GET /api/v1/pos/products`: Search products for the current outlet.
- `GET /api/v1/pos/categories`: Get category list for the terminal menu.

### Sales & Transactions
- `POST /api/v1/pos/sales`: Process a new sale (Supports loyalty points, multiple payments, and stock validation).
- `POST /api/v1/pos/sales/{id}/void`: Void a sale (Requires Manager PIN).

### Terminal Operations
- `GET /api/v1/pos/shift/current`: Get current shift status and sales summary.
- `POST /api/v1/pos/sync`: Sync offline drafts from the terminal to the server.

### Customer & Loyalty
- `GET /api/v1/pos/customers/{id}/points`: Get customer's current points, tier, and monetary value.

---

## 4. Implementation Details
- **Controllers**: `app/Http/Controllers/Api/PosApiController.php`
- **Logic**: Encapsulated in `app/Actions/Sales/ProcessSaleAction.php`.
- **Routes**: Defined in `routes/api.php` under the `v1` prefix.

---

## 5. Next Steps for Developers
1. **Frontend**: Update the POS UI to call these endpoints instead of standard form submissions.
2. **Mobile App**: Use these endpoints to build a "Staff Order Taking" app.
3. **Kiosk**: Use the `/products` and `/sales` endpoints to create a self-service checkout flow.
