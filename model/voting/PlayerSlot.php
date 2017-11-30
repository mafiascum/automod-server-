<?php

namespace mafiascum\automodServer\model\voting;

/**
 * Represents a player slot for the purposes of voting.
 *
 * A player is represented by a main slot game, and alises
 * (aliases are useful to handle replacements, and hydrae)
 */
class PlayerSlot {
	// a string
	private $mainName;
	// an array of strings representing other user ids used by this slot (hydrae, replacements)
	private $aliases;
	public static $NO_LYNCH;
	public function __construct($mainName, $aliases) {
		$this->mainName = $mainName;
		$this->aliases = $aliases ? $aliases : array();
	}

	public function getAliases() {
		return $this->aliases;
	}

	public function getMainName() {
		return $this->mainName;
	}

	public function __toString() {
		$str = $this->mainName;
		foreach ($this->aliases as $alias) {
			$str .= " (". $alias.")";
		}
		return $str;
	}
}

PlayerSlot::$NO_LYNCH = new PlayerSlot ( "No Lynch", array () );
