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
interface IAlphabet {
  const ALPHA_DIGITS_HEX = '0123456789abcdef';
  const ALPHA_DIGITS_LATIN_ALL = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const ALPHA_DIGITS_LATIN_LOWER = '0123456789abcdefghijklmnopqrstuvwxyz';
  const ALPHA_DIGITS_LATIN_UPPER = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const ALPHA_LATIN_ALL = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const ALPHA_LATIN_LOWER = 'abcdefghijklmnopqrstuvwxyz';
  const ALPHA_LATIN_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
}
