<?php
define('DEBUG_MODE', 0);

include 'System/functions.php';
require 'System/Utils/AutoLoader.php';

$autoloader = new \XPBot\System\Utils\AutoLoader('XPBot\\', './');
$autoloader->register();

$settings[] = ['from' => [], 'to' => []];

if(PHP_SAPI == 'cli') {
    if($argc < 3) {
        echo <<<USAGE
This script will migrate your roster from one account to another, and inform about that migration all your contacts.

Usage:

php migrate.php from@first.net:password to@second.org:password [-m|--message "Message"|no] [-s|--status "Status"|no]

Specified message will be sent to your contacts.
Status will be set to specified message, but there is no warranty that server will sent it to your friends. And you can't log in to this account anymore.
If you don't want to send message or set status, simply provide no to it.
On both you can use %old which will be replaced with your old jid and %new whick will be replaced with your new jid.
USAGE;
        exit;
    }

    if(!preg_match('/([^@\/\"\'\s\&\:><]+)\@([a-z_\-\.]*[a-z]{2,3}):(.*)/', $argv[1], $matches)) die('Please provide valid jid, password pair as first argument.');
    $settings['from'] = ['name' => $matches[1], 'server' => $matches[2], 'password' => $matches[3]];

    if(!preg_match('/([^@\/\"\'\s\&\:><]+)\@([a-z_\-\.]*[a-z]{2,3}):(.*)/', $argv[2], $matches)) die('Please provide valid jid, password pair as second argument.');
    $settings['to']   = ['name' => $matches[1], 'server' => $matches[2], 'password' => $matches[3]];


    if(($key = (array_search('-m', $argv)|array_search('--message', $argv))) && isset($argv[$key + 1]))
        $settings['message'] = $argv[$key + 1] == 'no' ? false : $argv[$key + 1];
    else
        $settings['message'] = 'Cześć! Zmieniłem swoje JID na: %new. Dodaj je do swoich kontaktów, a stare usuń ;)';

    if(($key = (array_search('-s', $argv)|array_search('--status', $argv))) && isset($argv[$key + 1]))
        $settings['status'] = $argv[$key + 1] == 'no' ? false : $argv[$key + 1];
    else
        $settings['status'] = 'Zmieniłem swoje JID na: %new. Dodaj je do swoich kontaktów, a stare usuń ;)';
}

$from = new \XPBot\System\Xmpp\XmppClient(new \XPBot\System\Xmpp\Jid($settings['from']['name'], $settings['from']['server'], 'migration'), $settings['from']['password']);
$from->connect();

$to = new \XPBot\System\Xmpp\XmppClient(new \XPBot\System\Xmpp\Jid($settings['to']['name'], $settings['to']['server'], 'migration'), $settings['from']['password']);
$to->connect();

$from->roster->onItem->add(function ($item) use ($settings, $from, $to) {
    $to->roster->add($item->jid, $item->name, $item->groups);

    if($settings['message'])
        $from->message($item->jid, str_replace(['%old', '%new'], [$from->jid->bare(), $to->jid->bare()], $settings['message']));

    if($settings['status'])
        $from->presence('unavailable', str_replace(['%old', '%new'], [$from->jid->bare(), $to->jid->bare()], $settings['status']));
});

$from->roster->onComplete->add(function ($roster) {
    exit;
});

try {
    while(true) {
        $from->process();
        $to->process();

        usleep(100);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}