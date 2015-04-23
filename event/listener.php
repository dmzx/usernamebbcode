<?php
/**
* @package username Extension - Username BBCode
* @copyright (c) 2015 dmzx - http://dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*/

namespace dmzx\username\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\template\template */
	protected $template;
	/** @var \phpbb\user */
	protected $user;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	protected $phpbb_root_path;

	protected $phpEx;
	/** @var string */
	protected $table_prefix;
	/** @var \phpbb\controller\helper */
	protected $controller_helper;
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\controller\helper $controller_helper, \phpbb\template\template $template, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, $root_path, $phpEx, $table_prefix)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->template = $template;
		$this->controller_helper = $controller_helper;
		$this->user = $user;
		$this->db = $db;
		$this->root_path = $root_path;
		$this->phpEx = $phpEx;
		$this->table_prefix = $table_prefix;
	}

	static public function getSubscribedEvents()
	{

		return array(
			'core.user_setup' => 'load_language_on_setup',
			'core.modify_posting_auth' => 'modify_posting_auth',
		);
	}

	public function modify_posting_auth($event)
	{

			$sql = "SELECT user_id, username, user_colour
			 FROM " . USERS_TABLE . "
			 WHERE user_type IN (" . USER_FOUNDER . ', ' . USER_NORMAL . ")
			 ORDER BY username_clean";
			$result	= $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result)){

			$row['user_colour'] = (!empty($row['user_colour'])) ? $row['user_colour'] : '000000';
			$style_bold		 = ($row['user_colour'] == '000000') ? 'normal' : 'bold';

			$this->template->assign_block_vars('username_bb', array(
			'USERNAME_BB_BOLD'	=> $style_bold,
			'USERNAME_BB_VALUE' => '|#' . $row['user_colour'] . '|' . $style_bold . '|'. $row['user_id'],
			'USERNAME_BB_USER'	=> $row['username']));}
			$this->db->sql_freeresult($result);

	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'dmzx/username',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}
}