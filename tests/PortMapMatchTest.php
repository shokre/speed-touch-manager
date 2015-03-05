<?php

use Shokre\SpeedTouch\Client;
use Shokre\SpeedTouch\GameInfo;


class MoneyTest extends PHPUnit_Framework_TestCase
{

	public function testIdentity()
	{
		$g1 = GameInfo::create('s1')
			->map(Client::PROTO_TCP, array(83, 87), 80);
		$this->assertTrue($g1->portMapEquals($g1));
	}

	public function testTwo()
	{
		$g1 = GameInfo::create('s1')
			->map(Client::PROTO_TCP, array(83, 87), 80);
		$g2 = GameInfo::create('s1')
			->map(Client::PROTO_TCP, array(83, 87), 80);

		$this->assertTrue($g1->portMapEquals($g2));
		$this->assertTrue($g2->portMapEquals($g1));
	}

	public function testDiff()
	{
		$g1 = GameInfo::create('s1')
			->map(Client::PROTO_TCP, array(83, 87), 80)
			->map(Client::PROTO_TCP, array(89, 95), 90);
		$g2 = GameInfo::create('s1')
			->map(Client::PROTO_TCP, array(83, 87), 80);

		$this->assertFalse($g1->portMapEquals($g2));
		$this->assertFalse($g2->portMapEquals($g1));
	}
}
/*

$g1 = GameInfo::create('s1')
	->map(Client::PROTO_TCP, array(83, 87), 80);
$g2 = GameInfo::create('s1')
	->map(Client::PROTO_TCP, array(83, 87), 80);




$g1->portDiff($g2);
*/
