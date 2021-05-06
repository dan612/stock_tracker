<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;
use App\Entity\Stock;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class IndexController extends AbstractController {

  /**
   * Guzzle Http Client
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

  protected $cache;

  protected $portfolio = [
    'DVAX',
    'GT',
    'NET',
    'GE',
    'AXL',
    'LIZI',
    'JETS',
    'F'
  ];

  protected $port_data = [];

  public function __construct() {
    $this->httpClient = new Client();
    $this->cache = new FilesystemAdapter('', 0, "cache");
    $this->setPortData();
  }

  public function setPortData() {
    foreach ($this->portfolio as $ticker) {

      // Create the cache key and get the item.
      $stock_cache_key = "stocks." . $ticker;
      $stock_cache_item = $this->cache->getItem($stock_cache_key);

      // If a cache item is present, use that.
      if ($stock_cache_item->isHit()) {
        $this->port_data[$ticker] = $stock_cache_item->get();
        continue;
      }

      // Create stock entity.
      $stock = new Stock($ticker);
      $price = $stock->getCurrentPrice();
      $last_price = $stock->getLastPrice();
      $change = (($price - $last_price) / $last_price) * 100;

      $price_tgt_key = "price_tgt." . $ticker;
      $price_tgt_item = $this->cache->getItem($price_tgt_key);

      // If a cache item is present, use that.
      if ($price_tgt_item->isHit()) {
        $price_tgt = $price_tgt_item->get();
      }
      else {
        $price_tgt = $stock->getPriceTarget();
        $price_tgt_item->set($price_tgt);
        $price_tgt_item->expiresAfter(86400);
        $this->cache->save($price_tgt_item);
      }

      $data = [
        'last' => $last_price,
        'current' => $price,
        'change' => round($change, 2),
        'price_target' => $price_tgt ?? 1
      ];
      // Set and save a new cache item with this data.
      $stock_cache_item->set($data);
      $stock_cache_item->expiresAfter(3600);
      $this->cache->save($stock_cache_item);

      // Update the stock data.
      $this->port_data[$ticker] = $data;
    }
  }

  public function build() {
    //
    $build = [
      'page_title' => 'Stock Tracker 1.0',
      'port_data' => $this->port_data
    ];
    return $this->render('page.html.twig', $build);
  }
}