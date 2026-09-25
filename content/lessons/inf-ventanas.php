<?php
return [
    'title' => 'Ventanas por grupo: shift, diff y cumsum',
    'icon' => '🪟',
    'minutes' => 15,
    'summary' => 'Compara cada venta con la anterior del mismo vendedor y acumula por grupo.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Mirar hacia atrás',
            'html' => <<<'HTML'
<p>Muchas preguntas comparan una fila con <b>la anterior del mismo grupo</b>: ¿cuántos días pasaron desde la última venta de este vendedor? ¿Cuánto lleva acumulado en el año?</p>
<table>
<tr><th>Operación</th><th>Qué hace</th></tr>
<tr><td><code>.shift(1)</code></td><td>trae el valor de la fila anterior</td></tr>
<tr><td><code>.diff()</code></td><td>valor actual − valor anterior</td></tr>
<tr><td><code>.cumsum()</code></td><td>suma acumulada</td></tr>
<tr><td><code>.cumcount()</code></td><td>número de fila dentro del grupo (0, 1, 2…)</td></tr>
</table>
<p>Combinadas con <code>groupby</code>, trabajan <b>dentro de cada grupo</b> y reinician en el siguiente:</p>
<pre>df = df.sort_values(["vendedor", "fecha"])          # ⚠️ ¡ordenar primero!
df["acumulado"] = df.groupby("vendedor")["total"].cumsum()</pre>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'shift por grupo',
            'code' => <<<'PY'
import pandas as pd

ventas = pd.read_csv("ventas.csv", parse_dates=["fecha"]).sort_values(["vendedor", "fecha"])
ventas["total"] = ventas["unidades"] * ventas["precio_unitario"]
ventas["total_anterior"] = ventas.groupby("vendedor")["total"].shift(1)
ventas[["vendedor", "fecha", "total", "total_anterior"]].head(6)
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📅 Días entre ventas',
            'html' => '<p>Ordena por vendedor y fecha, y crea <code>dias_desde_anterior</code>: cuántos días pasaron desde la venta anterior <b>del mismo vendedor</b> (la primera de cada vendedor queda vacía). Guarda en <code>mas_lento</code> el vendedor con el mayor promedio de días entre ventas.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\", parse_dates=[\"fecha\"])\n\n",
            'hint' => '',
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\", parse_dates=[\"fecha\"])\n\nventas = ventas.sort_values([\"vendedor\", \"fecha\"])\nventas[\"dias_desde_anterior\"] = ventas.groupby(\"vendedor\")[\"fecha\"].diff().dt.days\nmas_lento = ventas.groupby(\"vendedor\")[\"dias_desde_anterior\"].mean().idxmax()\nprint(\"Más lento:\", mas_lento)\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv", parse_dates=["fecha"]).sort_values(["vendedor", "fecha"])
_d = _v.groupby("vendedor")["fecha"].diff().dt.days
_e = _d.groupby(_v["vendedor"]).mean().idxmax()
_u = ventas.sort_values(["vendedor", "fecha", "id"])
assert _u["dias_desde_anterior"].isna().sum() == 8, "La primera venta de cada vendedor (8) debe quedar vacía."
assert abs(_u["dias_desde_anterior"].sum() - _d.sum()) < 1e-6, "Los días entre ventas no coinciden. ¿Ordenaste por vendedor y fecha?"
assert mas_lento == _e, f"El vendedor con más días promedio entre ventas es {_e}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📈 Acumulado del año por vendedor',
            'html' => '<p>Crea <code>acumulado</code>: la suma acumulada de <code>total</code> de cada vendedor en orden de fecha. Luego crea <code>meta_rota</code>: un diccionario {vendedor: fecha} con la <b>primera fecha</b> en que cada vendedor superó 15,000 acumulados (solo para quienes lo lograron).</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\", parse_dates=[\"fecha\"])\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => '',
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\", parse_dates=[\"fecha\"])\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nventas = ventas.sort_values([\"vendedor\", \"fecha\", \"id\"])\nventas[\"acumulado\"] = ventas.groupby(\"vendedor\")[\"total\"].cumsum()\nmeta_rota = ventas[ventas[\"acumulado\"] > 15000].groupby(\"vendedor\")[\"fecha\"].min().to_dict()\nmeta_rota\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv", parse_dates=["fecha"]).sort_values(["vendedor", "fecha", "id"])
_v["t"] = _v["unidades"] * _v["precio_unitario"]
_v["a"] = _v.groupby("vendedor")["t"].cumsum()
_e = _v[_v["a"] > 15000].groupby("vendedor")["fecha"].min()
_ult = ventas.sort_values(["vendedor", "fecha", "id"]).groupby("vendedor")["acumulado"].last()
assert ((_ult - _v.groupby("vendedor")["t"].sum()).abs() < 0.01).all(), "El último acumulado de cada vendedor debe ser su total del año."
assert set(meta_rota) == set(_e.index), f"Superaron 15,000: {sorted(_e.index)}"
for _k, _f in _e.items():
    assert pd.Timestamp(meta_rota[_k]) == _f, f"{_k} superó los 15,000 el {_f.date()}."
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué pasa si haces <code>groupby("vendedor")["total"].cumsum()</code> <b>sin ordenar</b> por fecha?',
            'options' => ['pandas ordena solo', 'El acumulado sigue el orden en que están las filas, que puede no ser cronológico', 'Da error', 'Suma todo el año en cada fila'],
            'answer' => 1,
            'explain' => 'Las operaciones de ventana respetan el <b>orden actual de las filas</b>. Si no está ordenado por fecha, el "acumulado" no tiene sentido temporal. ¡Ordena siempre primero!',
        ],
        [
            'type' => 'exercise',
            'title' => '🔢 Número de venta y la primera grande',
            'html' => '<p>Crea <code>n_venta</code> (1, 2, 3… para cada vendedor en orden de fecha; usa <code>cumcount</code>) y guarda en <code>primera_grande</code> un diccionario {vendedor: n_venta} con el número de la <b>primera venta mayor a 2,000</b> de cada vendedor.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\", parse_dates=[\"fecha\"])\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => '',
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\", parse_dates=[\"fecha\"])\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nventas = ventas.sort_values([\"vendedor\", \"fecha\", \"id\"])\nventas[\"n_venta\"] = ventas.groupby(\"vendedor\").cumcount() + 1\nprimera_grande = ventas[ventas[\"total\"] > 2000].groupby(\"vendedor\")[\"n_venta\"].min().to_dict()\nprimera_grande\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv", parse_dates=["fecha"]).sort_values(["vendedor", "fecha", "id"])
_v["t"] = _v["unidades"] * _v["precio_unitario"]
_v["n"] = _v.groupby("vendedor").cumcount() + 1
_e = _v[_v["t"] > 2000].groupby("vendedor")["n"].min().to_dict()
assert "cumcount" in _codigo, "Usa cumcount."
assert {k: int(v) for k, v in primera_grande.items()} == {k: int(v) for k, v in _e.items()}, f"primera_grande debería ser {_e}."
PY,
            'success' => '😈 Dominaste las funciones de ventana: la herramienta favorita de los analistas que también usan SQL.',
        ],
    ],
];
