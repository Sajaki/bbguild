<?php
/**
 * @package 	bbDKP
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2013 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 * @since 1.3.0
 */

namespace bbdkp;

/**
 * @ignore
 */

if (! defined('IN_PHPBB'))
{
	exit();
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;
if (!class_exists('\bbdkp\Admin'))
{
	require ("{$phpbb_root_path}includes/bbdkp/admin.$phpEx");
}
if (!class_exists('\bbdkp\Game'))
{
	require("{$phpbb_root_path}includes/bbdkp/games/Game.$phpEx");
}
//include the guilds class
if (!class_exists('\bbdkp\Roles'))
{
	require("{$phpbb_root_path}includes/bbdkp/guilds/Roles.$phpEx");
}
//Initialising the class
if (!class_exists('\bbdkp\WowAPI'))
{
	require($phpbb_root_path . 'includes/bbdkp/wowapi/WowAPI.' . $phpEx);
}

/**
 * manages member creation
 *
 * @package 	bbDKP
 *
 */
class Members extends \bbdkp\Admin
{

	/**
	 * game id
	 * @var string
	 */
	public $game_id;

	/**
	 * primary key in the bbDKP membertable
	 * @var integer
	 */
	public $member_id;

	/**
	 * utF-8 member name
	 * @var string
	 */
	protected $member_name;

	/**
	 * status (0 or 1)
	 * @var bool
	 */
	protected $member_status;

	/**
	 * level
	 * @var int
	 */
	protected $member_level;

	protected $member_race_id;
	protected $member_race;
	protected $member_class_id;
	protected $member_class;

	/**
	 * guild rankid
	 * @var int
	 */
	protected $member_rank_id;

	/**
	 * administrator comment
	 * @var string
	 */
	protected $member_comment;

	protected $member_joindate;
	protected $member_joindate_d;
	protected $member_joindate_mo;
	protected $member_joindate_y;
	protected $member_outdate;
	protected $member_outdate_d;
	protected $member_outdate_mo;
	protected $member_outdate_y;

	/**
	 * the id of my guild
	 * @var int
	 */
	protected $member_guild_id;

	/**
	 * my guildname
	 * @var string
	 */
	protected $member_guild_name;

	/**
	 * character realm
	 * @var string
	 */
	protected $member_realm;

	/**
	 * region to which the char is on
	 * @var string
	 */
	protected $member_region;


	/**
	 * Allowed regions
	 * readonly!
	 * @var array
	 */
	protected $regionlist = array( 'eu', 'us' , 'kr', 'tw', 'cn', 'sea');

	/**
	 *gender ID 0=male, 1=female
	 * @var int
	 */
	protected $member_gender_id;

	/**
	 * Achievement points
	 * @var int
	 */
	protected $member_achiev;

	/**
	 * url to armory
	 * @var string
	 */
	protected $member_armory_url;


	protected $member_portrait_url;

	/**
	 * The phpBB member id linked to this member
	 * @var int
	 */
	protected $phpbb_user_id;

	/**
	 * Class color
	 * @var string
	 */
	protected $colorcode;

	/**
	 * Race icon
	 * @var string
	 */
	protected $race_image;

	/**
	 * Class icon
	 * @var string
	 */
	protected $class_image;

	/**
	 * current talent builds
	 * @var string
	 */
	protected $talents;

	/**
	 * current title
	 * @var string
	 */
	protected $member_title;

	/**
	 * the role (for possible roles see role class)
	 * @var string
	 */
	protected $member_role;

	/**
	 * contains list of members for game x
	 * @var array
	 */
	public $gamememberlist;

	/**
	 */
	function __construct($member_id = 0)
	{
		parent::__construct();
		if(isset($member_id))
		{
			$this->member_id = $member_id;
		}
		else
		{
			$this->member_id = 0;
		}

		$this->Getmember();
	}


	/**
	 *
	 * @param string $fieldName
	 */
	public function __get($fieldName)
	{
		global $user;

		if (property_exists($this, $fieldName))
		{
			return $this->$fieldName;
		}
		else
		{
			trigger_error($user->lang['ERROR'] . '  '. $fieldName, E_USER_WARNING);
		}
	}

	/**
	 *
	 * @param unknown_type $property
	 * @param unknown_type $value
	 */
	public function __set($property, $value)
	{
		global $user;
		switch ($property)
		{
			case 'regionlist':
				// is readonly
				break;
			default:
				if (property_exists($this, $property))
				{
					$this->$property = $value;
				}
				else
				{
					trigger_error($user->lang['ERROR'] . '  '. $property, E_USER_WARNING);
				}
		}
	}

	/**
	 * gets 1 member from database
	 */
	public function Getmember()
	{
		global $user, $db, $config, $phpEx, $phpbb_root_path;

		$sql_array = array(
			'SELECT' => 'm.*, c.colorcode , c.imagename,  c1.name AS member_class, l1.name AS member_race,
						r.image_female, r.image_male,
						g.id as guild_id, g.name as guild_name, g.realm , g.region' ,
			'FROM' => array(
					MEMBER_LIST_TABLE => 'm' ,
					CLASS_TABLE => 'c' ,
					BB_LANGUAGE => 'l1' ,
					RACE_TABLE => 'r' ,
					GUILD_TABLE => 'g') ,
			'LEFT_JOIN' => array(
					array(
						'FROM' => array(
							BB_LANGUAGE => 'c1') ,
						'ON' => "c1.attribute_id = c.class_id
						AND c1.game_id = c.game_id
						AND c1.language= '" . $config['bbdkp_lang'] . "'
						AND c1.attribute = 'class'")) ,
			'WHERE' => "
					l1.attribute_id = r.race_id AND l1.game_id = r.game_id AND l1.language= '" . $config['bbdkp_lang'] . "' AND l1.attribute = 'race'
					AND m.game_id = c.game_id
					AND m.member_class_id = c.class_id
					AND m.game_id = r.game_id
					AND m.member_race_id = r.race_id
					AND m.member_guild_id = g.id
					AND member_id = " . (int) $this->member_id);

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{


			$this->member_id = $row['member_id'] ;
			$this->member_name = $row['member_name'] ;
			$this->member_race_id = $row['member_race_id'] ;
			$this->member_race = $row['member_race'] ;
			$this->member_class_id = $row['member_class_id'] ;
			$this->member_class = $row['member_class'] ;
			$this->member_level = $row['member_level'] ;
			$this->member_rank_id = $row['member_rank_id'] ;
			$this->member_comment = $row['member_comment'] ;
			$this->member_gender_id = $row['member_gender_id'] ;
			$this->member_joindate = $row['member_outdate'] ;
			$this->member_role = $row['member_role'] ;
			$this->member_joindate_d = date('j', $row['member_joindate']) ;
			$this->member_joindate_mo = date('n', $row['member_joindate']);
			$this->member_joindate_y = date('Y', $row['member_joindate']) ;
			$this->member_outdate = $row['member_outdate'];
			$this->member_outdate_d = date('j', $row['member_outdate']);
			$this->member_outdate_mo = date('n', $row['member_outdate']);
			$this->member_outdate_y = date('Y', $row['member_outdate']);
			$this->member_guild_name = $row['guild_name'];
			$this->member_guild_id = $row['guild_id'];
			$this->member_realm = $row['realm'];
			$this->member_region = $row['region'];
			$this->member_armory_url = $row['member_armory_url'];
			$this->member_portrait_url = $row['member_portrait_url'];
			$this->phpbb_user_id = $row['phpbb_user_id'];
			$this->member_status = $row['member_status'];
			$this->member_achiev = $row['member_achiev'];
			$this->game_id = $row['game_id'];
			$this->colorcode = $row['colorcode'];
			$race_image = (string) (($row['member_gender_id'] == 0) ? $row['image_male'] : $row['image_female']);
			$this->race_image = (strlen($race_image) > 1) ? $phpbb_root_path . "images/bbdkp/race_images/" . $race_image . ".png" : '';
			$this->class_image = (strlen($row['imagename']) > 1) ? $phpbb_root_path . "images/bbdkp/class_images/" . $row['imagename'] . ".png" : '';
			$this->member_title  = $row['member_title'];

			return true;
		}
		else
		{
			// load games class
			$games = new \bbdkp\Game();
			if(isset($games->games))
			{
				$this->games = $games->games;
				foreach($this->games as $key => $value)
				{
					$this->game_id = $key;
					break;
				}
			}
			unset($games);
			$this->member_id = 0 ;
			$this->member_name = $user->lang['NA'] ;
			$this->member_race_id = 0 ;
			$this->member_race = '' ;
			$this->member_class_id = 0;
			$this->member_class = '' ;
			$this->member_level = 0;
			$this->member_rank_id = 0;
			$this->member_comment = '' ;
			$this->member_gender_id = 0;
			$this->member_role = 'NA';
			$this->member_joindate = $this->time;
			$this->member_joindate_d = date('j', $this->time) ;
			$this->member_joindate_mo = date('n', $this->time);
			$this->member_joindate_y = date('Y', $this->time) ;
			$this->member_outdate = 0;
			$this->member_outdate_d = date('j', 0);
			$this->member_outdate_mo = date('n', 0);
			$this->member_outdate_y = date('Y', 0);
			$this->member_guild_name = '';
			$this->member_guild_id = 0;
			$this->member_realm = '';
			$this->member_region = '';
			$this->member_armory_url = '';
			$this->member_portrait_url = '';
			$this->phpbb_user_id = '';
			$this->member_status = 0;
			$this->member_achiev = 0;
			$this->colorcode = "#000000";
			$race_image = '';
			$this->race_image = '';
			$this->class_image = '';
			$this->member_title = '';
		}


	}

	/**
	 * get member id given a membername and guild
	 *
	 * @param string $membername
	 * @param int $guild_id optional
	 * @return int
	 */
	public function get_member_id ($membername, $guild_id = 0)
	{
		global $db;
		if($guild_id !=0)
		{
			$sql = 'SELECT member_id
					FROM ' . MEMBER_LIST_TABLE . "
					WHERE member_name ='" . $db->sql_escape($membername) . "'
					AND member_guild_id = " . (int) $db->sql_escape($guild_id);

		}
		else
		{
			$sql = 'SELECT member_id
					FROM ' . MEMBER_LIST_TABLE . "
					WHERE member_name ='" . $db->sql_escape($membername) . "'";
		}

		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$membid = $row['member_id'];
			break;
		}
		$db->sql_freeresult($result);
		if (isset($membid))
		{
			return $membid;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Insert new member
	 * @return number
	 */
	public function Makemember()
	{
		global $user, $db, $config, $phpEx, $phpbb_root_path;

		$error = array ();

		$this->member_status = 1;

		$sql = 'SELECT count(*) as memberexists
				FROM ' . MEMBER_LIST_TABLE . "
				WHERE member_name= '" . $db->sql_escape(ucwords($this->member_name)) . "'
				AND member_guild_id = " . $this->member_guild_id;
		$result = $db->sql_query($sql);
		$countm = $db->sql_fetchfield('memberexists');
		$db->sql_freeresult($result);
		if ($countm != 0)
		{
			$error[]= $user->lang['ERROR_MEMBEREXIST'];
		}

		$sql = 'SELECT count(*) as rankccount
			FROM ' . MEMBER_RANKS_TABLE . '
			WHERE rank_id=' . (int) $this->member_rank_id . '
			AND guild_id = ' . (int) $this->member_guild_id;
		$result = $db->sql_query($sql);
		$countm = $db->sql_fetchfield('rankccount');
		$db->sql_freeresult($result);
		if ($countm == 0)
		{
			$error[]= $user->lang['ERROR_INCORRECTRANK'];
		}

		if (count($error) > 0)
		{
			$log_action = array(
				'header' 	 => 'L_ACTION_MEMBER_ADDED' ,
				'L_NAME' 	 => ucwords($this->member_name)  ,
				'L_GAME' 	 => $this->game_id,
				'L_LEVEL' 	 => $this->member_level,
				'L_RACE' 	 => $this->member_race_id,
				'L_CLASS' 	 => $this->member_class_id,
				'L_ADDED_BY' => $user->data['username']);

			$this->log_insert(array(
				'log_type' 		=> 'L_ACTION_MEMBER_ADDED' ,
				'log_result' 	=> 'L_FAILED' ,
				'log_action' 	=> $log_action));

			return 0;
		}

		$sql = 'SELECT max(class_max_level) as maxlevel FROM ' . CLASS_TABLE;
		$result = $db->sql_query($sql);
		$maxlevel = $db->sql_fetchfield('maxlevel');
		$db->sql_freeresult($result);
		if ($this->member_level > $maxlevel)
		{
			$this->member_level = $maxlevel;
		}

		$sql = 'SELECT realm, region FROM ' . GUILD_TABLE . ' WHERE id = ' . (int) $this->member_guild_id;
		$result = $db->sql_query($sql);
		$this->member_realm  = $config['bbdkp_default_realm'];
		$this->member_region = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$this->member_realm = $row['realm'];
			$this->member_region = $row['region'];
		}


		switch ($this->game_id)
		{
			case 'wow':
				$this->Armory_getmember();
				break;
			case 'aion':
				$this->member_portrait_url = $this->generate_portraitlink();
		}

		$query = $db->sql_build_array('INSERT', array(
			'member_name' => ucwords($this->member_name) ,
			'member_status' => $this->member_status ,
			'member_level' => $this->member_level,
			'member_race_id' => $this->member_race_id ,
			'member_class_id' => $this->member_class_id ,
			'member_rank_id' => $this->member_rank_id ,
			'member_role' => $this->member_role,
			'member_comment' => (string) $this->member_comment ,
			'member_joindate' => (int) $this->member_joindate ,
			'member_outdate' => (int) $this->member_outdate ,
			'member_guild_id' => $this->member_guild_id ,
			'member_gender_id' => $this->member_gender_id ,
			'member_achiev' => $this->member_achiev ,
			'member_armory_url' => (string) $this->member_armory_url ,
			'phpbb_user_id' => (int) $this->phpbb_user_id ,
			'game_id' => (string) $this->game_id ,
			'member_portrait_url' => (string) $this->member_portrait_url,
			'member_title' => $this->member_title
			));

		$db->sql_query('INSERT INTO ' . MEMBER_LIST_TABLE . $query);

		$this->member_id = $db->sql_nextid();

		$log_action = array(
			'header' 	 => 'L_ACTION_MEMBER_ADDED' ,
			'L_NAME' 	 => ucwords($this->member_name)  ,
			'L_GAME' 	 => $this->game_id,
			'L_LEVEL' 	 => $this->member_level,
			'L_RACE' 	 => $this->member_race_id,
			'L_CLASS' 	 => $this->member_class_id,
			'L_ADDED_BY' => $user->data['username']);

		$this->log_insert(array(
			'log_type' => 'L_ACTION_MEMBER_ADDED' ,
			'log_action' => $log_action));

		return $this->member_id;

	}

	/**
	 * update member
	 * @param Members $old_member
	 * @return boolean
	 */
	public function Updatemember(Members $old_member)
	{
		global $user, $db, $config, $phpEx, $phpbb_root_path;

		if ($this->member_id == 0)
		{
			return false;
		}

		// if user chooses other name then check if the new name already exists. if so refuse update
		// namechange to existing membername is not allowed
		if($this->member_name != $old_member->member_name)
		{
			$sql = 'SELECT count(*) as memberexists
				FROM ' . MEMBER_LIST_TABLE . '
				WHERE member_id <> ' . $this->member_id . "
				AND UPPER(member_name)= UPPER('" . $db->sql_escape($this->member_name) . "')";
			$result = $db->sql_query($sql);
			$countm = $db->sql_fetchfield('memberexists');
			$db->sql_freeresult($result);
			if ($countm != 0)
			{
				trigger_error(sprintf($user->lang['ADMIN_UPDATE_MEMBER_FAIL'], ucwords($this->member_name)) , E_USER_WARNING);
			}
		}

			// check if rank exists
		$sql = 'SELECT count(*) as rankccount
				FROM ' . MEMBER_RANKS_TABLE . '
				WHERE rank_id=' . (int) $this->member_rank_id . ' and guild_id = ' . $this->member_guild_id;
		$result = $db->sql_query($sql);
		$countm = $db->sql_fetchfield('rankccount');
		$db->sql_freeresult($result);
		if ($countm == 0)
		{
			trigger_error($user->lang['ERROR_INCORRECTRANK'], E_USER_WARNING);
		}

		// check level
		$sql = 'SELECT max(class_max_level) as maxlevel FROM ' . CLASS_TABLE;
		$result = $db->sql_query($sql);
		$maxlevel = $db->sql_fetchfield('maxlevel');
		$db->sql_freeresult($result);
		if ($this->member_level > $maxlevel)
		{
			$this->member_level = $maxlevel;
		}

		switch ($this->game_id)
		{
			case 'wow':
				$this->Armory_getmember();
				break;
			case 'aion':
				if(trim($this->member_portrait_url) == '')
				{
					$this->member_portrait_url = $this->generate_portraitlink();
				}
				break;
		}

		// Get first and last raiding dates from raid table.
		$sql = "SELECT b.member_id,
			MIN(a.raid_start) AS startdate,
			MAX(a.raid_start) AS enddate
			FROM " . RAIDS_TABLE . " a
			INNER JOIN " . RAID_DETAIL_TABLE . " b on a.raid_id = b.raid_id
			WHERE  b.member_id = " . $this->member_id . "
			GROUP BY b.member_id ";
		$result = $db->sql_query($sql);
		$startraiddate = (int) $db->sql_fetchfield('startdate', false, $result);
		$endraiddate = (int) $db->sql_fetchfield('enddate', false, $result);
		$db->sql_freeresult($result);

		// if first recorded raiddate is before joindate then update joindate
		if ($startraiddate != 0 && ($this->member_joindate == 0 || $this->member_joindate > $startraiddate))
		{
			$this->member_joindate = $startraiddate;
		}

		// if last raiddate is after outdate or outdate is in future then reset it
		if ($endraiddate !=0 && ($this->member_outdate < $endraiddate || $this->member_outdate > time()))
		{
			$this->member_outdate = mktime(0, 0, 0, 12, 31, 2030);
		}

		// update the data including the phpbb userid
		$query = $db->sql_build_array('UPDATE', array(
			'member_name' => $this->member_name ,
			'member_status' => $this->member_status ,
			'member_level' => $this->member_level ,
			'member_race_id' => $this->member_race_id ,
			'member_class_id' => $this->member_class_id,
			'member_role' => $this->member_role,
			'member_rank_id' => $this->member_rank_id ,
			'member_gender_id' => $this->member_gender_id ,
			'member_comment' => $this->member_comment ,
			'member_guild_id' => $this->member_guild_id,
			'member_outdate' => $this->member_outdate,
			'member_joindate' => $this->member_joindate,
			'phpbb_user_id' => $this->phpbb_user_id,
			'member_armory_url' => $this->member_armory_url,
			'member_portrait_url' => $this->member_portrait_url,
			'member_achiev' => $this->member_achiev,
			'game_id' => $this->game_id,
			'member_title' => $this->member_title
			));

		$db->sql_query('UPDATE ' . MEMBER_LIST_TABLE . ' SET ' . $query . '
			WHERE member_id= ' . $this->member_id);

		// if status was 1 before then add a line in user comments
		if ($this->member_status == 0 && $old_member->member_status == 1)
		{
			// update the comment including the phpbb userid
			$query = $db->sql_build_array('UPDATE', array(
				'member_comment' => $this->member_comment . '
' . sprintf($user->lang['BBDKP_MEMBERDEACTIVATED'] , $user->data['username'], date( 'd.m.y G:i:s', $this->time ))  ,
			));
			$db->sql_query('UPDATE ' . MEMBER_LIST_TABLE . ' SET ' . $query . '
				WHERE member_id= ' . $this->member_id);
		}

		$log_action = array(
			'header' => 'L_ACTION_MEMBER_UPDATED' ,
			'L_NAME' => $this->member_name ,
			'L_NAME_BEFORE' => $old_member->member_name,
			'L_LEVELBEFORE' => $old_member->member_level,
			'L_RACE_BEFORE' => $old_member->member_race_id,
			'L_RANK_BEFORE' => $old_member->member_rank_id,
			'L_CLASS_BEFORE' => $old_member->member_class_id,
			'L_GENDER_BEFORE' => $old_member->member_gender_id,
			'L_ACHIEV_BEFORE' => $old_member->member_achiev,
			'L_NAME_AFTER' => $this->member_name,
			'L_LEVELAFTER' => $this->member_level,
			'L_RACE_AFTER' => $this->member_race_id ,
			'L_RANK_AFTER' => $this->member_rank_id,
			'L_CLASS_AFTER' => $this->member_class_id ,
			'L_GENDER_AFTER' => $this->member_gender_id,
			'L_ACHIEV_AFTER' => $this->member_achiev,
			'L_UPDATED_BY' => $user->data['username']);

		$this->log_insert(array(
			'log_type' => $log_action['header'] ,
			'log_action' => $log_action));

	}

	/**
	 * delete member from all tables
	 */
	public function Deletemember()
	{
		global $user, $db, $config, $phpEx, $phpbb_root_path;

		$sql = 'DELETE FROM ' . RAID_DETAIL_TABLE . ' where member_id = ' . (int) $this->member_id;
		$db->sql_query($sql);
		$sql = 'DELETE FROM ' . RAID_ITEMS_TABLE . ' where member_id = ' . (int) $this->member_id;
		$db->sql_query($sql);
		$sql = 'DELETE FROM ' . MEMBER_DKP_TABLE . ' where member_id = ' . (int) $this->member_id;
		$db->sql_query($sql);
		$sql = 'DELETE FROM ' . ADJUSTMENTS_TABLE . ' where member_id = ' . (int) $this->member_id;
		$db->sql_query($sql);
		$sql = 'DELETE FROM ' . MEMBER_LIST_TABLE . ' where member_id = ' . (int) $this->member_id;
		$db->sql_query($sql);

		$log_action = array(
			'header' => sprintf($user->lang['ACTION_MEMBER_DELETED'], $this->member_name) ,
			'L_NAME' => $this->member_name ,
			'L_LEVEL' => $this->member_level ,
			'L_RACE' => $this->member_race_id ,
			'L_CLASS' => $this->member_class_id);

		$this->log_insert(array(
			'log_type' => $log_action['header'] ,
			'log_action' => $log_action));

	}

	/**
	 * fetch info from Armory
	 * @return boolean
	 */
	public function Armory_getmember()
	{
		global $phpEx, $phpbb_root_path;
		switch ($this->game_id)
		{
			case 'wow':
				/**
					* available extra fields :
				 * 'guild','stats','talents','items','reputation','titles','professions','appearance',
				 * 'companions','mounts','pets','achievements','progression','pvp','quests'
				 */
				$api = new \bbdkp\WowAPI('character', $this->member_region);
				$params = array('guild', 'titles', 'talents' );

				$data = $api->Character->getCharacter($this->member_name, $this->member_realm, $params);
				unset($api);

				// if $data == false then there is no character data, so
				if($data != false)
				{
					$this->member_level = isset($data['level']) ? $data['level'] : $this->member_level;
					$this->member_race_id = isset($data['race']) ? $data['race'] : $this->member_race_id;
					$this->member_class_id = isset($data['class']) ? $data['class'] : $this->member_class_id;

					/*
					 * select the build
					*/
					$buildid = 0;
					if(isset($data['talents'][0]) && isset( $data['talents'][1]) )
					{
						if( isset($data['talents'][0]['selected']))
						{
							$buildid = 0;
						}
						elseif(isset($data['talents'][1]['selected']))
						{
							$buildid = 1;
						}
					}
					elseif(isset( $data['talents'][0]))
					{
						$buildid = 0;
					}
					elseif(isset( $data['talents'][1]))
					{
						$buildid = 1;
					}
					$role = isset($data['talents'][$buildid]['spec']['role']) ? $data['talents'][$buildid]['spec']['role'] : 'NA';
					$this->member_role = $role;

					$this->member_gender_id = isset($data['gender']) ? $data['gender'] : $this->member_gender_id;
					$this->member_achiev = isset($data['achievementPoints']) ? $data['achievementPoints'] : $this->member_achiev;

					if(isset($data['name']))
					{
						$this->member_armory_url = sprintf('http://%s.battle.net/wow/en/', $this->member_region) . 'character/' . $this->member_realm . '/' . $data ['name'] . '/simple';
					}

					if(isset($data['thumbnail']))
					{
						$this->member_portrait_url = sprintf('http://%s.battle.net/static-render/%s/', $this->member_region, $this->member_region) . $data['thumbnail'];
					}

					if (isset($data['titles']))
					{
						foreach($data['titles'] as $key => $title)
						{
							if (isset($title['selected']))
							{
								$this->member_title = $title['name'];
							}
						}
					}

					//if the last logged-in date is > 3 months ago then disable the account
					if( isset($data['lastModified']))
					{
						$latest = $data['lastModified']/1000;
						$diff = \round( \abs ( \time() - $latest) / 60 / 60 / 24, 2) ;
						if($diff > 90 && $this->member_status == 1)
						{
							$this->deactivate_wow($diff);
							return false;
						}

					}

					return true;

				}
				else
				{
					$this->deactivate_wow('API Error');
					return false;
				}
			default:
				return true;
		}

	}


	/**
	 * called when user is deactivated because of wow inactivity > 90 days
	 */
	private function deactivate_wow($daysago)
	{
		global $user;
		$this->member_status = 0;

		$log_action = array(
			'header' 	 => 'L_ACTION_MEMBER_DEACTIVATED' ,
			'L_NAME' 	 => \ucwords($this->member_name)  ,
			'L_DAYSAGO'  => $daysago,
			'L_ADDED_BY' => $user->data['username']);

		$this->log_insert(array(
			'log_type' 		=> 'L_ACTION_MEMBER_DEACTIVATED' ,
			'log_result' 	=> 'L_SUCCESS' ,
			'log_action' 	=> $log_action));


	}

	/**
	 * activates all checked members
	 * @param array $mlist
	 * @param array $mwindow
	 */
	public function Activatemembers(array $mlist, array $mwindow)
	{

		global $user, $db, $config, $phpEx, $phpbb_root_path;

		$db->sql_transaction('begin');
		//if checkbox set then activate
		$sql1 = 'UPDATE ' . MEMBER_LIST_TABLE . "
			SET member_status = '1'
			WHERE " . $db->sql_in_set('member_id', $mlist, false, true);
		$db->sql_query($sql1);
		//if checkbox not set and in window then deactivate
		$sql2 = 'UPDATE ' . MEMBER_LIST_TABLE . "
			SET member_status = '0'
			WHERE  " . $db->sql_in_set('member_id', $mlist, true, true) . "
			AND  " . $db->sql_in_set('member_id', $mwindow, false, true);
		$db->sql_query($sql2);
		$db->sql_transaction('commit');
	}


	/**
	 * generates a standard portrait image url for aion based on characterdata
	 */
	private function generate_portraitlink()
	{
		global $phpbb_root_path;
		if ($this->game_id == 'aion')
		{
			$this->member_portrait_url = $phpbb_root_path . 'images/roster_portraits/aion/' . $this->member_race_id . '_' . $this->member_gender_id . '.jpg';
		}
	}

	/**
	 * function for removing member from guild but leave him in the member table.;
	 * @param unknown_type $member_name
	 * @param unknown_type $guild_id
	 * @return boolean
	 * @todo fix this
	 */
	public function GuildKick($member_name, $guild_id)
	{
		global $db, $user, $config;
		// find id for existing member name
		$sql = "SELECT *
				FROM " . MEMBER_LIST_TABLE . "
				WHERE member_name = '" . $db->sql_escape($member_name) . "' and member_guild_id = " . (int) $guild_id;
		$result = $db->sql_query($sql);
		// get old data
		while ($row = $db->sql_fetchrow($result))
		{
			$this->old_member = array(
					'member_id' => $row['member_id'] ,
					'member_rank_id' => $row['member_rank_id'] ,
					'member_guild_id' => $row['member_guild_id'] ,
					'member_comment' => $row['member_comment']);
		}
		$db->sql_freeresult($result);
		$sql_arr = array(
			'member_rank_id' => 99 ,
			'member_comment' => "Member left " . date("F j, Y, g:i a") . ' by Armory plugin' ,
			'member_outdate' => $this->time ,
			'member_guild_id' => 0);
		$sql = 'UPDATE ' . MEMBER_LIST_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_arr) . '
			WHERE member_id = ' . (int) $this->old_member['member_id'] . ' and member_guild_id = ' . (int) $this->old_member['member_guild_id'];

		$db->sql_query($sql);

		$log_action = array(
			'header' => 'L_ACTION_MEMBER_UPDATED' ,
			'L_NAME' => $member_name ,
			'L_RANK_BEFORE' => $this->old_member['member_rank_id'] ,
			'L_COMMENT_BEFORE' => $this->old_member['member_comment'] ,
			'L_RANK_AFTER' => 99 ,
			'L_COMMENT_AFTER' => "Member left " . date("F j, Y, g:i a") . ' by Armory plugin' ,
			'L_UPDATED_BY' => $user->data['username']);

		$this->log_insert(array(
			'log_type' => $log_action['header'] ,
			'log_action' => $log_action));

		return true;
	}

	/**
	 * Process Blizzard Battle.NET Character API data
	 * @param array $memberdata
	 * @param int $guild_id
	 * @param char $region
	 * @param int $min_armory
	 */
	public function WoWArmoryUpdate($memberdata, $guild_id, $region, $min_armory)
	{
		global $user, $db, $phpEx, $phpbb_root_path;
		$member_id = 0;
		$member_ids = array();
		$oldmembers = array();
		$newmembers = array();

		/* GET OLD RANKS */
		$sql = ' select member_name, member_id FROM ' . MEMBER_LIST_TABLE . '
				WHERE member_guild_id =  ' . (int) $guild_id . "
				AND game_id='wow' order by member_name ASC";
		$result = $db->sql_query ($sql);
		while ($row = $db->sql_fetchrow ($result))
		{
			$oldmembers[] = $row['member_name'];

			//this is to find the memberindex when updating
			$member_ids[ bin2hex($row['member_name'])] = $row['member_id'];

		}
		$db->sql_freeresult ( $result );

		foreach($memberdata as $mb)
		{
			$newmembers[] = $mb['character']['name'];
		}

		// get the new members to insert
		$to_add = array_diff($newmembers, $oldmembers);

		// start transaction
		$db->sql_transaction('begin');
		$query = array();
		foreach($memberdata as $mb)
		{
			if (in_array($mb['character']['name'], $to_add) && $mb['character']['level'] >= $min_armory )
			{
				$this->game_id ='wow';
				$this->member_guild_id = $guild_id;
				$this->member_rank_id = $mb['rank'];
				$this->member_name = $mb['character']['name'];
				$this->member_level = (int) $mb['character']['level'];
				$this->member_gender_id = (int) $mb['character']['gender'];
				$this->member_race_id = (int) $mb['character']['race'];
				$this->member_class_id = (int) $mb['character']['class'];
				$this->member_achiev = (int) $mb['character']['achievementPoints'];
				$this->member_armory_url = sprintf('http://%s.battle.net/wow/en/', $region) . 'character/' . $mb['character']['realm'] . '/' . $this->member_name . '/simple';
				$this->member_status = 1;
				$this->member_comment = sprintf($user->lang['ADMIN_ADD_MEMBER_SUCCESS'], $this->member_name, date("F j, Y, g:i a") );
				$this->member_joindate = $this->time;
				$this->member_outdate = mktime ( 0, 0, 0, 12, 31, 2030 );
				$this->member_portrait_url = sprintf('http://%s.battle.net/static-render/%s/', $region, $region) . $mb['character']['thumbnail'];
				$this->member_title = '';
				if (isset($mb['titles']))
				{
					foreach($mb['titles'] as $key => $title)
					{
						if (isset($title['selected']))
						{
							$this->member_title = $title['name'];
						}
					}
				}


				$query [] = array (
					'member_name' => ucwords($this->member_name) ,
					'member_status' => $this->member_status ,
					'member_level' => $this->member_level,
					'member_race_id' => $this->member_race_id ,
					'member_guild_id' => $this->member_guild_id ,
					'member_class_id' => $this->member_class_id ,
					'member_rank_id' => $this->member_rank_id ,
					'member_comment' => (string) $this->member_comment ,
					'member_joindate' => (int) $this->member_joindate ,
					'member_outdate' => (int) $this->member_outdate ,
					'member_guild_id' => $this->member_guild_id ,
					'member_gender_id' => $this->member_gender_id ,
					'member_achiev' => $this->member_achiev ,
					'member_armory_url' => (string) $this->member_armory_url ,
					'phpbb_user_id' => (int) $this->phpbb_user_id ,
					'game_id' => (string) $this->game_id ,
					'member_portrait_url' => (string) $this->member_portrait_url,

				);
			}
		}

		if(count($query) > 0)
		{
			$db->sql_multi_insert(MEMBER_LIST_TABLE, $query);
			$db->sql_transaction('commit');
		}

		// get the members to update
		$to_update = array_intersect($newmembers, $oldmembers);
		foreach($memberdata as $mb)
		{
			if (in_array($mb['character']['name'], $to_update))
			{
				$member_id =  (int) $member_ids[bin2hex($mb['character']['name'])];
				$this->game_id ='wow';
				$this->member_rank_id = $mb['rank'];
				$this->member_name = $mb['character']['name'];
				$this->member_guild_id = $guild_id;
				$this->member_level = (int) $mb['character']['level'];
				$this->member_gender_id = (int) $mb['character']['gender'];
				$this->member_race_id = (int) $mb['character']['race'];
				$this->member_class_id = (int) $mb['character']['class'];
				$this->member_achiev = (int) $mb['character']['achievementPoints'];
				$this->member_armory_url = sprintf('http://%s.battle.net/wow/en/', $region) . 'character/' . $mb['character']['realm'] . '/' . $this->member_name . '/simple';
				$this->member_portrait_url = sprintf('http://%s.battle.net/static-render/%s/', $region, $region) . $mb['character']['thumbnail'];

				$sql_ary = array (
					'member_name' => ucwords($this->member_name) ,
					'member_level' => $this->member_level,
					'member_race_id' => $this->member_race_id ,
					'member_class_id' => $this->member_class_id ,
					'member_rank_id' => $this->member_rank_id ,
					'member_guild_id' => $this->member_guild_id ,
					'member_gender_id' => $this->member_gender_id ,
					'member_achiev' => $this->member_achiev ,
					'member_armory_url' => (string) $this->member_armory_url ,
					'phpbb_user_id' => (int) $this->phpbb_user_id ,
					'member_portrait_url' => (string) $this->member_portrait_url,
				);


				$sql = 'UPDATE ' . MEMBER_LIST_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE member_id = ' . $member_id;
				$db->sql_query($sql);


			}
		}

	}

	/**
	 * Enter joindate for guildmember (query is cached for 1 week !)
	 *
	 * @param unknown_type $member_id
	 * @return unknown
	 *
	 */
	public function get_joindate($member_id)
	{
		// get member joindate
		global $db;
		$sql = 'SELECT member_joindate  FROM ' . MEMBER_LIST_TABLE . ' WHERE member_id = ' . $member_id;
		$result = $db->sql_query($sql,3600);
		$joindate = $db->sql_fetchfield('member_joindate');

		$db->sql_freeresult($result);
		return $joindate;

	}


	/**
	 * ACP listmembers grid
	 * get a member list for given game/guild
	 * @param unknown_type $game_id
	 */
	public function listallmembers($game_id = '', $guild_id = 0)
	{
		global $db;

		$sql_array = array(
			'SELECT'    => 'r.rank_name, m.member_id ,m.member_name ',
			'FROM'      => array(
					MEMBER_LIST_TABLE 	  => 'm',
					MEMBER_RANKS_TABLE    => 'r',
			),

			'WHERE'     =>  ' m.member_guild_id = r.guild_id
								AND m.member_rank_id = r.rank_id
								AND r.rank_hide != 1 ',
			'ORDER_BY' => 'm.member_rank_id asc, m.member_level desc, m.member_name asc'
		);

		if($guild_id != 0)
		{
			$sql_array['WHERE'] .= ' AND m.member_guild_id = ' . $guild_id;
		}

		if ($game_id != '')
		{
			$sql_array['WHERE'] .= " AND m.game_id = '" . $game_id . "' ";
		}

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query ( $sql );

		while ( $row = $db->sql_fetchrow ( $result ) )
		{
			$this->gamememberlist[] = array(
				'member_id' 	=> $row['member_id'],
				'member_name' 	=> $row['member_name'],
				'rank_name'  	=> $row['rank_name'],
			);
		}

		$db->sql_freeresult( $result );
	}
	
	/**
	 * Frontview : member Roster listing 
	 * required class game property to be set before call
	 * 
	 * @param string $mode (listing or class)
	 * @param int $start start of query window
	 * @param int $guild_id optional guild id
	 * @param int $class_id optional class id
	 * @param int $race_id optional race id
	 * @param int $level1 optional level1 (default 1)
	 * @param int $level2 optional level2 (default 200)
	 * @return array (membercount, sql_fetchrowset of all rows)
	 *  
	 *  
	 */
	public function get_listingresult($start, $mode, $guild_id = 0, $class_id = 0, $race_id = 0, $level1=0, $level2=200)
	{
		global $db, $config;
		$sql_array = array();

		$sql_array['SELECT'] =  'm.game_id, m.member_guild_id,  m.member_name, m.member_level, m.member_race_id, e1.name as race_name,
    		m.member_class_id, m.member_gender_id, m.member_rank_id, m.member_achiev, m.member_armory_url, m.member_portrait_url,
    		r.rank_prefix , r.rank_name, r.rank_suffix, e.image_female, e.image_male,
    		g.name, g.realm, g.region, c1.name as class_name, c.colorcode, c.imagename, m.phpbb_user_id, u.username, u.user_colour  ';
	
		$sql_array['FROM'] = array(
				MEMBER_LIST_TABLE    =>  'm',
				CLASS_TABLE          =>  'c',
				GUILD_TABLE          =>  'g',
				MEMBER_RANKS_TABLE   =>  'r',
				RACE_TABLE           =>  'e',
				BB_LANGUAGE			 =>  'e1');
	
		$sql_array['LEFT_JOIN'] = array(
				array(
						'FROM'  => array(USERS_TABLE => 'u'),
						'ON'    => 'u.user_id = m.phpbb_user_id '),
				array(
						'FROM'  => array(BB_LANGUAGE => 'c1'),
						'ON'    => "c1.attribute_id = c.class_id AND c1.language= '" . $config['bbdkp_lang'] . "' AND c1.attribute = 'class'  and c1.game_id = c.game_id "
				));
	
		$sql_array['WHERE'] = " c.class_id = m.member_class_id
			AND c.game_id = m.game_id
			AND e.race_id = m.member_race_id
			AND e.game_id = m.game_id
			AND g.id = m.member_guild_id
			AND r.guild_id = m.member_guild_id
			AND r.rank_id = m.member_rank_id AND r.rank_hide = 0
			AND m.member_status = 1
			AND m.member_level >= ".  intval($config['bbdkp_minrosterlvl']) . "
			AND m.member_rank_id != 99
			AND m.game_id = '" . $db->sql_escape($this->game_id) . "'
			AND e1.attribute_id = e.race_id AND e1.language= '" . $config['bbdkp_lang'] . "'
			AND e1.attribute = 'race' and e1.game_id = e.game_id";
		
		// filters
		if($guild_id > 0)
		{
			$sql_array['WHERE'] .= " AND m.member_guild_id =  " . $guild_id;  
		}
		
		if($class_id > 0)
		{
			$sql_array['WHERE'] .= " m.member_class_id =  " . $class_id;  
		}
		
		if($race_id > 0)
		{
			$sql_array['WHERE'] .= " m.member_race_id =  " . $race_id;
		}

		if($level1 > 0)
		{
			$sql_array['WHERE'] .= " m.member_level >=  " . $level1;
		}

		if($level2 != 200)
		{
			$sql_array['WHERE'] .= " m.member_level <=  " . $level2;
		}
		
		// order
		$sort_order = array(
				0 => array('m.member_name', 'm.member_name desc'),
				1 => array('m.game_id', 'm.member_name desc'),
				2 => array('m.member_class_id', 'm.member_class_id desc'),
				3 => array('m.member_rank_id', 'm.member_rank_id desc'),
				4 => array('m.member_level', 'm.member_level  desc'),
				5 => array('u.username', 'u.username desc'),
				6 => array('m.member_achiev', 'm.member_achiev  desc')
		);
	
		$current_order = $this->switch_order($sort_order);
	
		if( $mode =='class')
		{
			$sql_array['ORDER_BY']  = "m.member_class_id, " . $current_order['sql'];
		}
		elseif ($mode =='listing')
		{
			$sql_array['ORDER_BY']  = $current_order['sql'];
		}
	
		$sql = $db->sql_build_query('SELECT', $sql_array);
	
		if ($mode =='listing')
		{
			// LISTING MODE
			$member_count=0;
			
			// get membercount in selection
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$member_count++;
			}
	
			//now get wanted window
			$result = $db->sql_query_limit ( $sql, $config ['bbdkp_user_llimit'], $start );
			$dataset = $db->sql_fetchrowset($result);
		}
		else
		{
			$result = $db->sql_query($sql);
			$dataset = $db->sql_fetchrowset($result);
			$member_count = count($dataset);
		}
	
		$db->sql_freeresult($result);
		
		return array($member_count, $dataset, $current_order); 
	}
	
	/**
	 * Frontview : gets all classes in roster selection
	 * required class game property to be set before call
	 * 
	 * @param int $guild_id optional guild id
	 * @param int $classid optional class id
	 * @param int $race optional race id
	 * @param int $level1 optional level1 (default 1)
	 * @param int $level2 optional level2 (default 200)
	 * @return array
	 * 
	 */
	public function get_classes($guild_id = 0, $classid = 0, $race_id = 0, $level1=0, $level2=200)
	{
		global $db, $config;
		$sql_array = array(
				'SELECT'    => 'c.class_id, c1.name as class_name, c.imagename, c.colorcode' ,
				'FROM'      => array(
						MEMBER_LIST_TABLE    =>  'm',
						CLASS_TABLE          =>  'c',
						BB_LANGUAGE			=>  'c1',
						MEMBER_RANKS_TABLE   =>  'r',
				),
				'WHERE'     => " c.class_id = m.member_class_id
    							AND c.game_id = m.game_id
    							AND r.guild_id = m.member_guild_id
    							AND r.rank_id = m.member_rank_id AND r.rank_hide = 0
    							AND c1.attribute_id =  c.class_id AND c1.language= '" . $config['bbdkp_lang'] . "' AND c1.attribute = 'class'
    							AND (c.game_id = '" . $db->sql_escape($this->game_id) . "')
    							AND c1.game_id=c.game_id
	
    							",
				'ORDER_BY'  =>  'c1.name asc', 
				'GROUP_BY'  =>  'c.class_id, c1.name, c.imagename, c.colorcode'  
		);
		
		// filters
		if($guild_id > 0)
		{
			$sql_array['WHERE'] .= " AND m.member_guild_id =  " . $guild_id;
		}
		
		if($classid > 0)
		{
			$sql_array['WHERE'] .= " m.member_class_id =  " . $classid;
		}
		
		if($race_id > 0)
		{
			$sql_array['WHERE'] .= " m.member_race_id =  " . $race_id;
		}
		
		if($level1 > 0)
		{
			$sql_array['WHERE'] .= " m.member_level >=  " . $level1;
		}
		
		if($level2 != 200)
		{
			$sql_array['WHERE'] .= " m.member_level <=  " . $level2;
		}
		
		
		
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$dataset = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
		unset ($result); 
		return $dataset; 
	}
	
	
	
	
	


}

?>