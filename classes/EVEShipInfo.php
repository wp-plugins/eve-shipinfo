<?php
/**
 * File containing the {@link EVEShipInfo} class.
 * 
 * @package EVEShipInfo
 * @see EVEShipInfo
 */

/**
 * The main plugin interface
 * @see EVEShipInfo_Plugin
 */
require_once dirname(__FILE__).'/EVEShipInfo/Plugin.php';

/**
 * Main plugin class for the EVE ShipInfo plugin. Registers
 * all required hooks, and implements most of the functionality.
 * Some special functionality is split into subclasses.
 * 
 * Generation of the virtual ship pages inspired by several
 * sources.
 * 
 * @package EVEShipInfo
 * @author Sebastian Mordziol <eve@aeonoftime.com>
 * @link http://www.aeonoftime.com
 * @link https://gist.github.com/brianoz/9105004
 */
class EVEShipInfo extends EVEShipInfo_Plugin
{
   /**
    * The name of the request variable which is used to
    * store the requested ship ID in the custom rewrite rule.
    * @var string
    * @see handle_initRewriteRules
    */
    const REQUEST_VAR_SHIP_ID = 'shipinfo_ship';
    
   /**
    * The name of the request variable which is used to
    * determine wether the ships list virtual page should
    * be shown.
    * 
    * @var string
    * @see handle_initRewriteRules
    */
    const REQUEST_VAR_SHIPSLIST = 'shipinfo_list';
    
   /**
    * @var EVEShipInfo
    */
	protected static $instance;
	
   /**
    * Retrieves/creates the global instance of the plugin.
    * Only one instance is needed per request.
    * 
    *  @return EVEShipInfo
    */
	public static function getInstance()
	{
		if(!isset(self::$instance)) {
			self::$instance = new EVEShipInfo();
		}
		
		return self::$instance;
	}
	
	protected $pluginFile;
	
   /**
    * The constructor sets up some vital properties and
    * registers essential hooks.
    */
	public function __construct()
	{
		$this->pluginFile = realpath(dirname(__FILE__).'/../eve-shipinfo.php');
		$this->dir = plugin_dir_path($this->pluginFile);
		$this->url = plugin_dir_url($this->pluginFile);
		$this->basename = plugin_basename($this->pluginFile);
		
		register_activation_hook($this->pluginFile, array($this, 'handle_activatePlugin'));
		register_deactivation_hook($this->pluginFile, array($this, 'handle_deactivatePlugin'));
		 
		add_action('init', array($this, 'handle_init'));
		add_action('admin_init', array($this, 'handle_initPluginSettings'));
		add_action('admin_menu', array($this, 'handle_initAdminMenu'));
		add_action('parse_query', array($this, 'handle_resolveContentToDisplay'));
		 
		load_plugin_textdomain('EVEShipInfo', false, $this->dir.'/languages');
	}
	
   /**
    * @var EVEShipInfo_Collection_Ship
    */
	protected $activeShip;
	
   /**
    * Retrieves the active ship, if any. 
    * @return EVEShipInfo_Collection_Ship|NULL
    */
	public function getActiveShip()
	{
	    if(!$this->isShipPage()) {
	        return null;
	    }
	    
	    if(!isset($this->activeShip)) {
	       $collection = $this->createCollection();
	       $this->activeShip = $collection->getShipByID($this->getShipID());
	    }
	    
	    return $this->activeShip;
	}
	
