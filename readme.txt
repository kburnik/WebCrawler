
==================================
PHP WebCrawler by Kristijan Burnik
==================================

- get the PHP framework from https://github.com/kburnik/framework

- get the PHP framework empty project from https://github.com/kburnik/framework-empty-project

- pull phpQuery from TobiaszCudnik : https://github.com/TobiaszCudnik/phpquery
  into the framework-empty-project/

- pull this repo into framework-empty-project/
  
- edit your framework-empty-project/WebCrawler/webcrawler.include.php to setup paths to the main 
  Framework project file and phpQuery


- test the crawler by running  
	cd framework-empty-project/WebCrawlerTestModule
	php testrun.php 
	
- test crawling by running from framework-empty-project/WebCrawler
	php shell.crawl.php http://www.invision-web-net/web/

	