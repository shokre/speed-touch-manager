<?php
/* Created: qa 05/03/15 00:37 */

namespace Shokre\SpeedTouch;


class GameInfo
{
	private $name;
	private $assignment;
	private $port_map = null;

	/**
	 * GameInfo constructor.
	 * @param $name
	 */
	public function __construct($name, $assignment = null, $exists = false)
	{
		$this->name = $name;
		$this->assignment = $assignment;
		$this->exists = $exists;
		if (!$this->exists)
			$this->port_map = array();
	}

	public static function create($name)
	{
		return new GameInfo($name);
	}

	private function isMapLoaded()
	{
		return $this->port_map !== null;
	}

	public function map($name, $from, $to)
	{
		$pm = new PortMapping($name, $from, $to);
		$this->port_map[] = $pm;
		return $this;
	}

	public function getMap($stm)
	{
		if (!$this->isMapLoaded() && $stm) {
			$this->port_map = PortMapping::mapFromArray($stm->listGamePorts($this->name));
		}
		return $this->port_map;
	}

	public static function fromClient($stm, $name)
	{
		$gi = self::create($name);
	}

	public function portMapEquals($other)
	{
		$map = $this->getMap(null);
		$omap = $other->getMap(null);

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
}
