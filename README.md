# HomeSC

A PHP site for browsing student housing near USC.

## Setup

1. Install PHP with the `mysqli` extension and have access to the HomeSC MySQL database. The site expects the `vw_housing_overview`, `listings`, `photos`, and `saved_properties` database objects.
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

## Student accounts and reviews

The application requires PHP's `curl` and `mysqli` extensions. Back up the database, then apply `schema.sql` once using a database account permitted to create tables. The new identity table maps Google subjects to existing `users`; reviews continue to use the existing `reviews` table. The database account used by the app needs read/write access to those tables and `saved_properties`.

Create a Google OAuth web application, register the exact callback URL ending in `/auth.php`, and add `google_client_id`, `google_client_secret`, and `google_redirect_uri` to the ignored `config.php` (see `config.example.php`). Use HTTPS in deployment and keep the client secret private. Sign-in accepts verified `@usc.edu` Google addresses. Administrators are a placeholder. An authenticated student can submit one editable review per property; reviews are published immediately.

The map reads `properties.latitude` and `properties.longitude`; properties without verified coordinates remain in the listing results but have no pin. The map uses OpenFreeMap Positron and does not require an API key. See [listing data research](LISTING_DATA.md) for source research, mapping, and the proposed future sync. No automated scraper or sync is installed.
