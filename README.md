# HomeSC

A PHP site for browsing student housing near USC.

## Setup

1. Install PHP 8.1 or later with the `mysqli` extension and have access to the HomeSC MySQL database. The site expects the `vw_housing_overview`, `listings`, `photos`, and `saved_properties` database objects.
2. Copy the example configuration into a local config file:

   ```sh
   cp config.example.php config.php
   ```

3. Edit `config.php` and replace the example host, user, password, database, and port with your database credentials. The site reads this file from `index.php`. `config.php` is ignored by Git; keep credentials out of commits.
4. From the project directory, start PHP's development server:

   ```sh
   php -S localhost:8000
   ```

5. Open `http://localhost:8000`.

The homepage and `search.php` submit filters to `results.php`, which reads matching listings from MySQL. Results include inactive and expired listings for the class assignment, labeled "Not currently available." Listing details read the same database; student sign-in requires the setup below.

## Accounts and listing administration

Users can register and log in with an email address and password. Passwords are stored as hashes. Email ownership and USC affiliation are not verified, and there is currently no password reset. Existing Google-only accounts cannot sign in until their owners receive a password through a separately implemented recovery process; registration will not take over an existing email.

If the existing database does not have `users.is_admin`, back it up and apply `schema.sql` once. Create an account through the site, and have a database administrator set `is_admin = 1` for that account. Use HTTPS on a deployed site. The admin dashboard is at `/admin/`. Create a property, add its units, then create a listing for one of those units. Only listings for published properties appear in the public overview. The database blocks deletion of records with dependents.

Listing forms edit the selected unit's amenities through `amenities` and `housing_amenities`. Changes apply to every listing for that unit. Clear unit-only amenities before removing the last listing if you intend to delete the unit.

Use **Manage photos** from a property or unit edit page to add an existing HTTPS image URL or a local image path such as `assets/ellendale.jpg`, edit captions, or remove photo records. This feature uses existing files and URLs. Removing a record leaves the image file in place.

The map reads `properties.latitude` and `properties.longitude`; properties without verified coordinates remain in the listing results but have no pin. The map uses OpenFreeMap Positron and does not require an API key. No automated scraper or sync is installed.

## Local admin preview

Set `'admin_preview' => true` in your local `config.php`, then start PHP's built-in development server on loopback. In PowerShell:

```powershell
$env:HOMESC_ENV = 'development'
php -S 127.0.0.1:8000
```

Open `http://127.0.0.1:8000/admin/`. The preview uses the configured database and grants read-only access to the admin pages without signing in. Set `admin_preview` back to `false` to use normal administrator sign-in on the local server.
