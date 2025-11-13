<?php

namespace LCMS\Storage;

class ListResponse
{
    /**
     * @param bool $success Whether the list operation was successful
     * @param string $message Success or error message from the API
     * @param string $path The directory path that was listed
     * @param string $assetBaseUrl Base URL for accessing assets
     * @param array $items Array of file items in the directory
     * @param array $prefixes Array of subdirectory prefixes
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly string $path,
        public readonly string $assetBaseUrl,
        public readonly array $items = [],
        public readonly array $prefixes = []
    ) {}

    /**
     * Create ListResponse from API response data
     *
     * @param array $data API response data
     * @return self
     */
    public static function from(array $data): self
    {
        $directory = $data['directory'] ?? $data;

        return new self(
            success: $data['success'] ?? true,
            message: $data['message'] ?? '',
            path: $directory['path'] ?? '',
            assetBaseUrl: $directory['asset_base_url'] ?? '',
            items: $directory['items'] ?? [],
            prefixes: $directory['prefixes'] ?? []
        );
    }

    /**
     * Get total number of items
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Check if directory is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items) && empty($this->prefixes);
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
            'path' => $this->path,
            'assetBaseUrl' => $this->assetBaseUrl,
            'items' => $this->items,
            'prefixes' => $this->prefixes,
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
