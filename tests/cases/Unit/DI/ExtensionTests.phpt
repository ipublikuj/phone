<?php declare(strict_types = 1);

namespace Tests\Cases;

use IPub\Phone;
use libphonenumber;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

/**
 * @testCase
 */
final class ExtensionTests extends BaseTestCase
{

	public function testFunctional(): void
	{
		$dic = $this->createContainer();

		Assert::true($dic->getService('phone.phone') instanceof Phone\Phone);
		Assert::true($dic->getService('phone.libphone.utils') instanceof libphonenumber\PhoneNumberUtil);
		Assert::true($dic->getService('phone.libphone.geoCoder') instanceof libphonenumber\geocoding\PhoneNumberOfflineGeocoder);
		Assert::true($dic->getService('phone.libphone.shortNumber') instanceof libphonenumber\ShortNumberInfo);
		Assert::true($dic->getService('phone.libphone.mapper.carrier') instanceof libphonenumber\PhoneNumberToCarrierMapper);
		Assert::true($dic->getService('phone.libphone.mapper.timezone') instanceof libphonenumber\PhoneNumberToTimeZonesMapper);
	}

}

$test_case = new ExtensionTests();
$test_case->run();
