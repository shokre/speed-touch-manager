<?php

namespace Shokre\SpeedTouch;

use Shokre\SpeedTouch\Utils\DOMWalker;

/**
 * class for controling speed touch router
 *
 * @author Krešo Brlek <kbrlek@gmail.com>
 * @copyright Copyright (c) 2002, Krešo Brlek
 * @version 0.0.2
 */
class Client
{
	private $host;
	private $user;
	private $pass;

	const URL_WLAN = '/cgi/b/_wli_/cfg/';
	const URL_INTF = '/cgi/b/intfs/_intf_/cfg/';
	const URL_GAME_CREATE = '/cgi/b/games/newserv/';
	const URL_GAMES_LIST = '/cgi/b/games/servdef/';
	const URL_GAME_CONF = '/cgi/b/games/_servconf_/cfg/';
	const URL_GAME_DETAILS = '/cgi/b/games/_servconf_/ov/';
	const URL_GAME_ASSIGN = '/cgi/b/games/cfg/';
	const URL_INTERNET = '/cgi/b/is/_pppoe_/ov/';
	const URL_INTERNET_STATE = '/cgi/b/is/';
	const URL_CHANGE_PASS = '/cgi/b/users/cfg/changepsswd/';
	const URL_RESET = '/cgi/b/info/reset/';

	const URL_DEVICES_LIST = '/cgi/b/devs/ov/';

	const TYPE_802_11b = 0;
	const TYPE_802_11b_legacy_g = 1;
	const TYPE_802_11bg = 2;
	const TYPE_802_11g = 3;

	const ALLOW_NONE = 0;
	const ALLOW_AUTO = 1;
	const ALLOW_REGISTER = 2;

	const ENC_DISABLED = 0;
	const ENC_WEP = 1;
	const ENC_WPA = 2;

	const WPA = 1;
	const WPA2 = 2;
	const WPA_WPA2 = 3;

	const PROTO_ANY = 0;
	const PROTO_TCP = 6;
	const PROTO_UDP = 17;

	public static $PROTO_MAP = array(
		'ANY' => self::PROTO_ANY,
		'TCP' => self::PROTO_TCP,
		'UDP' => self::PROTO_UDP,
	);
	private $_blank = '';

	public function __construct($host, $user = null, $pass = null)
	{
		$this->_blank = chr(194) . chr(160);
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
	}

	public function getBaseUrl()
	{
		$s = 'http://';
		if ($this->user)
			$s .= $this->user . ':' . $this->pass . '@';
		$s .= $this->host;
		return $s;
	}

	public function fetch_url($relative_url = '/', $post_data = null, $timeout = null)
	{
		$ch = curl_init();

		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $this->getBaseUrl() . $relative_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		if ($post_data) {
			//print_r($post_data);
			$a = array();
			foreach ($post_data as $k => $v)
				$a[] = $k . '=' . urlencode($v);
			$post_data = implode("&", $a);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		}

		if ($timeout)
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

		// grab URL and pass it to the browser
		$html = curl_exec($ch);
		$header = curl_getinfo($ch);
		curl_close($ch);
		//sleep(3);
		return array($header, $html);
	}

	public function fetch($relative_url = '/')
	{
		list($header, $html) = $this->fetch_url($relative_url);
		if ($header['http_code'] != 200) {
			echo "Failed";
			print_r($header);
		}
		return $html;
	}


	/**
	 * fetch form data from given url
	 */
	public function formData($url)
	{
		$dw = new DOMWalker($this->fetch($url));
		$rows = $dw->query('//input');
		$rez = array();
		foreach ($rows as $row) {
			$k = $row->getAttribute('name');
			$v = $row->getAttribute('value');

			if ($k && $v)
				$rez[$k] = $v;
		}

		return $rez;
	}

	public function postForm($url, $data)
	{
		$d = $this->formData($url);
		if (isset($d[2]))
			$data[2] = $d[2];
		list($header, $html) = $this->fetch_url($url, $data);
		return $header['http_code'] == 200;
	}

