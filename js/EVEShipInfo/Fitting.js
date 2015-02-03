/**
 * Class handling a single ship fitting: manages creating 
 * the fitting box that gets displayed when a fit link is
 * clicked.
 * 
 * @module EVEShipInfo
 * @class EVEShipInfo_Fitting
 * @constructor
 * @author Sebastian Mordziol <eve@aeonoftime.com>
 * @link http://eve.aeonoftime.com
 */
var EVEShipInfo_Fitting = function(linkID, fittingID, name, shipName, shipID, highSlots, medSlots, lowSlots, rigs, drones) 
{
	this.linkID = linkID;
	this.jsID = linkID + '-fit';
	this.id = fittingID;
	this.name = name;
	this.ship = {
		'name':shipName,
		'id':shipID
	};
	this.highSlots = highSlots;
	this.medSlots = medSlots;
	this.lowSlots = lowSlots;
	this.rigs = rigs;
	this.drones = drones;
	this.shown = false;
	this.rendered = false;
	
	this.Show = function()
	{
		if(this.shown) {
			this.Hide();
			return;
		}
		
		var link = jQuery('#'+this.linkID);
		if(link.length == 0) {
			return;
		}
		
		if(!this.rendered) {
			var html = ''+
			'<div id="'+this.jsID+'" class="shipinfo-fittingbox">'+
				'<div class="shipinfo-fittingbox-wrap">'+
					'<div class="shipinfo-fittingbox-header">'+
						this.name+' '+
						'<span class="shipinfo-fittingbox-shipname">'+
							'<a href="javascript:void(0)" class="shipinfo-shiplink" onclick="EVEShipInfo.InfoPopup(\''+this.ship.id+'\')">'+
								this.ship.name+
							'</a>'+
						'</span>'+
						'<span class="shipinfo-fittingbox-closer" onclick="fobj'+this.linkID+'.Hide()">x</span>'+
					'</div>'+
					'<div class="shipinfo-fittingbox-content">'+
						this.ExportHTML()+
					'</div>'+
					'<div class="shipinfo-fittingbox-toolbar">'+
						'<a href="javascript:void(0)" onclick="jQuery(\'#'+this.jsID+'-praisalform\').submit()">EVEPraisal</a>'+
					'</div>'+
					'<div style="display:none">'+
						'<form action="http://evepraisal.com/estimate" method="post" target="_blank" id="'+this.jsID+'-praisalform">'+
							'<input type="hidden" name="raw_paste" value="'+this.ExportTextonly()+'"/>'+
							'<input type="hidden" name="hide_buttons" value="false"/>'+
							'<input type="hidden" name="paste_autosubmit" value="false"/>'+
							'<input type="hidden" name="market" value="30000142"/>'+
							'<input type="hidden" name="save" value="true"/>'+
						'</form>'+
					'</div>'+
				'</div>'+
			'</div>';
			
			link.after(html);
			this.rendered = true;
		} else {
			jQuery('#'+this.jsID).show();
		}
		
		this.shown = true;
	};
	
	this.Hide = function()
	{
		jQuery('#'+this.jsID).hide();
		this.shown = false;
	};
	
	this.ExportHTML = function()
	{
		var html = '';
		
		jQuery.each(this.highSlots, function(idx, slot) {
			html += slot+'<br/>';
		});
		jQuery.each(this.medSlots, function(idx, slot) {
			html += slot+'<br/>';
		});
		jQuery.each(this.lowSlots, function(idx, slot) {
			html += slot+'<br/>';
		});
		jQuery.each(this.rigs, function(idx, slot) {
			html += slot+'<br/>';
		});
		jQuery.each(this.drones, function(idx, slot) {
			html += slot+'<br/>';
		});
		
		return html;
	};
	
	this.ExportTextonly = function()
	{
		var text = '';
		
		jQuery.each(this.highSlots, function(idx, slot) {
			text += slot+'\n';
		});
		jQuery.each(this.medSlots, function(idx, slot) {
			text += slot+'\n';
		});
		jQuery.each(this.lowSlots, function(idx, slot) {
			text += slot+'\n';
		});
		jQuery.each(this.rigs, function(idx, slot) {
			text += slot+'\n';
		});
		jQuery.each(this.drones, function(idx, slot) {
			text += slot+'\n';
		});
		
		return text;
	};
	
	this.GetShipID = function()
	{
		return this.ship.id;
	};
	
	this.GetName = function()
	{
		return this.name;
	};
};