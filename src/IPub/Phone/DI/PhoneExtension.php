<?php
/**
 * PhoneExtension.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:Phone!
 * @subpackage     DI
 * @since          1.0.0
 *
 * @date           12.12.15
 */

declare(strict_types = 1);

namespace IPub\Phone\DI;

use Nette;
use Nette\Bridges;
use Nette\DI;
use Nette\PhpGenerator as Code;

use IPub\Phone;

use libphonenumber;

/**
 * Phone extension container
 *
 * @package        iPublikuj:Phone!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class PhoneExtension extends DI\CompilerExtension
{
	/**
	 * @return void
	 */
	public function loadConfiguration()
	{
		// Get container builder
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('libphone.utils'))
			->setType(libphonenumber\PhoneNumberUtil::class)
			->setFactory('libphonenumber\PhoneNumberUtil::getInstance');

		$builder->addDefinition($this->prefix('libphone.geoCoder'))
			->setType(libphonenumber\geocoding\PhoneNumberOfflineGeocoder::class)
			->setFactory('libphonenumber\geocoding\PhoneNumberOfflineGeocoder::getInstance');

		$builder->addDefinition($this->prefix('libphone.shortNumber'))
			->setType(libphonenumber\ShortNumberInfo::class)
			->setFactory('libphonenumber\ShortNumberInfo::getInstance');

		$builder->addDefinition($this->prefix('libphone.mapper.carrier'))
			->setType(libphonenumber\PhoneNumberToCarrierMapper::class)
			->setFactory('libphonenumber\PhoneNumberToCarrierMapper::getInstance');

		$builder->addDefinition($this->prefix('libphone.mapper.timezone'))
			->setType(libphonenumber\PhoneNumberToTimeZonesMapper::class)
			->setFactory('libphonenumber\PhoneNumberToTimeZonesMapper::getInstance');

		$builder->addDefinition($this->prefix('phone'))
			->setType(Phone\Phone::class);
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 *
	 * @return void
	 */
	public static function register(Nette\Configurator $config, string $extensionName = 'phone')
	{
		$config->onCompile[] = function (Nette\Configurator $config, Nette\DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new PhoneExtension);
		};
	}
}
