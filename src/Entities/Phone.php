<?php declare(strict_types = 1);

/**
 * Phone.php
 *
 * @copyright      More in LICENSE.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:Phone!
 * @subpackage     Entities
 * @since          1.0.1
 *
 * @date           17.12.15
 */

namespace IPub\Phone\Entities;

use IPub;
use IPub\Phone\Exceptions;
use libphonenumber;
use libphonenumber\PhoneNumberFormat;
use Nette;

/**
 * Phone number entity
 *
 * @package        iPublikuj:Phone!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Phone
{

	use Nette\SmartObject;

	/**
	 * The country code
	 *
	 * @var int|null
	 */
	protected ?int $countryCode = null;

	/**
	 * The national number
	 *
	 * @var string|null
	 */
	protected ?string $nationalNumber = null;

	/**
	 * The international number
	 *
	 * @var string|null
	 */
	protected ?string $internationalNumber = null;

	/**
	 * The extension
	 *
	 * @var string|null
	 */
	protected ?string $extension = null;

	/**
	 * Whether this phone number uses an italian leading zero
	 *
	 * @var bool
	 */
	protected bool $italianLeadingZero = false;

	/**
	 * The number of leading zeros of this phone number
	 *
	 * @var int|null
	 */
	protected ?int $numberOfLeadingZeros;

	/**
	 * The raw input
	 *
	 * @var string|null
	 */
	protected ?string $rawOutput = null;

	/**
	 * The RFC3966 number format
	 *
	 * @var string|null
	 */
	protected ?string $rfcFormat = null;

	/**
	 * Phone number type
	 *
	 * @var string
	 */
	protected string $type;

	/**
	 * Carrier name
	 *
	 * @var string|null
	 */
	protected ?string $carrier;

	/**
	 * Country name
	 *
	 * @var string|null
	 */
	protected ?string $country;

	/**
	 * List of time zones
	 *
	 * @var string[]
	 */
	protected array $timeZones = [];

	/**
	 * @param string $rawInput
	 * @param string $rfcFormat
	 * @param string $nationalNumber
	 * @param string $internationalNumber
	 * @param int $countryCode
	 * @param string $country
	 * @param string $type
	 * @param string|null $carrierName
	 */
	public function __construct(
		string $rawInput,
		string $rfcFormat,
		string $nationalNumber,
		string $internationalNumber,
		?int $countryCode,
		?string $country,
		string $type,
		?string $carrierName = null
	) {
		$this->rawOutput = $rawInput;
		$this->rfcFormat = $rfcFormat;

		$this->nationalNumber = $nationalNumber;
		$this->internationalNumber = $internationalNumber;

		$this->countryCode = $countryCode;
		$this->country = $country;

		$this->type = $type;

		$this->carrier = ($carrierName !== '' && $carrierName !== null) ? $carrierName : null;
	}

	/**
	 * @return int|null
	 */
	public function getCountryCode(): ?int
	{
		return $this->countryCode;
	}

	/**
	 * @return string|null
	 */
	public function getNationalNumber(): ?string
	{
		return $this->nationalNumber;
	}

	/**
	 * @return string|null
	 */
	public function getInternationalNumber(): ?string
	{
		return $this->internationalNumber;
	}

	/**
	 * @param string $extension
	 *
	 * @return void
	 */
	public function setExtension(string $extension): void
	{
		$this->extension = $extension;
	}

	/**
	 * @return string|null
	 */
	public function getExtension(): ?string
	{
		return $this->extension;
	}

	/**
	 * @param bool $italianLeadingZero
	 *
	 * @return void
	 */
	public function setItalianLeadingZero(bool $italianLeadingZero): void
	{
		$this->italianLeadingZero = $italianLeadingZero;
	}

	/**
	 * @return bool
	 */
	public function getItalianLeadingZero(): bool
	{
		return $this->italianLeadingZero;
	}

	/**
	 * @param int $numberOfLeadingZeros
	 *
	 * @return void
	 */
	public function setNumberOfLeadingZeros(int $numberOfLeadingZeros): void
	{
		$this->numberOfLeadingZeros = $numberOfLeadingZeros;
	}

	/**
	 * @return int|null
	 */
	public function getNumberOfLeadingZeros(): ?int
	{
		return $this->numberOfLeadingZeros;
	}

	/**
	 * @return string|null
	 */
	public function getRawOutput(): ?string
	{
		return $this->rawOutput;
	}

	/**
	 * @return string|null
	 */
	public function getRfcFormat(): ?string
	{
		return $this->rfcFormat;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return string|null
	 */
	public function getCarrier(): ?string
	{
		return $this->carrier;
	}

	/**
	 * @return string|null
	 */
	public function getCountry(): ?string
	{
		return $this->country;
	}

	/**
	 * @param string[] $timeZones
	 *
	 * @return void
	 */
	public function setTimeZones(array $timeZones): void
	{
		$this->timeZones = $timeZones;
	}

	/**
	 * @return string[]
	 */
	public function getTimeZones(): array
	{
		return $this->timeZones;
	}

	/**
	 * @param string $timeZone
	 *
	 * @return bool
	 */
	public function isInTimeZone($timeZone): bool
	{
		return in_array($timeZone, $this->timeZones, true);
	}

	/**
	 * @param string $number
	 * @param string $country
	 *
	 * @return Phone
	 *
	 * @throws Exceptions\NoValidCountryException
	 * @throws Exceptions\NoValidPhoneException
	 */
	public static function fromNumber(string $number, string $country = 'AUTO'): Phone
	{
		$phoneNumberUtil = libphonenumber\PhoneNumberUtil::getInstance();
		$carrierMapper = libphonenumber\PhoneNumberToCarrierMapper::getInstance();
		$timeZonesMapper = libphonenumber\PhoneNumberToTimeZonesMapper::getInstance();

		// Country code have to be upper-cased
		$country = strtoupper($country);

		// Correct auto or null value
		if ($country === 'AUTO') {
			$country = 'AUTO';

		} elseif (strlen($country) !== 2 || ctype_alpha($country) === false || !in_array($country, $phoneNumberUtil->getSupportedRegions(), true)) {
			throw new Exceptions\NoValidCountryException(sprintf('Provided country code "%s" is not valid. Provide valid country code or AUTO for automatic detection.', $country));
		}

		try {
			// Parse string into phone number
			$parsed = $phoneNumberUtil->parse($number, $country);

			// Check if number is valid
			if (($country === 'AUTO' && $phoneNumberUtil->isValidNumber($parsed) === false) || ($country !== 'AUTO' && $phoneNumberUtil->isValidNumberForRegion($parsed, $country) === false)) {
				throw new Exceptions\NoValidPhoneException(sprintf('Provided phone number "%s" is not valid phone number. Provide valid phone number.', $number));
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
					throw new Exceptions\NoValidPhoneException(sprintf('Provided phone number "%s" is not valid phone number. Provide valid phone number.', $number));
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

		$entity = new self(
			$phoneNumberUtil->format($parsed, PhoneNumberFormat::E164),
			$phoneNumberUtil->format($parsed, PhoneNumberFormat::RFC3966),
			$phoneNumberUtil->format($parsed, PhoneNumberFormat::NATIONAL),
			$phoneNumberUtil->format($parsed, PhoneNumberFormat::INTERNATIONAL),
			$parsed->getCountryCode(),
			$phoneNumberUtil->getRegionCodeForNumber($parsed),
			$numberType,
			$carrierMapper->getNameForNumber($parsed, 'en')
		);

		$entity->setItalianLeadingZero($parsed->hasItalianLeadingZero());

		$entity->setTimeZones($timeZonesMapper->getTimeZonesForNumber($parsed));

		if ($parsed->hasExtension() && $parsed->getExtension() !== null) {
			$entity->setExtension($parsed->getExtension());
		}

		if ($parsed->hasNumberOfLeadingZeros()) {
			$entity->setNumberOfLeadingZeros($parsed->getNumberOfLeadingZeros());
		}

		return $entity;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->rawOutput;
	}

}
