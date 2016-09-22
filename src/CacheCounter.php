<?php
/*
 * CacheCounter class.
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0
 * @author deMagog <seotils@gmail.com>
 *
 */

namespace Seotils\Utilites\Counters;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Seotils\Utilites\Counters\Interfaces\ICounter;

class CacheCounterException extends \Exception {}

class CacheCounter implements ICounter {

  /**
   * Cache instance
   *
   * @var \Psr\Cache\CacheItemPoolInterface
   */
  protected $cache;

  /**
   * Is a counter captured
   *
   * @var boolean
   */
  protected $captured = false;

  /**
   * Counter key name
   *
   * @var string
   */
  protected $keyCounter;

  /**
   * Lock key name
   *
   * @var string
   */
  protected $keyLock;

  /**
   * Counter value
   *
   * @var int
   */
  protected $value = null;

  /**
   * Constructor
   *
   * @param CacheItemPoolInterface $cacheInstance Cache instance.
   * @param string $keyPrefix Prefix for cache key names.
   *
   * @throws CacheCounterException
   */
  public function __construct( CacheItemPoolInterface $cacheInstance, $keyPrefix) {
    if( empty( $keyPrefix ) || ! is_string( $keyPrefix)) {
      throw new CacheCounterException('Invalid key prefix.');
    }
    $this->cache = $cacheInstance;
    $this->keyLock = $keyPrefix . 'Lock';
    $this->keyCounter = $keyPrefix . 'Counter';
  }

  /**
   * Capture the counter.
   * @return void
   */
  public function capture() {
    if( ! $this->captured ) {
      if( ! $this->cache->hasItem( $this->keyLock )) {
        /* @var $item \Psr\Cache\CacheItemInterface */
        // In theory there are may be double access to value
        $itemLock = $this->cache->getItem( $this->keyLock );
        $itemLock->set( 1 );
        $itemCounter = $this->cache->getItem( $this->keyCounter );
        if( null === $itemCounter->get()) {
          $itemCounter->set( 0 );
        }
        $this->value = (int) $itemCounter->get() + 1;
        $itemCounter->set( $this->value );
        $this->captured = true;
      }
    }
    return $this->captured;
  }

  /**
   * Release capture.
   * @return void
   * @throws CacheCounterException
   */
  public function release() {
    if( $this->captured ) {
      if( $this->cache->hasItem( $this->keyLock )) {
        if( ! $this->cache->deleteItem( $this->keyLock )) {
          throw new CacheCounterException("Can`t delete `{$this->keyLock}` key from the cache.");
        }
        $this->captured = false;
      } else {
        throw new CacheCounterException("The counter is captured, but key `{$this->keyLock}` is not in the cache.");
      }
    }
    return ! $this->captured;
  }

  /**
   * Returns curent counter value
   * @return int Counter value.
   */
  public function value() {
    return $this->captured ? $this->value : false;
  }

}
