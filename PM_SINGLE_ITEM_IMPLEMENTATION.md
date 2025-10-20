# PM Single Item Management System - Implementation Summary

## Overview
Successfully implemented a comprehensive PM single item management system for postal services including SLP Courier, COD, and Register Post services with receipt generation and customer access.

## Features Implemented

### 1. PM Single Item Forms
- **SLP Courier Form**: Sender details, receiver details, weight, automatic postage calculation, barcode generation
- **COD Form**: All SLP features + COD amount, total calculation (amount + postage), enhanced receipt
- **Register Post Form**: Standard postal service with tracking and delivery confirmation

### 2. Database Structure
- **item_bulk table**: Stores bulk/batch information with sender, service type, location, category
- **items table**: Individual item details with barcode, receiver info, weight, total amount
- **sms_sents table**: Stores receiver mobile numbers linked to items for SMS notifications
- **receipts table**: Official receipts with passcode, payment details, linked to item_bulk

### 3. Data Flow
- **Items**: Receiver mobile stored in `sms_sents` table (not in items table)
- **COD Details**: COD amount and postage details stored in `item_bulk.notes` field
- **Total Amount**: Final amount (including COD + postage) stored in `items.amount`
- **Receipts**: Generated automatically with unique passcode and receipt number

### 4. PM Functionality
- **Service Selection**: Choose between SLP, COD, Register Post
- **Auto Pricing**: Weight-based pricing calculation using `SlpPricing` and `PostPricing` models
- **Barcode Generation**: Automatic unique barcode generation with service prefix
- **Receipt Generation**: Immediate receipt generation with print functionality
- **Location Control**: Items created under PM's assigned location

### 5. Customer Receipt Portal
- **View Receipts**: Customers can view all their receipts (sent or received)
- **Search by Barcode**: Quick search functionality using barcode
- **Download/Print**: Professional receipt download with print optimization
- **Mobile Integration**: Receiver mobile numbers properly linked via SMS table

### 6. Receipt Features
- **Comprehensive Details**: Receipt number, barcode, sender/receiver info, service type, amounts
- **Service-Specific Info**: Different layouts for SLP, COD, Register Post
- **Print-Optimized**: Clean print layout with all necessary postal information
- **Professional Format**: Official Sri Lanka Post styling and branding

## Technical Implementation

### Controllers
- `PMSingleItemController`: Handles all PM single item operations
- `CustomerReceiptController`: Manages customer receipt viewing and downloading

### Models Updated
- `Item`: Relationship with `SmsSent` for mobile numbers
- `SmsSent`: Stores receiver mobile and notification status
- `Receipt`: Links to item_bulk with comprehensive payment details

### Views Created
- `pm/single-item/index.blade.php`: Service selection dashboard
- `pm/single-item/slp-form.blade.php`: SLP courier form
- `pm/single-item/cod-form.blade.php`: COD form with amount calculation
- `pm/single-item/register-form.blade.php`: Register post form
- `pm/single-item/receipt.blade.php`: Receipt display
- `pm/single-item/print-receipt.blade.php`: Print-optimized receipt
- `customer/receipts/index.blade.php`: Customer receipt list
- `customer/receipts/show.blade.php`: Customer receipt details
- `customer/receipts/download.blade.php`: Customer receipt download

### Routes Added
- PM single item management routes under `pm/single-item/`
- Customer receipt routes under `customer/receipts/`
- AJAX postage calculation endpoint

### JavaScript Features
- **Real-time Postage Calculation**: Weight-based pricing updates
- **Barcode Generation**: Automatic unique barcode creation
- **Form Validation**: Client-side validation with server-side backup
- **COD Total Calculation**: Dynamic total amount calculation for COD items

## Key Benefits
1. **Streamlined Workflow**: PMs can quickly create single items without bulk uploads
2. **Accurate Pricing**: Automatic calculation prevents pricing errors
3. **Professional Receipts**: Official-looking receipts for customer confidence
4. **Customer Access**: Customers can view and download their receipts
5. **Mobile Integration**: Proper SMS notification setup for tracking
6. **Audit Trail**: Complete tracking from creation to receipt

## Usage Instructions

### For PMs:
1. Login to PM dashboard
2. Navigate to "Items" > "Add Single Item"
3. Choose service type (SLP/COD/Register)
4. Fill in sender and receiver details
5. Enter weight (automatic postage calculation)
6. Generate or enter barcode
7. Submit to create item and generate receipt
8. Print receipt for customer

### For Customers:
1. Login to customer portal
2. Navigate to "My Receipts"
3. View all receipts or search by barcode
4. Click on receipt to view details
5. Download/print receipt as needed

## Database Relationships
```
item_bulk (1) -> (many) items
items (1) -> (many) sms_sents
item_bulk (1) -> (1) receipt
```

This implementation provides a complete, production-ready single item management system that integrates seamlessly with the existing postal management infrastructure.
