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
	 * @param string $nationalNumber
	 * @param int $countryCode
	 */
	public function __construct($nationalNumber, $countryCode)
	{
		$this->nationalNumber = (string) $nationalNumber;
		$this->countryCode = (int) $countryCode;
	}

	/**
	 * @param int $countryCode
	 *
	 * @return $this
	 */
	public function setCountryCode($countryCode)
	{
		$this->countryCode = (int) $countryCode;

		return $this;
	}

	/**
	 * @return int|NULL
	 */
	public function getCountryCode()
	{
		return $this->countryCode;
	}

	/**
	 * @param string $number
	 *
	 * @return $this
	 */
	public function setNationalNumber($number)
	{
		$this->nationalNumber = (string) $number;

		return $this;
	}

	/**
	 * @return string|NULL
	 */
	public function getNationalNumber()
	{
		return $this->nationalNumber;
	}

	/**
	 * @param string $number
	 *
	 * @return $this
	 */
	public function setInternationalNumber($number)
	{
		$this->internationalNumber = (string) $number;

		return $this;
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
	 * @param string $rawOutput
	 *
	 * @return $this
	 */
	public function setRawOutput($rawOutput)
	{
		$this->rawOutput = (string) $rawOutput;

		return $this;
	}

	/**
	 * @return string|NULL
	 */
	public function getRawOutput()
	{
		return $this->rawOutput;
	}

	/**
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setType($type)
	{
		$this->type = (string) $type;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $carrier
	 *
	 * @return $this
	 */
	public function setCarrier($carrier)
	{
		$this->carrier = $carrier;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCarrier()
	{
		return $this->carrier;
	}

	/**
	 * @param string $country
	 *
	 * @return $this
	 */
	public function setCountry($country)
	{
		$this->country = $country;

		return $this;
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
