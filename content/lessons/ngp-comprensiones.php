<?php
return [
    'title' => 'Comprensiones: bucles en una línea',
    'icon' => '🌀',
    'minutes' => 10,
    'summary' => 'Crea listas y diccionarios filtrados con una sola línea elegante.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Bienvenido/a al New Game Plus',
            'html' => <<<'HTML'
<p>Ya dominas lo básico. En el <b>New Game Plus</b> vuelves a jugar… pero con enemigos más fuertes y herramientas más poderosas. 😈</p>
<p>Empecemos con un truco que usan todos los programadores de Python: las <b>comprensiones de lista</b>. ¿Recuerdas el patrón de "lista vacía + for + append"?</p>
<pre>con_iva = []
for p in precios:
    con_iva.append(p * 1.16)</pre>
<p>Con una comprensión se escribe así:</p>
<pre>con_iva = [p * 1.16 for p in precios]</pre>
<div class="analogy">🏭 Léelo como una frase: "dame <b>p × 1.16</b> <b>para cada</b> p <b>en</b> precios".</div>
<p>Y puedes agregar un filtro al final:</p>
<pre>caros = [p for p in precios if p > 100]</pre>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Comprensiones en acción',
            'code' => <<<'PY'
numeros = [3, 8, 12, 5, 20, 7]

dobles = [n * 2 for n in numeros]
pares = [n for n in numeros if n % 2 == 0]
etiquetas = ["alto" if n > 10 else "bajo" for n in numeros]

print(dobles)
print(pares)
print(etiquetas)

# También con diccionarios: {clave: valor for ...}
cuadrados = {n: n ** 2 for n in range(1, 6)}
print(cuadrados)
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué da esta comprensión?',
            'code' => '[x * 10 for x in range(5) if x % 2 == 0]',
            'options' => ['[0, 10, 20, 30, 40]', '[0, 20, 40]', '[10, 30]', '[20, 40]'],
            'answer' => 1,
            'explain' => '<code>range(5)</code> da 0,1,2,3,4. El filtro deja los pares (0, 2, 4) y luego se multiplican por 10 → <b>[0, 20, 40]</b>.',
        ],
        [
            'type' => 'exercise',
            'title' => '🧾 IVA en una línea',
            'html' => '<p>Crea <code>con_iva</code> con cada precio × 1.16, <b>redondeado a 2 decimales</b>, usando una <b>comprensión de lista</b> (sin <code>.append</code>).</p>',
            'starter' => "precios = [100, 250, 49.9, 1200, 15.5]\n\ncon_iva = \nprint(con_iva)\n",
            'hint' => '<code>[round(p * 1.16, 2) for p in precios]</code>',
            'solution' => "precios = [100, 250, 49.9, 1200, 15.5]\n\ncon_iva = [round(p * 1.16, 2) for p in precios]\nprint(con_iva)\n",
            'check' => <<<'PY'
assert ".append" not in _codigo, "¡Sin append! Usa una comprensión de lista: [ ... for p in precios]"
assert "for" in _codigo and "[" in _codigo, "Usa una comprensión de lista."
assert con_iva == [116.0, 290.0, 57.88, 1392.0, 17.98], f"Obtuviste {con_iva}"
PY,
        ],
        [
            'type' => 'text',
            'title' => 'zip: recorrer dos listas a la vez',
            'html' => <<<'HTML'
<p><code>zip()</code> junta dos (o más) listas elemento con elemento, como una cremallera 🤐:</p>
<pre>productos = ["café", "té", "pan"]
precios = [45, 35, 20]
for p, pr in zip(productos, precios):
    print(p, pr)        # café 45, té 35, pan 20</pre>
<p>Combinado con una comprensión de diccionario, construyes un catálogo en una línea:</p>
<pre>catalogo = {p: pr for p, pr in zip(productos, precios)}</pre>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '📖 Catálogo con descuento',
            'html' => '<p>Crea el diccionario <code>catalogo</code> con cada producto como clave y su precio <b>con 10% de descuento</b> como valor (precio × 0.9, redondeado a 2 decimales). Solo incluye los productos que cuestan <b>más de 30</b> (antes del descuento).</p>',
            'starter' => "productos = [\"café\", \"té\", \"pan\", \"pastel\", \"jugo\"]\nprecios = [45, 25, 20, 120, 38]\n\ncatalogo = \nprint(catalogo)\n",
            'hint' => ['Usa <code>zip(productos, precios)</code> para recorrer ambas listas.', '<code>{p: round(pr * 0.9, 2) for p, pr in zip(productos, precios) if pr > 30}</code>'],
            'solution' => "productos = [\"café\", \"té\", \"pan\", \"pastel\", \"jugo\"]\nprecios = [45, 25, 20, 120, 38]\n\ncatalogo = {p: round(pr * 0.9, 2) for p, pr in zip(productos, precios) if pr > 30}\nprint(catalogo)\n",
            'check' => <<<'PY'
assert isinstance(catalogo, dict), "catalogo debe ser un diccionario."
assert catalogo == {"café": 40.5, "pastel": 108.0, "jugo": 34.2}, f"Obtuviste {catalogo}. ¿Filtraste los que cuestan más de 30?"
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '👑 Clientes VIP',
            'html' => '<p>De la lista <code>clientes</code>, crea <code>vip</code>: una lista con los <b>nombres en mayúsculas</b> de quienes compraron <b>más de 1000</b>.</p>',
            'starter' => "clientes = [\n    {\"nombre\": \"Ana\", \"compras\": 1500},\n    {\"nombre\": \"Luis\", \"compras\": 300},\n    {\"nombre\": \"Carla\", \"compras\": 2200},\n    {\"nombre\": \"Pedro\", \"compras\": 999},\n    {\"nombre\": \"Sofía\", \"compras\": 1001},\n]\n\nvip = \nprint(vip)\n",
            'hint' => '<code>[c["nombre"].upper() for c in clientes if c["compras"] > 1000]</code>',
            'solution' => "clientes = [\n    {\"nombre\": \"Ana\", \"compras\": 1500},\n    {\"nombre\": \"Luis\", \"compras\": 300},\n    {\"nombre\": \"Carla\", \"compras\": 2200},\n    {\"nombre\": \"Pedro\", \"compras\": 999},\n    {\"nombre\": \"Sofía\", \"compras\": 1001},\n]\n\nvip = [c[\"nombre\"].upper() for c in clientes if c[\"compras\"] > 1000]\nprint(vip)\n",
            'check' => <<<'PY'
assert vip == ["ANA", "CARLA", "SOFÍA"], f"Obtuviste {vip}"
PY,
            'success' => '¡Una línea que filtra y transforma! Así se ve el código Python de nivel pro. 😎',
        ],
    ],
];
