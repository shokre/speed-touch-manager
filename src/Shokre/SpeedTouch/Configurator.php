<?php
/* Created: qa 05/03/15 02:02 */

namespace Shokre\SpeedTouch;


class Configurator
{
	/** @var Client */
	private $client;
	private $games;
	private $devices;

	/**
	 * Configurator constructor.
	 * @param $client
	 */
	public function __construct($client)
	{
		$this->client = $client;
	}

	/**
	 * @return GameInfo[]
	 */
	public function loadGames()
	{
		$this->games = array();
		$dmap = $this->devMap();
		foreach ($this->client->listGames() as $name => $game) {
			$aa = $game['assignment'];
			if ($aa) {
				if (array_key_exists($game['assignment'], $dmap))
					$aa = $dmap[$game['assignment']]['ip'];
			}
			$this->games[$name] = new GameInfo($name, $aa, $this->client);
		}

		return $this->games;
	}

	public function devMap()
	{
		if ($this->devices === null)
			$this->devices = $this->client->listDevices();
		return $this->devices;
	}

	/**
	 * @param GameInfo[] $map
	 */
	public function configure($map)
	{
		$games = $this->loadGames();
		$active = array();
		foreach ($map as $info) {
			// ako servis ne postoji
			// kreiraj
			// slozi portovi
			// assign

			// ako servis postoji
			// unassign
			// delete
			// kreiraj
			// slozi portovi
			// assign


			// ako servis postoji
			$name = $info->getName();
			if (array_key_exists($name, $games))
			{
				$gi = $games[$name];
				printf("> Exists: %s\n", $name);
				$gi->configure($this->client, $info);
			}
			else if (!$info->isDeleted())
			{
				$info->setClient($this->client);
				$info->createApp();
				$info->assign();
			}
			if (!$info->isDeleted())
				$active[$name] = true;
		}

		foreach ($games as $n => $gi)
		{
			if (array_key_exists($n, $active))
				continue;

			if (!$gi->getAssignment())
				continue;

			$gi->release();
		}
	}

}
