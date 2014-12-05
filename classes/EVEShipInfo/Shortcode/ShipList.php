<?php

class EVEShipInfo_Shortcode_ShipList extends EVEShipInfo_Shortcode
{
	public function getTagName()
	{
		return 'shipinfo_list';
	}
	
	public function getDescription()
	{
	    return __('Allows displaying fully customizable ship lists.', 'EVEShipInfo');
	}
	
	public function getName()
	{
	    return __('Ships list', 'EVEShipInfo');
	}
	
   /**
    * @var EVEShipInfo_Collection_Filter
    */
	protected $filter;
	
	public function process()
	{
		// is this list disabled?
		if($this->getAttribute('enabled')=='no') {
			return '';
		}
		
		$this->filter = $this->collection->createFilter();
		$this->configureFilter();
		
		$this->list = $this->collection->createList($this->filter);
		$this->configureList();
		
		if(!$this->renderTemplate()) {
			$this->renderFallback();
		}		
	}
	
	public function getDefaultAttributes()
	{
		return array(
			'enabled' => 'yes',
			'template' => 'no',
			'linked' => 'yes',
			'popup' => 'yes',
			'order_by' => 'name',
			'order_dir' => 'ascending',
			'show' => 'all',
			'thumbnail_classes' => '',
			'columns' => 'name',
			'races' => '',
			'highslots' => '',
			'medslots' => '',
			'lowslots' => '',
			'search' => '',
			'groups' => '',
			'debug' => 'no',
			'column_headers' => 'yes',
			'agility' => '',
			'warpspeed' => '',
			'velocity' => ''
		);
	}
	
