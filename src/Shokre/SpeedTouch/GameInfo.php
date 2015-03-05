<?php
/* Created: qa 05/03/15 00:37 */

namespace Shokre\SpeedTouch;


class GameInfo
{
	private $name;
	private $assignment;
	/** @var PortMapping[]  */
	private $port_map = null;
	/** @var Client */
	private $client = null;
	private $deleted = false;

	/**
	 * GameInfo constructor.
	 * @param $name
	 */
	public function __construct($name, $assignment = null, $client = null)
	{
		$this->name = $name;
		$this->assignment = $assignment;
		$this->client = $client;
		if (!$this->client)
			$this->port_map = array();
	}

	public static function create($name, $assignment = null)
	{
		return new GameInfo($name, $assignment);
	}

	private function isMapLoaded()
	{
		return $this->port_map !== null;
	}

	public function map($proto, $from, $to = null)
	{
		if ($proto == Client::PROTO_ANY) {
			$this->map(Client::PROTO_TCP, $from, $to);
			$this->map(Client::PROTO_UDP, $from, $to);
			return $this;
		}
		$pm = new PortMapping($proto, $from, $to);
		$this->port_map[] = $pm;
		return $this;
	}

	/**
	 * @param Client $stm
	 * @return PortMapping[]
	 */
	public function getMap()
	{
		if (!$this->isMapLoaded() && $this->client) {
			$this->port_map = PortMapping::mapFromArray($this->client->listGamePorts($this->name));
		}
		return $this->port_map;
	}

	public function absent()
	{
		$this->deleted = true;
		return $this;
	}

	public function portMapEquals($other)
	{
		$map = $this->getMap();
		$omap = $other->getMap();

		foreach ($map as $m) {
			$found = false;
			foreach ($omap as $k => $om) {
				if ($m->equals($om)) {
					$found = true;
					unset($omap[$k]);
					break;
				}
			}
			if (!$found)
				return false;
		}
		if ($omap)
			return false;

		return true;
	}

	/**
	 * assign service from device
	 */
	public function assign()
	{
		printf("%s: %s %s\n", __FUNCTION__, $this->name, $this->assignment);
		return $this->client->assignGame($this->name, $this->assignment);
	}

	/**
	 * unassign service from device
	 */
	public function release()
	{
		printf("%s: %s %s\n", __FUNCTION__, $this->name, $this->assignment);
		return $this->client->unassignGame($this->name);
	}

	/**
	 * unassign service from device
	 */
	public function deleteGame()
	{
		printf("%s: %s %s\n", __FUNCTION__, $this->name, $this->assignment);
		return $this->client->deleteGame($this->name);
	}

	public function configure($client, $cfg)
	{
		$asc = $this->assignment == $cfg->assignment;
		$pmc = $this->portMapEquals($cfg, $client);
		if (!$pmc)
			$asc = false;
		printf(">> %s: %s %s ||eqls P:%s A:%s\n", __FUNCTION__, $this->name, $this->assignment, $pmc ? 'Y' : 'N', $asc ? 'Y' : 'N');

		// before
		if ($this->assignment && !$asc) {
			$this->release();
		}

		if (!$pmc)
			$this->deleteGame();

		if ($cfg->deleted)
			return;

		$cfg->setClient($this->client);

		if (!$pmc)
			$cfg->createApp();

		if ($cfg->assignment && !$asc) {
			$cfg->assign();
		}
	}

	public function createApp()
	{
		if ($this->deleted)
			return;
		printf("%s: %s\n", __FUNCTION__, $this->name);
		$this->client->createGame($this->name);
		foreach ($this->port_map as $m)
			$m->createMapping($this->client, $this->name);
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return null
	 */
	public function getAssignment()
	{
		return $this->assignment;
	}

	/**
	 * @param null $client
	 */
	public function setClient($client)
	{
		$this->client = $client;
	}

	/**
	 * @return boolean
	 */
	public function isDeleted()
	{
		return $this->deleted;
	}
}
