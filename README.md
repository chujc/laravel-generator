# Laravel & Lumen 脚手架

自动生成model文件，RESTful路由以及对于的controller，request文件

## 安装

因为是开发环境下使用的包，请执行如下方式安装，不要省略`--dev`参数
```bash
composer require chujc/laravel-generator --dev
```
Laravel 5.5以上版本安装后就可以执行命令生成对应的文件，Lumen需要在`bootstrap/app.php` 文件下面 末端加入如下代码


```php
if ($app->environment() !== 'production') {
    $app->register(\ChuJC\LaravelGenerator\GeneratorServiceProvider::class);
}
```

## 生成model文件
使用前请确保您的数据库文件是配置正确的，同时您的数据表已经构建，因为此包生成的model是会读取您的表结构生产对应的model

自动扫描数据库结构，为目前的所有表生成model文件，如果使用到了软删除，创建，更新字段请最好使用`timestamp`类型 字段名称可以在配置文件中配置，其他不限。
##### models 脚手架
- [X] 生成model文件
- [X] 类名 class name 替换
- [X] 命名空间 namespace 与 继承 class 替换
- [X] 主键及主键属性判断
- [X] 批量更新字段白名单 
- [X] 创建与更新时间戳的判断
- [X] 软删除时间戳的判断 
#### 命令
```bash
php artisan chujc:models
```
为指定的**表**生成model文件 
```bash
php artisan chujc:models user_tags
```
> 单个表生成的时候，请注意不需要带上表前缀, 其余与表名一致

如果您的项目安装了`barryvdh/laravel-ide-helper` 与 `doctrine/dbal`，还会自动调用对应的命令为您生成model class的注释

## 生成API Controller文件

#### API脚手架
- [X] 生成controller文件
- [X] 命名空间 namespace 替换
- [X] 类名 class name 替换
- [X] 继承 class 替换
- [X] 对应的 model class 替换
- [X] 对应的 request class 替换
- [X] 列表，详情，创建，更新，删除方法的默认处理
- [X] 路由的新增 RESTful路由

#### 命令
api 只能单个生成，需要注意`controller` 命名
```bash
php artisan chujc:api UserController
```
关联model生成`controller`会关联`model`文件，生成`request`文件，常用方法的基本使用, RESTful路由
```bash
php artisan chujc:api UserTagController -m UserTag
```
> 注意-m 后面的model 一定是存在model配置文件夹中的 model，命名需与文件中一致

#### 路由
生成的route放在配置的文件中，你可以直接修改配置文件或者在引入路由文件
> 修改路由的主要原因是生成的controller文件等是完全可以自定义配置的，如果你不想修改可以配置成官方的路径 配置文件中apiDefault 修改成对应的配置既可

laravel中可以直接在`app\Providers\RouteServiceProvider` 中 新增或者修改对应的访问引入route文件
```php
...
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace('App\APIs\Controllers')
             ->group(base_path('app/APIs/route.php'));
    }
...
```

lumen可以直接在`bootstrap/app.php`下面修改
```php
$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require base_path('app/APIs/route.php');
});
```


## 配置相关
很多如果您很多需要自定义，比如model，controller放置文件夹等，请先执行以下命令生成配置文件。

```bash
php artisan vendor:publish --provider="ChuJC\LaravelGenerator\GeneratorServiceProvider" --tag=config
```

- 默认配置
```php
return [
    //Eloquent Model
    'model' => [
        // 生成model的命名空间
        'namespace' => 'App\Models',
        // 生成的model放置文件夹
        'path' => base_path('app/Models'),
        // 生成的models继承的class
        'extends' => 'Illuminate\Database\Eloquent\Model',
        'extends_name' => 'Model',
        // 忽略的table, 不会生成models
        'ignore' => ['users'],
        // 满足条件的table 不为空数组的时候 只会为填充的table生成model
        // 简单点说为多个指定的table生成model就填写在fill配置中
        'fill' => [],
        // 时间戳字段 如果自定义时间戳字段需要 只需要修改键对应的值
        'timestamps' => [
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ],
        // 如果有自定义软删除时间戳 只需要修改对应的字段值
        'soft_deletes' => 'deleted_at',

    ],
    'api_default' => 'laravel',

    'api_config' => [
        'laravel' => [
            // 生成API的命名空间
            'namespace' => 'App\Http\Controllers',
            // 生成的API放置文件夹
            'path' => base_path('app/Http/Controllers'),
            // 生成的API继承的class
            'extends' => 'App\Http\Controllers\Controller',
            'extends_name' => 'Controller',
            // 是否生成request
            'request' => true,
            'request_path' => base_path('app/Http/Requests'),
            'request_namespace' => 'App\Http\Requests',
            // 路由放置文件
            'route' => base_path('routes/api.php'),
        ],
        ....
    ],
];
```

#### 后续版本规划
此包的开发是方便大家在项目中快速构建对应的接口，如果您对功能的新增、修改有建议，希望发邮件到john1668#qq.com 邮箱（#需要替换为@）或者 直接**issues**
	
- [ ] 关系模型（一对一，一对多，多对多）的关系处理
- [ ] resource class 的添加（api 生成可选参数方式）

