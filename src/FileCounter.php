<?php
/*
 * FileCounter class.
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0
 * @author deMagog <seotils@gmail.com>
 *
 */

namespace Seotils\Utilites\Counters;

use Seotils\Utilites\Counters\BaseCounter;
use Seotils\Utilites\Counters\Interfaces\ICounter;

class FileCounterException extends \Exception {}

class FileCounter extends BaseCounter {

  /**
   * Counter file path
   *
   * @var string
   */
  protected $path;

  /**
   * Constructor
   *
   * @param string $filePath Path to the counter file.
   *
   * @throws FileCounterException
   */
  public function __construct( $filePath ) {
    if( ! is_string( $filePath) ) {
      $this->exception('Invalid path to the file');
    }
    $this->path = $filePath;
    if( ! file_exists( $filePath)) {
      try {
        $this->save( 0, 0);
      } catch( Exception $exc ) {
        $this->path = null;
        $this->exception("Can`t create a file `{$filePath}`.", 0 , $exc);
      }
    }
  }

  /**
   * Lock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function lock() {
    if( ! $this->captured ) {
      $fData = $this->read();
      list( $captured, $value ) = explode( ':', $fData);
      if( ! (int) $captured ) {
        if( $this->save( true, $value )) {
          $this->oldValue = $value;
          $this->captured = true;
        }
      }
    }
    return $this->captured;
  }

  /**
   * Read counter file contents.
   *
   * @return mixed File contents on success, otherwise FALSE.
   */
  protected function read() {
    $result = false;
    if( $fh = fopen( $this->path, "r")) {
      $result = fread( $fh, filesize( $this->path));
    }
    return $result;
  }

  /**
   * Save data to the counter file.
   *
   * @param bool $state TRUE if locked, otherwise FALSE.
   * @param int $value Value of a counter.
   *
   * @return bool TRUE on success, otherwise FALSE.
   *
   * @throws FileCounterException
   */
  protected function save( $state, $value) {
    if( ! is_string( $this->path ) || empty( $this->path )) {
      $this->exception('Invalid path to the file.');
    }
    $result = false;
    $st = (bool) $state ? 1 : 0;
    $fh = fopen( $this->path, "w");
    if( flock( $fh, LOCK_EX)) {
      fwrite( $fh, $st .':'. $value);
      flock( $fh, LOCK_UN);
      $result = true;
    }
    fclose( $fh );
    return $result;
  }

  /**
   * Unlock the counter.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function unlock() {
    $result = false;
    if( $fData = $this->read()) {
      list( $captured, $value ) = explode( ':', $fData);
      if( $this->save( false, $value )) {
        $result = true;
      }
    }
    return $result;
  }

}
