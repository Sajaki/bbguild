<?php
/**
 * bbdkp view class
 * standings and ladders block
 * 
 * @package bbDKP
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 */

/**
 * @ignore
 */
if ( !defined('IN_PHPBB') OR !defined('IN_BBDKP') )
{
	exit;
}

$startd = request_var ( 'startdkp', 0 );
$classes = array ();
if(count($classarray)==0)
{
	$template->assign_vars ( array (
			'S_SHOWLEAD' => false,
	));
}

if (!class_exists('\bbdkp\PointsController'))
{
	require("{$phpbb_root_path}includes/bbdkp/Points/PointsController.$phpEx");
}
$memberpoints = new \bbdkp\PointsController;

$memberarray = $memberpoints->get_standings($this->guild_id, $this->dkpsys_id, $this->game_id, $startd, $this->show_all, 
		$this->query_by_armor, $this->query_by_class, $this->filter, $this->query_by_pool);

// loop sorted member array and dump to template
foreach ( $memberarray as $key => $member )
{
	$u_rank_search = append_sid ( "{$phpbb_root_path}dkp.$phpEx" , 'page=standings&amp;rank=' . urlencode ( $member ['rank_name'] ) .'&amp;guild_id=' . $this->guild_id) ;
	
	// append inactive switch
	$u_rank_search .= (($config ['bbdkp_hide_inactive'] == 1) && (! $this->show_all)) ? '&amp;show=' : '&amp;show=all';
	
	// append armor or class filter
	$u_rank_search .= ($this->filter != $user->lang['ALL']) ? '&amp;filter=' . $this->filter : '';
	
	$templatearray = array (
		'COLORCODE' 	=> $member ['colorcode'],
		'CLASS_IMAGE' 	=> $member['class_image'],
		'S_CLASS_IMAGE_EXISTS' =>  $member['class_image_exists'],
		'RACE_IMAGE' 	=> $member['race_image'],
		'S_RACE_IMAGE_EXISTS' =>  $member['race_image_exists'],
		'DKPCOLOUR1' 	=> ($member ['member_adjustment'] >= 0) ? 'positive' : 'negative', 
		'DKPCOLOUR2' 	=> ($member ['member_current'] >= 0) ? 'positive' : 'negative', 
		'ID' 			=> $member ['member_id'], 
		'COUNT' 		=> $member ['count'], 
		'NAME' 			=> $member ['rank_prefix'] . (($member ['member_status'] == '0') ? 
			'<em>' . $member ['member_name'] . '</em>' : 
			$member ['member_name']) . $member ['rank_suffix'], 
		'RANK_NAME' => $member ['rank_name'],
		'RANK_HIDE' => $member ['rank_hide'],
		'RANK_SEARCH' => $u_rank_search, 
		'LEVEL' => ($member ['member_level'] > 0) ? $member ['member_level'] : '&nbsp;', 
		'CLASS' => (! empty ( $member ['member_class'] )) ? $member ['member_class'] : '&nbsp;', 
		'ARMOR' => (! empty ( $member ['armor_type'] )) ? $member ['armor_type'] : '&nbsp;', 
		'EARNED' => $member ['member_earned'], 
		'ADJUSTMENT' => $member ['member_adjustment'], 
		'SPENT' => $member ['member_spent'],
		'CURRENT' => $member ['member_current'], 
		'LASTRAID' => (! empty ( $member ['member_lastraid'] )) ? 
			date ( 'd.m.y', $member ['member_lastraid'] ) : 
			'&nbsp;', 
		'RAIDS_P1_DAYS' => $member ['attendanceP1'], 
		'U_VIEW_MEMBER' => append_sid ( "{$phpbb_root_path}dkp.$phpEx",
			'page=viewmember' .  
			'&amp;' . URI_NAMEID . '=' . $member ['member_id'] . 
			'&amp;' . URI_DKPSYS . '=' . $this->dkpsys_id), 
	);
		
		if($config['bbdkp_timebased'] == 1)
		{
			$templatearray['TIMEBONUS'] = $member ['member_time_bonus'];
		}
		if($config['bbdkp_zerosum'] == 1)
		{
			$templatearray['ZEROSUM'] = $member ['member_zerosum_bonus'];
		}
				
		if($config['bbdkp_decay'] == 1)
		{
			$templatearray['RAIDDECAY'] = $member ['member_raid_decay'];
			$templatearray['ITEMDECAY'] = $member ['member_item_decay'];
		}
		
		if($config['bbdkp_epgp'] == 1)
		{
			$templatearray['EP'] = $member ['ep'];
			$templatearray['GP'] = $member ['gp'];
			$templatearray['PR'] = $member ['pr'];
		}
		
		$template->assign_block_vars ( 'members_row', $templatearray);
		
}

