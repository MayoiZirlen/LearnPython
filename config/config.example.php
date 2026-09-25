<?php
// Copia este archivo como config.php y ajusta los datos si es necesario.
// Con XAMPP recién instalado, el usuario es "root" y la contraseña va vacía.
return [
    'db_host' => '127.0.0.1',
    'db_port' => 3306,
    'db_name' => 'pyaprende',
    'db_user' => 'root',
    'db_pass' => '',

    // URL de Pyodide (Python compilado a WebAssembly que corre en el navegador).
    // Si quieres trabajar sin internet, descarga Pyodide en assets/pyodide/
    // y cambia esto a: 'assets/pyodide/'
    'pyodide_url' => 'https://cdn.jsdelivr.net/pyodide/v0.27.2/full/',
];
