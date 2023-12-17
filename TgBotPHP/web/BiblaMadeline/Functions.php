<?
include 'Functions_mysqli.php'; // подключение функций работающих с таблицами

/* Список всех функций:
**
** exception_handler	-	// функция отлова исключений
**
** ----------------------
** _старт_АвтоЗаказБота
** _инфо_автоЗаказБота
** ----------------------
**
** ---------------------
** _создать
** _продам_куплю
** _ссылка_в_названии
** _выбор_категории
** _выбор_валюты
** _когда_валюта_выбрана
** _ввод_местонахождения
** _отправьте_файл
** _нужен_ли_фотоальбом
** _опишите_подробно
** _на_публикацию
** _изменить_подробности
** _ожидание_результата
** ----------------------
**
** _отправка_лота_админам  // для редактирования и опубликования
**
** ----------------------
** _повтор
** _отправить_на_повтор
** _удаление
** _удалить_выбранный_лот
** ----------------------
**
** _вывод_лота_на_каналы
** _публикация_на_канале_медиа
** _отправка_сообщений_инфоботу
** _отказать
**
** -----------------------------------
** _редакт_лота_на_канале_подробности
** -----------------------------------
*/

// при возникновении исключения вызывается эта функция
function exception_handler($exception) {
	global $bot, $master;	
	$bot->sendMessage($master, "Ошибка! ".$exception->getCode()." ".$exception->getMessage());	  
	exit('ok');  	
}

// функция старта бота ИНФОРМАЦИЯ О ПОЛЬЗОВАТЕЛЯХ
function _старт_АвтоЗаказБота() {		
	global $bot, $table_users, $chat_id, $callback_from_first_name, $from_first_name, $HideKeyboard;	
	_очистка_таблицы_ожидание();	
	if (!$callback_from_first_name) $callback_from_first_name = $from_first_name;	
	$админ = $bot->this_admin($table_users);	
	if ($админ) {		
		$ReplyKey = [ 'keyboard' => 
			[ [ [ 'text' => "Редактор лотов" ] ] ],
			'resize_keyboard' => true,
			'selective' => true,
		];	
		$bot->sendMessage($chat_id, "Здравствуй МАСТЕР *".$callback_from_first_name."*!", markdown, $ReplyKey);	
	}else {	
		$bot->sendMessage($chat_id, "Добро пожаловать, *".$callback_from_first_name."*!", markdown, $HideKeyboard);		
	}
    _инфо_автоЗаказБота();		
	exit('ok');	
}

// Краткая информация, перед началом работы с ботом
function _инфо_автоЗаказБота() {
	global $bot, $chat_id, $тех_поддержка;	
	$клавиатура = [
		[ [		
			'text' => 'Правила',
			'url' => 'https://t.me/podrobno_s_PZP/562'		
		] ],
		[ [		
			'text' => 'Создать заявку',
			'callback_data' => 'создать'		
		] ]
	];	
	if (_есть_ли_лоты()) {	
		$клавиатура = array_merge($клавиатура, [
			[ [
				'text' => 'Повтор публикации',
				'callback_data' => 'повторить'
			],[
				'text' => 'Удалить публикацию',
				'callback_data' => 'удалить'
			] ]
		]);		
	}		
	$inLine = [ 'inline_keyboard' => $клавиатура ];	
	$reply = "Это Бот для подачи заявки на публикацию вашего лота на канале [Покупки на PRIZMarket]".
		"(https://t.me/prizm_market)\n\nДля подачи заявки на публикацию пошагово пройдите".
		" по всем пунктам. Для начала изучите 'Правила', а затем нажмите кнопку ".
		"'Создать заявку'. После создания заявки появится возможность повтора публикации.{$тех_поддержка}";
	$bot->sendMessage($chat_id, $reply, markdown, $inLine, null, true);
}

//------------------------------------------
// Начало создания заявки на публикацию лота 
//------------------------------------------
function _создать() {
	global $bot, $from_id, $message_id, $callback_query_id, $callback_from_id;		
	$давно = _последняя_публикация();	
	if ($давно) {	
		if (!$callback_from_id) {		
			$callback_from_id = $from_id;			
		}else $bot->answerCallbackQuery($callback_query_id, "Начнём!");	
	}else {		
		if ($callback_query_id) {			
			$bot->answerCallbackQuery($callback_query_id, "Безоплатно можно публиковать только раз в сутки один лот!", true);			
		}		
		exit('ok');		
	}	
	_запись_в_таблицу_маркет();	
	_очистка_таблицы_медиа();	
	$inLine = [
		'inline_keyboard' => [
			[
				[ 'text' => '#продам', 'callback_data' => 'продам' ],
				[ 'text' => '#куплю', 'callback_data' => 'куплю' ]
			]
		]
	];
	$reply = "|\n|\n|\n|\n|\n|\n|\n|\nВыберите необходимое действие:";	
	$bot->sendMessage($callback_from_id, $reply, null, $inLine);
}

