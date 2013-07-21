<?php
/**
 * @package bbDKP
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 *
 */
namespace bbdkp;
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;
require_once ("{$phpbb_root_path}includes/bbdkp/iAdmin.$phpEx");

if (!class_exists('\bbdkp\Game'))
{
	require("{$phpbb_root_path}includes/bbdkp/games/Game.$phpEx");
}

if (!class_exists('\bbdkp\log'))
{
	require("{$phpbb_root_path}includes/bbdkp/log.$phpEx");
}

/**
 * 
 * bbDKP Admin foundation
 * @package bbDKP
 */
class Admin implements \bbdkp\iAdmin
{
    public $time = 0;
    public $bbtips = false;
    public $games;
    public $installed_games;
    public $regions;
    public $languagecodes;
    
    /**
     * where versionstring is stored
     * @var unknown_type
     */
    protected $versioncheckurl = array(
     'bbdkp' 				=> 'https://raw.github.com/Sajaki/bbDKP/v130/contrib/version.txt', 
 	 'bbdkp_apply' 			=> 'bbdkp.googlecode.com/svn/trunk/version_apply.txt',  
     'bbdkp_plugin_bbtips' 	=> 'bbdkp.googlecode.com/svn/trunk/version_bbtips.txt', 
     'bbdkp_bp' 			=>  'bbdkp.googlecode.com/svn/trunk/version_bossprogress.txt', 
     'bbdkp_raidplanner' 	=>  'bbdkp.googlecode.com/svn/trunk/version_raidplanner.txt', 
		); 
    
    
	public function __construct()
	{
		global $user, $phpbb_root_path, $phpEx, $config, $user;

		$user->add_lang ( array ('mods/dkp_admin' ) );
		$user->add_lang ( array ('mods/dkp_common' ) );
		
		if(!defined("EMED_BBDKP"))
		{
			trigger_error ( $user->lang['BBDKPDISABLED'] , E_USER_WARNING );
		}
				
		// Check for required extensions
		if (!function_exists('curl_init'))
		{
			trigger_error($user->lang['CURL_REQUIRED'], E_USER_WARNING);
		
		}
		
		if (!function_exists('json_decode'))
		{
			trigger_error($user->lang['JSON_REQUIRED'], E_USER_WARNING);
		}
		
		$this->regions = array(
				'eu' => $user->lang['REGIONEU'],
				'us' => $user->lang['REGIONUS'],
				'tw' => $user->lang['REGIONTW'],
				'kr' => $user->lang['REGIONKR'],
				'cn' => $user->lang['REGIONCN'],
				'sea' => $user->lang['REGIONSEA'],
				);
		
		$this->languagecodes = array(
				'de' => $user->lang['LANG_DE'] ,
				'en' => $user->lang['LANG_EN'] ,
				'fr' => $user->lang['LANG_FR']);
				
	    $games = new \bbdkp\Game(); 
	    $this->games = $games->preinstalled_games; 
	    $this->installed_games = $games->installed_games;
	    unset($games); 
	    $boardtime = array();
	    $boardtime = getdate(time() + $user->timezone + $user->dst - date('Z'));
	    $this->time = $boardtime[0];

	    if (isset($config['bbdkp_plugin_bbtips_version']))
	    {
	    	//check if config value and parser file exist.
	    	if($config['bbdkp_plugin_bbtips_version'] >= '0.3.1' && file_exists($phpbb_root_path. 'includes/bbdkp/bbtips/parse.' . $phpEx))
	    	{
	    		$this->bbtips = true;
	    	}
	    }
	    
	   

	}

    /**
	 * creates a unique key, used as adjustments, import, items and raid identifier
	 *
	 * @param $part1
	 * @param $part2
 	 * @param $part3
 	 *
 	 * @return $group_key
	 */
    public function gen_group_key($part1, $part2, $part3)
    {
        // Get the first 10-11 digits of each md5 hash
        $part1 = substr(md5($part1), 0, 10);
        $part2 = substr(md5($part2), 0, 11);
        $part3 = substr(md5($part3), 0, 11);

        // Group the hashes together and create a new hash based on uniqid()
        $group_key = $part1 . $part2 . $part3;
        $group_key = md5(uniqid($group_key));

        return $group_key;
    }

