<?php

namespace mafiascum\automodServer\model\voting;


class VoteTarget {
	const VOTE = 0;
	const UNVOTE = 1;
	const UNRECOGNIZED_TARGET = 2;

	private $target;
	private $type;

	private function __construct($target, $type) {
		$this->target = $target;
		$this->type = $type;
	}

	public static function vote($target) {
		return new VoteTarget($target, self::VOTE);
	}

	public static function unvote() {
		return new VoteTarget(NULL, self::UNVOTE);
	}

	public static function unrecognized() {
		return new VoteTarget(NULL, self::UNRECOGNIZED_TARGET);
	}

	public function getTarget() {
		return $this->target;
	}

	public function getType() {
		return $this->type;
	}
}
