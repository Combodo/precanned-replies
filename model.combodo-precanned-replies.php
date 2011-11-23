<?php
// Copyright (C) 2010 Combodo SARL
//
//   This program is free software; you can redistribute it and/or modify
//   it under the terms of the GNU General Public License as published by
//   the Free Software Foundation; version 3 of the License.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of the GNU General Public License
//   along with this program; if not, write to the Free Software
//   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


/**
 * Module combodo-precanned-replies
 *
 * @author      Erwan Taloc <erwan.taloc@combodo.com>
 * @author      Romain Quetiez <romain.quetiez@combodo.com>
 * @author      Denis Flaven <denis.flaven@combodo.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

/**
 * Pre-defined replies for fast answer to helpdesk tickets
 *
 * @author      Erwan Taloc <erwan.taloc@combodo.com>
 * @author      Romain Quetiez <romain.quetiez@combodo.com>
 * @author      Denis Flaven <denis.flaven@combodo.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

class PrecannedReply extends cmdbAbstractObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel",
			"key_type" => "autoincrement",
			"name_attcode" => "description",
			"state_attcode" => "",
			"reconc_keys" => array("description", "category"),
			"db_table" => "precanned_reply",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
		);
		MetaModel::Init_Params($aParams);

		MetaModel::Init_AddAttribute(new AttributeString("category", array("allowed_values"=>null, "sql"=>"category", "default_value"=>null, "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("keywords", array("allowed_values"=>null, "sql"=>"keywords", "default_value"=>null, "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("description", array("allowed_values"=>null, "sql"=>"description", "default_value"=>null, "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("attachments", array("allowed_values"=>null, "sql"=>"attachments", "default_value"=>null, "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeText("body", array("allowed_values"=>null, "sql"=>"body", "default_value"=>null, "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeBlob("file1", array("is_null_allowed"=>true, "depends_on"=>array())));
		
		MetaModel::Init_SetZListItems('details', array('category', 'description', 'keywords','body', 'file1'));
		MetaModel::Init_SetZListItems('standard_search', array('category', 'description', 'keywords'));
		MetaModel::Init_SetZListItems('list', array('category', 'description', 'attachments'));
	}
	
	public function ComputeValues()
	{
		$oDoc = $this->Get('file1');
		if (is_object($oDoc) && !$oDoc->IsEmpty())
		{
			$this->Set('attachments', $oDoc->GetFileName());
		}
		else
		{
			$this->Set('attachments', '');
		}
	}
}

/**
 * Link between an answer and the tickets it was used in
 *
 * @author      Erwan Taloc <erwan.taloc@combodo.com>
 * @author      Romain Quetiez <romain.quetiez@combodo.com>
 * @author      Denis Flaven <denis.flaven@combodo.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

class lnkPRToObject extends DBObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel",
			"key_type" => "autoincrement",
			"name_attcode" => "description",
			"state_attcode" => "",
			"reconc_keys" => array("description", "category"),
			"db_table" => "lnkprtoobject",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
		);
		MetaModel::Init_Params($aParams);

		MetaModel::Init_AddAttribute(new AttributeString("category", array("allowed_values"=>null, "sql"=>"category", "default_value"=>null, "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("keywords", array("allowed_values"=>null, "sql"=>"keywords", "default_value"=>null, "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("description", array("allowed_values"=>null, "sql"=>"description", "default_value"=>null, "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeText("body", array("allowed_values"=>null, "sql"=>"body", "default_value"=>null, "is_null_allowed"=>false, "depends_on"=>array())));
		
		MetaModel::Init_AddAttribute(new AttributeExternalKey("pr_id", array("targetclass"=>"PrecannedReply", "jointype"=>null, "allowed_values"=>null, "sql"=>"pr_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("obj_class", array("allowed_values"=>null, "sql"=>"obj_class", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeInteger("obj_key", array("allowed_values"=>null, "sql"=>"obj_key", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		
	}
}


$oToolsMenu = new MenuGroup('DataAdministration', 70 /* fRank */, 'PrecannedReply', UR_ACTION_MODIFY, UR_ALLOWED_YES|UR_ALLOWED_DEPENDS);
new OQLMenuNode('PrecannedReplies', 'SELECT PrecannedReply', $oToolsMenu->GetIndex(), 99 /* fRank */);

// Declare a class that implements iBackgroundProcess (will be called by the CRON)
// Extend the class AsyncTask to create a queue of asynchronous tasks (process by the CRON)
// Declare a class that implements iApplicationUIExtension (to tune object display and edition form)
// Declare a class that implements iApplicationObjectExtension (to tune object read/write rules)

