<?


class PageLink {

	public $url;

	public function __construct( $url ) 
	{
		$this->url = $url;		
	}
	
	public function __tostring() 
	{
		return $this->url;
	}
	

}


?>