	/**
	 * list available services
	 * @return array
	 */
	public function listGames()
	{
		$html = $this->fetch(self::URL_GAMES_LIST);

		$dw = new DOMWalker($html, '//div[@class="contentcontainer"]');
		$rows = $dw->query('//table[@class="edittable"]/tr');
		$rez = array();
		$bl = chr(194) . chr(160);

		foreach ($rows as $row) {
			/** @var $row DOMElement */
			if ($row->childNodes->length != 5)
				continue;
			$a = array();
			$game = $row->childNodes->item(0)->nodeValue;
			$z = $row->childNodes->item(1)->nodeValue;
			//printf("- %s\n", $row->childNodes->item(1)->childNodes->item(0)->getAttribute('href'));
			if ($z == $bl)
				$z = '';
			$a['assignment'] = $z;
			$a['mode'] = $row->childNodes->item(2)->nodeValue;
			$rez[$game] = $a;
		}

		return $rez;
	}

	private function parse_range($s)
	{
		$s = split(' - ', $s);
		foreach ($s as &$v)
			$v *=1;
		return $s;
	}

	public function listGamePorts($name)
	{
		$html = $this->fetch(self::URL_GAME_DETAILS . '?name=' . urlencode($name));

		$dw = new DOMWalker($html, '//div[@class="contentcontainer"]');
		$rows = $dw->query('//table[@class="edittable"]/tr');
		$rez = array();
		$bl = chr(194) . chr(160);

		foreach ($rows as $row) {
			/** @var $row DOMElement */
			if ($row->childNodes->length != 5)
				continue;
			$a = array();
			$a['proto'] = self::$PROTO_MAP[$row->childNodes->item(0)->nodeValue];
			$a['from'] = $this->parse_range($row->childNodes->item(1)->nodeValue);
			$z = $this->parse_range($row->childNodes->item(2)->nodeValue);
			$a['to'] = array_shift($z);
			$rez[] = $a;
		}

		return $rez;
	}

	public function createGame($name)
	{
		$this->postForm(self::URL_GAME_CREATE, array(
			0 => 10,
			1 => '',
			30 => $name,
			33 => 1
		));
	}

	public function deleteGame($name)
	{
		$this->postForm(self::URL_GAMES_LIST, array(
			0 => 22,
			1 => $name,
		));
	}

	public function assignPorts($name, $protocol, $from_port, $to_port = '', $dest_port = '', $trigger_prot = self::PROTO_ANY, $trigger_port = '')
	{

		if (!$to_port)
			$to_port = $from_port;

		if (!$dest_port)
			$dest_port = $from_port;

		$this->postForm(self::URL_GAME_CONF, array(
			0 => 19,
			1 => '',
			30 => $name,
			34 => $protocol,
			39 => $from_port,
			40 => $to_port,
			36 => $dest_port,
			37 => $trigger_prot,
			38 => $trigger_port,
		));
	}

	public function assignGame($name, $ip, $log = false)
	{
		$this->postForm(self::URL_GAME_ASSIGN, array(
			0 => 19,
			1 => '',
			30 => $name,
			31 => $ip,
			33 => $log ? 1 : 0,
			34 => 1,
			35 => 1,
		));
	}

	public function unassignGame($name)
	{
		$this->postForm(self::URL_GAME_ASSIGN, array(
			0 => 22,
			1 => $name,
		));
	}

	public function configUPNP($upnp = true, $extended_security = false)
	{
		$a = array(0 => 26, 1 => '');
		if ($upnp)
			$a[34] = 1;
		if ($extended_security)
			$a[35] = 1;
		$this->postForm(self::URL_GAME_ASSIGN, $a);
	}

	/** local network stuff */
	public function configLocal($ip_address = '192.168.7.254',
								$netmask = '255.255.255.0',
								$auto_ip = false,
								$dhcp_server_enable = false,
								$dhcp_pool_start = '192.168.7.65',
								$dhcp_pool_end = '192.168.7.250',
								$dhcp_lease_days = 1,
								$dhcp_lease_hours = 0,
								$dhcp_lease_mins = 0,
								$dhcp_lease_secs = 0)
	{
		$name = 'LocalNetwork';

		$d = $this->formData(self::URL_INTF . '?be=0&l0=3&l1=1&name=LocalNetwork');

		$a = array(
			0 => 10,
			1 => '',
			5 => 2,
			31 => $name,
			57 => $ip_address,
			58 => $netmask,
			33 => $d[33],
			37 => $dhcp_pool_start,
			38 => $dhcp_pool_end,
			46 => $dhcp_lease_days,
			47 => $dhcp_lease_hours,
			48 => $dhcp_lease_mins,
			49 => $dhcp_lease_secs,
			51 => 'LAN_private',
		);

		if (isset($d[2]))
			$a[2] = $d[2];

		if ($auto_ip) $a[35] = 1;
		if ($dhcp_server_enable) $a[56] = 1;


		return $this->fetch_url(self::URL_INTF, $a, 5);
	}

