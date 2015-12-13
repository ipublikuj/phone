<?php
/**
 * Test: IPub\Phone\Phone
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
use Nette\Forms;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Phone;

use libphonenumber;

require __DIR__ . '/../bootstrap.php';

class PhoneValidationTest extends Tester\TestCase
{
	public function testValidatePhoneWithDefaultCountryNoType()
	{
		// Validator with correct country value.
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['BR'])
			->setValue('016123456')
			->validate();

		Assert::true($field->hasErrors());

		// Validator with wrong country value.
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['NL'])
			->setValue('016123456')
			->validate();

		Assert::false($field->hasErrors());

		// Validator with multiple country values, one correct.
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['BE', 'NL'])
			->setValue('016123456')
			->validate();

		Assert::true($field->hasErrors());

		// Validator with multiple country values, value correct for second country in list.
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['NL', 'BE'])
			->setValue('016123456')
			->validate();

		Assert::true($field->hasErrors());

		// Validator with multiple wrong country values
		$field = $this->createControl();
		$field
			->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone', ['NL', 'DE'])
			->setValue('016123456')
			->validate();

		Assert::false($field->hasErrors());
	}

	/**
	 * @param array $data
	 *
	 * @return Forms\Controls\TextInput
	 */
	private function createControl($data = [])
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
	private function createInvalidControl($data = [])
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_FILES = [];
		$_POST = $data;

		// Create form
		$form = new Forms\Form;
		// Create form control
		$control = new Forms\Controls\SelectBox;
		// Add form control to form
		$form->addComponent($control, 'phone');

		return $control;
	}

	/**
	 * @param array $data
	 *
	 * @return Forms\Controls\TextInput
	 */
	private function createControls($data = [])
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
		// Create form control
		$control = new Forms\Controls\SelectBox;
		$control->setItems([
			'CZ' => 'Czech Republic',
			'SK' => 'Slovakia',
			'GB' => 'Great Britain',
		]);
		// Add form control to form
		$form->addComponent($control, 'phone_country');

		return $control;
	}

}

\run(new PhoneValidationTest());
