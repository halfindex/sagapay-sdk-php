<?php
/**
 * SagaPay PHP SDK - Main Client
 * 
 * @package   SagaPay\SDK
 * @author    SagaPay Team
 * @copyright Copyright (c) 2025, SagaPay (https://sagapay.net)
 * @license   MIT
 * @version   1.0.0
 */

namespace SagaPay\SDK;

/**
 * SagaPay Client Class
 */
class Client
{
    /**
     * API credentials
     */
    private string $apiKey;
    private string $apiSecret;
    
    /**
     * API base URL
     */
    private string $baseUrl = 'https://api.sagapay.net';
    
    /**
     * Constructor
     * 
     * @param string $apiKey    Your SagaPay API key
     * @param string $apiSecret Your SagaPay API secret
     * @param string $baseUrl   Optional custom API base URL
     */
    public function __construct(string $apiKey, string $apiSecret, string $baseUrl = null)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        
        if ($baseUrl !== null) {
            $this->baseUrl = rtrim($baseUrl, '/');
        }
    }
    
    /**
     * Create a deposit address
     * 
     * @param array $params Parameters for creating a deposit
     * @return array Response data
     * @throws Exception If the request fails
     */
    public function createDeposit(array $params): array
    {
        $requiredParams = ['networkType', 'contractAddress', 'amount', 'ipnUrl'];
        Util::validateRequiredParams($params, $requiredParams);
        
        return $this->request('POST', '/create-deposit', $params);
    }
    
    /**
     * Create a withdrawal
     * 
     * @param array $params Parameters for creating a withdrawal
     * @return array Response data
     * @throws Exception If the request fails
     */
    public function createWithdrawal(array $params): array
    {
        $requiredParams = ['networkType', 'contractAddress', 'address', 'amount', 'ipnUrl'];
        Util::validateRequiredParams($params, $requiredParams);
        
        return $this->request('POST', '/create-withdrawal', $params);
    }
    
    /**
     * Check transaction status
     * 
     * @param string $address Blockchain address to check
     * @param string $type    Transaction type ("deposit" or "withdrawal")
     * @return array Response data
     * @throws Exception If the request fails
     */
    public function checkTransactionStatus(string $address, string $type): array
    {
        if (!in_array($type, ['deposit', 'withdrawal'])) {
            throw new Exception("Type must be 'deposit' or 'withdrawal'", Exception::INVALID_PARAM);
        }
        
        return $this->request('GET', '/check-transaction-status', [
            'address' => $address,
            'type' => $type
        ]);
    }
    
    /**
     * Fetch wallet balance
     * 
     * @param string $address         Blockchain address to check
     * @param string $networkType     Network type
     * @param string $contractAddress Contract address (optional, use "0" for native currency)
     * @return array Response data
     * @throws Exception If the request fails
     */
    public function fetchWalletBalance(string $address, string $networkType, string $contractAddress = '0'): array
    {
        return $this->request('GET', '/fetch-wallet-balance', [
            'address' => $address,
            'networkType' => $networkType,
            'contractAddress' => $contractAddress
        ]);
    }
    
    /**
     * Get API key
     * 
     * @return string API key
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }
    
    /**
     * Get API secret
     * 
     * @return string API secret
     */
    public function getApiSecret(): string
    {
        return $this->apiSecret;
    }
    
    /**
     * Make an API request
     * 
     * @param string $method HTTP method
     * @param string $path   API endpoint path
     * @param array  $params Request parameters
     * @return array Response data
     * @throws Exception If the request fails
     */
    private function request(string $method, string $path, array $params = []): array
    {
        $url = $this->baseUrl . $path;
        $headers = [
            'x-api-key: ' . $this->apiKey,
            'x-api-secret: ' . $this->apiSecret,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        $ch = curl_init();
        
        if ($method === 'GET') {
            $url .= '?' . http_build_query($params);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($response === false) {
            throw new Exception('cURL error: ' . $error, Exception::NETWORK_ERROR);
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . $response, Exception::INVALID_RESPONSE);
        }
        
        if ($httpCode >= 400) {
            $message = isset($data['message']) ? $data['message'] : 'API error';
            $code = isset($data['error']) ? $data['error'] : Exception::API_ERROR;
            throw new Exception($message, $code, $httpCode);
        }
        
        return $data;
    }
}