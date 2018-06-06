<?php
/**
 * Phone.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Phone!
 * @subpackage     common
 * @since          1.0.0
 *
 * @date           12.12.15
 */

declare(strict_types = 1);

namespace IPub\Phone;

use Nette;
use Nette\Localization;

use libphonenumber;
use libphonenumber\PhoneNumberFormat;

use IPub;
use IPub\Phone\Entities;
use IPub\Phone\Exceptions;

/**
 * Phone number helpers
 *
 * @package        iPublikuj:Phone!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Phone
{
    use Nette\SmartObject;

	/**
	 * Define phone number types
	 */
	const TYPE_FIXED_LINE = 'FIXED_LINE';
	const TYPE_MOBILE = 'MOBILE';
	const TYPE_FIXED_LINE_OR_MOBILE = 'FIXED_LINE_OR_MOBILE';
	const TYPE_VOIP = 'VOIP';
	const TYPE_PAGER = 'PAGER';
	const TYPE_EMERGENCY = 'EMERGENCY';
	const TYPE_VOICEMAIL = 'VOICEMAIL';
	const TYPE_UNKNOWN = 'UNKNOWN';

	const FORMAT_E164 = PhoneNumberFormat::E164;
	const FORMAT_INTERNATIONAL = PhoneNumberFormat::INTERNATIONAL;
	const FORMAT_NATIONAL = PhoneNumberFormat::NATIONAL;
	const FORMAT_RFC3966 = PhoneNumberFormat::RFC3966;

	/**
	 * @var libphonenumber\PhoneNumberUtil
	 */
	private $phoneNumberUtil;

	/**
	 * @var libphonenumber\geocoding\PhoneNumberOfflineGeocoder
	 */
	private $phoneNumberGeocoder;

	/**
	 * @var libphonenumber\PhoneNumberToCarrierMapper
	 */
	private $carrierMapper;

	/**
	 * @var libphonenumber\PhoneNumberToTimeZonesMapper
	 */
	private $timeZonesMapper;

	/**
	 * @var Localization\ITranslator
	 */
	private $translator;

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
	)
	{
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
	 *
	 * @return Entities\Phone
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function parse(string $number, string $country = 'AUTO') : Entities\Phone
	{
		// Parse string into phone number
		return Entities\Phone::fromNumber($number, $country);
	}

	/**
	 * @param string|Entities\Phone $number
	 * @param string $country
	 * @param string|NULL $type
	 *
	 * @return bool
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidTypeException
	 */
	public function isValid(string $number, string $country = 'AUTO', $type = NULL) : bool
	{
		// Check if country is valid
		$country = $this->validateCountry($country);

		// Check if phone type is valid
		$type = $type !== NULL ? $this->validateType($type) : NULL;

		try {
			// Parse string into phone number
			$phoneNumber = $this->phoneNumberUtil->parse((string) $number, $country);

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
	 * @return string|NULL
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function format(string $number, string $country = 'AUTO', int $format = self::FORMAT_INTERNATIONAL)
	{
		// Create phone entity
		$entity = Entities\Phone::fromNumber($number, $country);

		switch ($format) {
			case self::FORMAT_INTERNATIONAL:
				return $entity->getInternationalNumber();

			case self::FORMAT_NATIONAL:
				return $entity->getNationalNumber();

			case self::FORMAT_E164:
				return $entity->getRawOutput();

			case self::FORMAT_RFC3966:
				return $entity->getRFCFormat();

			default:
				throw new Exceptions\InvalidArgumentException('Invalid number format given, provide valid phone number format.');
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
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function getLocation(string $number, string $country = 'AUTO', string $locale = NULL, string $userCountry = NULL) : string
	{
		if ($this->isValid((string) $number, $country)) {
			$country = strtoupper($country);

			if ($userCountry !== NULL) {
				// Check for valid user country
				$userCountry = $this->validateCountry($userCountry);
			}

			// Parse phone number
			$parsed = $this->phoneNumberUtil->parse((string) $number, $country);

			// Determine locale
			$locale = $locale === NULL && $this->translator && method_exists($this->translator, 'getLocale') ? $this->translator->getLocale() : 'en_US';

			// Get phone number location
			return $this->phoneNumberGeocoder->getDescriptionForNumber($parsed, $locale, $userCountry);

		} else {
			throw new Exceptions\NoValidPhoneException(sprintf('Provided phone number "%s" is not valid phone number. Provide valid phone number.', $number));
		}
	}

	/**
	 * @param string $number
	 * @param string $country
	 *
	 * @return string|NULL
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function getCarrier(string $number, string $country = 'AUTO')
	{
		// Create phone entity
		$entity = Entities\Phone::fromNumber($number, $country);

		// Extract carrier name from given phone number
		return $entity->getCarrier();
	}

	/**
	 * @param string $number
	 * @param string $country
	 *
	 * @return array
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function getTimeZones(string $number, string $country = 'AUTO') : array
	{
		// Create phone entity
		$entity = Entities\Phone::fromNumber($number, $country);

		// Extract carrier name from given phone number
		return $entity->getTimeZones();
	}

	/**
	 * Get list of library supported countries
	 *
	 * @return array
	 */
	public function getSupportedCountries() : array
	{
		return $this->phoneNumberUtil->getSupportedRegions();
	}

	/**
	 * Get dialing country code for provided country
	 *
	 * @param string $country
	 *
	 * @return int
	 *
	 * @throws Exceptions\NoValidCountryException
	 */
	public function getCountryCodeForCountry(string $country) : int
	{
		// Check if country is valid
		$country = $this->validateCountry($country);

		// Transform country to country code
		$code = $this->phoneNumberUtil->getCountryCodeForRegion($country);

		if ($code !== 0) {
			return $code;

		} else {
			throw new Exceptions\NoValidCountryException(sprintf('Provided country code "%s" is not valid. Provide valid country code.', $country));
		}
	}

	/**
	 * Get example country national number
	 *
	 * @param string $country
	 * @param string $type
	 *
	 * @return string
	 *
	 * @throws Exceptions\NoValidCountryException
	 */
	public function getExampleNationalNumber(string $country, $type = self::TYPE_FIXED_LINE) : string
	{
		return $this->getExampleNumber($country, PhoneNumberFormat::NATIONAL, $type);
	}

	/**
	 * Get example country international number
	 *
	 * @param string $country
	 * @param string $type
	 *
	 * @return string
	 *
	 * @throws Exceptions\NoValidCountryException
	 */
	public function getExampleInternationalNumber(string $country, $type = self::TYPE_FIXED_LINE) : string
	{
		return $this->getExampleNumber($country, PhoneNumberFormat::INTERNATIONAL, $type);
	}

	/**
	 * @param string $country
	 * @param int $format
	 * @param string $type
	 *
	 * @return string|NULL
	 *
	 * @throws Exceptions\NoValidCountryException
	 */
	private function getExampleNumber(string $country, int $format, $type = self::TYPE_FIXED_LINE)
	{
		// Check if country is valid
		$country = $this->validateCountry($country);

		// Check if phone type is valid
		$type = $type !== NULL ? $this->validateType($type) : NULL;

		// Create example number
		$number = $this->phoneNumberUtil->getExampleNumberForType($country, $type);

		return $number !== NULL ? $this->phoneNumberUtil->format($number, $format) : NULL;
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
	 * @throws Exceptions\NoValidCountryException
	 */
	private function validateCountry(string $country) : string
	{
		// Country code have to be upper-cased
		$country = strtoupper($country);

		// Correct auto or null value
		if ($country === 'AUTO' || $country === NULL) {
			return 'AUTO';

		} else if (strlen($country) === 2 && ctype_alpha($country) && in_array($country, $this->phoneNumberUtil->getSupportedRegions(), TRUE)) {
			return $country;

		} else {
			throw new Exceptions\NoValidCountryException('Provided country code "' . $country . '" is not valid. Provide valid country code or AUTO for automatic detection.');
		}
	}

	/**
	 * @param string $type
	 *
	 * @return int
	 *
	 * @throws Exceptions\NoValidTypeException
	 */
	private function validateType(string $type) : int
	{
		$constant = $this->constructPhoneTypeConstant($type);

		if (defined($constant) && in_array($type, [self::TYPE_FIXED_LINE, self::TYPE_MOBILE, self::TYPE_VOIP, self::TYPE_PAGER, self::TYPE_EMERGENCY, self::TYPE_VOICEMAIL], TRUE)) {
			return constant($constant);

		} else {
			throw new Exceptions\NoValidTypeException('Provide valid phone number type.');
		}
	}

	/**
	 * Constructs the corresponding namespaced class constant for a phone number type
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	private function constructPhoneTypeConstant(string $type) : string
	{
		return '\libphonenumber\PhoneNumberType::' . $type;
	}
}
