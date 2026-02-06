# iCCom Theme Implementation Plan

## Overview

Implementasi tema iCCom untuk web CMS dengan fitur-fitur berikut:
- **Form Submission** (Core Feature) - Form builder dinamis dengan field yang dapat dikustomisasi
- **Events Plugin** - Manajemen event dengan kategori dan tipe berbeda
- **Membership Plugin** - Registrasi member dengan data lengkap
- **iCCom Theme** - Tema modern dengan template HTML yang sudah disediakan

## User Review Required

> [!IMPORTANT]
> **Database Schema Design**
> 
> Form submission akan menggunakan 3 tabel utama:
> - `forms` - Menyimpan definisi form
> - `form_fields` - Menyimpan field-field dalam form
> - `form_entries` - Menyimpan data submission
> 
> Apakah struktur ini sudah sesuai dengan kebutuhan Anda?

> [!IMPORTANT]
> **Plugin vs Core Feature**
> 
> Form Submission akan dibuat sebagai **core feature** (bukan plugin) karena akan digunakan oleh banyak fitur lain. Events dan Membership akan dibuat sebagai **plugin** yang terpisah.
> 
> Apakah pendekatan ini sudah sesuai?

> [!WARNING]
> **Theme Integration**
> 
> Homepage akan menggunakan Pages feature yang sudah ada, bukan hardcoded. Ini berarti konten homepage dapat diedit melalui admin panel.
> 
> Apakah ini sesuai dengan ekspektasi Anda?

---

## Proposed Changes

### Core Features

#### [NEW] Form Submission Feature

**Database Migrations:**

##### [NEW] [create_forms_table.php](file:///c:/laragon/www/web-cms/database/migrations/YYYY_MM_DD_create_forms_table.php)
```php
Schema::create('forms', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->json('settings')->nullable(); // email notifications, success message, etc
    $table->timestamps();
    $table->softDeletes();
});
```

##### [NEW] [create_form_fields_table.php](file:///c:/laragon/www/web-cms/database/migrations/YYYY_MM_DD_create_form_fields_table.php)
```php
Schema::create('form_fields', function (Blueprint $table) {
    $table->id();
    $table->foreignId('form_id')->constrained()->onDelete('cascade');
    $table->string('label');
    $table->string('field_id'); // internal identifier
    $table->string('type'); // text, email, select, radio, checkbox, textarea, file
    $table->json('options')->nullable(); // for select, radio, checkbox
    $table->json('validation')->nullable(); // required, min, max, pattern, etc
    $table->integer('order')->default(0);
    $table->boolean('is_required')->default(false);
    $table->string('placeholder')->nullable();
    $table->string('help_text')->nullable();
    $table->timestamps();
});
```

##### [NEW] [create_form_entries_table.php](file:///c:/laragon/www/web-cms/database/migrations/YYYY_MM_DD_create_form_entries_table.php)
```php
Schema::create('form_entries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('form_id')->constrained()->onDelete('cascade');
    $table->json('data'); // submitted form data
    $table->string('ip_address')->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamps();
});
```

**Models:**

##### [NEW] [Form.php](file:///c:/laragon/www/web-cms/app/Models/Form.php)
- Relationships: `hasMany(FormField)`, `hasMany(FormEntry)`
- Methods: `renderForm()`, `processSubmission()`

##### [NEW] [FormField.php](file:///c:/laragon/www/web-cms/app/Models/FormField.php)
- Relationships: `belongsTo(Form)`
- Methods: `renderField()`, `validateValue()`

##### [NEW] [FormEntry.php](file:///c:/laragon/www/web-cms/app/Models/FormEntry.php)
- Relationships: `belongsTo(Form)`
- Methods: `getFieldValue()`, `exportToCsv()`

**Controllers:**

##### [NEW] [FormController.php](file:///c:/laragon/www/web-cms/app/Http/Controllers/Admin/FormController.php)
- CRUD operations untuk form management
- Form builder interface
- Entry management

##### [NEW] [FormSubmissionController.php](file:///c:/laragon/www/web-cms/app/Http/Controllers/FormSubmissionController.php)
- Handle form submissions dari frontend
- Validation
- Email notifications

**Views:**

##### [NEW] [forms/index.blade.php](file:///c:/laragon/www/web-cms/resources/views/admin/forms/index.blade.php)
- List semua forms dengan status dan entry count

##### [NEW] [forms/create.blade.php](file:///c:/laragon/www/web-cms/resources/views/admin/forms/create.blade.php)
- Form builder interface dengan drag & drop field builder

##### [NEW] [forms/entries.blade.php](file:///c:/laragon/www/web-cms/resources/views/admin/forms/entries.blade.php)
- List entries untuk setiap form dengan export functionality

---

### Events Plugin

#### [NEW] Plugin Structure

