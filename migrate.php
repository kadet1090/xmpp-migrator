<?php
define('DEBUG_MODE', 0);

$mid = '';

function info($type, $message) {
    global $mid;

    if(PHP_SAPI != 'cli') {
        file_put_contents('logs/'.$mid, "<div class=\"alert alert-$type\">$message</div>\n", FILE_APPEND);
    } else {
        echo "[$type] $message";
    }
}

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

    if(in_array('-nr', $argv))
        $settings['roster'] = false;
    else
        $settings['roster'] = true;

    if(in_array('-nv', $argv))
        $settings['vcard'] = false;
    else
        $settings['vcard'] = true;
} else {
    if(!preg_match('/([^@\/\"\'\s\&\:><]+)\@([a-z_\-\.]*[a-z]{2,3}):(.*)/', $_POST['from'], $matches)) info('danger', 'Podaj poprawne jid, z którego chcesz migrować.');
    $settings['from'] = ['name' => $matches[1], 'server' => $matches[2], 'password' => $matches[3]];

    if(!preg_match('/([^@\/\"\'\s\&\:><]+)\@([a-z_\-\.]*[a-z]{2,3}):(.*)/', $_POST['to']  , $matches)) info('danger', 'Podaj poprawne jid, na które chcesz migrować.');
    $settings['to']   = ['name' => $matches[1], 'server' => $matches[2], 'password' => $matches[3]];

    if(!empty($_POST['message']))
        $settings['message'] = $_POST['message'] == 'no' ? false : $_POST['message'];
    else
        $settings['message'] = 'Cześć! Zmieniłem swoje JID na: %new. Dodaj je do swoich kontaktów, a stare usuń ;)';

    if(!empty($_POST['status']))
        $settings['status'] = $_POST['status'] == 'no' ? false : $_POST['status'];
    else
        $settings['status'] = 'Zmieniłem swoje JID na: %new. Dodaj je do swoich kontaktów, a stare usuń ;)';

    if(!empty($_POST['roster']))
        $settings['roster'] = $_POST['roster'] == 'no' ? false : true;
    else
        $settings['roster'] = true;

    if(!empty($_POST['vcard']))
        $settings['vcard'] = $_POST['vcard'] == 'no' ? false : true;
    else
        $settings['vcard'] = true;

    $mid = md5($_POST['from'].':'.$_POST['to'].':'.time());
    touch('logs/'.$mid);
    echo $mid;

    // get the size of the output
    $size = ob_get_length();

    // send headers to tell the browser to close the connection
    header("Content-Length: $size");
    header('Connection: close');
    sleep(1);

    // flush all output
    ob_end_flush();
    ob_flush();
    flush();
}

$done = false;

$from = new \XPBot\System\Xmpp\XmppClient(new \XPBot\System\Xmpp\Jid($settings['from']['name'], $settings['from']['server'], 'migration'), $settings['from']['password']);
$from->connect();

$to = new \XPBot\System\Xmpp\XmppClient(new \XPBot\System\Xmpp\Jid($settings['to']['name'], $settings['to']['server'], 'migration'), $settings['from']['password']);
$to->connect();

$from->onAuth->add(function ($result) {
    if ($result->xml->getName() != 'success') {
        info('danger', 'Nie udało się zalogować na konto, z którego migrujesz, na pewno podałeś poprawne dane logowania?');
        exit;
    }
});

$to->onAuth->add(function ($result) {
    if ($result->xml->getName() != 'success') {
        info('danger', 'Nie udało się zalogować na konto, na którego migrujesz, na pewno podałeś poprawne dane logowania?');
        exit;
    }
});

$from->roster->onItem->add(function ($item) use ($settings, $from, $to, $mid) {
    if($settings['roster'])
        $to->roster->add($item->jid, $item->name, $item->groups);

    if($settings['message'])
        $from->message($item->jid, str_replace(['%old', '%new'], [$from->jid->bare(), $to->jid->bare()], $settings['message']));

    if(file_exists('logs/'.$mid)) touch('logs/'.$mid);
});

$from->roster->onComplete->add(function ($roster) use ($mid, $from, $to, $settings, &$done) {
    if($settings['roster'])
        info('success', 'Przeniesiono wszystkie kontakty.');
    if($settings['message'])
        info('success', 'Rozesłano wiadomości.');
    if($settings['status']) {
        $from->presence('unavailable', str_replace(['%old', '%new'], [$from->jid->bare(), $to->jid->bare()], $settings['status']));
        info('success', 'Ustawiono opis.');
    }

    if(!$settings['vcard']) $done = true;
    else {
        $xml = new \XPBot\System\Utils\XmlBranch('iq');
        $id = uniqid('vcard_');
        $xml->addAttribute('id', $id);
        $xml->addAttribute('type', 'get');
        $xml->addChild(new \XPBot\System\Utils\XmlBranch('vCard'))->addAttribute('xmlns', 'vcard-temp');

        $from->write($xml);
        $from->wait('iq', $id, function ($stanza) use($to, &$done) {
            if($stanza['type'] == 'result') {
                $xml = new \XPBot\System\Utils\XmlBranch('iq');
                $xml->addAttribute('type', 'set');
                $xml->addChild(new \XPBot\System\Utils\XmlBranch('vCard'))->addAttribute('xmlns', 'vcard-temp');
                $xml->vCard[0]->setContent(SimpleXMLElement_innerXML($stanza->vCard), false);
                $to->write($xml);
                info('success', 'Przeniesiono vCard.');
            } else {
                info('warning', 'Nie udało się pobrać vCard');
            }
            $done = true;
        });
    }

});

try {
    while(!$done) {
        $from->process();
        $to->process();
        usleep(100);
    }
} catch (Exception $e) {
    info('danger', $e->getMessage());
}

unset($from); unset($to);
sleep(10);
//if(file_exists('logs/'.$mid)) unlink('logs/'.$mid);
info('success', 'Migracja zakończona.');