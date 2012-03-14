<?php
class HomepageForDomainTest extends SapphireTest {
	
	protected $usesDatabase = true;

	static $fixture_file = 'HomepageForDomainTest.yml';

	function setUp() {
		parent::setUp();
		
		Object::add_extension("SiteTree", "FilesystemPublisher('assets/HomepageForDomainTest-static-folder/')");
		HomepageForDomainExtension::$write_homepage_map = false;
		
		$this->orig['domain_based_caching'] = FilesystemPublisher::$domain_based_caching;
		FilesystemPublisher::$domain_based_caching = false;
	}
	
	function tearDown() {
		parent::tearDown();

		Object::remove_extension("SiteTree", "FilesystemPublisher('assets/HomepageForDomainTest-static-folder/')");
		HomepageForDomainExtension::$write_homepage_map = true;

		FilesystemPublisher::$domain_based_caching = $this->orig['domain_based_caching'];

		if(file_exists(BASE_PATH . '/assets/HomepageForDomainTest-static-folder')) {
			Filesystem::removeFolder(BASE_PATH . '/assets/HomepageForDomainTest-static-folder');
		}
	}
	
	function testStaticPublishing() {
		$this->logInWithPermission('ADMIN');
		
		$p1 = new Page();
		$p1->URLSegment = strtolower(__CLASS__).'-page-1';
		$p1->HomepageForDomain = '';
		$p1->write();
		$p1->doPublish();
		$p2 = new Page();
		$p2->URLSegment = strtolower(__CLASS__).'-page-2';
		$p2->HomepageForDomain = 'domain1';
		$p2->write();
		$p2->doPublish();
		$p3 = new Page();
		$p3->URLSegment = strtolower(__CLASS__).'-page-3';
		$p3->HomepageForDomain = 'domain2,domain3';
		$p3->write();
		$p3->doPublish();
		
		$map = HomepageForDomainExtension::generate_homepage_domain_map();
		
		$this->assertEquals(
			$map, 
			array(
				'domain1' => strtolower(__CLASS__).'-page-2',
				'domain2' => strtolower(__CLASS__).'-page-3',
				'domain3' => strtolower(__CLASS__).'-page-3',
			), 
			'Homepage/domain map is correct when static publishing is enabled'
		);
	}

	function testRouting() {
		$originalHost = $_SERVER['HTTP_HOST'];

		// Tests matching an HTTP_HOST value to URLSegment homepage values
		$tests = array(
			'page.co.nz' => 'page1',
			'www.page.co.nz' => 'page1',
			'help.com' => 'page1',
			'www.help.com' => 'page1',
			'something.com' => 'page1',
			'www.something.com' => 'page1',

	 		'other.co.nz' => 'page2',
	 		'www.other.co.nz' => 'page2',
			'right' => 'page2',
			'www. right' => 'page2',

			'only.com' => 'page3',
			'www.only.com' => 'page3',
			
			'www.somethingelse.com' => 'home',
			'somethingelse.com' => 'home',
			
			// Test some potential false matches to page2 and page3
			'alternate.only.com' => 'home',
			'www.alternate.only.com' => 'home',
			'alternate.something.com' => 'home',
		);
		
		foreach($tests as $domain => $urlSegment) {
			RootURLController::reset();
			$_SERVER['HTTP_HOST'] = $domain;
			
			$this->assertEquals(
				$urlSegment, 
				RootURLController::get_homepage_link(), 
				"Testing $domain matches $urlSegment"
			);
		}
		
		$_SERVER['HTTP_HOST'] = $originalHost;
	}	

	public function testGetHomepageLink() {
		$default = $this->objFromFixture('Page', 'home');
		$nested  = $this->objFromFixture('Page', 'nested');
		
		SiteTree::disable_nested_urls();
		$this->assertEquals('home', RootURLController::get_homepage_link());
		SiteTree::enable_nested_urls();
		$this->assertEquals('home', RootURLController::get_homepage_link());
		
		$nested->HomepageForDomain = str_replace('www.', null, $_SERVER['HTTP_HOST']);
		$nested->write();
		
		RootURLController::reset();
		SiteTree::disable_nested_urls();
		$this->assertEquals('nested-home', RootURLController::get_homepage_link());
		
		RootURLController::reset();
		SiteTree::enable_nested_urls();
		$this->assertEquals('home/nested-home', RootURLController::get_homepage_link());
		
		$nested->HomepageForDomain = null;
		$nested->write();
	}
	
}