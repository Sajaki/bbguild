<?php
/**
* This class manages Bossprogress 
*  
* @package bbDkp.acp
* @copyright (c) 2009 bbdkp http://code.google.com/p/bbdkp/
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* $Id$
* 
* Powered by bbdkp © 2009 The bbDkp Project Team
* If you use this software and find it to be useful, we ask that you
* retain the copyright notice below.  While not required for free use,
* it will help build interest in the bbDkp project.
* 
* Thanks for sz3 for Bossprogress.
* Integrated by: ippeh
*  
**/


/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
if (! defined('EMED_BBDKP')) 
{
	$user->add_lang ( array ('mods/dkp_admin' ));
	trigger_error ( $user->lang['BBDKPDISABLED'] , E_USER_WARNING );
}

class acp_dkp_bossprogress extends bbDkp_Admin
{
	var $u_action;
	
	function main($id, $mode) 
	{
	    global $db, $user, $template, $config, $phpEx;   
        $user->add_lang(array('mods/dkp_admin'));   
		$link = '<br /><a href="'.append_sid("index.$phpEx", "i=dkp_bossprogress&amp;mode=bossprogress") . 
			'"><h3>'.$user->lang['RETURN_DKPINDEX'].'</h3></a>';
		
        switch ($mode)
		{
			case 'bossbase':
    			$this->page_title =  $user->lang['bossbase'] . ' - '. $user->lang['bb_am_conf'];
    			$this->tpl_name = 'dkp/acp_'. $mode;
				break;	
			
			case 'bossbase_offset':
    			$this->page_title =  $user->lang['bossbase'] . ' - '. $user->lang['bb_am_offs'];
    			$this->tpl_name = 'dkp/acp_'. $mode;
				break;	
		
			case 'bossprogress':	
				// page layout
				$submit = (isset($_POST['bpsave'])) ? true : false;
				
				// Saving
				if ($submit)
				{
					// global config
				  	set_config ('bbdkp_bp_hidenewzone',  ( isset($_POST['hidenewzone']) ) ? 1 : 0);
				  	set_config ('bbdkp_bp_hidenonkilled', ( isset($_POST['hidenewboss']) ) ? 1 : 0 );
				  	set_config ('bbdkp_bp_zonephoto',  request_var('headertype', 0), 0); 				  	
				  	set_config ('bbdkp_bp_zoneprogress', ( isset($_POST['showzone']) ) ? 1 : 0);
				  	set_config ('bbdkp_bp_zonestyle',  request_var('style', 0));
//					foreach ( $bzone as $zoneid => $bosslist )
//					{
//						$this->bossprogress_update_config ( 'sz_' . $zoneid, (isset ( $_POST ['sz_' . $zoneid] )) ? '1' : '0' );
//					}
					trigger_error($user->lang['BP_SAVED'] . $link, E_USER_NOTICE);
				}
				
				$bp_styles['0'] = $user->lang['BP_STYLE_BP'];
				$bp_styles['1'] = $user->lang['BP_STYLE_BPS'];
				$bp_styles['2'] = $user->lang['BP_STYLE_RP3R'];
				foreach ($bp_styles as $value => $option) 
				{
					$template->assign_block_vars('style_row', array (
							'VALUE' => $value,
							'SELECTED' => ($config['bbdkp_bp_zonestyle'] == $value) ? ' selected="selected"' : '',
							'OPTION' => $option
						)
					);
				}
				
				$arrvals = array (
					'F_CONFIG' 			 => append_sid("index.$phpEx", "i=dkp_bossprogress&amp;mode=bossprogress"),
					'BP_HIDENEWZONE'	 => ($config['bbdkp_bp_hidenewzone'] == 1) ? ' checked="checked"' : '',
					'BP_HIDENONKIBOSS' 	 => ($config['bbdkp_bp_hidenonkilled'] == 1) ? ' checked="checked"' : '',
					'BP_SHOWSB' 		 => ($config['bbdkp_bp_zoneprogress'] == 1) ? ' checked="checked"' : '',
					'HEADER_SEL_SEPIA'   => ($config['bbdkp_bp_zonephoto'] == 0 ) ? ' selected="selected"' : '',
					'HEADER_SEL_BLUE'    => ($config['bbdkp_bp_zonephoto'] == 1 ) ? ' selected="selected"' : '',
					'HEADER_SEL_NONE'    => ($config['bbdkp_bp_zonephoto'] == 2 ) ? ' selected="selected"' : '',
				);
				$template->assign_vars($arrvals);
				
				$sql = "select zonename, showzone, imagename from " . ZONEBASE ;
				$result = $db->sql_query($sql);
                $row = $db->sql_fetchrow($result); 
                while ( $row = $db->sql_fetchrow($result) )
                {
                    $template->assign_block_vars('gamezone', array(
                    'ZONE_NAME' => $user->lang['SHOWZONE'] . $row['zonename']  ,
                    'BOSS_SHOWN' => $row['showzone'],
                    'BOSS_CHK'   => ($row['showzone'] == 1) ? ' checked="checked"' : '',
                    ));
                }
                $db->sql_freeresult($result);

				
				
				$this->tpl_name = 'dkp/acp_'. $mode;
				break;		
		}
				
								
	}

}
?>
