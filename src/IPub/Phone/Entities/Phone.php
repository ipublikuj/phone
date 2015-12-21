<?php
/**
 * Phone.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Phone!
 * @subpackage     Entities
 * @since          1.0.1
 *
 * @date           17.12.15
 */

namespace IPub\Phone\Entities;

use Nette;

class Phone extends Nette\Object
{
	/**
	 * The country code
	 *
	 * @var int|NULL
	 */
	protected $countryCode = NULL;

	/**
	 * The national number
	 *
	 * @var string|NULL
	 */
	protected $nationalNumber = NULL;

	/**
	 * The international number
	 *
	 * @var string|NULL
	 */
	protected $internationalNumber = NULL;

	/**
	 * The extension
	 *
	 * @var string|NULL
	 */
	protected $extension = NULL;

	/**
	 * Whether this phone number uses an italian leading zero
	 *
	 * @var bool
	 */
	protected $italianLeadingZero = FALSE;

	/**
	 * The number of leading zeros of this phone number
	 *
	 * @var int|NULL
	 */
	protected $numberOfLeadingZeros;

	/**
	 * The raw input
	 *
	 * @var string|NULL
	 */
	protected $rawOutput = NULL;

	/**
	 * Phone number type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Carrier name
	 *
	 * @var string
	 */
	protected $carrier;

	/**
	 * Country name
	 *
	 * @var string
	 */
	protected $country;

	/**
	 * List of time zones
	 *
	 * @var array
	 */
	protected $timeZones = [];

	/**
	 * @param string $rawInput
	 * @param string $nationalNumber
	 * @param string $internationalNumber
	 * @param int $countryCode
	 * @param string $country
	 * @param string $type
	 * @param string $carrierName
	 */
	public function __construct(
		$rawInput,
		$nationalNumber,
		$internationalNumber,
		$countryCode,
		$country,
		$type,
		$carrierName
	) {
		$this->rawOutput = (string) $rawInput;

		$this->nationalNumber = (string) $nationalNumber;
		$this->internationalNumber = (string) $internationalNumber;

		$this->countryCode = (int) $countryCode;
		$this->country = (string) $country;

		$this->type = (string) $type;

		$this->carrier = (string) $carrierName;
	}

	/**
	 * @return int|NULL
	 */
	public function getCountryCode()
	{
		return $this->countryCode;
	}

	/**
	 * @return string|NULL
	 */
	public function getNationalNumber()
	{
		return $this->nationalNumber;
	}

	/**
	 * @return string|NULL
	 */
	public function getInternationalNumber()
	{
		return $this->internationalNumber;
	}

	/**
	 * @param string $extension
	 *
	 * @return $this
	 */
	public function setExtension($extension)
	{
		$this->extension = (string) $extension;

		return $this;
	}

	/**
	 * @return string|NULL
	 */
	public function getExtension()
	{
		return $this->extension;
	}

	/**
	 * @param bool $italianLeadingZero
	 *
	 * @return $this
	 */
	public function setItalianLeadingZero($italianLeadingZero)
	{
		$this->italianLeadingZero = (bool) $italianLeadingZero;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function getItalianLeadingZero()
	{
		return $this->italianLeadingZero;
	}

	/**
	 * @param int $numberOfLeadingZeros
	 *
	 * @return $this
	 */
	public function setNumberOfLeadingZeros($numberOfLeadingZeros)
	{
		$this->numberOfLeadingZeros = (int) $numberOfLeadingZeros;

		return $this;
	}

	/**
	 * @return int|NULL
	 */
	public function getNumberOfLeadingZeros()
	{
		return $this->numberOfLeadingZeros;
	}

	/**
	 * @return string|NULL
	 */
	public function getRawOutput()
	{
		return $this->rawOutput;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getCarrier()
	{
		return $this->carrier;
	}

	/**
	 * @return string
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * @param array $timeZones
	 *
	 * @return $this
	 */
	public function setTimeZones(array $timeZones)
	{
		$this->timeZones = $timeZones;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getTimeZones()
	{
		return $this->timeZones;
	}

	/**
	 * @param string $timeZone
	 *
	 * @return bool
	 */
	public function isInTimeZone($timeZone)
	{
		return (bool) in_array($timeZone, $this->timeZones);
	}

	/**
	 * @return string|NULL
	 */
	public function __toString()
	{
		return (string) $this->internationalNumber;
	}
}