// Обработка выбранного действия ПРОДАМ/КУПЛЮ
function _продам_куплю($действие) {
	global $bot, $message_id, $callback_query_id, $callback_from_id, $клавиатура_отмена_ввода;	
	_запись_в_таблицу_маркет($callback_from_id, 'kuplu_prodam', $действие);	
	_ожидание_ввода('nazvanie', 'kuplu_prodam');	
	$bot->answerCallbackQuery($callback_query_id, "Ожидаю ввод названия!");		
	$reply = "Введите название:";	
	$bot->sendMessage($callback_from_id, $reply, null, $клавиатура_отмена_ввода);	
}

// После ввода клиентом НАЗВАНИЯ предлагается на выбор, нужнали ссылка вшитая в название
function _ссылка_в_названии() {
	global $bot, $chat_id;	
	$inLine = [
		'inline_keyboard' => [ [
				[ 'text' => 'Да', 'callback_data' => 'нужна_ссылка' ],
				[ 'text' => 'Нет', 'callback_data' => 'не_нужна_ссылка' ]
		] ]
	];	
	$reply = "|\n|\n|\n|\n|\n|\n|\n|\nНужна ли Вам Ваша ссылка, вшитая в названии?\n\nЕсли не знаете или не поймёте о чём речь, нажмите 'НЕТ'.";	
	$bot->sendMessage($chat_id, $reply, null, $inLine);
}

// выбор категории в которой будет находиться лот?
function _выбор_категории() {
	global $bot, $chat_id, $категории;			
	$список_категорий = [];						
	for ($i=0; $i<12; $i++) {				
		$список_категорий = array_merge($список_категорий, [ [
			[ 'text' => $категории[$i], 'callback_data' => $категории[$i] ],
			[ 'text' => $категории[$i+1], 'callback_data' => $категории[$i+1] ]
		] ]);		
		$i++;		
	}	
	$inLine = [	
		'inline_keyboard' => $список_категорий		
	];	
	$reply = "Выберите категорию, к которой больше всего подходит Ваш товар/услуга.";	
	$bot->sendMessage($chat_id, $reply, null, $inLine);
}

// Предлагаются на выбор разные валюты
function _выбор_валюты() {	
	global $bot, $chat_id;	
	$inLine = [ 'inline_keyboard' => [ 
		[	[ 'text' => '₽', 'callback_data' => 'рубль' ],
			[ 'text' => '$', 'callback_data' => 'доллар' ],
			[ 'text' => '€', 'callback_data' => 'евро' ] ],
		[	[ 'text' => '¥', 'callback_data' => 'юань' ],
			[ 'text' => '₴', 'callback_data' => 'гривна' ],
			[ 'text' => '£', 'callback_data' => 'фунт' ] ],
		[ 	[ 'text' => 'Только PRIZM!', 'callback_data' => 'призм' ] ] 
	] ];	
	$reply = "|\n|\n|\n|\n|\n|\n|\n|\nВыберите валюту, с которой Вы работаете, помимо PRIZM.";	
	$bot->sendMessage($chat_id, $reply, null, $inLine);
}

// Когда уже выбрана валюта
function _когда_валюта_выбрана($валюта = null) {	
	global $callback_from_id;	
	if ($валюта) {		
		$валюта.= " / PZM";		
	}else {		
		$валюта = "PZM";		
	}	
	_запись_в_таблицу_маркет($callback_from_id, 'valuta', $валюта);	
	_ввод_местонахождения();	
}

// функция предлагающая клиенту ввести хештеги города
function _ввод_местонахождения() {
	global $bot, $callback_query_id, $callback_from_id, $from_id, $клавиатура_отмена_ввода;
	if (!$callback_from_id) {	
		$callback_from_id = $from_id;	
	}else $bot->answerCallbackQuery($callback_query_id, "Ожидаю ввода местонахождения!");	 	
	_ожидание_ввода('gorod', 'valuta');	
	$reply = "Введите хештеги местонахождения: (не больше трёх)".
		"\n\nВНИМАНИЕ: вводите хештеги БЕЗ пробелов!\n".
		"(#Весь мир 👈🏻 это будет ошибка)\n".		
		"Можно использовать _ (подчёркивание) вместо пробела. Никакие другие ".
		"символы вводить нельзя. В случае неверного ввода команда PRIZMarket ".
		"может отклонить заявку.\n".
		"ОБЯЗАТЕЛЬНО ставьте пробел между двумя разными хештегами!\n".
		"(#Весь_мир#Россия 👈🏻 это будет ошибка)".
		"\n\nПример верного ввода:\n#Весь_мир #Россия #Ростов_на_Дону";	
	$bot->sendMessage($callback_from_id, $reply, null, $клавиатура_отмена_ввода);		
}

