<?php
namespace mafiascum\automodServer\model\voting;

/**
 * Represents a player changing votes
 */
class VoteChange {
	private $postNumber;
	private $postId;
	private $voterPlayerSlot;
	private $targetPlayerSlot;

	public function __construct(
			$postNumber,
			$postId,
			$voterPlayerSlot,
			$targetPlayerSlot) {
	  $this->postId = $postId;
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

	public function getPostId() {
		return $this->postId;
	}
}
