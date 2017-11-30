<?php

namespace mafiascum\automodServer\model\voting;

/**
 * Represents a parsed raw vote target (or unvote).
 */
class RawVoteTarget {
	private $targetOrNullIfUnvote;
	private $debugBbCode;
	public function __construct($targetOrNullIfUnvote, $debugBbCode) {
		$this->targetOrNullIfUnvote = $targetOrNullIfUnvote;
		$this->debugBbCode = $debugBbCode;
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
	public function getDebugBbCode() {
		return $this->debugBbCode;
	}
	public function __toString() {
		if ($this->targetOrNullIfUnvote != NULL) {
			return "VOTE: " . $this->targetOrNullIfUnvote;
		} else {
			return "UNVOTE";
		}
	}
}
