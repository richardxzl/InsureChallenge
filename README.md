## ACME INSURE CHALLENGE

### Clonar Repositorio
- `git clone https://github.com/richardxzl/InsureChallenge.git`
- `cd InsureChallenge`

- El siguiente paso para la instalación y/o configuración del challenge tienen la opción de levantar los contenedores con docker-compose o hacerlo de forma manual.

1) Si deciden levantarlo por docker, deben tener instalado docker y docker-compose y seguir estos pasos a continuación:

    - `docker-compose up --build -d`
    - Debería decir algo como esto:
        - ![](https://github.com/richardxzl/InsureChallenge/blob/master/app/images/docker-compose-build.png)
      
    - El siguiente paso es ejecutar el siguiente comando para instalar las dependencias de composer:
        - `composer install`
      
    - Al final les saldrá algo como esto:
        - ![](https://github.com/richardxzl/InsureChallenge/blob/master/app/images/composer-install.png)
      
    - Luego ejecutar el siguiente comando para generar la key de la aplicación de Laravel:
        - `php artisan key:generate`
      
    - Verán esto:
        - ![](https://github.com/richardxzl/InsureChallenge/blob/master/app/images/key-generate.png)

2) Si deciden hacerlo de forma manual, deben tener instalado PHP 8.2, Composer, Laravel y seguir estos pasos a continuación:

    - Ejecutar el siguiente comando: `composer install`
    - Deben copiar el archivo `.env.example` y renombrarlo a `.env`
    - Ejecutar el comando: `php artisan key:generate`
    - Si todo va bien deben tener resultados muy parecidas a las imágenes anteriores.
    - Para levantar el servidor de Laravel ejecutar el siguiente comando:
        - `php artisan serve --host=0.0.0.0 --port=8080`
        - ![](https://github.com/richardxzl/InsureChallenge/blob/master/app/images/artisan-serve.png)


## Endpoint disponible
- Api Upload Json
    - [http://localhost:8000/api/upload-json](http://localhost:8000/api/upload-json)
  
    - Es una petición POST donde tienen que subir el archivo en el body de la petición -> form-data -> key: jsonFile (File), y suben su JSON. 
  
    - Debería verse algo como esto, tambien pueden descargar el XML que se genera:
        - ![](https://github.com/richardxzl/InsureChallenge/blob/master/app/images/api-post.png)


## Ejecutar Comando por linea de comandos (terminal)

- `php artisan  generate:insurance-xm {jsonFilePath}`

    - Ejemplo: `php artisan generate:insurance-xml storage/app/public/data/dataWithYoungDriver.json` pueden poner cualquier ruta de un archivo JSON que tengan en su máquina.
    - ![](https://github.com/richardxzl/InsureChallenge/blob/master/app/images/command-line.png)

## Ejecutar tests

- `php artisan test`
    - ![](https://github.com/richardxzl/InsureChallenge/blob/master/app/images/tests.png)
  
- También pueden ejecutar este comando `vendor/bin/phpunit`
    - ![](https://github.com/richardxzl/InsureChallenge/blob/master/app/images/tests2.png)


