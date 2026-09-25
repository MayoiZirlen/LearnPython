<?php
return [
    'title' => 'Vectorizar o morir',
    'icon' => '🚀',
    'minutes' => 13,
    'summary' => 'np.where, np.select y pd.cut: adiós a los bucles lentos.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'El pecado del bucle',
            'html' => <<<'HTML'
<p>En pandas, recorrer filas con <code>for</code> o usar <code>apply</code> es <b>lento</b>: Python procesa una fila a la vez. Las operaciones <b>vectorizadas</b> trabajan con toda la columna de golpe en código compilado, y suelen ser <b>10 a 100 veces más rápidas</b>.</p>
<table>
<tr><th>En vez de…</th><th>Usa…</th></tr>
<tr><td><code>apply(lambda x: "A" if x &gt; 5 else "B")</code></td><td><code>np.where(cond, "A", "B")</code></td></tr>
<tr><td>varios <code>if / elif</code></td><td><code>np.select([cond1, cond2], ["A", "B"], default="C")</code></td></tr>
<tr><td>clasificar en rangos con ifs</td><td><code>pd.cut(col, bins=[...], labels=[...])</code></td></tr>
</table>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'La carrera',
            'code' => <<<'PY'
import time
import numpy as np
import pandas as pd

df = pd.DataFrame({"x": np.random.default_rng(0).integers(0, 100, 200_000)})

t = time.perf_counter()
a = df["x"].apply(lambda v: "alto" if v > 50 else "bajo")
t_apply = time.perf_counter() - t

t = time.perf_counter()
b = np.where(df["x"] > 50, "alto", "bajo")
t_vec = time.perf_counter() - t

print(f"apply: {t_apply:.3f} s · np.where: {t_vec:.4f} s → {t_apply / t_vec:.0f} veces más rápido")
print("¿Mismo resultado?", (a.values == b).all())
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '⚡ np.where',
            'html' => '<p>Crea la columna <code>nivel</code>: <code>"alto"</code> si el total es 2,000 o más, <code>"normal"</code> si no. <b>Prohibido</b> usar <code>apply</code>, <code>map</code>, <code>for</code> o <code>if</code>.</p>',
            'starter' => "import numpy as np\nimport pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => '',
            'solution' => "import numpy as np\nimport pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nventas[\"nivel\"] = np.where(ventas[\"total\"] >= 2000, \"alto\", \"normal\")\nventas[\"nivel\"].value_counts()\n",
            'check' => <<<'PY'
import re
for _p in ["apply", ".map(", "for ", "if "]:
    assert _p not in _codigo, f"Prohibido usar {_p.strip()} en este reto."
assert (ventas["nivel"] == ["alto" if t >= 2000 else "normal" for t in ventas["total"]]).all(), "La columna nivel no es correcta (≥ 2000 es alto)."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🎚️ np.select: tres categorías',
            'html' => '<p>Crea <code>segmento</code> con <code>np.select</code>: <code>"premium"</code> si el total es ≥ 3,000; <code>"medio"</code> si es ≥ 500; <code>"básico"</code> en otro caso. Guarda en <code>conteo</code> el <code>value_counts()</code>. Mismas prohibiciones que antes.</p>',
            'starter' => "import numpy as np\nimport pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => '',
            'solution' => "import numpy as np\nimport pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\ncondiciones = [ventas[\"total\"] >= 3000, ventas[\"total\"] >= 500]\nventas[\"segmento\"] = np.select(condiciones, [\"premium\", \"medio\"], default=\"básico\")\nconteo = ventas[\"segmento\"].value_counts()\nconteo\n",
            'check' => <<<'PY'
for _p in ["apply", ".map(", "for ", "if "]:
    assert _p not in _codigo, f"Prohibido usar {_p.strip()} en este reto."
assert "select" in _codigo, "Usa np.select."
_e = ["premium" if t >= 3000 else "medio" if t >= 500 else "básico" for t in ventas["total"]]
assert list(ventas["segmento"]) == _e, "segmento no es correcto. ¿Pusiste las condiciones en el orden correcto (de la más exigente a la menos)?"
assert int(conteo["premium"]) == _e.count("premium"), "conteo no coincide."
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'En <code>np.select([total &gt;= 500, total &gt;= 3000], ["medio", "premium"], "básico")</code>, ¿qué segmento recibe una venta de 5,000?',
            'options' => ['premium', 'medio', 'básico', 'ambos'],
            'answer' => 1,
            'explain' => 'np.select usa la <b>primera</b> condición verdadera. 5,000 ≥ 500 ya es verdadero, así que queda "medio". ¡El orden de las condiciones importa!',
        ],
        [
            'type' => 'exercise',
            'title' => '📏 pd.cut: rangos de precio',
            'html' => '<p>Con <code>pd.cut</code>, crea <code>rango</code> clasificando el total en: <code>"&lt;100"</code> (0 a 100), <code>"100-1k"</code> (100 a 1,000), <code>"1k-5k"</code> (1,000 a 5,000) y <code>"&gt;5k"</code> (más de 5,000). Usa <code>bins=[0, 100, 1000, 5000, np.inf]</code>. Guarda en <code>por_rango</code> un diccionario {rango: cantidad de ventas}.</p>',
            'starter' => "import numpy as np\nimport pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => '',
            'solution' => "import numpy as np\nimport pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nventas[\"rango\"] = pd.cut(ventas[\"total\"], bins=[0, 100, 1000, 5000, np.inf], labels=[\"<100\", \"100-1k\", \"1k-5k\", \">5k\"])\npor_rango = ventas[\"rango\"].value_counts().to_dict()\npor_rango\n",
            'check' => <<<'PY'
import numpy as np
_t = ventas["total"]
_e = {"<100": int((_t <= 100).sum()), "100-1k": int(((_t > 100) & (_t <= 1000)).sum()), "1k-5k": int(((_t > 1000) & (_t <= 5000)).sum()), ">5k": int((_t > 5000).sum())}
assert {str(k): int(v) for k, v in por_rango.items()} == _e, f"por_rango debería ser {_e}."
PY,
        ],
    ],
];
