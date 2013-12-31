<?

class ShellWebCrawler 
{


	public function __construct() {
		// flush so we get mid results
		ob_end_flush();
		ob_flush();
		flush();

		// for the console
		define('SHELL_MODE',true);

		Console::Disable();
	}

	public function onPageVisited( $url , $fetchedPage ) 
	{
	
		switch ($fetchedPage->statusCode) {
			case 200:
				$color = "green";
			break;
			case 302:
				$color = "yellow";
			break;
			case 404:
			case 500:
				$color = "red";
			break;
			default:
				$color = "gray";
			break;
		}
	
		$message = "Visited {$url} | Status Code {$fetchedPage->statusCode}\n";
		echo ShellColors::getInstance()->getColoredString( $message, $color );
	}
	
	public function crawl( $start_url , $depth ) 
	{
		$httpClient = new CURLHttpClient();

		$storage = new MemoryStorage();

		$webCrawler = new WebCrawler( $httpClient , $storage );

		$webCrawler->addPartialEventHandler( $this );
		
		echo "Starting crawl to $start_url with depth = $depth\n";
		
		$webCrawler->startCrawl( $start_url  , $depth );


	}

}


?>
