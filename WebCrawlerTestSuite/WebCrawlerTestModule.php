<?

class WebCrawlerTestModule extends TestUnitModule 
{
	
	private $webCrawler;
	
	private $mockWebSiteMap;
	
	private $domainPrefix;
	
	private $siteIndexURL;
	
	private $mockTrackingStorage;
	
	public function __construct() 
	{
		
		$this->protocol = "http";
		
		$this->domain = "www.invision-web.net";
		
		$this->domainPrefix = $this->constructLink('');		
		
		$this->siteIndexURL = "{$this->domainPrefix}/";
		
		$dp = $this->domainPrefix;

		$this->mockWebSiteMap = array(
		
				"{$this->siteIndexURL}" 
					=> 
					"
					<title>Index page</title>
					<a href='/1'>Page 1</a>
					<a href='/2'>Page 2</a>
					<a href='/3'>Page 3</a>									
					",
					
						"{$dp}/1" => "
							<title>Page 1</title>
							<a href='/1.1'>Page 1.1</a>
							<a href='/1.2'>Page 1.2</a>
							<a href='/'>Back to index</a>
						" ,

							"{$dp}/1.1" => "
									<title>Page 1.1</title>									
									<a href='/1'>Back to 1</a>
							" ,
							
							"{$dp}/1.2" => "
									<title>Page 1.2</title>									
									<a href='/1'>Back to 1</a>
							" ,
								
						
						"{$dp}/2" => "
							<title>Page 2</title>
							<a href='/2.1'>Page 2.1</a>
							<a href='/2.2'>Page 2.2</a>
							<a href='/'>Back to index</a>
						" ,

							"{$dp}/2.1" => "
									<title>Page 2.1</title>									
									<a href='/2'>Back to 2</a>
							" ,
							
							"{$dp}/2.2" => "
									<title>Page 2.2</title>									
									<a href='/2'>Back to 2</a>
							" ,
						
						"{$dp}/3" => "
							<title>Page 3</title>
							<a href='/3.1'>Page 3.1</a>
							<a href='/3.2'>Page 3.2</a>
							<a href='/'>Back to index</a>
						" ,

							"{$dp}/3.1" => "
									<title>Page 3.1</title>									
									<a href='/3'>Back to 3</a>
							" ,
							
							"{$dp}/3.2" => "
									<title>Page 3.2</title>									
									<a href='/3'>Back to 3</a>
							" ,						
				
				
		);
		
		$httpClient = new MockHttpClient( $this->mockWebSiteMap );
		
		$this->mockTrackingStorage  = new MemoryStorage();
		
		$this->webCrawler 
			= new WebCrawler(
			
				$httpClient 
				, 
				
				$this->mockTrackingStorage 
				
			);
		
		Console::Disable();
	}
	
	public function __destruct() 
	{
		Console::Enable();
	}
	
	private function constructLink( $path ) 
	{
		return "{$this->protocol}://{$this->domain}{$path}";			
	}
	
	////
	
	public function setHttpClient_nonIHttpClientObject_ThrowsException()
	{
		$exceptionOccured = false;
		try {
			$this->webCrawler->setHttpClient( new MockObject() );
			
		} catch ( Exception $ex ) {
		
			$exceptionOccured = true;
		}
		
		$this->assertEqual( true, $exceptionOccured);
	}
	
	
	public function setHttpClient_null_ThrowsException()
	{
		$exceptionOccured = false;
		try {
			$this->webCrawler->setHttpClient( null );
			
		} catch ( Exception $ex ) {
		
			$exceptionOccured = true;
		}
		
		$this->assertEqual( true, $exceptionOccured);
	}
	
	
	public function getHttpClient_MockHttpClientObject_ReturnsGivenObject()
	{
		$mockHttpClient = new MockHttpClient( array() );
		
		$this->webCrawler->setHttpClient( $mockHttpClient );
		
		$resultClient = $this->webCrawler->getHttpClient();
				
		$this->assertIdentical( $mockHttpClient, $resultClient );
		
	}
	
	
	public function getPageWithStatusCode_NonExistingPage_Returns404StatusCode() {
	
		$nonExistingLink = $this->constructLink( '/nonexistingpage.html' );
						
		$fetchedPage = $this->webCrawler->getPageWithStatusCode( $nonExistingLink );
		
		$this->assertEqual( 404 , $fetchedPage->statusCode );
	
	}
	
	
	public function getPageWithStatusCode_DefaultIndexPage_Returns200StatusCode() 
	{
		
		$indexPageLink = $this->constructLink( '/' );
						
		$fetchedPage = $this->webCrawler->getPageWithStatusCode( $indexPageLink );
		
		$this->assertEqual( 200 , $fetchedPage->statusCode );
	
	}
	
