<?

class FetchedPageTestModule extends TestUnitModule 
{

	private function __assertCreateFetchPage( $pageContents , $statusCode ) {
		$fetchedPage = new FetchedPage( $pageContents , $statusCode );		
		$this->assertEqual( $statusCode , $fetchedPage->statusCode );		
		$this->assertEqual( $pageContents , $fetchedPage->contents );
	
	}

	public function createFetchedPage_emptyPageWithStatus200_CreatesEmptyContentsWithStatus200 () 
	{
		$pageContents = "<html><head><title>Small page</title></head><body>small page body</body></html>";

		$validStatusCode = 200;		
		
		$this->__assertCreateFetchPage(  $pageContents , $validStatusCode );
		
	
	}
	
	public function createFetchedPage_smallPageWithStatus200_CreatesSmallPageWithStatus200() 
	{
	
		$pageContents = "";
	
		$validStatusCode = 200;
		
		$this->__assertCreateFetchPage(  $pageContents , $validStatusCode );
	
	}

}


?>
