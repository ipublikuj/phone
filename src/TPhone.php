<?php declare(strict_types = 1);

/**
 * TPhone.php
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

	/** @var Phone */
	protected Phone $phone;

	/**
	 * @param Phone $phone
	 */
	public function injectPhone(Phone $phone)
	{
		$this->phone = $phone;
	}

}
