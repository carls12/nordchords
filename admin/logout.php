<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

logout_admin();
flash_set('success', t('logged_out'));
redirect(url('admin/login.php'));
