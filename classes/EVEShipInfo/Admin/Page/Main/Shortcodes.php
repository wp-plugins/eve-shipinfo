<?php

class EVEShipInfo_Admin_Page_Main_Shortcodes extends EVEShipInfo_Admin_Page_Tab
{
	protected $shortcodes = array();
	
   /**
    * @var EVEShipInfo_Shortcode
    */
	protected $shortcode;
	
	public function __construct(EVEShipInfo_Admin_Page $page)
	{
	    parent::__construct($page);
	    
	    $ids = $this->plugin->getShortcodeIDs();
	    if(isset($_REQUEST['shortcode']) && in_array($_REQUEST['shortcode'], $ids)) {
	    	$this->shortcode = $this->plugin->createShortcode($_REQUEST['shortcode']);
	    }
	}
	
	public function getTitle()
	{
		if(isset($this->shortcode)) { 
			return __('Shortcode reference:', 'EVEShipInfo').' '.$this->shortcode->getName();
		}
		
		return __('Shortcodes reference', 'EVEShipInfo');
	}
	
	public function render()
	{
		if(isset($this->shortcode)) {
			return $this->renderShortcode();
		}
		
		$shortcodes = $this->plugin->getShortcodes();
		
		/* @var $shortcode EVEShipInfo_Shortcode */
		
		$html = 
		'<p>'.
			__('Click on a shortcode to view a detailed explanation on how to use it.', 'EVEShipInfo').
		'</p>'.
		'<table class="wp-list-table widefat">'.
			'<thead>'.
				'<tr>'.
					'<th>'.__('Name', 'EVEShipInfo').'</th>'.
					'<th>'.__('Shortcode', 'EVEShipInfo').'</th>'.
					'<th>'.__('Description', 'EVEShipInfo').'</th>'.
				'</tr>'.
			'</thead>'.
			'<tbody>';
				foreach($shortcodes as $shortcode) {
					$html .=
					'<tr>'.
						'<td>'.
							'<a href="'.$shortcode->getAdminHelpURL().'">'.
								$shortcode->getName().
							'</a>'.
						'</td>'.
						'<td><code>['.$shortcode->getTagName().']</code></td>'.
						'<td>'.$shortcode->getDescription().'</td>'.
					'</tr>';
				}
				$html .=
			'</tbody>'.
		'</table>';
				
		return $html;
	}
	
	protected function renderShortcode()
	{
		$attributes = $this->shortcode->describeAttributes();
		
		ksort($attributes);
		
		$html =
		'<p>'.
			__('Shortcode:', 'EVEShipInfo').' <code>['.$this->shortcode->getTagName().']</code><br/>'.
		'</p>'.
		'<p>'.
			$this->shortcode->getDescription().
		'</p>'.
		'<h4>'.__('Available attributes', 'EVEShipInfo').'</h4>'.
		'<table class="wp-list-table widefat">'.
			'<thead>'.
				'<tr>'.
					'<th>'.__('Attribute', 'EVEShipInfo').'</th>'.
					'<th width="20%">'.__('Description', 'EVEShipInfo').'</th>'.
					'<th>'.__('Optional', 'EVEShipInfo').'</th>'.
					'<th>'.__('Type', 'EVEShipInfo').'</th>'.
					'<th>'.__('Default value', 'EVEShipInfo').'</th>'.
					'<th>'.__('Values', 'EVEShipInfo').'</th>'.
				'</tr>'.
			'</thead>'.
			'<tbody>';
				foreach($attributes as $name => $def) {
					$optional = 'No';
					if($def['optional']) {
						$optional = 'Yes';
					}
					
					$values = '';
					if(isset($def['values'])) {
						$values = array();
						foreach($def['values'] as $value => $label) {
							$values[] = '<code>'.$value.'</code> <i>'.$label.'</i>';
						}
						
						$values = implode('<br/>', $values);
					}
					
					$default = '';
					if(!empty($def['default'])) {
						$default = '<code>'.$def['default'].'</code>';
					}
					
				    $html .=
				    '<tr>'.
				    	'<td><code>'.$name.'</code></td>'.
				    	'<td>'.$def['descr'].'</td>'.
					    '<td>'.$optional.'</td>'.
					    '<td>'.$def['type'].'</td>'.
					    '<td>'.$default.'</td>'.
					    '<td>'.$values.'</td>'.
				    '</tr>';
				}
				$html .=
			'</tbody>'.
		'</table>'.
		'<br/>'.
		'<div id="poststuff">'.
			'<div class="stuffbox">'.
				'<h3>'.__('Examples', 'EVEShipInfo').'</h3>'.
				'<div class="inside">';
				
					$examples = $this->shortcode->getExamples();
					foreach($examples as $example) {
						$html .=
								'<code>'.$example['shortcode'].'</code>'.
								'<p class="description">'.$example['descr'].'</p><br/>';
					}
					$html .=
				'</div>'.
			'</div>'.
		'</div>';
						
		return $html;
	}
}