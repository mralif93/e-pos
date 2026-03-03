# e-POS Environment Configuration Guide

This document lists the required and optional `.env` variables for the newly implemented features in the e-POS system.

---

## 1. Loyalty Program Settings
Configure how customers earn and redeem points.

```env
# Points earned per RM 1.00 spent
LOYALTY_POINTS_PER_RINGGIT=1

# Minimum spend required to earn points
LOYALTY_MIN_SPEND=1.00

# Value of 1 point in RM for each tier (Bronze, Silver, Gold, Platinum)
LOYALTY_VALUE_BRONZE=0.025
LOYALTY_VALUE_SILVER=0.030
LOYALTY_VALUE_GOLD=0.035
LOYALTY_VALUE_PLATINUM=0.040
```

---

## 2. E-Invoicing (LHDN MyInvois)
Required for Malaysian tax compliance.

```env
# LHDN MyInvois API Credentials
LHDN_CLIENT_ID=your_client_id
LHDN_CLIENT_SECRET=your_client_secret
LHDN_TIN=your_tax_identification_number
LHDN_BRN=your_business_registration_number

# Toggle Sandbox or Production
LHDN_PRODUCTION=false
```

---

## 3. DuitNow QR Integration
For dynamic and static QR code generation via Compay or similar gateway.

```env
# DuitNow Merchant Details
DUITNOW_MERCHANT_ID=your_merchant_id
DUITNOW_SECRET_KEY=your_secret_key

# Gateway settings
DUITNOW_PRODUCTION=false
DUITNOW_EXPIRY_MINUTES=60
```

---

## 4. SSM Company Lookup
Used for verifying customer company details for B2B sales or E-Invoicing.

```env
# SSM API Key
SSM_API_KEY=your_ssm_api_key

# Toggle Sandbox or Production
SSM_PRODUCTION=false
```

---

## 5. System Features & Performance

```env
# Inventory Valuation Method (FIFO or AVCO)
INVENTORY_VALUATION_METHOD=FIFO

# Audit Logging Level (Set to 'true' to enable detailed activity tracking)
ENABLE_AUDIT_LOGGING=true

# POS Terminal Mode (true for dedicated terminal behavior)
POS_TERMINAL_MODE=true
```

---

## 6. Offline Synchronization

```env
# Frequency of auto-sync for offline drafts (in minutes)
OFFLINE_SYNC_INTERVAL=5

# Maximum number of retries for a failed sync
OFFLINE_SYNC_MAX_RETRIES=3
```

---

## Configuration Setup Note
After adding these variables to your `.env` file, ensure you run:
`php artisan config:cache` to apply the changes in production.
