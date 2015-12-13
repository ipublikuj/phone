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

	public function testFormatPhoneWithDefaultCountry()
	{
		// Format with correct country value and in default format
		Assert::equal('+32 16 12 34 56', $this->phone->format('016123456', 'be'));

		// Format with correct country value and in international format
		Assert::equal('+32 16 12 34 56', $this->phone->format('016123456', 'BE', Phone\Phone::FORMAT_INTERNATIONAL));

		// Format with correct country value and in national format
		Assert::equal('016 12 34 56', $this->phone->format('016123456', 'BE', Phone\Phone::FORMAT_NATIONAL));
	}

	/**
	 * @throws \IPub\Phone\Exceptions\NoValidCountryFoundException
	 */
	public function testFormatPhoneWithWrongCountry()
	{
		// Format with invalid country string
		$this->phone->format('016123456', 'tst');

		// Format with invalid country string
		$this->phone->format('016123456', 'belgium');

		// Format with invalid country string
		$this->phone->format('016123456', 'BEL');
	}

	/**
	 * @throws \IPub\Phone\Exceptions\NoValidPhoneException
	 */
	public function testFormatPhoneWithInvalidNumber()
	{
		// Format with invalid country value
		$this->phone->format('0499123456', 'NL');

		// Format with invalid country value
		$this->phone->format('016123456', 'NL');
	}

	public function testGeoLocatePhone()
	{
		// Geolocate with correct country value
		Assert::equal('Leuven', $this->phone->getLocation('016123456', 'be'));

		// Geolocate with correct country value
		Assert::equal('Belgium', $this->phone->getLocation('0499123456', 'be'));
	}

	public function testPhoneGetCarrier()
	{
		// Get unknown carrier with correct country value
		Assert::equal('', $this->phone->getCarrier('016123456', 'be'));

		// Get carrier with correct country value
		Assert::equal('Mobistar', $this->phone->getLocation('0499123456', 'be'));
	}

	public function testPhoneGetTimeZones()
	{
		// Get unknown carrier with correct country value
		Assert::equal(['Europe/Brussels'], $this->phone->getTimeZones('016123456', 'be'));

		// Get carrier with correct country value
		Assert::equal(['Europe/Brussels'], $this->phone->getTimeZones('0499123456', 'be'));
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