```
plugins/events/
├── plugin.json
├── database/
│   └── migrations/
│       └── create_events_table.php
├── src/
│   ├── Models/
│   │   └── Event.php
│   ├── Controllers/
│   │   ├── EventController.php
│   │   └── Admin/
│   │       └── EventAdminController.php
│   └── Helpers/
│       └── EventHelper.php
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── edit.blade.php
│       └── frontend/
│           ├── index.blade.php
│           ├── detail-open.blade.php
│           └── detail-closed.blade.php
└── routes/
    └── web.php
```

##### [NEW] [plugin.json](file:///c:/laragon/www/web-cms/plugins/events/plugin.json)
```json
{
  "name": "Events",
  "slug": "events",
  "version": "1.0.0",
  "description": "Event management plugin for iCCom",
  "author": "Your Name",
  "namespace": "Plugins\\Events",
  "providers": [
    "Plugins\\Events\\EventServiceProvider"
  ]
}
```

**Database Schema:**

##### [NEW] [create_events_table.php](file:///c:/laragon/www/web-cms/plugins/events/database/migrations/create_events_table.php)
```php
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('description');
    $table->date('event_date');
    $table->time('start_time');
    $table->time('end_time');
    $table->string('location')->nullable();
    $table->enum('type', ['online', 'offline']);
    $table->enum('category', ['ic-talk', 'ic-connect', 'ic-class', 'ic-meethub']);
    $table->enum('status', ['upcoming', 'ongoing', 'closed'])->default('upcoming');
    $table->string('featured_image')->nullable();
    $table->json('gallery')->nullable();
    $table->string('registration_link')->nullable();
    $table->boolean('is_featured')->default(false);
    $table->timestamps();
    $table->softDeletes();
});
```

**Key Features:**
- Event CRUD dengan kategori (iC-Talk, iC-Connect, iC-Class, iC-MeetHub)
- Event types (Online, Offline)
- Event status (Upcoming, Ongoing, Closed)
- Featured event untuk homepage
- Gallery untuk setiap event
- Filter by category dan type
- Registration link integration

---

### Membership Plugin

#### [NEW] Plugin Structure

```
plugins/membership/
├── plugin.json
├── database/
│   └── migrations/
│       └── create_members_table.php
├── src/
│   ├── Models/
│   │   └── Member.php
│   ├── Controllers/
│   │   ├── MembershipController.php
│   │   └── Admin/
│   │       └── MemberAdminController.php
│   └── Exports/
│       └── MembersExport.php
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       └── frontend/
│           ├── register.blade.php
│           └── success.blade.php
└── routes/
    └── web.php
```

##### [NEW] [plugin.json](file:///c:/laragon/www/web-cms/plugins/membership/plugin.json)
```json
{
  "name": "Membership",
  "slug": "membership",
  "version": "1.0.0",
  "description": "Member registration and management for iCCom",
  "author": "Your Name",
  "namespace": "Plugins\\Membership",
  "providers": [
    "Plugins\\Membership\\MembershipServiceProvider"
  ]
}
```

**Database Schema:**

##### [NEW] [create_members_table.php](file:///c:/laragon/www/web-cms/plugins/membership/database/migrations/create_members_table.php)
```php
Schema::create('members', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('phone');
    $table->string('job_level')->nullable();
    $table->string('job_title')->nullable();
    $table->string('domicile')->nullable();
    $table->string('linkedin_account')->nullable();
    $table->string('institution_company')->nullable();
    $table->string('education_level')->nullable();
    $table->string('industry')->nullable();
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->timestamps();
    $table->softDeletes();
});
```

**Key Features:**
- Member registration form
- Admin panel untuk member management
- Export members to CSV/Excel
- Member statistics dashboard
- Email notification untuk new member

---

### iCCom Theme

#### [NEW] Theme Structure

```
themes/iccom/
├── theme.json
├── assets/
│   ├── css/
│   │   └── style.css (from template)
│   ├── js/
│   │   └── main.js
│   └── images/
│       └── (all assets from template)
├── views/
│   ├── layouts/
│   │   └── app.blade.php
│   ├── components/
│   │   ├── header.blade.php
│   │   ├── footer.blade.php
│   │   ├── navigation.blade.php
│   │   ├── social-sidebar.blade.php
│   │   └── mobile-nav.blade.php
│   ├── pages/
│   │   ├── home.blade.php
│   │   └── page.blade.php
│   ├── posts/
│   │   ├── index.blade.php
│   │   └── single.blade.php
│   ├── events/
│   │   ├── index.blade.php
│   │   ├── detail-open.blade.php
│   │   └── detail-closed.blade.php
│   └── membership/
│       ├── register.blade.php
│       └── success.blade.php
└── functions.php
```

##### [NEW] [theme.json](file:///c:/laragon/www/web-cms/themes/iccom/theme.json)
```json
{
  "name": "iCCom",
  "slug": "iccom",
  "version": "1.0.0",
  "description": "Modern theme for Indonesia Cloud Community",
  "author": "Your Name",
  "screenshot": "screenshot.png",
  "supports": [
    "pages",
    "posts",
    "events",
    "membership",
    "menus",
    "widgets"
  ]
}
```

