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
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	public function __construct(\phpbb\template\template $template, \phpbb\db\driver\driver_interface $db)
	{
		$this->template = $template;
		$this->db = $db;
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

		while ($row = $this->db->sql_fetchrow($result))
		{

			$row['user_colour'] = (!empty($row['user_colour'])) ? $row['user_colour'] : '000000';
			$style_bold		 = ($row['user_colour'] == '000000') ? 'normal' : 'bold';

			$this->template->assign_block_vars('username_bb', array(
				'USERNAME_BB_BOLD'	=> $style_bold,
				'USERNAME_BB_VALUE' => '|#' . $row['user_colour'] . '|' . $style_bold . '|'. $row['user_id'],
				'USERNAME_BB_USER'	=> $row['username']
				)
			);
		}
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
