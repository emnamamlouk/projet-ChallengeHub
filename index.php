<?php
if ($_SERVER['REQUEST_URI'] === '/public/favicon.svg') {
    header('Content-Type: image/svg+xml');
    readfile('path/to/your/favicon.svg');
    exit;
}
