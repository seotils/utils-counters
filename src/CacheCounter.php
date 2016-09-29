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
use Seotils\Utilites\Counters\BaseCounter;
use Seotils\Utilites\Counters\Interfaces\ICounter;

class CacheCounterException extends \Exception {}

class CacheCounter  extends BaseCounter {

  /**
   * Cache instance
   *
   * @var \Psr\Cache\CacheItemPoolInterface
   */
  protected $cache;

  /**
   * Counter is locked flag. Default FALSE.
   *
   * @var boolean
   */
  protected $fLocked = false;

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
   * Constructor
   *
   * @param CacheItemPoolInterface $cacheInstance Cache instance.
   * @param string $keyPrefix Prefix for cache key names.
   *
   * @throws CacheCounterException
   */
  public function __construct( CacheItemPoolInterface $cacheInstance, $keyPrefix) {
    if( empty( $keyPrefix ) || ! is_string( $keyPrefix)) {
      $this->exception('Invalid key prefix.');
    }
    $this->cache = $cacheInstance;
    $this->keyLock = $keyPrefix . 'Lock';
    $this->keyCounter = $keyPrefix . 'Counter';
  }

  /**
   * Lock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
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
    return $this->fLocked;
  }

  /**
   * Resets counter to initial state.
   *
   * @return void
   */
  protected function reset() {
    $this->fLocked = false;
    parent::reset();
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
          $this->fLocked = true;
          $result = true;
        }
      }
    } elseif(
         $this->cache->hasItem( $this->keyLock )
      && $this->cache->deleteItem( $this->keyLock )
    ) {
      $this->fLocked = false;
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
   * Unlock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function unlock() {
    return $this->save( false, null);
  }

}
