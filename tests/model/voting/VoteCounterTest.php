<?php

namespace mafiascum\automodServer\model\voting;

use PHPUnit\Framework\TestCase;
use mafiascum\automodServer\model\voting\VoteConfigPostParser;
use mafiascum\automodServer\model\voting\PlayerSlot;
use mafiascum\automodServer\model\voting\VoteChange;
use mafiascum\automodServer\model\voting\VoteCount;

require_once (dirname ( __FILE__ ) . "/../../../model/voting/VoteConfigPostParser.php");
require_once (dirname ( __FILE__ ) . "/../../../model/voting/PlayerSlot.php");
require_once (dirname ( __FILE__ ) . "/../../../model/voting/VoteChange.php");
require_once (dirname ( __FILE__ ) . "/../../../model/voting/VoteCount.php");
class VoteCounterTest extends TestCase {
	public function testSimpleVoteCounter() {
		$a = new PlayerSlot ( 'a', "aa" );
		$b = new PlayerSlot ( 'b', NULL );
		$c = new PlayerSlot ( 'c', NULL );
		$d = new PlayerSlot ( 'd', NULL );
		$e = new PlayerSlot ( 'e', NULL );
		$f = new PlayerSlot ( 'f', NULL );
		$voteChangeArray = array (
				new VoteChange ( 1, 1, $a, VoteTarget::vote($b) ),
				new VoteChange ( 2, 2, $b, VoteTarget::vote($b) ),
				new VoteChange ( 3, 3, $b, VoteTarget::unvote() ),
				new VoteChange ( 4, 4, $c, VoteTarget::vote($b) ),
				new VoteChange ( 5, 5, $c, VoteTarget::vote($d) ),
				new VoteChange ( 6, 6, $c, VoteTarget::vote($d) ),
				new VoteChange ( 7, 7, $d, VoteTarget::unvote() ),
				new VoteChange ( 8, 8, $f, VoteTarget::vote($d) )
		);
		$config = <<<XML
		[voteconfig]
			<config version="1.0">
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
		$expected = '';
		$expected .= "[color=#FF0000]\n";
		$expected .= "[area=\"Auto-Generated Vote Count\"]\n";
		$expected .= "d (2): [post=5]c[/post] [post=8]f[/post]\n";
		$expected .= "b (1): [post=1]a[/post]\n";
		$expected .= "Not Voting (3): [post=3]b[/post] [post=1]d[/post] [post=1]e[/post]\n";
		$expected .= "[/area]\n";
		$expected .= "\nDeadline is in three days\n";
		$expected .= "\nPlayer A is on V/LA until saturday\n";
		$expected .= "[/color]\n";
		$voteConfig = VoteConfigPostParser::parseFromString ($config);
		$voteCount = VoteCount::generateWagons ( $voteConfig, $voteChangeArray );
		$this->assertEquals ( $expected, $voteCount->toBbcode ($voteConfig) );
	}
}
