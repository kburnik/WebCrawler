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
			$links = self::_FilterOnDomainLinks( $links, $domainRestriction );			
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
	
	private static function _FilterOnDomainLinks( $links, $domainRestriction ) 
	{

		$domainURL = parse_url( $domainRestriction );
		
		foreach ( $links as $i => $link ) 
		{
			
			$linkURL = parse_url( $link->url );
			if ( self::_ParsedURLIsRelative( $linkURL ) )
			{
			
				$newLinkURL = self::_url_to_absolute( $domainRestriction , $link->url );				
				
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
	
	
	private static function _url_to_absolute( $baseUrl, $relativeUrl )
	{
		// If relative URL has a scheme, clean path and return.
		$r = parse_url( $relativeUrl );
		if ( $r === FALSE )
			return FALSE;
		if ( !empty( $r['scheme'] ) )
		{
			if ( !empty( $r['path'] ) && $r['path'][0] == '/' )
				$r['path'] = self::_url_remove_dot_segments( $r['path'] );
			return http_build_url( "" , $r );
		}
	 
		// Make sure the base URL is absolute.
		$b = parse_url( $baseUrl );
		if ( $b === FALSE || empty( $b['scheme'] ) || empty( $b['host'] ) )
			return FALSE;
		$r['scheme'] = $b['scheme'];
	 
		// If relative URL has an authority, clean path and return.
		if ( isset( $r['host'] ) )
		{
			if ( !empty( $r['path'] ) )
				$r['path'] = self::_url_remove_dot_segments( $r['path'] );
			return http_build_url( "" , $r );
		}
		unset( $r['port'] );
		unset( $r['user'] );
		unset( $r['pass'] );
	 
		// Copy base authority.
		$r['host'] = $b['host'];
		if ( isset( $b['port'] ) ) $r['port'] = $b['port'];
		if ( isset( $b['user'] ) ) $r['user'] = $b['user'];
		if ( isset( $b['pass'] ) ) $r['pass'] = $b['pass'];
	 
		// If relative URL has no path, use base path
		if ( empty( $r['path'] ) )
		{
			if ( !empty( $b['path'] ) )
				$r['path'] = $b['path'];
			if ( !isset( $r['query'] ) && isset( $b['query'] ) )
				$r['query'] = $b['query'];
			return http_build_url( "" , $r );
		}
	 
		// If relative URL path doesn't start with /, merge with base path
		if ( $r['path'][0] != '/' )
		{
			$base = mb_strrchr( $b['path'], '/', TRUE, 'UTF-8' );
			if ( $base === FALSE ) $base = '';
			$r['path'] = $base . '/' . $r['path'];
		}
		$r['path'] = self::_url_remove_dot_segments( $r['path'] );
		return http_build_url( "" , $r );
	}
	
	
	private static function _url_remove_dot_segments( $path )
	{
		// multi-byte character explode
		$inSegs  = preg_split( '!/!u', $path );
		$outSegs = array( );
		foreach ( $inSegs as $seg )
		{
			if ( $seg == '' || $seg == '.')
				continue;
			if ( $seg == '..' )
				array_pop( $outSegs );
			else
				array_push( $outSegs, $seg );
		}
		$outPath = implode( '/', $outSegs );
		if ( $path[0] == '/' )
			$outPath = '/' . $outPath;
		// compare last multi-byte character against '/'
		if ( $outPath != '/' &&
			(mb_strlen($path)-1) == mb_strrpos( $path, '/', 'UTF-8' ) )
			$outPath .= '/';
		return $outPath;
	}
		
	
}


?>