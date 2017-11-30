<?php
namespace mafiascum\automodServer\model\voting;

/**
 * Represents a player changing votes
 */
class VoteChange {
	private $postNumber;
	private $voterPlayerSlot;
	private $targetPlayerSlot;

	public function __construct(
			$postNumber,
			$voterPlayerSlot,
			$targetPlayerSlot) {
      $this->postNumber = $postNumber;
      $this->voterPlayerSlot = $voterPlayerSlot;
      $this->targetPlayerSlot = $targetPlayerSlot;
	}

	public function getVoter() {
		return $this->voterPlayerSlot;
	}

	public function getTargetOrNullIfUnvote() {
		return $this->targetPlayerSlot;
	}

	public function getPostNumber() {
		return $this->postNumber;
	}
}
