<?php

require_once "DOMWalker.php";

/**
 * class for controling speed touch router
 *
 * @author Krešo Brlek <kbrlek@gmail.com>
 * @copyright Copyright (c) 2002, Krešo Brlek
 * @version 0.0.2
 */
class SpeedTouchManager
{
	private $host;
	private $user;
	private $pass;

	public function __construct($host, $user = null, $pass = null)
	{
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

	public function fetch_url($relative_url = '/', $post_data = null)
	{
		$ch = curl_init();

		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $this->getBaseUrl() . $relative_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		if ($post_data)
		{
			$a = array();
			foreach ($post_data as $k => $v)
				$a[] = $k .'='.urlencode($v);
			$post_data = implode("&", $a);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		}

		// grab URL and pass it to the browser
		$html = curl_exec($ch);
		$header = curl_getinfo($ch);
		curl_close($ch);
		sleep(3);
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

	public function listGames()
	{
		$html = $this->fetch('/cgi/b/games/servdef/');

		$dw = new DOMWalker($html, '//div[@class="contentcontainer"]');
		$rows = $dw->query('//table[@class="edittable"]/tr');
		$rez = array();
		foreach ($rows as $row) {
			/** @var $row DOMElement */
			if ($row->childNodes->length != 5)
				continue;
			$a = array();
			$a['game'] = $row->childNodes->item(0)->nodeValue;
			$a['assignment'] = $row->childNodes->item(1)->nodeValue;
			$a['mode'] = $row->childNodes->item(2)->nodeValue;
			$rez[] = $a;
		}

		return $rez;
	}

	public function createGame($name)
	{
		$this->fetch_url('/cgi/b/games/newserv/', array(
			0 => 10,
			1 => '',
			30 => $name,
			33 => 1
		));
	}
	const PROTO_ANY = 0;
	const PROTO_TCP = 6;
	const PROTO_UDP = 17;

	public function assignPorts($name, $protocol, $from_port, $to_port = '', $dest_port = '', $trigger_prot = self::PROTO_ANY, $trigger_port = '')
	{

		if (!$to_port)
			$to_port = $from_port;

		if (!$dest_port)
			$to_port = $from_port;

		$this->fetch_url('/cgi/b/games/_servconf_/cfg/', array(
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

	public function deleteGame($name)
	{
		$this->fetch_url('/cgi/b/games/servdef/', array(
			0 => 22,
			1 => $name
		));
	}

	public function assignGame($name, $ip, $log = false)
	{
		$this->fetch_url('/cgi/b/games/cfg/', array(
			0 => 19,
			1 => '',
			30 => $name,
			31 => $ip,
			33 => $log ? 1 : 0,
			34 => 1,
			35 => 1,
		));
	}

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


	public function configWLAN($enabled, $ssid, $type, $channel, $allow_multicast, $broadcast_name, $allow, $encryption, $wpa_psk, $wpa_ver)
	{

		$this->fetch_url('/cgi/b/_wli_/cfg/', array(
			0 => 10,
			1 => '',
			31 => $enabled ? 1 : 0,
			//32 => $ssid,
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
		));
	}
}