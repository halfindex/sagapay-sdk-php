<?php
/**
 * SagaPay PHP SDK - Exception
 * 
 * @package   SagaPay\SDK
 * @author    SagaPay Team
 * @copyright Copyright (c) 2025, SagaPay (https://sagapay.net)
 * @license   MIT
 * @version   1.0.0
 */

namespace SagaPay\SDK;

/**
 * SagaPay Exception Class
 */
class Exception extends \Exception
{
    /**
     * Error codes
     */
    public const NETWORK_ERROR = 1000;
    public const INVALID_RESPONSE = 1001;
    public const API_ERROR = 1002;
    public const INVALID_PARAM = 1003;
    public const INVALID_SIGNATURE = 1004;
    
    /**
     * HTTP status code
     */
    private int $httpCode;
    
    /**
     * Constructor
     * 
     * @param string $message  Error message
     * @param int    $code     Error code
     * @param int    $httpCode HTTP status code
     */
    public function __construct(string $message, int $code = 0, int $httpCode = 0)
    {
        parent::__construct($message, $code);
        $this->httpCode = $httpCode;
    }
    
    /**
     * Get HTTP status code
     * 
     * @return int HTTP status code
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}