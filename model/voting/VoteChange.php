<?php
namespace mafiascum\automodServer\model\voting;

/**
 * Represents a player changing votes
 */
class VoteChange {
	private $postNumber;
	private $postId;
	private $voterPlayerSlot;
	private $target;
	private $bbCodeSnippet;

	public function __construct(
			$postNumber,
			$postId,
			$voterPlayerSlot,
			$target,
			$bbCodeSnippet = NULL) {
	  $this->postNumber = $postNumber;
	  $this->postId = $postId;
      $this->voterPlayerSlot = $voterPlayerSlot;
      $this->target = $target;
      $this->bbCodeSnippet = $bbCodeSnippet;
	}

	public function getVoter() {
		return $this->voterPlayerSlot;
	}

	public function getTarget() {
		return $this->target;
	}

	public function getPostNumber() {
		return $this->postNumber;
	}

	public function getPostId() {
		return $this->postId;
	}

	public function getBbCodeSnippet() {
		return $this->bbCodeSnippet;
	}
}
