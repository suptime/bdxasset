### bdxasset for laravel
本项目基于百度官方超级链数字商品可信登记平台SDK的二次封装composer扩展包,适用于百度超级链数字商品可信登记平台相关业务开发.

### 配置方式

在`/config/app.php`中的`providers`数组中加入以下服务提供者
```php
\suptime\bdxasset\XassetProvider::class
```

# 发布配置
命令行执行以下命令发布配置文件

```
$php artisan vendor:publish --provider="suptime\bdxasset\XassetProvider"
```

# 使用
```php
$xasset = new \suptime\bdxasset\Xasset($config);

//生成新的account
$account = $xasset->createAccount();
print_r($account);

//xasset客户端实例
$client = $xasset->XassetClient();

//获取stoken
$stoken = $client->getStoken($account);
```
