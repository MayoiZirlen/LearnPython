<?php
return [
    'title' => 'Bucles: repite sin cansarte',
    'icon' => '🔁',
    'minutes' => 10,
    'summary' => 'for, range y el patrón acumulador para procesar datos.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'El bucle for',
            'html' => <<<'HTML'
<p>Imagina que tienes 10,000 ventas y quieres revisar cada una. ¿Escribir 10,000 líneas? ¡No! Un <b>bucle</b> repite instrucciones por ti.</p>
<pre>frutas = ["manzana", "pera", "uva"]

for fruta in frutas:
    print("Me gusta la", fruta)</pre>
<p>Se lee: <i>"<b>para cada</b> fruta <b>en</b> la lista frutas, haz esto"</i>. En cada vuelta, la variable <code>fruta</code> toma el siguiente valor de la lista.</p>
<div class="analogy">🎢 Es como una fila en la montaña rusa: cada persona (elemento) se sube, da la vuelta (se ejecuta el bloque) y pasa la siguiente.</div>
<p>Si solo quieres repetir algo <b>N veces</b>, usa <code>range(N)</code>, que genera los números 0, 1, 2, …, N-1:</p>
<pre>for i in range(3):
    print("Vuelta", i)   # Vuelta 0, Vuelta 1, Vuelta 2</pre>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Bucles en acción',
            'code' => <<<'PY'
frutas = ["manzana", "pera", "uva"]
for fruta in frutas:
    print("Me gusta la", fruta.upper())

print("---")
for i in range(1, 6):
    print(f"{i} x 7 = {i * 7}")

print("---")
for letra in "Python":
    print(letra, end=" ")
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Cuántas veces se muestra "Hola"?',
            'code' => "for i in range(4):\n    print(\"Hola\")",
            'options' => ['3', '4', '5', 'Infinitas'],
            'answer' => 1,
            'explain' => '<code>range(4)</code> genera 0, 1, 2, 3 → son <b>4</b> vueltas.',
        ],
        [
            'type' => 'text',
            'title' => 'El patrón acumulador',
            'html' => <<<'HTML'
<p>Este es <b>el patrón más importante</b> para procesar datos. Empiezas con una variable en 0 y en cada vuelta le vas sumando:</p>
<pre>ventas = [120, 340, 90, 410]
total = 0                  # 1. empieza en cero

for venta in ventas:
    total = total + venta  # 2. acumula en cada vuelta

print(total)               # 3. usa el resultado → 960</pre>
<p>Y combinándolo con <code>if</code> puedes <b>contar</b> o <b>filtrar</b>:</p>
<pre>ventas_grandes = 0
for venta in ventas:
    if venta > 200:
        ventas_grandes += 1   # += 1 es lo mismo que = ventas_grandes + 1
print(ventas_grandes)          # 2</pre>
<div class="tip">💡 <code>sum()</code> hace lo mismo que el primer ejemplo, pero entender el patrón te permite hacer cálculos que no traen las funciones de Python.</div>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '🌡️ Días de calor',
            'html' => '<p>Tienes las temperaturas máximas de dos semanas. Usando un <code>for</code> y un <code>if</code>, cuenta cuántos días pasaron de <b>30 grados</b> (mayor que 30) y guárdalo en <code>dias_calurosos</code>.</p>',
            'starter' => "temperaturas = [28, 31, 33, 29, 35, 30, 27, 32, 34, 26, 31, 29, 36, 30]\n\ndias_calurosos = 0\n\n\nprint(\"Días calurosos:\", dias_calurosos)\n",
            'hint' => ['Recorre la lista: <code>for t in temperaturas:</code>', 'Dentro del for: <code>if t > 30:</code> y luego <code>dias_calurosos += 1</code> (con doble sangría).'],
            'solution' => "temperaturas = [28, 31, 33, 29, 35, 30, 27, 32, 34, 26, 31, 29, 36, 30]\n\ndias_calurosos = 0\nfor t in temperaturas:\n    if t > 30:\n        dias_calurosos += 1\n\nprint(\"Días calurosos:\", dias_calurosos)\n",
            'check' => <<<'PY'
assert "for" in _codigo, "Usa un bucle for para recorrer las temperaturas."
assert dias_calurosos != 9, "Casi: 30 grados no cuenta, tiene que ser MAYOR que 30 (usa > y no >=)."
assert dias_calurosos == 7, f"Obtuviste {dias_calurosos}, pero hay 7 días con más de 30 grados."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🧾 Precios con IVA',
            'html' => '<p>Crea una lista nueva <code>precios_con_iva</code> con cada precio multiplicado por <code>1.16</code> y <b>redondeado a 2 decimales</b>. Empieza con una lista vacía <code>[]</code> y ve agregando con <code>.append()</code> dentro del bucle.</p>',
            'starter' => "precios = [100, 250, 49.9, 1200]\n\nprecios_con_iva = []\n\n\nprint(precios_con_iva)\n",
            'hint' => ['<code>for p in precios:</code>', 'Dentro: <code>precios_con_iva.append(round(p * 1.16, 2))</code>'],
            'solution' => "precios = [100, 250, 49.9, 1200]\n\nprecios_con_iva = []\nfor p in precios:\n    precios_con_iva.append(round(p * 1.16, 2))\n\nprint(precios_con_iva)\n",
            'check' => <<<'PY'
esperado = [116.0, 290.0, 57.88, 1392.0]
assert isinstance(precios_con_iva, list) and len(precios_con_iva) == 4, "precios_con_iva debe ser una lista con 4 precios."
assert all(abs(a - b) < 0.006 for a, b in zip(precios_con_iva, esperado)), f"Deberías obtener {esperado} y obtuviste {precios_con_iva}."
PY,
            'success' => 'Esto de "aplicar una operación a cada elemento" es tan común que NumPy y pandas lo hacen en una sola línea. ¡Ya lo verás! 🚀',
        ],
        [
            'type' => 'text',
            'title' => 'Bonus: el bucle while',
            'html' => <<<'HTML'
<p><code>while</code> (mientras) repite <b>mientras</b> una condición sea verdadera. Útil cuando no sabes cuántas vueltas darás:</p>
<pre>ahorro = 0
meses = 0
while ahorro &lt; 10000:
    ahorro += 1500
    meses += 1
print(f"Llegas a la meta en {meses} meses")</pre>
<div class="warn">⚠️ Si la condición nunca se vuelve falsa, tienes un <b>bucle infinito</b>. Tranquilidad: aquí, si tu código tarda más de 12 segundos, lo detenemos por ti.</div>
HTML,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué valor tiene <code>total</code> al final?',
            'code' => "total = 0\nfor n in [2, 4, 6]:\n    total += n\nprint(total)",
            'options' => ['6', '12', '0', '[2, 4, 6]'],
            'answer' => 1,
            'explain' => 'Vuelta 1: 0+2=2. Vuelta 2: 2+4=6. Vuelta 3: 6+6=<b>12</b>. ¡Es el patrón acumulador!',
        ],
    ],
];
