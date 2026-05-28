<?php
declare(strict_types=1);


define('DB_HOST', 'localhost');
define('DB_NAME', 'newssite');
define('DB_USER', 'root');          
define('DB_PASS', '');  
define('DB_CHARSET', 'utf8mb4');


define('UPLOAD_DIR',      __DIR__ . '/../uploads/');
define('UPLOAD_URL', '/newssite/uploads/');
define('MAX_FILE_SIZE',   2 * 1024 * 1024);           
define('ALLOWED_MIME',    ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_EXT',     ['jpg', 'jpeg', 'png', 'webp']);


define('MAX_HEADLINE_LEN', 100);
define('MAX_CONTENT_LEN',  10000);
define('MAX_USERNAME_LEN', 20);


define('SESSION_NAME', 'ns_session');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');


define('BASE_URL', 'http://localhost/newssite');  