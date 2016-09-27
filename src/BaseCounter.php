<?php
/*
 * BaseCounter class.
 *
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0
 * @author deMagog <seotils@gmail.com>
 *
 */

namespace Seotils\Utilites\Counters;

use Seotils\Utilites\Counters\Interfaces\ICounter;
use Seotils\Traits\DeferredExceptions;

/**
 * BaseCounter class. Use it as a parent class for your own counters.<p>
 * You must to implement the following methods:<br />
 * ::lock() - lock the counter and sets OldValue.<br />
 * ::save() - save the counter state and value.<br />
 * ::unlock() - unlock the counter.
 * </p>
 */
abstract class BaseCounter implements ICounter {

  /**
   * Is a counter captured (locked)
   *
   * @var boolean
   */
  protected $captured = false;

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
  protected $newValue = null;

  /**
   * Saves a new counter value.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function commit() {
    $result = false;
    if( $this->locked() && null !== $this->newValue ) {
      $this->save( false, $this->newValue);
      $this->reset();
      $result = true;
    }
    return $result;
  }

  /**
   * Generates a new counter value.
   *
   * @param int $baseValue Base (minimal) value for a counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   *
   * @throws mixed
   */
  public function generate( $baseValue = 0 ) {
    $result = false;

    if( ! is_numeric( $baseValue)) {
      $this->exception('Invalid (non-numeric) base value.');
    }

    if( $this->locked() ) {
      $baseValue = (int) $baseValue;
      if( $baseValue > $this->oldValue ) {
        $this->newValue = $baseValue + 1;
      } else {
        $this->newValue = $this->oldValue + 1;
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
   * @param float $timeout The maximum amount of time (in seconds) the counter will attempt to capture a control. Default is 0 (don`t use timeouts).
   *
   * @param int $sleep Delay (in microseconds) between attempts to lock the counter. Default is 1000.
   *
   * @return int Counter value.
   */
  public function getOne( $baseValue = 0, $timeout = 0.0, $sleep = 1000 ) {
    $result = null;

    // Try to lock the counter
    $this->tryToLock( $timeout, $sleep);

    // Generate a value
    if( $this->locked() && $this->generate( (int) $baseValue )) {
      $result = $this->value();
      // Try to commit or rollback changes
      if( ! $this->commit()) {
        $result = null;
        $this->rollback() || $this->unlock();
        $this->exception('Error while try to commit the counter value.');
      }
    }
    return $result;
  }

  /**
   * Lock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   *
   * @throws Exception
   */
  abstract public function lock();

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
    $this->newValue = null;
  }
  /**
   * Rollback value of a counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function rollback() {
    $result = false;
    if( $this->locked() && null !== $this->newValue ) {
      $this->save( false, $this->oldValue);
      $this->reset();
      $result = true;
    }
    return $result;
  }

  /**
   * Sets the counter value.
   *
   * @param int $value New value for a counter.
   *
   * @param float $timeout The maximum amount of time (in seconds) the counter will attempt to capture a control. Default is 0 (don`t use timeouts).
   *
   * @param int $sleep Delay (in microseconds) between attempts to lock the counter. Default is 1000.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   *
   * @throws mixed
   */
  public function setTo( $value, $timeout = 0.0, $sleep = 1000 ) {
    $result = false;

    // Lock the counter
    $this->tryToLock( $timeout, $sleep);

    // Generate a value
    if( $this->locked() ) {
      $this->newValue = (int) $value;
      // Try to commit or rollback changes
      if( $this->commit()) {
        $result = true;
      } else {
        $this->rollback() || $this->unlock();
        $this->exception('Error while try to commit the counter value.');
      }
    }

    return $result;
  }

  /**
   * Saves a current state to the counter storage.
   *
   * @param bool $state TRUE if locked, otherwise FALSE.
   * @param int $value Value of a counter.
   *
   * @return bool TRUE on success, otherwise FALSE.
   */
  abstract protected function save( $state, $value);

  /**
   * Try to lock the counter with timeouts.
   *
   * @param float $timeout The maximum amount of time (in seconds) the counter will attempt to capture a control.
   *
   * @param int $sleep Delay (in microseconds) between attempts to lock the counter.
   *
   * @return void
   *
   * @throws mixed
   */
  protected function tryToLock( $timeout, $sleep) {
    if( ! is_numeric( $timeout)) {
      $this->exception('Invalid (non-numeric) timeout value.');
    }

    if( ! is_numeric( $sleep)) {
      $this->exception('Invalid (non-numeric) sleep value.');
    }

    // Lock the counter
    $mt = microtime( true );
    while( ! $this->locked()) {
      $this->lock();
      if( $this->locked() || microtime( true )  - $mt > (float) $timeout ) {
        break;
      }
      usleep( (int) $sleep );
    }
  }

  /**
   * Returns curent counter value.
   *
   * @return int Counter value or FALSE if a counter has not been locked.
   */
  public function value() {
    return $this->locked() ? $this->newValue : false;
  }

  /**
   * Unlock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  abstract public function unlock();

}
