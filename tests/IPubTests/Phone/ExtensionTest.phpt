<?php
/**
 * Test: IPub\Phone\Extension
 * @testCase
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:Phone!
 * @subpackage     Tests
 * @since          1.0.0
 *
 * @date           13.12.15
 */

declare(strict_types = 1);

namespace IPubTests\Phone;

use Nette;

use Tester;
use Tester\Assert;

use IPub\Phone;

use libphonenumber;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

/**
 * Registering phone extension tests
 *
 * @package        iPublikuj:Phone!
 * @subpackage     Tests
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class ExtensionTest extends Tester\TestCase
{
	public function testFunctional()
	{
		$dic = $this->createContainer();

		Assert::true($dic->getService('phone.phone') instanceof Phone\Phone);
		Assert::true($dic->getService('phone.libphone.utils') instanceof libphonenumber\PhoneNumberUtil);
		Assert::true($dic->getService('phone.libphone.geoCoder') instanceof libphonenumber\geocoding\PhoneNumberOfflineGeocoder);
		Assert::true($dic->getService('phone.libphone.shortNumber') instanceof libphonenumber\ShortNumberInfo);
		Assert::true($dic->getService('phone.libphone.mapper.carrier') instanceof libphonenumber\PhoneNumberToCarrierMapper);
		Assert::true($dic->getService('phone.libphone.mapper.timezone') instanceof libphonenumber\PhoneNumberToTimeZonesMapper);
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer() : Nette\DI\Container
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		Phone\DI\PhoneExtension::register($config);

		$config->addConfig(__DIR__ . DS . 'files' . DS . 'config.neon');

		return $config->createContainer();
	}
}

\run(new ExtensionTest());
