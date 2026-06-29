<?php

namespace App\Api;

class E10Client extends BaseClient
{
    protected $baseHost;
    protected $baseUri;
    protected $hostVer;
    protected $hostProd;
    protected $serviceId;
    protected $acct;

    protected function init(): void
    {
        $this->baseHost = config('erp.host');
        $this->baseUri = config('erp.uri');
        $this->hostVer = config('erp.ver');
        $this->hostProd = config('erp.prod');
        $this->serviceId = config('erp.service');
        $this->acct = config('erp.acct');
    }

    protected function getToken()
    {
        $token = "";
        return $token;
    }

    public function setHeader(array $header = [])
    {
        // 服务调用端主机信息
        $digiHost = [
            "ver" => $this->hostVer,
            "prod" => $this->hostProd,
            "timezone" => "+8",
            "ip" => request()->ip(),
            "id" => "",
            "lang" => "zh_CN",
            "acct" => $this->acct,
            "timestamp" => date('YmdHis', time()) . $this->get_millisecond(),
        ];
        // E10集成服务主机信息
        $digiService = [
            "prod" => "E10",
            "ip" => $this->baseHost,
            "name" => $header['name'],
            "id" => $this->serviceId,
        ];
        $parameter = [
            'digi-key' => md5(json_encode($digiHost) . json_encode($digiService)),
            'digi-host' => json_encode($digiHost),
            'digi-service' => json_encode($digiService),
            'digi-data-exchange-protocol' => '1.0',
            'Content-Type' => 'application/json',
            'digi-type' => 'sync',
        ];
        //dump($parameter);
        return $parameter;
    }

    //格式化时间
    private function get_millisecond()
    {
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec * 1000 + $sec);
        return $msec;
    }
}
