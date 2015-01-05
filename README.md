<strong>2011-2014 BITSHARES</strong>



THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Bitshares plugin for Ecwid.

# Installation

1. Copy these files into your ecwid root directory
2. Copy Bitshares Checkout(https://github.com/sidhujag/bitsharescheckout) files into your ecwid root directory, overwrite any existing files.

# Configuration

In config.php:
1. Fill out config.php with the basic information and configure Bitshares Checkout
    - See the readme at https://github.com/sidhujag/bitsharescheckout
- set $hashValue - see below
- set $login - see below

In your Ecwid control panel:
- Click Payment Methods.  Rename any method you are not using to "Bitshares".
- Change Payment Processor to Credit Card: Authorize.net SIM
- Click Account Details
- API Login ID: choose something random here and copy it to config.php's $login variable.
- Transaction Key: choose something random
- MD5 Hash value: choose something random here and copy it to config.php's $hashValue variable.
- Transaction Type: Authorize.
- Click Advanced Settings.
- Type in the url `http://youwebsiteurl.com/ecwid/bitshares/redirect2bitshares.php` file on your server.
- Click Save
- Click Design > CSS Themes
- Either click "New CSS Theme" or edit your own theme.
- Add this to the text area of your custom theme:
<pre>
	/* bitshares checkout image */
		img.defaultCCImage {
padding: 50px 263px 0px 0px; 
background: url('http://youwebsiteurl.com/ecwid/bitshares/bitshares-logo.png'); 
background-size:auto; 
background-repeat:no-repeat;
width:0px; 
height: 30px;
		}
</pre>
- Click Save


Troubleshooting
---------------
- If you're using SSL, verify that your "notificationURL" for the invoice is "https://" (not "http://")
- If you're using SSL, ensure a valid SSL certificate is installed on your server. Also ensure your root CA cert is updated. If your CA cert is not current, you will see curl SSL verification errors.
- Verify that your callback handler at the "notificationURL" is properly receiving POSTs. You can verify this by POSTing your own messages to the server from a tool like Chrome Postman.
- Verify that the POST data received is properly parsed and that the logic that updates the order status on the merchants web server is correct.
- Verify that the merchants web server is not blocking POSTs from servers it may not recognize. Double check this on the firewall as well, if one is being used.
- Use the logging functionality to log errors during development. 
- Check the version of this plugin against the official repository to ensure you are using the latest version. Your issue might have been addressed in a newer version of the library.
- If all else fails, create an issue here.


Version
-------
- Bitshares plugin version 1.0
- Tested against Ecwid Version 16.8.541
