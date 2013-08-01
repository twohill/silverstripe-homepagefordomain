<?php
class HomepageForDomainExtension extends DataExtension {

	/**
	 * Whether or not to write the homepage map for static publisher
	 */
	public static $write_homepage_map = true;
	
	static $db = array(
		"HomepageForDomain" => "Varchar(100)"	
	);

	public function updateSettingsFields(&$fields) {
		$fields->addFieldsToTab("Root.Settings", array(
			new LiteralField(
				"HomepageForDomainInfo", 
				"<p>" . 
					_t('SiteTree.NOTEUSEASHOMEPAGE', 
					"Use this page as the 'home page' for the following domains: 
					(separate multiple domains with commas)") .
				"</p>"
			),
			new TextField(
				"HomepageForDomain",
				_t('SiteTree.HOMEPAGEFORDOMAIN', "Domain(s)", 'Listing domains that should be used as homepage')
			)
		));
	}

	public function onAfterPublish() {
		// Check to write CMS homepage map.
		$usingStaticPublishing = false;
		foreach(ClassInfo::subclassesFor('StaticPublisher') as $class) {
			if ($this->owner->hasExtension($class)) $usingStaticPublishing = true;
		}

		// Ff you change the path here, you must also change it in sapphire/static-main.php
		if (self::$write_homepage_map) {
			if ($usingStaticPublishing && $map = self::generate_homepage_domain_map()) {
				@file_put_contents(BASE_PATH.'/'.ASSETS_DIR.'/_homepage-map.php', "<?php\n\$homepageMap = ".var_export($map, true)."; ?>");
			} else { if (file_exists(BASE_PATH.'/'.ASSETS_DIR.'/_homepage-map.php')) unlink(BASE_PATH.'/'.ASSETS_DIR.'/_homepage-map.php'); }
		}
	}

	public function updateFieldLabels(&$labels) {
		$labels['HomepageForDomain'] = _t('SiteTree.HomepageForDomain', 'Hompage for this domain');
	}

	/**
	 * @return Array
	 */
	public static function generate_homepage_domain_map() {
		$domainSpecificHomepages = Versioned::get_by_stage('Page', 'Live', "\"HomepageForDomain\" != ''", "\"URLSegment\" ASC");
		if (!$domainSpecificHomepages) return false;
		
		$map = array();
		foreach($domainSpecificHomepages->map('URLSegment', 'HomepageForDomain') as $url => $domains) {
			foreach(explode(',', $domains) as $domain) $map[$domain] = $url;
		}
		return $map;
	}
}
