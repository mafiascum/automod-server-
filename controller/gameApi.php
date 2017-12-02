<?php

namespace mafiascum\automodServer\controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use mafiascum\restApi\model\resource\ResourceFactory;
use mafiascum\automodServer\model\voting\VoteConfigPostParser;
use mafiascum\automodServer\model\voting\VoteTarget;


require_once(dirname(__FILE__) . "/../../restApi/model/resource/resourceFactory.php");
require_once(dirname(__FILE__) . "/../model/voting/VoteConfigPostParser.php");
require_once(dirname(__FILE__) . "/../model/voting/VoteCount.php");
require_once(dirname(__FILE__) . "/../model/voting/VoteTarget.php");

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

	private function parseConfig($id) {
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
			throw new \Exception("voteconfig not found or invalid");
		}
		return $config;
	}

	private function parseVoteHistory($config, $id) {
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
					$postNum,
					$postRow['post_id'],
					$postRow ['username'],
					$postRow ['post_bbcode']
		            );
			$postNum ++;
		}

		return $voteHistory;
	}

	public function get_votes($id) {
		$config = $this->parseConfig($id);
		$voteHistory = $this->parseVoteHistory($config, $id);
		$voteCount = \mafiascum\automodServer\model\voting\VoteCount::generateWagons(
				$config, $voteHistory->getHistory());
		$result = array(
				'config' => json_encode($config->getPlayerSlotsArray()),
				'vc' => $voteCount->toBbCode($config));

		return new JSONResponse($result);
	}

	/**
	 * Controller for rendering vote history interface
	 *
	 * @param string $topic_id the id of the topic
	 *
	 * @see vote_history.html
	 *
	 * @throws \phpbb\exception\http_exception
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle_vote_history($topic_id)
	{
		if (!$topic_id) {
			throw new \phpbb\exception\http_exception(400, 'NO_TOPIC', $topic_id);
		}
		$topicData = ResourceFactory::retrieve_resource (
				$this->db, $this->auth,
				array ("topics"),
				array ("topic_id" => $topic_id),
				$this->request,
				true );
		if (!$topicData ) {
			throw new \phpbb\exception\http_exception(400, 'TOPIC_NOT_FOUND', $topic_id);
		}

		$config = $this->parseConfig($topic_id);
		$voteHistory = $this->parseVoteHistory($config, $topic_id);

		$this->template->assign_vars(array(
				'TOPIC_ID'  => $topic_id,
				'FORUM_ID' => $topicData['forum_id'],
				'U_VOTE_HISTORY' => $this->helper->route(
						'vote_history_route', array('topic_id' => $topic_id)),
		));

		foreach ( $voteHistory->getHistory () as $voteChange ) {
			switch ($voteChange->getTarget()->getType()) {
				case VoteTarget::VOTE:
					$target = $voteChange->getTarget()->getTarget();
					break;
				case VoteTarget::UNVOTE:
					$target = "--";
					break;
				case VoteTarget::UNRECOGNIZED_TARGET:
					$target = "*invalid*";
					break;
				default:
					throw new \Exception("unrecognized vote target type: "
							. $voteChange->getTarget()->getType());
			}
			$this->template->assign_block_vars('VOTES', array(
					'VOTER' => $voteChange->getVoter(),
					'TARGET' =>  $target,
					'POST_NUMBER' => $voteChange->getPostNumber(),
					'POST_ID' => $voteChange->getPostId(),
					'POST_BB_CODE' => $voteChange->getBbCodeSnippet(),
			));
		}
		return $this->helper->render('vote_history.html', $name);
	}
}
