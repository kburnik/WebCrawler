<?


class CURLHttpClient extends HttpClient 
{
	
	public function getPageContentsWithStatusCode( $url ) 
	{
	
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_URL, $url);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true );
		
		$response = curl_exec($ch);
		
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close($ch);
		
		return array( $response , $http_status);
	}
	
	
	

}


?>
