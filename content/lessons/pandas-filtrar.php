<?php
return [
    'title' => 'Seleccionar, filtrar y ordenar',
    'icon' => '🔎',
    'minutes' => 12,
    'summary' => 'Quédate solo con las filas y columnas que te importan.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Elegir columnas',
            'html' => <<<'HTML'
<p>Con un nombre obtienes una columna; con una <b>lista</b> de nombres (doble corchete) obtienes una tabla con esas columnas:</p>
<pre>ventas["producto"]                      # una columna (Series)
ventas[["producto", "unidades"]]        # tabla con 2 columnas (DataFrame)</pre>
<p>Y puedes crear columnas calculadas. La más importante en ventas es el <b>total</b>:</p>
<pre>ventas["total"] = ventas["unidades"] * ventas["precio_unitario"]</pre>
HTML,
        ],
        [
            'type' => 'text',
            'title' => 'Filtrar filas',
            'html' => <<<'HTML'
<p>¡Aquí vuelven las máscaras booleanas de NumPy! Escribe la condición dentro de los corchetes:</p>
<pre>ventas[ventas["region"] == "Norte"]            # solo el Norte
ventas[ventas["unidades"] > 10]                 # más de 10 unidades
ventas[(ventas["region"] == "Norte") &amp; (ventas["unidades"] > 10)]   # las dos cosas
ventas[ventas["producto"].isin(["Laptop", "Monitor"])]                 # varios valores</pre>
<div class="warn">⚠️ En pandas usa <code>&amp;</code> (y), <code>|</code> (o) y <code>~</code> (no) en vez de <code>and</code>, <code>or</code>, <code>not</code>. Y pon <b>cada condición entre paréntesis</b>.</div>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Pruébalo',
            'code' => <<<'PY'
import pandas as pd

ventas = pd.read_csv("ventas.csv")
ventas["total"] = ventas["unidades"] * ventas["precio_unitario"]

laptops = ventas[ventas["producto"] == "Laptop"]
print("Ventas de laptops:", len(laptops))
print("Ingreso por laptops:", round(laptops["total"].sum(), 2))

# Ventas grandes en el Sur, solo algunas columnas
grandes_sur = ventas[(ventas["region"] == "Sur") & (ventas["total"] > 2000)]
grandes_sur[["fecha", "producto", "vendedor", "total"]]
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Cuál filtro está <b>bien escrito</b> para obtener ventas del Norte con más de 5 unidades?',
            'options' => [
                'ventas[ventas["region"] == "Norte" and ventas["unidades"] > 5]',
                'ventas[(ventas["region"] == "Norte") & (ventas["unidades"] > 5)]',
                'ventas["region" == "Norte" & "unidades" > 5]',
                'ventas[region == "Norte"] & ventas[unidades > 5]',
            ],
            'answer' => 1,
            'explain' => 'En pandas se usa <code>&amp;</code> y cada condición va entre paréntesis.',
            'wrong' => [0 => '<code>and</code> no funciona con columnas completas de pandas; usa <code>&amp;</code>.'],
        ],
        [
            'type' => 'exercise',
            'title' => '💻 Solo tecnología',
            'html' => '<p>Crea el DataFrame <code>tecnologia</code> con las ventas cuya <code>categoria</code> sea <code>"Tecnología"</code> (con acento). Luego guarda en <code>num_ventas_tec</code> cuántas filas tiene.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\n",
            'hint' => ['<code>tecnologia = ventas[ventas["categoria"] == "Tecnología"]</code>', '<code>num_ventas_tec = len(tecnologia)</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\ntecnologia = ventas[ventas[\"categoria\"] == \"Tecnología\"]\nnum_ventas_tec = len(tecnologia)\nprint(num_ventas_tec)\ntecnologia.head()\n",
            'check' => <<<'PY'
import pandas as pd
assert isinstance(tecnologia, pd.DataFrame), "tecnologia debe ser un DataFrame (una tabla filtrada)."
assert (tecnologia["categoria"] == "Tecnología").all(), "tecnologia tiene filas que no son de Tecnología."
assert len(tecnologia) == 116, f"Deberían ser 116 ventas de tecnología, tienes {len(tecnologia)}."
assert num_ventas_tec == 116, "num_ventas_tec debe ser la cantidad de filas."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📦 Pedidos grandes del Norte',
            'html' => '<p>Crea <code>grandes_norte</code> con las ventas de la región <code>"Norte"</code> <b>y</b> con <b>10 o más</b> unidades.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\ngrandes_norte = \ngrandes_norte\n",
            'hint' => 'Dos condiciones entre paréntesis unidas con <code>&amp;</code>: <code>(ventas["region"] == "Norte") &amp; (ventas["unidades"] >= 10)</code>',
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\ngrandes_norte = ventas[(ventas[\"region\"] == \"Norte\") & (ventas[\"unidades\"] >= 10)]\ngrandes_norte\n",
            'check' => <<<'PY'
assert (grandes_norte["region"] == "Norte").all(), "Hay filas que no son del Norte."
assert (grandes_norte["unidades"] >= 10).all(), "Hay filas con menos de 10 unidades."
assert len(grandes_norte) == 36, f"Deberían ser 36 ventas; tienes {len(grandes_norte)}. ¿Usaste >= 10?"
PY,
        ],
        [
            'type' => 'text',
            'title' => 'Ordenar y encontrar los "top"',
            'html' => <<<'HTML'
<pre>ventas.sort_values("total")                      # de menor a mayor
ventas.sort_values("total", ascending=False)     # de mayor a menor
ventas.sort_values("total", ascending=False).head(5)   # top 5
ventas.nlargest(5, "total")                      # atajo para el top 5</pre>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '🏆 Top 5 ventas',
            'html' => '<p>Crea la columna <code>total</code> (unidades × precio_unitario) y luego el DataFrame <code>top5</code> con las <b>5 ventas de mayor total</b>, ordenadas de mayor a menor.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\n",
            'hint' => ['<code>ventas["total"] = ventas["unidades"] * ventas["precio_unitario"]</code>', '<code>top5 = ventas.sort_values("total", ascending=False).head(5)</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\ntop5 = ventas.sort_values(\"total\", ascending=False).head(5)\ntop5\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_v["total"] = _v["unidades"] * _v["precio_unitario"]
assert "total" in ventas.columns, "Primero crea la columna total en ventas."
assert len(top5) == 5, "top5 debe tener exactamente 5 filas."
assert list(top5["id"]) == list(_v.nlargest(5, "total")["id"]), "Esas no son las 5 ventas más grandes (o no están ordenadas de mayor a menor)."
PY,
            'success' => '¿Notas qué producto domina el top? Eso ya es un <b>insight</b>. 💡',
        ],
    ],
];
