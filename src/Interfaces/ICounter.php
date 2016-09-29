<?php
/*
 * IUniqueCounter interface
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0
 * @author deMagog <seotils@gmail.com>
 *
 */

namespace Seotils\Utilites\Counters\Interfaces;

/**
 * Interface for a counters classes
 */
interface ICounter {

  /**
   * Saves a new counter value.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function commit();

  /**
   * Generate a new counter value.
   *
   * @param int $baseValue Base (minimal) value for a counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function generate( $baseValue );

  /**
   * Generate and commits new counter value.
   *
   * @param int $baseValue Base (minimal) value for a counter.
   *
   * @param float $timeout The maximum amount of time (in seconds) the counter will attempt to capture a control.
   *
   * @param int $sleep Delay (in microseconds) between attempts to lock the counter.
   *
   * @return int Counter value.
   */
  public function getOne( $baseValue, $timeout, $sleep );

  /**
   * Lock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function lock();

  /**
   * Returns TRUE if counter is locked, otherwise FALSE.
   *
   * @return boolean
   */
  public function locked();

  /**
   * Rollback value of a counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function rollback();

  /**
   * Sets the counter value.
   *
   * @param int $value New value for a counter.
   *
   * @param float $timeout The maximum amount of time (in seconds) the counter will attempt to capture a control.
   *
   * @param int $sleep Delay (in microseconds) between attempts to lock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   *
   * @throws mixed
   */
  public function setTo( $value, $timeout, $sleep);
  
  /**
   * Returns curent counter value.
   * MUST return NULL if counter value are did not generated yet.
   *
   * @return int Counter value or FALSE if a counter has not been locked.
   */
  public function value();

  /**
   * Unlock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function unlock();

}