	public function extractUniqueLinksFromPage_EmptyPage_ReturnsEmptyArray() 
	{
	
		$emptyPage = new FetchedPage( "" , 200 );
	
		$links = $this->webCrawler->extractUniqueLinksFromPage( $emptyPage );
		
		$this->assertIdentical( array() , $links );
		
	}
	
	
	public function extractUniqueLinksFromPage_SingleAHrefTag_ReturnsSingleValidPageLink() {
		$pageWithSingleLink = 
			new FetchedPage( 				
				"<html><body><a href='http://www.invision-web.net/'>Goto invision!</a></body></html>" ,
				200
			);
		
		
		$links = $this->webCrawler->extractUniqueLinksFromPage( $pageWithSingleLink );
		
		$this->assertEqual( array( new PageLink ( "http://www.invision-web.net/" ) ) , $links );
		
	}
	
	
	public function extractUniqueLinksFromPage_DuplicateAHrefTag_ReturnsSingleValidPageLink() {
		$pageWithDuplicateLink = 
			new FetchedPage( 				
				"<html><body>
					<a href='http://www.invision-web.net/' id='first link'>Goto invision!</a>
					<a href='http://www.invision-web.net/' id='second link'>Goto invision again!</a>
				</body></html>" 
				, 200
			);
		
		
		$links = $this->webCrawler->extractUniqueLinksFromPage( $pageWithDuplicateLink );
		
		$this->assertEqual( array( new PageLink ( "http://www.invision-web.net/" ) ) , $links );
		
	}
	
	
	public function extractUniqueLinksFromPage_MultipleUniqueLinks_ReturnsLinksInSameOrder() 
	{
		$pageWithMultipleLinks = 
			new FetchedPage( 				
				"<html><body>
					<a href='http://www.invision-web.net/1' id='first link'>Goto invision 1!</a>
					<a href='http://www.invision-web.net/2' id='second link'>Goto invision 2!</a>
					<a href='http://www.invision-web.net/3' id='third link'>Goto invision 3!</a>
				</body></html>"  
				, 200
			);
		
		
		$links = $this->webCrawler->extractUniqueLinksFromPage( $pageWithMultipleLinks );
		
		$this->assertEqual( 
			array( 
				new PageLink ( "http://www.invision-web.net/1" )  ,
				new PageLink ( "http://www.invision-web.net/2" )  ,
				new PageLink ( "http://www.invision-web.net/3" ) 
			) 
			, $links
		);
		
	}
	
	
	public function extractUniqueLinksFromPage_MultipleDuplicateLinks_ReturnsUniqueLinksInOrderOfAppearence() 
	{
		$pageWithMultipleDuplicateLinks = 
			new FetchedPage( 				
				"<html><body>
					<a href='http://www.invision-web.net/1' id='first link'>Goto invision 1!</a>
					<a href='http://www.invision-web.net/2' id='second link'>Goto invision 2!</a>
					<a href='http://www.invision-web.net/3' id='third link'>Goto invision 3!</a>
					
					<a href='http://www.invision-web.net/3' id='third link'>Goto invision 3!</a>
					<a href='http://www.invision-web.net/2' id='second link'>Goto invision 2!</a>
					<a href='http://www.invision-web.net/1' id='first link'>Goto invision 1!</a>

					<a href='http://www.invision-web.net/3' id='third link'>Goto invision 3!</a>					
					<a href='http://www.invision-web.net/1' id='first link'>Goto invision 1!</a>					
					<a href='http://www.invision-web.net/2' id='second link'>Goto invision 2!</a>					
					
				</body></html>" 
				, 200
			);
		
		
		$links = $this->webCrawler->extractUniqueLinksFromPage( $pageWithMultipleDuplicateLinks );
		
		$this->assertEqual( 
			array( 
				new PageLink ( "http://www.invision-web.net/1" )  ,
				new PageLink ( "http://www.invision-web.net/2" )  ,
				new PageLink ( "http://www.invision-web.net/3" ) 
			) 
			, $links
		);
		
	}
	
