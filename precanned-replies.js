// (c) Combodo SARL 2011 - 2016

function GetWizardHelperJsonPrecannedReply() {
	var aReturn = {};
	if(typeof oWizardHelper != 'undefined') {
		oWizardHelper.UpdateWizard();
		aReturn = oWizardHelper.ToJSON();
	}
	return aReturn;
}
function SelectPrecannedReply(sObjectClass, iObjectKey, sLogAttCode)
{
	if ($('#precanned_button_'+sLogAttCode).prop('disabled')) return; // Disabled, do nothing
	if ($('#precanned_dlg').length == 0)
	{
		$('body').append('<div id="precanned_dlg"></div>');
	}
	$('#precanned_button_'+sLogAttCode).prop('disabled', true);
	$('#v_precanned_'+sLogAttCode).html('<img src="../images/indicator.gif" />');

	var $aJsonData = {'json' : GetWizardHelperJsonPrecannedReply()};
	if(Object.keys($aJsonData['json']).length === 0) {
		$aJsonData = {
			'object_class': $('[data-role="ibo-object-details"]').attr('data-object-class'),
			'object_id': $('[data-role="ibo-object-details"]').attr('data-object-id')
		}
	}
	
	var theMap = $.extend($aJsonData, {
		operation: 'select_precanned',
		obj_class: sObjectClass,
		obj_id: iObjectKey,
		log_attcode: sLogAttCode,
	 });
	
	// Run the query and get the result back directly in HTML
	$.post( AddAppContext(GetAbsoluteUrlModulesRoot()+'precanned-replies/ajax.php'), theMap, 
		function(data)
		{
			var dlg = $('#precanned_dlg');
			dlg.html(data);
			dlg.dialog({ 
				width: $(window).width()*0.8,
				height: $(window).height()*0.8,
				autoOpen: false,
				modal: true,
				title: Dict.S('UI:Dlg-PickAReply'),
				close: function() {OnClosePrecannedReply(sLogAttCode);},
				buttons: [
					{
						text: Dict.S('UI:Button:Cancel'),
						class: "ibo-is-alternative ibo-is-neutral",
						click: function() {
							$(this).dialog('close');
						}
					},
					{
						text:  Dict.S('UI:Button:Add'),
						class: "ibo-is-regular ibo-is-primary",
						id: "btn_ok_{{ oUIBlock.sLinkedSetId }}",
						click: function() {
							PrecannedDoSelect(sLogAttCode);
						}
					},
				],}
			);
			var data_area = $('#dr_precanned_select');
			//data_area.css('max-height', (0.5*$(document).height())+'px'); // Stay within the document's boundaries
			//data_area.css('overflow', 'auto'); // Stay within the document's boundaries
			dlg.dialog('open');
		},
		'html'
	);
}

function OnClosePrecannedReply(sLogAttCode)
{
	$('#precanned_button_'+sLogAttCode).prop('disabled', false);
	$('#v_precanned_'+sLogAttCode).html('');
}

function PrecannedDoSelect(sLogAttCode)
{
	var selected = $('#datatable_search_form_result_precanned_select input:checked');

	if (selected.length > 0)
	{
		var aSelected = new Array();
		var index = 0;
		selected.each( function () { aSelected[index++] = this.value; });

		var $aJsonData = {'json' : GetWizardHelperJsonPrecannedReply()};
		if(Object.keys($aJsonData['json']).length === 0) {
			$aJsonData = {
				'object_class': $('[data-role="ibo-object-details"]').attr('data-object-class'),
				'object_id': $('[data-role="ibo-object-details"]').attr('data-object-id')
			}
		}

		var theMap = $.extend($aJsonData, {
			operation: 'add_precanned',
			selected: aSelected,
			log_attcode: sLogAttCode
		 });
		
		// Run the query and get the result back directly in HTML
		$.post( AddAppContext(GetAbsoluteUrlModulesRoot()+'precanned-replies/ajax.php'), theMap, 
			function(aJson)
			{
				var sText = aJson[0].text;
				var iPrecannedId = aJson[0].id;
				var sInstanceCode =  $('[data-role="ibo-caselog-entry-form"][data-attribute-code="'+sLogAttCode+'"] textarea').attr('id');

				if (typeof CombodoCKEditorHandler !== 'undefined') {
					CombodoCKEditorHandler.InsertHtmlInsideInstance('#' +sInstanceCode, sText);
				}
				else {
					CKEDITOR.instances[sInstanceCode].insertHtml(sText);
				}

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
				$('#emry_enabled_'+sLogAttCode).prop('checked', true);
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

