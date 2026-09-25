<?php
return [
    'title' => 'Limpieza de datos',
    'icon' => '🧹',
    'minutes' => 14,
    'summary' => 'Duplicados, datos faltantes y textos desordenados.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'El 80% del trabajo',
            'html' => <<<'HTML'
<p>Se dice que un analista pasa el <b>80% de su tiempo limpiando datos</b>. Los datos reales vienen con errores: gente que escribe "norte " con espacio, celdas vacías, filas repetidas…</p>
<p>Si no limpias, tus resultados salen <b>mal</b> (y nadie se da cuenta 😱). En esta lección trabajarás con <code>ventas_sucias.csv</code>, una versión "sucia" de las ventas. Tu misión: dejarla impecable.</p>
<table>
<tr><th>Problema</th><th>Cómo detectarlo</th><th>Cómo arreglarlo</th></tr>
<tr><td>Filas duplicadas</td><td><code>df.duplicated().sum()</code></td><td><code>df.drop_duplicates()</code></td></tr>
<tr><td>Datos faltantes</td><td><code>df.isna().sum()</code></td><td><code>fillna()</code> o <code>dropna()</code></td></tr>
<tr><td>Texto inconsistente</td><td><code>df["col"].unique()</code></td><td><code>.str.strip().str.title()</code></td></tr>
</table>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Diagnóstico',
            'html' => '<p>Ejecuta y observa los tres problemas. En pandas los datos faltantes aparecen como <code>NaN</code> ("Not a Number").</p>',
            'code' => <<<'PY'
import pandas as pd

df = pd.read_csv("ventas_sucias.csv")
print("Tamaño:", df.shape)
print("\nFilas duplicadas:", df.duplicated().sum())
print("\nDatos faltantes por columna:")
print(df.isna().sum())
print("\nRegiones:", df["region"].unique())
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'Las regiones aparecen como <code>"Norte"</code> y <code>"norte "</code>. Si agrupas sin limpiar, ¿qué pasa?',
            'options' => ['pandas las junta automáticamente', 'Aparecen como dos regiones distintas y los totales salen mal', 'Da un error', 'Se borran'],
            'answer' => 1,
            'explain' => 'Para la computadora <code>"Norte"</code> y <code>"norte "</code> son textos distintos. ¡Por eso hay que limpiar antes de analizar!',
        ],
        [
            'type' => 'exercise',
            'title' => '👯 Adiós duplicados',
            'html' => '<p>Crea <code>limpio</code> a partir de <code>df</code> <b>sin filas duplicadas</b>.</p>',
            'starter' => "import pandas as pd\n\ndf = pd.read_csv(\"ventas_sucias.csv\")\n\nlimpio = df\nprint(\"Antes:\", len(df), \"Después:\", len(limpio))\n",
            'hint' => '<code>limpio = df.drop_duplicates()</code>',
            'solution' => "import pandas as pd\n\ndf = pd.read_csv(\"ventas_sucias.csv\")\n\nlimpio = df.drop_duplicates()\nprint(\"Antes:\", len(df), \"Después:\", len(limpio))\n",
            'check' => <<<'PY'
assert limpio.duplicated().sum() == 0, "Todavía hay filas duplicadas."
assert len(limpio) == 120, f"Deberían quedar 120 filas y quedan {len(limpio)}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🔤 Regiones uniformes',
            'html' => '<p>Arregla la columna <code>region</code> para que no tenga espacios sobrantes y tenga formato de título. Al final, <code>df["region"].unique()</code> debe tener solo 4 regiones.</p><p>Los métodos de texto que ya conoces funcionan en columnas enteras anteponiendo <code>.str</code>.</p>',
            'starter' => "import pandas as pd\n\ndf = pd.read_csv(\"ventas_sucias.csv\")\n\ndf[\"region\"] = df[\"region\"]\nprint(df[\"region\"].unique())\n",
            'hint' => '<code>df["region"] = df["region"].str.strip().str.title()</code>',
            'solution' => "import pandas as pd\n\ndf = pd.read_csv(\"ventas_sucias.csv\")\n\ndf[\"region\"] = df[\"region\"].str.strip().str.title()\nprint(df[\"region\"].unique())\n",
            'check' => <<<'PY'
assert sorted(df["region"].unique()) == ["Centro", "Este", "Norte", "Sur"], f"Las regiones quedaron así: {list(df['region'].unique())}"
PY,
        ],
        [
            'type' => 'text',
            'title' => '¿Qué hacer con los datos faltantes?',
            'html' => <<<'HTML'
<p>No hay una única respuesta; depende del caso:</p>
<ul>
  <li><b>Rellenar</b> (<code>fillna</code>) con un valor razonable, como la mediana: <br><code>df["unidades"] = df["unidades"].fillna(df["unidades"].median())</code></li>
  <li><b>Eliminar</b> las filas (<code>dropna</code>) cuando el dato es imprescindible y no se puede adivinar: <br><code>df = df.dropna(subset=["precio_unitario"])</code></li>
</ul>
<div class="tip">💡 Siempre documenta qué decidiste y por qué. Es parte del trabajo de un buen analista.</div>
HTML,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Por qué no es buena idea simplemente borrar <b>todas</b> las filas que tengan algún dato faltante?',
            'options' => [
                'Porque pandas no lo permite',
                'Porque podrías perder mucha información útil de las otras columnas',
                'Porque es muy lento',
                'Sí es buena idea siempre',
            ],
            'answer' => 1,
            'explain' => 'Si una venta solo tiene vacía una columna, sus otros datos siguen sirviendo. Borrar de más puede sesgar tus conclusiones.',
        ],
        [
            'type' => 'exercise',
            'title' => '🧽 Limpieza completa',
            'html' => <<<'HTML'
<p>¡Hora de juntar todo! Partiendo de <code>df</code>, aplica en orden:</p>
<ol>
  <li>Elimina duplicados</li>
  <li>Limpia la columna <code>region</code> (strip + title)</li>
  <li>Rellena los faltantes de <code>unidades</code> con la <b>mediana</b> de esa columna</li>
  <li>Elimina las filas donde falte el <code>precio_unitario</code></li>
</ol>
<p>El resultado debe quedar en <code>df</code>, sin ningún dato faltante.</p>
HTML,
            'starter' => "import pandas as pd\n\ndf = pd.read_csv(\"ventas_sucias.csv\")\n\n# 1. duplicados\n\n# 2. regiones\n\n# 3. unidades faltantes\n\n# 4. precios faltantes\n\nprint(df.isna().sum())\nprint(df.shape)\n",
            'hint' => ['Recuerda reasignar: <code>df = df.drop_duplicates()</code>', 'Paso 3: <code>df["unidades"] = df["unidades"].fillna(df["unidades"].median())</code>', 'Paso 4: <code>df = df.dropna(subset=["precio_unitario"])</code>'],
            'solution' => "import pandas as pd\n\ndf = pd.read_csv(\"ventas_sucias.csv\")\n\n# 1. duplicados\ndf = df.drop_duplicates()\n# 2. regiones\ndf[\"region\"] = df[\"region\"].str.strip().str.title()\n# 3. unidades faltantes\ndf[\"unidades\"] = df[\"unidades\"].fillna(df[\"unidades\"].median())\n# 4. precios faltantes\ndf = df.dropna(subset=[\"precio_unitario\"])\n\nprint(df.isna().sum())\nprint(df.shape)\n",
            'check' => <<<'PY'
import pandas as pd
_d = pd.read_csv("ventas_sucias.csv").drop_duplicates()
_d["region"] = _d["region"].str.strip().str.title()
_d["unidades"] = _d["unidades"].fillna(_d["unidades"].median())
_d = _d.dropna(subset=["precio_unitario"])
assert df.duplicated().sum() == 0, "Todavía hay duplicados."
assert sorted(df["region"].unique()) == ["Centro", "Este", "Norte", "Sur"], "Las regiones no están limpias."
assert df.isna().sum().sum() == 0, "Todavía hay datos faltantes."
assert len(df) == len(_d), f"Deberían quedar {len(_d)} filas y tienes {len(df)}. ¿Eliminaste solo las filas sin precio?"
assert abs(df["unidades"].sum() - _d["unidades"].sum()) < 1e-6, "Los faltantes de unidades deben rellenarse con la mediana."
PY,
            'success' => '¡Datos impecables! ✨ Ahora cualquier análisis que hagas con ellos será confiable.',
        ],
    ],
];