	public function extractUniqueLinksFromPage_PageWithOffsiteLinks_ReturnsOnDomainUniqueLinksInOrderWithFullURL() 
	{
		$pageWithOffsiteLinks = new FetchedPage( 
				"<html><body>
					<a href='http://www.invision-web.net/1' id='first link'>Goto invision 1!</a>
					<a href='http://www.invision-web.net/2' id='second link'>Goto invision 2!</a>
					<a href='http://www.invision-web.net/3' id='third link'>Goto invision 3!</a>
					
					<a href='http://www.invision-web.net/3' id='third link'>Goto invision 3!</a>
					<a href='http://www.invision-web.net/2' id='second link'>Goto invision 2!</a>
					<a href='http://www.invision-web.net/1' id='first link'>Goto invision 1!</a>

					<a href='/4' id='third link'>Goto invision 4!</a>					
					<a href='/5' id='first link'>Goto invision 5!</a>					
					<a href='/6' id='second link'>Goto invision 6!</a>					
					
					<a href='http://twitter.com/invision' id='third link'>Goto invision twitter!</a>					
					<a href='http://facebook.com/invision' id='first link'>Goto invision facebook!</a>					
					<a href='javascript:' id='second link'>Some javascript here!</a>					
					
				</body></html>"
				, 200
		);
		
		$links = $this->webCrawler->extractUniqueLinksFromPage( 
			$pageWithOffsiteLinks , 
			"http://www.invision-web.net" 
		);
		
		$this->assertEqual(
			array(
				new PageLink( "http://www.invision-web.net/1" ) ,
				new PageLink( "http://www.invision-web.net/2" ) ,
				new PageLink( "http://www.invision-web.net/3" ) ,
				new PageLink( "http://www.invision-web.net/4" ) ,
				new PageLink( "http://www.invision-web.net/5" ) ,
				new PageLink( "http://www.invision-web.net/6" ) ,
			),
			$links
		);
		
	}
	
	public function extractUniqueLinksFromPage_PageWithOffsiteLinksAndHTTPS_ReturnsOnDomainUniqueLinksInOrderWithFullURL() 
	{
		$pageWithMixedProtocolsAndOffsiteLinks = new FetchedPage(
			"<html><body>
					<a href='http://www.invision-web.net/1' id='first link'>Goto invision 1!</a>
					<a href='http://www.invision-web.net/2' id='second link'>Goto invision 2!</a>
					<a href='http://www.invision-web.net/3' id='third link'>Goto invision 3!</a>
					
					<a href='https://www.invision-web.net/3' id='third link'>Goto invision 3!</a>
					<a href='https://www.invision-web.net/2' id='second link'>Goto invision 2!</a>
					<a href='https://www.invision-web.net/1' id='first link'>Goto invision 1!</a>

					<a href='/4' id='third link'>Goto invision 4!</a>					
					<a href='/5' id='first link'>Goto invision 5!</a>					
					<a href='/6' id='second link'>Goto invision 6!</a>					
					
					<a href='http://twitter.com/invision' id='third link'>Goto invision twitter!</a>					
					<a href='http://facebook.com/invision' id='first link'>Goto invision facebook!</a>					
					<a href='javascript:' id='second link'>Some javascript here!</a>					
					
				</body></html>" 
				, 200
		);
		
		$links = $this->webCrawler->extractUniqueLinksFromPage( 
			$pageWithMixedProtocolsAndOffsiteLinks , 
			"https://www.invision-web.net" 
		);
		
		$this->assertEqual(
			array(
				
				new PageLink( "https://www.invision-web.net/3" ) ,
				new PageLink( "https://www.invision-web.net/2" ) ,
				new PageLink( "https://www.invision-web.net/1" ) ,
				new PageLink( "https://www.invision-web.net/4" ) ,
				new PageLink( "https://www.invision-web.net/5" ) ,
				new PageLink( "https://www.invision-web.net/6" ) ,
				
			),
			$links
		);
		
	}
	
	
	// gets set by MockWebCrawlerEventHandler::onPageVisited
	public $visitedPages = array();
	
	
	public function startCrawl_MockIndexPageWithNegativeDepth_TriggersNothing( ) 
	{
	
		$this->visitedPages = array();
		
		$mockedEventHandler = new MockWebCrawlerEventHandler( $this );
		
		$this->webCrawler->addEventHandler( $mockedEventHandler );
		
		$this->webCrawler->startCrawl( $this->siteIndexURL , -1 );
		
		$this->assertEqual(
		
			array(),
			
			$this->visitedPages
			
		);
	
	}
	
