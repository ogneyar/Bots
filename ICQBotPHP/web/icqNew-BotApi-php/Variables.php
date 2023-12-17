<?
// Обрабатываем пришедшие данные
$data = $bot->init('php://input');

// Вывод на печать JSON файла 
$bot->sendText($test_group, $bot->PrintArray($data)); 

$events = $data['events'];

foreach($events[0] as $event) {
	$lastEvent = $event['eventId'];
	$payload = $event['payload'];
		$chat = $payload['chat'];
			$chatId = $chat['chatId'];
			$chatType = $chat['type'];
		$from = $payload['from'];
			$firstName = $from['firstName'];
			$nick = $from['nick'];
			$userId = $from['userId'];
		$msgId = $payload['msgId'];
		$text = $payload['text'];
		$timestamp = $payload['timestamp'];
	$type = $event['type'];
}

?>
