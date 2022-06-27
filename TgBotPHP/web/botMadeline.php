<?
// Подключаем библиотеку с классом Bot
include_once 'myBotApi/Bot.php';

// Подключаем библиотеку с классом ImgBB
include_once 'myBotApi/ImgBB.php';

// Подключаем библиотеку с глобальными переменными
include_once 'a_conect.php';

//exit('ok');

$token = $tokenMadeline;

// Создаем объект бота
$bot = new Bot($token);

$id_bota = strstr($token, ':', true);	

$imgBB = new ImgBB($api_key);

// Группа администрирования бота (Админка)
//$admin_group = $admin_group_Madeline;

// Подключение БД
$mysqli = new mysqli($host, $username, $password, $dbname);

// проверка подключения 
if (mysqli_connect_errno()) {
	$bot->sendMessage($master, 'Чёт не выходит подключиться к MySQL');	
	exit('ok');
}else { 	

	// ПОДКЛЮЧЕНИЕ ВСЕХ ОСНОВНЫХ ФУНКЦИЙ
	include 'BiblaMadeline/Functions.php';	
	
	// ПОДКЛЮЧЕНИЕ ВСЕХ ОСНОВНЫХ ПЕРЕМЕННЫХ
	include 'myBotApi/Variables.php';
	
	//$bot->sendMessage($master, $bot->PrintArray($data)); 
		
	// Обработчик исключений
	set_exception_handler('exception_handler');
	
	//if ($chat_type == 'private' && !$from_is_bot) $bot->add_to_database($table_users);
	
	if (!$from_username && $chat_type == 'private') {
 
		$bot->sendMessage($chat_id, "Мы не работаем с клиентами без @username!\n\n".
			"Возвращайтесь когда поставите себе @username..\n\n\n[Как установить юзернейм?](https://t.me/podrobno_s_PZP/924)", markdown);
		exit('ok');		
			
	}
	
	// Если пришла ссылка типа t.me//..?start=123456789
	if (strpos($text, "/start ")!==false) $text = str_replace ("/start ", "", $text);
	
	if ($text == "/start"||$text == "s"||$text == "S"||$text == "с"||$text == "С"||$text == "c"||$text == "C"||$text == "Старт"||$text == "старт") {
		if ($chat_type=='private') {
			_старт_МаделинБота();  			
		}	
	}
	
	if ($chat_type == 'private' && $reply_markup['inline_keyboard'][0][0]['text'] == 'Подробнее') {
		//include_once 'BiblaMadeline/Data_transfer.php';
		exit('ok');
	}

	if ($chat_type == 'private' || $chat_id == $admin_group || $chat_id == $channel_info) {
		
		if ($data['callback_query']) {
		
			include_once 'BiblaMadeline/Callback_query.php';
			
		
		}elseif ($data['channel_post']) {
			
			include_once 'BiblaMadeline/Channel_post.php';
			
		}elseif ($data['edited_message']) {
		
			//include_once 'BiblaMadeline/Edit_message.php';		
		
		// если пришло сообщение MESSAGE подключается необходимый файл
		}elseif ($data['message']) {
			
			//-----------------------------
			// это команды бота для мастера
			if ($text){
				$number = stripos($text, '%');
				if ($number!==false&&$number == '0') {
					if ($chat_id==$master) {
						$text = substr($text, 1);
						include_once 'BiblaMadeline/Commands.php';
						exit('ok');
					}
				}
			}
			//-----------------------------
					
			include_once 'BiblaMadeline/Message.php';		
			
		}

	}
	
	if ($inline_query) {
	
		include_once 'BiblaMadeline/Inline_query.php';
	
	}
	
}

// закрываем подключение 
$mysqli->close();		


exit('ok'); //Обязательно возвращаем "ok", чтобы телеграмм не подумал, что запрос не дошёл
?>
