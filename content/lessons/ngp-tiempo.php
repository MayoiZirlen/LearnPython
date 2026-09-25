<?php
return [
    'title' => 'Series de tiempo',
    'icon' => '⏳',
    'minutes' => 13,
    'summary' => 'resample, crecimiento % y promedios móviles para ver tendencias.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'El tiempo es el eje de todo',
            'html' => <<<'HTML'
<p>Casi todas las preguntas de negocio tienen que ver con el tiempo: <i>¿vendimos más que el mes pasado? ¿Cuál es la tendencia?</i> pandas tiene herramientas especiales cuando el <b>índice</b> de la tabla son fechas:</p>
<table>
<tr><th>Herramienta</th><th>Para qué</th></tr>
<tr><td><code>.resample("ME").sum()</code></td><td>Agrupar por periodo (ME = fin de mes, W = semana, QE = trimestre, D = día)</td></tr>
<tr><td><code>.pct_change()</code></td><td>Cambio porcentual respecto al periodo anterior</td></tr>
<tr><td><code>.rolling(3).mean()</code></td><td>Promedio móvil: suaviza los altibajos</td></tr>
<tr><td><code>.cumsum()</code></td><td>Acumulado (ventas del año hasta la fecha)</td></tr>
</table>
<p>Primero hay que leer la fecha como fecha y ponerla de índice:</p>
<pre>ventas = pd.read_csv("ventas.csv", parse_dates=["fecha"])
serie = ventas.set_index("fecha")["unidades"]</pre>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'De diario a mensual',
            'code' => <<<'PY'
import pandas as pd

ventas = pd.read_csv("ventas.csv", parse_dates=["fecha"])
ventas["total"] = ventas["unidades"] * ventas["precio_unitario"]
serie = ventas.set_index("fecha")["total"]

print("Por trimestre:")
print(serie.resample("QE").sum().round(2))
print("\nAcumulado del año (primeros 5 meses):")
print(serie.resample("ME").sum().cumsum().round(2).head())
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📆 Ventas mensuales',
            'html' => '<p>Crea la Series <code>mensual</code> con la suma de <code>total</code> por mes usando <code>resample("ME")</code>.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\", parse_dates=[\"fecha\"])\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nmensual = \nmensual\n",
            'hint' => '<code>mensual = ventas.set_index("fecha")["total"].resample("ME").sum()</code>',
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\", parse_dates=[\"fecha\"])\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nmensual = ventas.set_index(\"fecha\")[\"total\"].resample(\"ME\").sum()\nmensual\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_t = (_v["unidades"] * _v["precio_unitario"]).sum()
assert len(mensual) == 12, f"mensual debería tener 12 meses y tiene {len(mensual)}."
assert abs(mensual.sum() - _t) < 0.01, "La suma de los meses no coincide con el total."
assert "resample" in _codigo, "Usa resample(\"ME\")."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📈 Crecimiento mes a mes',
            'html' => '<p>Con <code>mensual</code> ya calculada, crea <code>crecimiento</code>: el cambio porcentual respecto al mes anterior (× 100, redondeado a 1 decimal). Después guarda en <code>mejor_mes</code> el <b>número de mes</b> con el mayor crecimiento (pista: <code>.idxmax().month</code>).</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\", parse_dates=[\"fecha\"])\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\nmensual = ventas.set_index(\"fecha\")[\"total\"].resample(\"ME\").sum()\n\n",
            'hint' => ['<code>crecimiento = (mensual.pct_change() * 100).round(1)</code>', '<code>mejor_mes = crecimiento.idxmax().month</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\", parse_dates=[\"fecha\"])\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\nmensual = ventas.set_index(\"fecha\")[\"total\"].resample(\"ME\").sum()\n\ncrecimiento = (mensual.pct_change() * 100).round(1)\nmejor_mes = crecimiento.idxmax().month\nprint(crecimiento)\nprint(\"Mes con mayor crecimiento:\", mejor_mes)\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv", parse_dates=["fecha"])
_m = (_v["unidades"] * _v["precio_unitario"]).groupby(_v["fecha"].dt.month).sum()
_c = _m.pct_change() * 100
assert len(crecimiento) == 12, "crecimiento debe tener 12 valores (el primero queda vacío)."
assert pd.isna(crecimiento.iloc[0]), "El primer mes no tiene mes anterior: debería quedar NaN."
assert abs(float(crecimiento.iloc[5]) - round(_c.iloc[5], 1)) < 0.051, "Los porcentajes no coinciden. ¿Multiplicaste por 100?"
assert int(mejor_mes) == int(_c.idxmax()), f"El mes con mayor crecimiento es el {int(_c.idxmax())}."
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'Un promedio móvil de 3 meses (<code>rolling(3).mean()</code>) en marzo es…',
            'options' => ['El promedio de todo el año', 'El promedio de enero, febrero y marzo', 'Las ventas de marzo × 3', 'El promedio de marzo, abril y mayo'],
            'answer' => 1,
            'explain' => 'Toma la <b>ventana</b> de los últimos 3 valores hasta ese punto. Por eso los dos primeros meses quedan vacíos (no tienen 3 meses de historia).',
        ],
        [
            'type' => 'exercise',
            'title' => '〰️ Suaviza la tendencia',
            'html' => '<p>Crea <code>movil</code>: el promedio móvil de 3 meses de <code>mensual</code>. Luego grafica <code>mensual</code> y <code>movil</code> en el mismo gráfico (dos <code>.plot()</code> seguidos, con <code>label=</code>) y termina con <code>plt.legend()</code> y <code>plt.show()</code>.</p>',
            'starter' => "import pandas as pd\nimport matplotlib.pyplot as plt\n\nventas = pd.read_csv(\"ventas.csv\", parse_dates=[\"fecha\"])\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\nmensual = ventas.set_index(\"fecha\")[\"total\"].resample(\"ME\").sum()\n\n",
            'hint' => ['<code>movil = mensual.rolling(3).mean()</code>', '<code>mensual.plot(label="Mensual", marker="o")</code> y <code>movil.plot(label="Promedio móvil 3M", linewidth=3)</code>'],
            'solution' => "import pandas as pd\nimport matplotlib.pyplot as plt\n\nventas = pd.read_csv(\"ventas.csv\", parse_dates=[\"fecha\"])\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\nmensual = ventas.set_index(\"fecha\")[\"total\"].resample(\"ME\").sum()\n\nmovil = mensual.rolling(3).mean()\nmensual.plot(label=\"Mensual\", marker=\"o\", figsize=(9, 4))\nmovil.plot(label=\"Promedio móvil 3M\", linewidth=3)\nplt.title(\"Ventas y tendencia\")\nplt.legend()\nplt.show()\n",
            'check' => <<<'PY'
assert movil.isna().sum() == 2, "Los primeros 2 meses del promedio móvil deben quedar vacíos. ¿Usaste rolling(3)?"
assert abs(movil.iloc[2] - mensual.iloc[:3].mean()) < 0.01, "movil debe ser el promedio de los últimos 3 meses."
assert _imagenes >= 1, "Falta el gráfico."
assert "legend(" in _codigo, "Agrega plt.legend() para distinguir las líneas."
PY,
            'success' => 'Con el promedio móvil se ve la tendencia real, sin el "ruido" de cada mes. 📉📈',
        ],
    ],
];
