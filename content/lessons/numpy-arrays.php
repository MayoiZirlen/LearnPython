<?php
return [
    'title' => 'NumPy: arreglos veloces',
    'icon' => '⚡',
    'minutes' => 10,
    'summary' => 'Operaciones sobre miles de números sin escribir bucles.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Tu primera librería',
            'html' => <<<'HTML'
<p>Una <b>librería</b> es código que otras personas escribieron y que puedes usar. Se cargan con <code>import</code>. <b>NumPy</b> (Numerical Python) es la base de todo el análisis de datos en Python.</p>
<pre>import numpy as np   # "np" es el apodo que usa todo el mundo</pre>
<p>Su estrella es el <b>array</b> (arreglo): parecido a una lista, pero diseñado para números y con un superpoder: las <b>operaciones vectorizadas</b>. Es decir, una operación se aplica a <b>todos los elementos a la vez</b>, sin bucles.</p>
<div class="analogy">🏭 Una lista es como lavar platos uno por uno. Un array de NumPy es un lavavajillas: metes todos y los lava de golpe.</div>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Lista vs. array',
            'html' => '<p>Mira la diferencia al multiplicar por 2. <i>(La primera vez que uses NumPy tardará unos segundos en cargar.)</i></p>',
            'code' => <<<'PY'
import numpy as np

lista = [10, 20, 30]
arreglo = np.array([10, 20, 30])

print("Lista × 2:", lista * 2)      # ¡repite la lista!
print("Array × 2:", arreglo * 2)    # multiplica cada número

print("Array + 5:", arreglo + 5)
print("Array ** 2:", arreglo ** 2)

# Operaciones entre dos arrays (elemento con elemento)
precios = np.array([100, 250, 80])
cantidades = np.array([3, 1, 10])
print("Totales:", precios * cantidades)
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'Si <code>a = np.array([1, 2, 3])</code>, ¿qué da <code>a * 3</code>?',
            'options' => ['[1, 2, 3, 1, 2, 3, 1, 2, 3]', 'array([3, 6, 9])', '18', 'Error'],
            'answer' => 1,
            'explain' => 'Con arrays, la multiplicación se aplica a cada elemento: <b>[3, 6, 9]</b>. La opción A es lo que pasaría con una lista normal.',
        ],
        [
            'type' => 'text',
            'title' => 'Crear e inspeccionar arrays',
            'html' => <<<'HTML'
<pre>np.arange(1, 11)          # [1, 2, ..., 10]  (como range)
np.linspace(0, 1, 5)      # 5 números entre 0 y 1: [0, 0.25, 0.5, 0.75, 1]
np.zeros(3)               # [0., 0., 0.]

a = np.array([4, 8, 15, 16, 23, 42])
a.shape      # (6,)  → forma: 6 elementos
a.dtype      # int64 → tipo de dato
a[0]         # 4     → indexar igual que listas
a[1:4]       # [8, 15, 16]</pre>
<p>Los arrays pueden tener <b>2 dimensiones</b> (filas y columnas), ¡como una hoja de cálculo!</p>
<pre>tabla = np.array([[1, 2, 3],
                  [4, 5, 6]])
tabla.shape   # (2, 3) → 2 filas, 3 columnas</pre>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '🧾 IVA en un solo paso',
            'html' => '<p>¿Recuerdas el ejercicio del IVA con un bucle? Ahora hazlo con NumPy: crea <code>precios_iva</code> multiplicando el array <code>precios</code> por <code>1.16</code>. ¡Sin bucles!</p>',
            'starter' => "import numpy as np\n\nprecios = np.array([100, 250, 49.9, 1200])\n\nprecios_iva = \nprint(precios_iva)\n",
            'hint' => '<code>precios_iva = precios * 1.16</code>',
            'solution' => "import numpy as np\n\nprecios = np.array([100, 250, 49.9, 1200])\n\nprecios_iva = precios * 1.16\nprint(precios_iva)\n",
            'check' => <<<'PY'
import numpy as np
assert isinstance(precios_iva, np.ndarray), "precios_iva debe ser un array de NumPy (multiplica el array directamente)."
assert np.allclose(precios_iva, np.array([100, 250, 49.9, 1200]) * 1.16), "Los valores no coinciden con precios × 1.16."
assert "for" not in _codigo, "¡Sin bucles! Esa es la magia de NumPy 😉"
PY,
            'success' => 'Una línea en lugar de un bucle. Con un millón de precios sería igual de corto… ¡y rapidísimo! ⚡',
        ],
        [
            'type' => 'exercise',
            'title' => '🌡️ Celsius a Fahrenheit',
            'html' => '<p>Convierte todas las temperaturas del array <code>celsius</code> a Fahrenheit y guárdalas en <code>fahrenheit</code>. La fórmula es:</p><pre>F = C × 9 / 5 + 32</pre>',
            'starter' => "import numpy as np\n\ncelsius = np.array([-5, 0, 12.5, 25, 37, 100])\n\n",
            'hint' => '<code>fahrenheit = celsius * 9 / 5 + 32</code>',
            'solution' => "import numpy as np\n\ncelsius = np.array([-5, 0, 12.5, 25, 37, 100])\n\nfahrenheit = celsius * 9 / 5 + 32\nprint(fahrenheit)\n",
            'check' => <<<'PY'
import numpy as np
assert np.allclose(fahrenheit, [23, 32, 54.5, 77, 98.6, 212]), f"Deberías obtener [23, 32, 54.5, 77, 98.6, 212] y obtuviste {fahrenheit}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '💰 Ingresos por producto',
            'html' => '<p>Tienes los precios y las unidades vendidas de 5 productos. Calcula:</p><ul><li><code>ingresos</code>: array con precio × unidades de cada producto</li><li><code>ingreso_total</code>: la suma de todos (usa <code>ingresos.sum()</code>)</li></ul>',
            'starter' => "import numpy as np\n\nprecios = np.array([850, 25, 45, 220, 130])\nunidades = np.array([12, 150, 80, 30, 45])\n\n",
            'hint' => ['Multiplica los dos arrays: <code>precios * unidades</code>', 'Los arrays tienen métodos: <code>.sum()</code>, <code>.mean()</code>, <code>.max()</code>…'],
            'solution' => "import numpy as np\n\nprecios = np.array([850, 25, 45, 220, 130])\nunidades = np.array([12, 150, 80, 30, 45])\n\ningresos = precios * unidades\ningreso_total = ingresos.sum()\nprint(ingresos)\nprint(\"Total:\", ingreso_total)\n",
            'check' => <<<'PY'
import numpy as np
esperado = np.array([850, 25, 45, 220, 130]) * np.array([12, 150, 80, 30, 45])
assert np.array_equal(ingresos, esperado), f"ingresos debería ser {esperado}."
assert ingreso_total == esperado.sum(), f"ingreso_total debería ser {esperado.sum()}."
PY,
        ],
    ],
];
