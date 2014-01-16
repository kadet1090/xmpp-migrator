<?php
if(empty($_GET['mid'])) {
    header('HTTP/1.1 500 Internal Server Error');
    exit;
}

if(file_exists('./logs/'.$_GET['mid'])) {
    echo file_get_contents('./logs/'.$_GET['mid']);
    if(filemtime('./logs/'.$_GET['mid']) > 60)
        unlink('./logs/'.$_GET['mid']);
} else {
    header('HTTP/1.1 500 Internal Server Error');
    exit;
}