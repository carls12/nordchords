<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/user_auth.php';

logout_user();
flash_set('success', t('logged_out'));
redirect(url('public/login.php'));
