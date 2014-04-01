<?php
/**
 * bbdkp acp language file for mainmenu (German-Informal)
 * 
 * 
 * @copyright 2009 bbdkp <http://code.google.com/p/bbdkp/>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @translation unknown author, killerpommes
 * 
 */

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array

$lang = array_merge($lang, array(
	'ACP_DKP_RAIDS'	=> 'Raid Verwaltung',
	'ACP_DKP_RAID_ADD'	=> 'Raid hinzufügen',
	'ACP_DKP_RAID_EDIT'	=> 'Raid bearbeiten',
	'ACP_DKP_RAID_LIST'	=> 'Raid Liste',
));
