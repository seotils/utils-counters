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
   * Capture the counter.
   *
   * @return void
   */
  public function capture();

  /**
   * Release capture.
   *
   * @return void
   */
  public function release();

  /**
   * Returns curent counter value.
   *
   * @return int Counter value.
   */
  public function value();

}
