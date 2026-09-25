<?php
return [
    'title' => 'transform, rank y top-N por grupo',
    'icon' => '🏅',
    'minutes' => 15,
    'summary' => 'Participación dentro del grupo, rankings y los mejores de cada región.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Más allá del groupby().sum()',
            'html' => <<<'HTML'
<p><code>groupby().sum()</code> <b>reduce</b> la tabla (una fila por grupo). A veces necesitas el resultado del grupo <b>en cada fila original</b>. Para eso está <code>transform</code>:</p>
<pre># total de la región, repetido en cada venta de esa región
df["total_region"] = df.groupby("region")["total"].transform("sum")
df["pct_region"] = df["total"] / df["total_region"] * 100</pre>
<p>Otras armas del arsenal:</p>
<ul>
  <li><code>.rank(ascending=False)</code> → puesto de cada valor (1 = el mayor)</li>
  <li><code>groupby(...)[col].rank(...)</code> → puesto <b>dentro de su grupo</b></li>
  <li><code>df.sort_values(...).groupby(...).head(n)</code> → los <b>n mejores de cada grupo</b></li>
  <li><code>.unstack()</code> → pasa un nivel del índice a columnas (tabla cruzada)</li>
</ul>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'transform en acción',
            'code' => <<<'PY'
import pandas as pd

ventas = pd.read_csv("ventas.csv")
ventas["total"] = ventas["unidades"] * ventas["precio_unitario"]
ventas["prom_producto"] = ventas.groupby("producto")["total"].transform("mean")
ventas["vs_promedio"] = (ventas["total"] / ventas["prom_producto"]).round(2)
ventas[["producto", "total", "prom_producto", "vs_promedio"]].head(8)
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🥧 Participación dentro de la región',
            'html' => '<p>Crea la columna <code>pct_region</code>: qué porcentaje representa cada venta del total de <b>su</b> región. Usa <code>transform</code>. (Las ventas de cada región deben sumar 100.)</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => '',
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nventas[\"pct_region\"] = ventas[\"total\"] / ventas.groupby(\"region\")[\"total\"].transform(\"sum\") * 100\nventas.groupby(\"region\")[\"pct_region\"].sum()\n",
            'check' => <<<'PY'
assert "transform" in _codigo, "Usa transform."
assert len(ventas) == 300, "No cambies el número de filas: transform mantiene una fila por venta."
_s = ventas.groupby("region")["pct_region"].sum()
assert ((_s - 100).abs() < 1e-6).all(), f"Las ventas de cada región deben sumar 100%: {_s.round(2).to_dict()}"
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🏆 Ranking de vendedores por región',
            'html' => '<p>Crea <code>tabla</code> con el total por <code>region</code> y <code>vendedor</code> (con <code>as_index=False</code>) y agrega la columna <code>puesto</code> (entero) con el lugar de cada vendedor <b>dentro de su región</b> (1 = el que más vendió).</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => '',
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\ntabla = ventas.groupby([\"region\", \"vendedor\"], as_index=False)[\"total\"].sum()\ntabla[\"puesto\"] = tabla.groupby(\"region\")[\"total\"].rank(ascending=False).astype(int)\ntabla.sort_values([\"region\", \"puesto\"])\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_v["t"] = _v["unidades"] * _v["precio_unitario"]
_g = _v.groupby(["region", "vendedor"])["t"].sum()
assert len(tabla) == 8, "tabla debe tener una fila por vendedor (8)."
for _, _f in tabla.iterrows():
    _otros = _g[_f["region"]]
    assert int(_f["puesto"]) == int((_otros > _otros[_f["vendedor"]]).sum()) + 1, f"El puesto de {_f['vendedor']} en {_f['region']} no es correcto."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🥇🥈 Los 2 mejores productos de cada región',
            'html' => '<p>Crea <code>top2</code>: para cada región, los <b>2 productos</b> con más ingresos (columnas <code>region</code>, <code>producto</code>, <code>total</code>). Debe tener 8 filas.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => '',
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\npor_prod = ventas.groupby([\"region\", \"producto\"], as_index=False)[\"total\"].sum()\ntop2 = por_prod.sort_values(\"total\", ascending=False).groupby(\"region\").head(2).sort_values([\"region\", \"total\"], ascending=[True, False])\ntop2\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_v["total"] = _v["unidades"] * _v["precio_unitario"]
_p = _v.groupby(["region", "producto"])["total"].sum()
assert len(top2) == 8 and set(["region", "producto", "total"]) <= set(top2.columns), "top2 debe tener 8 filas con region, producto y total."
for _r in _p.index.get_level_values(0).unique():
    _esperados = set(_p[_r].nlargest(2).index)
    assert set(top2[top2["region"] == _r]["producto"]) == _esperados, f"Los 2 mejores de {_r} deberían ser {_esperados}."
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Cuántas filas tiene el resultado de <code>df.groupby("region")["total"].transform("sum")</code> si <code>df</code> tiene 300 filas y 4 regiones?',
            'options' => ['4', '300', '1', '1200'],
            'answer' => 1,
            'explain' => '<code>transform</code> devuelve un valor <b>por cada fila original</b> (300), a diferencia de <code>sum()</code>, que devuelve uno por grupo (4).',
        ],
        [
            'type' => 'exercise',
            'title' => '🧊 Tabla cruzada con unstack',
            'html' => '<p>Crea <code>matriz</code>: el total por <code>region</code> (filas) y <code>categoria</code> (columnas) usando <code>groupby</code> + <code>unstack</code> (no <code>pivot_table</code>), rellenando con 0. Después guarda en <code>celda_max</code> una tupla <code>(region, categoria)</code> con la combinación de mayor total.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => '',
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nmatriz = ventas.groupby([\"region\", \"categoria\"])[\"total\"].sum().unstack(fill_value=0)\ncelda_max = matriz.stack().idxmax()\nprint(celda_max)\nmatriz.round(0)\n",
            'check' => <<<'PY'
import pandas as pd
assert "unstack" in _codigo and "pivot" not in _codigo, "Usa groupby + unstack (sin pivot)."
_v = pd.read_csv("ventas.csv")
_v["t"] = _v["unidades"] * _v["precio_unitario"]
_s = _v.groupby(["region", "categoria"])["t"].sum()
assert matriz.shape == (4, 3), f"matriz debe ser 4 × 3 y es {matriz.shape}."
assert tuple(celda_max) == _s.idxmax(), f"La combinación de mayor total es {_s.idxmax()}."
PY,
        ],
    ],
];
