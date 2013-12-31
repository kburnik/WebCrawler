<?

class ContentParserTestCase extends TestCase {

	private $parser;

	public function __construct() {
		$this->parser = new ContentParser();
	}
	
	private function __assertParse( $inputString , $expectedArray ) 
	{
		$result = $this->parser->parse( $inputString );
		
		$this->assertEqual( $expectedArray , $result );
				
	}

	public function testParse_simpleWordList_returnsArrayOfSameWords() 
	{
		$inputString = "first second third fourth";
		$expectedArray = array("first","second","third","fourth");
		$this->__assertParse( $inputString , $expectedArray  );
	
	}
	
	public function testParse_croatianChars_keepsCroatianChars() 
	{
		$inputString = "treći četvrti peti šesti sedmi čćžšđ";
		$expectedArray = array("treći", "četvrti", "peti", "šesti", "sedmi", "čćžšđ");
		$this->__assertParse( $inputString , $expectedArray  );	
	}
	
	public function testParse_mixedCaseLetter_keepsCase() 
	{
		$inputString = "treći čeTvrti PetI šesti sedmi žedan čćžšđ";
		$expectedArray = array("treći", "čeTvrti", "PetI", "šesti", "sedmi", "žedan","čćžšđ");
		$this->__assertParse( $inputString , $expectedArray  );	
	}
	
	public function testParse_simpleWordListWithExtraSpaces_ignoresExtraSpaces() 
	{
		$inputString = "first   second   third   fourth   ";
		$expectedArray = array("first","second","third","fourth");
		$this->__assertParse( $inputString , $expectedArray  );
	}
	
	
	public function testParse_simpleWordListWithOneSpaceAtEnd_ignoresExtraSpacesAndEndSpace() 
	{
		$inputString = "first   second   third   fourth ";
		$expectedArray = array("first","second","third","fourth");
		$this->__assertParse( $inputString , $expectedArray  );
	}
	
	public function testParse_simpleTagsWithWords_ignoresTags() {
		$inputString = "first<br>   second <p>  third</p>   <div><span>fourth</span>  ";
		$expectedArray = array("first","second","third","fourth");
		$this->__assertParse( $inputString , $expectedArray  );
	}
	
	public function testParse_javascript_stripsJavascript() 
	{
	
		$inputString = "first   second   third   fourth   
			<script> var x = 100;
				console.log( x ); 
				for (var i = 0; i < 10; i++)
						console.log( i );
			</script>
			fifth
			";
		$expectedArray = array("first","second","third","fourth","fifth");
		$this->__assertParse( $inputString , $expectedArray  );
	
	}
	
	public function testParse_javascript_stripsJavascriptWithAttributes() 
	{
	
		$inputString = "first   second   third   fourth   
			<script type='sometype/somelang'> var x = 100;
				console.log( x ); 
				for (var i = 0; i < 10; i++)
						console.log( i );
			</script>
			fifth
			";
		$expectedArray = array("first","second","third","fourth","fifth");
		$this->__assertParse( $inputString , $expectedArray  );
	
	}
	
	public function testParse_css_removesCSS() 
	{
	
		$inputString = "first   second   third   fourth   
			<style type='text/css'>
				.someclass {
					width:100px;
					height:300px;
				}
				
				#someid {
					display:block;
				}
				
				table tr td {
					outline:1px solid #DDD;
				}
				
			</style>
			fifth
			";
		$expectedArray = array("first","second","third","fourth","fifth");
		$this->__assertParse( $inputString , $expectedArray  );
	
	}
	
	
	public function testParse_css_removesCSSWithoutMatchingClosingTag() 
	{
	
	 $inputString = "first   second   
			<style type='text/css'>
				.someclass {
					width:100px;
					height:300px;
				}
				
				#someid {
					display:block;
				}
				
				table tr td {
					outline:1px solid #DDD;
				}
				
			<style>
			notvisible
			";
		$expectedArray = array("first","second");
		$this->__assertParse( $inputString , $expectedArray  );
	}
	
	public function testParse_ignoreSpecialChars() 
	{
		$inputString = "one,two,three-four";
		$expectedArray = array("one","two","three","four");
		$this->__assertParse( $inputString , $expectedArray  );
	
	}
	
	
	public function testParse_realWorldSample1_returnsWordsAndNumbers() 
	{
		
		$inputString = file_get_contents(dirname(__FILE__) ."/test.input.1.html");
		$expectedArray = include(dirname(__FILE__) ."/test.output.1.php");
		
		$this->__assertParse( $inputString , $expectedArray  );
	
	}
	
	
	public function testParse_realWorldFullHTML_returnsWordsAndNumbers() 
	{
		
		$inputString = file_get_contents(dirname(__FILE__) ."/test.input.2.html");
		$expectedArray = include(dirname(__FILE__) ."/test.output.2.php");
		//$result = $this->parser->parse( $inputString );
		/* file_put_contents(dirname(__FILE__) ."/test.output.2.php","<? return ".var_export( $result ,true)."?>");*/
		
		$this->__assertParse( $inputString , $expectedArray  );
	}

}

?>