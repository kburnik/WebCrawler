<?

class WebCrawler extends Base
{
	
	private $httpClient;
	
	private $trackingStorage;
	
	public function __construct( $httpClient = null , $trackingStorage = null ) 
	{
		
		parent::__construct();
		
		
		$this->setHttpClient( $httpClient );
		
		$this->setTrackingStorage( $trackingStorage );
		
	}
	
	public function setHttpClient( $httpClient ) 
	{
		if ( ! $httpClient instanceOf IHttpClient ) 
		{
		
			throw new Exception("Expected IHttpClient, got " . var_export( $httpClient , true ));
			
		}
		
		$this->httpClient  = $httpClient;
	
	}
		
	public function getHttpClient() 
	{
		return $this->httpClient;
	}
	
	
	
	public function setTrackingStorage( $trackingStorage ) 
	{
		if ( ! $trackingStorage instanceOf IStorage ) 
		{
			throw new Exception("Expected IStorage, got " . var_export( $trackingStorage , true ));
		}
		
		$this->trackingStorage = $trackingStorage;
	
	}
	
	public function getTrackingStorage() 
	{
		return $this->trackingStorage;
	}
	
	
	public function getPageWithStatusCode( $url )
	{
	
		list( $contents , $statusCode )  = $this->httpClient->getPageContentsWithStatusCode( $url );
		
		return new FetchedPage( $contents , $statusCode );
		
	}
	
	
	public function extractUniqueLinksFromPage( $fetchedPage , $domainRestriction = null ) 
	{
		
		$links = array();
		
			
		foreach ( $this->httpClient->getLinksFromPage( $fetchedPage )  as $a ) 
		{
			$links[] = new PageLink( $a->getAttribute('href') );
		}
		
		
		if ( $domainRestriction != null ) 
		{
			$links = $this->_filterOnDomainLinks( $links, $domainRestriction );			
		}
	
		return array_values( array_unique( $links ) );
	}
	
	
	public function startCrawl( $startURL , $depth ) 
	{
		$p = parse_url( $startURL );
		
		$domainRestriction = "{$p['scheme']}://{$p['host']}";
		
		$this->_crawl( $startURL , $depth , $domainRestriction );
	}
	
	
	// privates
	
	private function _crawl( $url , $depth , $domainRestriction ) 
	{
		if ( $depth < 0 || $this->_isPageVisited( $url ) )
			return;
			
		
		$fetchedPage = $this->getPageWithStatusCode( $url );
	
		$this->_trackVisitedLink( $url );
		
		$this->onPageVisited( $url , $fetchedPage );
		
		if ( $depth == 0 )
			return;
				
		$nextLinks = $this->extractUniqueLinksFromPage( $fetchedPage , $url );
		
		foreach ( $nextLinks as $nextLink ) 
		{
			$this->_crawl( $nextLink->url , ($depth - 1) , $url );
		}
			
	}
	
	
	
	private static function _ParsedURLIsRelative( $parsedURL ) 
	{
		return $parsedURL['scheme'] == "" && $parsedURL['host'] == "" ;
	}
	
	private static function _ParsedURLsMatchIn( $parsedURL_A , $parsedURL_B, $fieldsToMatch ) 
	{
	
		$intersection = array_intersect( $parsedURL_A , $parsedURL_B  );
		
		$matchedFields = array_keys( $intersection ) ;		
		
		$diffCount = count( array_diff( $fieldsToMatch , $matchedFields ) );
		
		return ( $diffCount == 0 ) ;
		
	}
	
	private function _filterOnDomainLinks( $links, $domainRestriction ) 
	{

		$domainURL = parse_url( $domainRestriction );
		
		foreach ( $links as $i => $link ) 
		{
			
			$linkURL = parse_url( $link->url );
			if ( self::_ParsedURLIsRelative( $linkURL ) )
			{
			
				$newLinkURL = $this->httpClient->getAbsoluteURL( $domainRestriction , $link->url );
				
				$link->url = $newLinkURL;				
				
			}
			else if 
			( 
				! ( 
					self::_ParsedURLsMatchIn(  $domainURL , $linkURL , array("scheme","host") )
				)
			)
			{
				unset( $links[$i] );
			}

		}
		
		return array_values( $links );
	
	}
	
	private function _isPageVisited( $url ) 
	{
		return $this->trackingStorage->exists( $url );
	}
	
	private function _trackVisitedLink( $url ) 
	{
		$this->trackingStorage->write( $url , true );
	}
	
	
	
		
	
}


?>