<?php
/**
 * Test: IPub\Phone\Extension
 * @testCase
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Phone!
 * @subpackage	Tests
 * @since		5.0
 *
 * @date		13.12.15
 */

namespace IPubTests\Phone;

use Nette;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Phone;

use libphonenumber;

require __DIR__ . '/../bootstrap.php';

class ExtensionTest extends Tester\TestCase
{
	public function testFunctional()
	{
		$dic = $this->createContainer();

		Assert::true($dic->getService('phone.phone') instanceof IPub\Phone\Phone);
		Assert::true($dic->getService('phone.libphone.utils') instanceof libphonenumber\PhoneNumberUtil);
		Assert::true($dic->getService('phone.libphone.geoCoder') instanceof libphonenumber\geocoding\PhoneNumberOfflineGeocoder);
		Assert::true($dic->getService('phone.libphone.shortNumber') instanceof libphonenumber\ShortNumberInfo);
		Assert::true($dic->getService('phone.libphone.mapper.carrier') instanceof libphonenumber\PhoneNumberToCarrierMapper);
		Assert::true($dic->getService('phone.libphone.mapper.timezone') instanceof libphonenumber\PhoneNumberToTimeZonesMapper);
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer()
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		Phone\DI\PhoneExtension::register($config);

		$config->addConfig(__DIR__ . '/files/config.neon', $config::NONE);

		return $config->createContainer();
	}
}

\run(new ExtensionTest());
