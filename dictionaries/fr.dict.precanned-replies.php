<?php

/**
 * Localized data
 *
 * @copyright Copyright (C) 2010-2024 Combodo SAS
 * @license    https://opensource.org/licenses/AGPL-3.0
 *
 */
/**
 * @author Erwan Taloc <erwan.taloc@combodo.com>
 * @author Romain Quetiez <romain.quetiez@combodo.com>
 * @author Denis Flaven <denis.flaven@combodo.com>
 *
 */
Dict::Add('FR FR', 'French', 'Français', [
	'Class:PrecannedReply' => 'Réponse prédéfinie',
	'Class:PrecannedReply+' => 'Catalogue de Réponses prédéfinies réutilisables facilement pour gagner du temps lors des interractions dans les journaux des tickets',
	'Class:PrecannedReply/Attribute:body' => 'Texte',
	'Class:PrecannedReply/Attribute:body+' => 'Vous pouvez utiliser la syntaxe $this->attribute_code$ qui sera remplacé par la valeur de l\'attribut du ticket. Ainsi $this->team_id_friendlyname$ sera remplacé par le nom de l\'équipe.',
	'Class:PrecannedReply/Attribute:description' => 'Description',
	'Class:PrecannedReply/Attribute:description+' => 'Information à l\'usage des agents, afin de sélectionner la réponse prédéfinie la plus adaptée.',
	'Class:PrecannedReply/Attribute:name' => 'Nom',
	'Class:PrecannedReply/Attribute:name+' => '',
	'Menu:PrecannedReplies' => 'Réponses prédéfinies',
	'Menu:PrecannedReplies+' => 'Réponses prédéfinies',
	'UI:Button-AddReply' => 'Réponse prédéfinie...',
	'UI:Button-AddReply:Short' => 'Modèles',
	'UI:Dlg-PickAReply' => 'Choisissez une Réponse',
]);
