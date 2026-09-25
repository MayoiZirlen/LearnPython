<?php
return [
    'title' => 'Jugando con textos',
    'icon' => '🔤',
    'minutes' => 8,
    'summary' => 'Strings, f-strings y métodos para limpiar texto.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Los textos (strings)',
            'html' => <<<'HTML'
<p>Un <b>string</b> es cualquier texto entre comillas. Puedes:</p>
<pre>saludo = "Hola" + " " + "mundo"   # unir (concatenar): "Hola mundo"
risa = "ja" * 3                    # repetir: "jajaja"
len("Python")                      # contar caracteres: 6</pre>
<p>Pero la forma más cómoda de mezclar texto y variables son las <b>f-strings</b>: pon una <code>f</code> antes de las comillas y escribe las variables entre llaves <code>{ }</code>:</p>
<pre>nombre = "Ana"
edad = 25
print(f"Me llamo {nombre} y tengo {edad} años")</pre>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'F-strings en acción',
            'html' => '<p>Dentro de las llaves incluso puedes hacer cálculos. Y con <code>:.2f</code> muestras 2 decimales, y con <code>:,</code> separas miles:</p>',
            'code' => <<<'PY'
producto = "Laptop"
precio = 850
cantidad = 3

print(f"Compraste {cantidad} {producto}s")
print(f"Total: ${precio * cantidad}")
print(f"Con IVA: ${precio * cantidad * 1.16:.2f}")
print(f"Ventas anuales: ${1234567.891:,.2f}")
PY,
        ],
        [
            'type' => 'text',
            'title' => 'Posiciones y métodos',
            'html' => <<<'HTML'
<p>Cada letra tiene una posición (índice), <b>empezando en 0</b>:</p>
<pre>palabra = "Python"
#          P y t h o n
#          0 1 2 3 4 5
palabra[0]    # "P"
palabra[-1]   # "n"  (los negativos cuentan desde el final)
palabra[0:3]  # "Pyt" (desde 0 hasta antes del 3)</pre>
<p>Los strings tienen <b>métodos</b>: funciones que se escriben después de un punto. Son tu kit de limpieza de datos 🧹:</p>
<table>
<tr><th>Método</th><th>Ejemplo</th><th>Resultado</th></tr>
<tr><td><code>.upper()</code></td><td><code>"hola".upper()</code></td><td>"HOLA"</td></tr>
<tr><td><code>.lower()</code></td><td><code>"HoLa".lower()</code></td><td>"hola"</td></tr>
<tr><td><code>.title()</code></td><td><code>"ana lópez".title()</code></td><td>"Ana López"</td></tr>
<tr><td><code>.strip()</code></td><td><code>"  hola  ".strip()</code></td><td>"hola"</td></tr>
<tr><td><code>.replace()</code></td><td><code>"1,500".replace(",", "")</code></td><td>"1500"</td></tr>
<tr><td><code>.split()</code></td><td><code>"a,b,c".split(",")</code></td><td>["a", "b", "c"]</td></tr>
</table>
<div class="tip">💡 Los datos reales vienen sucios: espacios de más, mayúsculas mezcladas, comas en los números… ¡Estos métodos te salvarán la vida!</div>
HTML,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué da <code>"Python"[0]</code>?',
            'options' => ['"P"', '"y"', '"n"', 'Error'],
            'answer' => 0,
            'explain' => 'Las posiciones empiezan en 0, así que la posición 0 es la primera letra: <b>P</b>.',
            'wrong' => [1 => 'Recuerda: en Python se empieza a contar desde 0, no desde 1.'],
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué da <code>len("Hola mundo")</code>?',
            'options' => ['9', '2', '10', '11'],
            'answer' => 2,
            'explain' => '¡El espacio también cuenta como carácter! 4 + 1 + 5 = <b>10</b>.',
        ],
        [
            'type' => 'exercise',
            'title' => '🧹 Limpia el nombre',
            'html' => '<p>Un cliente escribió su nombre muy mal en un formulario: <code>"   ana GARCÍA  "</code>. Crea la variable <code>limpio</code> con el nombre sin espacios sobrantes y con formato de título: <code>"Ana García"</code>.</p>',
            'starter' => "crudo = \"   ana GARCÍA  \"\n\nlimpio = crudo\nprint(limpio)\n",
            'hint' => ['Primero quita los espacios con <code>.strip()</code> y luego usa <code>.title()</code>.', 'Los métodos se pueden encadenar: <code>crudo.strip().title()</code>'],
            'solution' => "crudo = \"   ana GARCÍA  \"\n\nlimpio = crudo.strip().title()\nprint(limpio)\n",
            'check' => <<<'PY'
assert isinstance(limpio, str), "limpio debe ser un texto."
assert limpio == limpio.strip(), "Todavía hay espacios al inicio o al final. Usa .strip()"
assert limpio == "Ana García", f"Obtuviste {limpio!r}, pero debería ser 'Ana García'. ¿Usaste .title()?"
PY,
        ],
        [
            'type' => 'exercise',
            'title' => 'Etiqueta de producto',
            'html' => '<p>Usando una <b>f-string</b>, muestra exactamente este mensaje con las variables dadas:</p><pre>El producto Laptop cuesta $850</pre>',
            'starter' => "producto = \"Laptop\"\nprecio = 850\n\nprint()\n",
            'hint' => 'Recuerda la <code>f</code> antes de las comillas y las variables entre llaves: <code>f"El producto {producto} ..."</code>',
            'solution' => "producto = \"Laptop\"\nprecio = 850\n\nprint(f\"El producto {producto} cuesta \${precio}\")\n",
            'check' => <<<'PY'
assert "El producto Laptop cuesta $850" in _salida, "El texto no coincide. Debe decir exactamente: El producto Laptop cuesta $850"
assert "{" in _codigo, "Usa una f-string con las variables entre llaves { }."
PY,
        ],
    ],
];
