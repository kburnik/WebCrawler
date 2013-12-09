<?
interface IHttpClient 
{
	
	// return array( pageContents , statusCode )
	public function getPageContentsWithStatusCode( $url );
	
}

?>