    /**
	 * connects to remote site and gets xml or html using Curl
	 * @param char $url
	 * @param bool $return_Server_Response_Header default false
	 * @param bool $loud default false
	 * @param bool $json default false
	 * @return array response
     */
  	public function curl($url, $return_Server_Response_Header = false, $loud= false, $json=true)
	{
		
		global $user; 
		
		if ( function_exists ( 'curl_init' ))
		{
			 /* Create a CURL handle. */
			if (($curl = curl_init($url)) === false)
			{
				trigger_error('curl_init Failed' , E_USER_WARNING);
			}
			
			// set options
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $url, 
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:21.0) Gecko/20100101 Firefox/21.0', 
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_SSL_VERIFYPEER => false, 
				CURLOPT_TIMEOUT => 60, 
				CURLOPT_VERBOSE => false, 
				CURLOPT_HEADER => false, 
			));
			
			//@todo : setup authentication keys
			
			// Execute
			$response = curl_exec($curl);
			$headers = curl_getinfo($curl); 
			$error = 0;
			
			$data = array(
					'response'		    => $json ? json_decode($response, true) : $response,
					'response_headers'  => (array) $headers,
					'error'				=> '',
			);
			
			//errorhandler
			if (!$response)
			{
				$error = curl_errno ($curl);
				/*
				 CURLE_OK = 0,
				CURLE_UNSUPPORTED_PROTOCOL,     1
				CURLE_FAILED_INIT,              2
				CURLE_URL_MALFORMAT,            3
				CURLE_URL_MALFORMAT_USER,       4 - NOT USED
				CURLE_COULDNT_RESOLVE_PROXY,    5
				CURLE_COULDNT_RESOLVE_HOST,     6
				CURLE_COULDNT_CONNECT,          7
				CURLE_FTP_WEIRD_SERVER_REPLY,   8
				*/
				switch ($error)
				{
					case "28" :
						$data['error'] = 'cURL error :' . $url . ": No response after 30 second timeout : err " . $error . "  ";
						break;
					case "1" :
						$data['error'] = 'cURL error :' . $url . " : error " . $error . " : UNSUPPORTED_PROTOCOL ";
						break;
					case "2" :
						$data['error'] = 'cURL error :' . $url . " : error " . $error . " : FAILED_INIT ";
						break;
					case "3" :
						$data['error'] = 'cURL error :' . $url . " : error " . $error . " : URL_MALFORMAT ";
						break;
					case "5" :
						$data['error'] = 'cURL error :' . $url . " : error " . $error . " : COULDNT_RESOLVE_PROXY ";
						break;
					case "6" :
						$data['error'] = 'cURL error :' . $url . " : error " . $error . " : COULDNT_RESOLVE_HOST ";
						break;
					case "7" :
						$data['error'] = 'cURL error :' . $url . " : error " . $error . " : COULDNT_CONNECT ";
				}
			}

			
			
			if (isset($data['response_headers']['http_code']))
			{
				switch ($data['response_headers']['http_code'] )
				{
					case 400:
						$data['error'] .= $user->lang['ERR400'] . ': ' . $data['response']['reason'];
						break;
					case 401:
						$data['error'] .= $user->lang['ERR401'] . ': ' . $data['response']['reason'];
						break;
					case 403:
						$data['error'] .= $user->lang['ERR403'] . ': ' . $data['response']['reason'];
						break;
					case 404:
						$data['error'] .= $user->lang['ERR404'] . ': ' . $data['response']['reason'];
						break;
					case 500:
						$data['error'] .= $user->lang['ERR500'] . ': ' . $data['response']['reason'];
						break;
					case 501:
						$data['error'] .= $user->lang['ERR501'] . ': ' . $data['response']['reason'];
						break;
					case 502:
						$data['error'] .= $user->lang['ERR502'] . ': ' . $data['response']['reason'];
						break;
					case 503:
						$data['error'] .= $user->lang['ERR503'] . ': ' . $data['response']['reason'];
						break;
					case 504:
						$data['error'] .= $user->lang['ERR504'] . ': ' . $data['response']['reason'];
						break;
				}
			}
				
			//close conection
			curl_close ($curl);
		}
		
		//report errors?
		if ($data['error'] != 0)
		{
			if($loud == true)
			{
				trigger_error($data['error'], E_USER_WARNING);
			}
	        return false;
		}
		else
		{
			return $data['response'];
		}

	}



	/**
	 * Pagination function altered from functions.php used in viewmember.php because we need two linked paginations
	 *
	 * Pagination routine, generates page number sequence
	 * tpl_prefix is for using different pagination blocks at one page
	 */
	public function generate_pagination2($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = true, $tpl_prefix = '')
	{
		global $template, $user;

		// Make sure $per_page is a valid value
		$per_page = ($per_page <= 0) ? 1 : $per_page;
		$total_pages = ceil($num_items / $per_page);

		$seperator = '<span class="page-sep">' . $user->lang['COMMA_SEPARATOR'] . '</span>';

		if ($total_pages == 1 || !$num_items)
		{
			return false;
		}

		$on_page = floor($start_item / $per_page) + 1;
		$url_delim = (strpos($base_url, '?') === false) ? '?' : '&amp;';

		$page_string = ($on_page == 1) ? '<strong>1</strong>' : '<a href="' . $base_url . '">1</a>';

		if ($total_pages > 5)
		{
			$start_cnt = min(max(1, $on_page - 4), $total_pages - 5);
			$end_cnt = max(min($total_pages, $on_page + 4), 6);

			$page_string .= ($start_cnt > 1) ? ' ... ' : $seperator;

			for ($i = $start_cnt + 1; $i < $end_cnt; $i++)
			{
				$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . $base_url . "{$url_delim}" . $tpl_prefix  . "=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
				if ($i < $end_cnt - 1)
				{
					$page_string .= $seperator;
				}
			}

			$page_string .= ($end_cnt < $total_pages) ? ' ... ' : $seperator;
		}
		else
		{
			$page_string .= $seperator;

			for ($i = 2; $i < $total_pages; $i++)
			{
				$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . $base_url . "{$url_delim}" . $tpl_prefix  . "=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
				if ($i < $total_pages)
				{
					$page_string .= $seperator;
				}
			}
		}

		$page_string .= ($on_page == $total_pages) ? '<strong>' . $total_pages . '</strong>' : '<a href="' . $base_url . "{$url_delim}" . $tpl_prefix  . "=" . (($total_pages - 1) * $per_page) . '">' . $total_pages . '</a>';
		if ($add_prevnext_text)
		{
			if ($on_page != 1)
			{
				$page_string = '<a href="' . $base_url . "{$url_delim}" . $tpl_prefix  . "=" . (($on_page - 2) * $per_page) . '">' . $user->lang['PREVIOUS'] . '</a>&nbsp;&nbsp;' . $page_string;
			}

			if ($on_page != $total_pages)
			{
				$page_string .= '&nbsp;&nbsp;<a href="' . $base_url . "{$url_delim}" . $tpl_prefix  . "=" . ($on_page * $per_page) . '">' . $user->lang['NEXT'] . '</a>';
			}
		}

		$template->assign_vars(array(
				$tpl_prefix . 'BASE_URL'		=> $base_url,
				'A_' . $tpl_prefix . 'BASE_URL'	=> addslashes($base_url),
				$tpl_prefix . 'PER_PAGE'		=> $per_page,

				$tpl_prefix . 'PREVIOUS_PAGE'	=> ($on_page == 1) ? '' : $base_url . "{$url_delim}" . $tpl_prefix  . "=" . (($on_page - 2) * $per_page),
				$tpl_prefix . 'NEXT_PAGE'		=> ($on_page == $total_pages) ? '' : $base_url . "{$url_delim}" . $tpl_prefix  . "=" . ($on_page * $per_page),
				$tpl_prefix . 'TOTAL_PAGES'		=> $total_pages,
		));

		return $page_string;
	}

	/*
	 * Switches the sorting order of a supplied array, prerserving key values
	* The array is in the format [number][0/1] (0 = the default, 1 = the opposite)
	* Returns an array containing the code to use in an SQL query and the code to
	* use to pass the sort value through the URI.  URI is in the format
	* (number).(0/1)
	*
	* checks that the 2nd element is either 0 or 1
	* @param $sort_order Sorting order array
	* @param $arg header variable
	* @return array SQL/URI information
	*/
	public function switch_order($sort_order, $arg = URI_ORDER)
	{
		$uri_order = ( isset($_GET[$arg]) ) ? request_var($arg, 0.0) : '0.0';

		$uri_order = explode('.', $uri_order);

		$element1 = ( isset($uri_order[0]) ) ? $uri_order[0] : 0;
		$element2 = ( isset($uri_order[1]) ) ? $uri_order[1] : 0;
		// check if correct input
		if ( $element2 != 1 )
		{
			$element2 = 0;
		}

		foreach($sort_order as $key => $value )
		{
			if ( $element1 == $key )
			{
				$uri_element2 = ( $element2 == 0 ) ? 1 : 0;
			}
			else
			{
				$uri_element2 = 0;
			}
			$current_order['uri'][$key] = $key . '.' . $uri_element2;
		}

		$current_order['uri']['current'] = $element1.'.'.$element2;
		$current_order['sql'] = $sort_order[$element1][$element2];

		return $current_order;
	}


	/**
	 * Create a bar graph
	 *
	 * @param $width
	 * @param $show_number Show number in middle of bar?
	 * @param $class Background class for bar
	 * @return string Bar HTML
	 */
	public function create_bar($width, $show_text = '', $color = '#AA0033')
	{
		$bar = '';

		if ( strstr($width, '%') )
		{
			$width = intval(str_replace('%', '', $width));
			if ( $width > 0 )
			{
				$width = ( intval($width) <= 100 ) ? $width . '%' : '100%';
			}
		}

		if ( $width > 0 )
		{
			$bar = '<table width="' . $width . '" border="0" cellpadding="0" cellspacing="0">';
			$bar .= '<tr><td style="text-align:left; background-color:' . $color .'; width: 100%; white-space: nowrap"  >';

			if ( $show_text != '' )
			{
				$bar .= '<span style="color:#EEEEEE" class="small">' . $show_text . '</span>';
			}

			$bar .= '</td></tr></table>';
		}

		return $bar;
	}
	
	
	/**
	 * makes an entry in the bbdkp log table
	 * log_action is an xml containing the log
	 *
	 * log_id	int(11)		UNSIGNED	No		auto_increment
	 * log_date	int(11)			No	0
	 * log_type	varchar(255)	utf8_bin		No
	 * log_action	text	utf8_bin		No
	 * log_ipaddress	varchar(15)	utf8_bin		No
	 * log_sid	varchar(32)	utf8_bin		No
	 * log_result	varchar(255)	utf8_bin		No
	 * log_userid	mediumint(8)	UNSIGNED	No	0
	 */
	public function log_insert($values = array())
	{
		// log
		$logs = \bbdkp\log::Instance();	
		return $logs->log_insert($values = array()); 
		
	}

}

?>
