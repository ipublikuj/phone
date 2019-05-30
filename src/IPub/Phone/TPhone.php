<?php
/**
 * TPhone.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:Phone!
 * @subpackage     common
 * @since          1.0.0
 *
 * @date           12.12.15
 */

declare(strict_types = 1);

namespace IPub\Phone;

/**
 * Phone number helpers trait
 *
 * @package        iPublikuj:Phone!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
trait TPhone
{
	/**
	 * @var Phone
	 */
	protected $phone;

	/**
	 * @param Phone $phone
	 */
	public function injectPhone(Phone $phone)
	{
		$this->phone = $phone;
	}
}
