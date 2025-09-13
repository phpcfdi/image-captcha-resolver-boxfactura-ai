# CHANGELOG

Usamos [Versionado Semántico 2.0.0](SEMVER.md) por lo que puedes usar esta librería sin temor a romper tu aplicación.

Pueden aparecer cambios no liberados que se integran a la rama principal, pero no ameritan una nueva liberación de
versión, aunque sí su incorporación en la rama principal de trabajo. Generalmente, se tratan de cambios en el desarrollo.

## Versión 0.2.0 2025-09-13

- Se actualiza la dependencia `phpcfdi/image-captcha-resolver` a 0.3.0. 
- Se elimina el soporte de PHP 8.1.
- Se agrega el soporte de PHP 8.4.
- Se cambian las definiciones implícitas a tipos *nullables* a explícitas.
- Se actualizó el año de la licencia.
- Se hacen diversos cambios para asegurar los tipos de datos y satisfacer PHPStan.

Se agregan los siguientes cambios al entorno de desarrollo:

- Se actualiza a PHPUnit 11.5.
- Se actualizan las reglas para `php-cs-fixer` y `phpcs`.
- En los flujos de trabajo de GitHub:
    - Se agrega PHP 8.4 a la matrix de pruebas.
    - Los trabajos se ejecutan en PHP 8.4.
    - Se actualiza la integración con SonarQube Cloud.
- Se actualizaron las herramientas de desarrollo.

## Versión 0.1.0 2024-10-02

Primera versión pública.
