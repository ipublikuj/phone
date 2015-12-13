<?php
/**
 * Phone.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Phone!
 * @subpackage	common
 * @since		5.0
 *
 * @date		12.12.15
 */

namespace IPub\Phone;

use Nette;
use Nette\Localization;

use libphonenumber;
use libphonenumber\PhoneNumberFormat;

use IPub;
use IPub\Phone\Exceptions;

class Phone extends Nette\Object
{
	const CLASSNAME = __CLASS__;

	/**
	 * Define phone number types
	 */
	const TYPE_FIXED_LINE	= 'FIXED_LINE';
	const TYPE_MOBILE		= 'MOBILE';
	const TYPE_VOIP			= 'VOIP';
	const TYPE_PAGER		= 'PAGER';
	const TYPE_EMERGENCY	= 'EMERGENCY';
	const TYPE_VOICEMAIL	= 'VOICEMAIL';

	const FORMAT_E164			= PhoneNumberFormat::E164;
	const FORMAT_INTERNATIONAL	= PhoneNumberFormat::INTERNATIONAL;
	const FORMAT_NATIONAL		= PhoneNumberFormat::NATIONAL;
	const FORMAT_RFC3966		= PhoneNumberFormat::RFC3966;

	/**
	 * @var libphonenumber\PhoneNumberUtil
	 */
	protected $phoneNumberUtil;

	/**
	 * @var libphonenumber\geocoding\PhoneNumberOfflineGeocoder
	 */
	protected $phoneNumberGeocoder;

	/**
	 * @var libphonenumber\PhoneNumberToCarrierMapper
	 */
	protected $carrierMapper;

	/**
	 * @var libphonenumber\PhoneNumberToTimeZonesMapper
	 */
	protected $timeZonesMapper;

	/**
	 * @var Localization\ITranslator
	 */
	protected $translator;

	/**
	 * @param libphonenumber\PhoneNumberUtil $phoneNumberUtil
	 * @param libphonenumber\geocoding\PhoneNumberOfflineGeocoder $phoneNumberGeocoder
	 * @param libphonenumber\PhoneNumberToCarrierMapper $carrierMapper
	 * @param libphonenumber\PhoneNumberToTimeZonesMapper $timeZonesMapper
	 * @param Localization\ITranslator $translator
	 */
	public function __construct(
		libphonenumber\PhoneNumberUtil $phoneNumberUtil,
		libphonenumber\geocoding\PhoneNumberOfflineGeocoder $phoneNumberGeocoder,
		libphonenumber\PhoneNumberToCarrierMapper $carrierMapper,
		libphonenumber\PhoneNumberToTimeZonesMapper $timeZonesMapper,
		Localization\ITranslator $translator = NULL
	) {
		// Lib phone library utils
		$this->phoneNumberUtil = $phoneNumberUtil;
		$this->phoneNumberGeocoder = $phoneNumberGeocoder;
		$this->carrierMapper = $carrierMapper;
		$this->timeZonesMapper = $timeZonesMapper;

		// Nette utils
		$this->translator = $translator;
	}

	/**
	 * @param string $number
	 * @param string $country
	 * @param string|NULL $type
	 *
	 * @return bool
	 *
	 * @throws Exceptions\NoValidCountryFoundException
	 * @throws Exceptions\NoValidTypeFoundException
	 */
	public function isValid($number, $country = 'AUTO', $type = NULL)
	{
		// Check if country is valid
		$country = $this->validateCountry($country);

		// Check if phone type is valid
		$type = $type !== NULL ? $this->validateType($type) : NULL;

		try {
			// Parse string into phone number
			$phoneNumber = $this->phoneNumberUtil->parse($number, $country);

			if ($type !== NULL && $this->phoneNumberUtil->getNumberType($phoneNumber) !== $type) {
				return FALSE;
			}

			// Automatic detection:
			if ($country == 'AUTO') {
				// Validate if the international phone number is valid for its contained country
				return (bool) $this->phoneNumberUtil->isValidNumber($phoneNumber);
			}

			// Validate number against the specified country
			return (bool) $this->phoneNumberUtil->isValidNumberForRegion($phoneNumber, $country);

		} catch (libphonenumber\NumberParseException $ex) {
			return FALSE;
		}
	}

