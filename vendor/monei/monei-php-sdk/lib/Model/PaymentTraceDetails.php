<?php
/**
 * PaymentTraceDetails
 *
 * PHP version 7.4
 *
 * @category Class
 * @package  OpenAPI\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * MONEI API v1
 *
 * <p>The MONEI API is organized around <a href=\"https://en.wikipedia.org/wiki/Representational_State_Transfer\">REST</a>. Our API has predictable resource-oriented URLs, accepts JSON-encoded request bodies, returns JSON-encoded responses, and uses standard HTTP response codes, authentication, and verbs.</p> <h4 id=\"base-url\">Base URL:</h4> <p><a href=\"https://api.monei.com/v1\">https://api.monei.com/v1</a></p> <h4 id=\"client-libraries\">Client libraries:</h4> <ul> <li><a href=\"https://github.com/MONEI/monei-php-sdk\">PHP SDK</a></li> <li><a href=\"https://github.com/MONEI/monei-python-sdk\">Python SDK</a></li> <li><a href=\"https://github.com/MONEI/monei-node-sdk\">Node.js SDK</a></li> <li><a href=\"https://postman.monei.com/\">Postman</a></li> </ul> <h4 id=\"important\">Important:</h4> <p><strong>If you are not using our official SDKs, you need to provide a valid <code>User-Agent</code> header in each request, otherwise your requests will be rejected.</strong></p>
 *
 * The version of the OpenAPI document: 1.4.4
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 6.0.1
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace OpenAPI\Client\Model;

use \ArrayAccess;
use \OpenAPI\Client\ObjectSerializer;

/**
 * PaymentTraceDetails Class Doc Comment
 *
 * @category Class
 * @description Information related to the browsing session of the user who initiated the payment.
 * @package  OpenAPI\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class PaymentTraceDetails implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'Payment-TraceDetails';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'ip' => 'string',
        'country_code' => 'string',
        'lang' => 'string',
        'device_type' => 'string',
        'device_model' => 'string',
        'browser' => 'string',
        'browser_version' => 'string',
        'os' => 'string',
        'os_version' => 'string',
        'source' => 'string',
        'source_version' => 'string',
        'user_agent' => 'string',
        'browser_accept' => 'string',
        'browser_color_depth' => 'int',
        'browser_screen_height' => 'int',
        'browser_screen_width' => 'int',
        'browser_timezone_offset' => 'string',
        'user_id' => 'string',
        'user_email' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'ip' => null,
        'country_code' => null,
        'lang' => null,
        'device_type' => null,
        'device_model' => null,
        'browser' => null,
        'browser_version' => null,
        'os' => null,
        'os_version' => null,
        'source' => null,
        'source_version' => null,
        'user_agent' => null,
        'browser_accept' => null,
        'browser_color_depth' => 'string',
        'browser_screen_height' => 'string',
        'browser_screen_width' => 'string',
        'browser_timezone_offset' => null,
        'user_id' => null,
        'user_email' => null
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'ip' => 'ip',
        'country_code' => 'countryCode',
        'lang' => 'lang',
        'device_type' => 'deviceType',
        'device_model' => 'deviceModel',
        'browser' => 'browser',
        'browser_version' => 'browserVersion',
        'os' => 'os',
        'os_version' => 'osVersion',
        'source' => 'source',
        'source_version' => 'sourceVersion',
        'user_agent' => 'userAgent',
        'browser_accept' => 'browserAccept',
        'browser_color_depth' => 'browserColorDepth',
        'browser_screen_height' => 'browserScreenHeight',
        'browser_screen_width' => 'browserScreenWidth',
        'browser_timezone_offset' => 'browserTimezoneOffset',
        'user_id' => 'userId',
        'user_email' => 'userEmail'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'ip' => 'setIp',
        'country_code' => 'setCountryCode',
        'lang' => 'setLang',
        'device_type' => 'setDeviceType',
        'device_model' => 'setDeviceModel',
        'browser' => 'setBrowser',
        'browser_version' => 'setBrowserVersion',
        'os' => 'setOs',
        'os_version' => 'setOsVersion',
        'source' => 'setSource',
        'source_version' => 'setSourceVersion',
        'user_agent' => 'setUserAgent',
        'browser_accept' => 'setBrowserAccept',
        'browser_color_depth' => 'setBrowserColorDepth',
        'browser_screen_height' => 'setBrowserScreenHeight',
        'browser_screen_width' => 'setBrowserScreenWidth',
        'browser_timezone_offset' => 'setBrowserTimezoneOffset',
        'user_id' => 'setUserId',
        'user_email' => 'setUserEmail'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'ip' => 'getIp',
        'country_code' => 'getCountryCode',
        'lang' => 'getLang',
        'device_type' => 'getDeviceType',
        'device_model' => 'getDeviceModel',
        'browser' => 'getBrowser',
        'browser_version' => 'getBrowserVersion',
        'os' => 'getOs',
        'os_version' => 'getOsVersion',
        'source' => 'getSource',
        'source_version' => 'getSourceVersion',
        'user_agent' => 'getUserAgent',
        'browser_accept' => 'getBrowserAccept',
        'browser_color_depth' => 'getBrowserColorDepth',
        'browser_screen_height' => 'getBrowserScreenHeight',
        'browser_screen_width' => 'getBrowserScreenWidth',
        'browser_timezone_offset' => 'getBrowserTimezoneOffset',
        'user_id' => 'getUserId',
        'user_email' => 'getUserEmail'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }


    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['ip'] = $data['ip'] ?? null;
        $this->container['country_code'] = $data['country_code'] ?? null;
        $this->container['lang'] = $data['lang'] ?? null;
        $this->container['device_type'] = $data['device_type'] ?? null;
        $this->container['device_model'] = $data['device_model'] ?? null;
        $this->container['browser'] = $data['browser'] ?? null;
        $this->container['browser_version'] = $data['browser_version'] ?? null;
        $this->container['os'] = $data['os'] ?? null;
        $this->container['os_version'] = $data['os_version'] ?? null;
        $this->container['source'] = $data['source'] ?? null;
        $this->container['source_version'] = $data['source_version'] ?? null;
        $this->container['user_agent'] = $data['user_agent'] ?? null;
        $this->container['browser_accept'] = $data['browser_accept'] ?? null;
        $this->container['browser_color_depth'] = $data['browser_color_depth'] ?? null;
        $this->container['browser_screen_height'] = $data['browser_screen_height'] ?? null;
        $this->container['browser_screen_width'] = $data['browser_screen_width'] ?? null;
        $this->container['browser_timezone_offset'] = $data['browser_timezone_offset'] ?? null;
        $this->container['user_id'] = $data['user_id'] ?? null;
        $this->container['user_email'] = $data['user_email'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets ip
     *
     * @return string|null
     */
    public function getIp()
    {
        return $this->container['ip'];
    }

    /**
     * Sets ip
     *
     * @param string|null $ip The IP address where the operation originated.
     *
     * @return self
     */
    public function setIp($ip)
    {
        $this->container['ip'] = $ip;

        return $this;
    }

    /**
     * Gets country_code
     *
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->container['country_code'];
    }

    /**
     * Sets country_code
     *
     * @param string|null $country_code Two-letter country code ([ISO 3166-1 alpha-2](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)).
     *
     * @return self
     */
    public function setCountryCode($country_code)
    {
        $this->container['country_code'] = $country_code;

        return $this;
    }

    /**
     * Gets lang
     *
     * @return string|null
     */
    public function getLang()
    {
        return $this->container['lang'];
    }

    /**
     * Sets lang
     *
     * @param string|null $lang Two-letter language code ([ISO 639-1](https://en.wikipedia.org/wiki/ISO_639-1)).
     *
     * @return self
     */
    public function setLang($lang)
    {
        $this->container['lang'] = $lang;

        return $this;
    }

    /**
     * Gets device_type
     *
     * @return string|null
     */
    public function getDeviceType()
    {
        return $this->container['device_type'];
    }

    /**
     * Sets device_type
     *
     * @param string|null $device_type Device type, could be `desktop`, `mobile`, `smartTV`, `tablet`.
     *
     * @return self
     */
    public function setDeviceType($device_type)
    {
        $this->container['device_type'] = $device_type;

        return $this;
    }

    /**
     * Gets device_model
     *
     * @return string|null
     */
    public function getDeviceModel()
    {
        return $this->container['device_model'];
    }

    /**
     * Sets device_model
     *
     * @param string|null $device_model Information about the device used for the browser session (e.g., `iPhone`).
     *
     * @return self
     */
    public function setDeviceModel($device_model)
    {
        $this->container['device_model'] = $device_model;

        return $this;
    }

    /**
     * Gets browser
     *
     * @return string|null
     */
    public function getBrowser()
    {
        return $this->container['browser'];
    }

    /**
     * Sets browser
     *
     * @param string|null $browser The browser used in this browser session (e.g., `Mobile Safari`).
     *
     * @return self
     */
    public function setBrowser($browser)
    {
        $this->container['browser'] = $browser;

        return $this;
    }

    /**
     * Gets browser_version
     *
     * @return string|null
     */
    public function getBrowserVersion()
    {
        return $this->container['browser_version'];
    }

    /**
     * Sets browser_version
     *
     * @param string|null $browser_version The version for the browser session (e.g., `13.1.1`).
     *
     * @return self
     */
    public function setBrowserVersion($browser_version)
    {
        $this->container['browser_version'] = $browser_version;

        return $this;
    }

    /**
     * Gets os
     *
     * @return string|null
     */
    public function getOs()
    {
        return $this->container['os'];
    }

    /**
     * Sets os
     *
     * @param string|null $os Operation system (e.g., `iOS`).
     *
     * @return self
     */
    public function setOs($os)
    {
        $this->container['os'] = $os;

        return $this;
    }

    /**
     * Gets os_version
     *
     * @return string|null
     */
    public function getOsVersion()
    {
        return $this->container['os_version'];
    }

    /**
     * Sets os_version
     *
     * @param string|null $os_version Operation system version (e.g., `13.5.1`).
     *
     * @return self
     */
    public function setOsVersion($os_version)
    {
        $this->container['os_version'] = $os_version;

        return $this;
    }

    /**
     * Gets source
     *
     * @return string|null
     */
    public function getSource()
    {
        return $this->container['source'];
    }

    /**
     * Sets source
     *
     * @param string|null $source The source component from where the operation was generated (mostly for our SDK's).
     *
     * @return self
     */
    public function setSource($source)
    {
        $this->container['source'] = $source;

        return $this;
    }

    /**
     * Gets source_version
     *
     * @return string|null
     */
    public function getSourceVersion()
    {
        return $this->container['source_version'];
    }

    /**
     * Sets source_version
     *
     * @param string|null $source_version The source component version from where the operation was generated (mostly for our SDK's).
     *
     * @return self
     */
    public function setSourceVersion($source_version)
    {
        $this->container['source_version'] = $source_version;

        return $this;
    }

    /**
     * Gets user_agent
     *
     * @return string|null
     */
    public function getUserAgent()
    {
        return $this->container['user_agent'];
    }

    /**
     * Sets user_agent
     *
     * @param string|null $user_agent Full user agent string of the browser session.
     *
     * @return self
     */
    public function setUserAgent($user_agent)
    {
        $this->container['user_agent'] = $user_agent;

        return $this;
    }

    /**
     * Gets browser_accept
     *
     * @return string|null
     */
    public function getBrowserAccept()
    {
        return $this->container['browser_accept'];
    }

    /**
     * Sets browser_accept
     *
     * @param string|null $browser_accept Browser accept header.
     *
     * @return self
     */
    public function setBrowserAccept($browser_accept)
    {
        $this->container['browser_accept'] = $browser_accept;

        return $this;
    }

    /**
     * Gets browser_color_depth
     *
     * @return int|null
     */
    public function getBrowserColorDepth()
    {
        return $this->container['browser_color_depth'];
    }

    /**
     * Sets browser_color_depth
     *
     * @param int|null $browser_color_depth The color depth of the browser session (e.g., `24`).
     *
     * @return self
     */
    public function setBrowserColorDepth($browser_color_depth)
    {
        $this->container['browser_color_depth'] = $browser_color_depth;

        return $this;
    }

    /**
     * Gets browser_screen_height
     *
     * @return int|null
     */
    public function getBrowserScreenHeight()
    {
        return $this->container['browser_screen_height'];
    }

    /**
     * Sets browser_screen_height
     *
     * @param int|null $browser_screen_height The screen height of the browser session (e.g., `1152`).
     *
     * @return self
     */
    public function setBrowserScreenHeight($browser_screen_height)
    {
        $this->container['browser_screen_height'] = $browser_screen_height;

        return $this;
    }

    /**
     * Gets browser_screen_width
     *
     * @return int|null
     */
    public function getBrowserScreenWidth()
    {
        return $this->container['browser_screen_width'];
    }

    /**
     * Sets browser_screen_width
     *
     * @param int|null $browser_screen_width The screen width of the browser session (e.g., `2048`).
     *
     * @return self
     */
    public function setBrowserScreenWidth($browser_screen_width)
    {
        $this->container['browser_screen_width'] = $browser_screen_width;

        return $this;
    }

    /**
     * Gets browser_timezone_offset
     *
     * @return string|null
     */
    public function getBrowserTimezoneOffset()
    {
        return $this->container['browser_timezone_offset'];
    }

    /**
     * Sets browser_timezone_offset
     *
     * @param string|null $browser_timezone_offset The timezone offset of the browser session (e.g., `-120`).
     *
     * @return self
     */
    public function setBrowserTimezoneOffset($browser_timezone_offset)
    {
        $this->container['browser_timezone_offset'] = $browser_timezone_offset;

        return $this;
    }

    /**
     * Gets user_id
     *
     * @return string|null
     */
    public function getUserId()
    {
        return $this->container['user_id'];
    }

    /**
     * Sets user_id
     *
     * @param string|null $user_id The ID of the user that started the operation.
     *
     * @return self
     */
    public function setUserId($user_id)
    {
        $this->container['user_id'] = $user_id;

        return $this;
    }

    /**
     * Gets user_email
     *
     * @return string|null
     */
    public function getUserEmail()
    {
        return $this->container['user_email'];
    }

    /**
     * Sets user_email
     *
     * @param string|null $user_email The email of the user that started the operation.
     *
     * @return self
     */
    public function setUserEmail($user_email)
    {
        $this->container['user_email'] = $user_email;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
       return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


