<?php
/*
 * CacheCounter class.
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0
 * @author deMagog <seotils@gmail.com>
 *
 */

namespace Seotils\Utilites\Counters;

use Seotils\Utilites\Counters\BaseCounter;

class OnetimeCounterException extends \Exception {}

class OnetimeCounter  extends BaseCounter {

  /**
   * Counter storage.
   *
   * @var int
   */
  protected static $counter = 0;

  /**
   * Counter is locked flag. Default FALSE.
   *
   * @var boolean
   */
  protected $fLocked = false;

  /**
   * Lock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function lock() {
    return $this->fLocked = true;
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
    $this->fLocked = (bool) $state;
    self::$counter = (int) $value;
    return true;
  }

  /**
   * Unlock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function unlock() {
    $this->fLocked = false;
  }

}
