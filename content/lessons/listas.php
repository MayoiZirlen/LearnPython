<?php
return [
    'title' => 'Listas: muchos datos juntos',
    'icon' => '📋',
    'minutes' => 9,
    'summary' => 'Guarda colecciones de datos y calcula totales al instante.',
    'steps' => [
        [
            'type' => 'text',
            'title' => '¿Qué es una lista?',
            'html' => <<<'HTML'
<p>Hasta ahora cada variable guardaba <b>un solo dato</b>. Pero en el mundo real tenemos las ventas de <i>todos</i> los días, los nombres de <i>todos</i> los clientes… Para eso existen las <b>listas</b>:</p>
<pre>ventas = [120, 340, 90, 410]
frutas = ["manzana", "pera", "uva"]
mezcla = ["Ana", 25, True, 3.5]   # pueden mezclar tipos</pre>
<p>Se escriben entre <b>corchetes</b> <code>[ ]</code> y los elementos se separan con comas. Como en los strings, cada elemento tiene una posición que <b>empieza en 0</b>:</p>
<pre>frutas[0]    # "manzana"
frutas[-1]   # "uva" (el último)
len(frutas)  # 3</pre>
<div class="analogy">🚂 Una lista es como un tren: cada vagón tiene un número (empezando en 0) y lleva un dato adentro.</div>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Modificar listas',
            'html' => '<p>Las listas pueden crecer, encogerse y cambiar:</p>',
            'code' => <<<'PY'
frutas = ["manzana", "pera", "uva"]

frutas.append("mango")      # agrega al final
print(frutas)

frutas.remove("pera")       # elimina por valor
print(frutas)

frutas[0] = "fresa"         # reemplaza la posición 0
print(frutas)

print("¿Hay uva?", "uva" in frutas)
print("Tengo", len(frutas), "frutas")
PY,
        ],
        [
            'type' => 'text',
            'title' => 'Superpoderes para datos',
            'html' => <<<'HTML'
<p>Python trae funciones que funcionan directo sobre listas de números. <b>Esto ya es análisis de datos:</b></p>
<table>
<tr><th>Función</th><th>Qué hace</th><th>Con <code>[4, 1, 9, 2]</code></th></tr>
<tr><td><code>sum(lista)</code></td><td>Suma todo</td><td>16</td></tr>
<tr><td><code>len(lista)</code></td><td>Cuántos elementos hay</td><td>4</td></tr>
<tr><td><code>max(lista)</code></td><td>El mayor</td><td>9</td></tr>
<tr><td><code>min(lista)</code></td><td>El menor</td><td>1</td></tr>
<tr><td><code>sorted(lista)</code></td><td>Ordena (menor a mayor)</td><td>[1, 2, 4, 9]</td></tr>
</table>
<p>Y con <b>rebanadas</b> (slicing) tomas una parte:</p>
<pre>numeros = [10, 20, 30, 40, 50]
numeros[0:3]   # [10, 20, 30]  primeros 3
numeros[-2:]   # [40, 50]      últimos 2</pre>
HTML,
        ],
        [
            'type' => 'quiz',
            'question' => 'Si <code>precios = [15, 8, 42, 23]</code>, ¿qué da <code>precios[-1]</code>?',
            'options' => ['15', '8', '23', 'Error: no hay posición -1'],
            'answer' => 2,
            'explain' => 'Los índices negativos cuentan desde el final: <code>-1</code> es el último elemento, <b>23</b>.',
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué da <code>sorted([3, 1, 2])[0]</code>?',
            'options' => ['3', '1', '2', '[1, 2, 3]'],
            'answer' => 1,
            'explain' => 'Primero se ordena la lista → <code>[1, 2, 3]</code> y luego se toma la posición 0 → <b>1</b>.',
        ],
        [
            'type' => 'exercise',
            'title' => '📊 Reporte semanal',
            'html' => <<<'HTML'
<p>Estas son las ventas de los 7 días de la semana. Calcula:</p>
<ul>
  <li><code>total</code>: la suma de todas las ventas</li>
  <li><code>promedio</code>: el total dividido entre la cantidad de días (usa <code>len</code>)</li>
  <li><code>mejor_dia</code>: la venta más alta</li>
  <li><code>peor_dia</code>: la venta más baja</li>
</ul>
HTML,
            'starter' => "ventas_semana = [120, 340, 90, 410, 230, 180, 300]\n\n\nprint(\"Total:\", total)\nprint(\"Promedio:\", promedio)\nprint(\"Mejor día:\", mejor_dia)\nprint(\"Peor día:\", peor_dia)\n",
            'hint' => ['Usa <code>sum()</code>, <code>len()</code>, <code>max()</code> y <code>min()</code>.', '<code>promedio = total / len(ventas_semana)</code>'],
            'solution' => "ventas_semana = [120, 340, 90, 410, 230, 180, 300]\n\ntotal = sum(ventas_semana)\npromedio = total / len(ventas_semana)\nmejor_dia = max(ventas_semana)\npeor_dia = min(ventas_semana)\n\nprint(\"Total:\", total)\nprint(\"Promedio:\", promedio)\nprint(\"Mejor día:\", mejor_dia)\nprint(\"Peor día:\", peor_dia)\n",
            'check' => <<<'PY'
v = [120, 340, 90, 410, 230, 180, 300]
assert total == sum(v), f"total debería ser {sum(v)}."
assert abs(promedio - sum(v) / len(v)) < 1e-9, "promedio debería ser total / len(ventas_semana)."
assert mejor_dia == 410, "mejor_dia debería ser la venta más alta."
assert peor_dia == 90, "peor_dia debería ser la venta más baja."
assert "sum(" in _codigo, "Usa sum() en lugar de sumar a mano 😉"
PY,
            'success' => '¡Eso es un reporte de ventas en 4 líneas! En Excel habrías usado SUMA, PROMEDIO, MAX y MIN. 📈',
        ],
        [
            'type' => 'exercise',
            'title' => 'Actualiza el inventario',
            'html' => '<p>Tienes la lista <code>productos</code>. Haz tres cambios, en este orden:</p><ol><li>Agrega <code>"Monitor"</code> al final</li><li>Elimina <code>"Fax"</code> (¡ya nadie usa fax!)</li><li>Crea la variable <code>primeros</code> con los <b>primeros 2</b> productos usando rebanadas</li></ol>',
            'starter' => "productos = [\"Laptop\", \"Fax\", \"Mouse\", \"Teclado\"]\n\n\nprint(productos)\nprint(primeros)\n",
            'hint' => ['Usa <code>.append()</code> y <code>.remove()</code>.', 'Los primeros 2 son <code>productos[0:2]</code> (o <code>productos[:2]</code>).'],
            'solution' => "productos = [\"Laptop\", \"Fax\", \"Mouse\", \"Teclado\"]\n\nproductos.append(\"Monitor\")\nproductos.remove(\"Fax\")\nprimeros = productos[:2]\n\nprint(productos)\nprint(primeros)\n",
            'check' => <<<'PY'
assert "Fax" not in productos, "Todavía aparece \"Fax\" en la lista."
assert productos[-1] == "Monitor", "\"Monitor\" debería estar al final de la lista."
assert productos == ["Laptop", "Mouse", "Teclado", "Monitor"], f"La lista quedó así: {productos}"
assert primeros == ["Laptop", "Mouse"], f"primeros debería ser ['Laptop', 'Mouse'] y es {primeros}."
PY,
        ],
    ],
];
