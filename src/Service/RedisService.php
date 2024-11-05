<?php

declare(strict_types=1);

namespace App\Service;

use Predis\Client;

final class RedisService
{
    public const PROJECT_NAME = 'pp';

    public function __construct(private Client $redis)
    {
    }

    public function generateKey(string $value): string
    {
        return self::PROJECT_NAME . ':' . $value;
    }

    public function exists(string $key): int
    {
        return $this->redis->exists($key);
    }

    public function get(string $key)
    {
        $value = $this->redis->get($key);
        // Check for false to handle non-existing keys
        if ($value === false) {
            return null;
        }
        // Ensure the value is actually a string before trying to decode it
        if (is_string($value)) {
            return json_decode($value, true);  // Decode as associative array
        }
        // Return null if the value isn't a string (safety check, though it should always be a string if not false)
        return null;
    }
    

    public function set(string $key, $value): void
    {
        if (is_array($value) || is_object($value)) {
            $this->redis->set($key, json_encode($value));
        } else {
            throw new InvalidArgumentException("Invalid data type. Only arrays and objects are supported.");
        }
    }

    public function setex(string $key, $value, int $ttl = 3600): void
    {
        if (is_array($value) || is_object($value)) {
            $this->redis->setex($key, $ttl, json_encode($value));
        } else {
            throw new InvalidArgumentException("Invalid data type. Only arrays and objects are supported.");
        }
    }

    /**
     * @param array<string> $keys
     */
    public function del(array $keys): void
    {
        $this->redis->del($keys);
    }

    /**
     * Scan and delete keys matching a specific pattern.
     */
    public function deleteKeysByPattern(string $pattern): void
    {
        $cursor = null;
        do {
            // SCAN for keys matching the pattern
            $result = $this->redis->scan($cursor, 'MATCH', $pattern, 'COUNT', 100);
            if ($result) {
                [$cursor, $keys] = $result;
                if ($keys) {
                    $this->del($keys);  // Delete all keys found in this iteration
                }
            }
        } while ($cursor);  // Continue until SCAN returns 0 as cursor
    }
}
