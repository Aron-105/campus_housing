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

The homepage and `search.php` submit filters to `results.php`, which reads matching listings from MySQL. Results include inactive and expired listings for the class assignment, labeled "Not currently available." Listing details and account sign-in are currently placeholder pages.
