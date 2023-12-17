<?
// Подключаем библиотеку с классом Bot
include_once 'InfoUsersBibla/Bot.php';
// Подключаем библиотеку с глобальными переменными
include_once 'a_conect.php';
//exit('ok');
$token = $tokenInfoUsers;

// Создаем объект бота
$bot = new Bot($token);

$id_bota = strstr($token, ':', true);	

// Группа администрирования бота (Админка)
$admin_group = $admin_group_InfoUsers;

// ФЛАГ ДЛЯ ВКЛЮЧЕНИЯ РЕЖИМА ОТЛАДКИ БОТА
$OtladkaBota = 'да';

// Подключение БД
$mysqli = new mysqli($host, $username, $password, $dbname);

// проверка подключения 
if (mysqli_connect_errno()) {
	$bot->sendMessage($master, 'Чёт не выходит подключиться к MySQL');	
	exit('ok');
}else { 	

	// ПОДКЛЮЧЕНИЕ ВСЕХ ОСНОВНЫХ ФУНКЦИЙ
	include 'InfoUsersBibla/Functions.php';	
	
	// ПОДКЛЮЧЕНИЕ ВСЕХ ОСНОВНЫХ ПЕРЕМЕННЫХ
	include 'InfoUsersBibla/Variables.php';
	
	// Обработчик исключений
	set_exception_handler('exception_handler');
	
	//$text = str_replace ("@TesterBotoffBot", "", $text);	
	
	//$this_admin = _this_admin();
	
	if ($text == "/start"||$text == "s"||$text == "S"||$text == "с"||$text == "С"||$text == "c"||$text == "C"||$text == "Старт"||$text == "старт") {
		if ($chat_type=='private') {
			_start_InfoUsers_bota();  			
		}	
	}
	
	if ($data['callback_query']) {
	
		include_once 'InfoUsersBibla/Callback_query.php';
	
	// если пришло сообщение MESSAGE подключается необходимый файл
	}elseif ($data['message']) include_once 'InfoUsersBibla/Message.php';		
}

// закрываем подключение 
$mysqli->close();		


exit('ok'); //Обязательно возвращаем "ok", чтобы телеграмм не подумал, что запрос не дошёл
?>
