<?php

namespace ChuJC\LaravelGenerator\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeAPICommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'chujc:api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the specified API controller.';

    protected $generator;


    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        return $name;
    }

    public function handle()
    {

        if ($this->option('config')) {
            $this->generator = config('generator.api_config.'. $this->option('config'));
        } else {
            $this->generator = config('generator.api_config.'. config('generator.api_default'));
        };

        if (!$this->generator) {
            $this->error('未找到配置文件，请检查config/generator.php');
            return;
        }

        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->generator['path'] . '/' . $this->getNamespace($name);

        $controllerFileName = $path . '/' . $this->getClassName($name) . '.php';

        if ($this->files->exists($controllerFileName)) {
            if (!$this->confirm("{$name} 已经存在, 请确认是否覆盖? [y|N]")) {
                $this->warn($name . ' created defeated.');
                return;
            }
        }

        if ($this->option("model")) {

            $modelFile = config('generator.model.path'). '/'. Str::studly($this->option('model')). '.php';

            if ($this->files->exists($modelFile)) {

                $controllerClassFile = $this->buildClassFile('model');

                $controllerClassFile = $this->replaceModel($controllerClassFile, Str::studly($this->option('model')));

                $useRequestNamespace = 'Illuminate\Http\Request';
                $requestName = 'Request';

                if ($this->generator['request']) {

                    $this->checkDirAndMakeDir($this->generator['request_path']);

                    $requestFile = $this->buildClassFile('request');

                    $requestFile = str_replace('{{requestNamespace}}', $this->generator['request_namespace'], $requestFile);
                    $requestFile = str_replace('{{requestClassName}}', Str::studly($this->option('model')). 'Request', $requestFile);

                    $fileName = $this->generator['request_path'] . '/' . Str::studly($this->option('model')). 'Request.php';
                    $this->files->put($fileName, $requestFile);

                    $useRequestNamespace = $this->generator['request_namespace']. '\\'. Str::studly($this->option('model')). 'Request';
                    $requestName = Str::studly($this->option('model')). 'Request';
                }

                $controllerClassFile = str_replace('{{useRequestNamespace}}', $useRequestNamespace, $controllerClassFile);
                $controllerClassFile = str_replace('{{requestName}}', $requestName, $controllerClassFile);

            } else {
                $this->warn('未找到对应的model文件'. $modelFile);

                $controllerClassFile = $this->buildClassFile();
            }
        } else {

            $controllerClassFile = $this->buildClassFile();
        }

        $controllerClassFile = $this->replaceNamespace($controllerClassFile, $name)
            ->replaceClass($controllerClassFile, $name);

        $controllerClassFile = $this->replaceExtends($controllerClassFile);

        $this->checkDirAndMakeDir($path);

        $this->files->put($controllerFileName, $controllerClassFile);

        $this->info($name . ' created successfully.');

        $this->buildRoute($name);

    }


    /**
     * 检查对应文件夹是否存在，并创建对应文件夹
     * @param $path
     * @date 2019-06-12
     * @author john_chu
     */
    protected function checkDirAndMakeDir($path)
    {
        if (!$this->files->exists($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    /**
     * 生成RESTful路由
     * @param $name
     * @date 2019-06-12
     * @author john_chu
     */
    protected function buildRoute($name)
    {

        $controller = $this->nameFormatController($name);

        $url = $this->removeControllerString($controller);

        $filePath = $this->relativePath($this->generator['path']. '/' . $this->getNamespace($name). '/'. $this->getClassName($name));

        $this->files->append($this->generator['route'], "// {$filePath}" . PHP_EOL);

        if ($this->lumenOrLaravel() == 'laravel') {
            $this->files->append($this->generator['route'], "Route::apiResource('{$url}', '{$controller}');" . PHP_EOL . PHP_EOL);
            $this->info($name . ' route created successfully.');
            return;
        }

        $id = 'id';
        if ($this->option("model")) {
            $id = lcfirst($this->getClassName(Str::studly($this->option('model'))));
        }

        $this->files->append($this->generator['route'], "Route::get('{$url}', '{$controller}@index');" . PHP_EOL);
        $this->files->append($this->generator['route'], "Route::post('{$url}', '{$controller}@store');" . PHP_EOL);
        $this->files->append($this->generator['route'], "Route::get('{$url}/{{$id}}', '{$controller}@show');" . PHP_EOL);
        $this->files->append($this->generator['route'], "Route::put('{$url}/{{$id}}', '{$controller}@update');" . PHP_EOL);
        $this->files->append($this->generator['route'], "Route::delete('{$url}/{{$id}}', '{$controller}@destroy');" . PHP_EOL);

        $this->info($name . ' route created successfully.');
    }

    /**
     * 替换为项目根目录开始的相对路径
     * @param $path
     * @return mixed
     * @date 2019-06-12
     * @author john_chu
     */
    protected function relativePath($path)
    {
        return str_replace(base_path() . '/', '', $path);
    }

    protected function nameFormatController($name)
    {
        return str_replace('\\', '/', $name);
    }

    protected function removeControllerString($name)
    {
        return  strtolower(str_replace('Controller', '', $name));
    }

    /**
     * 判断当前是lumen还是laravel环境
     * @return string
     * @date 2019-06-11
     * @author john_chu
     */
    protected function lumenOrLaravel()
    {
        if (stripos('Lumen', app()->version()) !== false) {
            return 'lumen';
        };
        return 'laravel';
    }

    /**
     * 生成类文件
     * @param string $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @date 2019-06-13
     * @author john_chu
     */
    protected function buildClassFile($name = 'api')
    {
        return $this->files->get($this->getStub($name));
    }

    /**
     * 替换空间名称
     * @param $stub
     * @param $name
     * @param string $type 'namespace|requestNamespace|resourceNamespace'
     * @return $this
     * @date 2019-06-10
     * @author john_chu
     */
    protected function replaceNamespace(&$stub, $name, $type = 'namespace')
    {
        $stub = str_replace('{{namespace}}', $this->getNamespaceName($name, $type), $stub);
        return $this;
    }

    /**
     * 替换类名
     * @param string $stub
     * @param string $name
     * @return $this|string
     * @date 2019-06-10
     * @author john_chu
     */
    protected function replaceClass($stub, $name)
    {
        $stub = str_replace('{{className}}', $this->getClassName($name), $stub);
        return $stub;
    }


    protected function replaceModel($stub, $name)
    {
        $stub = str_replace('{{useModelNamespace}}', config('generator.model.namespace'). '\\'. $name, $stub);
        $stub = str_replace('{{modelName}}', lcfirst($this->getClassName($name)), $stub);
        $stub = str_replace('{{upperModelName}}', $this->getClassName($name), $stub);
        return $stub;
    }

    /**
     * 替换继承类
     * @param $stub
     * @return mixed
     * @date 2019-06-10
     * @author john_chu
     */
    protected function replaceExtends($stub)
    {
        $stub = str_replace('{{extends}}', $this->generator['extends'], $stub);
        $stub = str_replace('{{extendsName}}', $this->generator['extends_name'], $stub);
        return $stub;
    }

    /**
     * 获取className
     * @param $name
     * @return mixed
     * @date 2019-06-10
     * @author john_chu
     */
    protected function getClassName($name)
    {
        return str_replace($this->getNamespace($name).'\\', '', $name);
    }

    /**
     * 获取空间名称
     * @param $name
     * @param string $type
     * @return string
     * @date 2019-06-10
     * @author john_chu
     */
    protected function getNamespaceName($name, $type = 'namespace')
    {
        if ($this->getNamespace($name)) {
            return $this->generator[$type] . '\\'. $this->getNamespace($name);
        }
        return $this->generator[$type];
    }

    protected function getStub($name = 'api')
    {
        switch ($name) {
            case 'request':
                return __DIR__ . '/../stubs/request.stub';
            case 'model':
                return __DIR__ . '/../stubs/controller.model.api.stub';
            default:
                return __DIR__ . '/../stubs/controller.api.stub';
        }
    }

    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a controller for the given model.'],
            ['config', 'c', InputOption::VALUE_OPTIONAL, 'Generates the controller using the specified configuration item.'],
        ];
    }
}
