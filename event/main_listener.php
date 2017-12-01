<?php
/**
 *
 * @package phpBB Extension - Mafiascum ISOS and Activity Monitor
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace mafiascum\automodServer\event;
/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{
    /* @var \phpbb\controller\helper */
    protected $helper;
    /* @var \phpbb\template\template */
    protected $template;
    /* @var \phpbb\request\request */
    protected $request;
    /* @var \phpbb\db\driver\driver */
    protected $db;

    static public function getSubscribedEvents() {
        return array(
            'core.user_setup'  => 'load_language_on_setup',
            'core.viewtopic_assign_template_vars_before' => 'inject_template_vars',
        );
    }

    /**
     * Constructor
     *
     * @param \phpbb\controller\helper  $helper     Controller helper object
     * @param \phpbb\template\template  $template   Template object
     * @param \phpbb\request\request    $request    Request object
     */
    public function __construct(
    		\phpbb\controller\helper $helper,
    		\phpbb\template\template $template,
    		\phpbb\request\request $request,
    		\phpbb\db\driver\driver_interface $db,
    		\phpbb\user $user,
    		\phpbb\user_loader $user_loader,
    		\phpbb\language\language $language,
    		\phpbb\auth\auth $auth,
    		$table_prefix)
    {
        $this->helper = $helper;
        $this->template = $template;
        $this->request = $request;
        $this->db = $db;
        $this->user = $user;
        $this->user_loader = $user_loader;
        $this->language = $language;
        $this->auth = $auth;
        $this->table_prefix = $table_prefix;
    }

    public function load_language_on_setup($event)
    {
    	$lang_set_ext = $event['lang_set_ext'];
    	$lang_set_ext[] = array(
    			'ext_name' => 'mafiascum/automodServer',
    			'lang_set' => 'common',
    	);
    	$event['lang_set_ext'] = $lang_set_ext;
    }

    public function inject_template_vars($event)
    {
    	$topic_id = $event['topic_id'];
        $this->template->assign_vars(array(
            'U_VOTE_HISTORY' => $this->helper->route(
            		'vote_history_route', array('topic_id' => $topic_id)),
        ));
    }
}