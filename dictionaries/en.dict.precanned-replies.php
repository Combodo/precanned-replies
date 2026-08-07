<?php

/**
 * Localized data
 *
 * @copyright Copyright (C) 2010-2018 Combodo SARL
 * @license	http://opensource.org/licenses/AGPL-3.0
 *
 * This file is part of iTop.
 *
 * iTop is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * iTop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with iTop. If not, see <http://www.gnu.org/licenses/>
 */

Dict::Add('EN US', 'English', 'English', [
	'Menu:PrecannedReplies' => 'Precanned replies',
	'Menu:PrecannedReplies+' => 'Precanned replies',

	'UI:Button-AddReply' => 'Precanned Replies...',
	'UI:Button-AddReply:Short' => 'Templates',
	'UI:Dlg-PickAReply' => 'Pick a Reply',

	'Class:PrecannedReply' => 'Precanned reply',
	'Class:PrecannedReply+' => 'A catalog of easily reusable canned responses to save time during interactions within ticket logs.',
	'Class:PrecannedReply/Attribute:name' => 'Name',
	'Class:PrecannedReply/Attribute:name+' => '',
	'Class:PrecannedReply/Attribute:description' => 'Description',
	'Class:PrecannedReply/Attribute:description+' => '',
	'Class:PrecannedReply/Attribute:body' => 'Body',
	'Class:PrecannedReply/Attribute:body+' => 'You can use the syntax $this->attribute_code$, which will be replaced by the ticket attribute\'s value. For example, $this->team_id_friendlyname$ will be replaced by the team name.',
]);
