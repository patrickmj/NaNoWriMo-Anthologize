<?php

/*
 * append* is for structural data
 * write* is for content (html)
 */

abstract class NaNoWriMoAnthologizer {

	public $api;
	public $useItems = array();
	public $badgeURL = '';
	public $output;


	public function __construct($api, $useItems = false) {
		$this->api = $api;
		$this->setUseItems($useItems);
		$this->init();
		$this->appendFront();
		$this->appendBody();
		$this->finish();

	}

	public function setUseItems($itemsArray = false) {
		if ($itemsArray) {
			$this->useItems = $itemsArray;
		} else {
			$this->useItems = $_SESSION['useItems'];
		}
	}

	abstract function init();

	abstract function appendFront();

	abstract function appendBody();

	abstract function appendPart($section, $partNumber);

	abstract function appendItem($section, $partNumber, $itemNumber);

	abstract function output();

	public function writePartHead($section, $partNumber) {
		$html = '';
		return $html;
	}

	public function writeItemHead($section, $partNumber, $itemNumber) {
		$html = '';
		return $html;
	}

	public function writeItemContent($section, $partNumber, $itemNumber) {
		$html = '';
		return $html;
	}

	public function useItem($guid) {
		return (in_array($guid, $this->useItems));
	}

	public function countItemWords($itemContent) {
		return round(str_word_count($itemContent), -1);
	}

	public function countTotalWords() {
		//get the content of all items
		//concatenate them together
		//count the words
		$contentString = '';
		$contentNodes = $this->api->getNodeListByXPath("//tei:div[@type='libraryItem']/div");
		foreach($contentNodes as $contentNode) {
			$contentString .= ' ' . $contentNode->textContent;
		}
		return round(str_word_count($contentString), -2);
	}
	public function finish() {
		//override me to do any final tasks
	}
}

class NaNoWriMoHTMLer extends NaNoWriMoAnthologizer {

	public $xpath;
	public $totalWords = 0;

	public function __construct($api, $useItems = false) {
		parent::__construct($api, $useItems);
	}

	public function init() {

		//we'll be building the HTML as a DOMDocument
		$this->output = new DOMDocument();
		$html = "<html><head><title></title>";
		$html .= "<style type='text/css'>
				div.title-container {text-align: center;}
				ul, li {list-style: none;}


					";
		$html .= "</head><body><div id='front'></div><div id='body'></body></html>";
		$this->output->loadHTML($html);
		$this->xpath = new DOMXPath($this->output);
		$this->badgeURL = WP_PLUGIN_URL .  "/nanowrimo/images/nanowrimo_participant_09_120x240.png";

	}

	public function appendFront() {
		$frontNode = $this->xpath->query("//div[@id='front']")->item(0);
		$titleContainer = $this->output->createElement('div');
		$titleContainer->setAttribute('class', 'title-container');
		$badge = $this->output->createElement('img');
		$badge->setAttribute('src', $this->badgeURL);
		$badge->setAttribute('class', 'nnwm-badge');
		$anth = $this->output->createElement('img');
		$anth->setAttribute('src', WP_PLUGIN_URL . "/nanowrimo/images/web_badge_square4.jpg");
		$anth->setAttribute('class', 'anth-badge');
		$projectTitle = $this->api->getProjectTitle(true);
		$titleH = $this->output->createElement('h1', $projectTitle);
		$subtitleH = $this->output->createElement('h2', $this->api->getProjectSubTitle(true));
		$subtitleH->setAttribute('class', 'subtitle');

		$titleContainer->appendChild($badge);
		$titleContainer->appendChild($anth);
		$titleContainer->appendChild($titleH);
		$titleContainer->appendChild($subtitleH);
		$frontNode->appendChild($titleContainer);

		$cr = $this->api->getProjectCopyright();
//TODO: fix this in api
		$cr = substr($cr, 43, -7);

		$frontNode->appendChild($this->output->createElement('p', $cr . " 2010"));


		//dedication
		$ded = $this->api->getSectionPartItemContent('front', 0, 0, true);
		$frontNode->appendChild($this->output->importNode($ded, true));
		//acknowledgements
		$ack = $this->api->getSectionPartItemContent('front', 0, 1, true);

		$frontNode->appendChild($this->output->importNode($ack, true));



		$frontNode->appendChild($this->writeTOC());
	}

