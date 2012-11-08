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
 * Module precanned-replies
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
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("name"),
			"db_table" => "precanned_reply",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
		);
		MetaModel::Init_Params($aParams);

		MetaModel::Init_AddAttribute(new AttributeString("name", array("allowed_values"=>null, "sql"=>"name", "default_value"=>null, "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("description", array("allowed_values"=>null, "sql"=>"description", "default_value"=>null, "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("attachments", array("allowed_values"=>null, "sql"=>"attachments", "default_value"=>null, "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeText("body", array("allowed_values"=>null, "sql"=>"body", "default_value"=>null, "is_null_allowed"=>false, "depends_on"=>array())));
		
		MetaModel::Init_SetZListItems('details', array('name', 'description', 'body'));
		MetaModel::Init_SetZListItems('standard_search', array('name', 'description'));
		MetaModel::Init_SetZListItems('list', array('name', 'description', 'attachments'));
	}
	
	public function ComputeValues()
	{
		// Build the list of attachments (CSV string)
		//
		$oSearch = DBObjectSearch::FromOQL("SELECT Attachment WHERE item_class = :class AND item_id = :item_id");
		$oSet = new DBObjectSet($oSearch, array(), array('class' => get_class($this), 'item_id' => $this->GetKey()));
		$aAtt = array();
		while ($oAttachment = $oSet->Fetch())
		{
			$oDoc = $oAttachment->Get('contents');
			$aAtt[] = $oDoc->GetFileName();
		}
		if (count($aAtt) > 0)
		{			
			$this->Set('attachments', implode(', ', $aAtt));
		}
	}
}


$oToolsMenu = new MenuGroup('DataAdministration', 70 /* fRank */, 'PrecannedReply', UR_ACTION_MODIFY, UR_ALLOWED_YES|UR_ALLOWED_DEPENDS);
new OQLMenuNode('PrecannedReplies', 'SELECT PrecannedReply', $oToolsMenu->GetIndex(), 99 /* fRank */);

// Declare a class that implements iBackgroundProcess (will be called by the CRON)
// Extend the class AsyncTask to create a queue of asynchronous tasks (process by the CRON)
// Declare a class that implements iApplicationUIExtension (to tune object display and edition form)
// Declare a class that implements iApplicationObjectExtension (to tune object read/write rules)

class PrecannedRepliesPlugIn implements iApplicationUIExtension, iApplicationObjectExtension
{
	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{
		if ($bEditMode && self::IsTargetObject($oObject) && !$oObject->IsNew())
		{
			$sAttCode = MetaModel::GetModuleSetting('precanned-replies', 'target_caselog', 'public_log');
			$sModuleUrl = utils::GetAbsoluteUrlModulesRoot().'precanned-replies/';
			$oPage->add_linked_script($sModuleUrl.'precanned-replies.js');
			//@@ Localize
			$oPage->add_ready_script("$('#field_2_$sAttCode div.caselog_input_header').append('<div id=\"precanned_replies\" style=\"display:inline-block; margin-left:20px;\"><input type=\"button\" id=\"precanned_button\" value=\"Precanned Replies...\" onClick=\"SelectPrecannedReply(\'$sAttCode\')\"/><span id=\"v_precanned\"></span></div>');");
		}
	}

	public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
	{
	}

	public function OnFormSubmit($oObject, $sFormPrefix = '')
	{
		if (self::IsTargetObject($oObject))
		{
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
		$sAllowedClass = MetaModel::GetModuleSetting('precanned-replies', 'target_class', 'UserRequest');
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
