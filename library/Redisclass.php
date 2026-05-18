<?php

class Redisclass
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

    public function DeleteKey(string $key): int
    {
        $this->connect();

        return $this->redis->del($key);
    }

    public function KeyExists(string $key): bool
    {
        $this->connect();

        return (bool) $this->redis->exists($key);
    }

    public function StoreNameWitValue(string $key, string $name, mixed $value): int
    {
        $this->connect();

        return $this->redis->hSet($key, $name, $value);
    }

    public function GetKeyRecords(string $key): array
    {
        $this->connect();

        $response = $this->redis->hGetAll($key);

        return is_array($response) ? $response : [];
    }

    public function StoreArrayRecords(string $key, array $array = [], int $expiry = SESSION_ID_EXP): bool
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

    public function ExpireRecords(string $key, int $seconds = 190): bool
    {
        $this->connect();

        if ($seconds <= 0) {
            return false;
        }

        return (bool) $this->redis->expire($key, $seconds);
    }

    public function Close(): bool
    {
        if (!$this->connected) {
            return true;
        }

        $this->connected = false;

        return $this->redis->close();
    }
}