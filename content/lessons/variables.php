<?php
return [
    'title' => 'Variables: cajas con etiqueta',
    'icon' => '📦',
    'minutes' => 7,
    'summary' => 'Guarda datos con nombre para usarlos después.',
    'steps' => [
        [
            'type' => 'text',
            'title' => '¿Qué es una variable?',
            'html' => <<<'HTML'
<div class="analogy">📦 Imagina una <b>caja con una etiqueta</b>. En la etiqueta escribes un nombre (<code>edad</code>) y dentro guardas un valor (<code>25</code>). Cuando necesites el valor, solo pides la caja por su nombre.</div>
<pre>edad = 25
nombre = "Ana"</pre>
<p>El signo <code>=</code> en Python <b>no significa "igual"</b> como en matemáticas: significa <b>"guarda esto aquí"</b>. Se lee de derecha a izquierda: <i>"guarda 25 en la caja llamada edad"</i>.</p>
<p>En análisis de datos usarás variables todo el tiempo: <code>ventas_totales</code>, <code>promedio</code>, <code>tabla_clientes</code>…</p>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Crea y usa variables',
            'html' => '<p>Fíjate que en <code>print(nombre)</code> no hay comillas: le pedimos a Python el <b>contenido</b> de la caja, no la palabra "nombre".</p>',
            'code' => <<<'PY'
nombre = "Ana"
edad = 25
ciudad = "Monterrey"

print(nombre)
print(edad)
print(nombre, "tiene", edad, "años y vive en", ciudad)
PY,
        ],
        [
            'type' => 'text',
            'title' => 'Las variables pueden cambiar',
            'html' => <<<'HTML'
<p>Por eso se llaman <i>variables</i>: su contenido puede cambiar. Si guardas algo nuevo en la misma caja, lo anterior se reemplaza.</p>
<pre>puntos = 10
puntos = puntos + 5   # toma el valor actual (10), súmale 5 y guárdalo
print(puntos)         # 15</pre>
<p><b>Reglas para nombrar variables:</b></p>
<ul>
  <li>✅ Letras, números y guion bajo: <code>ventas_2025</code>, <code>precio_total</code></li>
  <li>❌ No pueden empezar con número: <code>2025_ventas</code></li>
  <li>❌ Sin espacios ni guiones: <code>precio total</code>, <code>precio-total</code></li>
  <li>⚠️ Mayúsculas importan: <code>Edad</code> y <code>edad</code> son cajas distintas</li>
</ul>
<div class="tip">💡 Usa nombres que expliquen lo que guardan. <code>total_ventas</code> es mucho mejor que <code>x</code>.</div>
HTML,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué muestra este programa?',
            'code' => "x = 5\nx = x + 2\nprint(x)",
            'options' => ['5', '2', '7', 'x + 2'],
            'answer' => 2,
            'explain' => 'Primero <code>x</code> vale 5. Luego calculamos <code>5 + 2 = 7</code> y lo guardamos otra vez en <code>x</code>.',
        ],
        [
            'type' => 'quiz',
            'question' => '¿Cuál de estos nombres de variable <b>NO</b> es válido en Python?',
            'options' => ['total_ventas', 'ventas2', '2ventas', '_contador'],
            'answer' => 2,
            'explain' => 'Los nombres de variables no pueden empezar con un número.',
        ],
        [
            'type' => 'exercise',
            'title' => 'La cuenta del café',
            'html' => <<<'HTML'
<p>Estás en una cafetería. Crea estas variables:</p>
<ul>
  <li><code>producto</code> con el texto <code>"Café"</code></li>
  <li><code>precio</code> con el número <code>45</code></li>
  <li><code>cantidad</code> con el número <code>3</code></li>
  <li><code>total</code> que sea el <b>precio multiplicado por la cantidad</b> (en Python se multiplica con <code>*</code>)</li>
</ul>
<p>Al final, muestra el <code>total</code> con <code>print()</code>.</p>
HTML,
            'starter' => "producto = \"Café\"\n\n# Crea precio, cantidad y total\n\n",
            'hint' => ['Los números van <b>sin</b> comillas: <code>precio = 45</code>', 'El total se calcula usando las variables: <code>total = precio * cantidad</code>'],
            'solution' => "producto = \"Café\"\nprecio = 45\ncantidad = 3\ntotal = precio * cantidad\nprint(total)\n",
            'check' => <<<'PY'
assert producto == "Café", "La variable producto debe contener el texto \"Café\"."
assert precio == 45, "precio debe valer 45 (sin comillas, es un número)."
assert cantidad == 3, "cantidad debe valer 3."
assert total == 135, "total debe ser precio * cantidad."
assert "*" in _codigo, "Calcula el total multiplicando las variables, no escribas el resultado a mano 😉"
assert "135" in _salida, "No olvides mostrar el total con print(total)."
PY,
            'success' => 'Acabas de hacer tu primer cálculo con datos. ¡Así empieza todo analista! ☕',
        ],
        [
            'type' => 'exercise',
            'title' => 'Intercambio de valores',
            'html' => <<<'HTML'
<p>Un clásico de la programación: tenemos <code>a = 5</code> y <code>b = 10</code>, y queremos <b>intercambiar</b> sus valores para que <code>a</code> valga 10 y <code>b</code> valga 5.</p>
<p>El truco: usa una tercera variable <code>temporal</code> como caja auxiliar (como cuando cambias el agua de dos vasos y necesitas un tercer vaso). <b>No escribas los números 5 ni 10</b> después de las dos primeras líneas.</p>
HTML,
            'starter' => "a = 5\nb = 10\n\n# Intercambia los valores aquí\n\n\nprint(\"a =\", a)\nprint(\"b =\", b)\n",
            'hint' => ['Primero guarda <code>a</code> en <code>temporal</code>, para no perderlo.', '<code>temporal = a</code>, luego <code>a = b</code>, y por último <code>b = temporal</code>.'],
            'solution' => "a = 5\nb = 10\n\ntemporal = a\na = b\nb = temporal\n\nprint(\"a =\", a)\nprint(\"b =\", b)\n",
            'check' => <<<'PY'
assert a == 10 and b == 5, f"Ahora a vale {a} y b vale {b}. Deberían quedar a = 10 y b = 5."
cuerpo = _codigo.split("\n", 2)[2]
assert "10" not in cuerpo and "5" not in cuerpo.replace("a =", "").replace("b =", ""), "¡Sin trampa! Intercambia usando las variables, no escribiendo los números."
PY,
            'success' => 'Dato curioso: en Python también se puede hacer en una sola línea: <code>a, b = b, a</code> 😎',
        ],
    ],
];