	protected function _describeAttributes()
	{
		$filter = $this->collection->createFilter();
		$list = $this->collection->createList($filter);
		 
	    $attribs = array(
	    	'enabled' => array(
	    	    'descr' => __('Whether the list is enabled:', 'EVEShipInfo').' '.
	    	    		   __('Disabling a list allows you to keep its shortcode intact in your post without showing it.', 'EVEShipInfo'),
	    	    'optional' => true,
	    	    'type' => 'enum',
	    		'values' => array(
	    			'no' => __('List is enabled', 'EVEShipInfo'),
	    			'yes' => __('List is disabled', 'EVEShipInfo')
	    		)
	    	),
	        'template' => array(
	            'descr' => __('The theme template file to use to render the list.', 'EVEShipInfo').' '.
	        			   sprintf(__('Set to %1$s to disable.', 'EVEShipInfo'), '<code>no</code>').' '.
	        			   sprintf(__('The template gets the filtered ships list in the %1$s variable.', 'EVEShipInfo'), '<code>$ships</code>'),
	            'optional' => true,
	            'type' => 'text'
	        ),
	        'linked' => array(
	            'descr' => __('Whether to link the ship names.', 'EVEShipInfo'),
	            'optional' => true,
	            'type' => 'enum',
	        	'values' => array(
	        		'yes' => __('Yes, link the names.', 'EVEShipInfo'),
	        		'no' => __('No, don\'t link any names.', 'EVEShipInfo')
	        	)
	        ),
	        'popup' => array(
	            'descr' => __('Whether to show the ship popup when clicked.', 'EVEShipInfo'),
	            'optional' => true,
	            'type' => 'enum',
	            'values' => array(
	                'yes' => __('Yes, show a popup', 'EVEShipInfo'),
	                'no' => __('No, link to the virtual page', 'EVEShipInfo')
	            )
	        ),
	    	'order_by' => array(
	    	    'descr' => __('The ship attribute to sort the list by.', 'EVEShipInfo'),
	    	    'optional' => true,
	    	    'type' => 'enum',
	    	    'values' => $filter->getOrderFields()
	    	),
	    	'order_dir' => array(
	    	    'descr' => __('The direction in which to sort the list.', 'EVEShipInfo'),
	    	    'optional' => true,
	    	    'type' => 'enum',
	    	    'values' => array(
	    	    	'desc' => __('In descending order', 'EVEShipInfo'),
	    	    	'descending' => __('In descending order', 'EVEShipInfo'),
	    	    	'asc' => __('In ascending order', 'EVEShipInfo'),
	    	    	'ascending' => __('In ascending order', 'EVEShipInfo')
	    	    )
	    	),
	    	'show' => array(
	    	    'descr' => __('The amount of ships to limit the list to.', 'EVEShipInfo').' '.
	    				   sprintf(__('Set to %1$s to show all available ships.', 'EVEShipInfo'), '<code>all</code>'),
	    	    'optional' => true,
	    	    'type' => 'number'
	    	),
	    	'columns' => array(
	    	    'descr' => __('The column(s) to show in the list.', 'EVEShipInfo').' '.
	    				   __('They are shown in the exact order that you specify them.', 'EVEShipInfo').' '.
	    				   __('Example:', 'EVEShipInfo').' <code>name, race, group</code>',
	    	    'optional' => true,
	    	    'type' => 'commalist',
	    		'values' => $list->getColumns()
	    	),
	    	'races' => array(
	    	    'descr' => __('The race(s) to limit the list to.', 'EVEShipInfo').' '.
	    				   __('Example:', 'EVEShipInfo').' <code>minmatar, caldari</code>',
	    	    'optional' => true,
	    	    'type' => 'commalist',
	    		'values' => $filter->describeRaces()
	    	),
	    	'highslots' => array(
	    		'descr' => sprintf(__('The amount of %1$s slots to limit the list to.', 'EVEShipInfo'), __('high', 'EVEShipInfo')).' '.
	    				   __('This allows complex selections using expressions.', 'EVEShipInfo'),
	    		'optional' => true,
	    		'type' => 'text',
	    		'values' => $this->describeNumericExpressions()
	    	),
	    	'lowslots' => array(
	    	    'descr' => sprintf(__('The amount of %1$s slots to limit the list to.', 'EVEShipInfo'), __('low', 'EVEShipInfo')).' '.
	    	    		   __('This allows complex selections using expressions.', 'EVEShipInfo'),
	    	    'optional' => true,
	    	    'type' => 'text',
	    	    'values' => $this->describeNumericExpressions()
	    	),
	    	'medslots' => array(
	    	    'descr' => sprintf(__('The amount of %1$s slots to limit the list to.', 'EVEShipInfo'), __('med', 'EVEShipInfo')).' '.
	    	    __('This allows complex selections using expressions.', 'EVEShipInfo'),
	    	    'optional' => true,
	    	    'type' => 'text',
	    	    'values' => $this->describeNumericExpressions()
	    	),
	    	'search' => array(
	    	    'descr' => __('Limits the list to ships matching the search term either in their name or their description.', 'EVEShipInfo'),
	    	    'optional' => true,
	    	    'type' => 'text'
	    	),
	    	'column_headers' => array(
	    	    'descr' => __('Whether to display the column headers.', 'EVEShipInfo'),
	    	    'optional' => true,
	    	    'type' => 'enum',
	    		'values' => array(
	    			'yes' => __('Yes, show', 'EVEShipInfo'),
	    			'no' => __('No, don\'t show', 'EVEShipInfo')
	    		)
	    	),
	    	'debug' => array(
	    	    'descr' => __('Whether to display debugging information above the list.', 'EVEShipInfo').' '.
	    				   __('Useful when something does not work as expected, since this will also show any list validation messages.', 'EVEShipInfo'),
	    	    'optional' => true,
	    	    'type' => 'enum',
	    		'values' => array(
	    			'yes' => __('Yes, show', 'EVEShipInfo'),
	    			'no' => __('No, don\'t show', 'EVEShipInfo')
	    		)
	    	),
	    	'groups' => array(
	    		'descr' => __('The ship group(s) to limit the list to.', 'EVEShipInfo').' '.
	    				   __('The first groups in the list are special convenience groups that automatically select all ship groups of the same hull size.', 'EVEShipInfo').' '.
	    				   __('Example:', 'EVEShipInfo').' <code>cruiser, command ship</code>',
	    		'optional' => true,
	    		'type' => 'commalist',
	    		'values' => $filter->describeGroups()
	    	),
	    	'agility' => array(
    	        'descr' => __('The ship agility values to limit the list to.', 'EVEShipInfo').' '.
    	        		   __('This allows complex selections using expressions.', 'EVEShipInfo'),
    	        'optional' => true,
    	        'type' => 'text',
    	        'values' => $this->describeNumericExpressions()
	    	),
	    	'warpspeed' => array(
    	        'descr' => __('The ship warp speed values to limit the list to.', 'EVEShipInfo').' '.
    	        		   __('This allows complex selections using expressions.', 'EVEShipInfo'),
    	        'optional' => true,
    	        'type' => 'text',
    	        'values' => $this->describeNumericExpressions()
	    	),
	    	'velocity' => array(
	    		'descr' => __('The ship\'s maximum velocity to limit the list to.', 'EVEShipInfo').' '.
	    				   __('This allows complex selections using expressions.', 'EVEShipInfo'),
	    		'optional' => true,
	    		'type' => 'text',
	    		'values' => $this->describeNumericExpressions()
	    	)
	    );
	    
	    return $attribs;
	}
	
	protected $numericExpressionsDescribed;
	
	protected function describeNumericExpressions()
	{
		if(!isset($this->numericExpressionsDescribed)) {
			$this->numericExpressionsDescribed = array(
			    '5' => sprintf(__('Exactly %1$s', 'EVEShipInfo'), 5),
			    'bigger than 5' => sprintf(__('Any number above %1$s', 'EVEShipInfo'), 5),
			    'smaller than 5' => sprintf(__('Any number below %1$s', 'EVEShipInfo'), 5),
			    'bigger or equals 5' => sprintf(__('Any number above or exactly %1$s', 'EVEShipInfo'), 5),
				'smaller or equals 5' => sprintf(__('Any number below or exactly %1$s', 'EVEShipInfo'), 5),
				'between 3 and 5' => sprintf(__('Any number including and between %1$s and %2$s', 'EVEShipInfo'), 3, 5)
			);
		}
		
		return $this->numericExpressionsDescribed;
	}
	
