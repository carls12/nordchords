<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function render_chord_text(string $text): string
{
    $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
    $htmlLines = [];

    foreach ($lines as $line) {
        if (trim($line) === '') {
            $htmlLines[] = '<div class="lyric-line">&nbsp;</div>';
            continue;
        }

        $escaped = e($line);
        $parsed = preg_replace('/\[([^\]]+)\]/', '<span class="chord">[$1]</span>', $escaped);
        $htmlLines[] = '<div class="lyric-line">' . $parsed . '</div>';
    }

    return implode("\n", $htmlLines);
}
