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
	
}

?>