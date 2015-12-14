<?php
/**
 * Macros.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Phone!
 * @subpackage     Latte
 * @since          1.0
 *
 * @date           12.12.15
 */

namespace IPub\Phone\Latte;

use Nette;

use Latte;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\Macros\MacroSet;

use IPub;
use IPub\Phone;

/**
 * Phone number Latte macros
 *
 * @package        iPublikuj:Phone!
 * @subpackage     Latte
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Macros extends MacroSet
{
	/**
	 * Define class name
	 */
	const CLASS_NAME = __CLASS__;

	/**
	 * Register latte macros
	 *
	 * @param Compiler $compiler
	 *
	 * @return static
	 */
	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);

		/**
		 * {phone $phoneNumber[, $country, $format]}
		 */
		$me->addMacro('phone', [$me, 'macroPhone']);

		return $me;
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 *
	 * @return string
	 *
	 * @throws Latte\CompileException
	 */
	public function macroPhone(MacroNode $node, PhpWriter $writer)
	{
		$arguments = self::prepareMacroArguments($node->args);

		if ($arguments['phone'] === NULL) {
			throw new Latte\CompileException("Please provide phone number.");
		}

		return $writer->write('echo %escape($template->getPhoneNumberService()->format("' . $arguments['phone'] . '", "' . $arguments['country'] . '", ' . $arguments['format'] . '))');
	}

	/**
	 * @param string $macro
	 *
	 * @return array
	 */
	public static function prepareMacroArguments($macro)
	{
		$arguments = array_map(function ($value) {
			return trim(trim($value), '\'"');
		}, explode(",", $macro));

		$phone = $arguments[0];
		$country = (isset($arguments[1]) && !empty($arguments[1])) ? strtoupper($arguments[1]) : NULL;
		$format = (isset($arguments[2]) && !empty($arguments[2])) ? $arguments[2] : Phone\Phone::FORMAT_INTERNATIONAL;

		if (!self::isPhoneCountry($country)) {
			$format = (int) $country;
			$country = 'AUTO';
		}

		if ($country === NULL) {
			$country = 'AUTO';
		}

		return [
			'phone'   => (string) $phone,
			'country' => (string) $country,
			'format'  => (int) $format,
		];
	}

	/**
	 * Checks if the supplied string is a valid country code using some arbitrary country validation
	 *
	 * @param string $country
	 *
	 * @return bool
	 */
	protected static function isPhoneCountry($country)
	{
		return (strlen($country) === 2 && ctype_alpha($country) && ctype_upper($country));
	}
}
