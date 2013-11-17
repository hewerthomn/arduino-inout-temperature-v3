<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'inout-temperature');
define('DB_USER', 'root');
define('DB_PASS', 'joao');

define('API_KEY', 'b790f2291d7428ee');

setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");
date_default_timezone_set('America/Porto_Velho');

$refresh = 300; // 3 minutes

$btns = ['primary', 'success', 'info', 'danger', 'warning'];
$btnLink = $btns[rand(0, count($btns)-1)];
$btnInfo = $btns[rand(0, count($btns)-1)];

$themes = [
'amelia', 'cerulean', 'cosmo', 'cyborg', 'flatly', 'journal',
'readable', 'simlex', 'slate', 'spacelab', 'united'
];
$theme = $themes[rand(0, count($themes)-1)];

require_once 'classes/autoload.php';