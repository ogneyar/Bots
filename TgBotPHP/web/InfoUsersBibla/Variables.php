<?
// Обрабатываем пришедшие данные
$data = $bot->init('php://input');

// Вывод на печать JSON файла пришедшего от бота, в группу тестирования
if ($OtladkaBota == 'да') $bot->sendMessage($test_group, PrintArr($data)); 

$update_id = $data['update_id'];

if ($data['callback_query']){	
	
	$callback_query_id = $data['callback_query']['id'];
	
	$callback_from = $data['callback_query']['from'];
	
	$callback_from_id = $callback_from['id'];
	$callback_from_first_name = $callback_from['first_name'];
	$callback_from_last_name = $callback_from['last_name'];
	$callback_from_username = $callback_from['username'];
	$callback_from_language = $callback_from['language_code'];
	
	$data['message'] = $data['callback_query']['message'];
	
	$callback_data = $data['callback_query']['data'];
	
}

if ($data['message']){

	$message_id = $data['message']['message_id'];

	if ($data['message']['from']){
		
		$message_from = $data['message']['from'];
		
		$from_id = $message_from['id'];
		$from_first_name = $message_from['first_name'];
		$from_last_name = $message_from['last_name'];
		$from_username = $message_from['username'];
		$from_language = $message_from['language_code'];
	}
	
	if ($data['message']['chat']){
		
		$message_chat = $data['message']['chat'];
		
		$chat_id = $message_chat['id'];
		$chat_first_name = $message_chat['first_name'];
		$chat_last_name = $message_chat['last_name'];
		$chat_username = $message_chat['username'];
		$chat_title = $message_chat['title'];
		$chat_type = $message_chat['type'];
		
	}
		
	$date = $data['message']['date'];
		
	if ($data['message']['forward_from']) {
	
		$message_forward = $data['message']['forward_from'];

		$forward_id = $message_forward['id'];
		$forward_first_name = $message_forward['first_name'];
		$forward_last_name = $message_forward['last_name'];
		if ($forward_last_name=="") $forward_last_name = 'отсутствует';
		$forward_username = $message_forward['username'];
		if ($forward_username=="") $forward_username = 'отсутствует';

	}
	
	$forward_sender_name = $data['message']['forward_sender_name'];

	$forward_date = $data['message']['forward_date'];
	
	$text = $data['message']['text'];
	
}elseif ($data['edited_message']){

}elseif ($data['channel_post']) {

}elseif ($data['edited_channel_post']) {

}elseif ($data['inline_query']) {

}elseif ($data['chosen_inline_result']) {

}elseif ($data['callback_query']) {

}elseif ($data['shipping_query']) {

}elseif ($data['pre_checkout_query']) {

}elseif ($data['poll']) {

}



$RKeyMarkup = [
	'keyboard' => [
		[			
			[
				'text' => "Старт"
			],
			[
				'text' => "Стоп"
			]
		]
	],
	'resize_keyboard' => true,
        'selective' => true,
];


$ReplyKeyboardMarkup = [
	'keyboard' => [
		[
			[
				'text' => "Новая кнопка!",
				'request_contact' => false,
				'request_location' => false,
			],
			[
				'text' => "Вторая новая кнопка!",
				'request_contact' => false,
				'request_location' => false,
			],
		],
	],
	'resize_keyboard' => false,
	'one_time_keyboard' => false,
	'selective' => false,
];


$HideKeyboard = [
	'hide_keyboard' => true,
    'selective' => false,
];


$InlineKeyboardMarkup = [
	'inline_keyboard' => [
		[
			[
				'text' => 'Информация',
				'callback_data' => 'information'
			]
		]
	]
];











?>
