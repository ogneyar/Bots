<?php
//include_once '../vendor/autoload.php';
//include_once 'a_conect.php';

if (!file_exists('madeline.php')) {
    copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
}
include_once 'madeline.php';

$MP = new \danog\MadelineProto\API('session.madeline');
$MP->start();

$me = $MP->get_self();
$MP->logger($me);
$me = print_r($me, true);
$MP->messages->sendMessage(['peer' => '@Ogneyar_ya', 'message' => $me]);






?>
