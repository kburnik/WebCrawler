<?

class FetchedPage {

	public $contents;

	public $statusCode;

	

	public function __construct( $contents , $statusCode ) 
	{
		$this->statusCode = $statusCode;
		$this->contents = $contents;
	}

}

?>