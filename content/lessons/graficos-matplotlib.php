<?php
return [
    'title' => 'Tus primeros gráficos',
    'icon' => '🎨',
    'minutes' => 11,
    'summary' => 'Líneas, barras e histogramas con matplotlib.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Una imagen vale más que mil filas',
            'html' => <<<'HTML'
<p>Una tabla con 300 números no dice mucho a simple vista. Un gráfico revela <b>tendencias, comparaciones y sorpresas</b> en segundos. La librería clásica para graficar es <b>matplotlib</b>:</p>
<pre>import matplotlib.pyplot as plt

plt.plot(x, y)          # dibuja
plt.title("Título")     # decora
plt.xlabel("Eje X")
plt.ylabel("Eje Y")
plt.show()              # muestra</pre>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Gráfico de líneas',
            'html' => '<p>Las líneas son ideales para ver cómo cambia algo <b>en el tiempo</b>. Prueba cambiar el color (<code>"tab:red"</code>, <code>"green"</code>…) o el marcador (<code>"s"</code>, <code>"^"</code>).</p>',
            'code' => <<<'PY'
import matplotlib.pyplot as plt

meses = ["Ene", "Feb", "Mar", "Abr", "May", "Jun"]
ventas = [12500, 15000, 14200, 17800, 19500, 21000]

plt.figure(figsize=(8, 4))
plt.plot(meses, ventas, marker="o", color="tab:blue", linewidth=2)
plt.title("Ventas del primer semestre")
plt.xlabel("Mes")
plt.ylabel("Ventas ($)")
plt.grid(alpha=0.3)
plt.show()
PY,
        ],
        [
            'type' => 'example',
            'title' => 'Gráfico de barras',
            'html' => '<p>Las barras sirven para <b>comparar categorías</b>. Con <code>barh</code> las haces horizontales (útil cuando los nombres son largos).</p>',
            'code' => <<<'PY'
import matplotlib.pyplot as plt

productos = ["Laptop", "Monitor", "Silla", "Escritorio", "Mochila"]
ingresos = [52000, 18400, 21300, 27900, 6100]

plt.figure(figsize=(8, 4))
plt.bar(productos, ingresos, color=["#3776ab", "#3776ab", "#ffd43b", "#ffd43b", "#22c55e"])
plt.title("Ingresos por producto")
plt.ylabel("Ingresos ($)")
plt.show()
PY,
        ],
        [
            'type' => 'text',
            'title' => '¿Qué gráfico uso?',
            'html' => <<<'HTML'
<table>
<tr><th>Quiero mostrar…</th><th>Gráfico</th><th>En matplotlib</th></tr>
<tr><td>Cambio en el tiempo</td><td>📈 Líneas</td><td><code>plt.plot()</code></td></tr>
<tr><td>Comparar categorías</td><td>📊 Barras</td><td><code>plt.bar()</code></td></tr>
<tr><td>Cómo se distribuyen los valores</td><td>📶 Histograma</td><td><code>plt.hist()</code></td></tr>
<tr><td>Relación entre dos variables</td><td>⚬ Dispersión</td><td><code>plt.scatter()</code></td></tr>
<tr><td>Partes de un todo (pocas)</td><td>🥧 Pastel</td><td><code>plt.pie()</code></td></tr>
</table>
<div class="tip">💡 Regla de oro: todo gráfico necesita <b>título</b> y <b>ejes con nombre</b>. Si alguien no entiende tu gráfico en 5 segundos, simplifícalo.</div>
HTML,
        ],
        [
            'type' => 'quiz',
            'question' => 'Quieres mostrar al jefe cómo evolucionaron los clientes nuevos durante los 12 meses del año. ¿Qué gráfico usas?',
            'options' => ['Pastel', 'Líneas', 'Histograma', 'Dispersión'],
            'answer' => 1,
            'explain' => 'Evolución en el tiempo → <b>líneas</b>. El pastel sería ilegible con 12 rebanadas.',
        ],
        [
            'type' => 'exercise',
            'title' => '📊 Barras de ventas por región',
            'html' => '<p>Con los datos dados, crea un <b>gráfico de barras</b> de ventas por región. Debe tener un <b>título</b> (<code>plt.title</code>) y terminar con <code>plt.show()</code>.</p>',
            'starter' => "import matplotlib.pyplot as plt\n\nregiones = [\"Norte\", \"Sur\", \"Centro\", \"Este\"]\nventas = [45200, 38100, 52700, 42900]\n\n",
            'hint' => ['<code>plt.bar(regiones, ventas)</code>', 'No olvides <code>plt.title("...")</code> y <code>plt.show()</code>.'],
            'solution' => "import matplotlib.pyplot as plt\n\nregiones = [\"Norte\", \"Sur\", \"Centro\", \"Este\"]\nventas = [45200, 38100, 52700, 42900]\n\nplt.bar(regiones, ventas, color=\"tab:blue\")\nplt.title(\"Ventas por región\")\nplt.ylabel(\"Ventas ($)\")\nplt.show()\n",
            'check' => <<<'PY'
assert _imagenes >= 1, "No se generó ningún gráfico. ¿Usaste plt.bar() y plt.show()?"
assert ".bar(" in _codigo or ".barh(" in _codigo, "Usa un gráfico de barras: plt.bar()"
assert "title(" in _codigo, "Tu gráfico necesita un título: plt.title(\"...\")"
PY,
            'success' => '¡Tu primer gráfico! 🎨 Ya puedes presumirlo en una presentación.',
        ],
        [
            'type' => 'exercise',
            'title' => '📶 Histograma de edades',
            'html' => '<p>Un <b>histograma</b> agrupa valores en rangos (bins) y muestra cuántos hay en cada rango. Crea un histograma de <code>edades</code> con <code>plt.hist(edades, bins=8)</code>, agrega título y etiquetas a los ejes, y muéstralo.</p>',
            'starter' => "import matplotlib.pyplot as plt\n\nedades = [22, 25, 31, 35, 28, 45, 52, 38, 29, 33, 41, 27, 24, 36, 48,\n          30, 26, 39, 55, 34, 23, 31, 42, 29, 37, 60, 33, 28, 44, 32]\n\n",
            'hint' => '<code>plt.hist(edades, bins=8, color="tab:orange", edgecolor="white")</code>, luego <code>plt.title</code>, <code>plt.xlabel</code>, <code>plt.ylabel</code> y <code>plt.show()</code>.',
            'solution' => "import matplotlib.pyplot as plt\n\nedades = [22, 25, 31, 35, 28, 45, 52, 38, 29, 33, 41, 27, 24, 36, 48,\n          30, 26, 39, 55, 34, 23, 31, 42, 29, 37, 60, 33, 28, 44, 32]\n\nplt.hist(edades, bins=8, color=\"tab:orange\", edgecolor=\"white\")\nplt.title(\"Edad de los clientes\")\nplt.xlabel(\"Edad\")\nplt.ylabel(\"Cantidad de clientes\")\nplt.show()\n",
            'check' => <<<'PY'
assert _imagenes >= 1, "No se generó ningún gráfico."
assert ".hist(" in _codigo, "Usa plt.hist() para el histograma."
assert "xlabel(" in _codigo and "ylabel(" in _codigo, "Ponle nombre a los ejes con plt.xlabel() y plt.ylabel()."
PY,
            'success' => '¿Ves en qué rango de edad está la mayoría de los clientes? Esa es la magia del histograma. 👀',
        ],
    ],
];
