<?php
return [
    'title' => 'Generadores: datos sin fin',
    'icon' => '♾️',
    'minutes' => 14,
    'summary' => 'yield, generadores infinitos y procesar archivos sin cargarlos en memoria.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Bienvenido/a al Infierno',
            'html' => <<<'HTML'
<div class="warn">🔥 <b>Reglas del Infierno:</b> sin pistas, sin soluciones y solo <b>3 vidas</b> por lección. Cada respuesta incorrecta o revisión fallida cuesta una vida. Si las pierdes todas: <b>GAME OVER</b> y la lección vuelve a empezar. A cambio, todo da <b>XP ×2</b>.</div>
<p>Una lista guarda <b>todos</b> sus elementos en memoria. Con un archivo de 50 millones de filas, eso revienta tu computadora. 💥</p>
<p>Un <b>generador</b> produce los valores <b>uno por uno, cuando se los pides</b>. Se crea con una función que usa <code>yield</code> en vez de <code>return</code>:</p>
<pre>def contar_hasta(n):
    i = 1
    while i &lt;= n:
        yield i        # entrega un valor y se "pausa" aquí
        i += 1

for x in contar_hasta(3):
    print(x)           # 1, 2, 3</pre>
<p>También existen las <b>expresiones generadoras</b>: como una comprensión pero con paréntesis. <code>sum(x * 2 for x in datos)</code> nunca crea la lista completa.</p>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Pausar y continuar',
            'code' => <<<'PY'
def contar_hasta(n):
    print("  (arranca el generador)")
    for i in range(1, n + 1):
        print(f"  (voy a entregar {i})")
        yield i

gen = contar_hasta(3)
print("Creé el generador, pero no ha corrido nada todavía")
print("next →", next(gen))
print("next →", next(gen))
print("El resto:", list(gen))

import sys
lista = [x for x in range(1_000_000)]
generador = (x for x in range(1_000_000))
print(f"\nLista: {sys.getsizeof(lista):,} bytes · Generador: {sys.getsizeof(generador)} bytes")
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué pasa si recorres dos veces el mismo generador?',
            'code' => "gen = (x * 2 for x in range(3))\nprint(list(gen))\nprint(list(gen))",
            'options' => ['[0, 2, 4] y [0, 2, 4]', '[0, 2, 4] y []', 'Error', '[] y [0, 2, 4]'],
            'answer' => 1,
            'explain' => 'Un generador se <b>agota</b>: una vez que entregó todos sus valores, ya no tiene más. Si necesitas recorrerlo de nuevo, crea otro.',
        ],
        [
            'type' => 'exercise',
            'title' => '📦 Procesar por lotes',
            'html' => '<p>Crea el generador <code>lotes(datos, tamano)</code> que entregue listas de <code>tamano</code> elementos (el último lote puede ser más chico). Debe funcionar con cualquier iterable, incluso otro generador.</p><pre>list(lotes(range(10), 4))
# [[0, 1, 2, 3], [4, 5, 6, 7], [8, 9]]</pre>',
            'starter' => "def lotes(datos, tamano):\n    pass\n\n\nprint(list(lotes(range(10), 4)))\n",
            'hint' => 'Acumula en una lista; cuando llegue a <code>tamano</code>, haz <code>yield</code> y vacíala. Al final, entrega lo que sobre.',
            'solution' => "def lotes(datos, tamano):\n    lote = []\n    for x in datos:\n        lote.append(x)\n        if len(lote) == tamano:\n            yield lote\n            lote = []\n    if lote:\n        yield lote\n\n\nprint(list(lotes(range(10), 4)))\n",
            'check' => <<<'PY'
import inspect
assert inspect.isgeneratorfunction(lotes), "lotes debe ser un generador (usa yield)."
assert list(lotes(range(10), 4)) == [[0, 1, 2, 3], [4, 5, 6, 7], [8, 9]], "range(10) en lotes de 4 no da el resultado esperado."
assert list(lotes(range(6), 3)) == [[0, 1, 2], [3, 4, 5]], "Si el total es múltiplo del tamaño, no debe sobrar un lote vacío."
assert list(lotes([], 5)) == [], "Sin datos no debe entregar nada."
assert list(lotes((x for x in "abcde"), 2)) == [["a", "b"], ["c", "d"], ["e"]], "Debe funcionar también con otro generador."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🐚 Fibonacci infinito',
            'html' => '<p>Crea el generador <b>infinito</b> <code>fibonacci()</code> (0, 1, 1, 2, 3, 5, 8…). Usa <code>itertools.islice</code> para guardar en <code>primeros</code> los primeros 20 números, y en <code>primero_grande</code> el primer número de Fibonacci mayor a 1,000,000 (sin crear una lista gigante).</p>',
            'starter' => "import itertools\n\ndef fibonacci():\n    pass\n\n",
            'hint' => 'Recorre el generador con un for y rompe con break cuando pase del millón.',
            'solution' => "import itertools\n\ndef fibonacci():\n    a, b = 0, 1\n    while True:\n        yield a\n        a, b = b, a + b\n\nprimeros = list(itertools.islice(fibonacci(), 20))\nprimero_grande = next(x for x in fibonacci() if x > 1_000_000)\nprint(primeros)\nprint(primero_grande)\n",
            'check' => <<<'PY'
import inspect
assert inspect.isgeneratorfunction(fibonacci), "fibonacci debe ser un generador."
assert primeros == [0, 1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89, 144, 233, 377, 610, 987, 1597, 2584, 4181], "Los primeros 20 números no son correctos."
assert primero_grande == 1346269, f"El primer Fibonacci mayor a un millón es 1346269, no {primero_grande}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🌊 Un CSV sin pandas',
            'html' => '<p>Sin pandas y sin crear listas: usando <code>csv.DictReader</code> y una <b>expresión generadora</b>, calcula <code>total_norte</code>: la suma de unidades × precio_unitario de las ventas de la región Norte en <code>ventas.csv</code>.</p>',
            'starter' => "import csv\n\nwith open(\"ventas.csv\", encoding=\"utf-8\") as f:\n    filas = csv.DictReader(f)\n    total_norte = 0\n\nprint(round(total_norte, 2))\n",
            'hint' => '<code>sum(float(r["unidades"]) * float(r["precio_unitario"]) for r in filas if r["region"] == "Norte")</code>',
            'solution' => "import csv\n\nwith open(\"ventas.csv\", encoding=\"utf-8\") as f:\n    filas = csv.DictReader(f)\n    total_norte = sum(float(r[\"unidades\"]) * float(r[\"precio_unitario\"]) for r in filas if r[\"region\"] == \"Norte\")\n\nprint(round(total_norte, 2))\n",
            'check' => <<<'PY'
import pandas as _pd
_v = _pd.read_csv("ventas.csv")
_e = float((_v[_v["region"] == "Norte"]["unidades"] * _v[_v["region"] == "Norte"]["precio_unitario"]).sum())
assert "pandas" not in _codigo and "pd." not in _codigo, "¡Sin pandas en este reto!"
import re as _re
_resto = _codigo.split("filas =", 1)[1]
assert not _re.search(r"(sum|max|min)\(\s*\[", _resto) and "list(" not in _resto and ".append" not in _resto, "No crees listas: usa una expresión generadora con paréntesis."
assert abs(total_norte - _e) < 0.01, f"total_norte debería ser {_e:,.2f}."
PY,
            'success' => '🔥 Leíste un CSV fila por fila sin cargarlo completo. Así se procesan archivos de gigabytes.',
        ],
    ],
];
