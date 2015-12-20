<?php
/**
 * PhoneValidator.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Phone!
 * @subpackage     Forms
 * @since          1.0.0
 *
 * @date           12.12.15
 */

namespace IPub\Phone\Forms;

use Nette;
use Nette\Forms;

use libphonenumber;
use libphonenumber\PhoneNumberUtil;

use IPub\Phone;
use IPub\Phone\Exceptions;

/**
 * Phone number form field validator
 *
 * @package        iPublikuj:Phone!
 * @subpackage     Forms
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class PhoneValidator
{
	/**
	 * Define class name
	 */
	const CLASS_NAME = __CLASS__;

	/**
	 * Define validator calling constant
	 */
	const PHONE = 'IPub\Phone\Forms\PhoneValidator::validatePhone';

	/**
	 * @param Forms\IControl $control
	 * @param array|NULL $params
	 *
	 * @return bool
	 *
	 * @throws Exceptions\InvalidParameterException
	 * @throws Exceptions\NoValidCountryException
	 */
	public static function validatePhone(Forms\IControl $control, $params = [])
	{
		if (!$control instanceof Forms\Controls\TextInput) {
			throw new Exceptions\InvalidArgumentException('This validator could be used only on text field. You used it on: "' . get_class($control) . '"');
		}

		$params = $params === NULL ? [] : $params;

		// Get form element container
		$container = $control->getParent();

		// Get form element value
		$value = $control->getValue();
		// Sanitize params
		$params = array_map('strtoupper', $params);

		// Check if phone country field exists...
		if ($countryField = $container->getComponent($control->getName() . '_country', FALSE)) {
			// ...use selected value as a list of allowed countries
			$selectedCountry = $countryField->getValue();

			$allowedCountries = self::isPhoneCountry($selectedCountry) ? [$selectedCountry] : [];

		} else {
			// Get list of allowed countries from params
			$allowedCountries = self::determineCountries($params);
		}

		// Get list of allowed phone types
		$allowedTypes = self::determineTypes($params);

		// Check for leftover parameters
		self::checkLeftoverParameters($params, $allowedCountries, array_keys($allowedTypes));

		// Get instance of phone number util
		$phoneNumberUtil = PhoneNumberUtil::getInstance();

		// Perform validation
		foreach ($allowedCountries as $country) {
			try {
				// For default countries or country field, the following throws NumberParseException if
				// not parsed correctly against the supplied country
				// For automatic detection: tries to discover the country code using from the number itself
				$phoneProto = $phoneNumberUtil->parse($value, $country);

				// For automatic detection, the number should have a country code
				// Check if type is allowed
				if (
					$phoneProto->hasCountryCode() &&
					$allowedTypes === [] ||
					in_array($phoneNumberUtil->getNumberType($phoneProto), $allowedTypes)
				) {
					// Automatic detection:
					if ($country == 'ZZ') {
						// Validate if the international phone number is valid for its contained country
						return $phoneNumberUtil->isValidNumber($phoneProto);
					}

					// Validate number against the specified country. Return only if success
					// If failure, continue loop to next specified country
					if ($phoneNumberUtil->isValidNumberForRegion($phoneProto, $country)) {
						return TRUE;
					}
				}

			} catch (libphonenumber\NumberParseException $ex) {
				// Proceed to default validation error
			}
		}

		// All specified country validations have failed
		return FALSE;
	}

	/**
	 * Checks if the supplied string is a valid country code using some arbitrary country validation
	 * If using a package based on umpirsky/country-list, invalidate the option 'ZZ => Unknown or invalid region'
	 *
	 * @param string $country
	 *
	 * @return bool
	 */
	protected static function isPhoneCountry($country)
	{
		return (strlen($country) === 2 && ctype_alpha($country) && ctype_upper($country) && $country !== 'ZZ');
	}

	/**
	 * Checks if the supplied string is a valid phone number type
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	protected static function isPhoneType($type)
	{
		// Legacy support.
		$type = ($type == 'LANDLINE' ? 'FIXED_LINE' : $type);

		return defined(self::constructPhoneTypeConstant($type));
	}

	/**
	 * Sets the countries to validate against
	 *
	 * @param array $params
	 *
	 * @return array
	 *
	 * @throws Exceptions\NoValidCountryException
	 */
	protected static function determineCountries(array $params = [])
	{
		// Check if we need to parse for automatic detection
		if (in_array('AUTO', $params)) {
			return ['ZZ'];

		// Else use the supplied parameters
		} else {
			$allowedCountries = array_filter($params, function ($item) {
				return self::isPhoneCountry($item);
			});

			if ($allowedCountries === []) {
				throw new Exceptions\NoValidCountryException;
			}

			return $allowedCountries;
		}
	}

	/**
	 * Sets the phone number types to validate against
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	protected static function determineTypes(array $params = [])
	{
		// Get phone types
		$untransformedTypes = array_filter($params, function ($item) {
			return self::isPhoneType($item);
		});

		// Transform valid types to their namespaced class constant
		$allowedTypes = array_reduce($untransformedTypes, function (array $result, $item) {
			$result[$item] = constant('\libphonenumber\PhoneNumberType::' . constant(self::constructPhoneTypeConstant($item)));

			return $result;
		}, []);

		// Add in the unsure number type if applicable.
		if (array_intersect(['FIXED_LINE', 'MOBILE'], $params)) {
			$allowedTypes['FIXED_LINE_OR_MOBILE'] = libphonenumber\PhoneNumberType::FIXED_LINE_OR_MOBILE;
		}

		return $allowedTypes;
	}

	/**
	 * Constructs the corresponding namespaced class constant for a phone number type
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	protected static function constructPhoneTypeConstant($type)
	{
		return '\IPub\Phone\Phone::TYPE_' . $type;
	}

	/**
	 * Checks for parameter leftovers to force developers to write proper code
	 *
	 * @param array $params
	 * @param array $allowedCountries
	 * @param array $allowedTypes
	 *
	 * @throws Exceptions\InvalidParameterException
	 */
	protected static function checkLeftoverParameters(array $params = [], array $allowedCountries = [], array $allowedTypes = [])
	{
		// Remove the automatic detection option if applicable
		$leftovers = array_diff($params, $allowedCountries, $allowedTypes, ['AUTO']);

		if (!empty($leftovers)) {
			throw new Exceptions\InvalidParameterException('Invalid parameters were sent to the validator: "' . implode(', ', $leftovers) . '"');
		}
	}
}
