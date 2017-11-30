<?php

namespace mafiascum\automodServer\model\voting;

require_once dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'vendor/jbbcode/jbbcode/JBBCode/Parser.php';

require_once 'RawVoteTarget.php';

/**
 * Utility class to parse votes in bbcode encoded posts.
 */
// TODO: handle loved/hated
// TODO: handle vote modifiers (2xvoter, non-voters, etc)
class RawVoteParser {
	/**
	 * Parses all instances of syntactically valid votes and unvotes into an array
	 * of RawVoteTargets and returns the array.
	 */
	public static function parseAllRawVoteTargetsFromPost($bb_code_str) {
		$parser = new \JBBCode\Parser ();

		$builder = new \JBBCode\CodeDefinitionBuilder ( 'b', '{param}' );
		$builder->setParseContent ( "false" );
		$parser->addCodeDefinition ( $builder->build () );

		$builder = new \JBBCode\CodeDefinitionBuilder ( 'vote', 'vote: {param}' );
		$builder->setParseContent ( "false" );
		$parser->addCodeDefinition ( $builder->build () );

		$builder = new \JBBCode\CodeDefinitionBuilder ( 'v', 'vote: {param}' );
		$builder->setParseContent ( "false" );
		$parser->addCodeDefinition ( $builder->build () );

		$builder = new \JBBCode\CodeDefinitionBuilder ( 'unvote', 'unvote:' );
		$builder->setParseContent ( "false" );
		$parser->addCodeDefinition ( $builder->build () );

		$builder = new \JBBCode\CodeDefinitionBuilder ( 'uv', 'unvote:' );
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
		$visitor = new VoteTagVisitor ();
		$parser->accept ( $visitor );
		return $visitor->getMaybeVotes ();
	}
}
class VoteTagVisitor implements \JBBCode\NodeVisitor {
	private $maybeVotes = array ();
	public function getMaybeVotes() {
		return $this->maybeVotes;
	}
	public function visitDocumentElement(\JBBCode\DocumentElement $documentElement) {
		foreach ( $documentElement->getChildren () as $child ) {
			$child->accept ( $this );
		}
	}
	public function visitTextNode(\JBBCode\TextNode $textNode) {
		// DO NOTHING
	}

	/**
	 * Parses an element node that is potentially a vote and returns a RawVoteTarget if
	 * a valid syntax vote is detected.
	 *
	 * ElementNodes passed here must resolve into the pattern '[un]vote(: target)
	 * when converted to HTML in order to be parsed as votes (or unvotes).
	 */
	private function fromElementNode(\JBBCode\ElementNode $elementNode) {
		$parsed_maybe_vote = strtolower ( $elementNode->getAsHTML () );
		// check if this is an unvote
		$num_matches = preg_match ( "/[\t ]*unvote[:]*.*/", $parsed_maybe_vote );
		if ($num_matches) {
			return new RawVoteTarget ( NULL, $elementNode->getAsBBCode () );
		}

		$num_matches = preg_match (
				"/[ \t]*vote:[ \t]*(.*[^ ])[ \t]*$/", $parsed_maybe_vote, $matches );

		if ($num_matches) {
			return new RawVoteTarget ( $matches [1], $elementNode->getAsBBCode () );
		}
		return NULL;
	}

	public function visitElementNode(\JBBCode\ElementNode $elementNode) {
		$maybeVoteTarget = $this->fromElementNode ( $elementNode );
		if ($maybeVoteTarget != NULL) {
			$this->maybeVotes [] = $maybeVoteTarget;
		}
	}
}

