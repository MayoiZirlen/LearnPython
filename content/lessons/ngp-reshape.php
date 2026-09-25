<?php
return [
    'title' => 'Tablas anchas y largas (melt / pivot)',
    'icon' => '🔄',
    'minutes' => 11,
    'summary' => 'Transforma el formato de Excel al formato que ama pandas y viceversa.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Dos formas de la misma información',
            'html' => <<<'HTML'
<p>En Excel es común el formato <b>ancho</b>: una columna por mes. Es cómodo para leer, pero incómodo para analizar.</p>
<table>
<tr><th>tienda</th><th>Ene</th><th>Feb</th><th>Mar</th></tr>
<tr><td>Centro</td><td>120</td><td>135</td><td>150</td></tr>
<tr><td>Norte</td><td>200</td><td>180</td><td>170</td></tr>
</table>
<p>pandas prefiere el formato <b>largo</b>: una fila por cada combinación.</p>
<table>
<tr><th>tienda</th><th>mes</th><th>ventas</th></tr>
<tr><td>Centro</td><td>Ene</td><td>120</td></tr>
<tr><td>Centro</td><td>Feb</td><td>135</td></tr>
<tr><td>…</td><td>…</td><td>…</td></tr>
</table>
<ul>
  <li><code>melt</code> 🫠 convierte de ancho → largo (se "derrite" la tabla)</li>
  <li><code>pivot</code> / <code>pivot_table</code> convierte de largo → ancho</li>
</ul>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Derritiendo una tabla',
            'code' => <<<'PY'
import pandas as pd

ancho = pd.DataFrame({
    "tienda": ["Centro", "Norte", "Sur"],
    "Ene": [120, 200, 90], "Feb": [135, 180, 110], "Mar": [150, 170, 140],
})
largo = ancho.melt(id_vars="tienda", var_name="mes", value_name="ventas")
print(largo)
print()
# Ahora es fácil agrupar por lo que quieras
largo.groupby("mes", sort=False)["ventas"].sum()
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🫠 De ancho a largo',
            'html' => '<p>Convierte <code>ancho</code> en <code>largo</code> con columnas <code>tienda</code>, <code>mes</code> y <code>ventas</code>. Después calcula <code>mejor_mes</code>: el mes con más ventas totales (agrupa y usa <code>idxmax</code>).</p>',
            'starter' => "import pandas as pd\n\nancho = pd.DataFrame({\n    \"tienda\": [\"Centro\", \"Norte\", \"Sur\", \"Este\"],\n    \"Ene\": [120, 200, 90, 140], \"Feb\": [135, 180, 110, 150],\n    \"Mar\": [150, 170, 140, 90], \"Abr\": [160, 210, 175, 130],\n})\n\n",
            'hint' => ['<code>largo = ancho.melt(id_vars="tienda", var_name="mes", value_name="ventas")</code>', '<code>mejor_mes = largo.groupby("mes")["ventas"].sum().idxmax()</code>'],
            'solution' => "import pandas as pd\n\nancho = pd.DataFrame({\n    \"tienda\": [\"Centro\", \"Norte\", \"Sur\", \"Este\"],\n    \"Ene\": [120, 200, 90, 140], \"Feb\": [135, 180, 110, 150],\n    \"Mar\": [150, 170, 140, 90], \"Abr\": [160, 210, 175, 130],\n})\n\nlargo = ancho.melt(id_vars=\"tienda\", var_name=\"mes\", value_name=\"ventas\")\nmejor_mes = largo.groupby(\"mes\")[\"ventas\"].sum().idxmax()\nprint(\"Mejor mes:\", mejor_mes)\nlargo\n",
            'check' => <<<'PY'
assert largo.shape == (16, 3), f"largo debería tener 16 filas × 3 columnas y tiene {largo.shape}."
assert set(largo.columns) == {"tienda", "mes", "ventas"}, f"Las columnas deben ser tienda, mes y ventas; son {list(largo.columns)}."
assert mejor_mes == "Abr", f"El mejor mes es Abr (675 ventas), no {mejor_mes!r}."
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'Una tabla ancha tiene 5 tiendas y 12 columnas de meses. Después de <code>melt(id_vars="tienda")</code>, ¿cuántas filas tiene?',
            'options' => ['5', '12', '17', '60'],
            'answer' => 3,
            'explain' => 'Una fila por cada combinación tienda × mes: 5 × 12 = <b>60</b>.',
        ],
        [
            'type' => 'exercise',
            'title' => '🌡️ Tabla de clima estilo Excel',
            'html' => '<p><code>clima.csv</code> está en formato largo. Crea <code>tabla</code> en formato ancho con <b>una fila por mes</b> (<code>num_mes</code>) y <b>una columna por ciudad</b>, con la temperatura. Luego guarda en <code>diferencia</code> una Series con (Cancún − CDMX) para cada mes.</p>',
            'starter' => "import pandas as pd\n\nclima = pd.read_csv(\"clima.csv\")\n\n",
            'hint' => ['<code>tabla = clima.pivot(index="num_mes", columns="ciudad", values="temperatura")</code>', '<code>diferencia = tabla["Cancún"] - tabla["CDMX"]</code>'],
            'solution' => "import pandas as pd\n\nclima = pd.read_csv(\"clima.csv\")\n\ntabla = clima.pivot(index=\"num_mes\", columns=\"ciudad\", values=\"temperatura\")\ndiferencia = tabla[\"Cancún\"] - tabla[\"CDMX\"]\nprint(diferencia.round(1))\ntabla\n",
            'check' => <<<'PY'
import pandas as pd
_c = pd.read_csv("clima.csv").pivot(index="num_mes", columns="ciudad", values="temperatura")
assert tabla.shape == (12, 4), f"tabla debería ser de 12 × 4 y es {tabla.shape}."
assert ((diferencia - (_c["Cancún"] - _c["CDMX"])).abs() < 1e-9).all(), "diferencia debe ser Cancún − CDMX."
PY,
            'success' => 'Ahora sabes moverte entre el "formato Excel" y el "formato pandas". ¡Nivel desbloqueado! 🔓',
        ],
    ],
];
