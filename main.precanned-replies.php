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
	const XML_LEGACY_VERSION = '';

	/**
	 * Compare static::XML_LEGACY_VERSION with ITOP_DESIGN_LATEST_VERSION and returns true if the later is <= to the former.
	 * If static::XML_LEGACY_VERSION, return false
	 *
	 * @return bool
	 *
	 * @since 1.2.0
	 */
	public static function UseLegacy(){
		return false;
	}

	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{

		if (self::IsTargetObject($oObject) && !$oObject->IsNew())
		{

            $oPage->LinkScriptFromModule('precanned-replies/precanned-replies.js');
            $sButtonLabel = Dict::S('UI:Button-AddReply');
            $oPage->add_dict_entry('UI:Dlg-PickAReply');
            $sButtonLTooltip = $sButtonLabel;
            $sButtonLabel = Dict::S('UI:Button-AddReply:Short');

            $sObjectClass = get_class($oObject);
            $iObjectId = $oObject->GetKey();
			// Iterate on the caselogs applicables to object class
            $aAttCodes = self::GetLogAttCodes($oObject);
            foreach($aAttCodes as $sAttCode) {
                $oPage->add_ready_script(
                    <<<JS
$('[data-role=\"ibo-caselog-entry-form\"][data-attribute-code=\"$sAttCode\"] [data-role=\"ibo-caselog-entry-form--action-buttons--extra-actions\"]').append('<div id=\"precanned_replies_$sAttCode\" style=\"display:inline-block;\"><button type=\"button\" class=\"emry-button ibo-button ibo-is-regular ibo-is-neutral\" id=\"precanned_button_$sAttCode\" value=\"$sButtonLabel\" onClick=\"SelectPrecannedReply(\'$sObjectClass\', \'$iObjectId\', \'$sAttCode\')\" data-tooltip-content=\"$sButtonLTooltip\"><span class=\"ibo-button--icon fas fa-file-invoice\"></span><span class=\"ibo-button--label\">$sButtonLabel</span></button><span id=\"v_precanned_$sAttCode\"></span></div>');
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
	
	protected function IsTargetObject(DBObject $oObject) : bool
	{
        return (count(self::GetLogAttCodes($oObject) ) > 0);
	}

    // Get an array of applicable caselog AttCodes for the class of the object
    // Merge the caselogs of the object class and its parent classes
    // Return an empty array if no caselog is applicable
    public static function GetLogAttCodes(DBObject $oObject) : array
    {

        $aParams = []; // merge the caselogs of the object class and its parent classes
        $aCaselogs = []; // return an array of applicable caselog AttCodes
        $aConfig = self::GetConfig();
        $sObjClass = get_class($oObject);
        foreach($aConfig as $sClass => $sClassParam) {
            if ($sObjClass === $sClass || array_key_exists($sObjClass, MetaModel::EnumChildClasses($sClass))) {
                $aClassParam = explode(',', $sClassParam);
                    // merge the arrays without duplicated values
                $aParams = array_unique(array_merge($aParams, $aClassParam));
            }
        }
        foreach($aParams as $i => $sAttCode) {
            if (MetaModel::IsValidAttCode($sObjClass, $sAttCode) && (MetaModel::GetAttributeDef($sObjClass, $sAttCode) instanceof AttributeCaseLog)) {
                $aCaselogs[] = $sAttCode;
            }
        }
        return $aCaselogs;
    }
    // return an array of $sClassName => $sCaseLogs // comma separated AttCodes
    public static function GetConfig() : array
    {   // Get multi-classes param table
        $aConfig = MetaModel::GetModuleSetting('precanned-replies', 'targets', array());

        // Merge legacy params in multi-classes format
        $sSingleClass = MetaModel::GetModuleSetting('precanned-replies', 'target_class', '');
        if (($sSingleClass !== '') && (!array_key_exists($sSingleClass, $aConfig))) {
            $aConfig[$sSingleClass]= array(MetaModel::GetModuleSetting('precanned-replies', 'target_caselog', ''));
        }
        // No config available, set the default in the multi-classes format
        if (empty($aConfig)) {
            $aConfig['UserRequest'] = ['public_log'];
        }
        return $aConfig;
    }
}

