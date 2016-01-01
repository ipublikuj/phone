<?php
/**
 * IPhone.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Phone!
 * @subpackage     Entities
 * @since          1.1.5
 *
 * @date           01.01.16
 */

namespace IPub\Phone\Entities;

use Nette;

use IPub;
use IPub\Phone\Exceptions;

interface IPhone
{
	/**
	 * @return int|NULL
	 */
	public function getCountryCode();

	/**
	 * @return string|NULL
	 */
	public function getNationalNumber();

	/**
	 * @return string|NULL
	 */
	public function getInternationalNumber();

	/**
	 * @param string $extension
	 *
	 * @return $this
	 */
	public function setExtension($extension);

	/**
	 * @return string|NULL
	 */
	public function getExtension();

	/**
	 * @param bool $italianLeadingZero
	 *
	 * @return $this
	 */
	public function setItalianLeadingZero($italianLeadingZero);

	/**
	 * @return bool
	 */
	public function getItalianLeadingZero();

	/**
	 * @param int $numberOfLeadingZeros
	 *
	 * @return $this
	 */
	public function setNumberOfLeadingZeros($numberOfLeadingZeros);

	/**
	 * @return int|NULL
	 */
	public function getNumberOfLeadingZeros();

	/**
	 * @return string|NULL
	 */
	public function getRawOutput();

	/**
	 * @return string|NULL
	 */
	public function getRFCFormat();

	/**
	 * @return string
	 */
	public function getType();

	/**
	 * @return string|NULL
	 */
	public function getCarrier();

	/**
	 * @return string
	 */
	public function getCountry();

	/**
	 * @param array $timeZones
	 *
	 * @return $this
	 */
	public function setTimeZones(array $timeZones);

	/**
	 * @return array
	 */
	public function getTimeZones();

	/**
	 * @param string $timeZone
	 *
	 * @return bool
	 */
	public function isInTimeZone($timeZone);

	/**
	 * @param string $number
	 * @param string $country
	 *
	 * @return static
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public static function fromNumber($number, $country = 'AUTO');
}
