<?php
return [
    'title' => 'Números y operaciones',
    'icon' => '🧮',
    'minutes' => 8,
    'summary' => 'Python como calculadora: enteros, decimales y operadores.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Dos tipos de números',
            'html' => <<<'HTML'
<ul>
  <li><b>Enteros</b> (<code>int</code>): sin decimales → <code>7</code>, <code>-3</code>, <code>2025</code></li>
  <li><b>Decimales</b> (<code>float</code>): con punto → <code>3.14</code>, <code>-0.5</code>, <code>19.99</code></li>
</ul>
<div class="warn">⚠️ En Python los decimales se escriben con <b>punto</b>, no con coma: <code>19.99</code> ✅ &nbsp; <code>19,99</code> ❌</div>
<p><b>Operadores</b> para hacer cuentas:</p>
<table>
<tr><th>Operador</th><th>Qué hace</th><th>Ejemplo</th><th>Resultado</th></tr>
<tr><td><code>+</code></td><td>Suma</td><td><code>7 + 2</code></td><td>9</td></tr>
<tr><td><code>-</code></td><td>Resta</td><td><code>7 - 2</code></td><td>5</td></tr>
<tr><td><code>*</code></td><td>Multiplicación</td><td><code>7 * 2</code></td><td>14</td></tr>
<tr><td><code>/</code></td><td>División</td><td><code>7 / 2</code></td><td>3.5</td></tr>
<tr><td><code>//</code></td><td>División entera (sin decimales)</td><td><code>7 // 2</code></td><td>3</td></tr>
<tr><td><code>%</code></td><td>Residuo (lo que sobra)</td><td><code>7 % 2</code></td><td>1</td></tr>
<tr><td><code>**</code></td><td>Potencia</td><td><code>7 ** 2</code></td><td>49</td></tr>
</table>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'La calculadora más potente',
            'html' => '<p>Ejecuta y luego cambia los números. ¿Qué pasa si divides entre 0? 🤔</p>',
            'code' => <<<'PY'
print(7 + 2)
print(7 / 2)
print(7 // 2)
print(7 % 2)
print(2 ** 10)

# También respeta el orden de las operaciones (primero * y /)
print(2 + 3 * 4)
print((2 + 3) * 4)
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Cuánto da <code>17 // 5</code>?',
            'options' => ['3.4', '3', '2', '4'],
            'answer' => 1,
            'explain' => '<code>//</code> divide y se queda solo con la parte entera: 17 ÷ 5 = 3.4 → <b>3</b>.',
        ],
        [
            'type' => 'quiz',
            'question' => 'El operador <code>%</code> da el residuo. ¿Para qué sirve <code>numero % 2</code>?',
            'options' => ['Para sacar el 2% de un número', 'Para saber si un número es par (da 0) o impar (da 1)', 'Para dividir entre 2', 'Para elevar al cuadrado'],
            'answer' => 1,
            'explain' => 'Si al dividir entre 2 no sobra nada (residuo 0), el número es par. ¡Truco muy usado!',
            'wrong' => [0 => 'Cuidado: en Python <code>%</code> no es porcentaje, es el residuo de una división.'],
        ],
        [
            'type' => 'text',
            'title' => 'Tipos y conversiones',
            'html' => <<<'HTML'
<p>Con <code>type()</code> puedes preguntarle a Python qué tipo de dato es algo:</p>
<pre>type(10)      # &lt;class 'int'&gt;
type(10.5)    # &lt;class 'float'&gt;
type("10")    # &lt;class 'str'&gt;  ← ¡es texto!</pre>
<p>Y puedes <b>convertir</b> entre tipos. Esto es muy común al leer datos de archivos, donde a veces los números llegan como texto:</p>
<pre>int("42")       # 42   (texto → entero)
float("19.99")  # 19.99 (texto → decimal)
str(100)        # "100" (número → texto)
round(3.14159, 2)  # 3.14 (redondear a 2 decimales)</pre>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => 'Promedio de calificaciones',
            'html' => '<p>Tienes tres calificaciones: <code>8</code>, <code>9.5</code> y <code>7</code>. Calcula el promedio (suma las tres y divide entre 3), guárdalo en la variable <code>promedio</code> <b>redondeado a 2 decimales</b> con <code>round()</code> y muéstralo.</p>',
            'starter' => "calif1 = 8\ncalif2 = 9.5\ncalif3 = 7\n\npromedio = \n",
            'hint' => ['Usa paréntesis para sumar primero: <code>(calif1 + calif2 + calif3) / 3</code>', 'Para redondear: <code>round(valor, 2)</code>'],
            'solution' => "calif1 = 8\ncalif2 = 9.5\ncalif3 = 7\n\npromedio = round((calif1 + calif2 + calif3) / 3, 2)\nprint(promedio)\n",
            'check' => <<<'PY'
assert isinstance(promedio, (int, float)), "promedio debe ser un número."
assert promedio != 19.83, "¡Cuidado con el orden de las operaciones! Sin paréntesis solo se divide calif3. Usa (calif1 + calif2 + calif3) / 3"
assert promedio == 8.17, f"El promedio debería ser 8.17 y tu resultado es {promedio}. ¿Lo redondeaste a 2 decimales?"
assert "8.17" in _salida, "Muestra el promedio con print()."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📊 Tu primer indicador: crecimiento',
            'html' => <<<'HTML'
<p>¡Primer cálculo de analista! Una tienda vendió <code>12500</code> en enero y <code>15000</code> en febrero. El <b>crecimiento porcentual</b> se calcula así:</p>
<pre>crecimiento = (nuevo - anterior) / anterior * 100</pre>
<p>Guarda el resultado en <code>crecimiento</code> y muéstralo.</p>
HTML,
            'starter' => "ventas_enero = 12500\nventas_febrero = 15000\n\n",
            'hint' => 'Aquí "nuevo" es febrero y "anterior" es enero: <code>(ventas_febrero - ventas_enero) / ventas_enero * 100</code>',
            'solution' => "ventas_enero = 12500\nventas_febrero = 15000\n\ncrecimiento = (ventas_febrero - ventas_enero) / ventas_enero * 100\nprint(crecimiento)\n",
            'check' => <<<'PY'
assert abs(crecimiento - 20) < 1e-9, f"El crecimiento debería ser 20 (%) y te dio {crecimiento}. Revisa los paréntesis."
assert "ventas_enero" in _codigo.split("crecimiento", 1)[1], "Calcula usando las variables, no el número directo."
assert "20" in _salida, "Muestra el resultado con print()."
PY,
            'success' => 'Las ventas crecieron 20%. Este tipo de indicador (KPI) es de lo que más calcularás como analista. 📈',
        ],
    ],
];
