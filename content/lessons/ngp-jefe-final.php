<?php
return [
    'title' => 'JEFE FINAL NG+: La Auditoría',
    'icon' => '👹',
    'minutes' => 25,
    'summary' => 'Auditoría completa de TiendaPy: descuentos, tendencias, calidad y reporte en Excel.',
    'steps' => [
        [
            'type' => 'text',
            'title' => '🚨 Misión: auditar TiendaPy',
            'html' => <<<'HTML'
<div class="analogy">
<p><b>De:</b> Dirección de Finanzas<br><b>Para:</b> Analista senior (¡ascendiste! 🎉)<br><b>Asunto:</b> URGENTE – auditoría 2025</p>
<p>Sospechamos que algunos vendedores dan demasiados descuentos y que hay problemas con la calidad de los datos. Necesito:</p>
<ol>
  <li>Quién da el <b>mayor descuento promedio</b> sobre el precio de lista.</li>
  <li>En qué mes tuvimos la <b>peor caída</b> de ventas.</li>
  <li>Un <b>reporte de calidad</b> del archivo sucio.</li>
  <li>Todo en un <b>Excel con formato</b> para la junta.</li>
</ol>
</div>
<p>Este jefe combina todo lo del New Game Plus: <b>merge</b>, <b>series de tiempo</b>, <b>comprensiones</b>, <b>ExcelWriter</b> y <b>openpyxl</b>. ¡Sin miedo! 💪</p>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '1️⃣ ¿Quién regala más?',
            'html' => '<p>Une las ventas con la hoja <code>Productos</code> (por <code>producto</code> y <code>categoria</code>), calcula <code>descuento_pct</code> = (precio_lista − precio_unitario) / precio_lista × 100 y guarda en <code>mas_descuento</code> el nombre del vendedor con el <b>mayor descuento promedio</b>.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\nproductos = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Productos\")\n\n",
            'hint' => ['<code>df = ventas.merge(productos, on=["producto", "categoria"])</code>', '<code>mas_descuento = df.groupby("vendedor")["descuento_pct"].mean().idxmax()</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\nproductos = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Productos\")\n\ndf = ventas.merge(productos, on=[\"producto\", \"categoria\"])\ndf[\"descuento_pct\"] = (df[\"precio_lista\"] - df[\"precio_unitario\"]) / df[\"precio_lista\"] * 100\npor_vendedor = df.groupby(\"vendedor\")[\"descuento_pct\"].mean().sort_values(ascending=False)\nmas_descuento = por_vendedor.idxmax()\nprint(por_vendedor.round(2))\nprint(\"🕵️ Mayor descuento promedio:\", mas_descuento)\n",
            'check' => <<<'PY'
import pandas as pd
_d = pd.read_excel("ventas.xlsx", sheet_name="Ventas").merge(pd.read_excel("ventas.xlsx", sheet_name="Productos"), on=["producto", "categoria"])
_d["x"] = (_d["precio_lista"] - _d["precio_unitario"]) / _d["precio_lista"] * 100
_e = _d.groupby("vendedor")["x"].mean().idxmax()
assert mas_descuento == _e, f"{mas_descuento!r} no es quien da más descuento promedio."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '2️⃣ La peor caída',
            'html' => '<p>Calcula las ventas mensuales (total = unidades × precio_unitario, con <code>resample("ME")</code>), su cambio porcentual y guarda en <code>peor_mes</code> el <b>número de mes</b> con la mayor caída (el cambio más negativo).</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => ['<code>mensual = ventas.set_index("fecha")["total"].resample("ME").sum()</code>', '<code>peor_mes = mensual.pct_change().idxmin().month</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nmensual = ventas.set_index(\"fecha\")[\"total\"].resample(\"ME\").sum()\ncambio = (mensual.pct_change() * 100).round(1)\npeor_mes = cambio.idxmin().month\nprint(cambio)\nprint(\"📉 Peor caída en el mes\", peor_mes)\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
_m = (_v["unidades"] * _v["precio_unitario"]).groupby(pd.to_datetime(_v["fecha"]).dt.month).sum()
assert int(peor_mes) == int(_m.pct_change().idxmin()), f"El mes con la peor caída es el {int(_m.pct_change().idxmin())}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '3️⃣ Reporte de calidad',
            'html' => '<p>Con <code>ventas_sucias.csv</code> crea, usando una <b>comprensión de diccionario</b>, <code>faltantes</code> = {columna: cantidad de vacíos} <b>solo para las columnas que tienen algún vacío</b>. Guarda también en <code>duplicados</code> cuántas filas repetidas hay y en <code>regiones_raras</code> cuántos valores de <code>region</code> tienen espacios al inicio o final.</p>',
            'starter' => "import pandas as pd\n\nsucio = pd.read_csv(\"ventas_sucias.csv\")\n\n",
            'hint' => ['<code>faltantes = {c: int(n) for c, n in sucio.isna().sum().items() if n > 0}</code>', '<code>regiones_raras = (sucio["region"] != sucio["region"].str.strip()).sum()</code>'],
            'solution' => "import pandas as pd\n\nsucio = pd.read_csv(\"ventas_sucias.csv\")\n\nfaltantes = {c: int(n) for c, n in sucio.isna().sum().items() if n > 0}\nduplicados = int(sucio.duplicated().sum())\nregiones_raras = int((sucio[\"region\"] != sucio[\"region\"].str.strip()).sum())\nprint(\"Faltantes:\", faltantes)\nprint(\"Duplicados:\", duplicados)\nprint(\"Regiones con espacios:\", regiones_raras)\n",
            'check' => <<<'PY'
import pandas as pd
_s = pd.read_csv("ventas_sucias.csv")
_f = {c: int(n) for c, n in _s.isna().sum().items() if n > 0}
assert isinstance(faltantes, dict) and {k: int(v) for k, v in faltantes.items()} == _f, f"faltantes debería ser {_f}."
assert int(duplicados) == int(_s.duplicated().sum()), "La cantidad de duplicados no coincide."
assert int(regiones_raras) == int((_s["region"] != _s["region"].str.strip()).sum()), "regiones_raras no coincide."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '4️⃣ El reporte para la junta',
            'html' => <<<'HTML'
<p>Genera <code>auditoria.xlsx</code> con dos hojas:</p>
<ul>
  <li><code>Vendedores</code>: columnas <code>vendedor</code>, <code>total</code> (suma) y <code>descuento_pct</code> (promedio, 2 decimales), ordenada por <code>descuento_pct</code> de mayor a menor</li>
  <li><code>Mensual</code>: columnas <code>mes</code> (1 a 12) y <code>total</code></li>
</ul>
<p>Y con openpyxl pon en <b>negritas</b> los encabezados de ambas hojas e inmoviliza la fila 1 (<code>freeze_panes = "A2"</code>).</p>
HTML,
            'starter' => "import pandas as pd\nfrom openpyxl.styles import Font\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\nproductos = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Productos\")\ndf = ventas.merge(productos, on=[\"producto\", \"categoria\"])\ndf[\"total\"] = df[\"unidades\"] * df[\"precio_unitario\"]\ndf[\"descuento_pct\"] = (df[\"precio_lista\"] - df[\"precio_unitario\"]) / df[\"precio_lista\"] * 100\n\n",
            'hint' => [
                '<code>vend = df.groupby("vendedor", as_index=False).agg(total=("total", "sum"), descuento_pct=("descuento_pct", "mean"))</code>',
                '<code>mensual = df.groupby(df["fecha"].dt.month.rename("mes"), as_index=False)["total"].sum()</code> (o crea antes la columna mes)',
                'Dentro del <code>with pd.ExcelWriter("auditoria.xlsx", engine="openpyxl") as excel:</code> escribe ambas hojas y luego recorre <code>for nombre in ["Vendedores", "Mensual"]:</code> para dar formato a <code>excel.sheets[nombre]</code>.',
            ],
            'solution' => "import pandas as pd\nfrom openpyxl.styles import Font\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\nproductos = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Productos\")\ndf = ventas.merge(productos, on=[\"producto\", \"categoria\"])\ndf[\"total\"] = df[\"unidades\"] * df[\"precio_unitario\"]\ndf[\"descuento_pct\"] = (df[\"precio_lista\"] - df[\"precio_unitario\"]) / df[\"precio_lista\"] * 100\n\nvend = df.groupby(\"vendedor\", as_index=False).agg(total=(\"total\", \"sum\"), descuento_pct=(\"descuento_pct\", \"mean\"))\nvend[\"descuento_pct\"] = vend[\"descuento_pct\"].round(2)\nvend = vend.sort_values(\"descuento_pct\", ascending=False)\n\ndf[\"mes\"] = df[\"fecha\"].dt.month\nmensual = df.groupby(\"mes\", as_index=False)[\"total\"].sum()\n\nwith pd.ExcelWriter(\"auditoria.xlsx\", engine=\"openpyxl\") as excel:\n    vend.to_excel(excel, sheet_name=\"Vendedores\", index=False)\n    mensual.to_excel(excel, sheet_name=\"Mensual\", index=False)\n    for nombre in [\"Vendedores\", \"Mensual\"]:\n        hoja = excel.sheets[nombre]\n        for celda in hoja[1]:\n            celda.font = Font(bold=True)\n        hoja.freeze_panes = \"A2\"\n\nprint(\"📗 auditoria.xlsx lista para la junta\")\n",
            'check' => <<<'PY'
import pandas as pd
from openpyxl import load_workbook
assert "auditoria.xlsx" in _codigo and "ExcelWriter" in _codigo, "Escribe auditoria.xlsx con pd.ExcelWriter."
_l = pd.read_excel("auditoria.xlsx", sheet_name=None)
assert list(_l) == ["Vendedores", "Mensual"], f"Las hojas deben ser Vendedores y Mensual; son {list(_l)}."
_ve, _me = _l["Vendedores"], _l["Mensual"]
assert list(_ve.columns) == ["vendedor", "total", "descuento_pct"], f"Columnas de Vendedores: {list(_ve.columns)}"
assert len(_ve) == 8 and _ve["descuento_pct"].is_monotonic_decreasing, "Vendedores debe tener 8 filas ordenadas por descuento_pct de mayor a menor."
assert list(_me.columns) == ["mes", "total"] and list(_me["mes"]) == list(range(1, 13)), "Mensual debe tener columnas mes (1 a 12) y total."
_v = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
assert abs(_me["total"].sum() - (_v["unidades"] * _v["precio_unitario"]).sum()) < 0.01, "Los totales mensuales no suman el total de ventas."
_wb = load_workbook("auditoria.xlsx")
for _n in ["Vendedores", "Mensual"]:
    _h = _wb[_n]
    assert all(c.font.bold for c in _h[1]), f"Los encabezados de {_n} deben ir en negritas."
    assert _h.freeze_panes == "A2", f"Inmoviliza la fila 1 de {_n} con freeze_panes = \"A2\"."
PY,
            'success' => '👹💥 ¡JEFE DERROTADO! Tu auditoría está lista y con formato profesional.',
        ],
        [
            'type' => 'text',
            'title' => '🏆 NEW GAME PLUS COMPLETADO',
            'html' => <<<'HTML'
<p>Terminaste el juego… <b>dos veces</b>. Ahora sabes cosas que muchos analistas junior todavía no dominan:</p>
<ul>
  <li>🌀 Comprensiones, <code>try/except</code> y lambdas</li>
  <li>🔗 Unir tablas, ⏳ series de tiempo, 🔤 regex y 🔄 melt/pivot</li>
  <li>📗 Generar libros de Excel de varias hojas con formato y fórmulas</li>
  <li>🎯 Detectar atípicos, medir correlaciones y 🔮 entrenar modelos predictivos</li>
</ul>
<p><b>Siguientes misiones en el mundo real:</b> SQL para bases de datos, Power BI o Tableau para tableros, y Kaggle para competir con datasets reales. Y recuerda que el <a href="playground.php">Laboratorio</a> acepta tus propios archivos de Excel. 🚀</p>
<p>Pulsa <b>Terminar lección</b> para reclamar tu <b>Trofeo de Platino</b>. 🏆</p>
HTML,
        ],
    ],
];
