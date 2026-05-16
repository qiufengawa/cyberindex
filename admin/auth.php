<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/core/config.php';
require_once ROOT_PATH . '/core/session.php';
require_once ROOT_PATH . '/core/db.php';
require_once ROOT_PATH . '/core/functions.php';

initSession();

function requireAuth(): void
{
    if (empty($_SESSION['admin_id'])) {
        redirect('/admin/?action=login');
    }
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']);
}
