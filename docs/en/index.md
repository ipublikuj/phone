# Quickstart

This extension brings you a phone number validator & formatter.

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
    public $phone;

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

### Using trait

If you are using PHP ver. 4.0 and higher, you can use simple trait in your presenters and components, to inject helper service:

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter
{
	use IPub\Phone\TPhone;
}
```
