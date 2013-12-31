<?

class PageLinkTestModule extends TestUnitModule {

	public function createPageLink_validURL_hasGivenURLSet() 
	{
		
		$validURL =  "http://www.invision-web.net/";
		
		$pageLink = new PageLink( $validURL  ) ;
		
		$this->assertEqual( $validURL , $pageLink->url );
	
	}
	
	public function PageLinktoString_validURL_ReturnsSameURL() 
	{
		
		$validURL =  "http://www.invision-web.net/web/";
		
		$pageLink = new PageLink( $validURL ) ;
		
		$this->assertEqual( $validURL , (string) $pageLink );
	
	}


}


?>