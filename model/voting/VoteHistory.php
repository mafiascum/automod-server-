<?php

namespace mafiascum\automodServer\model\voting;

require_once 'PlayerSlot.php';
require_once 'RawVoteParser.php';
require_once 'VoteChange.php';
require_once 'VoteNameMatcher.php';
require_once 'VoteTarget.php';


/**
 * Represents a voting history
 */
class VoteHistory {
	//vote change history
	private $voteChangeArray = array ();

	private /*VoteNameMatcher*/ $voteNameMatcher;

	function __construct(/*array of PlayerSlot*/ $playerSlotArray) {
		$this->voteNameMatcher = new VoteNameMatcher ( $playerSlotArray );
	}

	/**
	 * Tries to parse the BBCode post and extract a vote command (vote or unvote) that
	 * matches the list of players defined in this vote history.
	 *
	 * @param integer $postNumber
	 * @param integer $postId
	 * @param string $postUsername
	 * @param string $postBbcode
	 */
	public function maybeAddFromPost(
			$postNumber,
			$postId,
			$postUsername,
			$postBbcode) {
		$voter = $this->voteNameMatcher->matchExact ( $postUsername );

		if ($voter != NULL) {
			// voter is recognized as a voting slot
			$rawVoteTargetArray = RawVoteParser::parseAllRawVoteTargetsFromPost ( $postBbcode );
			$current = NULL;
			foreach ( $rawVoteTargetArray as $rawVoteTarget ) {
				if (! $rawVoteTarget->getTargetOrNullIfUnvote ()) {
					// unvote
					$current = new VoteChange (
							$postNumber,
							$postId,
							$voter,
							VoteTarget::unvote(),
							$rawVoteTarget->getBbcodeSnippet());
				} else {
					// try to find a valid target
					$targetSlot = $this->voteNameMatcher->matchTarget (
							$rawVoteTarget->getTargetOrNullIfUnvote () );

					if ($targetSlot) {
						$target = VoteTarget::vote($targetSlot);
					} else {
						$target = VoteTarget::unrecognized();
					}
					$current = new VoteChange (
							$postNumber,
							$postId,
							$voter,
							$target,
							$rawVoteTarget->getBbcodeSnippet() );
				}
			}
			if ($current) {
				$this->voteChangeArray [] = $current;
			}
		}
	}

	/**
	 * @return an array of VoteChange in this history
	 */
	public function getHistory() {
		return $this->voteChangeArray;
	}
}


