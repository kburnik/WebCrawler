<?

abstract class HttpClient implements IHttpClient 
{

	public function getLinksFromPage( $fetchedPage ) 
	{
	
		if ( ! $fetchedPage instanceOf FetchedPage ) 
		{
			throw new Exception( 
				"Expected FetchedPage, got " 
				. var_export( $fetchedPage , true ) 
			);
		}
		
		$htmlDocument = phpQuery::newDocumentHTML( $fetchedPage->contents );
		
		$links = pq( 'a' , $htmlDocument );
		
		return $links;
	}
	
	public function getAbsoluteURL( $currentURL , $relativeURL ) 
	{
		return $this->_url_to_absolute( $currentURL , $relativeURL );
	}
	
	
	private function _url_to_absolute( $baseUrl, $relativeUrl )
	{
		// If relative URL has a scheme, clean path and return.
		$r = parse_url( $relativeUrl );
		if ( $r === FALSE )
			return FALSE;
		if ( !empty( $r['scheme'] ) )
		{
			if ( !empty( $r['path'] ) && $r['path'][0] == '/' )
				$r['path'] = $this->_url_remove_dot_segments( $r['path'] );
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
				$r['path'] = $this->_url_remove_dot_segments( $r['path'] );
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
		$r['path'] = $this->_url_remove_dot_segments( $r['path'] );
		return http_build_url( "" , $r );
	}
	
	
	private function _url_remove_dot_segments( $path )
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