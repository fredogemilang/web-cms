# Web CMS - Modular Laravel CMS

A powerful, modular CMS built with Laravel 12, featuring a robust plugin architecture and modern UI.

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)

## 🚀 Deployment & Installation

### 1. Pulling from GitHub
To get the latest version of the code on your server:

```bash
cd /path/to/your/project
git pull origin main
```

### 2. Installation on Shared Hosting

This guide assumes you are using cPanel or a similar shared hosting environment.

#### Option A: With SSH Access (Recommended)
1.  **Clone/Pull Repository**:
    ```bash
    git clone https://github.com/your-repo/web-cms.git .
    ```
2.  **Install Dependencies**:
    ```bash
    composer install --optimize-autoloader --no-dev
    ```
3.  **Environment Setup**:
    - Copy `.env.example` to `.env`
    - Configure your database credentials in `.env`
    - Run `php artisan key:generate`
4.  **Database & Storage**:
    ```bash
    php artisan migrate
    php artisan db:seed
    php artisan storage:link
    ```

#### Option B: Without SSH (File Manager/FTP)
1.  **Upload Files**: Upload all files to your server (preferably outside `public_html` for security, or in a subdirectory).
2.  **Vendor Folder**: Since you cannot run `composer install`, upload the `vendor` folder from your local machine to the server.
3.  **Environment**: 
    - Upload `.env` file (ensure hidden files are visible).
    - Edit `.env` with your database details.
4.  **Symlink Storage**:
    - You may need to create a symlink manually via PHP script if `php artisan storage:link` is unavailable:
    ```php
    <?php
    symlink('/path/to/storage/app/public', '/path/to/public_html/storage');
    ?>
    ```

### 3. Public Folder Setup (Important)
Laravel serves from the `public` folder. On shared hosting:
-   **Best Practice**: Point your domain's "Document Root" to the `/public` folder of the project.
-   **Alternative**: If you must use `public_html`, move the contents of `public/` into `public_html/` and update `index.php` to point back to the correct paths in `bootstrap/app.php`.

---

## ✨ Features

This CMS is built with a modular plugin system. Here are the active modules:

### 📅 Events Management (`/plugins/events`)
Complete solution for managing offline/online events.
-   **Registration System**: User registration with capacity limits and status tracking.
-   **Categories**: Organized by color-coded categories (iC-Talk, iC-Class, etc.).
-   **Doorprize System**: Built-in tool for event engagement and giveaways.
-   **Gallery**: Event photo galleries.
-   **Location**: Google Maps integration.
-   **Automation**: Auto-completes expired events via scheduled tasks.

### 👥 Membership System (`/plugins/membership`)
Simple and effective community member management.
-   **Registration Flow**: Users register -> Admin Approves -> Member Active.
-   **Member Portal**: Dedicated dashboard for members.
-   **Management**: Admin interface to filter, approve, reject, or suspend members.
-   **Export**: CSV export for member data.

### 📰 Blog & News (`/plugins/posts`)
Full-featured blogging platform.
-   **Rich Text Editor**: Uses TipTap for effortless content creation.
-   **Organization**: Categories and Tags support.
-   **WordPress Migration**: Tool to import content from WordPress.
-   **Frontend Submission**: Community members can submit articles via `/upload-article`.

### 📂 Article Submission (`/plugins/article-submission`)
-   **Public Uploads**: Dedicated form for users to upload documents/articles.
-   **Admin Review**: Backend interface to review and download submissions.

### 🛠 Core CMS Capabilities
-   **Page Builder**: Create custom pages like Homepage, About Us, etc.
-   **Media Library**: Centralized file and image management.
-   **Theme System**: Support for switching frontend themes (Blade-based).
-   **Dynamic Menus**: Manage header and footer links via admin.
-   **Settings**: Global site configuration (Name, Logo, Meta).

---

## ⏰ Scheduled Tasks (Cron Jobs)

To ensure events auto-complete and maintenance scripts run, set up a Cron Job:

**Command**:
```bash
* * * * * /usr/local/bin/php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1
```

**Common Duties**:
-   `events:complete-expired`: Marks past events as completed (Daily @ 00:01).

---

## 🔒 Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com).

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
