<?

class MockHttpClient implements IHttpClient 
{

	private $existingPages = null;	
	
	public function __construct( $existingPages ) 
	{
		
		$this->existingPages = $existingPages;
		
	}


	public function getPageContentsWithStatusCode( $url ) 
	{
	
		if ( array_key_exists( $url , $this->existingPages  ) ) 
		{
		
			return 
				array( 
					$this->existingPages[ $url ] 
					, 200 
				);
			
		} else {
		
			return 
				array(
					"Page $url not found! Error code 404"
					, 404
				);
		}
	
	}	

}

?>