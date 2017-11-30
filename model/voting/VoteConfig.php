<?php

namespace mafiascum\automodServer\model\voting;

require_once ('PlayerSlot.php');
require_once ('VoteHistory.php');

/**
 * Game configuration to hold information about players and their roles.
 */
class VoteConfig {
	private const DEFAULT_XML_VERSION = "1.0";
	/*
	 * An array of PlayerSlots representing the voting and votable slots in this game.
	 */
	private /*array of PlayerSlot*/ $playerSlotsArray;

	/*
	 * First post of the day (where to start counting from).
	 */
	private /*int*/ $daystart;

	/*
	 * Color format string representing the color that should be used in vote counts.
	 */
	private /*string*/ $color;

	/*
	 * Array of mod annoucements that will show up on vote counts.
	 */
	private /*array of string*/ $annoucements;

	public function __construct($playerSlotsArray, $daystart, $color, $annoucements) {
		$this->playerSlotsArray = $playerSlotsArray;
		$this->daystart = $daystart;
		$this->color = $color;
		$this->annoucements = $annoucements;
	}

	/**
	 * Parses a VoteConfig from a XML string.
	 */
	// TODO(toto): add link to XML doc
	public static function parseFromXml($slotsXml) {
		$xml = new \SimpleXMLElement ( $slotsXml );

		$xmlVersion = $xml['version'];
		if (trim($xmlVersion) == '') {
			$xmlVersion = VoteConfig::DEFAULT_XML_VERSION;
		}

		if ($xmlVersion == "1.0") {
			return VoteConfig::parseFromXmlV1_0($xml);
		} else {
			throw new \Exception("Unrecognized VoteConfig version: " . $xmlVersion);
		}
	}

	public static function parseFromXmlV1_0($xml) {
		$playerSlots = array ();
		foreach ( $xml->{'slot'} as $slot ) {
			$aliases = array ();
			foreach ( $slot->alias as $alias ) {
				$aliases [] = ( string ) $alias['name'];
			}
			$playerSlots [] = new PlayerSlot ( ( string ) $slot ['name'], $aliases );
		}


		$announcements = array();
		foreach ( $xml->{'announcement'} as $announcement ) {
			$announcements[] = (string) $announcement;
		}

		return new VoteConfig (
				$playerSlots,
				( int ) $xml->daystart,
				( string ) $xml->color,
				$announcements);
	}

	public function getPlayerSlotsArray() {
		return $this->playerSlotsArray;
	}

	public function getDayStart() {
		return $this->daystart;
	}

	public function getColor() {
		return $this->color;
	}

	public function getAnnouncements() {
		return $this->annoucements;
	}

	public function newVoteHistory() {
		return new VoteHistory ( $this->playerSlotsArray );
	}
}

