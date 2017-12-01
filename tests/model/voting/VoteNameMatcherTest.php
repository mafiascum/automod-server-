<?php
namespace mafiascum\automodServer\model\voting;

use PHPUnit\Framework\TestCase;
use mafiascum\automodServer\model\voting\PlayerSlot;
use mafiascum\automodServer\model\voting\VoteNameMatcher;

require_once(dirname(__FILE__) . "/../../../model/voting/VoteNameMatcher.php");

class VoteNameMatcherTest extends TestCase {

	public function testNameMatcher() {
		$slots = array(
			$asn = new PlayerSlot('A Simple Name', NULL),
			$duplicate = new PlayerSlot('ADuplicateName', NULL),
			$distinctive = new PlayerSlot('A Distinctive noun', NULL),
			$m2w = new PlayerSlot('Mr Two Words', NULL),
			$long = new PlayerSlot('verylongname', NULL),
			$some1 = new PlayerSlot('someone', NULL),
			$undercores = new PlayerSlot('i_use_underscores', NULL),
			$umlaut = new PlayerSlot('ümlaute', NULL),
			$omlaut = new PlayerSlot('omlaute', NULL),
		);

		$expectations = array(
			"Simple" => $asn,
		    "ASN" => $asn,
			"asn" => $asn,
			// name has multiple plausible matches
			"name" => NULL,
			"Duplicate" => $duplicate,
			// matches both duplicate and distinctive
			"ADN" => NULL,
			#"2w" => $m2w,
			"words" => $m2w,
			"twowords" => $m2w,
			#"very" => $long,
		    #"long" => $long,
			#"some1" => $some1,
			#"s1" => $some1,
			#"some" => $some1,
		    "underscorez" => $undercores,
		);

		$matcher = new VoteNameMatcher($slots);

		$output = array();

		foreach ($expectations as $input => $expected) {
			$output[$input] = $matcher->matchTarget($input);
		}
		$this->assertEquals($expectations, $output);
	}
}
