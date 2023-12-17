<?

if ($inline_query) {

	//$результат = _возврат_лотов_для_инлайн($from_id);
	
	$запрос = "SELECT * FROM {$table_market} WHERE id_client={$from_id} AND id_zakaz>0";		
	$результат = $mysqli->query($запрос);	
	if ($результат) {		
		if ($результат->num_rows > 0) {		
			$результМассив = $результат->fetch_all(MYSQLI_ASSOC);			
			$i = 1;			
			$InlineQueryResult = [];			
			foreach ($результМассив as $строка) {			
				$файлАйди = $строка['file_id'];		
				$формат_файла = $строка['format_file'];				
				$название = $строка['nazvanie'];
				$ссыль_в_названии = $строка['url_nazv'];
				$куплю_или_продам = $строка['kuplu_prodam'];							
				$валюта = $строка['valuta'];				
				$хештеги_города = $строка['gorod'];				
				$юзера_имя = $строка['username'];				
				$доверие = $строка['doverie'];
				$категория = $строка['otdel'];								
				$ссыль_на_подробности = $строка['url_podrobno'];						
				$номер_лота = $строка['id_zakaz'];				
				$photo_url = $строка['url_tgraph'];							
				$inLine = [
					'inline_keyboard' => [
						[ [ 'text' => 'Подробнее', 'url' => $ссыль_на_подробности ] ]
					]
				];
				$хештеги = "{$куплю_или_продам}\n\n{$категория}\n▪️";				
				$хештеги = str_replace('_', '\_', $хештеги);				
				$текст_после_названия = "\n▪️{$валюта}\n▪️{$хештеги_города}\n▪️{$юзера_имя}\n  лот {$номер_лота}";
				$текст_после_названия = str_replace('_', '\_', $текст_после_названия);					
				if ($доверие) $текст_после_названия .= "\n✅ PRIZMarket доверяет❗️"; 					
				$текст = "{$хештеги}[{$название}]({$ссыль_в_названии}){$текст_после_названия}";					
				if ($формат_файла == 'фото') {				
					$InlineQueryResult = array_merge($InlineQueryResult, [
						[
							'type' => 'photo',
							'id' => $from_id."_".$i,
							'photo_url' => $photo_url,
							'thumb_url' => $photo_url,							
							'title' => $куплю_или_продам,
							'description' => $название,
							'caption' => $текст,
							'parse_mode' => 'markdown',
							'reply_markup' => $inLine,							
						],
					]);									
				}elseif ($формат_файла == 'видео') {					
					$Объект_файла = $bot->getFile($файлАйди);			
					$ссыль_на_файл = $bot->fileUrl . $bot->token;						
					$ссыль = $ссыль_на_файл . "/" . $Объект_файла['file_path'];					
					$InlineQueryResult = array_merge($InlineQueryResult, [
						[						
							'type' => 'video',
							'id' => $from_id."_".$i,
							'video_url' => $ссыль,
							'mime_type' => 'video/mp4', // или 'text/html'
							'thumb_url' => $ссыль,
							'title' => $куплю_или_продам,					
							'caption' => $текст,
							'description' => $название,
							'parse_mode' => 'markdown',
							'reply_markup' => $inLine,								
						],
					]);										
				}				
				$i++;				
			}							
		}else throw new Exception("Или нет заказа или больше одного..");		
	}else throw new Exception("Нет такого заказа..");		
		
	$bot->answerInlineQuery($inline_query_id, $InlineQueryResult, null, false, null, "в бот", "s");

}


?>