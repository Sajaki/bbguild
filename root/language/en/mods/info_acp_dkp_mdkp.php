<?php
/**
 * bbdkp acp language file for mainmenu
 * 
 * 
 * @copyright 2009 bbdkp <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
  	'ACP_DKP_MDKP'	        	=> 'Member Points Management', 
    'ACP_DKP_EDITMEMBERDKP'		=> 'Edit member Points',
	'ACP_DKP_LISTMEMBERDKP'		=> 'Member Points',  
	'ACP_DKP_MEMBER_TRF'		=> 'Points Transfer',
));

?>