	protected function renderTemplate()
	{
		if($this->getAttribute('template')=='no') {
			return false;
		}
		
		// is a template for the list present in the theme? Use that.
		$tmpl = locate_template('shipinfo_list.php');
		if(empty($tmpl)) {
			return false;
		}
		
		// make the relevant variables available to the template
		$collection = $this->collection;
		$filter = $this->filter;
		$ships = $this->filter->getShips();
		
		ob_start();
		require $tmpl;
	    $this->content = ob_get_clean();
	    
	    return true;
	}
	
	protected function isAscending()
	{
		if(in_array($this->getAttribute('order_dir'), array('descending', 'desc'))) {
			return false;
		}
		
		return true;
	}
	
	protected function renderFallback()
	{
		$this->content = $this->list->render();
	}
	
   /**
    * @var EVEShipInfo_Collection_List
    */
	protected $list;
	
	protected function configureList()
	{
		if($this->getAttribute('debug')=='yes') {
		    $this->list->enableDebug();
		}
		
		if($this->getAttribute('linked')=='yes') {
		    $this->list->enableLinks();
		}
		
		if($this->getAttribute('popup')=='yes') {
		    $this->list->enablePopups();
		}
		
		if($this->getAttribute('column_headers')=='no') {
			$this->list->disableColumnHeaders();
		}
		
		$thumbClasses = $this->getAttribute('thumbnail_classes');
		if(!empty($thumbClasses)) {
		    $classes = array_map('trim', explode(' ', $thumbClasses));
		    $this->list->addThumbnailClasses($classes);
		}
		
		$this->list->enableColumns($this->parseCommaAttribute('columns'));
	}

	protected function parseCommaAttribute($attribName)
	{
		$value = trim($this->getAttribute($attribName));
		if(empty($value)) {
		    return array();
		}
		
		$items = array_map('strtolower', array_map('trim', explode(',', $value)));
		return $items;
	}
	
	protected function configureFilter()
	{
		$this->filter->setOrderBy($this->getAttribute('order_by'), $this->isAscending());
		
		$show = $this->getAttribute('show');
		if(is_numeric($show)) {
		    $this->filter->setLimit($show);
		}
		
		$this->filter->selectRaceNames($this->parseCommaAttribute('races'));
		$this->filter->selectGroupNames($this->parseCommaAttribute('groups'));
		
		$highslots = trim($this->getAttribute('highslots'));
		if(!empty($highslots)) {
			$this->filter->selectHighSlots($highslots);
		}

		$medslots = trim($this->getAttribute('medslots'));
		if(!empty($medslots)) {
			$this->filter->selectMedSlots($medslots);
		}
	
		$lowslots = trim($this->getAttribute('lowslots'));
		if(!empty($lowslots)) {
			$this->filter->selectLowSlots($lowslots);
		}
		
		$search = trim($this->getAttribute('search'));
		if(!empty($search)) {
			$this->filter->selectSearch($search);	
		}
		
		$agility = trim($this->getAttribute('agility'));
		if(!empty($agility)){
			$this->filter->selectAgility($agility);
		}
		
		$warpspeed = trim($this->getAttribute('warpspeed'));
		if(!empty($warpspeed)){
		    $this->filter->selectWarpSpeed($warpspeed);
		}
		
		$velocity = trim($this->getAttribute('velocity'));
		if(!empty($velocity)) {
			$this->filter->selectVelocity($velocity);
		}
	}
	
	protected function _getExamples()
	{
		return array(
			array(
				'shortcode' => '[TAGNAME]',
				'descr' => __('Lists all ships in the database.', 'EVEShipInfo')
			),
			array(
				'shortcode' => '[TAGNAME show="10"]',
				'descr' => sprintf(__('Lists the first %1$s ships from the database.', 'EVEShipInfo'), '10')
			),
			array(
				'shortcode' => '[TAGNAME races="minmatar"]',
				'descr' => sprintf(__('Lists all %1$s ships.', 'EVEShipInfo'), 'Minmatar')
			),
			array(
			    'shortcode' => '[TAGNAME groups="assault frigate,interceptor"]',
			    'descr' => __('Lists all assault frigates and interceptors.', 'EVEShipInfo')
			),
			array(
				'shortcode' => '[TAGNAME search="stabber"]',
				'descr' => sprintf(__('Lists all ships with the search term %1$s in their name or description.', 'EVEShipInfo'), 'stabber')
			),
			array(
				'shortcode' => '[TAGNAME show="10" columns="name, agility" order_by="agility" order_dir="descending"]',
				'descr' => sprintf(__('Lists the %1$s most agile ships in the database, showing the agility values.', 'EVEShipInfo'), '10')
			),
			array(
				'shortcode' => '[TAGNAME columns="name, highslots" races="minmatar" highslots="bigger than 6"]',
				'descr' => sprintf(__('Lists all %1$s ships with over %2$s high slots.', 'EVEShipInfo'), 'Minmatar', 6)
			),
			array(
			    'shortcode' => '[TAGNAME columns="name, group" highslots="3" medslots="3" lowslots="3"]',
			    'descr' => sprintf(__('Lists all ships with exactly %1$s high, med and low slots.', 'EVEShipInfo'), 3)
			),
		);
	} 
}