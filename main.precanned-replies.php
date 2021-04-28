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


// Declare a class that implements iBackgroundProcess (will be called by the cron)
// Extend the class AsyncTask to create a queue of asynchronous tasks (process by the cron)
// Declare a class that implements iApplicationUIExtension (to tune object display and edition form)
// Declare a class that implements iApplicationObjectExtension (to tune object read/write rules)

class PrecannedRepliesPlugIn implements iApplicationUIExtension, iApplicationObjectExtension
{
	const XML_LEGACY_VERSION = '1.7';

	/**
	 * Compare static::XML_LEGACY_VERSION with ITOP_DESIGN_LATEST_VERSION and returns true if the later is <= to the former.
	 * If static::XML_LEGACY_VERSION, return false
	 *
	 * @return bool
	 *
	 * @since 1.2.0
	 */
	public static function UseLegacy(){
		return static::XML_LEGACY_VERSION !== '' ? version_compare(ITOP_DESIGN_LATEST_VERSION, static::XML_LEGACY_VERSION, '<=') : false;
	}

	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{
		if ($bEditMode && self::IsTargetObject($oObject) && !$oObject->IsNew())
		{
			$sAttCode = MetaModel::GetModuleSetting('precanned-replies', 'target_caselog', 'public_log');
			$sModuleUrl = utils::GetAbsoluteUrlModulesRoot().'precanned-replies/';
			$bIsLegacy = static::UseLegacy();
			$sIsLegacy = $bIsLegacy === true ? 'true' : 'false';
			$oPage->add_ready_script("IsPrecannedRepliesLegacy = $sIsLegacy;");
			$oPage->add_linked_script($sModuleUrl.'precanned-replies.js');
			$sButtonLabel = Dict::S('UI:Button-AddReply');
			$oPage->add_dict_entry('UI:Dlg-PickAReply');
			if($bIsLegacy){
				$oPage->add_ready_script("$('#field_2_$sAttCode div.caselog_input_header').append('<div id=\"precanned_replies\" style=\"display:inline-block; margin-left:20px;\"><input type=\"button\" id=\"precanned_button\" value=\"$sButtonLabel\" onClick=\"SelectPrecannedReply(\'$sAttCode\')\"/><span id=\"v_precanned\"></span></div>');");
			}
			else{
				$sButtonLTooltip = $sButtonLabel;
				$sButtonLabel = Dict::S('UI:Button-AddReply:Short');
				$oPage->add_ready_script(
					<<<JS
$('[data-role=\"ibo-caselog-entry-form\"][data-attribute-code=\"$sAttCode\"] [data-role=\"ibo-caselog-entry-form--action-buttons--extra-actions\"]').append('<div id=\"precanned_replies\" style=\"display:inline-block;\"><button type=\"button\" class=\"emry-button ibo-button ibo-is-regular ibo-is-neutral\" id=\"precanned_button\" value=\"$sButtonLabel\" onClick=\"SelectPrecannedReply(\'$sAttCode\')\" data-tooltip-content=\"$sButtonLTooltip\"><span class=\"ibo-button--icon fas fa-file-invoice\"></span><span class=\"ibo-button--label\">$sButtonLabel</span></button><span id=\"v_precanned\"></span></div>');
CombodoTooltip.InitTooltipFromMarkup($('#precanned_button'), true);
JS
				);
			}
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
}

