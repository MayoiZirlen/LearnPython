<?php
return [
    'title' => 'JEFE FINAL: Lucifer, Señor de los Datos Sucios',
    'icon' => '😈',
    'minutes' => 30,
    'summary' => 'Construye un pipeline a prueba de todo que funcione con cualquier archivo que te lance.',
    'steps' => [
        [
            'type' => 'text',
            'title' => '👿 El último enemigo',
            'html' => <<<'HTML'
<div class="warn">
<p>🔥 <b>Lucifer</b> no te dará un archivo: te dará <b>muchos</b>, cada uno más sucio que el anterior. Y no le basta que tu código funcione con uno: quiere una <b>función reutilizable</b> que sobreviva a cualquiera.</p>
</div>
<p>Tu misión: escribir <code>pipeline(ruta)</code>, una función que reciba la ruta de un CSV con el formato de ventas y devuelva un <b>diccionario de KPIs</b>. La vamos a probar con <code>ventas.csv</code>, con <code>ventas_sucias.csv</code> y con un <b>archivo secreto</b> que Lucifer genera al vuelo. 😈</p>
<p>Requisitos de limpieza (en este orden):</p>
<ol>
  <li>Quitar filas duplicadas</li>
  <li><code>region</code>: quitar espacios y formato Título</li>
  <li><code>unidades</code>: convertir a número (<code>pd.to_numeric(..., errors="coerce")</code>) y rellenar vacíos con la <b>mediana</b></li>
  <li><code>precio_unitario</code>: convertir a número y <b>eliminar</b> las filas sin precio</li>
  <li><code>total</code> = unidades × precio_unitario</li>
</ol>
<p>Y devolver: <code>{"filas", "ingreso", "region_top", "vendedor_top", "mes_top"}</code> (ingreso redondeado a 2 decimales; mes_top es el número de mes con más ingreso).</p>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '🔥 Forja el pipeline',
            'html' => '<p>Escribe <code>pipeline(ruta)</code> según los requisitos. Lucifer probará tu función con 3 archivos distintos, incluido uno que tú nunca verás. <b>Nada de valores escritos a mano.</b></p>',
            'starter' => "import pandas as pd\n\ndef pipeline(ruta):\n    df = pd.read_csv(ruta)\n    # 1. duplicados\n    # 2. region\n    # 3. unidades\n    # 4. precio_unitario\n    # 5. total\n    return {\n        \"filas\": 0,\n        \"ingreso\": 0,\n        \"region_top\": None,\n        \"vendedor_top\": None,\n        \"mes_top\": None,\n    }\n\n\nprint(pipeline(\"ventas_sucias.csv\"))\n",
            'hint' => '',
            'solution' => "import pandas as pd\n\ndef pipeline(ruta):\n    df = pd.read_csv(ruta)\n    df = df.drop_duplicates()\n    df[\"region\"] = df[\"region\"].astype(str).str.strip().str.title()\n    df[\"unidades\"] = pd.to_numeric(df[\"unidades\"], errors=\"coerce\")\n    df[\"unidades\"] = df[\"unidades\"].fillna(df[\"unidades\"].median())\n    df[\"precio_unitario\"] = pd.to_numeric(df[\"precio_unitario\"], errors=\"coerce\")\n    df = df.dropna(subset=[\"precio_unitario\"])\n    df[\"total\"] = df[\"unidades\"] * df[\"precio_unitario\"]\n    mes = pd.to_datetime(df[\"fecha\"]).dt.month\n    return {\n        \"filas\": int(len(df)),\n        \"ingreso\": round(float(df[\"total\"].sum()), 2),\n        \"region_top\": df.groupby(\"region\")[\"total\"].sum().idxmax(),\n        \"vendedor_top\": df.groupby(\"vendedor\")[\"total\"].sum().idxmax(),\n        \"mes_top\": int(df.groupby(mes)[\"total\"].sum().idxmax()),\n    }\n\n\nprint(pipeline(\"ventas_sucias.csv\"))\nprint(pipeline(\"ventas.csv\"))\n",
            'check' => <<<'PY'
import pandas as pd
import numpy as np

def _ref(ruta):
    d = pd.read_csv(ruta).drop_duplicates()
    d["region"] = d["region"].astype(str).str.strip().str.title()
    d["unidades"] = pd.to_numeric(d["unidades"], errors="coerce")
    d["unidades"] = d["unidades"].fillna(d["unidades"].median())
    d["precio_unitario"] = pd.to_numeric(d["precio_unitario"], errors="coerce")
    d = d.dropna(subset=["precio_unitario"])
    d["total"] = d["unidades"] * d["precio_unitario"]
    m = pd.to_datetime(d["fecha"]).dt.month
    return {"filas": len(d), "ingreso": round(float(d["total"].sum()), 2),
            "region_top": d.groupby("region")["total"].sum().idxmax(),
            "vendedor_top": d.groupby("vendedor")["total"].sum().idxmax(),
            "mes_top": int(d.groupby(m)["total"].sum().idxmax())}

# 😈 El archivo secreto de Lucifer: más sucio, con texto en columnas numéricas
_rng = np.random.default_rng(666)
_s = pd.read_csv("ventas.csv").sample(180, random_state=13)
_s.loc[_s.sample(15, random_state=1).index, "region"] = _s["region"].str.upper() + "  "
_s["unidades"] = _s["unidades"].astype(object)
_s.loc[_s.sample(10, random_state=2).index, "unidades"] = "n/a"
_s["precio_unitario"] = _s["precio_unitario"].astype(object)
_s.loc[_s.sample(7, random_state=3).index, "precio_unitario"] = ""
_s = pd.concat([_s, _s.head(12)])
_s.to_csv("_lucifer.csv", index=False)

for _archivo in ["ventas.csv", "ventas_sucias.csv", "_lucifer.csv"]:
    _nombre = "el archivo secreto de Lucifer" if _archivo.startswith("_") else _archivo
    try:
        _r = pipeline(_archivo)
    except Exception as _e:
        raise AssertionError(f"Tu pipeline explotó con {_nombre}: {type(_e).__name__}: {_e}")
    _e = _ref(_archivo)
    assert isinstance(_r, dict), "pipeline debe devolver un diccionario."
    for _k, _val in _e.items():
        assert _k in _r, f"Falta la clave {_k!r} en el resultado."
        if _k == "ingreso":
            assert abs(float(_r[_k]) - _val) < 0.02, f"Con {_nombre}, el ingreso debería ser {_val:,.2f} y obtuviste {_r[_k]}."
        else:
            assert _r[_k] == _val or str(_r[_k]) == str(_val), f"Con {_nombre}, {_k} debería ser {_val!r} y obtuviste {_r[_k]!r}."
import os
os.remove("_lucifer.csv")
PY,
            'success' => '💥 ¡Tu pipeline sobrevivió al archivo secreto de Lucifer!',
        ],
        [
            'type' => 'exercise',
            'title' => '📜 El informe final',
            'html' => <<<'HTML'
<p>Usa tu <code>pipeline</code> (cópialo aquí) para crear <code>comparativo</code>: un DataFrame con los KPIs como filas y dos columnas, <code>limpio</code> (resultado de <code>ventas.csv</code>) y <code>sucio</code> (resultado de <code>ventas_sucias.csv</code>).</p>
<p>Guárdalo en <code>informe_infierno.xlsx</code>, hoja <code>KPIs</code>, <b>con</b> el índice (los nombres de los KPIs), encabezados en negritas y el ancho de la columna A en 20.</p>
HTML,
            'starter' => "import pandas as pd\nfrom openpyxl.styles import Font\n\n# pega aquí tu función pipeline\n\n",
            'hint' => '',
            'solution' => "import pandas as pd\nfrom openpyxl.styles import Font\n\ndef pipeline(ruta):\n    df = pd.read_csv(ruta)\n    df = df.drop_duplicates()\n    df[\"region\"] = df[\"region\"].astype(str).str.strip().str.title()\n    df[\"unidades\"] = pd.to_numeric(df[\"unidades\"], errors=\"coerce\")\n    df[\"unidades\"] = df[\"unidades\"].fillna(df[\"unidades\"].median())\n    df[\"precio_unitario\"] = pd.to_numeric(df[\"precio_unitario\"], errors=\"coerce\")\n    df = df.dropna(subset=[\"precio_unitario\"])\n    df[\"total\"] = df[\"unidades\"] * df[\"precio_unitario\"]\n    mes = pd.to_datetime(df[\"fecha\"]).dt.month\n    return {\n        \"filas\": int(len(df)),\n        \"ingreso\": round(float(df[\"total\"].sum()), 2),\n        \"region_top\": df.groupby(\"region\")[\"total\"].sum().idxmax(),\n        \"vendedor_top\": df.groupby(\"vendedor\")[\"total\"].sum().idxmax(),\n        \"mes_top\": int(df.groupby(mes)[\"total\"].sum().idxmax()),\n    }\n\ncomparativo = pd.DataFrame({\"limpio\": pipeline(\"ventas.csv\"), \"sucio\": pipeline(\"ventas_sucias.csv\")})\n\nwith pd.ExcelWriter(\"informe_infierno.xlsx\", engine=\"openpyxl\") as excel:\n    comparativo.to_excel(excel, sheet_name=\"KPIs\", index_label=\"kpi\")\n    hoja = excel.sheets[\"KPIs\"]\n    for celda in hoja[1]:\n        celda.font = Font(bold=True)\n    hoja.column_dimensions[\"A\"].width = 20\n\nprint(\"👑 Informe entregado. Lucifer ha caído.\")\ncomparativo\n",
            'check' => <<<'PY'
import pandas as pd
from openpyxl import load_workbook
assert "informe_infierno.xlsx" in _codigo, "Escribe el archivo informe_infierno.xlsx."
assert list(comparativo.columns) == ["limpio", "sucio"], "comparativo debe tener las columnas limpio y sucio (en ese orden)."
assert set(comparativo.index) == {"filas", "ingreso", "region_top", "vendedor_top", "mes_top"}, "Las filas deben ser los 5 KPIs."
assert int(comparativo.loc["filas", "limpio"]) == 300, "Con ventas.csv deben quedar 300 filas."
_l = pd.read_excel("informe_infierno.xlsx", sheet_name="KPIs", index_col=0)
assert set(_l.index) == set(comparativo.index), "La hoja KPIs debe incluir el índice con los nombres de los KPIs."
_h = load_workbook("informe_infierno.xlsx")["KPIs"]
assert all(c.font.bold for c in _h[1] if c.value is not None), "Los encabezados deben ir en negritas."
assert _h.column_dimensions["A"].width == 20, "La columna A debe tener ancho 20."
PY,
            'success' => '👑🔥 LUCIFER HA CAÍDO. Eres oficialmente Rey/Reina del Infierno de los Datos.',
        ],
        [
            'type' => 'text',
            'title' => '👑 Saliste del Infierno',
            'html' => <<<'HTML'
<p>Pocos llegan aquí. Completaste el juego principal, el New Game Plus <b>y</b> el Infierno. Eso significa que ya sabes:</p>
<ul>
  <li>♾️ Procesar datos que no caben en memoria con generadores</li>
  <li>🏗️ Modelar el mundo con clases y escribir código reutilizable</li>
  <li>⏱️ Pensar en la eficiencia de tus algoritmos</li>
  <li>🏅 Rankings, participación y ventanas por grupo en pandas</li>
  <li>🚀 Vectorizar en vez de hacer bucles</li>
  <li>⚖️ Decidir con pruebas de hipótesis, y 🤖 clasificar con modelos evaluados como profesional</li>
  <li>🔥 Construir pipelines de limpieza que sobreviven a cualquier archivo</li>
</ul>
<p>Pulsa <b>Terminar lección</b> y reclama tu corona. 👑</p>
HTML,
        ],
    ],
];
