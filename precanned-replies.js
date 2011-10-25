// (c) Combodo SARL 2011
var aNewAttachments = new Array();
var aPRAttachments = new Array();

function SelectPrecannedReply(sCaseLogId)
{
	if ($('#precanned_button').attr('disabled')) return; // Disabled, do nothing
	if ($('#precanned_dlg').length == 0)
	{
		$('body').append('<div id="precanned_dlg"></div>');
	}
	// Query the server to get the form to create a target object
	$('#precanned_button').attr('disabled', 'disabled');
	$('#v_precanned').html('<img src="../images/indicator.gif" />');

	oWizardHelper.UpdateWizard();
	var theMap = { 'json': oWizardHelper.ToJSON(),
			   operation: 'select_precanned'
			 };
	
	// Run the query and get the result back directly in HTML
	$.post( AddAppContext(GetAbsoluteUrlAppRoot()+'modules/combodo-precanned-replies/ajax.php'), theMap, 
		function(data)
		{
			var dlg = $('#precanned_dlg');
			dlg.html(data);
			dlg.dialog({ width: 'auto', height: 'auto', autoOpen: false, modal: true, title: 'Pick a Reply', close: OnClosePrecannedReply });
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
			PrecannedDoSearch();
		},
		'html'
	);
}

function OnClosePrecannedReply()
{
	$('#precanned_button').attr('disabled', '');
	$('#v_precanned').html('');
}

function PrecannedDoSelect()
{
//	$('#precanned_button').attr('disabled', '');
//	$('#v_precanned').html('');
	var selected = $('input.selectListprecanned-select_results:checked');
	if (selected.length > 0)
	{
		var aSelected = new Array();
		var index = 0;
		selected.each( function () { aSelected[index++] = this.value; });

		oWizardHelper.UpdateWizard();
		var theMap = { 'json': oWizardHelper.ToJSON(),
				   operation: 'add_precanned',
				   selected: aSelected
				 };
		
		// Run the query and get the result back directly in HTML
		$.post( AddAppContext(GetAbsoluteUrlAppRoot()+'modules/combodo-precanned-replies/ajax.php'), theMap, 
			function(aJson)
			{
				var sText = aJson[0].text;
				var iPrecannedId = aJson[0].id;
				var sPrevVal = $('#2_ticket_log').val();
				if (sPrevVal != '')
				{
					sPrevVal = '\n'+sPrevVal;
				}
				$('#2_ticket_log').val(sText+sPrevVal);
				var aFiles = aJson[0].files;
				var index = 0;
				while(index < aFiles.length)
				{
					sFileName = aFiles[index];
					PrecannedAddFile(iPrecannedId, index, sFileName);
					index++;
				}
				$('#precanned_sendmail').attr('checked', true);
			},
			'json'
		);
	
	
	}
	var dlg = $('#precanned_dlg');
	dlg.dialog('close');
}

function PrecannedDoSearch()
{
	var theMap = {
				operation: 'search_precanned'
			 };
	
	// Run the query and get the result back directly in HTML
	$.post( AddAppContext(GetAbsoluteUrlAppRoot()+'modules/combodo-precanned-replies/ajax.php'), theMap, 
		function(data)
		{
			var res = $('#dr_precanned-select');
			res.html(data);
		},
		'html'
	);
	return false; // Stay on page
}

function PrecannedAddFile(iPrecannedId, iFileId, sFileName)
{
	var sForm = '<input type="hidden" name="precanned_attachment[]" value="PrecannedReply::'+iPrecannedId+'/'+iFileId+'"/>';
	sForm += '<input type="hidden" name="precanned_attachment_name[]" value="'+sFileName+'"/>';
	$('#precanned_form').append(sForm);
	aPRAttachments.push( { attId: iPrecannedId, name: sFileName } );
	PrecannedUpdateFileCount();
}

function PrecannedOnAddAttachment(event, attId, sAttName)
{
	aNewAttachments.push({ attId: attId, name: sAttName});
	var sForm = '<input type="hidden" id=\"precanned_attachment_'+attId+'\" name="precanned_attachment[]" value="Attachment::'+attId+'"/>';
	sForm += '<input type="hidden" id=\"precanned_attachment_name_'+attId+'\" name="precanned_attachment_name[]" value="'+sAttName+'"/>';
	$('#precanned_form').append(sForm);
	PrecannedUpdateFileCount();
}

function PrecannedOnRemoveAttachment(event, attId)
{
	var index = 0;
	var bFound = false;
	while(!bFound && (index < aNewAttachments.length))
	{
		if (aNewAttachments[index].attId == ''+attId)
		{
			bFound = true;
		}
		else
		{
			index++;
		}
	}
	if (bFound)
	{
		aNewAttachments.splice(index, 1); //remove from the array
	}
	$('#precanned_attachment_'+id).remove();
	$('#precanned_attachment_name_'+id).remove();
	PrecannedUpdateFileCount();
}

function PrecannedUpdateFileCount()
{
	var iCount  = aNewAttachments.length + aPRAttachments.length;
	$('#precanned_files_count').html(iCount);
	var sHtml = '';
	var index = 0;
	while(index < aNewAttachments.length)
	{
		sHtml += aNewAttachments[index].name+"<br>\n";
		index++;
	}
	index = 0;
	while(index < aPRAttachments.length)
	{
		sHtml += aPRAttachments[index].name+"<br>\n";
		index++;
	}
	if (sHtml == '')
	{
		sHtml = 'No attachment'; //@@Localize
	}
	var api = $('#precanned_files_count').qtip("api");
	api.destroy();
	$('#precanned_files_count').qtip({ content: sHtml, show: 'mouseover', hide: 'unfocus', position: { corner: { target: 'topRight', tooltip: 'bottomLeft'}}, style: { name: 'dark', tip: 'bottomLeft' } });
}