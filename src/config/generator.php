<?php

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
        'ignore' => ['users', 'migrations'],
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
        'api' => [
            // 生成API的命名空间
            'namespace' => 'App\APIs\Controllers',
            // 生成的API放置文件夹
            'path' => base_path('app/APIs/Controllers'),
            // 生成的API继承的class
            'extends' => 'App\Http\Controllers\Controller',
            'extends_name' => 'Controller',
            // 是否生成request
            'request' => true,
            'request_path' => base_path('app/APIs/Requests'),
            'request_namespace' => 'App\APIs\Requests',
            // 路由放置文件
            'route' => base_path('app/APIs/route.php'),

        ],
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
        'lumen' => [
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
            'route' => base_path('routes/web.php'),
        ],
    ],



];