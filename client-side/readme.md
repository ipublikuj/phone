## Javascript API Documentation

API for Phone extension is accessible in global object `window.IPub.Phone`.

### Forms live validation

To enable live validation on your forms, you need to include JS files from this extension, insert them into you layout or use some third-party extension for managing static files

```html
<!DOCTYPE html>
<html class="uk-height-1-1 uk-notouch" lang="en">
<head>
    // ...
    <script src="path-to-file/ipub.phone.js" type="text/javascript">
    <script src="path-to-file/ipub.phone.library.js" type="text/javascript">
    // ...
</head>
```

and that is it.

### Calling API methods

JavaScript part of this extension come with some useful methods. You can use them with the `IPub.Phone.Utils` object.

#### Validating phone number

```js
var phoneUtils = new IPub.Phone.Utils;

if (phoneUtils.isValidNumber('+123456789', 'US')) {
    alert('Phone is valid');
}
```

#### Formating phone number

```js
var phoneUtils = new IPub.Phone.Utils;

alert('Number in international format: ' + phoneUtils.formatInternational('+123456789', 'US'));

alert('Number in local format: ' + phoneUtils.formatLocal('+123456789', 'US'));

alert('Number in E164 format: ' + phoneUtils.formatE164('+123456789', 'US'));
```

### About JS part

This part of Phone extension is based on google's library too, but this part has **NO** auto update! It was last synced on December 15, 2015.

If it cause some problems, and fresh data will help, fell free to use [Google's Closure Compile Service](http://closure-compiler.appspot.com/home) to update base library.

1. Open [closure.txt](https://github.com/iPublikuj/phone/blob/master/client-side/closure.txt) in this part of extension and copy whole content to your clipboard
2. Open [Google's Closure Compile Service](http://closure-compiler.appspot.com/home) and the content of [closure.txt](https://github.com/iPublikuj/phone/blob/master/client-side/closure.txt) into left big textarea
3. Make sure you have overwritten all previous content
4. Press **Compile** button and wait for result
5. If everything is ok, on the right side of page will be freshly compiled [ipub.phone.library.js](https://github.com/iPublikuj/phone/blob/master/client-side/js/ipub.phone.library.js)
6. Update this file in your local fork of this repository
7. Commit and submit a Pull Request
