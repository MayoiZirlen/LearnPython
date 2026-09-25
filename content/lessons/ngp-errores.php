<?php
return [
    'title' => 'Atrapa los errores (try / except)',
    'icon' => '🛡️',
    'minutes' => 11,
    'summary' => 'Haz que tu código sobreviva a datos rotos sin detenerse.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Cuando los datos atacan',
            'html' => <<<'HTML'
<p>Los datos reales traen sorpresas: <code>"n/a"</code> donde debería haber un número, celdas vacías, precios con <code>"$1,500"</code>… Si haces <code>float("n/a")</code>, Python lanza un <code>ValueError</code> y <b>todo tu programa se detiene</b>.</p>
<p>Con <code>try</code> / <code>except</code> le dices: "<i>intenta esto, y si falla, haz esto otro en lugar de explotar</i>":</p>
<pre>try:
    numero = float(texto)
except ValueError:
    numero = None      # plan B</pre>
<div class="analogy">🛡️ Es como un escudo: el golpe (el error) llega, pero tu personaje sigue en pie.</div>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Pruébalo',
            'html' => '<p>Fíjate que el programa llega hasta el final aunque varios datos estén rotos.</p>',
            'code' => <<<'PY'
datos = ["120", "n/a", "35.5", "", "hola", "7"]

for texto in datos:
    try:
        valor = float(texto)
        print(f"✅ {texto!r} → {valor}")
    except ValueError:
        print(f"⚠️ {texto!r} no es un número, lo salto")

print("¡Terminé sin explotar! 🎉")
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué muestra este código?',
            'code' => "try:\n    x = 10 / 0\n    print(\"A\")\nexcept ZeroDivisionError:\n    print(\"B\")\nprint(\"C\")",
            'options' => ['A y C', 'B y C', 'Solo B', 'A, B y C'],
            'answer' => 1,
            'explain' => 'La división falla antes de llegar a <code>print("A")</code>, salta al <code>except</code> (B) y el programa continúa normalmente (C).',
        ],
        [
            'type' => 'exercise',
            'title' => '🔢 Conversor a prueba de todo',
            'html' => <<<'HTML'
<p>Crea la función <code>a_numero(texto)</code> que:</p>
<ul>
  <li>Quite los signos <code>$</code> y las comas <code>,</code> del texto (usa <code>.replace</code>)</li>
  <li>Intente convertirlo a <code>float</code> y lo devuelva</li>
  <li>Si no se puede, devuelva <code>None</code></li>
</ul>
<pre>a_numero("$1,500")   # 1500.0
a_numero("hola")     # None</pre>
HTML,
            'starter' => "def a_numero(texto):\n    limpio = texto.replace(\"$\", \"\").replace(\",\", \"\")\n    # usa try / except aquí\n\n\nprint(a_numero(\"$1,500\"))\nprint(a_numero(\"hola\"))\n",
            'hint' => ['Dentro de <code>try:</code> haz <code>return float(limpio)</code>.', 'En <code>except ValueError:</code> haz <code>return None</code>.'],
            'solution' => "def a_numero(texto):\n    limpio = texto.replace(\"$\", \"\").replace(\",\", \"\")\n    try:\n        return float(limpio)\n    except ValueError:\n        return None\n\n\nprint(a_numero(\"$1,500\"))\nprint(a_numero(\"hola\"))\n",
            'check' => <<<'PY'
assert "try" in _codigo and "except" in _codigo, "Usa try / except."
casos = {"$1,500": 1500.0, "20.5": 20.5, "$0": 0.0, "hola": None, "": None, "n/a": None, "1,234,567": 1234567.0}
for texto, esperado in casos.items():
    r = a_numero(texto)
    assert r == esperado, f"a_numero({texto!r}) debería dar {esperado} y dio {r}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🧽 Limpia una columna rota',
            'html' => '<p>Usando <code>a_numero</code> (ya está lista) y una <b>comprensión de lista</b>, crea <code>limpios</code> con los valores convertidos, <b>descartando los que den None</b>. Después calcula <code>total</code>.</p>',
            'starter' => "def a_numero(texto):\n    limpio = texto.replace(\"$\", \"\").replace(\",\", \"\")\n    try:\n        return float(limpio)\n    except ValueError:\n        return None\n\ncrudos = [\"120\", \"n/a\", \"35.5\", \"\", \"$1,000\", \"abc\", \"$44.50\"]\n\nlimpios = \ntotal = \nprint(limpios, total)\n",
            'hint' => ['Puedes llamar la función dos veces: <code>[a_numero(x) for x in crudos if a_numero(x) is not None]</code>', 'O primero convierte todo y luego filtra: <code>[v for v in convertidos if v is not None]</code>'],
            'solution' => "def a_numero(texto):\n    limpio = texto.replace(\"$\", \"\").replace(\",\", \"\")\n    try:\n        return float(limpio)\n    except ValueError:\n        return None\n\ncrudos = [\"120\", \"n/a\", \"35.5\", \"\", \"$1,000\", \"abc\", \"$44.50\"]\n\nconvertidos = [a_numero(x) for x in crudos]\nlimpios = [v for v in convertidos if v is not None]\ntotal = sum(limpios)\nprint(limpios, total)\n",
            'check' => <<<'PY'
assert limpios == [120.0, 35.5, 1000.0, 44.5], f"Obtuviste {limpios}"
assert total == 1200.0, f"total debería ser 1200.0 y es {total}"
PY,
        ],
        [
            'type' => 'text',
            'title' => 'Lanzar tus propios errores',
            'html' => <<<'HTML'
<p>A veces <b>tú</b> quieres detener el programa si un dato no tiene sentido (una edad negativa, un precio de cero…). Para eso está <code>raise</code>:</p>
<pre>def validar_precio(p):
    if p &lt;= 0:
        raise ValueError(f"Precio inválido: {p}")
    return p</pre>
<p>Es mejor que falle con un mensaje claro a que un dato basura contamine todo tu análisis sin que nadie lo note. 🕵️</p>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '🚨 Validador de edades',
            'html' => '<p>Crea <code>validar_edad(n)</code> que lance <code>ValueError</code> si la edad es menor que 0 o mayor que 120, y si no, que devuelva la edad.</p>',
            'starter' => "def validar_edad(n):\n    pass\n\n\nprint(validar_edad(30))\n",
            'hint' => '<code>if n &lt; 0 or n > 120: raise ValueError("Edad inválida")</code> y después <code>return n</code>.',
            'solution' => "def validar_edad(n):\n    if n < 0 or n > 120:\n        raise ValueError(f\"Edad inválida: {n}\")\n    return n\n\n\nprint(validar_edad(30))\n",
            'check' => <<<'PY'
for ok in [0, 30, 120]:
    assert validar_edad(ok) == ok, f"validar_edad({ok}) debería devolver {ok}."
for mala in [-1, 121, 500]:
    try:
        validar_edad(mala)
    except ValueError:
        continue
    raise AssertionError(f"validar_edad({mala}) debería lanzar ValueError.")
PY,
        ],
    ],
];
