<?php

class Model
{
    protected Formatclass $format;
    protected Logs $log;
    protected Redisclass $redis;

    public function __construct()
    {
        $this->format = new Formatclass();
        $this->log = new Logs();
        $this->redis = new Redisclass();

        Session::start();
    }

    public function SessionExists(array $req_params): bool
    {
        if (empty($req_params['sessionId'])) {
            return false;
        }

        return $this->redis->KeyExists($req_params['sessionId']);
    }

    public function GetSessionRecords(string $sessionId): array
    {
        if (empty($sessionId)) {
            return [];
        }

        return $this->redis->GetKeyRecords($sessionId);
    }

    public function GetShortCodeData(string $shortcode, string $operator): array|int
    {
        return $this->getOperatorRoute($shortcode, $operator);
    }

    public function getOperatorRoute(string $shortcode, string $operator): array|int
    {
        foreach (SHORT_CODES as $value) {
            if (
                isset($value['shortcode']) &&
                $value['shortcode'] == $shortcode &&
                isset($value[$operator . '_url'])
            ) {
                return [
                    'route_url' => $value[$operator . '_url'],
                    'shortcode' => $value['shortcode']
                ];
            }
        }

        foreach (SHORT_CODES as $value) {
            if (
                isset($value['default']) &&
                $value['default'] == 'yes' &&
                isset($value[$operator . '_url'], $value['shortcode'])
            ) {
                return [
                    'route_url' => $value[$operator . '_url'],
                    'shortcode' => $value['shortcode']
                ];
            }
        }

        return 0;
    }

    public function SaveNewSession(array $data): bool
    {
        if (
            empty($data['sessionId']) ||
            empty($data['msisdn']) ||
            empty($data['url']) ||
            empty($data['shortcode'])
        ) {
            return false;
        }

        $postData = [
            'msisdn'    => $data['msisdn'],
            'url'       => $data['url'],
            'shortcode' => $data['shortcode']
        ];

        return $this->redis->StoreArrayRecords($data['sessionId'], $postData);
    }

    public function ProcessCleanSession(array $req_params): bool
    {
        if (empty($req_params['sessionId'])) {
            return false;
        }

        return $this->redis->DeleteKey($req_params['sessionId']) > 0;
    }

    public function WriteResponseXML(array $array): string|bool
    {
        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><response></response>'
        );

        $this->ArrayToXML($array, $xml);

        return $xml->asXML();
    }

    public function ArrayToXML(array $array, SimpleXMLElement &$xml): void
    {
        foreach ($array as $key => $value) {
            $key = is_numeric($key) ? 'item' : $key;

            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->ArrayToXML($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8'));
            }
        }
    }

    public function SendGetByCURL(string $url, array $req_params = [], array $extra_headers = []): string|bool
    {
        $this->log->ExeLog($req_params, 'Model::SendGetByCURL Sending To ' . $url, 2);

        $ch = curl_init();

        if ($ch === false) {
            $this->log->ExeLog($req_params, 'Model::SendGetByCURL failed to initialize CURL', 2);
            return false;
        }

        if (!empty($extra_headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $extra_headers);
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $url,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $content = curl_exec($ch);

        if (curl_errno($ch)) {
            $log = 'Curl error: ' . curl_error($ch);
        } else {
            $info = curl_getinfo($ch);
            $log = 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'];
        }

        curl_close($ch);

        $this->log->ExeLog($req_params, 'Model::SendGetByCURL Returning ' . $log, 2);
        $this->log->ExeLog($req_params, 'Model::SendGetByCURL response content ' . var_export($content, true), 2);

        return $content;
    }
}

?>