<?php
function __autoload($class)
{
    // nome completo desse arquivo
    $arquivo = __FILE__;
    // nome desta pasta
    $pasta   = str_replace('autoload.php', '', $arquivo);
    // nome do aruqivo da classe a ser includa
    $arquivo = "{$pasta}/{$class}.class.php";

    if(file_exists($arquivo))
        require_once $arquivo;
    else
    	throw new Exception("Class \"{$arquivo}\" Not Found.");
}

function d($v, $vardump=false) {
    echo '<pre>';
    if ($vardump) {
        var_dump($v);
    } else {
        print_r($v);
    }
    echo '</pre>';
}