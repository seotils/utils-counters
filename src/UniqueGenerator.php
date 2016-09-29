<?php
/**
 * UniqueGenerator
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0
 * @author deMagog <seotils@gmail.com>
 *
 */

namespace Seotils\Utilites\Counters;

use Seotils\Utilites\Alphabet;
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

  protected $zeroTime;

  /**
   * Constructor.
   *
   * @param int $zeroTime The basic time reference for the generation of value.
   *
   * @throws FileCounterException
   */
  public function __construct( $zeroTime = 0 ) {
    $this->zeroTime =
      (int) $zeroTime > 0
        ? (int) $zeroTime
        : 0;
  }

  public function asHex() {
    $symbols = IAlphabet::ALPHA_DIGITS_HEX;
    $radix = mb_strlen( $symbols, 'utf-8' );
    return $this->notation( $this->value, $radix, $symbols);
  }

  public function asInt() {
    return $this->value;
  }

  public function asString() {
    $symbols =
      $this->alphabet && ! empty( $this->alphabet )
      ? $this->alphabet
      : Alphabet::ALPHA_DIGITS_LATIN_ALL;
    $radix = mb_strlen( $symbols, 'utf-8' );
    return $this->notation( $this->value, $radix, $symbols);
  }

  public function generate() {
    $this->value = time() - $this->zeroTime;
    return $this;
  }

  /**
   * Sets aviable symbols to represent value as a string.
   *
   * @param string $alphabet Aviable symbols.
   *
   * @return boolean TRUE on success, otherwise FALSE.
   */
  public function setAlphabet( $alphabet ) {
    $result = false;
    if( self::alphabetIsUnique( $alphabet )) {
      $this->alphabet = $alphabet;
      $result = true;
    }
    return $result;
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