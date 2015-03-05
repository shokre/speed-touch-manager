<?php
/* Created: qa 05/03/15 02:02 */

namespace Shokre\SpeedTouch;


class Configurator
{
	private $client;
	private $games;

	/**
	 * Configurator constructor.
	 * @param $client
	 */
	public function __construct($client)
	{
		$this->client = $client;
	}

	public function loadGames()
	{
		$this->games = array();

		foreach ($this->client->listGames() as $name => $game) {
			$this->games[$name] = new GameInfo($name, $game['assignment'], true);
		}

		return $this->games;
	}

	public function configure($map)
	{
		foreach ($map as $info) {

		}
	}

}