	public function appendBody() {
		$bodyNode = $this->xpath->query("//div[@id = 'body']")->item(0);
		for($partN = 0; $partN < $this->api->getSectionPartCount('body'); $partN++) {
			$partDiv = $this->output->createElement('div');
			$partDiv->setAttribute('id', "body-$partN");
			$partDiv->setAttribute('class', 'part');
			$partDiv->appendChild($this->writePartHead('body', $partN));

			for($itemN = 0; $itemN < $this->api->getSectionPartItemCount('body', $partN); $itemN++ ) {
				$itemDiv = $this->output->createElement('div');

				$itemDiv->setAttribute('id', "body-$partN-$itemN");
				$itemDiv->setAttribute('class', 'item');
				$itemDiv->appendChild($this->writeItemHead('body', $partN, $itemN));
				$itemDiv->appendChild($this->writeItemContent('body', $partN, $itemN));
				$partDiv->appendChild($itemDiv);
			}
			$bodyNode->appendChild($partDiv);
		}
	}

	public function appendItem($section, $partNumber, $itemNumber) {
		//the HTML output should be nested in divs, rather that linearly as with PDF, so skip this and handle in appendBody
	}
	public function appendPart($section, $partNumber) {
		//the HTML output should be nested in divs, rather that linearly as with PDF, so skip this and handle in appendBody
	}

	public function output() {
		return $this->output->saveHTML();
	}

	public function writePartHead($section, $partN) {
		$partHeaderDiv = $this->output->createElement('div');
		$partHeaderDiv->setAttribute('class', 'part-header');
		$titleH = $this->output->createElement('h2');
		$titleH->setAttribute('class', 'part-title');
		$title = $this->output->importNode($this->api->getSectionPartTitle('body', $partN, true), true );
		$titleH->appendChild($title->firstChild);
		$partHeaderDiv->appendChild($titleH);
		return $partHeaderDiv;

	}

	public function writeItemHead($section, $partN, $itemN) {
		$itemHeaderDiv = $this->output->createElement('div');

		$itemHeaderDiv->setAttribute('class', 'item-header');
		$titleH = $this->output->createElement('h3');
		$titleH->setAttribute('class', 'item-title');
		$title = $this->output->importNode($this->api->getSectionPartItemTitle($section, $partN, $itemN, true), true );
		$titleH->appendChild($title->firstChild);

		$wordCount = $this->countItemWords($this->api->getSectionPartItemContent($section, $partN, $itemN));
		$this->totalWords = $this->totalWords + $wordCount;
		$wcP = $this->output->createElement('p', "About $wordCount words");


		$itemHeaderDiv->appendChild($titleH);
		$itemHeaderDiv->appendChild($wcP);
		return $itemHeaderDiv;
	}

	public function writeItemContent($section, $partN, $itemN) {
		$contentDiv = $this->output->importNode($this->api->getSectionPartItemContent($section, $partN, $itemN, true), true);
		return $contentDiv;
	}

	public function writeTOC() {
		//TODO: also make changes to epub toc.ncx while I'm looping for same data
		$tocDiv = $this->output->createElement('div');
		$tocDiv->setAttribute('class', 'toc');
		$tocDiv->appendChild($this->output->createElement('h2', 'Table of Contents'));
		$partsUL = $this->output->createElement('ul');
		$partsUL->setAttribute('class', 'toc-parts');
		for($partN = 0; $partN < $this->api->getSectionPartCount('body'); $partN++) {
			$partLI = $this->output->createElement('li');
			$partLI->setAttribute('class', 'toc-part');
			$titleH = $this->output->createElement('h3');
			$title = $this->output->importNode($this->api->getSectionPartTitle('body', $partN, true), true );
			$titleH->appendChild($title->firstChild);
			$partLI->appendChild($titleH);
			$itemsUL = $this->output->createElement('ul');
			$itemsUL->setAttribute('class', 'toc-items');
			$partLI->appendChild($itemsUL);
			for($itemN = 0; $itemN < $this->api->getSectionPartItemCount('body', $partN); $itemN++ ) {
				$itemLI = $this->output->createElement('li');
				$itemLI->setAttribute('class', 'toc-item');
				$itemTitleH = $this->output->createElement('h4');
				$itemTitle = $this->output->importNode($this->api->getSectionPartItemTitle('body', $partN, $itemN, true), true );


				$titleA = $this->output->createElement('a');
				$titleA->appendChild($itemTitle->firstChild);
				$titleA->setAttribute('href', "#body-$partN-$itemN");
				$itemTitleH->appendChild($titleA);
				$itemLI->appendChild($itemTitleH);
				$itemsUL->appendChild($itemLI);
			}
		$partsUL->appendChild($partLI);
		}
		$tocDiv->appendChild($partsUL);
		return $tocDiv;
	}

	public function finish() {
		$frontNode = $this->xpath->query("//div[@id='front']")->item(0);
		//$item = $frontNode->childNodes->item(0);
		$words = $this->output->createElement('p', "About " . $this->totalWords . " words");
		$frontNode->firstChild->appendChild($words);

	}
}




