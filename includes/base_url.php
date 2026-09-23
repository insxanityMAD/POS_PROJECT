<?php
declare(strict_types=1);

// Computes the app's URL prefix (e.g. "/POS-PROJECT") by diffing the project
// root's filesystem path against the web server's document root, so links
// keep working no matter what the project folder is named or nested under.
// Deliberately has no dependency on config.php/the database, since login.php
// and staff_panel.php need it before (or without) connecting to the DB.
if (!defined('BASE_URL')) {
    $appRoot = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
    define('BASE_URL', ($docRoot !== '' && str_starts_with($appRoot, $docRoot)) ? substr($appRoot, strlen($docRoot)) : '');
}
