<?php

/**----------+
 * Class Bot |
 * ----------+
 *
 *
 * Список методов:
 *
 *
 * sendMessage
 *
 * answerCallbackQuery
 *
 * getChat
 *
 *
 *
 */

class Bot
{
    // $token - созданный токен для нашего бота от @BotFather
    private $token = null;
    // адрес для запросов к API Telegram
    private $apiUrl = "https://api.telegram.org/bot";
    
	/*
	** @param str $token
	*/
    public function __construct($token)
    {
        $this->token = $token;
    }    
    
	/*
	** @param JSON $data_php
	** @return array
	*/
    public function init($data_php)
    {
        // создаем массив из пришедших данных от API Telegram
        $data = $this->getData($data_php); 
        
        return $data;        
    }
	
	/*
    ** @param JSON $data
    ** @return array
    */
    private function getData($data)
    {
        return json_decode(file_get_contents($data), TRUE);
    }
    
    
    /* 
	** Отправляем запрос в Телеграмм
	**
    ** @param str $method
    ** @param array $data    
	**
    ** @return mixed
    */
    public function call($method, $data)
    {
        $result = null;
        if (is_array($data)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $this->token . '/' . $method);
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            $result = curl_exec($ch);
            curl_close($ch);
        }
        return $result;
    }
    
    
    /*
	**  функция отправки текстового сообщения	
	**
	**  @param int $chat_id
 	**  @param str $text
	**  @param str $parse_mode
	**  @param array $reply_markup
	**  @param bool $disable_web_page_preview
	**  @param bool $disable_notification
	**  @param str $reply_to_message_id
	**  
	**  @return mixed
	*/
    public function sendMessage(
		$chat_id, 
		$text,
		$parse_mode = null,
		$reply_markup = null,
		$disable_web_page_preview = false,
		$disable_notification = false,
		$reply_to_message_id = null
	) {
		
		if ($reply_markup) $reply_markup = json_encode($reply_markup);
		
		$response = $this->call("sendMessage", [
			'chat_id' => $chat_id,
			'text' => $text,
			'parse_mode' => $parse_mode,			
			'disable_web_page_preview' => $disable_web_page_preview,
			'disable_notification' => $disable_notification,
			'reply_to_message_id' => $reply_to_message_id,
			'reply_markup' => $reply_markup
		]);	
		
		return $response;
	}
    
	/*
	** Ответное сообщение на нажатие кнопки callback_query
	**
	** @param int $callback_query_id
	** @param str $text
	** @param bool $show_alert
	** @param str $url
	** @param date $cache_time
	** 
	** @return mixed
	*/
	public function answerCallbackQuery(
		$callback_query_id,
		$text = null,
		$show_alert = false,
		$url = null,
		$cache_time = null
	){
		$response = $this->call("answerCallbackQuery", [
			'callback_query_id' => $callback_query_id,
			'text' => $text,
			'show_alert' => $show_alert,
			'url' => $url,
			'cache_time' => $cache_time
		]);
		
		return $response;
	}
	
	/*
	** Вывод информации о чате (о юзере, группе, супергруппе или о канале)
	**
	** @param int $chat_id
	**
	** @return mixed
	*/
	public function getChat($chat_id){
		
		$response = $this->call("getChat", ['chat_id' => $chat_id]);
		
		return $response;
	}

    
    
}

?>
