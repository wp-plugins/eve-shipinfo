/**
 * Clientside script handling the information on ships in a page,
 * and used as a hub to display the information dialogs.
 * 
 * @module EVEShipInfo
 * @class EVEShipInfo
 * @static
 * @main EVEShipInfo
 * @author Sebastian Mordziol <eve@aeonoftime.com> 
 */
var EVEShipInfo =
{
	'ships':{},
	
	AddShip:function(shipData)
	{
		this.ships[shipData.id] = new EVEShipInfo_Ship(this, shipData);
	},
	
	InfoPopup:function(shipID)
	{
		if(typeof(this.ships[shipID])=='undefined') {
			return;
		}
		
		this.ships[shipID].InfoPopup();
	}
};