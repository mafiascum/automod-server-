<?php

namespace mafiascum\automodServer\model\voting;

/**
 * Represents a parsed raw vote target (or unvote).
 */
class RawVoteTarget {
	private $targetOrNullIfUnvote;
	private $bbCodeSnippet;
	public function __construct($targetOrNullIfUnvote, $bbCodeSnippet) {
		$this->targetOrNullIfUnvote = $targetOrNullIfUnvote;
		$this->bbCodeSnippet = $bbCodeSnippet;
	}

	/**
	 * Returns the target vote string, or NULL if this is an unvote.
	 */
	public function getTargetOrNullIfUnvote() {
		return $this->targetOrNullIfUnvote;
	}

	/**
	 * Returns the original bbcode from which this vote was parsed form
	 */
	public function getBbCodeSnippet() {
		return $this->bbCodeSnippet;
	}
	public function __toString() {
		if ($this->targetOrNullIfUnvote != NULL) {
			return "VOTE: " . $this->targetOrNullIfUnvote;
		} else {
			return "UNVOTE";
		}
	}
}
