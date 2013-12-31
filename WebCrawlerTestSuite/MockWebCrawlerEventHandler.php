<?


class MockWebCrawlerEventHandler implements IWebCrawlerEventHandler {
	
	private $testModuleForResults;
	
	public function __construct( $testModuleForResults ) 
	{
	
		$this->testModuleForResults = $testModuleForResults;
	}
	
	public function onPageVisited( $url , $fetchedPage ) 
	{
		$this->testModuleForResults->visitedPages[] = array($url,$fetchedPage);
	}

}



?>