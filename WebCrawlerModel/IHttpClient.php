<?
interface IHttpClient 
{
	
	// return array( pageContents , statusCode )
	public function getPageContentsWithStatusCode( $url );
	
	public function getLinksFromPage( $fetchedPage );
	
	public function getAbsoluteUrl( $currentURL , $relativeURL );
	
}

?>