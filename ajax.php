<?php
require_once('../../approot.inc.php');
require_once(APPROOT.'/application/application.inc.php');
//remove require itopdesignformat at the same time as version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') < 0
if (! defined("ITOP_DESIGN_LATEST_VERSION")) {
	require_once APPROOT.'setup/itopdesignformat.class.inc.php';
}
if (version_compare(ITOP_DESIGN_LATEST_VERSION, '3.0') < 0) {
	require_once(APPROOT.'/application/ajaxwebpage.class.inc.php');
}
require_once(APPROOT.'/application/wizardhelper.class.inc.php');
require_once(APPROOT.'/application/ui.linkswidget.class.inc.php');
require_once(APPROOT.'/application/ui.extkeywidget.class.inc.php');

try
{
	require_once(APPROOT.'/application/startup.inc.php');
	require_once(APPROOT.'/application/user.preferences.class.inc.php');
	
	require_once(APPROOT.'/application/loginwebpage.class.inc.php');
	LoginWebPage::DoLogin(false /* bMustBeAdmin */, false /* IsAllowedToPortalUsers */); // Check user rights and prompt if needed

	if (version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') < 0) {
		$oPage = new ajax_page('');
		$oPage->no_cache();
	} else {
		$oPage = new AjaxPage('');
	}

	$sOperation = utils::ReadParam('operation', '');
	$sLogAttCode = utils::ReadParam('log_attcode', '');

	switch($sOperation)
	{
		case 'select_precanned':
            $oFilter = new DBObjectSearch('PrecannedReply');
            $sObjClass = utils::ReadParam('obj_class', '', false, 'class');
            $sObjId = utils::ReadParam('obj_id', 0, false, 'integer');
            $sAttCode = utils::ReadParam('log_attcode', '', false, 'string');
            if($sObjClass != '' && $sObjId > 0) {
                $oObject = MetaModel::GetObject($sObjClass, $sObjId);
                $sLogAttCode = (MetaModel::IsValidAttCode($sObjClass, $sAttCode) && (MetaModel::GetAttributeDef($sObjClass, $sAttCode) instanceof AttributeCaseLog)) ? $sAttCode : '';
                PrecannedReply::FilterApplicableReplies($oObject, $oFilter, $sLogAttCode);

            }
            else {
                    // Non specific filter
            }
            $oSet = new CMDBObjectSet($oFilter);
            $oBlock = new DisplayBlock($oFilter, 'search', false);
            if(PrecannedRepliesPlugIn::UseLegacy()){
                $sHTML = '<div class="wizContainer" style="vertical-align:top;"><div>';
                $sHTML .= $oBlock->GetDisplay($oPage, 'precanned_select', array('open' => true, 'currentId' => 'precanned_select', 'table_inner_id' => 'datatable_search_form_result_precanned_select'));
                $sHTML .= "<form id=\"fr_precanned_select\" OnSubmit=\"return PrecannedDoSelect('$sLogAttCode');\">\n";
                $sHTML .= "<div id=\"dr_precanned_select\" class=\"sf_results_area\" style=\"vertical-align:top;background: #fff;height:100%;overflow:auto;padding:0;border:0;\">\n";
                $sHTML .= "<div style=\"background: #fff; border:0; text-align:center; vertical-align:middle;\"><p>".Dict::S('UI:Message:EmptyList:UseSearchForm')."</p></div>\n";
                $sHTML .= "</div>\n";
            }
            else{
                $oPage->add('<div class="wizContainer" style="vertical-align:top;"><div>');
                $oBlock->Display($oPage, 'precanned_select', array('open' => true, 'currentId' => 'precanned_select', 'table_inner_id' => 'datatable_search_form_result_precanned_select',				'menu' => false,
                 'selection_mode' => true,
                 'selection_type' => 'single',));
                $sHTML = "<form id=\"fr_precanned_select\" OnSubmit=\"return PrecannedDoSelect('$sLogAttCode');\">\n";
            }
            $sHTML .= "<input type=\"hidden\" id=\"count_precanned_select\" value=\"0\">";
            $sHTML .= "</form>\n";
            $sHTML .= '</div></div>';

            $oPage->add($sHTML);
            $oPage->add_ready_script("$('#fs_precanned_select').bind('submit', function() {PrecannedDoSearch('$sLogAttCode'); return false;} );\n");
		break;
		
		case 'search_precanned':	
		$oFilter = new DBObjectSearch('PrecannedReply');
		$oBlock = new DisplayBlock($oFilter, 'list', false);
		$oBlock->Display($oPage, 'search_form_result_precanned_select', array('cssCount'=> '#count_precanned_select', 'menu' => false, 'selection_mode' => true, 'selection_type' => 'single')); // Don't display the 'Actions' menu on the results
		break;
		
		case 'add_precanned':
		$aSelected = utils::ReadParam('selected', '');

		$sJson = utils::ReadParam('json', '', false, 'raw_data');
		$sObjClass = utils::ReadParam('object_class', '', false, 'class');
		$sObjId = utils::ReadParam('object_id', 0, false, 'integer');
		if (!empty($sJson)) {
			$oWizardHelper = WizardHelper::FromJSON($sJson);
			$oObj = $oWizardHelper->GetTargetObject();
			$aContext = $oObj->ToArgs('this');
		}
		else if(!PrecannedRepliesPlugIn::UseLegacy() && $sObjClass != '' && $sObjId > 0) {
			$oObj = MetaModel::GetObject($sObjClass, $sObjId);
			$aContext = $oObj->ToArgs('this');
		}
		else {
			// Bug!!!!
			$aContext = array();
		}

		$aResult = array();
		foreach($aSelected as $iId)
		{
			$oPR = MetaModel::GetObject('PrecannedReply', $iId, false);
			if ($oPR != null)
			{
				// Apply context ($this->...$)
				$sText = MetaModel::ApplyParams($oPR->Get('body'), $aContext);

				// Search for attachments
				$oSearch = DBObjectSearch::FromOQL("SELECT Attachment WHERE item_class = :class AND item_id = :item_id");
				$oSet = new DBObjectSet($oSearch, array(), array('class' => get_class($oPR), 'item_id' => $oPR->GetKey()));
				$aAtt = array();
				while ($oAttachment = $oSet->Fetch())
				{
					$oDoc = $oAttachment->Get('contents');
					$aAtt[] = array(
						'container_class' => get_class($oAttachment),
						'container_id' => $oAttachment->GetKey(),
						'blob_attcode' => 'contents',
						'file_name' => $oDoc->GetFileName()
					);
				}

				$aResult[] = array(
					'id' => $iId,
					'text' => $sText,
					'files' => $aAtt
				);
			}
		}
		$oPage->SetContentType('application/json');
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
