<?php
/**
 * UniqueGenerator
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0
 * @author deMagog <seotils@gmail.com>
 *
 */

namespace Seotils\Utilites\Counters;

use Seotils\Traits\DeferredExceptions;
use Seotils\Utilites\Counters\Interfaces\ICounter;

class UniqueGeneratorException extends \Exception {}

class UniqueGenerator {

  protected $alphabet = null;

  protected $counter = null;

  protected $counterWait = 0;

  protected $counterTimeout = 0;

  protected $microtimeMultiplier = 1;

  protected $shardingCount = 0;

  protected $shardingNumber = 0;

  protected $value = null;

  protected $zeroTime = 0;

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

  public function asHex() {

    return $this;
  }

  public function asInt() {

    return $this;
  }

  public function asString() {

    return $this;
  }

  public function generate() {

    return $this;
  }

  public function setAlphabet( $alphabet ) {
    $this->alphabet = $alphabet;
    return $this;
  }

  public function setCounter( ICounter $counter, $wait, $timeout ) {
    $this->counter = $counter;
    $this->counterWait = (int) $wait;
    $timeout && $this->counterTimeout = (int) $timeout;
    return $this;
  }

  public function setSharding( $shardingCount, $shardingNumber ) {
    $this->shardingCount = (int) $shardingCount;
    $this->shardingNumber = (int) $shardingNumber;
    if( $this->shardingCount < 0 || $this->shardingNumber < 0 ) {
      $this->shardingCount = 0;
      $this->shardingNumber = 0;
    }
    return $this;
  }

  public static function fromAlphabet( $value, $alphabet ) {

    return $this;
  }

  public static function fromHex( $value ) {

    return $this;
  }

}