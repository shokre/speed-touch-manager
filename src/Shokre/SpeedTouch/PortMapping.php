<?php
/* Created: qa 05/03/15 00:51 */

namespace Shokre\SpeedTouch;


class PortMapping
{

	private $type;
	private $from = array();
	private $to = array();

	/**
	 * PortMapping constructor.
	 */
	public function __construct($type, $from, $to = null)
	{
		$this->type = $type;
		if (!is_array($from))
			$from = array($from, $from);

		if (!$to)
			$to = $from;

		if (!is_array($to))
			$to = array($to, $to);

		$this->from = $from;
		$this->to = $to;
	}

	public static function mapFromArray($its)
	{
		$r = array();
		foreach ($its as $a)
			$r[] = new self($a['proto'], $a['from'], $a['to']);
		return $r;
	}

	public function equals($o)
	{
		return ($this->type == $o->type) && ($this->from == $o->from) && ($this->to == $o->to);

	}
}