// функция предлагает клиенту ввести файл
function _отправьте_файл() {
	global $bot, $chat_id, $клавиатура_отмена_ввода;	
	_ожидание_ввода('format_file', 'gorod');		
	$reply = "Отправьте один файл. \n\nФото или видео. \n\nЕсли пришлёте больше, я приму только один файл,".
		" остальные проигнорирую.\nЕсли Вы хотите скинуть много фото, Вы это сможете сделать чуть позже!\n\n".
		"(учтите: видео должно быть коротким, не более 5 МБ)";	
	$bot->sendMessage($chat_id, $reply, null, $клавиатура_отмена_ввода);		
}

// Спрашивается, нужен ли клиенту фотоальбом, на отдельном канале?
function _нужен_ли_фотоальбом() {
	global $bot, $chat_id;	
	$inLine = [ 'inline_keyboard' => [ [
		[ 'text' => 'Да', 'callback_data' => 'нужен_альбом' ],
		[ 'text' => 'Нет', 'callback_data' => 'не_нужен_альбом' ]
	] ] ];	
	$reply = "|\n|\n|\n|\n|\n|\n|\n|\nНужен ли Вам фотоальбом, размещённый на отдельном канале?\n\nЕсли не знаете или не поймёте о чём речь, нажмите 'НЕТ'.";
	$bot->sendMessage($chat_id, $reply, null, $inLine);
}

// функция предлагающая ввести ПОДРОБНую информацию о товаре/услуге
function _опишите_подробно($хорошо = null) {
	global $bot, $chat_id, $клавиатура_отмена_ввода;	
	if ($хорошо) {		// если хорошо, значит при ОТМЕНЕ просто выводится надпись ХОРОШО
		_ожидание_ввода('podrobno', $хорошо);		
	}else _ожидание_ввода('podrobno', 'foto_album');	
	$reply = "Теперь опишите подробно Ваш товар/услугу для канала Подробности, на который будет ссылаться".
		" Ваш лот.\nСсылки на сайт или соцсети приветствуются.\n(ссылки не должны быть реферальными)\n\nДостаточно подробно. \n\nНо, не переусердствуйте, ведь Вы же не намерены переутомить своих".
		" потенциальных клиентов?\n\nКоличество вводимых символов должно быть не менее 100 и не более 2000.";	
	$bot->sendMessage($chat_id, $reply, null, $клавиатура_отмена_ввода);	
}

// отправка клиентом введённой информации 
function _на_публикацию() {	
	global $callback_query_id, $callback_from_id, $from_id, $bot, $message_id;	
	if (!$callback_from_id) $callback_from_id = $from_id;		
	$давно = _последняя_публикация();	
	if ($давно) {		
		_отправка_лота_админам();
		_ожидание_результата();
		_отправка_сообщений_инфоботу();			
		_запись_в_таблицу_маркет($callback_from_id, 'date', time());		
		$inLine = [
			'inline_keyboard' => [
				[ [ 'text' => 'Отправлено', 'callback_data' => 'отправлено' ] ],
				[ [ 'text' => 'В начало', 'callback_data' => 'старт' ] ]
			]
		];		
		$bot->editMessageReplyMarkup($callback_from_id, $message_id, null, $inLine);		
	}else $bot->answerCallbackQuery($callback_query_id, "Безоплатно можно публиковать только раз в сутки один лот!", true);
}

// возврат к вводу подробной информации о лоте
function _изменить_подробности() { _опишите_подробно('хорошо'); }

// КОНЕЦ - клиент ожидает решения администрации
function _ожидание_результата() { 
	global $bot, $from_id, $callback_from_id;	
	if (!$callback_from_id) $callback_from_id = $from_id;	
	$reply = "|\n|\n|\n|\n|\n|\n|\n|\nОжидайте результат.\n\n(После публикации Вашего лота".
		" Вы будете об этом уведомлены, в случае отказа Вас также, уведомят. Ожидайте..)";	
	$bot->sendMessage($callback_from_id, $reply);
}

