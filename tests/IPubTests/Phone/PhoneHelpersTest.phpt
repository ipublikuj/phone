<?php
/**
 * Test: IPub\Phone\Validator
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

class PhoneHelpersTest extends Tester\TestCase
{
	/**
	 * @var Phone\Phone
	 */
	private $phone;

	/**
	 * Set up
	 */
	public function setUp()
	{
		parent::setUp();

		$dic = $this->createContainer();

		// Get phone helper from container
		$this->phone = $dic->getService('phone.phone');
	}

	public function testValidatePhoneWithDefaultCountryWithoutType()
	{
		// Validator with correct country value
		Assert::true($this->phone->isValid('016123456', 'be'));

		// Validator with wrong country value
		Assert::false($this->phone->isValid('016123456', 'NL'));
	}

	public function testValidatePhoneWithDefaultCountryWithType()
	{
		// Validator with correct country value, correct type
		Assert::true($this->phone->isValid('0499123456', 'be', Phone\Phone::TYPE_MOBILE));

		// Validator with correct country value, wrong type
		Assert::false($this->phone->isValid('016123456', 'be', Phone\Phone::TYPE_MOBILE));

		// Validator with wrong country value, correct type
		Assert::false($this->phone->isValid('0499123456', 'NL', Phone\Phone::TYPE_MOBILE));

		// Validator with wrong country value, wrong type
		Assert::false($this->phone->isValid('016123456', 'NL', Phone\Phone::TYPE_MOBILE));
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

\run(new PhoneHelpersTest());
