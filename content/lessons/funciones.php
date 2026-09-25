<?php
return [
    'title' => 'Crea tus propias funciones',
    'icon' => '🧰',
    'minutes' => 10,
    'summary' => 'def, parámetros y return: empaqueta código reutilizable.',
    'steps' => [
        [
            'type' => 'text',
            'title' => '¿Qué es una función?',
            'html' => <<<'HTML'
<p>Ya usaste muchas funciones: <code>print()</code>, <code>len()</code>, <code>sum()</code>… Ahora vas a <b>crear las tuyas</b>.</p>
<div class="analogy">🥤 Una función es como una <b>licuadora</b>: le metes ingredientes (parámetros), hace su trabajo y te devuelve un resultado (return). Y la puedes usar las veces que quieras.</div>
<pre>def calcular_iva(precio):
    iva = precio * 0.16
    return iva

print(calcular_iva(100))   # 16.0
print(calcular_iva(250))   # 40.0</pre>
<ul>
  <li><code>def</code> significa "definir". Luego va el nombre y los <b>parámetros</b> entre paréntesis.</li>
  <li>La línea termina en <code>:</code> y el cuerpo va con sangría (como en <code>if</code>).</li>
  <li><code>return</code> <b>devuelve</b> el resultado a quien llamó la función.</li>
</ul>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Funciones con varios parámetros',
            'code' => <<<'PY'
def saludar(nombre, hora):
    if hora < 12:
        return f"¡Buenos días, {nombre}! ☀️"
    elif hora < 19:
        return f"¡Buenas tardes, {nombre}! 🌤️"
    else:
        return f"¡Buenas noches, {nombre}! 🌙"

print(saludar("Ana", 9))
print(saludar("Luis", 15))
print(saludar("Sofía", 22))

# Parámetro con valor por defecto
def precio_final(precio, descuento=0):
    return precio * (1 - descuento / 100)

print(precio_final(500))       # sin descuento
print(precio_final(500, 20))   # con 20% de descuento
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Cuál es la diferencia entre <code>print</code> y <code>return</code> dentro de una función?',
            'options' => [
                'Son lo mismo',
                'print solo muestra en pantalla; return entrega el valor para poder guardarlo o usarlo después',
                'return muestra en pantalla y print guarda el valor',
                'print es más rápido',
            ],
            'answer' => 1,
            'explain' => 'Con <code>return</code> puedes hacer <code>resultado = mi_funcion(5)</code> y seguir usando ese valor. <code>print</code> solo lo muestra y listo.',
        ],
        [
            'type' => 'exercise',
            'title' => 'Área de un rectángulo',
            'html' => '<p>Crea la función <code>area_rectangulo(base, altura)</code> que <b>devuelva</b> (con <code>return</code>) la base multiplicada por la altura.</p>',
            'starter' => "def area_rectangulo(base, altura):\n    pass  # reemplaza esta línea por tu código\n\n\nprint(area_rectangulo(4, 5))\n",
            'hint' => ['Dentro de la función escribe <code>return base * altura</code> (con 4 espacios de sangría).'],
            'solution' => "def area_rectangulo(base, altura):\n    return base * altura\n\n\nprint(area_rectangulo(4, 5))\n",
            'check' => <<<'PY'
assert callable(area_rectangulo), "Define la función area_rectangulo."
for b, h in [(4, 5), (10, 2), (3.5, 2), (0, 9)]:
    r = area_rectangulo(b, h)
    assert r is not None, "Tu función no devuelve nada. ¿Usaste return?"
    assert r == b * h, f"area_rectangulo({b}, {h}) debería dar {b*h} y dio {r}."
PY,
            'success' => 'Probamos tu función con 4 rectángulos distintos y todos salieron bien. 📐',
        ],
        [
            'type' => 'exercise',
            'title' => '¿Par o impar?',
            'html' => '<p>Crea la función <code>es_par(n)</code> que devuelva <code>True</code> si el número es par y <code>False</code> si es impar. <br>Pista de la lección de números: el operador <code>%</code> da el residuo. 😉</p>',
            'starter' => "def es_par(n):\n    pass  # reemplaza esta línea\n\n\nprint(es_par(4))\nprint(es_par(7))\n",
            'hint' => ['Un número es par si <code>n % 2 == 0</code>.', 'Puedes devolver la comparación directamente: <code>return n % 2 == 0</code>'],
            'solution' => "def es_par(n):\n    return n % 2 == 0\n\n\nprint(es_par(4))\nprint(es_par(7))\n",
            'check' => <<<'PY'
for n in [0, 1, 2, 7, 10, 13, 100]:
    assert es_par(n) == (n % 2 == 0), f"es_par({n}) debería ser {n % 2 == 0} y dio {es_par(n)}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '💸 Calculadora de propina',
            'html' => '<p>Crea <code>calcular_propina(cuenta, porcentaje=15)</code> que devuelva la propina <b>redondeada a 2 decimales</b>. Si no se indica el porcentaje, debe usar 15%.</p><pre>calcular_propina(200)      # 30.0\ncalcular_propina(200, 10)  # 20.0</pre>',
            'starter' => "def calcular_propina(cuenta, porcentaje=15):\n    pass  # reemplaza esta línea\n\n\nprint(calcular_propina(200))\nprint(calcular_propina(345.50, 10))\n",
            'hint' => ['La propina es <code>cuenta * porcentaje / 100</code>.', '<code>return round(cuenta * porcentaje / 100, 2)</code>'],
            'solution' => "def calcular_propina(cuenta, porcentaje=15):\n    return round(cuenta * porcentaje / 100, 2)\n\n\nprint(calcular_propina(200))\nprint(calcular_propina(345.50, 10))\n",
            'check' => <<<'PY'
assert calcular_propina(200) == 30, "calcular_propina(200) debería dar 30 (15% por defecto)."
assert calcular_propina(200, 10) == 20, "calcular_propina(200, 10) debería dar 20."
assert calcular_propina(345.50, 10) == 34.55, f"calcular_propina(345.50, 10) debería dar 34.55 y dio {calcular_propina(345.50, 10)}."
assert calcular_propina(99.99) == 15.0, "Redondea el resultado a 2 decimales con round()."
PY,
        ],
    ],
];
