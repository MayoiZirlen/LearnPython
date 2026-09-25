<?php
return [
    'title' => 'Gráficos directo desde pandas',
    'icon' => '🖼️',
    'minutes' => 11,
    'summary' => 'De tabla a gráfico en una línea con .plot().',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'pandas también dibuja',
            'html' => <<<'HTML'
<p>Las Series y DataFrames tienen el método <code>.plot()</code>, que usa matplotlib por debajo. Así pasas de un <code>groupby</code> a un gráfico en <b>una sola línea</b>:</p>
<pre>ventas.groupby("region")["total"].sum().plot(kind="bar")</pre>
<p>El parámetro <code>kind</code> elige el tipo: <code>"line"</code>, <code>"bar"</code>, <code>"barh"</code>, <code>"hist"</code>, <code>"pie"</code>, <code>"scatter"</code>…</p>
<p>En esta lección usaremos también <code>clima.csv</code>: temperatura y lluvia mensual de 4 ciudades.</p>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Del groupby al gráfico',
            'code' => <<<'PY'
import pandas as pd
import matplotlib.pyplot as plt

ventas = pd.read_csv("ventas.csv")
ventas["total"] = ventas["unidades"] * ventas["precio_unitario"]

por_producto = ventas.groupby("producto")["total"].sum().sort_values()
por_producto.plot(kind="barh", color="tab:purple", figsize=(8, 4), title="Ingresos por producto")
plt.xlabel("Ingresos ($)")
plt.show()
PY,
        ],
        [
            'type' => 'example',
            'title' => 'Varias líneas: pivot',
            'html' => '<p>Para comparar ciudades en el tiempo, <code>pivot</code> reacomoda la tabla: los meses como filas y cada ciudad como columna. ¡Y cada columna se convierte en una línea!</p>',
            'code' => <<<'PY'
import pandas as pd
import matplotlib.pyplot as plt

clima = pd.read_csv("clima.csv")
tabla = clima.pivot(index="num_mes", columns="ciudad", values="temperatura")
print(tabla.head(3))

tabla.plot(marker="o", figsize=(9, 4), title="Temperatura promedio por mes")
plt.xlabel("Mes")
plt.ylabel("°C")
plt.show()
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'Después de <code>clima.pivot(index="num_mes", columns="ciudad", values="lluvia_mm")</code>, ¿cuántas columnas tiene la tabla?',
            'options' => ['1', '4 (una por ciudad)', '12 (una por mes)', '48'],
            'answer' => 1,
            'explain' => 'El parámetro <code>columns="ciudad"</code> convierte cada ciudad distinta en una columna. Hay 4 ciudades.',
        ],
        [
            'type' => 'exercise',
            'title' => '🗺️ Ingresos por región',
            'html' => '<p>Crea la Series <code>por_region</code> con la suma del total por región, ordenada de mayor a menor, y grafícala como barras con <code>.plot(kind="bar")</code>. Agrega un título y termina con <code>plt.show()</code>.</p>',
            'starter' => "import pandas as pd\nimport matplotlib.pyplot as plt\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => ['<code>por_region = ventas.groupby("region")["total"].sum().sort_values(ascending=False)</code>', '<code>por_region.plot(kind="bar", title="Ingresos por región")</code> y luego <code>plt.show()</code>'],
            'solution' => "import pandas as pd\nimport matplotlib.pyplot as plt\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\npor_region = ventas.groupby(\"region\")[\"total\"].sum().sort_values(ascending=False)\npor_region.plot(kind=\"bar\", color=\"tab:green\", title=\"Ingresos por región\")\nplt.ylabel(\"Ingresos ($)\")\nplt.show()\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_v["total"] = _v["unidades"] * _v["precio_unitario"]
_e = _v.groupby("region")["total"].sum().sort_values(ascending=False)
assert list(por_region.index) == list(_e.index), "por_region debe estar ordenada de mayor a menor."
assert ((por_region - _e).abs() < 0.01).all(), "Los totales por región no coinciden."
assert _imagenes >= 1, "No se generó el gráfico. ¿Usaste .plot() y plt.show()?"
assert "title" in _codigo, "Agrega un título al gráfico."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🌧️ ¿Dónde llueve más?',
            'html' => '<p>Con <code>clima.csv</code>, calcula <code>lluvia_total</code>: la suma de <code>lluvia_mm</code> por ciudad. Guarda en <code>ciudad_lluviosa</code> el nombre de la ciudad con más lluvia y haz un gráfico de barras de <code>lluvia_total</code>.</p>',
            'starter' => "import pandas as pd\nimport matplotlib.pyplot as plt\n\nclima = pd.read_csv(\"clima.csv\")\n\n",
            'hint' => ['<code>lluvia_total = clima.groupby("ciudad")["lluvia_mm"].sum()</code>', '<code>ciudad_lluviosa = lluvia_total.idxmax()</code>'],
            'solution' => "import pandas as pd\nimport matplotlib.pyplot as plt\n\nclima = pd.read_csv(\"clima.csv\")\n\nlluvia_total = clima.groupby(\"ciudad\")[\"lluvia_mm\"].sum()\nciudad_lluviosa = lluvia_total.idxmax()\nlluvia_total.plot(kind=\"bar\", color=\"tab:cyan\", title=\"Lluvia anual (mm)\")\nplt.show()\nprint(\"La ciudad más lluviosa es\", ciudad_lluviosa)\n",
            'check' => <<<'PY'
import pandas as pd
_c = pd.read_csv("clima.csv").groupby("ciudad")["lluvia_mm"].sum()
assert ((lluvia_total.sort_index() - _c).abs() < 0.01).all(), "lluvia_total debe ser la suma de lluvia_mm por ciudad."
assert ciudad_lluviosa == _c.idxmax(), f"La ciudad más lluviosa no es {ciudad_lluviosa!r}."
assert _imagenes >= 1, "Falta el gráfico."
PY,
        ],
    ],
];
