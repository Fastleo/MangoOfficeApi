<?php

namespace Fastleo\MangoOfficeApi;

/**
 * Class Mango
 */
class Mango
{
    /**
     * Уникальный код вашей АТС
     * @var string
     */
    protected $api_key;

    /**
     * Ключ для создания подписи
     * @var string
     */
    protected $api_salt;

    /**
     * Адрес API Виртуальной АТС
     * @var string
     */
    protected $api_url = 'https://app.mango-office.ru/vpbx/';

    /**
     * Mango constructor.
     * @param string $api_key
     * @param string $api_salt
     * @param string|null $api_url
     */
    public function __construct(string $api_key, string $api_salt, string $api_url = null)
    {
        $this->api_key = $api_key;
        $this->api_salt = $api_salt;
        if (!is_null($api_url)) {
            $this->api_url = $api_url;
        }
    }

    /**
     * Отправка данных на сервер
     * @param string $url
     * @param array $data
     * @return string
     */
    private function execute(string $url, array $data): string
    {
        $url = str_replace($this->api_url, '', $url);
        $post = http_build_query($data);
        $ch = curl_init($this->api_url . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * @param string $csv
     * @return array
     */
    private function csvToData(string $csv): array
    {
        $result = [];
        $data = explode("\r\n", $csv);
        foreach ($data as $k => $value) {
            $array = explode(';', $value);
            if (count($array) > 1) {
                $result[$k]['time_start'] = $array[1];
                $result[$k]['time_finish'] = $array[2];
                $result[$k]['time_answer'] = $array[3];
                $result[$k]['date_start'] = date('Y-m-d H:i:s', $array[1]);
                $result[$k]['date_finish'] = date('Y-m-d H:i:s', $array[2]);
                $result[$k]['date_answer'] = date('Y-m-d H:i:s', $array[3]);
                $result[$k]['from_extension'] = $array[4];
                $result[$k]['from_number'] = $array[5];
                $result[$k]['to_extension'] = $array[6];
                $result[$k]['to_number'] = $array[7];
                $result[$k]['disconnect_reason'] = $array[8];
                $result[$k]['location'] = $array[9];
                $result[$k]['line_number'] = $array[10];
            }
        }
        return $result;
    }

    /**
     * Сбор параметров для отправки
     * @param array|object|string $data
     * @return array
     */
    protected function setParams($data): array
    {
        $json = json_encode($data);
        $sign = hash('sha256', $this->api_key . $json . $this->api_salt);
        return [
            'vpbx_api_key' => $this->api_key,
            'sign' => $sign,
            'json' => $json,
        ];
    }

    public function getHistory($date_from, $date_to): array
    {
        $data = [
            'date_from' => $date_from,
            'date_to' => $date_to,
            'from' => [
                'extension' => '',
                'number' => ''
            ],
            'to' => [
                'extension' => '',
                'number' => ''
            ],
            'fields' => 'records,start,finish,answer,from_extension,from_number,to_extension,to_number,disconnect_reason,line_number,location,entry_id'
        ];

        // Получаем ключ
        $post = $this->setParams($data);
        $key = $this->execute('stats/request', $post);

        // Получаем историю
        $post = $this->setParams(json_decode($key));
        $history = $this->execute('stats/result', $post);
        return $this->csvToData($history);
    }
}