//leaderboard
foreach ($classarray as $k => $class)
{
	$template->assign_block_vars ( 'class',
			array (
					'CLASSNAME' 	=> $class ['class_name'],
					'CLASSIMGPATH'	=> (strlen($class['imagename']) > 1) ? $class['imagename'] . ".png" : '',
					'COLORCODE' 	=> $class['colorcode']
			)
	);

	foreach ($memberarray as  $member)
	{
		if($member['class_id'] == $class['class_id'] && $member['game_id'] == $class['game_id'])
		{
			//dkp data per class
			$dkprowarray= array (
					'NAME' => ($member ['member_status'] == '0') ? '<em>' . $member ['member_name'] . '</em>' : $member ['member_name'] ,
					'CURRENT' => $member ['member_current'],
					'DKPCOLOUR' => ($member ['member_current'] >= 0) ? 'positive' : 'negative',
					'U_VIEW_MEMBER' => append_sid ( "{$phpbb_root_path}dkp.$phpEx", 'page=viewmember&amp;'.
					URI_NAMEID . '=' . $member ['member_id'] . '&amp;' .
					URI_DKPSYS . '=' . $this->dkpsys_id ) );

			if($config['bbdkp_epgp'] == 1)
			{
				$dkprowarray[ 'PR'] = $member ['pr'] ;
			}

			$template->assign_block_vars ( 'class.dkp_row', $dkprowarray );
		}

	}

	$template->assign_vars ( array (
			'S_SHOWLEAD' => true,
	));
}

// Added to the end of the sort links
$uri_addon = '';
$uri_addon .= '&amp;filter=' . urlencode ( $this->filter );
$uri_addon .= (isset ( $_GET ['show'] )) ? '&amp;show=' . request_var ( 'show', '' ) : '';

/* sorting links */
$sortlink = array ();
for($i = 1; $i <= 20; $i ++)
{
	if (isset ( $sortorder ) && $sortorder == $i)
	{
		$j = - $i;
	} 
	else
	{
		$j = $i;
	}
	
	{
		if ($this->query_by_pool)
		{
			$sortlink [$i] = append_sid ( "{$phpbb_root_path}dkp.$phpEx", 'page=standings&amp;' . URI_ORDER. '=' . $j . $uri_addon .
			 '&amp;' . URI_DKPSYS . '=' . $this->dkpsys_id . '&amp;guild_id=' . $this->guild_id );
		} 
		else
		{
			$sortlink [$i] = append_sid ( "{$phpbb_root_path}dkp.$phpEx", 'page=standings&amp;' . URI_ORDER. '=' . $j . $uri_addon .
			 '&amp;' . URI_DKPSYS . '=' . $user->lang['ALL'] . '&amp;guild_id=' . $this->guild_id);
		}
	}

}

$allmember_count = 0; 
// calculate pagination
$sortorder = request_var ( URI_ORDER, 0 );

$arg='';
if ($this->query_by_pool)
{
	$arg = '&amp;' . URI_DKPSYS. '=' . $this->dkpsys_id;
}

