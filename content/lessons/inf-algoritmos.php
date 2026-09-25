<?php
return [
    'title' => 'Algoritmos: la velocidad importa',
    'icon' => '⏱️',
    'minutes' => 15,
    'summary' => 'Búsqueda binaria, conjuntos y por qué un doble for puede tardar horas.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'O grande: cómo crece el tiempo',
            'html' => <<<'HTML'
<p>Un código que tarda 1 segundo con 1,000 filas, ¿cuánto tarda con 1,000,000? Depende de su <b>complejidad</b>:</p>
<table>
<tr><th>Complejidad</th><th>Ejemplo</th><th>1,000 → 1,000,000 filas</th></tr>
<tr><td><b>O(1)</b></td><td><code>x in conjunto</code>, <code>dic[clave]</code></td><td>igual de rápido ⚡</td></tr>
<tr><td><b>O(log n)</b></td><td>búsqueda binaria</td><td>el doble de pasos 🚀</td></tr>
<tr><td><b>O(n)</b></td><td><code>x in lista</code>, un for</td><td>1,000 veces más 🚶</td></tr>
<tr><td><b>O(n²)</b></td><td>un for dentro de otro for</td><td>¡un millón de veces más! 🐢💀</td></tr>
</table>
<p>Regla de oro: si vas a buscar muchas veces "¿está X en esta colección?", usa un <code>set</code> o un <code>dict</code>, no una lista.</p>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Lista vs. conjunto',
            'code' => <<<'PY'
import time

datos = list(range(200_000))
como_set = set(datos)
buscar = list(range(199_000, 200_000))   # 1,000 búsquedas

t = time.perf_counter()
encontrados = sum(1 for x in buscar if x in datos)
lento = time.perf_counter() - t

t = time.perf_counter()
encontrados = sum(1 for x in buscar if x in como_set)
rapido = time.perf_counter() - t

print(f"Lista: {lento:.3f} s · Set: {rapido:.5f} s → {lento / rapido:,.0f} veces más rápido")
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'Tienes 1 millón de clientes y debes revisar, para cada una de 100 mil ventas, si el cliente existe. ¿Qué estructura usas para los clientes?',
            'options' => ['Una lista', 'Un set (o dict)', 'Un string con todos los nombres', 'Da igual'],
            'answer' => 1,
            'explain' => 'Buscar en un <code>set</code> es O(1): 100 mil búsquedas instantáneas. Con una lista serían 100 mil × 1 millón de comparaciones. 🐢',
        ],
        [
            'type' => 'exercise',
            'title' => '🔎 Búsqueda binaria',
            'html' => <<<'HTML'
<p>Implementa <code>busqueda_binaria(lista, objetivo)</code> para una lista <b>ordenada</b>: devuelve la posición del objetivo o <code>-1</code> si no está. Prohibido usar <code>in</code>, <code>.index()</code> o recorrer toda la lista.</p>
<p>Idea: mira el elemento del medio; si es menor que el objetivo, descarta la mitad izquierda; si es mayor, la derecha. Repite. 🎯</p>
HTML,
            'starter' => "def busqueda_binaria(lista, objetivo):\n    izq, der = 0, len(lista) - 1\n    # tu código aquí\n    return -1\n\n",
            'hint' => 'while izq <= der: medio = (izq + der) // 2 …',
            'solution' => "def busqueda_binaria(lista, objetivo):\n    izq, der = 0, len(lista) - 1\n    while izq <= der:\n        medio = (izq + der) // 2\n        if lista[medio] == objetivo:\n            return medio\n        if lista[medio] < objetivo:\n            izq = medio + 1\n        else:\n            der = medio - 1\n    return -1\n\n\nprint(busqueda_binaria([2, 5, 8, 12, 16, 23, 38], 23))\n",
            'check' => <<<'PY'
import random, time
_cuerpo = _codigo.split("def busqueda_binaria", 1)[1].split("\nprint", 1)[0]
assert ".index(" not in _cuerpo and " in lista" not in _cuerpo, "Prohibido usar .index() o 'in lista'."
_l = [2, 5, 8, 12, 16, 23, 38]
for _i, _x in enumerate(_l):
    assert busqueda_binaria(_l, _x) == _i, f"busqueda_binaria(lista, {_x}) debería ser {_i}."
for _x in [1, 3, 40, 13]:
    assert busqueda_binaria(_l, _x) == -1, f"{_x} no está: debería devolver -1."
assert busqueda_binaria([], 5) == -1, "Con lista vacía debe devolver -1."
_grande = list(range(0, 3_000_000, 3))
_t = time.perf_counter()
for _x in random.Random(1).sample(_grande, 2000):
    assert busqueda_binaria(_grande, _x) == _x // 3
assert time.perf_counter() - _t < 1.5, "Demasiado lento: ¿de verdad descartas la mitad en cada paso?"
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '👯 Duplicados a la velocidad de la luz',
            'html' => '<p>Crea <code>duplicados(lista)</code> que devuelva una lista <b>ordenada</b> con los valores que aparecen <b>más de una vez</b>. Debe resolver una lista de 40,000 elementos en menos de medio segundo, así que nada de <code>for</code> dentro de <code>for</code> ni <code>lista.count()</code> dentro de un bucle. 🔥</p>',
            'starter' => "def duplicados(lista):\n    resultado = []\n    for x in lista:\n        if lista.count(x) > 1 and x not in resultado:\n            resultado.append(x)\n    return sorted(resultado)\n\n\nprint(duplicados([3, 1, 3, 7, 1, 9]))\n",
            'hint' => 'Recorre una sola vez con dos sets: vistos y repetidos.',
            'solution' => "def duplicados(lista):\n    vistos, repetidos = set(), set()\n    for x in lista:\n        if x in vistos:\n            repetidos.add(x)\n        vistos.add(x)\n    return sorted(repetidos)\n\n\nprint(duplicados([3, 1, 3, 7, 1, 9]))\n",
            'check' => <<<'PY'
import random, time
assert duplicados([3, 1, 3, 7, 1, 9]) == [1, 3], "Resultado incorrecto para [3, 1, 3, 7, 1, 9]."
assert duplicados([]) == [] and duplicados([1, 2, 3]) == [], "Sin repetidos debe devolver []."
assert duplicados([5, 5, 5, 5]) == [5], "Cada repetido debe aparecer una sola vez."
_r = random.Random(7)
_datos = [_r.randint(0, 60_000) for _ in range(40_000)]
_t = time.perf_counter()
_res = duplicados(_datos)
_dt = time.perf_counter() - _t
from collections import Counter
assert _res == sorted(k for k, n in Counter(_datos).items() if n > 1), "El resultado con la lista grande no es correcto."
assert _dt < 0.5, f"Tardó {_dt:.2f} s: necesitas un algoritmo O(n)."
PY,
            'success' => '⚡ Tu versión es miles de veces más rápida que la del código inicial. Eso separa a un analista junior de un senior.',
        ],
    ],
];
