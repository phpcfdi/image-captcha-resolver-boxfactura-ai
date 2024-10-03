# phpcfdi/image-captcha-resolver-boxfactura-ai

[![Source Code][badge-source]][source]
[![Packagist PHP Version Support][badge-php-version]][php-version]
[![Discord][badge-discord]][discord]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Reliability][badge-reliability]][reliability]
[![Maintainability][badge-maintainability]][maintainability]
[![Code Coverage][badge-coverage]][coverage]
[![Violations][badge-violations]][violations]
[![Total Downloads][badge-downloads]][downloads]

> Resolución de captchas del SAT usando Inteligencia Artificial

:us: The documentation of this project is in spanish as this is the natural language for the intended audience.

## Acerca de

Esta librería permite resolver captchas del SAT usando un modelo Onnx de Inteligencia Artificial.
El modelo de AI está basado en [Onnx](https://onnx.ai/) y ha sido alimentado con los captchas de tipo mancha de color.

![captcha sample](tests/_files/BK22PD.png)

El modelo ha sido entrenado por [BOX Factura][] y se encuentra disponible en el repositorio 
[`BoxFactura/sat-captcha-ai-model`](https://github.com/BoxFactura/sat-captcha-ai-model).

Esta implementación está directamente relacionada con [`phpcfdi/image-captcha-resolver`](https://github.com/phpcfdi/image-captcha-resolver) 
al tratarse de un resolvedor adicional para este proyecto.

## Instalación

Usa [composer](https://getcomposer.org/)

```shell
composer require phpcfdi/image-captcha-resolver-boxfactura-ai
```

### Instalación del modelo

El modelo que permite resolver los captchas se encuentra en el proyecto [`BoxFactura/sat-captcha-ai-model`](https://github.com/BoxFactura/sat-captcha-ai-model).
En este repositorio está el script de *BASH* `bin/download-model` que descarga los archivos necesarios.

El siguiente comando instala el modelo en el directorio `storage/sat-captcha-ai-model`.

```shell
bin/download-model storage/sat-captcha-ai-model
```

## Ejemplos de uso

### Resolver un captcha con `phpcfdi/image-captcha-resolver`

Para este ejemplo se asume que la imagen del captcha se encuentra como imagen embedida y su contenido en `$theImgElementSrcAtributte`.
Tambien asume que el archivo de configuraciones del modelo está en `storage/sat-captcha-ai-model/configs.yaml`.

```php
<?php

use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\BoxFacturaAIResolver;
use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;

/** @var string $theImgElementSrcAtributte */
$image = CaptchaImage::newFromInlineHtml($theImgElementSrcAtributte);

// Creación del resolvedor
$configsFile = 'storage/sat-captcha-ai-model/configs.yaml';
$resolver = BoxFacturaAIResolver::createFromConfigs($configsFile);

try {
    $answer = $resolver->resolve($image);
} catch (UnableToResolveCaptchaException $exception) {
    echo "No se pudo resolver el captcha: {$exception->getMessage()}", PHP_EOL;
    return;
}

echo "Respuesta del captcha: {$answer->getValue()}", PHP_EOL;
```

### Uso fuera de `phpcfdi/image-captcha-resolver`

Se puede utilizar este proyecto fuera de la librería `phpcfdi/image-captcha-resolver`.
Para lograrlo hay que utilizar directamente el objeto `Procesor` con los métodos `resolveImageFile`
o `resolveImageContent`, que reciben una ruta a un archivo o el contenido de un archivo,
y devuelven el texto que contiene el captcha.

```php
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\BoxFacturaAIResolver;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\ConfigsReader;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Processor;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Settings;

$configsFile = 'storage/sat-captcha-ai-model/configs.yaml';
$reader = new ConfigsReader();
$settings = $reader->settingsFromFile($configsFile);
$processor = Processor::createFromSettings($settings);

$image = 'storage/captcha.png';
$result = $processor->resolveImageFile($image);
```

### Uso de la herramienta CLI

Con esta herramienta se agrega un script de ejecución por línea de comandos, con la que se le da uno o más
archivos de imágenes de captcha y devuelve la resolución para cada uno, o un error si no se pudo obtener.

```shell
php bin/resolve.php --config model/configs.yaml samples/*.png samples/non-existent.png
samples/14YYHT.png: 14YYHT
samples/SSKTQC.png: SSKTQC
samples/INVALID.png: [ERROR] Unable to open image samples/INVALID.png
samples/non-existent.png: [ERROR] File samples/non-existent.png does not exist.
```

### Configuración de `libonnxruntime`

Se recomienda utilizar la librería `libonnxruntime` que se instala automáticamente con el 
componente [`ankane/onnxruntime`](https://github.com/ankane/onnxruntime-php).

Sin embargo, se puede utilizar la librería instalada en su sistema, por ejemplo:

```shell
\OnnxRuntime\FFI::$lib = '/usr/lib/x86_64-linux-gnu/libonnxruntime.so';
```

De igual forma, se puede utilizar otra librería que no sea GD para procesar imágenes,
sin embargo, en las pruebas de desarrollo se encontró que es mucho más rápida que las
soluciones basadas en Imagick. También se puede activar el soporte de GPU para Onnx.

El siguiente ejemplo muestra ambos casos:

```php
use Imagine\Imagick\Imagine as ImagineImagick;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\BoxFacturaAIResolver;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\ConfigsReader;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Processor;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Settings;
use OnnxRuntime\InferenceSession;

\OnnxRuntime\FFI::$lib = '/usr/lib/x86_64-linux-gnu/libonnxruntime.so';

$configsFile = 'storage/sat-captcha-ai-model/configs.yaml';
$reader = new ConfigsReader();
$settings = $reader->settingsFromFile($configsFile);
$onnxSession = new InferenceSession($settings->onnxModel, providers: ['CUDAExecutionProvider']);
$imagineEngine = new ImagineImagick();
$processor = new Processor(
    $settings->imageWidth,
    $settings->imageHeight,
    $settings->alphabetArray,
    $onnxSession,
    $imagineEngine;
);
$resolver = new BoxFacturaAIResolver($processor);
```

## Soporte

Puedes obtener soporte abriendo un ticket en GitHub.

Adicionalmente, esta librería pertenece a la comunidad [PhpCfdi](https://www.phpcfdi.com), así que puedes usar los
mismos canales de comunicación para obtener ayuda de algún miembro de la comunidad.

## Compatibilidad

Esta librería se mantendrá compatible con al menos la versión con
[soporte activo de PHP](https://www.php.net/supported-versions.php) más reciente.

También utilizamos [Versionado Semántico 2.0.0](docs/SEMVER.md) por lo que puedes usar esta librería
sin temor a romper tu aplicación.

## Contribuciones

Las contribuciones con bienvenidas. Por favor lee [CONTRIBUTING][] para más detalles
y recuerda revisar el archivo de tareas pendientes [TODO][] y el archivo [CHANGELOG][].

## BOX Factura

Ofreciendo soluciones premium en descarga, recepción y resguardo de CFDI para empresas modernas, 
la suite de herramientas de [Box Factura][] le ha permitido tanto a usuarios finales como 
especialistas en IT simplificar las labores administrativas a través de bóveda digital, 
descarga masiva diaria, portal de proveedores, gestión de viáticos y monitor de cancelaciones, 
además de API que permitan desarrollos e implementaciones personalizadas.

Agradecemos a [Box Factura][] la creación libre del modelo de inteligencia artificial que permite resolver los captchas 
y esperamos poder contribuir con sus proyectos.

## Copyright and License

The `phpcfdi/image-captcha-resolver-boxfactura-ai` library is copyright © [PhpCfdi](https://www.phpcfdi.com/)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[Box Factura]: https://www.boxfactura.com/

[contributing]: https://github.com/phpcfdi/image-captcha-resolver-boxfactura-ai/blob/main/CONTRIBUTING.md
[changelog]: https://github.com/phpcfdi/image-captcha-resolver-boxfactura-ai/blob/main/docs/CHANGELOG.md
[todo]: https://github.com/phpcfdi/image-captcha-resolver-boxfactura-ai/blob/main/docs/TODO.md

[source]: https://github.com/phpcfdi/image-captcha-resolver-boxfactura-ai
[php-version]: https://packagist.org/packages/phpcfdi/image-captcha-resolver-boxfactura-ai
[discord]: https://discord.gg/aFGYXvX
[release]: https://github.com/phpcfdi/image-captcha-resolver-boxfactura-ai/releases
[license]: https://github.com/phpcfdi/image-captcha-resolver-boxfactura-ai/blob/main/LICENSE
[build]: https://github.com/phpcfdi/image-captcha-resolver-boxfactura-ai/actions/workflows/build.yml?query=branch:main
[reliability]:https://sonarcloud.io/component_measures?id=phpcfdi_image-captcha-resolver-boxfactura-ai&metric=Reliability
[maintainability]: https://sonarcloud.io/component_measures?id=phpcfdi_image-captcha-resolver-boxfactura-ai&metric=Maintainability
[coverage]: https://sonarcloud.io/component_measures?id=phpcfdi_image-captcha-resolver-boxfactura-ai&metric=Coverage
[violations]: https://sonarcloud.io/project/issues?id=phpcfdi_image-captcha-resolver-boxfactura-ai&resolved=false
[downloads]: https://packagist.org/packages/phpcfdi/image-captcha-resolver-boxfactura-ai

[badge-source]: https://img.shields.io/badge/source-phpcfdi/image--captcha--resolver--boxfactura--ai-blue?logo=github
[badge-discord]: https://img.shields.io/discord/459860554090283019?logo=discord
[badge-php-version]: https://img.shields.io/packagist/php-v/phpcfdi/image-captcha-resolver-boxfactura-ai?logo=php
[badge-release]: https://img.shields.io/github/release/phpcfdi/image-captcha-resolver-boxfactura-ai?logo=git
[badge-license]: https://img.shields.io/github/license/phpcfdi/image-captcha-resolver-boxfactura-ai?logo=open-source-initiative
[badge-build]: https://img.shields.io/github/actions/workflow/status/phpcfdi/image-captcha-resolver-boxfactura-ai/build.yml?branch=main&logo=github-actions
[badge-reliability]: https://sonarcloud.io/api/project_badges/measure?project=phpcfdi_image-captcha-resolver-boxfactura-ai&metric=reliability_rating
[badge-maintainability]: https://sonarcloud.io/api/project_badges/measure?project=phpcfdi_image-captcha-resolver-boxfactura-ai&metric=sqale_rating
[badge-coverage]: https://img.shields.io/sonar/coverage/phpcfdi_image-captcha-resolver-boxfactura-ai/main?logo=sonarcloud&server=https%3A%2F%2Fsonarcloud.io
[badge-violations]: https://img.shields.io/sonar/violations/phpcfdi_image-captcha-resolver-boxfactura-ai/main?format=long&logo=sonarcloud&server=https%3A%2F%2Fsonarcloud.io
[badge-downloads]: https://img.shields.io/packagist/dt/phpcfdi/image-captcha-resolver-boxfactura-ai?logo=packagist
