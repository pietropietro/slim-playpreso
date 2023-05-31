<?php

declare(strict_types=1);

namespace App\Service;

use Predis\Client;

final class RedisService
{
    public const PROJECT_NAME = 'slim-playpreso';

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
        if ($value !== false) {
            $decodedValue = json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decodedValue;
            } else {
                return json_decode($value, true);
            }
        }
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
}
