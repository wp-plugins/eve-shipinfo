var FittingsList = 
{
	'allSelected':false,
	
	ToggleAll:function()
	{
		jQuery('.fits-toggler').prop('checked', false);
		
		if(this.allSelected) {
			jQuery('.fit-checkbox').prop('checked', false);
			this.allSelected = false;
			return;
		}
		
		jQuery('.fit-checkbox').prop('checked', true);
		this.allSelected = true;
	}
};