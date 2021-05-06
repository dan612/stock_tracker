<?php

namespace App;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use GuzzleHttp\Client;

class TipRanksConnector {

  public $client;
  public $driver;

  CONST TIPRANKS = 'https://www.tipranks.com/stocks/';
  CONST SERVER = 'http://localhost:4444';

  public function __construct() {
    $desiredCapabilities = DesiredCapabilities::chrome();
    $chromeOptions = new ChromeOptions();
    $chromeOptions->addArguments(['-headless']);
    $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
    // Chrome
    $this->driver = RemoteWebDriver::create(self::SERVER, $desiredCapabilities);
  }

  public function createUrl($ticker) {
    $url = self::TIPRANKS . $ticker . '/forecast';
    return $url;
  }

  public function closeDriver() {
    $this->driver->close();
  }

}