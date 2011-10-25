<?php
require_once('../../approot.inc.php');
require_once(APPROOT.'/application/application.inc.php');
require_once(APPROOT.'/application/webpage.class.inc.php');
require_once(APPROOT.'/application/ajaxwebpage.class.inc.php');
require_once(APPROOT.'/application/wizardhelper.class.inc.php');
require_once(APPROOT.'/application/ui.linkswidget.class.inc.php');
require_once(APPROOT.'/application/ui.extkeywidget.class.inc.php');

try
{
	require_once(APPROOT.'/application/startup.inc.php');
	require_once(APPROOT.'/application/user.preferences.class.inc.php');
	
	require_once(APPROOT.'/application/loginwebpage.class.inc.php');
	LoginWebPage::DoLogin(false /* bMustBeAdmin */, true /* IsAllowedToPortalUsers */); // Check user rights and prompt if needed
	
	$oPage = new ajax_page("");
	$oPage->no_cache();
	
	$sOperation = utils::ReadParam('operation', '');

	switch($sOperation)
	{
		case 'select_precanned':
		$sHTML = '<div class="wizContainer" style="vertical-align:top;"><div>';

		$oFilter = new DBObjectSearch('PrecannedReply');
		$oSet = new CMDBObjectSet($oFilter);
		$oBlock = new DisplayBlock($oFilter, 'search', false);
		$sHTML .= $oBlock->GetDisplay($oPage, 'precanned-select', array('open' => true, 'currentId' => 'precanned-select'));
		$sHTML .= "<form id=\"fr_precanned-select\" OnSubmit=\"return PrecannedDoSelect();\">\n";
		$sHTML .= "<div id=\"dr_precanned-select\" style=\"vertical-align:top;background: #fff;height:100%;overflow:auto;padding:0;border:0;\">\n";
		$sHTML .= "<div style=\"background: #fff; border:0; text-align:center; vertical-align:middle;\"><p>".Dict::S('UI:Message:EmptyList:UseSearchForm')."</p></div>\n";
		$sHTML .= "</div>\n";
		$sHTML .= "<input type=\"button\" id=\"btn_cancel_precanned-select\" value=\"".Dict::S('UI:Button:Cancel')."\" onClick=\"$('#precanned_dlg').dialog('close');\">&nbsp;&nbsp;";
		$sHTML .= "<input type=\"button\" id=\"btn_ok_precanned-select\" value=\"".Dict::S('UI:Button:Ok')."\" onClick=\"PrecannedDoSelect();\">";
		$sHTML .= "<input type=\"hidden\" id=\"count_precanned-select\" value=\"0\">";
		$sHTML .= "</form>\n";
		$sHTML .= '</div></div>';
		
		$oPage->add($sHTML);
		$oPage->add_ready_script("$('#fs_precanned-select').bind('submit', PrecannedDoSearch);\n");
		break;
		
		case 'search_precanned':	
		$oFilter = new DBObjectSearch('PrecannedReply');
		$oBlock = new DisplayBlock($oFilter, 'list', false);
		$oBlock->Display($oPage, 'precanned-select_results', array('cssCount'=> '#count_precanned-select', 'menu' => false, 'selection_mode' => true, 'selection_type' => 'single')); // Don't display the 'Actions' menu on the results
		break;
		
		case 'add_precanned':
		$aSelected = utils::ReadParam('selected', '');
		$aResult = array();
		foreach($aSelected as $iId)
		{
			$oPR = MetaModel::GetObject('PrecannedReply', $iId, false);
			if ($oPR != null)
			{
				$aData = array('id' => $iId, 'text' => $oPR->Get('body'));
				$oFile = $oPR->Get('file1');
				if (!$oFile->IsEmpty())
				{
					// For now just one file 'file1'
					$aData['files'] = array($oFile->GetFileName());
				}
				else
				{
					$aData['files'] = array();
				}
				$aResult[] = $aData;
			}
		}
		$oPage->add(json_encode($aResult));
		break;
		
		default:
		$oPage->add("Operation $sOperation no supported.");
	}

	$oPage->output();
}
catch (Exception $e)
{
	echo $e->GetMessage();
	IssueLog::Error($e->getMessage());
}