   /**
    * Generates a virtual post for the selected virtual page,
    * by delegating the rendering of the content  to one of
    * the virtual page classes.
    * 
    * Note: This hook is only added if a virtual page has been
    * requested. 
    * 
    * @param array $posts
    * @return array()
    * @hook the_post
    */
	public function handle_generateVirtualPost($posts)
	{
		// we need the base class for virtual pages
		$this->loadClass('EVEShipInfo_VirtualPage');
		
		$className = 'EVEShipInfo_VirtualPage_'.$this->virtualPageName;   
	    $this->loadClass($className);
	    
	    $page = new $className($this);
	    
	    // make sure the class is a valid virtual page class
	    if(!$page instanceof EVEShipInfo_VirtualPage) {
	    	$this->registerError(
	    		sprintf(
	    			__('The class %1$s is not a valid virtual page, it must extend the %2$s class.', 'EVEShipInfo'),
	    			'['.$className.']',
	    			'[EVEShipInfo_VirtualPage]'
	    		),
	    		self::ERROR_NOT_A_VALID_VIRTUAL_PAGE
	    	);
	    	return $posts;
	    }
	    
	    // create the post: we simply use the first page from the database
	    // that we find, and replace its contents with ours. This way we 
	    // don't mess around with any of the other post settings and variables,
	    // only the essentials like the title and content.
	    $virtual = $this->getDummyPage();
	    if(!$virtual) {
	    	return $posts;
	    }
	    
	    $virtual->post_title = $page->renderTitle();
	    $virtual->post_content = $page->renderContent();
	    $virtual->guid = $page->getGUID();
	    $virtual->post_name = $page->getPostName();
	    
	    /* @var $wp_query WP_Query */
	    global $wp_query;
	    
	    // make sure that wordpress treats this virtual post
	    // as a single page.
	    $wp_query->is_page = true;
	    $wp_query->is_singular = true;
	    $wp_query->is_home = false;
	    $wp_query->is_archive = false;
	    $wp_query->is_category = false;
	    $wp_query->is_404 = false;
	    
	    // and we return a posts collection containing only our virtual page.
	    return array($virtual);
	}
	
	public function getDummyPage()
	{
		$pages = get_pages(array('number' => 1));
		if(!empty($pages)) {
		    return $pages[0];
		}
		 
		return null;
	}	
	
	public function registerError($errorMessage, $errorCode)
	{
		// FIXME What to do with these?	
	}
	
   /**
    * Checks whether the blog has URL rewriting enabled.
    * @return boolean
    */
	public function isBlogURLRewritingEnabled()
	{
		$structure = get_option('permalink_structure');
		return !empty($structure);
	}
	
	protected $shipID = null;
	
	protected $virtualPageName;
	
   /**
    * Checks the request vars and stores the ID of the ship to show
    * if the user requested to view a ship's detail page. We do this
    * with the parse_query hook, so we only have to do this once in
    * the request.
    * 
    * @param WP_Query $wp_query
    * @hook parse_query
    */
	public function handle_resolveContentToDisplay($wp_query)
	{
		// due to how permalinks are created, the linking between
		// the virtual ship pages will only work correctly when
		// url rewriting is enabled (regardless of the chosen 
		// rewriting structure).
		if(!$this->isBlogURLRewritingEnabled()) {
			return;
		}
		
		if(!$this->isVirtualPagesEnabled()) {
			return;
		}
		
		// a specific ship ID has been requested.
	    if(isset($wp_query->query_vars[self::REQUEST_VAR_SHIP_ID])) 
	    {
	    	// identifier may be a ship ID or ship name
	    	$identifier = urldecode($wp_query->query_vars[self::REQUEST_VAR_SHIP_ID]);
	    	$collection = $this->createCollection();
	    	
	    	// we will only really display the ship page if 
	    	// the ship exists in the collection, otherwise
	    	// we simply ignore the request.
	    	if(is_numeric($identifier) && $collection->shipIDExists($identifier)) {
	    		$this->virtualPageName = 'ShipDetail';
	    		$this->shipID = $identifier;
	    	} else if($collection->shipNameExists($identifier)) {
	    		$this->virtualPageName = 'ShipDetail';
	    		$this->shipID = $collection->getShipByName($identifier)->getID();
	    	}
	    } 
	    // the ships overview list has been requested.
	    else if(isset($wp_query->query_vars[self::REQUEST_VAR_SHIPSLIST])) 
	    {
	    	$this->virtualPageName = 'ShipFinder';
	    	wp_enqueue_script('jquery');
	    	wp_enqueue_script('jquery-ui-dialog');
	    	wp_enqueue_script('eveshipinfo_shipfinder');
	    }
	     
	    // now that we know we want to display a virtual page, we 
	    // can add the filters we'll need. Fortunately these all 
	    // happen after the parse_query hook, so we can do this here.
	    if(isset($this->virtualPageName)) {
	    	add_filter('the_posts', array($this, 'handle_generateVirtualPost'));
	    	add_filter( 'template_include', array($this, 'handle_chooseTemplate') );
	    	add_filter( 'body_class', array($this, 'handle_initVirtualPage') );
	    }
	}
	
