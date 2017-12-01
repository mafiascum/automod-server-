<?php
/**
 *
 * @package phpBB Extension - MafiaScum Private Topics
 * @copyright (c) 2017 mafiascum.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'VOTE_HISTORY'       => 'Vote History',
    'VOTE_TARGET'            => 'Target',
    'VOTE_VOTER'        => 'Voter',
    'VOTE_POST'       => 'Post Number',
	'GENERATE_VOTE_COUNT' => 'Post VC',
));
