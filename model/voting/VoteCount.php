<?php

namespace mafiascum\automodServer\model\voting;

class VoteCount {

	/*
	 * A map from PlayerSlot to VoteChange representing where a player first voted that slot,
	 * and didn't vote a different target after that. Not Voting is represented as key = NULL
	 * in the returned map;
	 */
	private $voteChangeByWagonee;
	private function __construct($voteChangeByWagonee) {
		$this->voteChangeByWagonee = $voteChangeByWagonee;
	}

	/**
	 * Returns a VoteCount vased on the given playing player and the list of
	 * vote changes
	 *
	 * @param VoteConfig $voteConfig
	 * @param VoteChange[] $voteChangeArray
	 */
	public static function generateWagons($voteConfig, $voteChangeArray) {
		$voteChangeByVotingPlayer = array ();

		// Initialize everyone 'unvoting' at the first post of the day.
		foreach ( $voteConfig->getPlayerSlotsArray () as $playerSlot ) {
			$voteChangeByVotingPlayer [$playerSlot->getMainName ()]
			   = new VoteChange (
			   		$voteConfig->getDayStart (),
			   		/*postid*/ NULL,
			   		$playerSlot,
			   		VoteTarget::unvote(),
			   		 NULL );
		}

		// update first vote to current target that didn't change after that vote
		// for each player
		foreach ( $voteChangeArray as $voteChange ) {
			$voter = $voteChange->getVoter ()->getMainName ();
			// check the new target is different from the old target
			if ($voteChangeByVotingPlayer [$voter]->getTarget ()
					!= $voteChange->getTarget ()) {
				$voteChangeByVotingPlayer [$voter] = $voteChange;
			}
		}

		// 'reverse' the map
		$voteChangeByWagonee = array ();
		foreach ( $voteChangeByVotingPlayer as $voteChange ) {
			$target = $voteChange->getTarget ();
			$wagonee = $target->getType() == VoteTarget::VOTE ? $target->getTarget()->getMainName () : NULL;
			$voteChangeByWagonee [$wagonee] [] = $voteChange;
		}

		uksort ( $voteChangeByWagonee, function ($a, $b) use($voteChangeByWagonee) {
			if ($a == $b) {
				return 0;
			}
			if ($a == NULL) {
				return 1;
			}
			if ($b == NULL) {
				return - 1;
			}
			// largest wagon first
			$ca = count ( $voteChangeByWagonee [$a] );
			$cb = count ( $voteChangeByWagonee [$b] );
			if ($cb - $ca != 0) {
				return $cb - $ca;
			}
			// alphanumerically smaller first
			return strcmp ( $a, $b );
		} );

		foreach ( $voteChangeByWagonee as $target => $voteChangeArray ) {
			usort($voteChangeArray, function($a, $b) {
		      $pn = $a->getPostNumber() - $b->getPostNumber();
		      if ($pn != 0) {
		      	return $pn;
		      }
		      return strcmp($a->getVoter()->getMainName(), $b->getVoter()->getMainName());
			});
		}

		return new VoteCount ( $voteChangeByWagonee );
	}

	public function getWagonData() {
		return $this->voteChangeByWagonee;
	}

	public function toBbcode($voteConfig) {
		$strVoteCount = "";
		if ($voteConfig->getColor()) {
			$strVoteCount = "[color=" . $voteConfig->getColor() . "]\n";
		}
		$strVoteCount .= "[area=\"Auto-Generated Vote Count\"]\n";
		foreach ( $this->voteChangeByWagonee as $target => $voteChangeArray ) {
			if (! $target) {
				$target = "Not Voting";
			}
			$strVoteCount .= sprintf ( "%s (%d):", $target, count ( $voteChangeArray ) );
			foreach ( $voteChangeArray as $voteChange ) {
				$strVoteCount .= sprintf ( " [post=%d]%s[/post]",
						$voteChange->getPostNumber (),
						$voteChange->getVoter ()->getMainName () );
			}
			$strVoteCount .= "\n";
		}
		$strVoteCount .= "[/area]\n";
		foreach ($voteConfig->getAnnouncements() as $ann) {
			$strVoteCount .= "\n" . $ann ."\n";
		}
		if ($voteConfig->getColor()) {
			$strVoteCount .= "[/color]\n";
		}
		return $strVoteCount;
	}
}
