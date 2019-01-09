var style = document.createElement('style');

style.innerHTML =
  '@import "https://fonts.googleapis.com/css?family=Montserrat";' +
	'body::after {' +
    'font-family: "Montserrat", sans-serif;' +
    'font-weight: 600;' +
    'font-size: 17px;' +
    'content: "ERROR: The library has been found in our registry (E_ASSET_JS_04)";' +
    'text-align: center;' +
    'vertical-align: middle;' +
    'line-height: 55px;' +
    'position: absolute;' +
    'top: 0;' +
    'left: 0;' +
    'width: 100%;' +
    'height: 60px;' +
    'background-color: red;' +
	'}';

var ref = document.querySelector('script');
ref.parentNode.insertBefore(style, ref);
