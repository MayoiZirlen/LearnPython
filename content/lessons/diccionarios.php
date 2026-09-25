<?php
return [
    'title' => 'Diccionarios: datos con etiqueta',
    'icon' => '📖',
    'minutes' => 10,
    'summary' => 'Pares clave-valor, conteos y tu primera "tabla".',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Buscar por nombre, no por posición',
            'html' => <<<'HTML'
<p>En una lista buscas por posición (0, 1, 2…). Pero a veces es más natural buscar por <b>nombre</b>. Para eso están los <b>diccionarios</b>: guardan pares <b>clave → valor</b>.</p>
<pre>cliente = {
    "nombre": "Ana",
    "edad": 28,
    "ciudad": "Monterrey"
}
print(cliente["nombre"])   # Ana</pre>
<div class="analogy">📇 Como un diccionario de verdad: buscas la palabra (clave) y encuentras su definición (valor). O como una ficha de contacto.</div>
<p>Se escriben con <b>llaves</b> <code>{ }</code>, cada par es <code>clave: valor</code> y se separan con comas.</p>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Leer, cambiar y agregar',
            'code' => <<<'PY'
precios = {"café": 45, "té": 35, "pan": 20}

print(precios["café"])        # leer
precios["pan"] = 25           # cambiar
precios["jugo"] = 50          # agregar (si la clave no existe, se crea)
print(precios)

print("Productos:", list(precios.keys()))
print("Precios:", list(precios.values()))

# Recorrer clave y valor al mismo tiempo
for producto, precio in precios.items():
    print(f"{producto:>6}: ${precio}")

# .get() evita errores si la clave no existe
print(precios.get("galleta", "No disponible"))
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'Si <code>d = {"a": 1, "b": 2}</code>, ¿qué pasa con <code>d["c"]</code>?',
            'options' => ['Devuelve 0', 'Devuelve None', 'Da un error KeyError', 'Crea la clave "c"'],
            'answer' => 2,
            'explain' => 'Pedir una clave que no existe da <b>KeyError</b>. Si no estás seguro, usa <code>d.get("c")</code>.',
        ],
        [
            'type' => 'exercise',
            'title' => '🏪 Valor del inventario',
            'html' => <<<'HTML'
<p>El diccionario <code>stock</code> dice cuántas unidades hay de cada producto, y <code>precios</code> cuánto cuesta cada uno.</p>
<ol>
  <li>Agrega al <code>stock</code> el producto <code>"monitor"</code> con <code>4</code> unidades.</li>
  <li>Calcula <code>valor_total</code>: la suma de <b>unidades × precio</b> de cada producto.</li>
</ol>
HTML,
            'starter' => "stock = {\"laptop\": 5, \"mouse\": 40, \"teclado\": 15}\nprecios = {\"laptop\": 850, \"mouse\": 25, \"teclado\": 45, \"monitor\": 220}\n\n\nvalor_total = 0\n\n\nprint(\"Valor del inventario:\", valor_total)\n",
            'hint' => ['Para agregar: <code>stock["monitor"] = 4</code>', 'Recorre con <code>for producto, unidades in stock.items():</code> y acumula <code>unidades * precios[producto]</code>.'],
            'solution' => "stock = {\"laptop\": 5, \"mouse\": 40, \"teclado\": 15}\nprecios = {\"laptop\": 850, \"mouse\": 25, \"teclado\": 45, \"monitor\": 220}\n\nstock[\"monitor\"] = 4\n\nvalor_total = 0\nfor producto, unidades in stock.items():\n    valor_total += unidades * precios[producto]\n\nprint(\"Valor del inventario:\", valor_total)\n",
            'check' => <<<'PY'
assert stock.get("monitor") == 4, "Agrega \"monitor\" con 4 unidades al diccionario stock."
assert valor_total == 5*850 + 40*25 + 15*45 + 4*220, f"valor_total debería ser {5*850 + 40*25 + 15*45 + 4*220} y es {valor_total}."
PY,
        ],
        [
            'type' => 'text',
            'title' => 'El truco del conteo',
            'html' => <<<'HTML'
<p>Una de las preguntas más comunes en análisis es <b>"¿cuántas veces aparece cada cosa?"</b>. Con un diccionario es facilísimo:</p>
<pre>votos = ["rojo", "azul", "rojo", "verde", "rojo"]
conteo = {}
for v in votos:
    conteo[v] = conteo.get(v, 0) + 1
print(conteo)   # {'rojo': 3, 'azul': 1, 'verde': 1}</pre>
<p><code>conteo.get(v, 0)</code> significa: "dame el valor de <code>v</code>, y si todavía no existe, dame 0".</p>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '📊 ¿Qué método de pago prefieren?',
            'html' => '<p>Cuenta cuántas veces aparece cada método de pago y guárdalo en el diccionario <code>conteo</code>. Después, guarda en <code>favorito</code> el método más usado.</p>',
            'starter' => "pagos = [\"tarjeta\", \"efectivo\", \"tarjeta\", \"transferencia\", \"tarjeta\",\n         \"efectivo\", \"tarjeta\", \"transferencia\", \"tarjeta\", \"efectivo\"]\n\nconteo = {}\n\n\nprint(conteo)\n",
            'hint' => ['Usa el truco: <code>conteo[p] = conteo.get(p, 0) + 1</code> dentro de un for.', 'Para el favorito: <code>favorito = max(conteo, key=conteo.get)</code> (busca la clave con el valor más alto). ¡O compáralo a ojo y escríbelo!'],
            'solution' => "pagos = [\"tarjeta\", \"efectivo\", \"tarjeta\", \"transferencia\", \"tarjeta\",\n         \"efectivo\", \"tarjeta\", \"transferencia\", \"tarjeta\", \"efectivo\"]\n\nconteo = {}\nfor p in pagos:\n    conteo[p] = conteo.get(p, 0) + 1\n\nfavorito = max(conteo, key=conteo.get)\nprint(conteo)\nprint(\"Favorito:\", favorito)\n",
            'check' => <<<'PY'
assert conteo == {"tarjeta": 5, "efectivo": 3, "transferencia": 2}, f"El conteo no es correcto: {conteo}"
assert favorito == "tarjeta", "El método favorito es el que más aparece."
PY,
        ],
        [
            'type' => 'example',
            'title' => '🌉 Puente a pandas: una lista de diccionarios',
            'html' => '<p>Si cada venta es un diccionario y las juntas en una lista… ¡tienes una <b>tabla</b>! Cada diccionario es una fila y cada clave es una columna. Así es como piensa pandas, la librería que verás más adelante.</p>',
            'code' => <<<'PY'
ventas = [
    {"producto": "Laptop", "region": "Norte", "total": 1700},
    {"producto": "Mouse",  "region": "Sur",   "total": 75},
    {"producto": "Silla",  "region": "Norte", "total": 390},
]

print(f"{'Producto':<10}{'Región':<8}{'Total':>7}")
for v in ventas:
    print(f"{v['producto']:<10}{v['region']:<8}{v['total']:>7}")

total_norte = 0
for v in ventas:
    if v["region"] == "Norte":
        total_norte += v["total"]
print("\nVentas en el Norte:", total_norte)
PY,
        ],
    ],
];