// отправка лота администрации на проверку и редактирование
function _отправка_лота_админам($номер_лота = null) {	
	global $table_market, $from_id, $bot, $mysqli, $imgBB, $admin_group;	
	global $callback_from_username, $from_username, $callback_from_id, $from_id;	
	if (!$callback_from_username) $callback_from_username = $from_username;	
	if (!$callback_from_id) $callback_from_id = $from_id;	
	if ($номер_лота) {
		$id = $номер_лота;
		$кнопки = [ [ [ 'text' => 'Применить!',
			'callback_data' => 'применить:'.$id ] ], ];
		$запрос = "SELECT * FROM {$table_market} WHERE id_zakaz={$id}";	
	}else {
		$id = $callback_from_id;
		$кнопки = [ [ [ 'text' => 'Опубликовать',
			'callback_data' => 'опубликовать:'.$id ] ], ];
		$запрос = "SELECT * FROM {$table_market} WHERE id_client={$id} AND id_zakaz=0";	
	}
	$кнопки = array_merge($кнопки, [		
		[ [ 'text' => 'PRIZMarket доверяет',
			'callback_data' => 'доверяет:'.$id ] ],
		[ [ 'text' => 'PRIZMarket НЕ доверяет',
			'callback_data' => 'не_доверяет:'.$id ] ],
		[ [ 'text' => 'Редактировать название',
			'callback_data' => 'редактировать_название:'.$id ] ],
	]);				
	$результат = $mysqli->query($запрос);	
	if ($результат) {		
		if ($результат->num_rows == 1) {		
			$результМассив = $результат->fetch_all(MYSQLI_ASSOC);			
			foreach ($результМассив as $строка) {			
				$файлАйди = $строка['file_id'];		
				$формат_файла = $строка['format_file'];				
				$название = $строка['nazvanie'];
				if ($строка['url_nazv']) {					
					$ссыль_в_названии = $строка['url_nazv'];						
					$название_для_подробностей = "[{$название}]({$ссыль_в_названии})";
					$кнопки = array_merge($кнопки, [
						[ [ 'text' => 'Редактировать ссылку',
							'callback_data' => 'редактировать_ссылку:'.$id ] ],
					]);						
				}else $название_для_подробностей = str_replace('_', '\_', $название);	
				$кнопки = array_merge($кнопки, [
					[ [ 'text' => 'Редактировать хештеги',
						'callback_data' => 'редактировать_хештеги:'.$id ] ],
					[ [ 'text' => 'Редактировать подробности',
						'callback_data' => 'редактировать_подробности:'.$id ] ],
					[ [ 'text' => 'Редактировать фото',
						'callback_data' => 'редактировать_фото:'.$id ] ],	
				]);	
				if ($номер_лота) {
					$кнопки = array_merge($кнопки, [ [ [ 'text' => 'УДАЛИТЬ',
						'callback_data' => 'удаление:'.$id ] ],]);
				}else {
					$кнопки = array_merge($кнопки, [ [ [ 'text' => 'ОТКАЗАТЬ',
						'callback_data' => 'отказать:'.$id ] ],]);
				}
				$inLine = [ 'inline_keyboard' => $кнопки ];				
				$куплю_или_продам = $строка['kuplu_prodam'];						
				$валюта = $строка['valuta'];				
				$хештеги_города = $строка['gorod'];				
				$юзера_имя = $строка['username'];				
				$категория = $строка['otdel'];								
				$подробности = $строка['podrobno'];				
				$хештеги = "{$куплю_или_продам}\n\n{$категория}\n▪️";			
				$хештеги = str_replace('_', '\_', $хештеги);				
				$текст = "\n▪️{$валюта}\n▪️{$хештеги_города}\n▪️{$юзера_имя}\n\n{$подробности}";				
				$текст = str_replace('_', '\_', $текст);								
				if ($ссыль_в_названии) {					
					$ссыль_в_названии = str_replace('_', '\_', $ссыль_в_названии);		
					$текст = "{$хештеги}{$название_для_подробностей}\n({$ссыль_в_названии}){$текст}";		
				}else $текст = "{$хештеги}{$название_для_подробностей}{$текст}";
				if ($формат_файла == 'фото') {	
					if ($номер_лота) {
						$реплика = "[_________]({$строка['url_tgraph']})\n{$текст}";
						$КаналИнфо = $bot->sendMessage($callback_from_id, $реплика, markdown, $inLine);
					}else {
						$Объект_файла = $bot->getFile($файлАйди);				
						$ссыль_на_файл = $bot->fileUrl . $bot->token;
						$ссыль = $ссыль_на_файл . "/" . $Объект_файла['file_path'];
						$результат = $imgBB->upload($ссыль);
						if ($результат) {								
							$imgBB_url = $результат['url'];		
							_запись_в_таблицу_маркет($callback_from_id, 'url_tgraph', $imgBB_url);				
						}else throw new Exception("Не смог выложить пост..");
						$реплика = "[_________]({$imgBB_url})\n{$текст}";
						$КаналИнфо = $bot->sendMessage($admin_group, $реплика, markdown, $inLine);
					}
					
				}else $КаналИнфо = $bot->sendMessage($admin_group, $текст, markdown, $inLine);				
				if (!$КаналИнфо) throw new Exception("Не смог опубликовать лот..");				
			}					
		}else throw new Exception("Или нет заказа или больше одного..");	
	}else throw new Exception("Нет такого заказа..");	
}


