# Deployment Guide (Feb 2026 Update)

**Goal**: Update the live site with "Clean URLs", Mobile Admin fixes, and new features without causing downtime.

**Prerequisites**:
- Access to cPanel
- Access to PHPMyAdmin (via cPanel)
- A backup of your current site (highly recommended)

---

## Step 1: Backup (Safety First)
1. Log in to cPanel.
2. Go to **File Manager**.
3. Select your project folder (e.g., `public_html`) and click **Compress** to create a backup zip.
4. Go to **PHPMyAdmin**, select your database, and click **Export** to save a `.sql` backup.

---

## Step 2: Enable Clean URLs (.htaccess)
This step ensures your new links (like `pkautomations.co.ke/shop`) work instead of giving 404 errors.

1. In File Manager, go to your root folder (`public_html` or subdomain).
2. Look for a file named `.htaccess`. 
   - If it exists, **Edit** it.
   - If it doesn't exist, create a new file named `.htaccess`.
3. **Copy-Paste** the content from the file `deployment/production_htaccess` (included in your project) into this file.
   - *Key Rule to look for*: `RewriteRule ^([^\.]+)$ $1.php [NC,L]`
4. Save the file.

---

## Step 3: Update Database Structure
We added features like "Hidden Orders", "Hero Images", and "Preferences". The database needs these new tables.

1. Go to **PHPMyAdmin** in cPanel.
2. Select your live database.
3. Click the **Import** tab.
4. Choose the file `deployment/UPDATE_DB.sql` from your computer.
5. Click **Go** / **Import**.
   - *Note: This script uses `IF NOT EXISTS` so it won't break anything if you already have some of these tables.*

---

## Step 4: Upload New Code
Now we update the PHP files.

1. **On your computer**:
   - Zip your entire project folder (`pk 2026`), EXCLUDING:
     - `node_modules` (if any)
     - `.git` (if any)
     - `deployment` folder (optional, not needed on server)
     - **CRITICAL**: Be careful with `includes/config.php` or `includes/env.php`.
       - If your local `config.php` has distinct content from live, **DO NOT** overwrite the live one.
       - *Recommendation*: Zip all folders (`admin`, `assets`, `includes`, `css`, etc.) and root `.php` files.
2. **On cPanel File Manager**:
   - Navigate to your public folder.
   - Click **Upload** and upload your zip file.
   - Right-click the zip and select **Extract**.
   - Select "Overwrite existing files" if prompted (or confirm the extraction replaces old files).
3. **Verify Configuration**:
   - Check `includes/config.php` on the server to ensure it still connects to the live database (sometimes overwriting it with a local version breaks the connection). If you overwrote it, edit it to restore the correct `DB_PASS` and `DB_USER`.

---

## Step 5: Test
1. Visit your site: `pkautomations.co.ke` (or your URL).
2. Click links in the menu. They should look like `/shop` (no `.php`).
3. Check the **Admin Panel** on your **Mobile Phone**.
   - Login.
   - You should see a menu button. Click it to slide out the sidebar.
4. If you see "404 Not Found" errors on links:
   - Double-check Step 2 (The `.htaccess` file).
   - Ensure `RewriteEngine On` is at the top of that file.

---
**Done!** Your site is now updated with the latest features.
