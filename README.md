# Digiwin E10 API Token Client

用于调用鼎捷 E10 API 的基础 Token 生成与请求封装。

## Composer 安装

```bash
composer require jmcc/digiwin_e10_api
```

## 配置参数

安装后需要在项目配置中添加 `erp` 配置项。当前包会通过 Laravel 的 `config()` 方法读取以下参数：

| 参数 | 说明 | 示例 |
| --- | --- | --- |
| `erp.uri` | E10 API 基础地址，作为 Guzzle `base_uri` 使用 | `https://e10.example.com/` |
| `erp.acc` | 获取 Token 的账号，对应登录接口的 `username` | `api_user` |
| `erp.psd` | 获取 Token 的密码，对应登录接口的 `password` | `your_password` |

## Laravel 配置示例

建议在 Laravel 项目中新增配置文件 `config/erp.php`：

```php
<?php

return [
    'uri' => env('ERP_URI', 'https://e10.example.com/'),
    'acc' => env('ERP_ACC', ''),
    'psd' => env('ERP_PSD', ''),
];
```

然后在 `.env` 中添加：

```dotenv
ERP_URI=https://e10.example.com/
ERP_ACC=api_user
ERP_PSD=your_password
```

> `ERP_URI` 建议以 `/` 结尾，因为包内请求 Token 时使用的是相对路径 `auth/login`。

## Token 获取逻辑

实例化 `Jmcc\Digiwin\E10Client` 后，包会在请求时自动获取 Token：

1. 先从 Laravel Cache 中读取缓存键 `e10_api_token`。
2. 如果缓存不存在，则请求 `auth/login`。
3. 登录请求体为 JSON：

```json
{
  "username": "ERP_ACC",
  "password": "ERP_PSD"
}
```

4. 当接口返回 `code = 200` 时，会读取 `data.access_token` 和 `data.expires`。
5. Token 会根据 `expires` 计算剩余分钟数并写入 Cache。
6. 后续请求会自动添加请求头：

```http
Authorization: {access_token}
```

## 使用示例

```php
use Jmcc\Digiwin\E10Client;

$client = new E10Client();

// GET 请求
$response = $client->httpGet('api/example', [
    'keyword' => 'test',
]);

// POST 表单请求
$response = $client->httpPost('api/example', [
    'name' => 'test',
]);

// POST JSON 请求
$response = $client->httpPostJson('api/example', [
    'name' => 'test',
]);
```

## 注意事项

- 当前包依赖 Laravel 的 `config()` 与 `Cache` Facade，请确保在 Laravel 项目中使用，或自行提供兼容环境。
- `auth/login` 接口返回结构需包含 `code`、`data.access_token`、`data.expires`、`msg` 字段。
- Token 缓存键固定为 `e10_api_token`，用于避免和项目中其他 Token 缓存键混淆。

