<?

/**------------+
 * Class ImgBB |
 * ------------+
 *
 * ---------------
 * Список методов:
 * ---------------
 *
 * upload
 *
 *
 */

class ImgBB
{
    // токен ImgBB
    private $api_key = null;
    // ссылка для доступа к апи ImgBB
    private $apiUrl = "https://api.imgbb.com/1/";
    
	/*
	** @param str $api_key
	*/
    public function __construct($api_key)
    {
        $this->api_key = $api_key;
    }    
  
    
    /* 
	** Отправляем запрос в ImgBB
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
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $method);
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            $result = curl_exec($ch);
            curl_close($ch);
        }
        return $result;
    }
    
	
	
	
    
    /*
	**  функция загрузки фото
	**
	**  @param str $image	
	**
	**
	**  @return array
	*/
    public function upload($image) {		
		
		$response = $this->call("upload", [
			'key' => $this->api_key,
			'image' => $image
		]);	
				
		$response = json_decode($response, true);
		
		if ($response['success']) {
			$response = $response['data'];
		}else $response = false;
		
		return $response;
	}
	
	

	
}



?>