	public function addRouterIPAddress($ip_address = '192.168.1.254',
									   $netmask = '255.255.255.0')
	{
		return $this->postForm(self::URL_INTF, array(
			0 => 19,
			1 => '',
			5 => 0,
			31 => 'LocalNetwork',
			56 => 1,
			57 => $ip_address,
			58 => $netmask
		));
	}

	public function factoryReset()
	{
		return $this->postForm(self::URL_RESET, array(
			0 => 18,
			1 => '',
		));
	}

	public function services($auto_ip = false,
							 $dhcp_server_enable = false)
	{
		if ($auto_ip) $a[35] = 1;
		if ($dhcp_server_enable) $a[56] = 1;

		return $this->postForm(self::URL_INTF, array(
			0 => 10,
			1 => '',
			5 => 0,
			31 => 'LocalNetwork',
			57 => '',
			58 => '',
		));
	}

	/** internet conectivity */
	public function configInternet($username, $password, $remember_pass = true)
	{
		$a = array(
			0 => 12,
			1 => 'Internet',
			5 => '1',
			30 => $username,
			31 => $password,
		);
		if ($remember_pass)
			$a[32] = 1;
		return $this->postForm(self::URL_INTERNET, $a);
	}

	public function internetState($connected = false)
	{
		return $this->postForm(self::URL_INTERNET_STATE, array(
			0 => $connected ? 12 : 13,
			1 => 'Internet',
			5 => '1',
		));
	}

	/** current user password change */
	public function changePass($old_pass, $new_pass)
	{
		return $this->postForm(self::URL_CHANGE_PASS, array(
			0 => 10,
			1 => 'psswApply',
			42 => 'popup',
			37 => $old_pass,
			38 => $new_pass,
			39 => $new_pass
		));
	}

	public function configWLAN($enabled, $ssid, $type, $channel, $allow_multicast, $broadcast_name, $allow, $encryption, $wpa_psk, $wpa_ver)
	{
		$d = $this->formData(self::URL_WLAN);

		$data = array(
			0 => 10,
			1 => '',
			2 => $d[2],
			31 => $enabled ? 1 : 0,
			32 => 'WLAN: ' . $d[33],
			33 => $ssid,
			34 => $type,
			35 => 0, // auto or manual
			36 => $channel,
			47 => $allow_multicast ? 1 : 0,
			37 => $broadcast_name ? 1 : 0,
			38 => $allow,
			39 => $encryption,
			41 => $wpa_psk,
			44 => $wpa_ver,
			53 => 0
		);

		if (isset($d[2]))
			$data[2] = $d[2];

		foreach ($data as $k => $v)
			$d[$k] = $v;

		ksort($d);

		return $this->fetch_url(self::URL_WLAN, $d);
	}

	public function str($s)
	{
		$s = trim($s);
		if ($s == $this->_blank)
			$s = '';
		return $s;
	}

	public function listDevices()
	{
		$html = $this->fetch(self::URL_DEVICES_LIST);

		$dw = new DOMWalker($html, '//div[@class="contentcontainer"]');
		$rows = $dw->query('//table[@class="edittable"]/tr');
		$rez = array();
		$bl = chr(194) . chr(160);

		foreach ($rows as $row) {
			/** @var $row DOMElement */
			$cn = $row->childNodes;
			$name = $cn->item(0)->nodeValue;

			if (!$name)
				continue;
			$ip = $cn->item(1)->nodeValue;
			$a = array(
				'ip' => $ip,
				'name' => $name,
			);

			$mcs = $dw->query('.//a', $cn->item(0));
			if ($mcs) {
				$h = $mcs[0]->getAttribute('href');
				$mat = array();
				if (preg_match("/key=(([0-9A-F]{2}:){5}([0-9A-F]{2}))'/i", $h, $mat)) {
					$a['mac'] = $mat[1];
				}

			}
			$pv = $this->str($cn->item(2)->nodeValue);
			if ($cn->length == 3)
				$a['port'] = $pv;
			$rez[$name] = $a;
		}

		return $rez;
	}
}
