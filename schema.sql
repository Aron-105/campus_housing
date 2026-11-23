-- Apply once to the existing HomeSC database after backing it up.
CREATE TABLE IF NOT EXISTS google_identities (
    google_subject VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
