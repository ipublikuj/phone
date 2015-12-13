<?php
/**
 * PhoneExtension.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Phone!
 * @subpackage	DI
 * @since		5.0
 *
 * @date		12.12.15
 */

namespace IPub\Phone\DI;

use Nette;
use Nette\DI;
use Nette\PhpGenerator as Code;

use IPub;
use IPub\Phone;

use libphonenumber;

class PhoneExtension extends DI\CompilerExtension
{
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('libphone.utils'))
			->setClass('libphonenumber\PhoneNumberUtil')
			->setFactory('libphonenumber\PhoneNumberUtil::getInstance');

		$builder->addDefinition($this->prefix('libphone.geoCoder'))
			->setClass('libphonenumber\geocoding\PhoneNumberOfflineGeocoder')
			->setFactory('libphonenumber\geocoding\PhoneNumberOfflineGeocoder::getInstance');

		$builder->addDefinition($this->prefix('libphone.shortNumber'))
			->setClass('libphonenumber\ShortNumberInfo')
			->setFactory('libphonenumber\ShortNumberInfo::getInstance');

		$builder->addDefinition($this->prefix('libphone.mapper.carrier'))
			->setClass('libphonenumber\PhoneNumberToCarrierMapper')
			->setFactory('libphonenumber\PhoneNumberToCarrierMapper::getInstance');

		$builder->addDefinition($this->prefix('libphone.mapper.timezone'))
			->setClass('libphonenumber\PhoneNumberToTimeZonesMapper')
			->setFactory('libphonenumber\PhoneNumberToTimeZonesMapper::getInstance');

		$builder->addDefinition($this->prefix('phone'))
			->setClass(Phone\Phone::CLASSNAME);

		// Register template helpers
		$builder->addDefinition($this->prefix('helpers'))
			->setClass(Phone\Templating\Helpers::CLASSNAME)
			->setFactory($this->prefix('@phone') . '::createTemplateHelpers')
			->setInject(FALSE);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// Install extension latte macros
		$latteFactory = $builder->getDefinition($builder->getByType('\Nette\Bridges\ApplicationLatte\ILatteFactory') ?: 'nette.latteFactory');

		$latteFactory
			->addSetup('IPub\Phone\Latte\Macros::install(?->getCompiler())', ['@self'])
			->addSetup('addFilter', ['phone', [$this->prefix('@helpers'), 'phone']])
			->addSetup('addFilter', ['getPhoneNumberService', [$this->prefix('@helpers'), 'getPhoneNumberService']]);
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $config, $extensionName = 'phone')
	{
		$config->onCompile[] = function (Nette\Configurator $config, Nette\DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new PhoneExtension);
		};
	}
}