// ----------------------------------------------------------------------------------
// функция вывода на экран лота, который необходимо повторить
function _повтор($номер_лота) {	
	global $callback_from_id, $bot, $callback_query_id;	
	$давно = _последняя_публикация();	
	if ($давно) {
		_отправка_лота($callback_from_id, $номер_лота);		
		$inLine = [ 'inline_keyboard' => [ [
			[ 'text' => 'Да', 'callback_data' => "отправить_на_повтор:".$номер_лота ],
			[ 'text' => 'Нет', 'callback_data' => "старт" ]
		] ] ];						
		$bot->sendMessage($callback_from_id, "|\n|\n|\nПовторить? Если хотите повторить публикацию этого лота, нажмите 'Да'.", null, $inLine);	
	}else {		
		if ($callback_query_id) {			
			$bot->answerCallbackQuery($callback_query_id, "Безоплатно можно публиковать только раз в сутки один лот!", true);			
		}		
		exit('ok');		
	}	
}

// функция вывода АДМИНАМ на экран лота, с просьбой о необходимости повторить
function _отправить_на_повтор($номер_лота) {	
	global $admin_group, $bot, $callback_from_id, $callback_query_id;	
	$давно = _последняя_публикация();	
	if ($давно) {	
		_отправка_лота($admin_group, $номер_лота);			
		_установка_времени($номер_лота);		
		$юзер_неим = _узнать_имя_по_номеру_лота($номер_лота);				
		$bot->sendMessage($admin_group, "{$юзер_неим} просит: Повторите публикацию, будьте так любезны, заранее благодарю.");		
		$bot->sendMessage($callback_from_id, "|\n|\n|\nОтправил, ожидайте ответ.");	
		$bot->answerCallbackQuery($callback_query_id, "Ожидайте!");	
	}else {		
		$bot->answerCallbackQuery($callback_query_id, "Безоплатно можно публиковать только раз в сутки один лот!", true);		
		exit('ok');
	}		
}

// функция вывода на экран лота, который необходимо удалить
function _удаление($номер_лота) {	
	global $callback_from_id, $bot;	
	_отправка_лота($callback_from_id, $номер_лота);		
	$inLine = [ 'inline_keyboard' => [ [
		[ 'text' => 'Да', 'callback_data' => "удалить_выбранный_лот:".$номер_лота ],
		[ 'text' => 'Нет', 'callback_data' => "старт" ]
	] ] ];					
	$bot->sendMessage($callback_from_id, "|\n|\n|\nУдалить? Если хотите удалить этот лот из базы нажмите 'Да'.", null, $inLine);
}

// функция удаления лота из базы данных
function _удалить_выбранный_лот($номер_лота) {
	global $table_market, $таблица_медиагруппа, $callback_query_id, $mysqli, $bot, $master;	
	$запрос = "DELETE FROM {$table_market} WHERE id_zakaz='{$номер_лота}'";	
	$результат = $mysqli->query($запрос);	
	if ($результат) {		
		_старт_АвтоЗаказБота();		
		$bot->answerCallbackQuery($callback_query_id, "Лот удалён из базы!");		
		$запрос = "DELETE FROM {$таблица_медиагруппа} WHERE id='{$номер_лота}'";	
		$результат = $mysqli->query($запрос);	
	}else {
		$bot->answerCallbackQuery($callback_query_id, "Не смог удалить лот..");
		throw new Exception("Не смог удалить лот..");	
	}
}
//-----------------------------------------------------------------------------------


