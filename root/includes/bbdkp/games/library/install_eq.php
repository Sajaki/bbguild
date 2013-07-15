<?php
/**
 * everquest install data
 * 
 * @package 	bbDKP
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2013 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 *
 */
namespace bbdkp;
/**
 * @ignore
 */
if (! defined ( 'IN_PHPBB' ))
{
	exit ();
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;

if (!class_exists('\bbdkp\aGameinstall'))
{
	require("{$phpbb_root_path}includes/bbdkp/games/library/aGameinstall.$phpEx");
}


/**
 * Installs Everquest
 * 
 * @author Sajaki
 * @package 	bbDKP
 * 
 */
class install_eq extends \bbdkp\aGameinstall
{

	/**
	 * Installs factions
	 */
	public function Installfactions()
	{
		global  $db, $table_prefix, $umil, $user;
		// Everquest factions
		$db->sql_query('DELETE FROM ' . $table_prefix . "bbdkp_factions where game_id = 'eq'" );
		$sql_ary = array();
		$sql_ary[] = array('game_id' => 'eq', 'faction_id' => 1, 'faction_name' => 'Good' );
		$sql_ary[] = array('game_id' => 'eq', 'faction_id' => 2, 'faction_name' => 'Evil' );
		$sql_ary[] = array('game_id' => 'eq', 'faction_id' => 3, 'faction_name' => 'Neutral' );
		$db->sql_multi_insert( $table_prefix . 'bbdkp_factions', $sql_ary);
		unset ($sql_ary);
		
	}
	
	/**
	 * Installs game classes
	*/
	public function InstallClasses()
	{
		global  $db, $table_prefix, $umil, $user;
		
		$db->sql_query('DELETE FROM ' . $table_prefix . "bbdkp_classes WHERE game_id = 'eq'" );
		$sql_ary = array();
		
		// Everquest classes - level cap raised to 100
		$sql_ary = array();
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 0, 'class_faction_id' => 3, 'class_armor_type' => 'PLATE', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#DCD09A', 'imagename' => 'eq_unknown');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 1, 'class_faction_id' => 3, 'class_armor_type' => 'PLATE', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#FF8855', 'imagename' => 'eq_warrior');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 2, 'class_faction_id' => 3, 'class_armor_type' => 'LEATHER', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#FFDD55', 'imagename' => 'eq_rogue');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 3, 'class_faction_id' => 3, 'class_armor_type' => 'LEATHER', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#FFFF88', 'imagename' => 'eq_monk');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 4, 'class_faction_id' => 3, 'class_armor_type' => 'LEATHER', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#EEFF99', 'imagename' => 'eq_ranger');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 5, 'class_faction_id' => 3, 'class_armor_type' => 'PLATE', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#FFBBCC', 'imagename' => 'eq_paladin');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 6, 'class_faction_id' => 3, 'class_armor_type' => 'PLATE', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#FF1166', 'imagename' => 'eq_shadow_Knight');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 7, 'class_faction_id' => 3, 'class_armor_type' => 'CLOTH', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#00FFAA', 'imagename' => 'eq_bard');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 8, 'class_faction_id' => 3, 'class_armor_type' => 'LEATHER', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#00EE00', 'imagename' => 'eq_beastlord');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 9, 'class_faction_id' => 3, 'class_armor_type' => 'CLOTH', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#00EEBB', 'imagename' => 'eq_cleric');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 10, 'class_faction_id' => 3, 'class_armor_type' => 'CLOTH', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#00EECC', 'imagename' => 'eq_druid');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 11, 'class_faction_id' => 3, 'class_armor_type' => 'CLOTH', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#00EEDD', 'imagename' => 'eq_shaman');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 12, 'class_faction_id' => 3, 'class_armor_type' => 'CLOTH', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#CC00AA', 'imagename' => 'eq_enchanter');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 13, 'class_faction_id' => 3, 'class_armor_type' => 'CLOTH', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#7700AA', 'imagename' => 'eq_wizard');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 14, 'class_faction_id' => 3, 'class_armor_type' => 'CLOTH', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#AA00AA', 'imagename' => 'eq_necromancer');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 15, 'class_faction_id' => 3, 'class_armor_type' => 'CLOTH', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#CC0099', 'imagename' => 'eq_magician');
		$sql_ary [] = array ('game_id' => 'eq', 'class_id' => 16, 'class_faction_id' => 3, 'class_armor_type' => 'LEATHER', 'class_min_level' => 1, 'class_max_level' => 100 , 'colorcode' =>  '#FFBB55', 'imagename' => 'eq_berserker');
		$db->sql_multi_insert( $table_prefix . 'bbdkp_classes', $sql_ary);
		
		$db->sql_query('DELETE FROM ' . $table_prefix . "bbdkp_language WHERE game_id = 'eq' AND attribute='class'  ");
		$sql_ary = array ();
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 0, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 1, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Warrior' ,  'name_short' =>  'Warrior' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 2, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Rogue' ,  'name_short' =>  'Rogue' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 3, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Monk' ,  'name_short' =>  'Monk' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 4, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Ranger' ,  'name_short' =>  'Ranger' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 5, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Paladin' ,  'name_short' =>  'Paladin' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 6, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Shadow Knight' ,  'name_short' =>  'Shadow Knight' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 7, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Bard' ,  'name_short' =>  'Bard' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 8, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Beastlord' ,  'name_short' =>  'Beastlord' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 9, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Cleric' ,  'name_short' =>  'Cleric' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 10, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Druid' ,  'name_short' =>  'Druid' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 11, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Shaman' ,  'name_short' =>  'Shaman' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 12, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Enchanter' ,  'name_short' =>  'Enchanter' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 13, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Wizard' ,  'name_short' =>  'Wizard' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 14, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Necromancer' ,  'name_short' =>  'Necromancer' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 15, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Magician' ,  'name_short' =>  'Magician' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 16, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Berserker' ,  'name_short' =>  'Berserker' );
		// remark : no french / german content since eq is english only
		$db->sql_multi_insert ( $table_prefix . 'bbdkp_language', $sql_ary);
		unset ( $sql_ary );
	}
	
	/**
	 * Installs races
	*/
	public function InstallRaces()
	{
		global  $db, $table_prefix, $umil, $user;
		
		// Everquest races
		$db->sql_query('DELETE FROM ' . $table_prefix . "bbdkp_races  where game_id = 'eq'");
		$sql_ary = array();
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 0, 'race_faction_id' => 2 ); //Unknown
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 1, 'race_faction_id' => 3 ); //Gnome
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 2, 'race_faction_id' => 3 ); //Human
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 3, 'race_faction_id' => 1 ); //Barbarian
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 4, 'race_faction_id' => 1 ); //Dwarf
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 5, 'race_faction_id' => 1 ); //High Elf
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 6, 'race_faction_id' => 3 ); //Half Elf
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 7, 'race_faction_id' => 2 ); //Dark Elf
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 8, 'race_faction_id' => 1 ); //Wood Elf'
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 9, 'race_faction_id' => 3 ); //Vah Shir
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 10, 'race_faction_id' => 2 ); //Troll
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 11, 'race_faction_id' => 2 ); //Ogre
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 12, 'race_faction_id' => 3 ); //Froglok
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 13, 'race_faction_id' => 2 ); //Iksar
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 14, 'race_faction_id' => 3 ); //Erudite
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 15, 'race_faction_id' => 1 ); //Halfling
		$sql_ary[] = array('game_id' => 'eq', 'race_id' => 16, 'race_faction_id' => 3 ); //Drakkin
		$db->sql_multi_insert( $table_prefix . 'bbdkp_races', $sql_ary);
		unset ($sql_ary);
		

		$db->sql_query('DELETE FROM ' . $table_prefix . "bbdkp_language  where game_id = 'eq' and attribute = 'race' ");
		$sql_ary = array ();
		

		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 0, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 1, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Gnome' ,  'name_short' =>  'Gnome' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 2, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Human' ,  'name_short' =>  'Human' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 3, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Barbarian' ,  'name_short' =>  'Barbarian' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 4, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Dwarf' ,  'name_short' =>  'Dwarf' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 5, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'High Elf' ,  'name_short' =>  'High Elf' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 6, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Half Elf' ,  'name_short' =>  'Half Elf' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 7, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Dark Elf' ,  'name_short' =>  'Dark Elf' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 8, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Wood Elf' ,  'name_short' =>  'Wood Elf' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 9, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Vah Shir' ,  'name_short' =>  'Vah Shir' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 10, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Troll' ,  'name_short' =>  'Troll' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 11, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Ogre' ,  'name_short' =>  'Ogre' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 12, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Froglok' ,  'name_short' =>  'Froglok' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 13, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Iksar' ,  'name_short' =>  'Iksar' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 14, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Erudite' ,  'name_short' =>  'Erudite' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 15, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Halfling' ,  'name_short' =>  'Halfling' );
		$sql_ary[] = array('game_id' => 'eq', 'attribute_id' => 16, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Drakkin' ,  'name_short' =>  'Drakkin' );
		
		// remark : no french / german content since eq is english only
		$db->sql_multi_insert ( $table_prefix . 'bbdkp_language', $sql_ary);
		unset ( $sql_ary );
		
		
	}
	
	/**
	 * Event Groups
	 * see parent function InstallEventGroup()
	 */


	/**
	 * Events and Events
	 * see parent function InstallEvents()
	 */

	
}


    
?>