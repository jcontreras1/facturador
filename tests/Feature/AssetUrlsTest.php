<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->publicOriginal = public_path();
    $this->publicTemporal = sys_get_temp_dir().DIRECTORY_SEPARATOR.'facturador-assets-'.Str::uuid();
    File::ensureDirectoryExists($this->publicTemporal.DIRECTORY_SEPARATOR.'build');
    File::put($this->publicTemporal.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'manifest.json', json_encode([
        'resources/js/app.js' => [
            'file' => 'assets/app-test.js',
            'isEntry' => true,
            'src' => 'resources/js/app.js',
            'css' => ['assets/app-test.css'],
        ],
    ], JSON_THROW_ON_ERROR));
    $this->app->usePublicPath($this->publicTemporal);
    Vite::useHotFile($this->publicTemporal.DIRECTORY_SEPARATOR.'hot');
    URL::useAssetOrigin('https://facturador.test');
});

afterEach(function () {
    $this->app->usePublicPath($this->publicOriginal);
    File::deleteDirectory($this->publicTemporal);
});

test('compiled asset tags use the request installation path instead of a fixed origin', function (string $basePath) {
    $this->app->instance('request', Request::create(
        'https://produccion.example'.$basePath.'/comprobantes',
        'GET',
        server: [
            'SCRIPT_FILENAME' => '/var/www/public/index.php',
            'SCRIPT_NAME' => $basePath.'/index.php',
            'PHP_SELF' => $basePath.'/index.php',
        ],
    ));

    $html = Vite::withEntryPoints(['resources/js/app.js'])->toHtml();

    expect($html)->toContain(
        'src="'.$basePath.'/build/assets/app-test.js"',
        'href="'.$basePath.'/build/assets/app-test.css"',
        'href="'.$basePath.'/build/assets/app-test.js"',
    )->not->toContain('facturador.test', 'produccion.example', '/comprobantes/build');
})->with(['', '/facturar', '/gestion/facturar']);

test('the development server retains its own asset URLs', function () {
    File::put($this->publicTemporal.DIRECTORY_SEPARATOR.'hot', 'http://localhost:5173');

    $html = Vite::withEntryPoints(['resources/js/app.js'])->toHtml();

    expect($html)->toContain(
        'src="http://localhost:5173/@vite/client"',
        'src="http://localhost:5173/resources/js/app.js"',
    );
});