// вывод на канал подробности уже готового лота (кнопка у админов ОПУБЛИКОВАТЬ)
function _вывод_лота_на_каналы($id_client, $номер_лота = 0) {
	global $table_market, $bot, $chat_id, $mysqli, $imgBB, $channel_podrobno, $channel_market;
	global $таблица_медиагруппа, $channel_media_market, $master, $message_id, $admin_group, $три_часа;
	_очистка_таблицы_ожидание();	
	$запрос = "SELECT * FROM {$table_market} WHERE id_client={$id_client} AND id_zakaz='{$номер_лота}'";	
	$результат = $mysqli->query($запрос);	
	if ($результат) {		
		if ($результат->num_rows == 1) {		
			$результМассив = $результат->fetch_all(MYSQLI_ASSOC);			
			foreach ($результМассив as $строка) {			
				$файлАйди = $строка['file_id'];		
				$формат_файла = $строка['format_file'];				
				$название = $строка['nazvanie'];				
				if ($строка['url_nazv']) {				
					$ссыль_в_названии = $строка['url_nazv'];						
					$название_для_подробностей = "[{$название}]({$ссыль_в_названии})";
				}else $название_для_подробностей = str_replace('_', '\_', $название);
				$куплю_или_продам = $строка['kuplu_prodam'];
				$валюта = $строка['valuta'];				
				$хештеги_города = $строка['gorod'];				
				$юзера_имя = $строка['username'];				
				$доверие = $строка['doverie'];
				$категория = $строка['otdel'];				
				$подробности = $строка['podrobno'];							
				$хештеги = "{$куплю_или_продам}\n\n{$категория}\n▪️";				
				$хештеги = str_replace('_', '\_', $хештеги);				
				$текст = "\n▪️{$валюта}\n▪️{$хештеги_города}\n▪️{$юзера_имя}";				
				$текст = str_replace('_', '\_', $текст);								
				$количество = substr_count($подробности, '[');				
				if ($количество == 0) {					
					$подробности = str_replace('_', '\_', $подробности);
				}							
				$текст .= "\n\n{$подробности}"; 								
				$текст = "{$хештеги}{$название_для_подробностей}{$текст}";				
				$imgBB_url = $строка['url_tgraph'];	
				if ($imgBB_url) {								
					$реплика = "[_________]({$imgBB_url})\n{$текст}";					
				}else $реплика = $текст;						
				$ссылка_инфобота = $строка['url_info_bot'];								
				$кнопки = [
					[
						[ 'text' => 'О пользователе', 'url' => $ссылка_инфобота ],
						[ 'text' => 'ГАРАНТ', 'url' => 'https://t.me/podrobno_s_PZP/1044' ]
					]
				];				
				if ($строка['foto_album'] == '1') {	
					$ссылка_на_канал_медиа = _публикация_на_канале_медиа($id_client);						
					if ($ссылка_на_канал_медиа) {					
						$кнопки = array_merge($кнопки, [ 
							[ [ 'text' => 'Фото', 'url' => $ссылка_на_канал_медиа ] ]
						]);
					}				
				}				
				$кнопки = array_merge($кнопки, [
					[
						[   'text' => 'INSTAGRAM PRIZMarket',
							'url' => 'https://www.instagram.com/prizm_market_inst' ],
						[ 	'text' => 'PZMarket bot',
							'url' => 'https://t.me/Prizm_market_bot' ]
					],
					[
						[   'text' => 'Заказать пост', 
							'url' => 'https://t.me/Zakaz_prizm_bot' ],
						[ 	'text' => 'Канал PRIZMarket',
							'url' => 'https://t.me/prizm_market/' ]
					]
				]);				
				$inLine = ['inline_keyboard' => $кнопки];						
				$КаналИнфо = $bot->sendMessage($channel_podrobno, $реплика, markdown, $inLine);				
			}			
			if ($КаналИнфо) {									
				$id_zakaz = $КаналИнфо['message_id'];				
				if ($ссылка_на_канал_медиа) {				
					_запись_в_таблицу_медиагрупа($id_client, $id_zakaz, $ссылка_на_канал_медиа);			
				}					
				$ссыль_на_подробности = "https://t.me/{$КаналИнфо['chat']['username']}/{$id_zakaz}";		
				_запись_в_таблицу_маркет($id_client, 'id_zakaz', $id_zakaz);
				if (!$ссыль_в_названии) {					
					_запись_в_таблицу_маркет($id_client, 'url_nazv', $ссыль_на_подробности);				
					$ссыль_в_названии = $ссыль_на_подробности;						
				}					
				_запись_в_таблицу_маркет($id_client, 'url_podrobno', $ссыль_на_подробности);			
				$inLine = [ 'inline_keyboard' => [
					[ [ 'text' => 'Подробнее', 'url' => $ссыль_на_подробности ] ]
				] ];									
				$текст = "\n▪️{$валюта}\n▪️{$хештеги_города}\n▪️{$юзера_имя}";					
				$текст = str_replace('_', '\_', $текст);					
				if ($доверие) $текст .= "\n  лот {$id_zakaz}\n✅ PRIZMarket доверяет❗️"; 
				else $текст .= "\n   лот {$id_zakaz}";
				$текст = "{$хештеги}[{$название}]({$ссыль_в_названии}){$текст}";					
				if ($формат_файла == 'фото') {					
					$публикация = $bot->sendPhoto($admin_group, $файлАйди, $текст, markdown, $inLine);		
				}elseif ($формат_файла == 'видео') {					
					$публикация = $bot->sendVideo($admin_group, $файлАйди, $текст, markdown, $inLine);		
				}					
				if ($публикация) {										
					_запись_в_таблицу_маркет($id_client, 'status', "одобрен");						
				}else throw new Exception("Не смог отправить лот в админку.");						
			}else throw new Exception("Не отправился лот на канал Подробности..");			
		}else throw new Exception("Или нет заказа или больше одного..");				
	}else throw new Exception("Нет такого заказа..");	
	$inLine = [ 'inline_keyboard' => [
		[ [ 'text' => 'Опубликованно в подробностях', 'url' => $ссыль_на_подробности ] ]
	] ];	
	$bot->editMessageReplyMarkup($chat_id, $message_id, null, $inLine);
}