	public function startCrawl_MockIndexPageWith0Depth_TriggersEventForStartingURL( ) 
	{
	
		$this->visitedPages = array();
		
		$mockedEventHandler = new MockWebCrawlerEventHandler( $this );
		
		$this->webCrawler->addEventHandler( $mockedEventHandler );
		
		
		
		$this->webCrawler->startCrawl( $this->siteIndexURL , 0 );
		
		$indexPageContents = $this->mockWebSiteMap[ $this->siteIndexURL  ];
		
		$this->assertEqual(			
			array(
				array(
					$this->siteIndexURL 
					, new FetchedPage( $indexPageContents , 200 )
				)
			),
			$this->visitedPages
			
		);
	
	}
	
	
	public function startCrawl_MockIndexPageWith1Depth_TriggersEventForStartingURLAndLinksOnItOnce( ) 
	{
	
		$this->visitedPages = array();
		
		$mockedEventHandler = new MockWebCrawlerEventHandler( $this );
		
		$this->webCrawler->addEventHandler( $mockedEventHandler );
		
		
		
		$this->webCrawler->startCrawl( $this->siteIndexURL , 1 );
		
		
		$firstPageURL = "{$this->domainPrefix}/1";
		$secondPageURL = "{$this->domainPrefix}/2";
		$thirdPageURL = "{$this->domainPrefix}/3";
		
		$indexPageContents = $this->mockWebSiteMap[ $this->siteIndexURL  ];
		
		$this->assertEqual(			
			array(
				array(
					$this->siteIndexURL 
					, new FetchedPage( $indexPageContents , 200 )
				),
				
				array(
					$firstPageURL
					, new FetchedPage( $this->mockWebSiteMap[ $firstPageURL ] , 200 )
				),
				
				array(
					$secondPageURL
					, new FetchedPage( $this->mockWebSiteMap[ $secondPageURL ] , 200 )
				),
				
				array(
					$thirdPageURL
					, new FetchedPage( $this->mockWebSiteMap[ $thirdPageURL ] , 200 )
				),
				
			),
			$this->visitedPages
			
		);
	
	}
	
	
	public function startCrawl_MockIndexPageWith2Depth_TriggersEventFor2LevelsEachURLOnce( ) 
	{
	
		$this->visitedPages = array();
		
		$mockedEventHandler = new MockWebCrawlerEventHandler( $this );
		
		$this->webCrawler->addEventHandler( $mockedEventHandler );
		
		
		
		$this->webCrawler->startCrawl( $this->siteIndexURL , 2 );
		
		
		$firstPageURL = "{$this->domainPrefix}/1";
			$firstPageFirstSubPageURL = "{$this->domainPrefix}/1.1";
			$firstPageSecondSubPageURL = "{$this->domainPrefix}/1.2";
		$secondPageURL = "{$this->domainPrefix}/2";
			$secondPageFirstSubPageURL = "{$this->domainPrefix}/2.1";
			$secondPageSecondSubPageURL = "{$this->domainPrefix}/2.2";
		$thirdPageURL = "{$this->domainPrefix}/3";
			$thirdPageFirstSubPageURL = "{$this->domainPrefix}/3.1";
			$thirdPageSecondSubPageURL = "{$this->domainPrefix}/3.2";
		
		$indexPageContents = $this->mockWebSiteMap[ $this->siteIndexURL  ];
		
		$this->assertEqual(			
			array(
				array(
					$this->siteIndexURL 
					, new FetchedPage( $indexPageContents , 200 )
				),
				
					array(
						$firstPageURL
						, new FetchedPage( $this->mockWebSiteMap[ $firstPageURL ] , 200 )
					),
					
						array(
							$firstPageFirstSubPageURL
							, new FetchedPage( $this->mockWebSiteMap[ $firstPageFirstSubPageURL ] , 200 )
						),
						
						
						array(
							$firstPageSecondSubPageURL
							, new FetchedPage( $this->mockWebSiteMap[ $firstPageSecondSubPageURL ] , 200 )
						),
					
					array(
						$secondPageURL
						, new FetchedPage( $this->mockWebSiteMap[ $secondPageURL ] , 200 )
					),
					
						array(
							$secondPageFirstSubPageURL
							, new FetchedPage( $this->mockWebSiteMap[ $secondPageFirstSubPageURL ] , 200 )
						),
						
						
						array(
							$secondPageSecondSubPageURL
							, new FetchedPage( $this->mockWebSiteMap[ $secondPageSecondSubPageURL ] , 200 )
						),
					
					array(
						$thirdPageURL
						, new FetchedPage( $this->mockWebSiteMap[ $thirdPageURL ] , 200 )
					),
					
						array(
							$thirdPageFirstSubPageURL
							, new FetchedPage( $this->mockWebSiteMap[ $thirdPageFirstSubPageURL ] , 200 )
						),
						
						
						array(
							$thirdPageSecondSubPageURL
							, new FetchedPage( $this->mockWebSiteMap[ $thirdPageSecondSubPageURL ] , 200 )
						),				
					
			),
			$this->visitedPages
			
		);
	
	}

}


?> 