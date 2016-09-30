<?php
spl_autoload_register(function ($class) {
    $prefix = 'Glorea\\TweetBlog\\';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if(strncmp($prefix, $class, $len) !== 0) return;

    $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, $len)) . '.php';

    if (file_exists($file)) require $file;
});

require __DIR__ . '/Initializer.php';
