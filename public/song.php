<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../includes/chord_render.php';

function songs_has_audio_column_public(): bool
{
    static $hasColumn = null;

    if (is_bool($hasColumn)) {
        return $hasColumn;
    }

    $stmt = db()->query("SHOW COLUMNS FROM songs LIKE 'audio_url'");
    $hasColumn = (bool) $stmt->fetch();
    return $hasColumn;
}

$songId = (int) ($_GET['id'] ?? 0);
$versionId = (int) ($_GET['v'] ?? 0);

if ($songId < 1) {
    http_response_code(404);
    exit(t('song_not_found'));
}

$hasSongAudioColumn = songs_has_audio_column_public();
$songSql = 'SELECT id, title, artist, audio_url, 
            IFNULL(song_key, "") as song_key,
            IFNULL(tuning, "Standard (E A D G B E)") as tuning,
            IFNULL(difficulty, "Novice") as difficulty,
            IFNULL(rating_value, 0) as rating_value,
            IFNULL(rating_votes, 0) as rating_votes,
            updated_at 
            FROM songs WHERE id = ? LIMIT 1';
$stmt = db()->prepare($songSql);
$stmt->execute([$songId]);
$song = $stmt->fetch();

if (!$song) {
    http_response_code(404);
    exit(t('song_not_found'));
}

$versionsSql = 'SELECT id, version_label, content, notes FROM chord_versions WHERE song_id = ? ORDER BY version_label ASC, id ASC';
$versionsStmt = db()->prepare($versionsSql);
$versionsStmt->execute([$songId]);
$versions = $versionsStmt->fetchAll();

$currentVersion = null;
if ($versions) {
    if ($versionId > 0) {
        foreach ($versions as $v) {
            if ((int) $v['id'] === $versionId) {
                $currentVersion = $v;
                break;
            }
        }
    }

    if (!$currentVersion) {
        $currentVersion = $versions[0];
    }
}

// Check if user has song in songbook
$isInSongbook = false;
$user = current_user();
if ($user) {
    $sbStmt = db()->prepare('SELECT 1 FROM user_songbooks WHERE user_id = ? AND song_id = ? LIMIT 1');
    $sbStmt->execute([(int) $user['id'], $songId]);
    $isInSongbook = (bool) $sbStmt->fetch();
}

$pageTitle = $song['title'];
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="mb-3">
    <a class="text-decoration-none" href="<?= e(url('public/index.php')) ?>"><i class="bi bi-arrow-left me-1"></i><?= e(t('back_to_songs')) ?></a>
</div>