	public function handle_initVirtualPage($classes)
	{
		$classes[] = 'eveshipinfo';
		$classes[] = 'virtual-'.strtolower($this->virtualPageName);
		
		return $classes;
	}
	
	public function handle_chooseTemplate()
	{
		$template = locate_template('page.php');
		return $template;
	}
	
	public function handle_activatePlugin()
	{
	    
	}
	
	public function handle_deactivatePlugin()
	{
	    
	}
	
   /**
    * Checks whether the current page is a ships overview list.
    * @return boolean
    */
	public function isShipsList()
	{
		if($this->virtualPageName=='ShipsList') {
			return true;
		}
		
		return false;
	}
	
   /**
    * Checks whether the current page is a ship detail page.
    * @return boolean
    */
	public function isShipPage()
	{
	    if($this->virtualPageName == 'ShipDetail') {
	        return true;
	    }
	    
	    return false;
	}
	
   /**
    * Retrieves the ID of the ship that has been requested, or
    * NULL otherwise.
    * 
    * @return integer|NULL
    */
	public function getShipID()
	{
	    return $this->shipID;
	}
	
	public function handle_init()
	{
	    $this->handle_initRewriteRules();
	    $this->handle_initShortcodes();
	    $this->handle_initScripts();
	}
	
   /**
    * Initializes all the shortcodes that come bundled with the plugin.
    * Each shortcode is in a separate class.
    * 
    * @hook init
    */
	protected function handle_initShortcodes()
	{
		// no need to register the shortcodes in the admin area
		if(is_admin()) {
			return;
		}
		
		$shortcodes = $this->getShortcodes();
		
		foreach($shortcodes as $instance) {
			add_shortcode($instance->getTagName(), array($instance, 'handle_call'));
		}
	}
	
   /**
    * Retrieves an indexed array containing instances of 
    * each of all available shortcodes bundled with the plugin.
    * 
    * @return multitype:EVEShipInfo_Shortcode
    */
	public function getShortcodes()
	{
		$ids = $this->getShortcodeIDs();
		$shortcodes = array();
		foreach($ids as $id) {
			$shortcodes[] = $this->createShortcode($id);
		}
		
		return $shortcodes;
	}
	
	protected $shortcodeIDs;
	
	public function getShortcodeIDs()
	{
		if(isset($this->shortcodeIDs)) {
			return $this->shortcodeIDs;
		}
		
		$this->shortcodeIDs = array();
		
		$folder = $this->getDir().'/classes/EVEShipInfo/Shortcode';
		if(!file_exists($folder)) {
		    return $this->shortcodeIDs;
		}
		
		$d = new DirectoryIterator($folder);
		foreach($d as $item) {
		    $file = $item->getFilename();
		    $ext = pathinfo($file, PATHINFO_EXTENSION);
		    if($ext != 'php') {
		        continue;
		    }
		     
		    $this->shortcodeIDs[] = str_replace('.php', '', $file);
		}

		return $this->shortcodeIDs;
	}
	
	public function createShortcode($id)
	{
		$this->loadClass('EVEShipInfo_Shortcode');
		
		$class = 'EVEShipInfo_Shortcode_'.$id;
		$this->loadClass($class);

		$instance = new $class($this);
		return $instance;    
	}
	
   /**
    * @var EVEShipInfo_EFTManager
    */
	protected $eftManager;
	
   /**
    * Creates/gets the helper class used to retrieve information
    * about the EFT XML export, when available (when the user has
    * uploaded one).
    * 
    * @return EVEShipInfo_EFTManager
    */
	public function createEFTManager()
	{
		if(isset($this->eftManager)) {
			return $this->eftManager;
		}
		
		$this->loadClass('EVEShipInfo_EFTManager');
		
		$this->eftManager = new EVEShipInfo_EFTManager($this);
		return $this->eftManager;
	}
	
