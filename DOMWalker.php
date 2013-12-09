<?php

/**
 * class for walking trough HTML documents
 *
 * @author Krešo Brlek <kbrlek@gmail.com>
 * @copyright Copyright (c) 2002, Krešo Brlek
 * @version 0.0.2
 */
class DOMWalker
{

	private $dom;
	private $xpath;
	private $content_node;

	function __construct($html, $context = null)
	{
		$this->dom = $this->makeDOM($html);
		$this->xpath = new DOMXPath($this->dom);
		$this->content_node = null;
		if ($context) {
			$rez = $this->xpath->query($context);
			$this->content_node = $rez->item(0);
		}
		else
			$this->content_node = null;
	}

	function query($xp)
	{
		$rez = $this->xpath->query($xp, $this->content_node);
		return iterator_to_array($rez);
	}

	public function makeDOM($fp, $validateOnParse = false)
	{
		$doc = new DOMDocument();

		// no whitespace
		$doc->validateOnParse = $validateOnParse;
		$doc->preserveWhiteSpace = false;
		$doc->strinctErrorChecking = false;
		//$doc->encoding = "utf8";

		// disable warnings because of broken HTML files
		$err = ini_get("error_reporting");
		ini_set("error_reporting", "E_ALL & ~( E_NOTICE | E_STRICT | E_WARNING )");

		if (is_array($fp))
			$fp = implode('', $fp);

		$doc->loadHTML($fp);

		// reenable warnings
		ini_set("error_reporting", $err);

		return $doc;
	}
}