<?php
/**
 * Test: IPub\Phone\Validator
 * @testCase
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Phone!
 * @subpackage     Tests
 * @since          1.0.0
 *
 * @date           13.12.15
 */

namespace IPubTests\Phone;

use Nette;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Phone;

use libphonenumber;

require __DIR__ . '/../bootstrap.php';

/**
 * Phone number helpers tests
 *
 * @package        iPublikuj:Phone!
 * @subpackage     Tests
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class PhoneHelpersTest extends Tester\TestCase
{
	/**
	 * @var Phone\Phone
	 */
	private $phone;

	/**
	 * @return array[]|array
	 */
	public function dataValidCountriesToCodes()
	{
		return [
			['CZ', 420],
			['SK', 421],
			['US', 1],
		];
	}

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
	 * @throws \IPub\Phone\Exceptions\NoValidCountryException
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

	public function testFormatPhoneWithInvalidNumber()
	{
		Assert::exception(function() {
			$this->phone->format('0499123456', 'US');
		}, 'IPub\Phone\Exceptions\NoValidPhoneException');

		Assert::exception(function() {
			$this->phone->format('016123456', 'NL');
		}, 'IPub\Phone\Exceptions\NoValidPhoneException');
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
		Assert::equal(NULL, $this->phone->getCarrier('016123456', 'be'));

		// Get carrier with correct country value
		Assert::equal('Mobistar', $this->phone->getCarrier('0499123456', 'be'));
	}

	public function testPhoneGetTimeZones()
	{
		// Get unknown carrier with correct country value
		Assert::equal(['Europe/Brussels'], $this->phone->getTimeZones('016123456', 'be'));

		// Get carrier with correct country value
		Assert::equal(['Europe/Brussels'], $this->phone->getTimeZones('0499123456', 'be'));
	}

	public function testParsingValidNumber()
	{
		$number = $this->phone->parse('0499123456', 'be');

		Assert::type('IPub\Phone\Entities\Phone', $number);
		Assert::equal('BE', $number->getCountry());
		Assert::equal('+32 499 12 34 56', $number->getInternationalNumber());
		Assert::equal('0499 12 34 56', $number->getNationalNumber());
		Assert::equal('Mobistar', $number->getCarrier());
		Assert::equal('+32499123456', $number->getRawOutput());
		Assert::equal(['Europe/Brussels'], $number->getTimeZones());

		Assert::equal('+32499123456', (string) $number);
	}

	/**
	 * @throws \IPub\Phone\Exceptions\NoValidPhoneException
	 */
	public function testParsingInvalidNumber()
	{
		$this->phone->parse('012345', 'be');
	}

	/**
	 * @dataProvider dataValidCountriesToCodes
	 *
	 * @param string $country
	 * @param string $expected
	 */
	public function testGetCountryCodeForCountry($country, $expected)
	{
		Assert::equal($expected, $this->phone->getCountryCodeForCountry($country));
	}

	/**
	 * @throws \IPub\Phone\Exceptions\NoValidCountryException
	 */
	public function testGetCountryCodeForInvalidCountry()
	{
		$this->phone->getCountryCodeForCountry('xy');
	}

	public function testCreateExampleNationalNumber()
	{
		$number = $this->phone->getExampleNationalNumber('us');
		Assert::equal('(201) 555-5555', $number);

		$number = $this->phone->getExampleNationalNumber('cz');
		Assert::equal('212 345 678', $number);
	}

	public function testCreateExampleInternationalNumber()
	{
		$number = $this->phone->getExampleInternationalNumber('us');
		Assert::equal('+1 201-555-5555', $number);

		$number = $this->phone->getExampleInternationalNumber('cz');
		Assert::equal('+420 212 345 678', $number);
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
