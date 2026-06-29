# 鼎捷 E10 API 基类说明

这是一个用于调用鼎捷 E10 API 的 Composer 包，内置基于 Guzzle 的 HTTP 请求封装，并在请求时生成 E10 集成服务需要的 `digi-host`、`digi-service` 等请求头。

## Composer 安装

```bash
composer require jmcc/digiwin_e10_api
```

安装后请确认项目已加载 Composer 自动加载文件：

```php
require_once __DIR__ . '/vendor/autoload.php';
```

## 必填配置

当前客户端通过 `config('erp.xxx')` 读取 E10 连接参数，因此安装后需要在项目配置文件中增加 `erp` 配置。

如果是 Laravel 项目，建议新增或补充 `config/erp.php`：

```php
<?php

return [
    // E10 集成服务主机地址或 IP，用于 digi-service.ip
    'host' => env('ERP_HOST', '127.0.0.1'),

    // E10 API 基础地址，用于 Guzzle base_uri
    'uri' => env('ERP_URI', 'http://127.0.0.1/'),

    // 调用端版本号，用于 digi-host.ver
    'ver' => env('ERP_VER', '1.0'),

    // 调用端产品标识，用于 digi-host.prod
    'prod' => env('ERP_PROD', 'APP'),

    // E10 集成服务 ID，用于 digi-service.id
    'service' => env('ERP_SERVICE_ID', ''),

    // E10 账套，用于 digi-host.acct
    'acct' => env('ERP_ACCT', ''),
];
```

对应 `.env` 示例：

```dotenv
ERP_HOST=192.168.2.241
ERP_URI=http://192.168.2.241:9990/CROSS/RESTful/
ERP_VER=6.3.0.1
ERP_PROD=YOA
ERP_SERVICE=test_External
ERP_ACCT=dcms
```

## 配置项说明

| 配置项 | 必填 | 说明 | 使用位置 |
| --- | --- | --- | --- |
| `erp.host` | 是 | E10 集成服务主机地址或 IP | `digi-service.ip` |
| `erp.uri` | 是 | E10 API 基础请求地址 | Guzzle `base_uri` |
| `erp.ver` | 是 | 调用端版本号 | `digi-host.ver` |
| `erp.prod` | 是 | 调用端产品标识 | `digi-host.prod` |
| `erp.service` | 是 | E10 集成服务 ID | `digi-service.id` |
| `erp.acct` | 是 | E10 账套编号或账套标识 | `digi-host.acct` |

## 调用示例

调用接口时，第三个参数中的 `name` 会写入 `digi-service.name`，通常对应 E10 中配置的服务名称。

```php
use App\Api\E10Client;

$client = new E10Client();

$result = $client->httpPostJson(
    '/api/path',
    [
        'field' => 'value',
    ],
    [
        'name' => 'service-name',
    ]
);
```

## 请求头生成规则

客户端会根据配置和当前请求信息生成以下请求头：

| 请求头 | 说明 |
| --- | --- |
| `digi-key` | `digi-host` 与 `digi-service` JSON 字符串拼接后的 MD5 |
| `digi-host` | 调用端主机信息，包含 `ver`、`prod`、`timezone`、`ip`、`lang`、`acct`、`timestamp` |
| `digi-service` | E10 集成服务信息，包含 `prod`、`ip`、`name`、`id` |
| `digi-data-exchange-protocol` | 固定为 `1.0` |
| `digi-type` | 固定为 `sync` |
| `Content-Type` | 固定为 `application/json` |

## 注意事项

- 当前实现依赖 `config()` 和 `request()->ip()` 辅助函数，适合在 Laravel 项目中使用。
- 每次调用时都需要传入服务名称：`['name' => 'service-name']`，否则无法生成完整的 `digi-service` 请求头。
- `erp.uri` 会作为 Guzzle 的 `base_uri`，接口地址可以传相对路径。
