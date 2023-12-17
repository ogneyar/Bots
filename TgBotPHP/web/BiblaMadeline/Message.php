<?
// Если клиент шлёт сразу группу файлов
if ($media_group_id) {
	_запись_в_таблицу_медиагрупа();	
}
// Если админ пишет команду, через двоеточие номер $id (номер клиента/заказа)
if (strpos($text, ":")!==false) {
	$команда = strstr($text, ':', true);		
	$id = substr(strrchr($text, ":"), 1);	
	if ($id == '') $id = null;	
	if ($команда == 'лоты') {	
		if ($id) {		
			_вывод_списка_лотов("покажи", $id);			
		}else {				
			_вывод_списка_лотов("покажи", null, true);		
		}				
		exit('ok');
	}
}
// Если было ответное сообщение (reply_to_message) в админке 
// то отправляется это сообщение клиенту, по его юзернейму
if (($reply_to_message && $chat_id == $admin_group) || ($reply_to_message && $chat_id == $master)) {	
	if (!$reply_caption) $reply_caption = $reply_text;		
	$номер_строки = strpos($reply_caption, '@');		
	if ($номер_строки >= 0) {				
		$строка = strstr($reply_caption, '@');
		$есть_ли_энтр = strpos($строка, 10);				
		if ($есть_ли_энтр) {					
			$юзер_нейм = strstr($строка, 10, true);					
		}else {			
			$есть_ли_пробел = strpos($строка, ' ');			
			if ($есть_ли_пробел) {
				$юзер_нейм = strstr($строка, ' ', true);			
			}else {			
				$юзер_нейм = $строка;			
			}
		}			
		$id_client = _дай_айди($юзер_нейм);		
		$главное_меню = "\n\n/start 👈🏻 в главное меню!";		
		$результат = $bot->sendMessage($id_client, $text.$главное_меню);		
		if ($результат) {			
			$bot->sendMessage($chat_id, "Отправил.", null, null, $message_id);			
		}		
	}
// Редактирование лотов админами (кнопка "Редактор лотов" видна только админам)
}elseif ($text=='Редактор лотов') {
	$админ = $bot->this_admin($table_users);	
	if ($админ) {	
		_ожидание_ввода('редактор_лотов', 'старт');				
		$reply = "Пришлите мне номер лота.";		
		$bot->sendMessage($chat_id, $reply, null, $клавиатура_отмена_ввода);	
	}
// Когда отправлена команда "Отмена ввода" проверяется функция "_ожидание_ввода"
}elseif ($text=='Отмена ввода') {
	$bot->sendMessage($chat_id, "Ввод отменён.", null, $HideKeyboard);	
	$result = _ожидание_ввода();		
	if ($result) {	
		if ($result['last'] == 'kuplu_prodam') {						
			_очистка_таблицы_ожидание();			
			_создать();		
		}elseif ($result['last'] == 'nazvanie') {		
			_очистка_таблицы_ожидание();			
			_ссылка_в_названии();		
		}elseif ($result['last'] == 'valuta') {		
			_очистка_таблицы_ожидание();			
			_выбор_валюты();		
		}elseif ($result['last'] == 'gorod') {		
			_очистка_таблицы_ожидание();			
			_ввод_местонахождения();	
		}elseif ($result['last'] == 'format_file') {		
			_очистка_таблицы_ожидание();			
			_отправьте_файл();		
		}elseif ($result['last'] == 'foto_album') {
			_очистка_таблицы_ожидание();			
			_нужен_ли_фотоальбом();		
		}elseif ($result['last'] == 'хорошо') {		
			_очистка_таблицы_ожидание();						
		}elseif ($result['last'] == 'старт') {		
			_очистка_таблицы_ожидание();			
			_старт_АвтоЗаказБота();			
		}		
	}else _старт_АвтоЗаказБота();
// Если не спец команды, значит проверяется функция "_ожидание_ввода" 
}else { 	
	$result = _ожидание_ввода();	
	if ($result) {		
		if ($result['ojidanie'] == 'редактор_лотов') {					
			if ($text) {
				_очистка_таблицы_ожидание();
				_отправка_лота_админам($text);
			}			
		}elseif ($result['ojidanie'] == 'замена_названия') {			
			$номер = $result['last'];					
			if ($text) {				
				$text = str_replace("'", "\'", $text);
				$text = str_replace("`", "\`", $text);
				if (_есть_ли_лот($номер)) {
					_запись_в_таблицу_маркет(null, 'nazvanie', $text, $номер);	
				}else _запись_в_таблицу_маркет($номер, 'nazvanie', $text);				
				_очистка_таблицы_ожидание();				
				$bot->sendMessage($chat_id, "Принял. Заменил.", null, $HideKeyboard);	
				$bot->sendMessage($chat_id, $text);							
			}						
		}elseif ($result['ojidanie'] == 'замена_ссылки') {			
			$номер = $result['last'];			
			if ($text) {			
				if (_есть_ли_лот($номер)) {
					_запись_в_таблицу_маркет(null, 'url_nazv', $text, $номер);	
				}else _запись_в_таблицу_маркет($номер, 'url_nazv', $text);				
				_очистка_таблицы_ожидание();				
				$bot->sendMessage($chat_id, "Принял. Заменил.", null, $HideKeyboard);
				$bot->sendMessage($chat_id, $text);							
			}						
		}elseif ($result['ojidanie'] == 'замена_хештегов') {			
			$номер = $result['last'];			
			if ($text) {			
				if (_есть_ли_лот($номер)) {
					_запись_в_таблицу_маркет(null, 'gorod', $text, $номер);	
				}else _запись_в_таблицу_маркет($номер, 'gorod', $text);				
				_очистка_таблицы_ожидание();				
				$bot->sendMessage($chat_id, "Принял. Заменил.", null, $HideKeyboard);	
				$bot->sendMessage($chat_id, $text);						
			}						
		}elseif ($result['ojidanie'] == 'замена_подробностей') {			
			$номер = $result['last'];			
			if ($text) {				
				$text = str_replace("'", "\'", $text);
				$text = str_replace("`", "\`", $text);			
				if (_есть_ли_лот($номер)) {
					_запись_в_таблицу_маркет(null, 'podrobno', $text, $номер);	
				}else _запись_в_таблицу_маркет($номер, 'podrobno', $text);				
				_очистка_таблицы_ожидание();				
				$bot->sendMessage($chat_id, "Принял. Заменил.", null, $HideKeyboard);	
				$bot->sendMessage($chat_id, $text);					
			}						
		}elseif ($result['ojidanie'] == 'замена_фото') {			
			$номер = $result['last'];			
			if ($photo) {	
				$есть_ли_лот = _есть_ли_лот($номер);
				if ($есть_ли_лот) {
					_запись_в_таблицу_маркет(null, 'file_id', $text, $номер);	
				}else _запись_в_таблицу_маркет($номер, 'file_id', $file_id);			
				_очистка_таблицы_ожидание();				
				$bot->sendMessage($chat_id, "Принял. Заменил.", null, $HideKeyboard);	
				$Объект_файла = $bot->getFile($file_id);			
				$ссыль_на_файл = $bot->fileUrl . $bot->token;						
				$ссыль = $ссыль_на_файл . "/" . $Объект_файла['file_path'];				
				$результат = $imgBB->upload($ссыль);									
				if ($результат) {								
					$imgBB_url = $результат['url'];		
					if ($есть_ли_лот) {
						_запись_в_таблицу_маркет(null, 'url_tgraph', $text, $номер);	
					}else _запись_в_таблицу_маркет($номер, 'url_tgraph', $imgBB_url);	
				}else throw new Exception("Не смог сделать imgBB_url");					
			}						
		// Если ожидается ввод названия
		}elseif ($result['ojidanie'] == 'nazvanie') {			
			if ($text) {												
				if (strlen($text) > 60) {				
					$bot->sendMessage($chat_id, "Слишком длинное название.\nНапишите название, около 30 символов.");					
					exit('ok');
				}				
				$text = str_replace('_', ' ', $text);				
				$text = str_replace("'", "\'", $text);
				$text = str_replace('"', '\"', $text);
				$text = str_replace(';', '', $text);
				$text = str_replace('*', 'х', $text);
				$text = str_replace('%', '', $text);
				$text = str_replace('`', '', $text);				
				$text = str_replace('&', '', $text);
				$text = str_replace('$', '', $text);
				$text = str_replace('^', '', $text);								
				$text = str_replace('\\', '', $text);
				$text = str_replace('|', '', $text);
				$text = str_replace('/', '', $text);
				$text = str_replace('<', '', $text);
				$text = str_replace('>', '', $text);
				$text = str_replace('~', '', $text);								
				_запись_в_таблицу_маркет($from_id, 'nazvanie', $text);			
				_очистка_таблицы_ожидание();				
				$bot->sendMessage($chat_id, "Принял.", null, $HideKeyboard);	
				$bot->sendMessage($chat_id, $text);
				
				_ссылка_в_названии();							
				
			}else $bot->deleteMessage($chat_id, $message_id);	
		// Если ожидается ввод ссылки, вшиваемой в название
		}elseif ($result['ojidanie'] == 'url_nazv') {			
			if ($text) {														
				//надо проверить есть ли в тексте http://
				_запись_в_таблицу_маркет($from_id, 'url_nazv', $text);					
				_очистка_таблицы_ожидание();				
				$bot->sendMessage($chat_id, "Принял.", null, $HideKeyboard);	
				$bot->sendMessage($chat_id, $text);
				
				_выбор_категории();					
				
			}else $bot->deleteMessage($chat_id, $message_id);	
		// Если ожидается ввод хештегов местонахождения клиента
		}elseif ($result['ojidanie'] == 'gorod') {		
			if ($text) {
				$text = str_replace('|', '', $text);
				$text = str_replace('/', '', $text);
				$text = str_replace('<', '', $text);
				$text = str_replace('>', '', $text);
				$text = str_replace('~', '', $text);
				$text = str_replace(':', '', $text);				
				$text = str_replace("'", "", $text);
				$text = str_replace('"', '', $text);
				$text = str_replace(';', '', $text);
				$text = str_replace('*', '', $text);
				$text = str_replace('%', '', $text);
				$text = str_replace('`', '', $text);
				$text = str_replace('?', '', $text);
				$text = str_replace('&', '', $text);
				$text = str_replace('$', '', $text);
				$text = str_replace('^', '', $text);
				$text = str_replace('\\', '', $text);				
				$количество = substr_count($text, '#');				
				if ($количество == 0) {					
					$bot->sendMessage($chat_id, "Повторите ввод, но только теперь, обязательно поставьте хештег - #.");					
					$bot->deleteMessage($chat_id, $message_id);							
				}elseif ($количество>3) {					
					$bot->sendMessage($chat_id, "Повторите ввод, но, не больше трёх - #.");					
					$bot->deleteMessage($chat_id, $message_id);						
				}else {					
					// тут можно по entities достать только хештеги					
					_запись_в_таблицу_маркет($from_id, 'gorod', $text);					
					_очистка_таблицы_ожидание();					
					$bot->sendMessage($chat_id, "Принял.", null, $HideKeyboard);
					$bot->sendMessage($chat_id, $text);
					
					_отправьте_файл();				
					
				}				
			}else $bot->deleteMessage($chat_id, $message_id);		
		// Если ожидается ввод основного фото/видео
		}elseif ($result['ojidanie'] == 'format_file') {						
			if ($photo||$video) {
				if ($video) {
					if ($file_size>'5242880') {						
						$bot->sendMessage($chat_id, "Повторите ввод, а то Ваш файл размером больше 5 МБ, сократите его немного.");					
						$bot->deleteMessage($chat_id, $message_id);						
						exit('ok');						
					}					
				}			
				_запись_в_таблицу_маркет($from_id, 'format_file', $формат_файла);
				_запись_в_таблицу_маркет($from_id, 'file_id', $file_id);				
				_очистка_таблицы_ожидание();				
				if ($media_group_id) {					
					$реплика = "Принял только ЭТОТ 👆🏻 файл.";				
				}else $реплика = "Принял.";				
				$bot->sendMessage($chat_id, $реплика, null, $HideKeyboard);		
				
				_нужен_ли_фотоальбом();			
				
			}else $bot->deleteMessage($chat_id, $message_id);		
		// Если ожидается ввод фотоальбома
		}elseif ($result['ojidanie'] == 'foto_album') {									
			if ($формат_файла) {			
				if ($media_group_id) {				
					_очистка_таблицы_ожидание();
					$bot->sendMessage($chat_id, "Принял, ВСЕ.", null, $HideKeyboard);	
					
					_опишите_подробно();				
					
				}else {					
					$bot->sendMessage($chat_id, "Пришлите все фото сразу, не по одному!\n(При отправке выберите: 'отправить альбом')");				
					$bot->deleteMessage($chat_id, $message_id);						
				}				
			}else $bot->deleteMessage($chat_id, $message_id);	
		// Если ожидается ввод текста с подробным описанием товара/услуги
		}elseif ($result['ojidanie'] == 'podrobno') {		
			if ($text) {				
				$количество = strlen($text);				
				if ($количество < 200) {					
					$bot->sendMessage($chat_id, "Для 'подробностей' слишком мало текста. Повторите ввод.");					
					exit('ok');					
				}elseif ($количество > 4000) {					
					$bot->sendMessage($chat_id, "Для 'подробностей' слишком много текста. Повторите ввод.");					
					exit('ok');				
				}				
				$text = str_replace("'", "\'", $text);
				$text = str_replace("`", "\`", $text);				
				$text = str_replace('"', '\"', $text);			
				$text = str_replace('*', 'х', $text);				
				$text = str_replace('[', '(', $text);
				$text = str_replace(']', ')', $text);				
				_запись_в_таблицу_маркет($from_id, 'podrobno', $text);					
				_очистка_таблицы_ожидание();				
				$bot->sendMessage($chat_id, "Принял.", null, $HideKeyboard);	
				$bot->sendMessage($chat_id, $text);
				
				_отправка_лота($chat_id, 0, false, true);
				
			}else $bot->deleteMessage($chat_id, $message_id);					
		}		
	// Если нет ожидания ввода, то в личке у бота удаляются сообщения
	}elseif ($chat_type == 'private') $bot->deleteMessage($chat_id, $message_id);	
}
	

?>