<?
// Подключаем библиотеку с классом Bot
include_once 'icqNew-BotApi-php/Bot.php';

$token = getenv("TOKEN");

exit('ok');

// Создаем объект бота
$bot = new Bot($token);

$id_bota = substr(strstr($token, ':'), 1);	

$eventId = 0;

$bot->sendText("@Ogneyar_", "Цикл начат.");

do {
	$события = $bot->getEvents($eventId,15);

	foreach($события as $event) {
		$eventId = $event['eventId'];
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
	
	if (!$eventId) $eventId = 0;
	
	echo "Последнее событие: ".$eventId."<br><br>";
	
	if ($text=='ё') {
		$bot->sendText($chatId, "клмн");
	}elseif ($text=='/start') {
		$реплика = "Здравствуй ".$firstName."\n\nПопробуй команду /help";
		$bot->sendText($chatId, $реплика);
	}elseif ($text=='/help') {
		$реплика = "Список понимаемых мною команд:\n\nё\nПривет\nКак дела?\nеее\nуфь";
		$bot->sendText($chatId, $реплика);
	}elseif ($text=='Привет') {
		$реплика = "Сам ты привет. И брат твой привет. И сестра твоя привет.";
		$bot->sendText($chatId, $реплика);
	}elseif ($text=='Как дела?') {
		$реплика = "Дааа норм чо, а #сам_чо_как?";
		$bot->sendText($chatId, $реплика);
	}elseif ($text=='еее') {
		$реплика = "Так держать хозяин!";
		$bot->sendText($chatId, $реплика);
	}elseif ($text=='уфь') {
		$реплика = "Эт ты на каком языке? Уууфь, нет такой буквы в алфавите!";
		$bot->sendText($chatId, $реплика);
	}elseif ($text) $bot->sendText($chatId, "я не понимаю(");

}while ($eventId);

echo "Цикл окончен. <br><br>";
$bot->sendText("@Ogneyar_", "Цикл окончен.");

echo "<pre>"; print_r($события); echo "</pre><br><br>";

exit('ok');

?>
