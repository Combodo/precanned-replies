// (c) Combodo SARL 2011

function SelectPrecannedReply(sLogAttCode)
{
	if ($('#precanned_button').attr('disabled')) return; // Disabled, do nothing
	if ($('#precanned_dlg').length == 0)
	{
		$('body').append('<div id="precanned_dlg"></div>');
	}
	$('#precanned_button').attr('disabled', 'disabled');
	$('#v_precanned').html('<img src="../images/indicator.gif" />');

	oWizardHelper.UpdateWizard();
	var theMap = { 'json': oWizardHelper.ToJSON(),
			   operation: 'select_precanned',
			   log_attcode: sLogAttCode
			 };
	
	// Run the query and get the result back directly in HTML
	$.post( AddAppContext(GetAbsoluteUrlModulesRoot()+'precanned-replies/ajax.php'), theMap, 
		function(data)
		{
			var dlg = $('#precanned_dlg');
			dlg.html(data);
			dlg.dialog({ width: 'auto', height: 'auto', autoOpen: false, modal: true, title: 'Pick a Reply', close: function() {OnClosePrecannedReply(sLogAttCode);} });
			dlg.dialog('open');
			// Adjust the dialog's size to fit into the screen
			if (dlg.width() > ($(window).width()-40))
			{
				dlg.width($(window).width()-40);
			}
			if (dlg.height() > ($(window).height()-70))
			{
				dlg.height($(window).height()-70);
			}
			PrecannedDoSearch(sLogAttCode);
		},
		'html'
	);
}

function OnClosePrecannedReply(sLogAttCode)
{
	$('#precanned_button').removeAttr('disabled');
	$('#v_precanned').html('');
}

function PrecannedDoSelect(sLogAttCode)
{
	var selected = $('input.selectListprecanned_select_results:checked');
	if (selected.length > 0)
	{
		var aSelected = new Array();
		var index = 0;
		selected.each( function () { aSelected[index++] = this.value; });

		oWizardHelper.UpdateWizard();
		var theMap = { 'json': oWizardHelper.ToJSON(),
			operation: 'add_precanned',
			selected: aSelected,
			log_attcode: sLogAttCode
		 };
		
		// Run the query and get the result back directly in HTML
		$.post( AddAppContext(GetAbsoluteUrlModulesRoot()+'precanned-replies/ajax.php'), theMap, 
			function(aJson)
			{
				var sText = aJson[0].text;
				var iPrecannedId = aJson[0].id;
				var sPrevVal = $('#2_'+sLogAttCode).val();
				if (sPrevVal != '')
				{
					sPrevVal = '\n'+sPrevVal;
				}
				$('#2_'+sLogAttCode).val(sText+sPrevVal);
				var aFiles = aJson[0].files;
				var index = 0;
				while(index < aFiles.length)
				{
					$('#emry_event_bus_'+sLogAttCode).trigger('add_blob', [
						aFiles[index]['container_class'],
						aFiles[index]['container_id'],
						aFiles[index]['blob_attcode'],
						aFiles[index]['file_name']
					])
					index++;
				}
				$('#emry_enabled_'+sLogAttCode).attr('checked', true);
			},
			'json'
		);
	
	
	}
	var dlg = $('#precanned_dlg');
	dlg.dialog('close');
	dlg.html('');
}

function PrecannedDoSearch(sLogAttCode)
{
	var theMap = {};

	// Gather the parameters from the search form
	$('#fs_precanned_select :input').each( function() {
		if (this.name != '')
		{
			var val = $(this).val(); // supports multiselect as well
			if (val !== null)
			{
				theMap[this.name] = val;					
			}
		}
	});
	theMap['operation'] = 'search_precanned';
	theMap['log_attcode'] = sLogAttCode;
	
	// Run the query and get the result back directly in HTML
	$.post( AddAppContext(GetAbsoluteUrlModulesRoot()+'precanned-replies/ajax.php'), theMap, 
		function(data)
		{
			var res = $('#dr_precanned_select');
			res.html(data);
		},
		'html'
	);
	return false; // Stay on page
}
