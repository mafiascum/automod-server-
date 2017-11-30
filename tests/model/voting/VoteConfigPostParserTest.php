<?php

namespace mafiascum\automodServer\model\voting;

use PHPUnit\Framework\TestCase;
use mafiascum\automodServer\model\voting\PlayerSlot;
use mafiascum\automodServer\model\voting\VoteConfigPostParser;

require_once(dirname(__FILE__) . "/../../../model/voting/VoteConfigPostParser.php");


class VoteConfigPostParserTest extends TestCase {
	public function testSimpleParser() {
		$config = <<<XML
		[voteconfig]
			<config>
				<slot name="a">
					<alias name="a_replaced"/>
					<alias name="a_replaced_alt"/>
				</slot>
				<slot name="b">
					<alias name="b_alt"/>
				</slot>
				<slot name="c"/>
				<slot name="d"/>
				<slot name="e"/>
				<slot name="f"/>
				<color>#FF0000</color>
				<daystart>1</daystart>
				<announcement>Deadline is in three days</announcement>
				<announcement>Player A is on V/LA until saturday</announcement>
			</config>
		[/voteconfig]
XML;
		$voteConfig = VoteConfigPostParser::parseFromString ($config);

		$this->assertEquals(6, count($voteConfig->getPlayerSlotsArray()));
		$this->assertEquals(
			new PlayerSlot('a', array("a_replaced", "a_replaced_alt")),
			$voteConfig->getPlayerSlotsArray()[0]
		);
		$this->assertEquals(
			new PlayerSlot('b', array("b_alt")),
			$voteConfig->getPlayerSlotsArray()[1]
		);
		$this->assertEquals(
			new PlayerSlot('c', array()),
			$voteConfig->getPlayerSlotsArray()[2]
		);
		$this->assertEquals(
			new PlayerSlot('d', array()),
			$voteConfig->getPlayerSlotsArray()[3]
		);
		$this->assertEquals(
			new PlayerSlot('e', array()),
			$voteConfig->getPlayerSlotsArray()[4]
		);
		$this->assertEquals(
			new PlayerSlot('f', array()),
			$voteConfig->getPlayerSlotsArray()[5]
		);
		$this->assertEquals("#FF0000", $voteConfig->getColor());
		$this->assertEquals(1, $voteConfig->getDayStart());
		$this->assertEquals(2,
				count($voteConfig->getAnnouncements()));
		$this->assertEquals("Deadline is in three days",
				$voteConfig->getAnnouncements()[0]);
		$this->assertEquals("Player A is on V/LA until saturday",
				$voteConfig->getAnnouncements()[1]);
	}
}
