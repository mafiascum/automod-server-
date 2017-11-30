<?php

namespace mafiascum\automodServer\controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

use mafiascum\restApi\model\resource\ResourceFactory;
use mafiascum\automodServer\voting\VoteConfigPostParser;

require_once(dirname(__FILE__) . "/../../restApi/model/resource/resourceFactory.php");
require_once(dirname(__FILE__) . "/../model/voting/VoteConfigPostParser.php");
require_once(dirname(__FILE__) . "/../model/voting/VoteCount.php");

class GameApi {
	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\request\request */
	protected $request;

	/* @var \phpbb\db\driver\driver */
	protected $db;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\user_loader */
	protected $user_loader;

	/* @var \phpbb\auth\auth */
	protected $auth;

	/* phpbb\language\language */
	protected $language;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, \phpbb\auth\auth $auth) {
		$this->helper = $helper;
		$this->template = $template;
		$this->request = $request;
		$this->db = $db;
		$this->user = $user;
		$this->user_loader = $user_loader;
		$this->language = $language;
		$this->auth = $auth;
	}

	private function getVotesData($id) {
		$topicPostsListData = ResourceFactory::list_resources(
				$this->db,
				$this->auth,
				array("topics", "posts"),
				array("topic_id" => $id),
				array("start" => 0, "limit" => "1"),
				true
		)['data'];
		$result = array ();
		$config = \mafiascum\automodServer\model\voting\VoteConfigPostParser::parseFromString(
				$topicPostsListData[0]['post_bbcode']);

		if (!$config) {
			return new JsonResponse ( array (
					"reason" => "gameconfig not found or invalid in firt post."
			), Response::HTTP_NOT_FOUND );
		}

		$topicPostsListData = ResourceFactory::list_resources(
				$this->db,
				$this->auth,
				array("topics", "posts"),
				array("topic_id" => $id),
				array("start" => $config->getDayStart(), "limit" => 10000),
				true
		)['data'];

		$voteHistory = $config->newVoteHistory ();
		$postNum = $config->getDayStart();
		foreach ( $topicPostsListData as $postRow ) {
			$voteHistory->maybeAddFromPost (
					$postNum, $postRow ['username'], $postRow ['post_bbcode'] );
			$postNum ++;
		}
		$votes = array ();
		foreach ( $voteHistory->getHistory () as $voteChange ) {
			$row = array ();
			$row ['voter'] = $voteChange->getVoter ()->getMainName ();
			$row ['target'] = $voteChange->getTargetOrNullIfUnvote ()
				? $voteChange->getTargetOrNullIfUnvote ()->getMainName () : NULL;
			$row ['postNumber'] = $voteChange->getPostNumber ();
			$votes [] = $row;
		}
		$voteCount = \mafiascum\automodServer\model\voting\VoteCount::generateWagons(
				$config, $voteHistory->getHistory());
		$result = array(
				'config' => json_encode($config->getPlayerSlotsArray()),
				'votes'=> $votes,
				'vc' => $voteCount->toBbCode($config));
		return $result;
	}

	public function get_votes($id) {


		$topicData = ResourceFactory::retrieve_resource ( $this->db, $this->auth, array (
				"topics"
		), array (
				"topic_id" => $id
		), $this->request, true );
		if (is_null ( $topicData )) {
			return new JsonResponse ( array (
					"reason" => "Resource with id '" . $id . "' does not exist."
			), Response::HTTP_NOT_FOUND );
		}
		return new JSONResponse($this->getVotesData($id));
	}

	public function post_vote_count($id) {
		// submit_post("reply", $subject, $username, $topic_type, $poll, $data);
		if (! function_exists ( 'submit_post' )) {
			include ('/var/www/html/includes/functions_posting.php');
		}
		$topicData = ResourceFactory::retrieve_resource ( $this->db, $this->auth, array (
				"topics"
		), array (
				"topic_id" => $id
		), $this->request, true );
		if (is_null ( $topicData )) {
			return new JsonResponse ( array (
					"reason" => "Resource with id '" . $id . "' does not exist."
			), Response::HTTP_NOT_FOUND );
		}
		// note that multibyte support is enabled here
		// variables to hold the parameters for submit_post
		$poll = $uid = $bitfield = $options = '';
		$my_text = $this->getVotesData($id)['vc'];
		generate_text_for_storage ( $my_subject, $uid, $bitfield, $options, false, false, false );
		generate_text_for_storage ( $my_text, $uid, $bitfield, $options, true, true, true );
		$data = array (
				'forum_id' => $topicData['forum_id'],
				'topic_id' => $id,
				'icon_id' => false,

				'enable_bbcode' => true,
				'enable_smilies' => true,
				'enable_urls' => true,
				'enable_sig' => true,

				'message' => $my_text,
				'message_md5' => md5 ( $my_text ),

				'bbcode_bitfield' => $bitfield,
				'bbcode_uid' => $uid,

				'post_edit_locked' => 0,
				#'topic_title' => '',
				'notify_set' => false,
				'notify' => false,
				'post_time' => 0,
				'forum_name' => '',
				'enable_indexing' => true
		);
		$url = submit_post ( 'reply', '', '', POST_NORMAL, $poll, $data );
		#return new JSONResponse(array('url' => $url, 'enc' => urlencode($url)));
		#var_dump();
		return new RedirectResponse(htmlspecialchars_decode($url));
	}
}
