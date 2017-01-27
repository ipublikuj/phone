<?php
/**
 * Test: IPub\Phone\Phone
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

declare(strict_types = 1);

namespace IPubTests\Phone;

use Nette;
use Nette\Forms;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Phone;

use libphonenumber;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

/**
 * Phone number form validation tests
 *
 * @package        iPublikuj:Phone!
 * @subpackage     Tests
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class PhoneValidationTest extends Tester\TestCase
{
	public function testValidatePhoneWithDefaultCountryWithoutType()
	{
		// Validator with correct country value
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['BE'])
			->setValue('016123456')
			->validate();

		Assert::false($field->hasErrors());

		// Validator with wrong country value
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['NL'])
			->setValue('016123456')
			->validate();

		Assert::true($field->hasErrors());

		// Validator with multiple country values, one correct
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['BE', 'NL'])
			->setValue('016123456')
			->validate();

		Assert::false($field->hasErrors());

		// Validator with multiple country values, value correct for second country in list
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['NL', 'BE'])
			->setValue('016123456')
			->validate();

		Assert::false($field->hasErrors());

		// Validator with multiple wrong country values
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['NL', 'DE'])
			->setValue('016123456')
			->validate();

		Assert::true($field->hasErrors());
	}

	public function testValidatePhoneWithCountryFieldWithoutType()
	{
		// Validator with correct country field supplied
		$field = $this->createControls();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone')
			->setValue('016123456');

		$countryField = $field->getForm()->getComponent('phone_country');
		$countryField
			->setValue('BE');

		$field->validate();

		Assert::false($field->hasErrors());

		// Validator with wrong country field supplied
		$field = $this->createControls();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone')
			->setValue('016123456');

		$countryField = $field->getForm()->getComponent('phone_country');
		$countryField
			->setValue('NL');

		$field->validate();

		Assert::true($field->hasErrors());
	}

	public function testValidatePhoneWithDefaultCountryWithType()
	{
		// Validator with correct country value, correct type
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['BE', 'mobile'])
			->setValue('0499123456')
			->validate();

		Assert::false($field->hasErrors());

		// Validator with correct country value, wrong type
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['BE', 'mobile'])
			->setValue('016123456')
			->validate();

		Assert::true($field->hasErrors());

		// Validator with wrong country value, correct type
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['NL', 'mobile'])
			->setValue('0499123456')
			->validate();

		Assert::true($field->hasErrors());

		// Validator with wrong country value, wrong type
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['NL', 'mobile'])
			->setValue('016123456')
			->validate();

		Assert::true($field->hasErrors());

		// Validator with multiple country values, one correct, correct type
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['BE', 'NL', 'mobile'])
			->setValue('0499123456')
			->validate();

		Assert::false($field->hasErrors());

		// Validator with multiple country values, one correct, wrong type
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['BE', 'NL', 'mobile'])
			->setValue('016123456')
			->validate();

		Assert::true($field->hasErrors());

		// Validator with multiple country values, none correct, correct type
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['DE', 'NL', 'mobile'])
			->setValue('0499123456')
			->validate();

		Assert::true($field->hasErrors());

		// Validator with multiple country values, none correct, wrong type
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['DE', 'NL', 'mobile'])
			->setValue('016123456')
			->validate();

		Assert::true($field->hasErrors());
	}

	public function testValidatePhoneWithCountryFieldWithType()
	{
		// Validator with correct country field supplied, correct type
		$field = $this->createControls();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['mobile'])
			->setValue('0499123456');

		$countryField = $field->getForm()->getComponent('phone_country');
		$countryField
			->setValue('BE');

		$field->validate();

		Assert::false($field->hasErrors());

		// Validator with correct country field supplied, wrong type
		$field = $this->createControls();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['mobile'])
			->setValue('016123456');

		$countryField = $field->getForm()->getComponent('phone_country');
		$countryField
			->setValue('BE');

		$field->validate();

		Assert::true($field->hasErrors());

		// Validator with wrong country field supplied, correct type
		$field = $this->createControls();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['mobile'])
			->setValue('0499123456');

		$countryField = $field->getForm()->getComponent('phone_country');
		$countryField
			->setValue('NL');

		$field->validate();

		Assert::true($field->hasErrors());

		// Validator with wrong country field supplied, wrong type
		$field = $this->createControls();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['mobile'])
			->setValue('016123456');

		$countryField = $field->getForm()->getComponent('phone_country');
		$countryField
			->setValue('NL');

		$field->validate();

		Assert::true($field->hasErrors());
	}

	public function testValidatePhoneAutomaticDetectionFromInternationalInput()
	{
		// Validator with correct international input
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['AUTO'])
			->setValue('+3216123456')
			->validate();

		Assert::false($field->hasErrors());

		// Validator with wrong international input
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['AUTO'])
			->setValue('003216123456')
			->validate();

		Assert::true($field->hasErrors());

		// Validator with wrong international input
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['AUTO'])
			->setValue('+321456')
			->validate();

		Assert::true($field->hasErrors());
	}

	/**
	 * @throws \IPub\Phone\Exceptions\NoValidCountryException
	 */
	public function testValidatePhoneNoDefaultCountryNoCountryField()
	{
		// Validator with no country field or given country
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone')
			->setValue('016123456')
			->validate();

		// Validator with no country field or given country, wrong type
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['mobile'])
			->setValue('016123456')
			->validate();

		// Validator with no country field or given country, correct type
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['mobile'])
			->setValue('0499123456')
			->validate();

		// Validator with no country field or given country, correct type, faulty parameter
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['mobile', 'xyz'])
			->setValue('0499123456')
			->validate();
	}

	/**
	 * @throws \IPub\Phone\Exceptions\InvalidParameterException
	 */
	public function testValidatePhoneFaultyParameters()
	{
		// Validator with given country, correct type, faulty parameter
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['BE', 'mobile', 'xyz'])
			->setValue('016123456')
			->validate();

		// Validator with country field, correct type, faulty parameter
		$field = $this->createControls();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['mobile', 'xyz'])
			->setValue('016123456');

		$countryField = $field->getForm()->getComponent('phone_country');
		$countryField
			->setValue('BE');

		$field->validate();
	}

	/**
	 * @throws \IPub\Phone\Exceptions\InvalidArgumentException
	 */
	public function testValidatorOnWrongControl()
	{
		// Validator with given country assigned to wrong control type
		$field = $this->createInvalidControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['BE'])
			->setValue('016123456')
			->validate();
	}

	/**
	 * @param array $data
	 *
	 * @return Forms\Controls\TextInput
	 */
	private function createControl(array $data = []) : Forms\Controls\TextInput
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_FILES = [];
		$_POST = $data;

		// Create form
		$form = new Forms\Form;
		// Create form control
		$control = new Forms\Controls\TextInput;
		// Add form control to form
		$form->addComponent($control, 'phone');

		return $control;
	}

	/**
	 * @param array $data
	 *
	 * @return Forms\Controls\SelectBox
	 */
	private function createInvalidControl(array $data = []) : Forms\Controls\SelectBox
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_FILES = [];
		$_POST = $data;

		// Create form
		$form = new Forms\Form;
		// Create form control
		$control = new Forms\Controls\TextArea;
		// Add form control to form
		$form->addComponent($control, 'phone');

		return $control;
	}

	/**
	 * @param array $data
	 *
	 * @return Forms\Controls\TextInput
	 */
	private function createControls(array $data = []) : Forms\Controls\TextInput
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_FILES = [];
		$_POST = $data;

		// Create form
		$form = new Forms\Form;

		// Create form control
		$control = new Forms\Controls\SelectBox;
		$control->setItems([
			'CZ' => 'Czech Republic',
			'SK' => 'Slovakia',
			'GB' => 'Great Britain',
			'BE' => 'Belgium',
			'NL' => 'Netherlands',
		]);
		// Add form control to form
		$form->addComponent($control, 'phone_country');

		// Create form control
		$control = new Forms\Controls\TextInput;
		// Add form control to form
		$form->addComponent($control, 'phone');

		return $control;
	}

}

\run(new PhoneValidationTest());