class CombodoPrecannedRepliesPlugIn implements iApplicationUIExtension, iApplicationObjectExtension
{
	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{
		if ($bEditMode && self::IsTargetObject($oObject) && !$oObject->IsNew())
		{
			$sAttCode = MetaModel::GetModuleSetting('combodo-precanned-replies', 'target_caselog', 'ticket_log');
			//@@ Localize
			$oPage->add_linked_script("../modules/combodo-precanned-replies/precanned-replies.js");
			$oPage->add_ready_script("$('#field_2_$sAttCode div.caselog_input_header').append('<input id=\"precanned_sendmail\" type=\"checkbox\" name=\"precanned_sendmail\" value=\"yes\">&nbsp;<img src=\"../images/mail.png\">&nbsp;<img src=\"../modules/combodo-precanned-replies/paper_clip.png\">&nbsp;(<span id=\"precanned_files_count\">0</span>)<div id=\"precanned_replies\" style=\"display:inline-block; margin-left:20px;\"><input type=\"button\" id=\"precanned_button\" value=\"Precanned Replies...\" onClick=\"SelectPrecannedReply()\"/><span id=\"v_precanned\"></span></div>');");
			$oPage->add_ready_script("$('#form_2').append('<div id=\"precanned_form\"></div>');");
			$oPage->add_ready_script("$('#attachment_plugin').bind('add_attachment', PrecannedOnAddAttachment );");
			$oPage->add_ready_script("$('#attachment_plugin').bind('remove_attachment',  PrecannedOnRemoveAttachment );");
			$oPage->add_ready_script("$('#precanned_files_count').qtip({ content: 'No attachment', show: 'mouseover', hide: 'unfocus', position: { corner: { target: 'topRight', tooltip: 'bottomLeft'}}, style: { name: 'dark', tip: 'bottomLeft' } });");
			//$oPage->add_at_the_end("<div id=\"precanned_dlg\"></div>");
		}
	}

	public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
	{
	}

