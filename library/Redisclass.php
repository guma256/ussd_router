<?php

class RedisClass
{
    private Redis $redis;
    private bool $connected = false;

    public function __construct()
    {
        $this->redis = new Redis();
    }

    private function connect(): void
    {
        if ($this->connected) {
            return;
        }

        try {
            $this->redis->pconnect(REDIS_HOST, REDIS_PORT, 2.5);

            if (defined('REDIS_PASSWORD') && REDIS_PASSWORD !== '') {
                $this->redis->auth(REDIS_PASSWORD);
            }

            $this->connected = true;
        } catch (RedisException $e) {
            error_log('Redis connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteKey(string $key): int
    {
        $this->connect();
        return $this->redis->del($key);
    }

    public function keyExists(string $key): bool
    {
        $this->connect();
        return (bool) $this->redis->exists($key);
    }

    public function storeNameWithValue(string $key, string $name, mixed $value): int
    {
        $this->connect();
        return $this->redis->hSet($key, $name, $value);
    }

    public function getKeyRecords(string $key): array
    {
        $this->connect();
        $records = $this->redis->hGetAll($key);

        return is_array($records) ? $records : [];
    }

    public function storeArrayRecords(string $key, array $array, int $expiry = SESSION_ID_EXP): bool
    {
        $this->connect();

        if (empty($array)) {
            return false;
        }

        $saved = $this->redis->hMSet($key, $array);

        if ($saved && $expiry > 0) {
            $this->redis->expire($key, $expiry);
        }

        return (bool) $saved;
    }

    public function expireRecords(string $key, int $seconds = 190): bool
    {
        $this->connect();

        if ($seconds <= 0) {
            return false;
        }

        return (bool) $this->redis->expire($key, $seconds);
    }

    public function close(): bool
    {
        if (!$this->connected) {
            return true;
        }

        $this->connected = false;
        return $this->redis->close();
    }
}