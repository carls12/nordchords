<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'chord_app';
const DB_USER = 'root';
const DB_PASS = '';

const APP_NAME = 'Chord App';
const BASE_URL = '/nordchords';
const ADMIN_SESSION_KEY = 'admin_id';
const USER_SESSION_KEY = 'user_id';
const APP_DEFAULT_LANGUAGE = 'en';
const LANGUAGE_COOKIE_KEY = 'app_lang';
const SUPPORTED_LANGUAGES = [
    'en' => 'English',
    'de' => 'Deutsch',
    'es' => 'Espanol',
    'fr' => 'Francais',
];
