<?php
return [
    'title' => 'Estadística y filtros con NumPy',
    'icon' => '🎯',
    'minutes' => 11,
    'summary' => 'mean, median, std y máscaras booleanas para filtrar datos.',
    'steps' => [
        [
            'type' => 'example',
            'title' => 'Estadística en una línea',
            'html' => '<p>¿Te acuerdas de lo que te costó programar la mediana? Mira esto 😅:</p>',
            'code' => <<<'PY'
import numpy as np

ventas = np.array([120, 340, 90, 410, 230, 180, 300, 95, 510, 275])

print("Total:     ", ventas.sum())
print("Media:     ", ventas.mean())
print("Mediana:   ", np.median(ventas))
print("Desv. est.:", ventas.std().round(2))
print("Mínimo:    ", ventas.min())
print("Máximo:    ", ventas.max())
print("Día del máximo (posición):", ventas.argmax())
PY,
        ],
        [
            'type' => 'text',
            'title' => '¿Qué es la desviación estándar?',
            'html' => <<<'HTML'
<p>La <b>desviación estándar</b> (<code>std</code>) mide qué tan <b>dispersos</b> están los datos respecto al promedio.</p>
<ul>
  <li>📏 <b>Pequeña</b>: los datos están cerca del promedio → comportamiento estable. Ej: <code>[49, 50, 51]</code></li>
  <li>🎢 <b>Grande</b>: los datos varían mucho → comportamiento impredecible. Ej: <code>[5, 50, 95]</code></li>
</ul>
<p>Los dos ejemplos tienen media 50, ¡pero son muy distintos! Por eso un buen analista <b>nunca reporta solo el promedio</b>.</p>
HTML,
        ],
        [
            'type' => 'quiz',
            'question' => 'Dos tiendas venden en promedio $1,000 al día. La tienda A tiene std = 50 y la tienda B tiene std = 600. ¿Qué significa?',
            'options' => ['La tienda A vende más', 'Las ventas de la tienda B son mucho más variables de un día a otro', 'La tienda B vende 600 al día', 'No significa nada'],
            'answer' => 1,
            'explain' => 'Mismo promedio, pero B tiene días muy buenos y días muy malos. A es más predecible.',
        ],
        [
            'type' => 'text',
            'title' => 'Filtrar con máscaras booleanas',
            'html' => <<<'HTML'
<p>Esta es una de las ideas <b>más poderosas</b> del análisis de datos (y la usarás igualito en pandas). Si comparas un array, obtienes un array de <code>True</code>/<code>False</code>:</p>
<pre>ventas = np.array([120, 340, 90, 410])
ventas > 200          # array([False,  True, False,  True])</pre>
<p>Y si usas ese array de booleanos <b>como índice</b>, te quedas solo con los <code>True</code>:</p>
<pre>ventas[ventas > 200]  # array([340, 410])</pre>
<p>Para combinar condiciones usa <code>&amp;</code> (y) y <code>|</code> (o), con <b>paréntesis</b> en cada condición:</p>
<pre>ventas[(ventas > 100) &amp; (ventas &lt; 400)]   # array([120, 340])</pre>
<div class="tip">💡 Truco: <code>(ventas > 200).sum()</code> cuenta cuántos cumplen, porque True vale 1 y False vale 0.</div>
HTML,
        ],
        [
            'type' => 'quiz',
            'question' => 'Si <code>a = np.array([5, 12, 8, 20])</code>, ¿qué da <code>a[a > 10]</code>?',
            'options' => ['array([12, 20])', 'array([False, True, False, True])', '2', 'array([5, 8])'],
            'answer' => 0,
            'explain' => 'La máscara <code>a > 10</code> marca True en 12 y 20, y al usarla como índice nos quedamos con esos valores.',
            'wrong' => [1 => 'Eso sería <code>a > 10</code> solo. Al ponerlo dentro de <code>a[...]</code> se filtran los valores.'],
        ],
        [
            'type' => 'exercise',
            'title' => '🎯 Días por encima del promedio',
            'html' => '<p>Con las ventas de 12 días:</p><ul><li><code>promedio</code>: la media de las ventas</li><li><code>dias_buenos</code>: array solo con las ventas <b>mayores al promedio</b></li><li><code>cuantos</code>: cuántos días buenos hubo</li></ul>',
            'starter' => "import numpy as np\n\nventas = np.array([320, 150, 410, 280, 90, 505, 330, 210, 460, 180, 390, 275])\n\n",
            'hint' => ['<code>promedio = ventas.mean()</code>', '<code>dias_buenos = ventas[ventas > promedio]</code>', '<code>cuantos = len(dias_buenos)</code>'],
            'solution' => "import numpy as np\n\nventas = np.array([320, 150, 410, 280, 90, 505, 330, 210, 460, 180, 390, 275])\n\npromedio = ventas.mean()\ndias_buenos = ventas[ventas > promedio]\ncuantos = len(dias_buenos)\nprint(promedio, dias_buenos, cuantos)\n",
            'check' => <<<'PY'
import numpy as np
v = np.array([320, 150, 410, 280, 90, 505, 330, 210, 460, 180, 390, 275])
assert abs(promedio - v.mean()) < 1e-9, "promedio debe ser la media de las ventas."
assert np.array_equal(np.sort(dias_buenos), np.sort(v[v > v.mean()])), "dias_buenos debe contener solo las ventas mayores al promedio."
assert cuantos == 6, f"Debería haber 6 días buenos y obtuviste {cuantos}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🏬 Tiendas × meses (array 2D)',
            'html' => <<<'HTML'
<p>La matriz <code>ventas</code> tiene 3 filas (tiendas) y 4 columnas (meses). En arrays 2D, el parámetro <code>axis</code> indica la dirección:</p>
<ul><li><code>axis=1</code> → opera <b>a lo largo de cada fila</b> (un resultado por tienda)</li><li><code>axis=0</code> → opera <b>a lo largo de cada columna</b> (un resultado por mes)</li></ul>
<p>Calcula <code>total_por_tienda</code> y <code>total_por_mes</code>, y en <code>mejor_tienda</code> guarda la <b>posición</b> de la tienda con más ventas (usa <code>.argmax()</code>).</p>
HTML,
            'starter' => "import numpy as np\n\n#                 Ene  Feb  Mar  Abr\nventas = np.array([[120, 135, 150, 160],   # Tienda 0\n                   [200, 180, 170, 210],   # Tienda 1\n                   [90,  110, 140, 175]])  # Tienda 2\n\n",
            'hint' => ['<code>total_por_tienda = ventas.sum(axis=1)</code>', '<code>mejor_tienda = total_por_tienda.argmax()</code>'],
            'solution' => "import numpy as np\n\nventas = np.array([[120, 135, 150, 160],\n                   [200, 180, 170, 210],\n                   [90,  110, 140, 175]])\n\ntotal_por_tienda = ventas.sum(axis=1)\ntotal_por_mes = ventas.sum(axis=0)\nmejor_tienda = total_por_tienda.argmax()\nprint(total_por_tienda, total_por_mes, mejor_tienda)\n",
            'check' => <<<'PY'
import numpy as np
assert list(total_por_tienda) == [565, 760, 515], f"total_por_tienda debería ser [565, 760, 515]. ¿Usaste axis=1?"
assert list(total_por_mes) == [410, 425, 460, 545], f"total_por_mes debería ser [410, 425, 460, 545]. ¿Usaste axis=0?"
assert mejor_tienda == 1, "La mejor tienda es la de la posición 1."
PY,
            'success' => '¡Ya piensas en tablas! En la siguiente lección conocerás pandas, que hace esto con nombres de filas y columnas. 🐼',
        ],
    ],
];