**Template Conversion:**
- Convert all HTML files to Blade templates
- Extract reusable components (header, footer, nav)
- Integrate with existing Pages and Posts features
- Add dynamic data binding
- Implement theme customization options

---

## Implementation Steps

### Step 1: Core Form Submission Feature (Week 1)

1. **Database Setup**
   - Create migrations for forms, form_fields, form_entries
   - Run migrations

2. **Models & Relationships**
   - Create Form, FormField, FormEntry models
   - Define relationships and methods

3. **Admin Controllers**
   - Create FormController for CRUD
   - Implement form builder logic

4. **Admin Views**
   - Create form management interface
   - Build drag & drop form builder
   - Create entry management view

5. **Frontend Integration**
   - Create FormSubmissionController
   - Add form rendering helper
   - Implement validation and submission logic

### Step 2: Events Plugin (Week 2)

1. **Plugin Structure**
   - Create plugin directory structure
   - Create plugin.json
   - Create EventServiceProvider

2. **Database & Models**
   - Create events migration
   - Create Event model

3. **Admin Interface**
   - Create EventAdminController
   - Build event management views
   - Add image upload functionality

4. **Frontend Templates**
   - Create event listing page
   - Create event detail pages (open/closed)
   - Implement filtering functionality

### Step 3: Membership Plugin (Week 2)

1. **Plugin Structure**
   - Create plugin directory structure
   - Create plugin.json
   - Create MembershipServiceProvider

2. **Database & Models**
   - Create members migration
   - Create Member model

3. **Registration System**
   - Create MembershipController
   - Build registration form
   - Add validation

4. **Admin Interface**
   - Create MemberAdminController
   - Build member management views
   - Add export functionality

### Step 4: iCCom Theme (Week 3)

1. **Theme Setup**
   - Create theme directory structure
   - Copy assets from HTML template
   - Create theme.json

2. **Layout & Components**
   - Create main layout (app.blade.php)
   - Extract header component
   - Extract footer component
   - Extract navigation component
   - Extract social sidebar component
   - Extract mobile nav component

3. **Page Templates**
   - Convert index.html to home.blade.php
   - Create generic page template
   - Convert blog.html to posts/index.blade.php
   - Create single post template

4. **Plugin Integration**
   - Integrate Events plugin templates
   - Integrate Membership plugin templates
   - Add dynamic menu system

5. **Theme Activation**
   - Register theme in system
   - Set as active theme
   - Test all pages

### Step 5: Testing & Integration (Week 4)

1. **Feature Testing**
   - Test form creation and submission
   - Test event management
   - Test member registration

2. **Theme Testing**
   - Test all page templates
   - Test responsive design
   - Test navigation and menus

3. **Integration Testing**
   - Test homepage with Pages feature
   - Test blog with Posts plugin
   - Test Events integration
   - Test Membership integration

4. **Final Adjustments**
   - Fix bugs
   - Optimize performance
   - Add documentation

---

## Verification Plan

### Automated Tests

```bash
# Run migrations
php artisan migrate

# Test form creation
php artisan tinker
>>> $form = Form::create(['name' => 'Test Form', 'slug' => 'test-form']);
>>> $form->fields()->create(['label' => 'Name', 'field_id' => 'name', 'type' => 'text']);

# Test event creation
>>> $event = Event::create([...]);

# Test member registration
>>> $member = Member::create([...]);
```

### Manual Verification

1. **Form Submission Feature**
   - [ ] Create a new form via admin panel
   - [ ] Add various field types
   - [ ] Render form on frontend
   - [ ] Submit form and verify entry is saved
   - [ ] Export entries to CSV

2. **Events Plugin**
   - [ ] Create multiple events with different categories
   - [ ] Upload event images
   - [ ] View events listing page
   - [ ] Filter events by category and type
   - [ ] View event detail pages

3. **Membership Plugin**
   - [ ] Fill out membership registration form
   - [ ] Verify member is saved in database
   - [ ] View member in admin panel
   - [ ] Export members list

4. **iCCom Theme**
   - [ ] Activate theme
   - [ ] View homepage (using Pages feature)
   - [ ] View blog listing and single post
   - [ ] View events pages
   - [ ] View membership registration
   - [ ] Test responsive design on mobile
   - [ ] Test all navigation elements

---

## Timeline

- **Week 1**: Core Form Submission Feature
- **Week 2**: Events & Membership Plugins
- **Week 3**: iCCom Theme Development
- **Week 4**: Testing & Integration

**Total Estimated Time**: 4 weeks

---

## Notes

- Form Submission sebagai core feature akan memudahkan plugin lain untuk menggunakan form builder
- Events dan Membership sebagai plugin memudahkan maintenance dan update
- Theme menggunakan Blade templating untuk fleksibilitas
- Semua fitur mengikuti struktur MVC yang sudah ada di CMS