// публикация альбома с фотографиями на отдельном канале
function _публикация_на_канале_медиа($номер_клиента) {	
	global $bot, $master, $таблица_медиагруппа, $mysqli, $channel_media_market;	
	$ответ = false;
	$запрос = "SELECT * FROM {$таблица_медиагруппа} WHERE id_client={$номер_клиента} AND id='0'";		
	$результат = $mysqli->query($запрос);		
	if ($результат) {			
		if ($результат->num_rows > 1) {			
			$результМассив = $результат->fetch_all(MYSQLI_ASSOC);				
			$файл_медиа = [];				
			foreach ($результМассив as $строка) {					
				if ($строка['format_file'] == 'фото') {						
					$тип = 'photo';						
				}elseif ($строка['format_file'] == 'видео') {					
					$тип = 'video';					
				}						
				$медиа = $строка['file_id'];						
				$файл_медиа = array_merge($файл_медиа, [					
					[ 'type' => $тип, 'media' => $медиа	]							
				]);					
			}			
			$результат = $bot->sendMediaGroup($channel_media_market, $файл_медиа);				
			if ($результат) {						
				$ответ = "https://t.me/{$результат[0]['chat']['username']}/{$результат[0]['message_id']}";	
			}			
		}else $bot->sendMessage($master, "Или нет заказа или меньше двух.. (_публикация_на_канале_медиа)");		
	}else $bot->sendMessage($master, "Нет такого заказа.. (_публикация_на_канале_медиа)");		
	return $ответ;	
}

// отправка сообщений инфоботу на его канал, для формирования ссылки "о клиенте"
function _отправка_сообщений_инфоботу() {	
	global $bot, $channel_info;
	global $callback_from_username, $from_username, $callback_from_id, $from_id;	
	if (!$callback_from_username) $callback_from_username = $from_username;	
	if (!$callback_from_id) $callback_from_id = $from_id;	
	$bot->sendMessage($channel_info, "@".$callback_from_username);	
	$bot->sendMessage($channel_info, $callback_from_id);		
}

// Если клиенту отказанно в публикации лота (кнопка у админов ОТКАЗ)
function _отказать($id) {
	global $bot, $callback_query_id, $chat_id, $message_id, $mysqli, $table_market;
	$bot->sendMessage($id, "Вам отказанно. [Читайте правила](https://t.me/podrobno_s_PZP/562).\n\n/start 👈🏻 в главное меню!", markdown, true);	
	$query = "DELETE FROM ".$table_market." WHERE id_client=".$id." AND id_zakaz='0'";
	if ($mysqli->query($query)) {		
		$inLine = [ 'inline_keyboard' => [
				[ [ 'text' => 'Отказанно', 'callback_data' => 'отказанно' ] ] 
		] ];		
		$bot->editMessageReplyMarkup($chat_id, $message_id, null, $inLine);
	}else throw new Exception("Не смог удалить запись в таблице {$table_market}");	
}

