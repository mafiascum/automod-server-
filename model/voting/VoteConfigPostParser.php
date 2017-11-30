<?php

namespace mafiascum\automodServer\model\voting;

require_once dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'vendor/jbbcode/jbbcode/JBBCode/Parser.php';

require_once ('VoteConfig.php');

/**
 * Game configuration to hold information about players and their roles.
 */
class VoteConfigPostParser {
	private /*array of PlayerSlot*/ $playerSlotsArray;
	public function __construct($playerSlotsArray) {
		$this->playerSlotsArray = $playerSlotsArray;
	}

	/**
	 * Parses the game config from a BBCode encoded post.
	 */
	public static function parseFromString($bb_code_str) {
		$parser = new \JBBCode\Parser ();

		$bb_code_str = html_entity_decode($bb_code_str);

		$builder = new \JBBCode\CodeDefinitionBuilder ( 'voteconfig', '{param}' );
		$builder->setParseContent ( "false" );
		$parser->addCodeDefinition ( $builder->build () );

		// Ignore votes inside the following tags: (quote, spoilers)
		$builder = new \JBBCode\CodeDefinitionBuilder ( 'quote', '' );
		$builder->setParseContent ( "false" );
		$parser->addCodeDefinition ( $builder->build () );

		$builder = new \JBBCode\CodeDefinitionBuilder ( 'spoiler', '' );
		$builder->setParseContent ( "false" );
		$parser->addCodeDefinition ( $builder->build () );

		$parser->parse ( $bb_code_str );
		$visitor = new VoteConfigNodeVisitor ();
		$parser->accept ( $visitor );
		return $visitor->getVoteConfig ();
	}
	public function getPlayerSlotsArray() {
		return $this->playerSlotsArray;
	}
	public function newVoteHistory() {
		return new VoteHistory ( $this->playerSlotsArray );
	}
}

class VoteConfigNodeVisitor implements \JBBCode\NodeVisitor {
	private $voteConfig;
	public function getVoteConfig() {
		return $this->voteConfig;
	}
	public function visitDocumentElement(\JBBCode\DocumentElement $documentElement) {
		foreach ( $documentElement->getChildren () as $child ) {
			$child->accept ( $this );
		}
	}
	public function visitTextNode(\JBBCode\TextNode $textNode) {
		// DO NOTHING
	}
	public function visitElementNode(\JBBCode\ElementNode $elementNode) {
		if ('voteconfig' == strtolower ( $elementNode->getTagName () )) {
			$content = trim ( $elementNode->getAsText());
			$this->voteConfig = VoteConfig::parseFromXml ($content);
		}
	}
}
