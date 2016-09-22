<?php
/*
 * FileCounter class.
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0
 * @author deMagog <seotils@gmail.com>
 *
 */

namespace Seotils\Utilites\Counters;

use Seotils\Utilites\Counters\Interfaces\ICounter;

class FileCounterException extends \Exception {}

class FileCounter implements ICounter {

  /**
   * Is a counter captured
   *
   * @var boolean
   */
  protected $captured = false;

  /**
   * Counter file path
   *
   * @var string
   */
  protected $path;

  /**
   * Counter value
   *
   * @var int
   */
  protected $value = null;

  /**
   * Constructor
   *
   * @param string $filePath Path to the counter file.
   *
   * @throws FileCounterException
   */
  public function __construct( $filePath ) {
    if( ! is_string( $filePath) ) {
      throw new FileCounterException( 'Invalid path to the file');
    }
    if( ! file_exists( $filePath)) {
      try {
        file_put_contents( $filePath, '0:0');
      } catch( Exception $exc ) {
        throw new FileCounterException( 'Invalid path to the file', 0 , $exc);
      }
    }
    $this->path = $filePath;
  }

  /**
   * Capture the counter.
   *
   * @return void
   */
  public function capture() {
    if( ! $this->captured ) {
      $fData = file_get_contents( $this->path );
      list( $captured, $value ) = explode( ':', $fData);
      if( ! (int) $captured ) {
        $this->value = (int) $value + 1;
        file_put_contents( $this->path, '1:' . $this->value);
        $this->captured = true;
      }
    }
    return $this->captured;
  }

  /**
   * Release capture.
   *
   * @return void
   */
  public function release() {
    if( $this->captured ) {
      file_put_contents( $this->path, '0:' . $this->value);
      $this->captured = false;
    }
    return ! $this->captured;
  }

  /**
   * Returns curent counter value.
   *
   * @return int Counter value.
   */
  public function value() {
    return $this->captured ? $this->value : false;
  }

}
