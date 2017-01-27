<?php
/**
 * PhoneExtension.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
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

use IPub;
use IPub\Phone;

use libphonenumber;

/**
 * Phone extension container
 *
 * @package        iPublikuj:Phone!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
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
			->setClass(libphonenumber\PhoneNumberUtil::class)
			->setFactory('libphonenumber\PhoneNumberUtil::getInstance');

		$builder->addDefinition($this->prefix('libphone.geoCoder'))
			->setClass(libphonenumber\geocoding\PhoneNumberOfflineGeocoder::class)
			->setFactory('libphonenumber\geocoding\PhoneNumberOfflineGeocoder::getInstance');

		$builder->addDefinition($this->prefix('libphone.shortNumber'))
			->setClass(libphonenumber\ShortNumberInfo::class)
			->setFactory('libphonenumber\ShortNumberInfo::getInstance');

		$builder->addDefinition($this->prefix('libphone.mapper.carrier'))
			->setClass(libphonenumber\PhoneNumberToCarrierMapper::class)
			->setFactory('libphonenumber\PhoneNumberToCarrierMapper::getInstance');

		$builder->addDefinition($this->prefix('libphone.mapper.timezone'))
			->setClass(libphonenumber\PhoneNumberToTimeZonesMapper::class)
			->setFactory('libphonenumber\PhoneNumberToTimeZonesMapper::getInstance');

		$builder->addDefinition($this->prefix('phone'))
			->setClass(Phone\Phone::class);

		// Register template helpers
		$builder->addDefinition($this->prefix('helpers'))
			->setClass(Phone\Templating\Helpers::class)
			->setFactory($this->prefix('@phone') . '::createTemplateHelpers')
			->setInject(FALSE);
	}

	/**
	 * {@inheritdoc}
	 */
	public function beforeCompile()
	{
		parent::beforeCompile();

		// Get container builder
		$builder = $this->getContainerBuilder();

		// Install extension latte macros
		$latteFactory = $builder->getDefinition($builder->getByType(Bridges\ApplicationLatte\ILatteFactory::class) ?: 'nette.latteFactory');

		$latteFactory
			->addSetup('IPub\Phone\Latte\Macros::install(?->getCompiler())', ['@self'])
			->addSetup('addFilter', ['phone', [$this->prefix('@helpers'), 'phone']])
			->addSetup('addFilter', ['getPhoneNumberService', [$this->prefix('@helpers'), 'getPhoneNumberService']]);
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
