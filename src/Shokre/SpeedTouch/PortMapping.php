<?php
/* Created: qa 05/03/15 00:51 */

namespace Shokre\SpeedTouch;


class PortMapping
{

	private $type;
	private $from = array();
	private $to = array();

	/**
	 * @param $type - PROTO_*
	 * @param $src - int or array
	 * @param null $dest - int or defaults to $src[0]
	 */
	public function __construct($type, $src, $dest = null)
	{
		$this->type = $type;
		if (!is_array($src))
			$src = array($src, $src);

		if (!$dest)
			$dest = $src[0];

		$this->from = $src;
		$this->to = $dest;
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
