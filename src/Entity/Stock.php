<?php

namespace App\Entity;

use App\TipRanksConnector;
use Facebook\WebDriver\WebDriverBy;
use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Stock {

  public $symbol;
  public $currentPrice;
  public $lastPrice;
  public $reactId;
  public $cache;
  public $pageContent;
  public $client;

  public function __construct($ticker) {
    $this->symbol = $ticker;
    $yahoo_url = "https://finance.yahoo.com/quote/" . $this->symbol;
    $this->client = new Client();
    $this->pageContent = $this->client->get($yahoo_url)->getBody()->getContents();
  }

  // Gets the current price from yahoo finance.
  public function getCurrentPrice() {
    $this->setCurrentPrice();
    return $this->currentPrice;
  }

  // Search for item by react id, which varies.
  public function setCurrentPrice() {
    $this->setReactId($this->symbol, 'current');
    $exp = '/<span class.*data-reactid="' . $this->reactId . '">([0-9]+(.)[0-9]+)<\/span>/';
    preg_match($exp, $this->pageContent, $matches);

    if (empty($matches[1])) {
      $this->currentPrice = '0.00';
    }
    else {
      $this->currentPrice = $matches[1];
      $this->getLastPrice();
    }
  }

  public function getLastPrice() {
    $this->setReactId($this->symbol, 'last');
    $exp = '/<span class.*data-reactid="' . $this->reactId . '">([0-9]+(.)[0-9]+)<\/span>/';
    preg_match($exp, $this->pageContent, $matches);

    if (empty($matches[1])) {
      $this->lastPrice = '0.00';
    }
    else {
      $this->lastPrice = $matches[1];
    }
    return $this->lastPrice;
  }

  public function setReactId($sym, $target) {
    if ($target === 'last') {
      if ($sym === 'JETS') {
        $this->reactId = '44';
      }
      else {
        $this->reactId = '98';
      }
    }
    else {
      if ($sym === 'JETS') {
        $this->reactId = '33';
      }
      else {
        $this->reactId = '50';
      }
    }
  }

  // Get price target uses web-driver. **NEW**
  public function getPriceTarget() {
    // @todo
    // Jets is broken.
    if ($this->symbol === 'JETS') {
      return;
    }
    $tipranks = new TipRanksConnector();
    $tr_url = $tipranks->createUrl($this->symbol);
    $tipranks->driver->get($tr_url);
    $price_target = $tipranks->driver->findElement(
      WebDriverBy::cssSelector('.client-components-stock-research-analysts-price-target-style__actualMoney')
    )->getText();
    $tipranks->closeDriver();

    return $price_target;
  }

}