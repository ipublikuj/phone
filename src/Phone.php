<?php declare(strict_types = 1);

/**
 * Phone.php
 *
 * @copyright      More in LICENSE.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:Phone!
 * @subpackage     common
 * @since          1.0.0
 *
 * @date           12.12.15
 */

namespace IPub\Phone;

use libphonenumber;
use libphonenumber\PhoneNumberFormat;
use Nette;
use Nette\Localization;

/**
 * Phone number helpers
 *
 * @package        iPublikuj:Phone!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Phone
{

	use Nette\SmartObject;

	// Define phone number types
	public const TYPE_FIXED_LINE = 'FIXED_LINE';
	public const TYPE_MOBILE = 'MOBILE';
	public const TYPE_FIXED_LINE_OR_MOBILE = 'FIXED_LINE_OR_MOBILE';
	public const TYPE_VOIP = 'VOIP';
	public const TYPE_PAGER = 'PAGER';
	public const TYPE_EMERGENCY = 'EMERGENCY';
	public const TYPE_VOICEMAIL = 'VOICEMAIL';
	public const TYPE_UNKNOWN = 'UNKNOWN';

	public const FORMAT_E164 = PhoneNumberFormat::E164;
	public const FORMAT_INTERNATIONAL = PhoneNumberFormat::INTERNATIONAL;
	public const FORMAT_NATIONAL = PhoneNumberFormat::NATIONAL;
	public const FORMAT_RFC3966 = PhoneNumberFormat::RFC3966;

	/** @var libphonenumber\PhoneNumberUtil */
	private libphonenumber\PhoneNumberUtil $phoneNumberUtil;

	/** @var libphonenumber\geocoding\PhoneNumberOfflineGeocoder */
	private libphonenumber\geocoding\PhoneNumberOfflineGeocoder $phoneNumberGeocoder;

	/** @var libphonenumber\PhoneNumberToCarrierMapper */
	private libphonenumber\PhoneNumberToCarrierMapper $carrierMapper;

	/** @var libphonenumber\PhoneNumberToTimeZonesMapper */
	private libphonenumber\PhoneNumberToTimeZonesMapper $timeZonesMapper;

	/** @var Localization\ITranslator|null */
	private ?Localization\ITranslator $translator;

	/**
	 * @param libphonenumber\PhoneNumberUtil $phoneNumberUtil
	 * @param libphonenumber\geocoding\PhoneNumberOfflineGeocoder $phoneNumberGeocoder
	 * @param libphonenumber\PhoneNumberToCarrierMapper $carrierMapper
	 * @param libphonenumber\PhoneNumberToTimeZonesMapper $timeZonesMapper
	 * @param Localization\ITranslator|null $translator
	 */
	public function __construct(
		libphonenumber\PhoneNumberUtil $phoneNumberUtil,
		libphonenumber\geocoding\PhoneNumberOfflineGeocoder $phoneNumberGeocoder,
		libphonenumber\PhoneNumberToCarrierMapper $carrierMapper,
		libphonenumber\PhoneNumberToTimeZonesMapper $timeZonesMapper,
		?Localization\ITranslator $translator = null
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
	 *
	 * @return Entities\Phone
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function parse(
		string $number,
		string $country = 'AUTO'
	): Entities\Phone {
		// Parse string into phone number
		return Entities\Phone::fromNumber($number, $country);
	}

	/**
	 * @param string $number
	 * @param string $country
	 * @param string|null $type
	 *
	 * @return bool
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidTypeException
	 */
	public function isValid(
		string $number,
		string $country = 'AUTO',
		?string $type = null
	): bool {
		// Check if country is valid
		$country = $this->validateCountry($country);

		// Check if phone type is valid
		$type = $type !== null ? $this->validateType($type) : null;

		try {
			// Parse string into phone number
			$phoneNumber = $this->phoneNumberUtil->parse($number, $country);

			if ($type !== null && $this->phoneNumberUtil->getNumberType($phoneNumber) !== $type) {
				return false;
			}

			// Automatic detection:
			if ($country === 'AUTO') {
				// Validate if the international phone number is valid for its contained country
				return $this->phoneNumberUtil->isValidNumber($phoneNumber);
			}

			// Validate number against the specified country
			return $this->phoneNumberUtil->isValidNumberForRegion($phoneNumber, $country);

		} catch (libphonenumber\NumberParseException $ex) {
			return false;
		}
	}

	/**
	 * @param string $number
	 * @param string $country
	 * @param int $format
	 *
	 * @return string|null
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function format(
		string $number,
		string $country = 'AUTO',
		int $format = self::FORMAT_INTERNATIONAL
	): ?string {
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
				return $entity->getRfcFormat();

			default:
				throw new Exceptions\InvalidArgumentException('Invalid number format given, provide valid phone number format.');
		}
	}

	/**
	 * @param string $number
	 * @param string $country
	 * @param string|null $locale
	 * @param string|null $userCountry
	 *
	 * @return string
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidPhoneException
	 * @throws Exceptions\NoValidTypeException
	 * @throws libphonenumber\NumberParseException
	 */
	public function getLocation(
		string $number,
		string $country = 'AUTO',
		?string $locale = null,
		?string $userCountry = null
	): string {
		if ($this->isValid($number, $country)) {
			$country = strtoupper($country);

			if ($userCountry !== null) {
				// Check for valid user country
				$userCountry = $this->validateCountry($userCountry);
			}

			// Parse phone number
			$parsed = $this->phoneNumberUtil->parse($number, $country);

			// Determine locale
			$locale = $locale === null && $this->translator !== null && method_exists($this->translator, 'getLocale') ? $this->translator->getLocale() : 'en_US';

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
	 * @return string|null
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function getCarrier(
		string $number,
		string $country = 'AUTO'
	): ?string {
		// Create phone entity
		$entity = Entities\Phone::fromNumber($number, $country);

		// Extract carrier name from given phone number
		return $entity->getCarrier();
	}

	/**
	 * @param string $number
	 * @param string $country
	 *
	 * @return string[]
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public function getTimeZones(
		string $number,
		string $country = 'AUTO'
	): array {
		// Create phone entity
		$entity = Entities\Phone::fromNumber($number, $country);

		// Extract carrier name from given phone number
		return $entity->getTimeZones();
	}

	/**
	 * Get list of library supported countries
	 *
	 * @return string[]
	 */
	public function getSupportedCountries(): array
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
	public function getCountryCodeForCountry(
		string $country
	): int {
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
	 * @throws Exceptions\NoValidTypeException
	 */
	public function getExampleNationalNumber(
		string $country,
		string $type = self::TYPE_FIXED_LINE
	): string {
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
	 * @throws Exceptions\NoValidTypeException
	 */
	public function getExampleInternationalNumber(
		string $country,
		string $type = self::TYPE_FIXED_LINE
	): string {
		return $this->getExampleNumber($country, PhoneNumberFormat::INTERNATIONAL, $type);
	}

	/**
	 * @param string $country
	 * @param int $format
	 * @param string $type
	 *
	 * @return string
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidTypeException
	 */
	private function getExampleNumber(
		string $country,
		int $format,
		string $type = self::TYPE_FIXED_LINE
	): string {
		// Check if country is valid
		$country = $this->validateCountry($country);

		// Check if phone type is valid
		$type = $this->validateType($type);

		// Create example number
		$number = $this->phoneNumberUtil->getExampleNumberForType($country, $type);

		if ($number !== null) {
			return $this->phoneNumberUtil->format($number, $format);
		}

		throw new Exceptions\InvalidArgumentException('Provided values could not build example number');
	}

	/**
	 * @param string $country
	 *
	 * @return string
	 *
	 * @throws Exceptions\NoValidCountryException
	 */
	private function validateCountry(
		string $country
	): string {
		// Country code have to be upper-cased
		$country = strtoupper($country);

		// Correct auto or null value
		if ($country === 'AUTO') {
			return 'AUTO';

		} elseif (
			strlen($country) === 2
			&& ctype_alpha($country)
			&& in_array($country, $this->phoneNumberUtil->getSupportedRegions(), true)
		) {
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
	private function validateType(
		string $type
	): int {
		$constant = $this->constructPhoneTypeConstant($type);

		if (defined($constant) && in_array($type, [
				self::TYPE_FIXED_LINE,
				self::TYPE_MOBILE,
				self::TYPE_VOIP,
				self::TYPE_PAGER,
				self::TYPE_EMERGENCY,
				self::TYPE_VOICEMAIL,
			], true)) {
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
	private function constructPhoneTypeConstant(
		string $type
	): string {
		return '\libphonenumber\PhoneNumberType::' . $type;
	}

}