	/**
	 * @param string $number
	 * @param string $country
	 * @param int $format
	 *
	 * @return string
	 *
	 * @throws Exceptions\NoValidCountryFoundException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function format($number, $country = 'AUTO', $format = self::FORMAT_INTERNATIONAL)
	{
		// Check if country is valid
		$country = $this->validateCountry($country);

		// Check if phone number is valid
		if ($this->isValid($number, $country)) {
			// Parse string into phone number
			$phoneNumber = $this->phoneNumberUtil->parse($number, $country);
			// Format phone number in given format
			return $this->phoneNumberUtil->format($phoneNumber, $format);

		} else {
			throw new Exceptions\NoValidPhoneException('Provided phone number is not valid!');
		}
	}

	/**
	 * @param string $number
	 * @param string $country
	 * @param string|NULL $locale
	 * @param string|NULL $userCountry
	 *
	 * @return string
	 *
	 * @throws Exceptions\NoValidCountryFoundException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function getLocation($number, $country = 'AUTO', $locale = NULL, $userCountry = NULL)
	{
		// Check if country is valid
		$country = $this->validateCountry($country);

		try {
			// Check for valid user country
			$userCountry = $this->validateCountry($userCountry);

		} catch (Exceptions\NoValidCountryFoundException $ex) {
			$userCountry = NULL;
		}

		// Check if phone number is valid
		if ($this->isValid($number, $country)) {
			// Parse string into phone number
			$phoneNumber = $this->phoneNumberUtil->parse($number, $country);
			// Determine locale
			$locale = $locale === NULL && $this->translator && method_exists($this->translator, 'getLocale') ? $this->translator->getLocale() : 'en_US';
			// Get phone number location
			return $this->phoneNumberGeocoder->getDescriptionForNumber($phoneNumber, $locale, $userCountry);

		} else {
			throw new Exceptions\NoValidPhoneException('Provided phone number is not valid!');
		}
	}

	/**
	 * @param string $number
	 * @param string $country
	 *
	 * @return string
	 *
	 * @throws Exceptions\NoValidCountryFoundException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function getCarrier($number, $country = 'AUTO')
	{
		// Check if country is valid
		$country = $this->validateCountry($country);

		// Check if phone number is valid
		if ($this->isValid($number, $country)) {
			// Parse string into phone number
			$phoneNumber = $this->phoneNumberUtil->parse($number, $country);
			// Extract carrier name from given phone number
			return $this->carrierMapper->getNameForNumber($phoneNumber, 'en');

		} else {
			throw new Exceptions\NoValidPhoneException('Provided phone number is not valid!');
		}
	}

	/**
	 * @param string $number
	 * @param string $country
	 *
	 * @return array
	 *
	 * @throws Exceptions\NoValidCountryFoundException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function getTimeZones($number, $country = 'AUTO')
	{
		// Check if country is valid
		$country = $this->validateCountry($country);

		// Check if phone number is valid
		if ($this->isValid($number, $country)) {
			// Parse string into phone number
			$phoneNumber = $this->phoneNumberUtil->parse($number, $country);
			// Parse time zones from given phone number
			return $this->timeZonesMapper->getTimeZonesForNumber($phoneNumber);

		} else {
			throw new Exceptions\NoValidPhoneException('Provided phone number is not valid!');
		}
	}

	/**
	 * @return Templating\Helpers
	 */
	public function createTemplateHelpers()
	{
		return new Templating\Helpers($this);
	}

	/**
	 * @param string $country
	 *
	 * @return string
	 *
	 * @throws Exceptions\NoValidCountryFoundException
	 */
	protected function validateCountry($country)
	{
		// Country code have to be upper-cased
		$country = strtoupper($country);

		// Correct auto or null value
		if ($country === 'AUTO' || $country === NULL) {
			$country = 'AUTO';
		}

		if ((strlen($country) === 2 && ctype_alpha($country) && ctype_upper($country)) || $country === 'AUTO') {
			return $country;

		} else {
			throw new Exceptions\NoValidCountryFoundException('Provided country code "'. $country .'" is not valid. Provide valid country code or AUTO for automatic detection.');
		}
	}

	/**
	 * @param string $type
	 *
	 * @return int
	 *
	 * @throws Exceptions\NoValidTypeFoundException
	 */
	protected function validateType($type)
	{
		$constant = $this->constructPhoneTypeConstant($type);

		if (defined($constant) && in_array($type, [self::TYPE_FIXED_LINE, self::TYPE_MOBILE, self::TYPE_VOIP, self::TYPE_PAGER, self::TYPE_EMERGENCY, self::TYPE_VOICEMAIL])) {
			return constant($constant);

		} else {
			throw new Exceptions\NoValidTypeFoundException('Provide valid phone number type.');
		}
	}

	/**
	 * Constructs the corresponding namespaced class constant for a phone number type
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	protected function constructPhoneTypeConstant($type)
	{
		return '\libphonenumber\PhoneNumberType::' . $type;
	}
}
