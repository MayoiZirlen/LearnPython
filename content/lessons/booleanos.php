<?php
return [
    'title' => 'Verdadero o falso',
    'icon' => '⚖️',
    'minutes' => 7,
    'summary' => 'Booleanos, comparaciones y los operadores and, or, not.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Preguntas de sí o no',
            'html' => <<<'HTML'
<p>Las computadoras toman decisiones haciendo preguntas de <b>sí o no</b>. En Python las respuestas son <code>True</code> (verdadero) o <code>False</code> (falso). A este tipo de dato se le llama <b>booleano</b> (<code>bool</code>).</p>
<table>
<tr><th>Comparación</th><th>Significado</th><th>Ejemplo</th><th>Resultado</th></tr>
<tr><td><code>==</code></td><td>¿Es igual?</td><td><code>5 == 5</code></td><td>True</td></tr>
<tr><td><code>!=</code></td><td>¿Es diferente?</td><td><code>5 != 3</code></td><td>True</td></tr>
<tr><td><code>&gt;</code></td><td>¿Mayor que?</td><td><code>5 &gt; 8</code></td><td>False</td></tr>
<tr><td><code>&lt;</code></td><td>¿Menor que?</td><td><code>5 &lt; 8</code></td><td>True</td></tr>
<tr><td><code>&gt;=</code></td><td>¿Mayor o igual?</td><td><code>5 &gt;= 5</code></td><td>True</td></tr>
<tr><td><code>&lt;=</code></td><td>¿Menor o igual?</td><td><code>4 &lt;= 3</code></td><td>False</td></tr>
</table>
<div class="warn">⚠️ <b>El error más común:</b> <code>=</code> guarda un valor, <code>==</code> compara. <code>x = 5</code> es "guarda 5 en x"; <code>x == 5</code> es "¿x vale 5?".</div>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Haz preguntas',
            'html' => '<p>Ejecuta y prueba tus propias comparaciones. ¿Qué pasa al comparar textos?</p>',
            'code' => <<<'PY'
edad = 20
print(edad >= 18)
print(edad == 30)
print("hola" == "Hola")   # ¡Las mayúsculas importan!

ventas = 15400
meta = 15000
supero_meta = ventas > meta
print("¿Superamos la meta?", supero_meta)
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué da <code>5 == "5"</code>?',
            'options' => ['True', 'False', 'Error', '5'],
            'answer' => 1,
            'explain' => 'Uno es el número 5 y el otro es el <b>texto</b> "5". Son de tipos distintos, así que no son iguales. ¡Esto causa muchos bugs al leer datos!',
        ],
        [
            'type' => 'text',
            'title' => 'Combinar condiciones: and, or, not',
            'html' => <<<'HTML'
<ul>
  <li><code>and</code> (y): <b>las dos</b> deben ser verdaderas. <i>"Tengo boleto <b>y</b> soy mayor de edad"</i>.</li>
  <li><code>or</code> (o): basta con que <b>una</b> sea verdadera. <i>"Pago con efectivo <b>o</b> con tarjeta"</i>.</li>
  <li><code>not</code> (no): invierte el valor. <code>not True</code> es <code>False</code>.</li>
</ul>
<pre>edad = 25
tiene_boleto = True
print(edad >= 18 and tiene_boleto)   # True
print(edad < 12 or edad > 65)        # False
print(not tiene_boleto)              # False</pre>
<div class="tip">💡 En análisis de datos usarás esto para <b>filtrar</b>: "ventas de la región Norte <b>y</b> mayores a $1000".</div>
HTML,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué da <code>True and False</code>?',
            'options' => ['True', 'False'],
            'answer' => 1,
            'explain' => 'Con <code>and</code> ambas partes deben ser verdaderas. Como una es False, el resultado es <b>False</b>.',
        ],
        [
            'type' => 'quiz',
            'question' => 'Un cliente obtiene envío gratis si compra más de $500 <b>o</b> es cliente premium. ¿Cuál es la condición correcta?',
            'options' => ['total > 500 and premium', 'total > 500 or premium', 'total >= 500 and not premium', 'not total > 500'],
            'answer' => 1,
            'explain' => 'Basta con cumplir <b>una</b> de las dos, por eso usamos <code>or</code>.',
        ],
        [
            'type' => 'exercise',
            'title' => '¿Puede votar?',
            'html' => '<p>Una persona puede votar si tiene <b>18 años o más</b> <b>y</b> tiene credencial. Crea la variable <code>puede_votar</code> usando una comparación y <code>and</code>, y muéstrala.</p>',
            'starter' => "edad = 20\ntiene_credencial = True\n\npuede_votar = \n",
            'hint' => ['Compara la edad con <code>&gt;=</code>.', '<code>puede_votar = edad >= 18 and tiene_credencial</code>'],
            'solution' => "edad = 20\ntiene_credencial = True\n\npuede_votar = edad >= 18 and tiene_credencial\nprint(puede_votar)\n",
            'check' => <<<'PY'
assert isinstance(puede_votar, bool), "puede_votar debe ser True o False (resultado de una comparación)."
assert ">=" in _codigo and "and" in _codigo, "Usa una comparación con >= y el operador and."
assert puede_votar is True, "Con 20 años y credencial, puede_votar debería ser True."
assert "True" in _salida, "Muestra el resultado con print()."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📊 Alerta de inventario',
            'html' => '<p>Un producto necesita reorden si quedan <b>menos de 20 unidades</b> <b>o</b> si <b>no</b> está activo el proveedor alterno. Crea <code>reordenar</code> con esa lógica (usa <code>&lt;</code>, <code>or</code> y <code>not</code>).</p>',
            'starter' => "unidades = 35\nproveedor_alterno = False\n\nreordenar = \nprint(reordenar)\n",
            'hint' => ['Son dos condiciones unidas con <code>or</code>: <code>unidades &lt; 20</code> y <code>not proveedor_alterno</code>.'],
            'solution' => "unidades = 35\nproveedor_alterno = False\n\nreordenar = unidades < 20 or not proveedor_alterno\nprint(reordenar)\n",
            'check' => <<<'PY'
assert "or" in _codigo and "not" in _codigo and "<" in _codigo, "Usa <, or y not en tu condición."
assert reordenar is True, "Como no hay proveedor alterno, reordenar debería ser True."
PY,
        ],
    ],
];
