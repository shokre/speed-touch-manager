<?php

require_once "vendor/autoload.php";

use Shokre\SpeedTouch\Client;
use Shokre\SpeedTouch\GameInfo;
use Shokre\SpeedTouch\Configurator;


$new_pass = 'notHolNeb1';
$dest = '192.168.1.2';
$dest2 = '192.168.1.12';
$router_ip = '192.168.1.254';
$stm = new Client($router_ip, 'user', $new_pass);

$cf = new Configurator($stm);

$cf->configure(array(
	GameInfo::create('www80', $dest)->absent(),
		//->map(Client::PROTO_TCP, 99, 92),
	GameInfo::create('proba', $dest)->absent(),
		//->map(Client::PROTO_TCP, 56802, 9872),
	GameInfo::create('DNS', $dest)->map(Client::PROTO_ANY, 53),
	//GameInfo::create('git', $dest)->map(Client::PROTO_TCP, 9418),
	GameInfo::create('tinc', $dest)->map(Client::PROTO_TCP, 655),
	GameInfo::create('torz', $dest)->map(Client::PROTO_TCP, array(28381, 28389)),
	//GameInfo::create('www88', $dest)->map(Client::PROTO_TCP, 88),
	GameInfo::create('www', $dest)->map(Client::PROTO_TCP, 80),
	GameInfo::create('ssh-gitlab', $dest)->map(Client::PROTO_TCP, 22, 10022),
	GameInfo::create('salt', $dest)->map(Client::PROTO_TCP, array(4505,4506)),
	GameInfo::create('SMTP', $dest)->map(Client::PROTO_TCP, 25),
	/*GameInfo::create('IPSEC vpn', $dest)
		->map(Client::PROTO_ANY, 500)
		->map(Client::PROTO_ANY, 4500)
		->map(Client::PROTO_ANY, 1701),*/

));

