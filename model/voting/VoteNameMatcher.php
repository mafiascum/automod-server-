<?php

namespace mafiascum\automodServer\model\voting;

/**
 * Takes an array of PlayerSlot and returns a matching PlayerSlot for this raw target
 * or NULL if none of the targets match.
 */
class VoteNameMatcher {
	const CHARSET = "UTF-8";

	const SUBS = [
		"1" => "one",
		"2" => "two",
		"3" => "three",
		"3" => "three",
		"4" => "four",
		"5" => "five",
		"6" => "six",
		"7" => "seven",
		"8" => "eight",
		"9" => "nine",
	];

	private $playerSlotArray;
	private $slotsToUniqueTokens;

	public function __construct($playerSlotArray) {
		$this->playerSlotArray = $playerSlotArray;
		$this->slotsToUniqueTokens = self::tokenize ( $playerSlotArray );
	}

	private static function tokenize($playerSlotArray) {
		$tokToSlots = array ();

		foreach ( $playerSlotArray as $slot ) {
			$tokens = self::allSubTokens ( $slot->getMainName () );
			for ($i = 0; $i < count($tokens); ++$i) {
				$token = "";
				for ($j = 0; $i +$j < count($tokens); ++$j) {
					$token .= mb_strtolower($tokens[$i + $j], self::CHARSET);
					$tokToSlots [$token] [] = $slot;
					//$token .= " ";
				}
			}
			$abrv = "";
			foreach ( $tokens as $token ) {
				$abrv .= mb_substr($token, 0, 1, self::CHARSET);
			}
			$tokToSlots [$abrv] [] = $slot;
		}

		$slotsToUniqueTokens = array ();
		foreach ( $tokToSlots as $tok => $slots ) {
			if (count ( $slots ) == 1) {
				$slot = $slots [0];
				// if (!$slotsToUniqueTokens[$slot]) {
				// $slotsToUniqueTokens[$slot] = array();
				// }
				$slotsToUniqueTokens [$slot->getMainName ()] [] = $tok;
			}
		}
		return $slotsToUniqueTokens;
	}

	private static function allSubTokens($str) {
		$result = array ();
		$re = '/(?#! splitCamelCase Rev:20140412)
				# Split camelCase "words". Two global alternatives. Either g1of2:
					(?<=[a-z])      # Position is after a lowercase,
					(?=[A-Z])       # and before an uppercase letter.
				|   (?<=[A-Z])      # Or g2of2; Position is after uppercase,
					(?=[A-Z][a-z])  # and before upper-then-lower case.
    				/x';

		$simpleTokens = preg_split ( "/[\s_]+/", $str );
		foreach($simpleTokens as $simple) {
			$camelCaseTokens = preg_split ( $re, $simple );
			foreach($camelCaseTokens as $tok) {
				$result[] = $tok;
			}
		}
		return $result;
	}

	public function matchExact($str) {
		$str = strtolower ( $str );
		foreach ( $this->playerSlotArray as $playerSlot ) {

			if (strtolower ($playerSlot->getMainName()) == $str) {
				return $playerSlot;
			}

			if ($playerSlot->getAliases ()) {
				foreach ( $playerSlot->getAliases () as $alias ) {
					if (strtolower ( $alias ) == $str) {
						return $playerSlot;
					}
				}
			}
		}
		return NULL;
	}

	private static function isSameChar($a, $b, $i, $j) {
		$ua = mb_strtolower ( mb_substr ( $a, $i, 1, self::CHARSET ), self::CHARSET );
		$ub = mb_strtolower ( mb_substr ( $b, $j, 1, self::CHARSET ), self::CHARSET );
		return $ua == $ub;
	}

	// TODO: this has not really been tested.
	// min edit distance impelemented with top-down dynamic programming
	public static function dist($a, $b, $i = -1, $j = -1, &$mem = NULL) {
		if ($i < 0 || $j < 0) {
			$mem = array ();
			return self::dist (
					$a,
					$b,
					mb_strlen ( $a, self::CHARSET),
					mb_strlen ( $b, self::CHARSET ),
					$mem );
		}

		// base case
		if (min ( $i, $j ) == 0) {
			return max($i, $j);
		}

		if (! array_key_exists ( $i, $mem )) {
			$mem [$i] = array ();
		}

		// if already computed return that value
		if (array_key_exists ( $j, $mem [$i] )) {
			return $mem [$i] [$j];
		}

		// othewise compute it recursively
		$best = min ( self::dist ( $a, $b, $i - 1, $j, $mem ) + 1,
				self::dist ( $a, $b, $i, $j - 1, $mem ) + 1,
				self::dist ( $a, $b, $i - 1, $j - 1, $mem)
					+ (self::isSameChar ( $a, $b, $i - 1, $j - 1 ) ? 0 : 1) );

		// store the computed value
		$mem [$i] [$j] = $best;
		return $best;
	}

	private static function startsWith($word, $prefix) {
		return NULL;
	}

	/**
	 * Match the vote target string to one of the PlayerSlots in this matcher using
	 * clever heuristics.
	 */
	public function matchTarget($str) {
		$exact = $this->matchExact ( $str );

		if ($exact) {
			return $exact;
		}

		$bestDist = - 1;
		$best = NULL;

		// try full names first
		foreach ( $this->playerSlotArray as $slot ) {
			$dist = self::dist ( $slot->getMainName (), $str );
			if ($bestDist < 0 || $bestDist > $dist) {
				$best = array ($slot);
				$bestDist = $dist;
			} elseif ($bestDist == $dist) {
				$best [] = $slot;
			}
		}

		if ($bestDist < 3) {
			if (count ( $best ) == 1) {
				return $best [0];
			}
			var_dump($best);
			return NULL;
		}
		// try start with
		// try subsequences of name tokens
		foreach ( $this->playerSlotArray as $slot ) {
			foreach($this->slotsToUniqueTokens[$slot->getMainName()] as $token) {
				$dist = self::dist ( $token, $str );
				if ($bestDist < 0 || $bestDist > $dist) {
					$best = array ($slot);
					$bestDist = $dist;
				} elseif ($bestDist == $dist && !in_array($slot, $best)) {
					$best [] = $slot;
				}
			}
		}

		if ($bestDist < 3) {
			if (count ( $best ) == 1) {
				return $best [0];
			}
            var_dump($best);
			return NULL;
		}

		return NULL;
		// use some heuristics here.
	}
}
