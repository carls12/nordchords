import re
from bs4 import BeautifulSoup

# Read the HTML file
with open('website.html', 'r', encoding='utf-8') as f:
    html_content = f.read()

soup = BeautifulSoup(html_content, 'html.parser')

songs = []

for article in soup.find_all(class_='song-card'):
    title_elem = article.find('h2', class_='song-title')
    lyrics_elem = article.find('div', class_='lyrics')
    
    if title_elem and lyrics_elem:
        title = title_elem.get_text().strip()
        lyrics = lyrics_elem.get_text().strip()  # Extract plain text without HTML tags
        
        songs.append((title, lyrics))

# Generate SQL
sql_statements = []

for title, lyrics in songs:
    # Escape single quotes
    title_escaped = title.replace("'", "''")
    lyrics_escaped = lyrics.replace("'", "''")
    
    # Insert into songs and get ID
    song_sql = f"INSERT INTO songs (title, artist) VALUES ('{title_escaped}', '');"
    sql_statements.append(song_sql)
    
    # Insert into chord_versions using LAST_INSERT_ID()
    version_sql = f"INSERT INTO chord_versions (song_id, version_label, content) VALUES (LAST_INSERT_ID(), 'Default', '{lyrics_escaped}');"
    sql_statements.append(version_sql)

# Write to file
with open('songs_import_new.sql', 'w', encoding='utf-8') as f:
    f.write('\n'.join(sql_statements))

print(f"Generated SQL for {len(songs)} songs.")