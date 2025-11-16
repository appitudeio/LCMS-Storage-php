<?php

namespace LCMS;

use Exception;
use InvalidArgumentException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LCMS\Storage\UploadResponse;
use LCMS\Storage\ListResponse;

class Storage
{
    private Client $client;
    private string $domain;
    private string $apiKey;
    private string $apiUrl = 'https://api.logicalcms.com';

    /**
     * Initialize LCMS Storage client
     *
     * @param string $domain Your domain (e.g., 'example.com')
     * @param string $apiKey Your API key
     * @throws InvalidArgumentException If domain or API key is missing
     */
    public function __construct(string $domain, string $apiKey)
    {
        if (empty($domain)) {
            throw new InvalidArgumentException('Domain is required');
        }

        if (empty($apiKey)) {
            throw new InvalidArgumentException('API key is required');
        }

        $this->domain = $domain;
        $this->apiKey = $apiKey;

        $this->client = new Client([
            'base_uri' => "{$this->apiUrl}/{$this->domain}/asset/",
            'timeout' => 30,
        ]);
    }

    /**
     * Upload a file to LCMS Storage
     *
     * @param string $filePath Local file path to upload
     * @param string $remotePath Remote path where file should be stored (e.g., '/images/logo.png')
     * @return UploadResponse
     * @throws Exception If upload fails
     */
    public function upload(string $filePath, string $remotePath): UploadResponse
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File not found: {$filePath}");
        }

        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $normalizedPath = ltrim($this->normalizePath($remotePath), '/');

        try {
            // Step 1: Get presigned upload URL
            $response = $this->client->post($normalizedPath, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            // Step 2: Upload file to S3 via presigned URL
            $uploadClient = new Client(['timeout' => 60]);
            $uploadClient->put($data['url']['upload'], [
                'body' => fopen($filePath, 'r'),
                'headers' => [
                    'Content-Type' => $mimeType,
                ],
            ]);

            return UploadResponse::from($data);
        } catch (GuzzleException $e) {
            $errorMessage = $this->extractErrorMessage($e);
            throw new Exception('Failed to upload file: ' . $errorMessage, 0, $e);
        }
    }

    /**
     * Delete a file from LCMS Storage
     *
     * @param string $path Path to file to delete (e.g., '/images/logo.png')
     * @return void
     * @throws Exception If deletion fails
     */
    public function delete(string $path): void
    {
        $normalizedPath = ltrim($this->normalizePath($path), '/');

        try {
            $this->client->delete($normalizedPath, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                ],
            ]);
        } catch (GuzzleException $e) {
            $errorMessage = $this->extractErrorMessage($e);
            throw new Exception('Failed to delete file: ' . $errorMessage, 0, $e);
        }
    }

    /**
     * List directory contents
     *
     * @param string $path Directory path to list (e.g., '/images' or '' for root)
     * @return ListResponse
     * @throws Exception If listing fails
     */
    public function list(string $path = ''): ListResponse
    {
        $normalizedPath = ltrim($this->normalizePath($path), '/');

        try {
            $response = $this->client->get($normalizedPath, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (!isset($data['directory'])) {
                throw new Exception('Invalid response from server');
            }

            return ListResponse::from($data);
        } catch (GuzzleException $e) {
            $errorMessage = $this->extractErrorMessage($e);
            throw new Exception('Failed to list directory: ' . $errorMessage, 0, $e);
        }
    }

    /**
     * Normalize file path
     *
     * @param string $path Input path
     * @return string Normalized path
     */
    private function normalizePath(string $path): string
    {
        $path = trim($path);
        $path = '/' . ltrim($path, '/');
        return $path;
    }

    /**
     * Extract error message from Guzzle exception
     *
     * @param GuzzleException $e The exception
     * @return string Error message
     */
    private function extractErrorMessage(GuzzleException $e): string
    {
        if (!$e->hasResponse()) {
            return $e->getMessage();
        }

        $responseBody = $e->getResponse()->getBody()->getContents();
        $errorData = json_decode($responseBody, true);

        return $errorData['error']
            ?? $errorData['message']
            ?? $errorData['Message']  // AWS sometimes uses capital M
            ?? $e->getMessage();
    }
}