	public function OnFormSubmit($oObject, $sFormPrefix = '')
	{
		if (self::IsTargetObject($oObject))
		{
			$sAttCode = MetaModel::GetModuleSetting('combodo-precanned-replies', 'target_caselog', 'ticket_log');
			$sOperation = utils::ReadPostedParam('precanned_sendmail', null);
			if ($sOperation == 'yes')
			{
				$sMessageId = EmailReplica::MakeMessageId($oObject);
				$oReplica = self::GetLatestReplica($oObject);
				if ($oReplica == null)
				{
					//echo "<p>No previous EmailReplica found</p>";
					$sPreviousMessageText =  $oObject->Get('description');
					$sPreviousMessageDate = $oObject->Get('start_date');
					$sRawMessageId = '';
				}
				else
				{
					//echo "<p>EmailReplica found:".$oReplica->GetKey()."</p>";
					$sPreviousMessageText = $oReplica->Get('message_text');
					$sPreviousMessageDate = $oReplica->Get('message_date');
					$sRawMessageId = $oReplica->Get('message_id');
				}
				$oCaseLog = $oObject->Get($sAttCode);
				$sLatestEntry = $oCaseLog->GetLatestEntry();
				$sEmailText = nl2br(htmlentities($sLatestEntry, ENT_QUOTES, 'UTF-8'));
				$sAddressee = $oObject->Get('caller_email');
				$sAgentEmail = $oObject->Get('agent_email');
				$sSubject = 'Re: '.$oObject->Get('title');

				$sBody = $sEmailText."\n<blockquote cite=\"mid:$sRawMessageId\" type=\"cite\"><pre>".htmlentities($sPreviousMessageText, ENT_QUOTES, 'UTF-8')."</pre></blockquote>";

				$oEmail = new EMail();

				
				$aPrecannedAttachments = utils::ReadParam('precanned_attachment', array(), false, 'raw_data');
				$aAttachmentsReporting = array();
				foreach($aPrecannedAttachments as $sAttach)
				{
					// Process the attachments to the Precanned Reply
					$aMatches = array();
					if (preg_match('|PrecannedReply::([0-9]+)/([0-9]+)|', $sAttach, $aMatches))
					{
						$iPrecannedId = $aMatches[1];
						$iAttachmentId = $aMatches[2];
						$oPrecanned = MetaModel::GetObject('PrecannedReply', $iPrecannedId, false);
						if ($oPrecanned != null)
						{
							$oFile1 = $oPrecanned->Get('file1');
							if (!$oFile1->IsEmpty())
							{
								$aAttachmentsReporting[] = $oFile1->GetFileName();
								$oEmail->AddAttachment($oFile1->GetData(), $oFile1->GetFileName(), $oFile1->GetMimeType());
							}
							
						}
					}
					else if (preg_match('|Attachment::([0-9]+)|', $sAttach, $aMatches))
					{
						// Process the newly added Attachments to the ticket
						$iAttachmentId = $aMatches[1];
						$oAttachment = MetaModel::GetObject('Attachment', $iAttachmentId, false);
						if ($oAttachment != null)
						{
							$oFile = $oAttachment->Get('contents');
							if (!$oFile->IsEmpty())
							{
								$aAttachmentsReporting[] = $oFile->GetFileName();
								$oEmail->AddAttachment($oFile->GetData(), $oFile->GetFileName(), $oFile->GetMimeType());
							}
							
						}
					}
				}
				
				$oEmail->SetSubject($sSubject);
				$oEmail->SetBody($sBody);
				$oEmail->SetRecipientTO($sAddressee);
				$oEmail->SetRecipientBCC($sAgentEmail);
				$sFromAddrTemplate = MetaModel::GetModuleSetting('combodo-precanned-replies', 'template_from', '$this->workgroup_id->email$');
				$oFromAddrTemplate = new TemplateString($sFromAddrTemplate);
				$sFromAddr = $oFromAddrTemplate->Render(array('this' => $oObject));
				$sFromLabelTemplate = MetaModel::GetModuleSetting('combodo-precanned-replies', 'template_from_label', '$this->agent_id->first_name$ $this->agent_id->name$');
				$oFromLabelTemplate = new TemplateString($sFromLabelTemplate);
				$sFromLabel = trim($oFromLabelTemplate->Render(array('this' => $oObject)));
				//echo "<p>From: template: $sFromAddr &lt;$sFromLabel&gt;</p>";
				$oEmail->SetRecipientFrom($sFromAddr, $sFromLabel);
				if ($sRawMessageId != '')
				{
					$oEmail->SetReferences(EmailReplica::MakeReferencesHeader($sRawMessageId, $oObject));
				}
				$oEmail->SetMessageId($sMessageId);
				$oLog = null;
				$iRes = $oEmail->Send($aErrors, false, $oLog); // allow asynchronous mode
				switch ($iRes)
				{
					case EMAIL_SEND_OK:
					case EMAIL_SEND_PENDING:
						$sLatestEntry .= "\n\nSent by email to: $sAddressee"; //@@ Localize
						if (count($aAttachmentsReporting) > 0)
						{
							$sLatestEntry .= "\nAttachment(s): ".implode(', ', $aAttachmentsReporting);
						}
						$oObject->Set($sAttCode, $sLatestEntry);
					break;
	
					case EMAIL_SEND_ERROR:
						$sLatestEntry .= "\n\nFAILED to send the email to: $sAddressee\n"; //@@ Localize
						$sLatestEntry .= "Errors: ".implode(', ', $aErrors);
						$oObject->Set($sAttCode, $sLatestEntry);
				}				
			}
		}
	}
	
	public function OnFormCancel($sTempId)
	{
	}

	public function EnumUsedAttributes($oObject)
	{
		return array();
	}

	public function GetIcon($oObject)
	{
		return '';
	}

	public function GetHilightClass($oObject)
	{
		// Possible return values are:
		// HILIGHT_CLASS_CRITICAL, HILIGHT_CLASS_WARNING, HILIGHT_CLASS_OK, HILIGHT_CLASS_NONE	
		return HILIGHT_CLASS_NONE;
	}

	public function EnumAllowedActions(DBObjectSet $oSet)
	{
		// No action
		return array();
    }

	public function OnIsModified($oObject)
	{
		return false;
	}

	public function OnCheckToWrite($oObject)
	{
		return array();
	}

	public function OnCheckToDelete($oObject)
	{
		return array();
	}

	public function OnDBUpdate($oObject, $oChange = null)
	{
	}
	
	public function OnDBInsert($oObject, $oChange = null)
	{
	}
	
	public function OnDBDelete($oObject, $oChange = null)
	{	
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////
	//
	// Plug-ins specific functions
	//
	///////////////////////////////////////////////////////////////////////////////////////////////////////
	
	protected function IsTargetObject($oObject)
	{
		$sAllowedClass = MetaModel::GetModuleSetting('combodo-precanned-replies', 'target_class', 'UserRequest');
		return ($oObject instanceof $sAllowedClass);
	}
	
	protected static function GetLatestReplica($oTicket)
	{
		$sOql = "SELECT EmailReplica WHERE ticket_id = :ticket_id";
		$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOql), array('message_date' => false), array('ticket_id' => $oTicket->GetKey()));
		return $oSet->Fetch();
	}
}

?>
