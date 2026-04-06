README - Chord App (PHP + MySQL)
================================

1) Requirements
- PHP 8+
- MySQL 5.7+ / MariaDB
- Apache (XAMPP is fine)

2) Setup
- Project path example: c:\xampp\htdocs\nordchords
- Create database/tables by importing: sql/schema.sql
- Update DB credentials in includes/config.php if needed.

3) URLs
- Root redirect: /nordchords  ->  /nordchords/public/index.php
- User login: /nordchords/public/login.php
- Forgot password: /nordchords/public/forgot_password.php
- Reset password: /nordchords/public/reset_password.php?token=...
- Direct reset (no link): /nordchords/public/reset_password_direct.php
- Public song list: /nordchords/public/index.php
- Public settings: /nordchords/public/settings.php
- Public profile: /nordchords/public/profile.php
- Admin login: /nordchords/admin/login.php
- Admin profile: /nordchords/admin/profile.php
- Admin users: /nordchords/admin/users.php
- Quick song audio page: /nordchords/admin/song_audio.php?song_id=...

4) Default admin
- Username: admin
- Password: admin123

5) Chord syntax in version content
- Use tags inside lyrics, for example:
  [A]Amazing [D]grace how [G/C]sweet the sound

6) Notes
- Admin can create songs, then add multiple versions per song.
- Songs can be viewed without login; user-specific features require login (registration is disabled for normal users).
- Password reset flow is available for users (forgot/reset password).
- Direct password reset without email link is available.
- Audio belongs to the SONG (not chord version), so it stays while switching chord versions.
- Admin can add song audio in song edit (URL or file upload).
- Admin songs list has an "Audio" button per song for fast upload/edit.
- Uploaded files are stored in: `public/assets/audio/`
- For users, audio player appears near the top of song page (under title area).
- Only admins can access admin dashboard (song/chord stats) and admin tools.
- Admin can promote/demote users to/from admin in Admin Users.
- UI supports 4 languages: English, Deutsch, Espanol, Francais.
- Users can change language in Settings/Profile; admins can change language in Admin Profile.
- Mobile uses an app-like bottom navigation (no burger menu).

7) Existing databases (upgrade)
- If your `admins` table was created before language support:
  ALTER TABLE admins ADD COLUMN language VARCHAR(5) NOT NULL DEFAULT 'en';
- Optional but recommended for clean user<->admin mapping:
  ALTER TABLE admins ADD COLUMN user_id INT UNSIGNED NULL UNIQUE;
- Ensure users table exists:
  CREATE TABLE users (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(100) NOT NULL UNIQUE,
      email VARCHAR(190) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      language VARCHAR(5) NOT NULL DEFAULT 'en',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB;
- For direct reset links/tokens:
  CREATE TABLE password_resets (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      user_id INT UNSIGNED NOT NULL,
      token_hash VARCHAR(255) NOT NULL,
      expires_at DATETIME NOT NULL,
      used_at DATETIME NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB;
- For song-level audio playback:
  ALTER TABLE songs ADD COLUMN audio_url VARCHAR(500) DEFAULT '';