//------------------------------------------------
// именение содержимого лота на канале подробности
function _редакт_лота_на_канале_подробности($номер_лота) {
	global $table_market, $bot, $chat_id, $mysqli, $imgBB, $channel_podrobno, $channel_market;
	global $таблица_медиагруппа, $channel_media_market, $master, $message_id, $admin_group, $три_часа;	
	$запрос = "SELECT * FROM {$table_market} WHERE id_zakaz='{$номер_лота}'";		
	$результат = $mysqli->query($запрос);	
	if ($результат) {		
		if ($результат->num_rows == 1) {
			$результМассив = $результат->fetch_all(MYSQLI_ASSOC);			
			foreach ($результМассив as $строка) {			
				$файлАйди = $строка['file_id'];		
				$формат_файла = $строка['format_file'];				
				$название = $строка['nazvanie'];				
				if ($строка['url_nazv']) {				
					$ссыль_в_названии = $строка['url_nazv'];						
					$название_для_подробностей = "[{$название}]({$ссыль_в_названии})";					
				}else $название_для_подробностей = str_replace('_', '\_', $название);				
				$куплю_или_продам = $строка['kuplu_prodam'];								
				$валюта = $строка['valuta'];				
				$хештеги_города = $строка['gorod'];				
				$юзера_имя = $строка['username'];				
				$доверие = $строка['doverie'];
				$категория = $строка['otdel'];				
				$подробности = $строка['podrobno'];							
				$хештеги = "{$куплю_или_продам}\n\n{$категория}\n▪️";				
				$хештеги = str_replace('_', '\_', $хештеги);
				$текст = "\n▪️{$валюта}\n▪️{$хештеги_города}\n▪️{$юзера_имя}";				
				$текст = str_replace('_', '\_', $текст);								
				$количество = substr_count($подробности, '[');				
				if ($количество == 0) {					
					$подробности = str_replace('_', '\_', $подробности);					
				}							
				$текст .= "\n\n{$подробности}"; 						
				$текст = "{$хештеги}{$название_для_подробностей}{$текст}";				
				$imgBB_url = $строка['url_tgraph'];	
				if ($imgBB_url) {								
					$реплика = "[_________]({$imgBB_url})\n{$текст}";					
				}else $реплика = $текст;						
				$ссылка_инфобота = $строка['url_info_bot'];								
				$кнопки = [
					[ 
						[ 'text' => 'О пользователе', 'url' => $ссылка_инфобота ],
						[ 'text' => 'ГАРАНТ', 'url' => 'https://t.me/podrobno_s_PZP/1044' ]
					]
				];		
				if ($строка['foto_album'] == '1') {					
					$запрос = "SELECT url FROM {$таблица_медиагруппа} WHERE id='{$номер_лота}'";			
					$результат = $mysqli->query($запрос);	
					if ($результат) {						
						if ($результат->num_rows > 0) {						
							$результМассив = $результат->fetch_all(MYSQLI_ASSOC);						
							$ссылка_на_канал_медиа = $результМассив[0]['url'];
						}						
						if ($ссылка_на_канал_медиа) {							
							$кнопки = array_merge($кнопки, [
								[ [ 'text' => 'Фото', 'url' => $ссылка_на_канал_медиа ] ]
							]);
						}					
					}						
				}		
				$кнопки = array_merge($кнопки, [
					[
						[ 	'text' => 'INSTAGRAM PRIZMarket',
							'url' => 'https://www.instagram.com/prizm_market_inst' ],
						[ 	'text' => 'PZMarket bot',
							'url' => 'https://t.me/Prizm_market_bot' ]
					],
					[
						[ 	'text' => 'Заказать пост', 
							'url' => 'https://t.me/Zakaz_prizm_bot' ],
						[ 	'text' => 'Канал PRIZMarket',
							'url' => 'https://t.me/prizm_market/' ]
					]
				]);				
				$inLine = ['inline_keyboard' => $кнопки];		
				try {
					$изменил = $bot->editMessageText($channel_podrobno, $номер_лота, $реплика, null, markdown, false, $inLine);						
				}catch(Exception $e) {
					$изменил = false;
				}
			}			
		}else $bot->sendMessage($master, "Или нет заказа или больше одного.. (_редакт_лота_на_канале_подробности)");	
	}else throw new Exception("Не смог найти заказ.. (_редакт_лота_на_канале_подробности)");		
	if ($изменил) $bot->sendMessage($chat_id, "Изменил лот на канале 'Подробности'");	
}

?>