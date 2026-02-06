# Membership Plugin - Simple Community Membership ✅

## Overview
Simple community membership management system for iCCom with registration and approval workflow. No tiers, no payment, just member management.

## ✅ What's Included

### Plugin Structure
- `plugin.json` - Plugin configuration
- `MembershipServiceProvider.php` - Service provider with admin menu
- `routes/web.php` - Admin, member portal, and public routes

### Database (1 Table)
**memberships** - Simple member records with approval workflow

Fields:
- `id` - Primary key
- `user_id` - Foreign key to users table
- `status` - pending, active, rejected, suspended
- `joined_at` - Date when approved/activated
- `notes` - Admin notes
- `metadata` - JSON field for custom registration data
- `approved_by` - Foreign key to admin who approved
- `approved_at` - Approval timestamp
- `timestamps` - created_at, updated_at
- `soft_deletes` - deleted_at

### Models
**Membership** - Member records with status management and approval methods

### Livewire Components
**MembersTable** - Modern table with:
- Real-time search (name & email)
- Status filter
- Sorting
- Bulk approve/delete
- Stats cards (Total, Active, Pending, Rejected)

### Features Implemented
- ✅ Member registration tracking
- ✅ Admin approval workflow
- ✅ Member status management (pending/active/rejected/suspended)
- ✅ CSV export functionality
- ✅ Member statistics dashboard
- ✅ Modern UI with dark mode
- ✅ Bulk actions
- ✅ Search and filters

### Admin Routes
- `/admin/membership` - All members
- `/admin/membership/pending` - Pending approvals
- `/admin/membership/{id}` - Member details
- `/admin/membership/export/csv` - Export CSV

### Member Portal Routes
- `/member/dashboard` - Member dashboard (optional)

### Public Routes
- `/membership/register` - Registration form

## 🚀 Installation

```bash
# Run migration to create simplified table
php artisan migrate --path=plugins/membership/database/migrations/2026_02_01_073429_simplify_membership_system.php
```

**Note**: This migration will drop old tables (membership_tiers, membership_benefits) and create a new simplified memberships table.

## 📋 Usage

### Admin Workflow
1. Member registers via `/membership/register`
2. Admin sees pending member in `/admin/membership`
3. Admin approves or rejects member
4. Approved members get `active` status and `joined_at` date

### Member Statuses
- **pending** - Awaiting admin approval
- **active** - Approved and active member
- **rejected** - Application rejected
- **suspended** - Temporarily suspended

### Bulk Actions
- Select multiple members
- Bulk approve pending members
- Bulk delete members

### Export
- Export all members to CSV
- Includes: ID, Name, Email, Status, Joined Date, Registered Date

## 🎨 UI Features

- Modern Tailwind CSS design
- Dark mode support
- Responsive layout
- Stats cards with icons
- Real-time search
- Smooth transitions
- Material Symbols icons

## 🔧 Customization

### Add Custom Fields
Use the `metadata` JSON field to store custom registration data:

```php
$membership->metadata = [
    'phone' => '081234567890',
    'company' => 'Example Corp',
    'reason' => 'Want to join community',
];
```

### Add Admin Notes
```php
$membership->notes = 'Verified via email';
```

## 📝 Model Methods

```php
// Approve member
$membership->approve($adminId);

// Reject member
$membership->reject();

// Suspend member
$membership->suspend();

// Reactivate member
$membership->reactivate();

// Check if active
$membership->is_active; // boolean

// Export to array
$membership->toExportArray();
```

## 🎯 What Was Removed

From the previous tiered system:
- ❌ Membership tiers
- ❌ Payment tracking
- ❌ Pricing/benefits
- ❌ Expiry dates
- ❌ Auto-renewal
- ❌ Tier management UI

## 💡 Future Enhancements (Optional)

- [ ] Email notifications on approval/rejection
- [ ] Member portal with profile
- [ ] Registration form builder
- [ ] Member directory (public)
- [ ] Member badges/achievements
- [ ] Activity tracking
