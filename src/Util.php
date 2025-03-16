<?php
/**
 * SagaPay PHP SDK - Utilities
 * 
 * @package   SagaPay\SDK
 * @author    SagaPay Team
 * @copyright Copyright (c) 2025, SagaPay (https://sagapay.net)
 * @license   MIT
 * @version   1.0.0
 */

namespace SagaPay\SDK;

/**
 * SagaPay Utilities Class
 */
class Util
{
    /**
     * Validate required parameters
     * 
     * @param array $params         Parameters to validate
     * @param array $requiredParams List of required parameter keys
     * @throws Exception If any required parameter is missing
     */
    public static function validateRequiredParams(array $params, array $requiredParams): void
    {
        foreach ($requiredParams as $param) {
            if (!isset($params[$param]) || $params[$param] === '') {
                throw new Exception("Missing required parameter: {$param}", Exception::INVALID_PARAM);
            }
        }
    }
    
    /**
     * Generate HMAC signature
     * 
     * @param string $payload Data to sign
     * @param string $secret  Secret key
     * @return string Signature
     */
    public static function generateSignature(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }
    
    /**
     * Verify HMAC signature
     * 
     * @param string $payload   Data that was signed
     * @param string $signature Signature to verify
     * @param string $secret    Secret key
     * @return bool Whether the signature is valid
     */
    public static function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = self::generateSignature($payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Get current timestamp in ISO 8601 format
     * 
     * @return string ISO 8601 timestamp
     */
    public static function getIsoTimestamp(): string
    {
        return (new \DateTime())->format('c');
    }
}