<?php

namespace LCMS\Storage;

class UploadResponse
{
    /**
     * @param bool $success Whether the upload was successful
     * @param string $message Success or error message from the API
     * @param array $url URL object containing 'asset' and 'upload' keys
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly array $url
    ) {}

    /**
     * Create UploadResponse from API response data
     *
     * @param array $data API response data
     * @return self
     */
    public static function from(array $data): self
    {
        return new self(
            success: $data['success'] ?? false,
            message: $data['message'] ?? '',
            url: $data['url'] ?? []
        );
    }

    /**
     * Convert response to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }

    /**
     * Convert response to JSON
     *
     * @param int $options JSON encoding options
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
