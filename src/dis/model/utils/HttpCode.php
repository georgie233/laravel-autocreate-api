<?php


namespace App\utils;

/***
 * Class HttpCode
 * @package App\utils
 * @author Georgie
 * @link http://www.georgie.cn/375.html
 * @link http://www.jia233.cn/375.html
 */
class HttpCode
{
    //相关描述参考上方链接
    public static $Continue = 100;
    public static $SwitchingProtocols = 101;
    public static $OK = 200;
    public static $Created = 201;
    public static $Accepted = 202;
    public static $NonAuthoritativeInformation = 203;
    public static $NoContent = 204;
    public static $ResetContent = 205;
    public static $PartialContent = 206;
    public static $MultipleChoices = 300;
    public static $MovedPermanently = 301;
    public static $Found = 302;
    public static $SeeOther = 303;
    public static $NotModified = 304;
    public static $UseProxy = 305;
    public static $TemporaryRedirect = 307;
    public static $BadRequest = 400;
    public static $Unauthorized = 401;
    public static $PaymentRequired = 402;
    public static $Forbidden = 403;
    public static $NotFound = 404;
    public static $MethodNotAllowed = 405;
    public static $NotAcceptable = 406;
    public static $ProxyAuthenticationRequired = 407;
    public static $RequestTimeOut = 408;
    public static $Conflict = 409;
    public static $Gone = 410;
    public static $LengthRequired = 411;
    public static $PreconditionFailed = 412;
    public static $RequestEntityTooLarge = 413;
    public static $RequestURITooLarge = 414;
    public static $UnsupportedMediaType = 415;
    public static $RequestedRangeNotSatisfiable = 416;
    public static $ExpectationFailed = 417;
    public static $InternalServerError = 500;
    public static $NotImplemented = 501;
    public static $BadGateway = 502;
    public static $ServiceUnavailable = 503;
    public static $GatewayTimeOut = 504;
    public static $HTTPVersionNotSupported = 505;
}
