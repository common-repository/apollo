<?php
namespace Spaceinvoices;

/**
 * Class Spaceinvoices
 *
 * @package Spaceinvoices
 */

class Spaceinvoices
{
    // @var string token to be used for requests.
    public static $accessToken = null;
    // @var string The base URL for the Space invoices API.
    public static $apiBaseUrl = 'https://api.spaceinvoices.com/v1';
    // public static $apiBaseUrl = 'https://api-test.spaceinvoices.com/v1';



    // @var string|null The version of the Space invoices API to use for requests.
    public static $apiVersion = null;
    const VERSION = '0.0.1';

    /**
     * Gets the accessToken to be used for requests.
     *
     * @return string $accessToken
     */
    public static function getAccessToken()
    {
        return self::$accessToken;
    }

    /**
     * Sets the accessToken to be used for requests.
     *
     * @param string $accessToken
     */
    public static function setAccessToken($accessToken)
    {
        self::$accessToken = $accessToken;
    }

    /**
     *  @return string The API version used for requests. null if we're using the
     *    latest version.
     */
    public static function getApiVersion()
    {
        return self::$apiVersion;
    }

    /**
     * @param string $apiVersion The API version to use for requests.
     */
    public static function setApiVersion($apiVersion)
    {
        self::$apiVersion = $apiVersion;
    }

}