	protected function handle_initScripts()
	{
		// don't enqueue the scripts in the admin area
		if(is_admin()) {
			return;
		}
		
		add_action('wp_head', array($this, 'handle_renderJavascriptHead'));
		
		wp_register_script('eveshipinfo', $this->getScriptURL('EVEShipInfo.js'), array('jquery'));
		wp_register_script('eveshipinfo_ship', $this->getScriptURL('EVEShipInfo/Ship.js'), array('eveshipinfo'));
		wp_register_script('eveshipinfo_shipfinder', $this->getScriptURL('EVEShipInfo/ShipFinder.js'), array('eveshipinfo'));
		
		wp_enqueue_script('eveshipinfo');
		wp_enqueue_script('eveshipinfo_ship');
		wp_enqueue_script('eveshipinfo_translation');
		
		wp_register_style('eveshipinfo', $this->getScriptURL('EVEShipInfo.css'));
		wp_register_style('eveshipinfo_light', $this->getScriptURL('ThemeLight.css'), array('eveshipinfo'));
		
		wp_enqueue_style('eveshipinfo');
		wp_enqueue_style('eveshipinfo_light');
	}
	
   /**
    * Renders and echos the javascript code required for the clientside
    * translations. This is added to the page header.
    * 
    * @hook wp_head
    */
	public function handle_renderJavascriptHead()
	{
		$strings = array(
	        'Slots' => __('Slots', 'EVEShipInfo'),
	        'Cargo bay' => __('Cargo bay', 'EVEShipInfo'),
	        'Drones' => __('Drones', 'EVEShipInfo'),
	        'No launchers' => __('No launchers', 'EVEShipInfo'),
	        'X launchers' => __('%s launchers', 'EVEShipInfo'),
	        '1 launcher' => __('1 launcher', 'EVEShipInfo'),
	        'No turrets' => __('No turrets', 'EVEShipInfo'),
	        'X turrets' => __('%s turrets', 'EVEShipInfo'),
	        '1 turret' => __('1 turret', 'EVEShipInfo'),
	        'Warp speed' => __('Warp speed', 'EVEShipInfo'),
	        'Agility' => __('Agility', 'EVEShipInfo'),
	        'Max velocity' => __('Max velocity', 'EVEShipInfo'),
	        'None' => __('None', 'EVEShipInfo'),
	        'Capacitor' => __('Capacitor', 'EVEShipInfo'),
	        'X recharge rate' => __('%s recharge rate', 'EVEShipInfo'),
	        'X power output' => __('%s power output', 'EVEShipInfo'),
	        'X capacitor capacity' => __('%s capacity', 'EVEShipInfo'),
	        'Shield' => __('Shield', 'EVEShipInfo'),
	        'Armor' => __('Armor', 'EVEShipInfo'),
	        'Structure' => __('Structure', 'EVEShipInfo'),
	        'X signature radius' => __('%s signature radius', 'EVEShipInfo'),
	        'Max target range' => __('Max target range', 'EVEShipInfo'),
	        'Max locked targets' => __('Max locked targets', 'EVEShipInfo'),
	        'Scan speed' => __('Scan speed', 'EVEShipInfo'),
	        'Scan resolution' => __('Scan resolution', 'EVEShipInfo'),
		);
		
		$lines = array();
		foreach($strings as $key => $text) {
		    $lines[] = "'".$key."':'".addslashes($text)."'";
		}
		
		$content =
		"<script type=\"text/javascript\">
/**
 * Container for localized clientside strings.
 * @module EVEShipInfo
 * @class EVEShipInfo_Translation
 * @static
 */
var EVEShipInfo_Translation = {".
	'translations:{'.
		implode(',', $lines).
	'},'.
	'Translate:function(name) {'.
		"if(typeof(this.translations[name]!='undefined')) {".
			'return this.translations[name];'.
		'}'.
		'return name;'.
	'}'.
'};'.
		'</script>';
		
		echo $content;
	}
	
   /**
    * Retrieves the absolute URL to a javascript or stylesheet file
    * from the plugin's folder.
    * 
    * @param string $file
    * @return string
    */
	protected function getScriptURL($file)
	{
		$folder = 'js';
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		if($ext=='css') {
			$folder = 'css';
		}
		
		return rtrim($this->getURL(), '/').'/'.$folder.'/'.$file;
	}
	
   /**
    * Initializes the plugin's custom rewrite rules used to
    * display the special ship pages.
    * 
    * @hook init
    */
	protected function handle_initRewriteRules()
	{
	    add_rewrite_tag('%'.self::REQUEST_VAR_SHIP_ID.'%', '([0-9a-zA-Z \-\'%]+)');
	    add_rewrite_rule(
	       'eve/ship/([0-9a-zA-Z \-\'%]+)',
	       'index.php?'.self::REQUEST_VAR_SHIP_ID.'=$matches[1]',
	       'top'
	    );
	    
	    add_rewrite_tag('%'.self::REQUEST_VAR_SHIPSLIST.'%', '([1]{1})');
	    add_rewrite_rule(
	    	'eve/ships?',
	    	'index.php?'.self::REQUEST_VAR_SHIPSLIST.'=1',
	    	'top'
	    );
	}
	
	public function handle_initPluginSettings()
	{
		$basic = $this->createPage('Settings', '')->selectTab('Basic')->getActiveTab();
		$basic->initSettings();
	}
	
	public function handle_initAdminMenu()
	{
	    // Adds a link in the plugins list to the plugin's settings.
	    add_filter(
	       'plugin_action_links_'.$this->basename, 
	       array($this, 'handle_renderSettingsLink')
	    );
	    
	    // Adds an option page for the plugin under the "Settings" menu.
	    add_options_page(
	       __('EVE ShipInfo settings', 'EVEShipInfo'), 
	       __('EVE ShipInfo', 'EVEShipInfo'), 
	       'manage_options', 
	       'eveshipinfo_settings',
	       array($this, 'handle_displaySettingsPage')
	    );
	    
	    add_menu_page(
	    	__('EVE ShipInfo', 'EVEShipInfo'),
	    	__('EVE ShipInfo', 'EVEShipInfo'),
	    	'edit_posts',
	    	'eveshipinfo',
	    	array($this, 'handle_displayMainPage')
	    );
	    
	    add_submenu_page(
		    'eveshipinfo',
		    __('Dashboard', 'EVEShipInfo'),
		    __('Dashboard', 'EVEShipInfo'),
		    'edit_posts',
		    'eveshipinfo',
		    array($this, 'handle_displayMainPage')
	    );
	    
	    add_submenu_page(
		    'eveshipinfo',
		    __('Help and Documentation', 'EVEShipInfo'),
		    __('Help', 'EVEShipInfo'),
		    'edit_posts',
		    'eveshipinfo_help',
		    array($this, 'handle_displayHelpPage')
	    );
	    
	    add_submenu_page(
	   		'eveshipinfo',
		    __('Database reference', 'EVEShipInfo'),
		    __('Database', 'EVEShipInfo'),
		    'edit_posts',
		    'eveshipinfo_database',
		    array($this, 'handle_displayDatabasePage')
	    );
	    
	    add_submenu_page(
	    	'eveshipinfo',
	    	__('Shortcodes reference', 'EVEShipInfo'),
	    	__('Shortcodes', 'EVEShipInfo'),
	    	'edit_posts',
	    	'eveshipinfo_shortcodes',
	    	array($this, 'handle_displayShortcodesPage')
	    );

	    add_submenu_page(
	    'eveshipinfo',
		    __('EFT import', 'EVEShipInfo'),
		    __('EFT import', 'EVEShipInfo'),
		    'edit_posts',
		    'eveshipinfo_eftimport',
		    array($this, 'handle_displayEFTImportPage')
	    );
	     
	    $eft = $this->createEFTManager();
	    if($eft->hasFittings()) {
	    	add_submenu_page(
		    	'eveshipinfo',
		    	__('EFT fittings', 'EVEShipInfo'),
		    	__('EFT fittings', 'EVEShipInfo'),
		    	'edit_posts',
		    	'eveshipinfo_eftfittings',
		    	array($this, 'handle_displayEFTFittingsPage')
	    	);
	    }
	    	
	    
	     
	}
	
	public function handle_displayMainPage($tabID=null)
	{
		$this->createPage('Main', 'eveshipinfo')
			->selectTab($tabID)
			->display();
	}

	public function handle_displayShortcodesPage()
	{
	    $this->handle_displayMainPage('Shortcodes');
	}

	public function handle_displayEFTImportPage()
	{
		$this->handle_displayMainPage('EFTImport');
	}
	
	public function handle_displayEFTFittingsPage()
	{
		$this->handle_displayMainPage('EFTFittings');
	}
	
	public function handle_displayDatabasePage()
	{
		$this->handle_displayMainPage('Database');
	}

	public function handle_displayHelpPage()
	{
	    $this->handle_displayMainPage('Help');
	}
	
   /**
    * Renders and outputs the markup for the plugin's 
    * admin settings screen. This is delegated to a
    * separate class.
    * 
    * @see EVEShipInfo_Admin_SettingsPage
    */
	public function handle_displaySettingsPage()
	{
	    $settings = $this->createPage('Settings', '');
	    $settings->display();
	}
	
   /**
    * Creates an administration page instance.
    * @param string $id
    * @param string $slug
    * @return EVEShipInfo_Admin_Page
    */
	protected function createPage($id, $slug)
	{
		$this->loadClass('EVEShipInfo_Admin_Page');
		
		$class = 'EVEShipInfo_Admin_Page_'.$id;
		$this->loadClass($class);
		$page = new $class($this, $slug);
		
		return $page;
	}
	
   /**
    * Hook handler for the plugin_action_links hook, which adds
    * a link to the plugin's settings page in the plugins list.
    * 
    * @param array $links
    * @return array
    */
	public function handle_renderSettingsLink($links)
	{
	    $link = 
	    '<a href="'.$this->getAdminSettingsURL().'">'.
	        __('Settings', 'EVEShipInfo').
	    '</a>';
	   
	    array_unshift($links, $link);
	    
	    $link =
	    '<a href="'.$this->getAdminDashboardURL().'">'.
	    	__('Dashboard', 'EVEShipInfo').
	    '</a>';
	    
	    array_unshift($links, $link);
	     
	    return $links;
	}

   /**
    * Retrieves the URL to the plugin settings screen in the administration.
    * @return string
    * @param string $tabID The tab in the settings screen to show
    * @param array $params Associative array with additional request parameters
    */
	public function getAdminSettingsURL($tabID='Basic', $params=array())
	{
		$params['page'] = 'eveshipinfo_settings';
		$params['tab'] = $tabID;
		
	    return 'options-general.php?'.http_build_query($params, null, '&amp;');
	}
	
	public function getAdminDashboardURL()
	{
		$params['page'] = 'eveshipinfo';
		return 'admin.php?'.http_build_query($params, null, '&amp;');
	}
	
   /**
    * Retrieves the URL to the plugin's help page in the administration.
    * @param unknown $params
    */
	public function getAdminHelpURL($params=array())
	{
		return $this->getAdminSettingsURL('Help', $params);
	}
	
   /**
    * Loads a class from the plugin's classes repository.
    * @param string $className
    */
	public function loadClass($className)
	{
	    if(class_exists($className)) {
	        return;
	    }
	    
	    $file = $this->dir.'/classes/'.str_replace('_', '/', $className).'.php';
	    require_once $file;
	}
	
	protected static $jsIDCounter = 0;
	
	public function nextJSID()
	{
		self::$jsIDCounter++;
		return 'esi'.self::$jsIDCounter;
	}
	
	protected $defaultSettings = array(
		'enable_virtual_pages' => 'yes'
	);
	
	public function getSetting($name)
	{
		$default = null;
		if(isset($this->defaultSettings[$name])) {
			$default = $this->defaultSettings[$name];
		}
		
		return get_option($name, $default);
	}
	
   /**
    * Checks whether virtual pages are enabled.
    * @return boolean
    */
	public function isVirtualPagesEnabled()
	{
		if($this->getSetting('enable_virtual_pages')=='yes') {
			return true;
		}
		
		return false;
	}
	
   /**
    * @var EVEShipInfo_Admin_UI
    */
	protected $adminUI;
	
	public function getAdminUI()
	{
		if(!isset($this->adminUI)) {
		    $this->loadClass('EVEShipInfo_Admin_UI');
		    $this->adminUI = new EVEShipInfo_Admin_UI($this);
		}
		
		return $this->adminUI;
	}
}
	