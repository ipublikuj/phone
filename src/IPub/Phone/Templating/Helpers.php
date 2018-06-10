<?php
/**
 * Helpers.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Phone!
 * @subpackage     Templating
 * @since          1.0.0
 *
 * @date           12.12.15
 */

declare(strict_types = 1);

namespace IPub\Phone\Templating;

use Nette;

use Latte\Engine;

use IPub;
use IPub\Phone;

/**
 * Phone number Latte helpers
 *
 * @package        iPublikuj:Phone!
 * @subpackage     Latte
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Helpers
{
    use Nette\SmartObject;

	/**
	 * @var Phone\Phone
	 */
	private $phone;

	/**
	 * @param Phone\Phone $phone
	 */
	public function __construct(Phone\Phone $phone)
	{
		$this->phone = $phone;
	}

	/**
	 * @param string $phone
	 * @param string $country
	 * @param int $format
	 *
	 * @return string
	 */
	public function phone(string $phone, string $country = 'AUTO', int $format = Phone\Phone::FORMAT_INTERNATIONAL)
	{
		$country = strtoupper($country);

		if ((strlen($country) !== 2 || !ctype_alpha($country) || !ctype_upper($country)) && $country !== 'AUTO') {
			$format = $country;
			$country = 'AUTO';
		}

		return $this->phone->format($phone, $country, $format);
	}

	/**
	 * @return Phone\Phone
	 */
	public function getPhoneNumberService() : Phone\Phone
	{
		return $this->phone;
	}
}
