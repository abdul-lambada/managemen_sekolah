<?php

$target = 'public/index.php';

if (!empty($_SERVER['QUERY_STRING'])) {
    $target .= '?' . $_SERVER['QUERY_STRING'];
}

header('Location: ' . $target);
exit;
