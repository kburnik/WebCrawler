<?

include_once( dirname(__FILE__)."/webcrawler.include.php" );

$start_url = $argv[1];

$depth = intval( $argv[2] );

$crawler = new ShellWebCrawler();

$crawler->crawl( $start_url , $depth  );


?>