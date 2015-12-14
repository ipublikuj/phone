# Quickstart

This extension brings you a phone number validator for your forms and also brings you some useful macros and helpers for better rendering in templates.
This extension is based on the [PHP port](https://github.com/giggsey/libphonenumber-for-php) of [Google's libphonenumber API](https://github.com/googlei18n/libphonenumber) by [giggsey](https://github.com/giggsey).

## Installation

The best way to install ipub/phone is using  [Composer](http://getcomposer.org/):

```sh
$ composer require ipub/phone
```

After that you have to register extension in config.neon.

```neon
extensions:
    phone: IPub\Phone\DI\PhoneExtension
```

## Usage

### Use it with forms as validator

Phone number validator from this extension can operate in three ways:

#### With defined country codes

In the rule definition you can list allowed countries in [ISO 3166-1 alpha-2 compliant](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements)

```php
    $form = new Nette\Application\UI\Form;

    $form
        ->addText('phone_number', 'Your phone number')
        ->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone.', ['GB', 'US', 'CZ']);
```

Now the given value is checked if is valid phone number in one of defined country.

#### With special field for country

In this way, you can create filed with suffix `_country` which will be used as a country definition:

```php
    $form = new Nette\Application\UI\Form;

    $form
        ->addText('phone_number', 'Your phone number')
        ->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone.', ['GB', 'US', 'CZ']);

    $form
        ->addText('phone_number_country', 'Select your country')
        ->setItems([
            'CZ' => 'Czech Republic',
            'SK' => 'Slovakia',
            'GB' => 'Great Britain',
            'BE' => 'Belgium',
            'NL' => 'Netherlands',
        ]);
```

Validator will search for this field and use selected value as a validation rule for the phone number field.

#### Automatic country detection

And in this way, you don't have to specify list of countries, just use automatic detection.

```php
    $form = new Nette\Application\UI\Form;

    $form
        ->addText('phone_number', 'Your phone number')
        ->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone. Enter it in international format.', ['AUTO']);
```

Validator try to extract country from given number.

Note: Inserted value must be in international format (prefixed with a + sign, e.g. +420 ....). Leading double zeros will **NOT** be parsed correctly as this isn't an established consistency.

#### Validate phone type

This validator support also validating of phone type eg. mobile, fixed line etc. To use this feature, just add phone type in rule parameters:

```php
    $form = new Nette\Application\UI\Form;

    $form
        ->addText('phone_number', 'Your phone number')
        ->addRule(Phone\Forms\PhoneValidator::PHONE, 'Invalid phone. Enter it in international format.', ['AUTO', 'mobile', 'fixed_line']);
```

The most common types are 'mobile' and 'fixed_line', but feel free to use any of the types defined [here](https://github.com/iPublikuj/phone/blob/master/src/IPub/Phone/Phone.php).

## Using in presenters and components

This extension have service which could be used for formatting and validating phone numbers.

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter
{
    /**
     * @inject
     * @var \IPub\Phone\Phone
     */
    public $phone

    public function actionDefault()
    {
        // ...

        // Check if phone number is valid
        if ($this->phone->isValid($phone)) {
            // Do something if phone is valid
        } else {
            // Do something if phone is not valid
        }

        // ...
    }
    
    public function renderDefault()
    {
        // ...

        // Format phone number for displaying
        $this->template->phoneNumber = $this->phone->format($phone);

        // Get phone number carrier name
        $this->template->phoneCarrierName = $this->phone->getCarrier($phone);

        // Get phone number location
        $this->template->phoneLocation = $this->phone->getLocation($phone);

        // Get phone number all time zones in a array
        $this->template->phoneTimeZones = $this->phone->getTimeZones($phone);

        // ...
    }
}
```

Second parameter of each helper method is `country code` so if is not specified, the number have to be in international format, if not, exception will be thrown.

Method for getting location info `getLocation` has additional params:

* `$locale` - the language code for which the description should be written. If you are using translator which could return locale eg. en_US, helper will automatically use this translator.
* `$userCountry` - the region code for a given user. This region will be omitted from the description if the phone number comes from this region. It is a two-letter uppercase ISO country code as defined by ISO 3166-1.

## Using in Latte

In Latte templates you can use macros and helpers for formatting phone numbers

```html
<p>
    Phone number: {phone '016123456', 'BE'}
</p>
```

and it will render output like:

```html
<p>
    Phone number: +32 16 12 34 56
</p>
```

And if you need to use helper, you can do it this way:

```html
<p>
    Phone number: {$phoneNumber|phone:'BE'}
</p>
```

and it will render output like:

```html
<p>
    Phone number: +32 16 12 34 56
</p>
```

Both, macro and helper have two optional parameters `country code` and `number format`. Country code have to be in [ISO 3166-1 alpha-2 compliant](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements) and format you can choose from [defined constants](https://github.com/iPublikuj/phone/blob/master/src/IPub/Phone/Phone.php) with prefix `TYPE_`
