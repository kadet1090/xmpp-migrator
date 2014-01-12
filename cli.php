<?php
define('DEBUG_MODE', 0);

include 'System/functions.php';
require 'System/Utils/AutoLoader.php';

$autoloader = new \XPBot\System\Utils\AutoLoader('XPBot\\', './');
$autoloader->register();

$from = new \XPBot\System\Xmpp\XmppClient(new \XPBot\System\Xmpp\Jid($argv[1]), $argv[2]);
$from->connect();

$to = new \XPBot\System\Xmpp\XmppClient(new \XPBot\System\Xmpp\Jid($argv[3]), $argv[4]);
$to->connect();

while(true) {
    $from->process();
    $to->process();


    usleep(100);
}