<?php
/*
 * CacheCounter class.
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0
 * @author deMagog <seotils@gmail.com>
 *
 */

namespace Seotils\Utilites\Counters;

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
   * Old counter value
   *
   * @var int
   */
  protected $oldValue = null;

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
   * Saves a new counter value.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function commit() {
    $result = false;

    if( $this->locked() ) {
      $this->save( false, $this->value);
      $this->reset();
      $result = true;
    }

    return $result;
  }

  /**
   * Generate a new counter value.
   *
   * @param int $baseValue Base (minimal) value for a counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   *
   * @throws FileCounterException
   */
  public function generate( $baseValue = 0 ) {
    $result = false;

    if( ! is_numeric( $baseValue)) {
      throw new FileCounterException('Invalid base value');
    }

    if( $this->locked() ) {
      $baseValue = (int) $baseValue;
      if( $baseValue > $this->oldValue ) {
        $this->value = $baseValue + 1;
      } else {
        $this->value = $this->oldValue + 1;
      }
      $result = true;
    }

    return $result;
  }

  /**
   * Generate and commit a new counter value.
   *
   * @param int $baseValue Base (minimal) value for a counter.
   *
   * @param float $timeout The maximum amount of time (in seconds) the counter will attempt to capture a control.
   *
   * @return int Counter value.
   */
  public function getOne( $baseValue = 0, $timeout = 0.0 ) {
    $result = null;

    // Lock the counter
    $mt = microtime( true );
    while( ! $this->locked()) {
      $this->lock();
      if( $this->locked() || microtime( true )  - $mt > $timeout ) {
        break;
      }
      usleep( 100 );
    }

    // Generate a value
    if( $this->locked() && $this->generate( $baseValue )) {
      $result = $this->value();
      // Try to commit or rollback changes
      if( ! $this->commit()) {
        $result = null;
        $this->rollback() || $this->unlock();
      }
    }
    return $result;
  }

  /**
   * Lock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   *
   * @throws FileCounterException
   */
  public function lock() {
    if( ! $this->locked() ) {
      if( $this->save( true, null)) {
        $itemCounter = $this->cache->getItem( $this->keyCounter );
        $this->oldValue = $itemCounter->get();
        if( null === $this->oldValue ) {
          $itemCounter->set( 0 );
          $this->cache->save( $itemCounter );
          $this->oldValue = 0;
        }
      }
    }
    return $this->locked();
  }

  /**
   * Returns TRUE if counter is locked, otherwise FALSE.
   *
   * @return boolean
   */
  public function locked() {
    return $this->captured;
  }

  /**
   * Resets counter to initial state.
   *
   * @return void
   */
  protected function reset() {
    $this->captured = false;
    $this->oldValue = null;
    $this->value = null;
  }

  /**
   * Rollback value of a counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function rollback() {
    $result = false;
    if( $this->locked() ) {
      $this->save( false, $this->oldValue);
      $this->reset();
      $result = true;
    }
    return $result;
  }

  /**
   * Save counter to the cache.
   *
   * @param bool $state TRUE if locked, otherwise FALSE.
   *
   * @param int $value Value of a counter.
   *
   * @return bool TRUE on success, otherwise FALSE.
   */
  protected function save( $state, $value) {
    $result = false;

    // Lock/Unlock counter
    if( $state ) {
      if( ! $this->cache->hasItem( $this->keyLock )) {
        $itemLock = $this->cache->getItem( $this->keyLock );
        $itemLock->set( 1 );
        if( $this->cache->save( $itemLock )) {
          $this->captured = true;
          $result = true;
        }
      }
    } elseif(
         $this->cache->hasItem( $this->keyLock )
      && $this->cache->deleteItem( $this->keyLock )
    ) {
      $this->captured = false;
      $result = true;
    }

    // Save value
    if( null !== $value ) {
      $itemCounter = $this->cache->getItem( $this->keyCounter );
      $itemCounter->set( $value );
      $result = $this->cache->save( $itemCounter );
    }

    return $result;
  }

  /**
   * Returns curent counter value.
   *
   * @return mixed NULL if it not generated, FALSE if counter is not locked, otherwise counter value.
   */
  public function value() {
    return $this->locked() ? $this->value : false;
  }

  /**
   * Unlock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function unlock() {
    return $this->save( false, null);
  }

}