if($this->filter != '')
{
	$arg .= '&amp;filter=' . $this->filter;
}
else
{
	$arg .= '&amp;filter=all';
}
$u_listmembers = append_sid ( "{$phpbb_root_path}dkp.$phpEx", 'page=standings' . $arg . '&amp;guild_id=' . $this->guild_id );

$dkppagination = $this->generate_pagination2($u_listmembers . '&amp;o=' . $sortorder , 
$allmember_count , $config ['bbdkp_user_llimit'], $startd, true, 'startdkp'  );

// footcount link
if (($config ['bbdkp_hide_inactive'] == 1) && (! $this->show_all))
{
	$flink = '<a href="' . append_sid ( "{$phpbb_root_path}dkp.$phpEx", 
		'page=standings' .	
		'&amp;' . URI_ORDER . '=' . $j . '&amp;show=' . $user->lang['ALL'] . '&amp;guild_id=' . $this->guild_id . 
		'&amp;' . URI_DKPSYS . '=' . $this->dkpsys_id ) . '" class="rowfoot">';
	$footcount_text = sprintf ( $user->lang ['LISTMEMBERS_ACTIVE_FOOTCOUNT'], count($memberarray) , $flink );
} 
else
{
	$footcount_text = sprintf ( $user->lang ['LISTMEMBERS_FOOTCOUNT'], count($memberarray) );
}

$template->assign_vars ( array (
	'F_MEMBERS' => $u_listmembers, 
	'O_NAME' => $sortlink [1], 
	'O_RANK' => $sortlink [2], 
	'O_LEVEL' => $sortlink [3], 
	'O_CLASS' => $sortlink [4], 
	'O_ARMOR' => $sortlink [5], 
	'O_MEMBER_RAID_VALUE' => $sortlink [6], 
	'O_EARNED' => $sortlink [9],
	'O_ADJUSTMENT' => $sortlink [10], 
	'O_SPENT' => $sortlink [13],
	'O_CURRENT' => $sortlink [17],
	'O_LASTRAID' => $sortlink [18], 
	'O_RAIDS_P1_DAYS' => $sortlink [19], 
	'O_RAIDS_P2_DAYS' => $sortlink [20], 
	'RAIDS_P1_DAYS' => sprintf ( $user->lang ['RAIDS_X_DAYS'], $config ['bbdkp_list_p1'] ), 
	'S_SHOWZS' 		=> ($config['bbdkp_zerosum'] == '1') ? true : false, 
	'S_SHOWDECAY' 	=> ($config['bbdkp_decay'] == '1') ? true : false,
	'S_SHOWEPGP' 	=> ($config['bbdkp_epgp'] == '1') ? true : false,
 	'S_SHOWTIME' 	=> ($config['bbdkp_timebased'] == '1') ? true : false,
	'S_QUERYBYPOOL' => $this->query_by_pool, 
	'S_DISPLAY_STANDINGS' => true,
	'DKPPAGINATION' 	=> $dkppagination ,
	'FOOTCOUNT' => (isset ( $_POST ['compare'] )) ? 
		sprintf ( $footcount_text, sizeof (request_var ( 'compare_ids', array ('' => 0 )))) : 
		$footcount_text, 
));

 
if($config['bbdkp_timebased'] == 1) 
{
	$template->assign_var('O_DKP_HOUR', $sortlink [7]);
	
}

if($config['bbdkp_zerosum'] == 1)
{
	$template->assign_var('O_ZEROSUM_BONUS', $sortlink [8]);
}

if($config['bbdkp_decay'] == 1)
{
	$template->assign_vars ( array (
	'O_EPDECAY' => $sortlink [11],
	'O_ITEMDECAY' => $sortlink [14]
	));
}

if($config['bbdkp_epgp'] == 1)
{
	$template->assign_vars ( array (
	'O_EP' => $sortlink [12],
	'O_GP' => $sortlink [15],
	'O_PR' => $sortlink [16],
	));
	
}

// Output page
page_header ( $user->lang ['LISTMEMBERS_TITLE'] );
?>