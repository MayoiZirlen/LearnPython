<?php
return [
    'title' => 'Estadística a mano',
    'icon' => '📐',
    'minutes' => 12,
    'summary' => 'Programa la media, la mediana y el rango tú mismo.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Las medidas que usa todo analista',
            'html' => <<<'HTML'
<p>Antes de usar librerías que lo hacen todo, vas a programar tú mismo las estadísticas básicas. Así entenderás de verdad lo que pasa "por dentro".</p>
<table>
<tr><th>Medida</th><th>Qué es</th><th>Con <code>[2, 3, 3, 5, 12]</code></th></tr>
<tr><td><b>Media</b> (promedio)</td><td>Suma ÷ cantidad</td><td>25 ÷ 5 = <b>5</b></td></tr>
<tr><td><b>Mediana</b></td><td>El valor del medio cuando los ordenas</td><td><b>3</b></td></tr>
<tr><td><b>Rango</b></td><td>Máximo − mínimo</td><td>12 − 2 = <b>10</b></td></tr>
</table>
<p>Si la cantidad de datos es <b>par</b>, no hay un solo valor en el medio: la mediana es el <b>promedio de los dos del centro</b>. Con <code>[1, 3, 5, 7]</code> la mediana es (3 + 5) ÷ 2 = <b>4</b>.</p>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => 'Función media',
            'html' => '<p>Crea <code>media(datos)</code> que reciba una lista de números y devuelva su promedio.</p>',
            'starter' => "def media(datos):\n    pass\n\n\nprint(media([2, 3, 3, 5, 12]))\n",
            'hint' => '<code>return sum(datos) / len(datos)</code>',
            'solution' => "def media(datos):\n    return sum(datos) / len(datos)\n\n\nprint(media([2, 3, 3, 5, 12]))\n",
            'check' => <<<'PY'
for d in [[2, 3, 3, 5, 12], [10], [1.5, 2.5], [100, 200, 300, 400]]:
    r = media(d)
    assert r is not None, "Tu función no devuelve nada. ¿Usaste return?"
    assert abs(r - sum(d) / len(d)) < 1e-9, f"media({d}) debería dar {sum(d)/len(d)} y dio {r}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => 'Función mediana (reto 🌶️)',
            'html' => <<<'HTML'
<p>Crea <code>mediana(datos)</code>. Pasos:</p>
<ol>
  <li>Ordena los datos con <code>sorted()</code>.</li>
  <li>Calcula la posición del medio: <code>mitad = len(ordenados) // 2</code></li>
  <li>Si la cantidad es <b>impar</b>, la mediana es <code>ordenados[mitad]</code>.</li>
  <li>Si es <b>par</b>, es el promedio de <code>ordenados[mitad - 1]</code> y <code>ordenados[mitad]</code>.</li>
</ol>
HTML,
            'starter' => "def mediana(datos):\n    ordenados = sorted(datos)\n    mitad = len(ordenados) // 2\n    # completa aquí\n\n\nprint(mediana([5, 1, 3]))        # 3\nprint(mediana([7, 1, 5, 3]))     # 4.0\n",
            'hint' => ['Para saber si es par: <code>if len(ordenados) % 2 == 0:</code>', 'En el caso par: <code>return (ordenados[mitad - 1] + ordenados[mitad]) / 2</code>'],
            'solution' => "def mediana(datos):\n    ordenados = sorted(datos)\n    mitad = len(ordenados) // 2\n    if len(ordenados) % 2 == 0:\n        return (ordenados[mitad - 1] + ordenados[mitad]) / 2\n    else:\n        return ordenados[mitad]\n\n\nprint(mediana([5, 1, 3]))        # 3\nprint(mediana([7, 1, 5, 3]))     # 4.0\n",
            'check' => <<<'PY'
import statistics
for d in [[5, 1, 3], [7, 1, 5, 3], [10], [4, 2], [9, 2, 7, 4, 1, 8], [3, 3, 3, 100]]:
    r = mediana(d)
    assert r is not None, f"mediana({d}) no devolvió nada. ¿Te falta un return?"
    assert r == statistics.median(d), f"mediana({d}) debería dar {statistics.median(d)} y dio {r}."
PY,
            'success' => '¡Programaste una mediana de verdad! Probamos con listas pares, impares y desordenadas. 🌶️🔥',
        ],
        [
            'type' => 'quiz',
            'question' => 'Los sueldos de un equipo son <code>[10, 11, 12, 12, 13, 150]</code> (miles). El jefe gana 150. ¿Qué medida representa mejor el sueldo "típico"?',
            'options' => ['La media (≈ 34.7)', 'La mediana (12)', 'El rango (140)', 'El máximo (150)'],
            'answer' => 1,
            'explain' => 'Un valor extremo (<b>outlier</b>) "jala" la media hacia arriba, pero casi no afecta a la mediana. Por eso en sueldos o precios de casas se usa mucho la mediana. ¡Tip de analista! 🧠',
        ],
        [
            'type' => 'exercise',
            'title' => '📊 Mini reporte estadístico',
            'html' => '<p>Crea la función <code>resumen(datos)</code> que devuelva un <b>diccionario</b> con las claves <code>"min"</code>, <code>"max"</code>, <code>"rango"</code> y <code>"media"</code> (redondeada a 2 decimales).</p><pre>resumen([2, 3, 3, 5, 12])\n# {"min": 2, "max": 12, "rango": 10, "media": 5.0}</pre>',
            'starter' => "def resumen(datos):\n    return {\n        \"min\": min(datos),\n        # completa las demás claves\n    }\n\n\nprint(resumen([2, 3, 3, 5, 12]))\n",
            'hint' => ['El rango es <code>max(datos) - min(datos)</code>.', 'La media redondeada: <code>round(sum(datos) / len(datos), 2)</code>'],
            'solution' => "def resumen(datos):\n    return {\n        \"min\": min(datos),\n        \"max\": max(datos),\n        \"rango\": max(datos) - min(datos),\n        \"media\": round(sum(datos) / len(datos), 2),\n    }\n\n\nprint(resumen([2, 3, 3, 5, 12]))\n",
            'check' => <<<'PY'
for d in [[2, 3, 3, 5, 12], [7, 1, 9], [1.5, 2.25, 10]]:
    r = resumen(d)
    assert isinstance(r, dict), "resumen debe devolver un diccionario."
    for clave in ["min", "max", "rango", "media"]:
        assert clave in r, f"Falta la clave {clave!r} en el diccionario."
    assert r["min"] == min(d) and r["max"] == max(d), "Revisa min y max."
    assert r["rango"] == max(d) - min(d), "El rango es máximo menos mínimo."
    assert r["media"] == round(sum(d) / len(d), 2), f"La media de {d} redondeada debería ser {round(sum(d)/len(d), 2)}."
PY,
        ],
        [
            'type' => 'text',
            'title' => '🎉 ¡Terminaste los fundamentos!',
            'html' => <<<'HTML'
<p>Lo que acabas de programar es exactamente lo que hacen por dentro herramientas como Excel o pandas. Ahora que entiendes cómo funciona, en el siguiente módulo vas a conocer <b>NumPy</b>, que hace todo esto (y mucho más) en una sola línea y miles de veces más rápido:</p>
<pre>import numpy as np
datos = np.array([2, 3, 3, 5, 12])
datos.mean()       # 5.0
np.median(datos)   # 3.0</pre>
<p>¡Nos vemos en el mundo de los datos! 🚀</p>
HTML,
        ],
    ],
];
