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

use IPub;
use IPub\Phone\Exceptions;

use libphonenumber;
use libphonenumber\PhoneNumberFormat;

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
	 * The RFC3966 number format
	 *
	 * @var string|NULL
	 */
	protected $rfcFormat = NULL;

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
	 * @param string $rfcFormat
	 * @param string $nationalNumber
	 * @param string $internationalNumber
	 * @param int $countryCode
	 * @param string $country
	 * @param string $type
	 * @param string $carrierName
	 */
	public function __construct(
		$rawInput,
		$rfcFormat,
		$nationalNumber,
		$internationalNumber,
		$countryCode,
		$country,
		$type,
		$carrierName
	)
	{
		$this->rawOutput = (string) $rawInput;
		$this->rfcFormat = (string) $rfcFormat;

		$this->nationalNumber = (string) $nationalNumber;
		$this->internationalNumber = (string) $internationalNumber;

		$this->countryCode = (int) $countryCode;
		$this->country = (string) $country;

		$this->type = (string) $type;

		$this->carrier = ($carrierName !== '' && $carrierName !== NULL) ? (string) $carrierName : NULL;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCountryCode()
	{
		return $this->countryCode;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNationalNumber()
	{
		return $this->nationalNumber;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInternationalNumber()
	{
		return $this->internationalNumber;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setExtension($extension)
	{
		$this->extension = (string) $extension;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getExtension()
	{
		return $this->extension;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setItalianLeadingZero($italianLeadingZero)
	{
		$this->italianLeadingZero = (bool) $italianLeadingZero;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getItalianLeadingZero()
	{
		return $this->italianLeadingZero;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setNumberOfLeadingZeros($numberOfLeadingZeros)
	{
		$this->numberOfLeadingZeros = (int) $numberOfLeadingZeros;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNumberOfLeadingZeros()
	{
		return $this->numberOfLeadingZeros;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRawOutput()
	{
		return $this->rawOutput;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRFCFormat()
	{
		return $this->rfcFormat;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCarrier()
	{
		return $this->carrier;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTimeZones(array $timeZones)
	{
		$this->timeZones = $timeZones;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTimeZones()
	{
		return $this->timeZones;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isInTimeZone($timeZone)
	{
		return (bool) in_array($timeZone, $this->timeZones);
	}

	/**
	 * {@inheritdoc}
	 */
	public static function fromNumber($number, $country = 'AUTO')
	{
		$phoneNumberUtil = libphonenumber\PhoneNumberUtil::getInstance();
		$carrierMapper = libphonenumber\PhoneNumberToCarrierMapper::getInstance();
		$timeZonesMapper = libphonenumber\PhoneNumberToTimeZonesMapper::getInstance();

		// Country code have to be upper-cased
		$country = strtoupper($country);

		// Correct auto or null value
		if ($country === 'AUTO' || $country === NULL) {
			$country = 'AUTO';

		} else if (strlen($country) !== 2 || ctype_alpha($country) === FALSE || !in_array($country, $phoneNumberUtil->getSupportedRegions())) {
			throw new Exceptions\NoValidCountryException('Provided country code "' . $country . '" is not valid. Provide valid country code or AUTO for automatic detection.');
		}

		try {
			// Parse string into phone number
			$parsed = $phoneNumberUtil->parse($number, $country);

			// Check if number is valid
			if (($country == 'AUTO' && $phoneNumberUtil->isValidNumber($parsed) === FALSE) || ($country != 'AUTO' && $phoneNumberUtil->isValidNumberForRegion($parsed, $country) === FALSE)) {
				throw new Exceptions\NoValidPhoneException('Provided phone number "' . $number . '" is not valid phone number. Provide valid phone number.');
			}

		} catch (libphonenumber\NumberParseException $ex) {
			switch ($ex->getErrorType()) {
				case libphonenumber\NumberParseException::INVALID_COUNTRY_CODE:
					throw new Exceptions\NoValidCountryException('Missing or invalid country.');

				case libphonenumber\NumberParseException::NOT_A_NUMBER:
					throw new Exceptions\NoValidPhoneException('The string supplied did not seem to be a phone number.');

				case libphonenumber\NumberParseException::TOO_SHORT_AFTER_IDD:
					throw new Exceptions\NoValidPhoneException('Phone number had an IDD, but after this was not long enough to be a viable phone number.');

				case libphonenumber\NumberParseException::TOO_SHORT_NSN:
					throw new Exceptions\NoValidPhoneException('The string supplied is too short to be a phone number.');

				case libphonenumber\NumberParseException::TOO_LONG:
					throw new Exceptions\NoValidPhoneException('The string supplied was too long to parse into phone number.');

				default:
					throw new Exceptions\NoValidPhoneException('Provided phone number "' . $number . '" is not valid phone number. Provide valid phone number.');
			}
		}

		switch ($phoneNumberUtil->getNumberType($parsed)) {
			case libphonenumber\PhoneNumberType::MOBILE:
				$numberType = IPub\Phone\Phone::TYPE_MOBILE;
				break;

			case libphonenumber\PhoneNumberType::FIXED_LINE:
				$numberType = IPub\Phone\Phone::TYPE_FIXED_LINE;
				break;

			case libphonenumber\PhoneNumberType::FIXED_LINE_OR_MOBILE:
				$numberType = IPub\Phone\Phone::TYPE_FIXED_LINE_OR_MOBILE;
				break;

			case libphonenumber\PhoneNumberType::VOIP:
				$numberType = IPub\Phone\Phone::TYPE_VOIP;
				break;

			case libphonenumber\PhoneNumberType::PAGER:
				$numberType = IPub\Phone\Phone::TYPE_PAGER;
				break;

			case libphonenumber\PhoneNumberType::EMERGENCY:
				$numberType = IPub\Phone\Phone::TYPE_EMERGENCY;
				break;

			case libphonenumber\PhoneNumberType::VOICEMAIL:
				$numberType = IPub\Phone\Phone::TYPE_VOICEMAIL;
				break;

			default:
				$numberType = IPub\Phone\Phone::TYPE_UNKNOWN;
				break;
		}

		$entity = new static(
			$phoneNumberUtil->format($parsed, PhoneNumberFormat::E164),
			$phoneNumberUtil->format($parsed, PhoneNumberFormat::RFC3966),
			$phoneNumberUtil->format($parsed, PhoneNumberFormat::NATIONAL),
			$phoneNumberUtil->format($parsed, PhoneNumberFormat::INTERNATIONAL),
			$parsed->getCountryCode(),
			$phoneNumberUtil->getRegionCodeForNumber($parsed),
			$numberType,
			$carrierMapper->getNameForNumber($parsed, 'en') ?: NULL
		);

		$entity->setItalianLeadingZero($parsed->hasItalianLeadingZero());

		$entity->setTimeZones($timeZonesMapper->getTimeZonesForNumber($parsed));

		if ($parsed->hasExtension()) {
			$entity->setExtension($parsed->getExtension());
		}

		if ($parsed->hasNumberOfLeadingZeros()) {
			$entity->setNumberOfLeadingZeros($parsed->getNumberOfLeadingZeros());
		}

		return $entity;
	}

	/**
	 * @return string|NULL
	 */
	public function __toString()
	{
		return (string) $this->rawOutput;
	}
}