<!-- Song Hero Section -->
<div class="song-hero-section mb-4" style="<?= e(song_theme_vars((string) $song['id'] . '|' . (string) $song['title'])) ?>">
    <div class="row align-items-center g-4">
        <div class="col-md-8">
            <h1 class="h2 mb-2"><?= e($song['title']) ?></h1>
            <p class="lead text-muted mb-3"><?= e($song['artist'] ?: t('unknown_artist')) ?></p>
            
            <!-- Rating Stars -->
            <div class="song-rating mb-3">
                <?php 
                $rating = (float) ($song['rating_value'] ?? 0);
                $votes = (int) ($song['rating_votes'] ?? 0);
                $fullStars = (int) $rating;
                $hasHalfStar = ($rating - $fullStars) >= 0.5;
                ?>
                <span class="rating-stars">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <?php if ($i < $fullStars): ?>
                            <i class="bi bi-star-fill text-warning"></i>
                        <?php elseif ($i == $fullStars && $hasHalfStar): ?>
                            <i class="bi bi-star-half text-warning"></i>
                        <?php else: ?>
                            <i class="bi bi-star text-muted"></i>
                        <?php endif; ?>
                    <?php endfor; ?>
                </span>
                <span class="ms-2 text-muted small"><?= e(number_format($rating, 1)) ?> (<?= (int) $votes ?> votes)</span>
            </div>

            <!-- Metadata Tags -->
            <div class="song-metadata mb-3">
                <?php if (!empty($song['difficulty'])): ?>
                    <span class="badge bg-primary"><?= e($song['difficulty']) ?></span>
                <?php endif; ?>
                <?php if (!empty($song['song_key'])): ?>
                    <span class="badge bg-info">Key: <?= e($song['song_key']) ?></span>
                <?php endif; ?>
                <?php if (!empty($song['tuning'])): ?>
                    <span class="badge bg-secondary"><?= e($song['tuning']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Last Updated -->
            <?php if (!empty($song['updated_at'])): ?>
                <p class="small text-muted mb-0">
                    <i class="bi bi-clock me-1"></i>Last Updated: <?= e(date('F j, Y', strtotime($song['updated_at']))) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Control Toolbar -->
<div class="control-toolbar card border-0 shadow-sm mb-4 sticky-top" style="top: 70px; z-index: 990;">
    <div class="card-body py-2">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="text-muted small d-block mb-1">Font Size</label>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary" id="decreaseFont" title="Decrease font size">
                        <i class="bi bi-dash"></i> A
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="resetFont" title="Reset font size">
                        A
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="increaseFont" title="Increase font size">
                        A <i class="bi bi-plus"></i>
                    </button>
                </div>
            </div>

            <div class="col-auto">
                <label class="text-muted small d-block mb-1">Transpose</label>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary" id="transposeDown" title="Transpose down (-1 semitone)">
                        <i class="bi bi-dash"></i>
                    </button>
                    <span id="transposeDisplay" class="btn btn-outline-secondary disabled" style="min-width: 45px;">0</span>
                    <button type="button" class="btn btn-outline-secondary" id="transposeUp" title="Transpose up (+1 semitone)">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
            </div>

            <div class="col-auto">
                <label class="text-muted small d-block mb-1">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleChords" title="Hide/Show chords">
                    <i class="bi bi-eye"></i> Chords
                </button>
            </div>

            <div class="col-auto ms-auto">
                <label class="text-muted small d-block mb-1">&nbsp;</label>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()" title="Print">
                        <i class="bi bi-printer"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="shareBtn" title="Share">
                        <i class="bi bi-share"></i>
                    </button>
                    <?php if ($user): ?>
                        <button type="button" class="btn btn-outline-secondary" id="songbookBtn" title="Add to songbook">
                            <i class="bi bi-<?= $isInSongbook ? 'bookmark-fill' : 'bookmark' ?>"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audio Player -->
<?php if (!empty($song['audio_url'])): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <label class="form-label mb-2"><i class="bi bi-play-circle me-1"></i>Audio Track</label>
            <audio controls class="w-100">
                <source src="<?= e((string) $song['audio_url']) ?>">
            </audio>
        </div>
    </div>
<?php endif; ?>

<!-- Version Selector -->
<?php if (!$versions): ?>
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i><?= e(t('no_versions_for_song')) ?></div>
<?php else: ?>
    <?php if (count($versions) > 1): ?>
        <form method="get" class="mb-4">
            <input type="hidden" name="id" value="<?= (int) $songId ?>">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Chord Version</label>
                    <select name="v" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($versions as $v): ?>
                            <option value="<?= (int) $v['id'] ?>" <?= ((int) $currentVersion['id'] === (int) $v['id']) ? 'selected' : '' ?>>
                                <?= e($v['version_label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>
    <?php endif; ?>

    <!-- Lyrics Box with Chords -->
    <div class="card border-0 shadow-sm chord-display-card">
        <div class="card-body">
            <?php if (!empty($currentVersion['notes'])): ?>
                <p class="small text-muted mb-3"><i class="bi bi-info-circle me-1"></i><?= e($currentVersion['notes']) ?></p>
            <?php endif; ?>

            <div class="lyrics-box" id="lyricsBox">
                <?= render_chord_text((string) $currentVersion['content']) ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- JavaScript for Interactive Features -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let fontLevel = 0;
    let transposeValue = 0;
    let chordsHidden = false;

    // Font Size Controls
    document.getElementById('increaseFont').addEventListener('click', function() {
        fontLevel++;
        updateFontSize();
    });

    document.getElementById('decreaseFont').addEventListener('click', function() {
        fontLevel--;
        updateFontSize();
    });

    document.getElementById('resetFont').addEventListener('click', function() {
        fontLevel = 0;
        updateFontSize();
    });

    function updateFontSize() {
        const lyricsBox = document.getElementById('lyricsBox');
        const baseSize = 16;
        const step = 2;
        const newSize = baseSize + (fontLevel * step);
        lyricsBox.style.fontSize = newSize + 'px';
        localStorage.setItem('chordFontLevel', fontLevel);
    }

    // Transpose Controls
    document.getElementById('transposeUp').addEventListener('click', function() {
        transposeValue++;
        updateTranspose();
    });

    document.getElementById('transposeDown').addEventListener('click', function() {
        transposeValue--;
        updateTranspose();
    });

    function updateTranspose() {
        document.getElementById('transposeDisplay').textContent = transposeValue > 0 ? '+' + transposeValue : transposeValue;
        transposeChords(transposeValue);
        localStorage.setItem('chordTranspose', transposeValue);
    }

    function transposeChords(semitones) {
        const chords = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
        const allChords = document.querySelectorAll('.chord');
        
        allChords.forEach(chord => {
            const text = chord.textContent.replace(/[\[\]]/g, '').trim();
            if (!text) return;

            const parts = text.split('/');
            let transposed = [];

            parts.forEach((part, idx) => {
                part = part.trim();
                let base = part;
                let suffix = '';

                // Extract base chord and modifiers
                const match = part.match(/^([A-G]#?)(.*)$/);
                if (match) {
                    base = match[1];
                    suffix = match[2];
                }

                const index = chords.indexOf(base);
                if (index !== -1) {
                    const newIndex = (index + semitones + 120) % 12;
                    transposed.push(chords[newIndex] + suffix);
                } else {
                    transposed.push(part);
                }
            });

            chord.textContent = '[' + transposed.join('/') + ']';
        });
    }

    // Toggle Chords
    document.getElementById('toggleChords').addEventListener('click', function() {
        chordsHidden = !chordsHidden;
        const chords = document.querySelectorAll('.chord');
        const btn = this;

        chords.forEach(chord => {
            if (chordsHidden) {
                chord.style.display = 'none';
            } else {
                chord.style.display = 'inline';
            }
        });

        if (chordsHidden) {
            btn.classList.add('active');
            btn.innerHTML = '<i class="bi bi-eye-slash"></i> Chords';
        } else {
            btn.classList.remove('active');
            btn.innerHTML = '<i class="bi bi-eye"></i> Chords';
        }

        localStorage.setItem('chordsHidden', chordsHidden);
    });

    // Share Button
    document.getElementById('shareBtn').addEventListener('click', function() {
        const url = window.location.href;
        const title = document.querySelector('h1').textContent;

        if (navigator.share) {
            navigator.share({
                title: title,
                url: url
            });
        } else {
            const shareText = 'Check out this song: ' + title + '\n' + url;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(shareText);
                alert('Link copied to clipboard!');
            }
        }
    });

    // Songbook Button
    const songbookBtn = document.getElementById('songbookBtn');
    if (songbookBtn) {
        songbookBtn.addEventListener('click', function() {
            const songId = <?= (int) $songId ?>;
            fetch('<?= e(url('public/api/songbook.php')) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'toggle',
                    song_id: songId
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.added) {
                        songbookBtn.innerHTML = '<i class="bi bi-bookmark-fill"></i>';
                    } else {
                        songbookBtn.innerHTML = '<i class="bi bi-bookmark"></i>';
                    }
                }
            });
        });
    }

    // Load saved preferences
    const savedFontLevel = localStorage.getItem('chordFontLevel');
    if (savedFontLevel) {
        fontLevel = parseInt(savedFontLevel);
        updateFontSize();
    }

    const savedTranspose = localStorage.getItem('chordTranspose');
    if (savedTranspose) {
        transposeValue = parseInt(savedTranspose);
        document.getElementById('transposeDisplay').textContent = transposeValue > 0 ? '+' + transposeValue : transposeValue;
        transposeChords(transposeValue);
    }

    const savedChordsHidden = localStorage.getItem('chordsHidden');
    if (savedChordsHidden === 'true') {
        document.getElementById('toggleChords').click();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
