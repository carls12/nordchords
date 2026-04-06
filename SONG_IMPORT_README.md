# Song Import Instructions

This directory contains a script to import songs from `website.html` into the database.

## Files:
- `extract_songs.py`: Python script to extract songs and generate SQL
- `songs_import.sql`: Generated SQL file with INSERT statements for all 84 songs

## How to Import:
1. Ensure your database is set up with the schema from `sql/schema.sql`
2. Run the SQL file in your MySQL database:
   ```bash
   mysql -u username -p chord_app < songs_import.sql
   ```
   Or use phpMyAdmin to import the file.

## Notes:
- Each song gets a default chord version labeled 'Default'
- Artist field is left empty; you can update it later in the admin panel
- The content includes HTML formatting for chords and lyrics