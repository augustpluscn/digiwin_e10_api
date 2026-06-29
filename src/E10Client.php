<?php

namespace Jmcc\Digiwin;

use Illuminate\Support\Facades\Cache;

class E10Client extends BaseClient
{
    protected $baseUri;
    protected $acc;
    protected $psd;

    protected function init(): void
    {
        $this->baseUri = config('erp.uri');
        $this->acc = config('erp.acc');
        $this->psd = config('erp.psd');
    }

    protected function getToken()
    {
        $token = "";
        if (Cache::has('e10_api_token')) {
            //存在缓存
            $token = Cache::get('e10_api_token');
        } else {
            $data = [
                'username' => $this->acc,
                'password' => $this->psd,
            ];
            $url = 'auth/login';
            $response = $this->request($url, 'POST', ['json' => $data]);
            $body = \json_decode($response->getBody()->getContents());
            if ($body->code == 200) {
                $resData = $body->data;
                $token = $resData->access_token;
                $expires = $resData->expires;

                $time = time();
                $min = floor(($expires - $time) / 60);

                Cache::put('e10_api_token', $token, $min);
            } else {
                throw new \Exception('获取token失败，失败原因:' . $body->msg);
            }
        }
        return $token;
    }

    public function setHeader(array $header = [])
    {
        $arr = [
            'Authorization' => $this->getToken(),
        ];
        return array_merge($arr, $header);
    }
}
