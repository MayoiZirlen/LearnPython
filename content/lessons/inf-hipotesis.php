<?php
return [
    'title' => 'Pruebas de hipótesis y bootstrap',
    'icon' => '⚖️',
    'minutes' => 16,
    'summary' => '¿Es real la diferencia o fue suerte? t-test, chi² e intervalos de confianza.',
    'steps' => [
        [
            'type' => 'text',
            'title' => '¿Casualidad o efecto real?',
            'html' => <<<'HTML'
<p>El Sur vendió en promedio más que el Norte. ¿Es una diferencia <b>real</b> o <b>pura suerte</b>? La estadística lo responde así:</p>
<ol>
  <li><b>Hipótesis nula (H₀)</b>: "no hay diferencia, es azar".</li>
  <li>Calculamos el <b>valor p</b>: la probabilidad de ver una diferencia así de grande <i>si H₀ fuera cierta</i>.</li>
  <li>Si p &lt; 0.05, decimos que es <b>estadísticamente significativa</b> (rechazamos H₀).</li>
</ol>
<table>
<tr><th>Prueba</th><th>Cuándo</th><th>scipy</th></tr>
<tr><td>t de Student</td><td>comparar <b>promedios</b> de 2 grupos</td><td><code>stats.ttest_ind(a, b, equal_var=False)</code></td></tr>
<tr><td>Chi cuadrada</td><td>¿dos variables <b>categóricas</b> están relacionadas?</td><td><code>stats.chi2_contingency(tabla)</code></td></tr>
</table>
<p>Y el <b>bootstrap</b>: remuestrear tus datos miles de veces para ver cuánto varía un resultado. Con eso construyes un <b>intervalo de confianza</b> sin fórmulas.</p>
HTML,
        ],
        [
            'type' => 'quiz',
            'question' => 'Una prueba da p = 0.30. ¿Qué concluyes?',
            'options' => ['Hay 30% de diferencia entre grupos', 'La diferencia podría deberse al azar: no hay evidencia suficiente', 'H₀ es falsa con 70% de seguridad', 'Los grupos son idénticos'],
            'answer' => 1,
            'explain' => 'Un p alto significa que una diferencia así es <b>común por azar</b>. No prueba que los grupos sean iguales, solo que no hay evidencia suficiente de que sean distintos.',
        ],
        [
            'type' => 'exercise',
            'title' => '🧪 t-test: ¿Norte vs. Sur en Tecnología?',
            'html' => '<p>Compara el <code>total</code> de las ventas de <b>Tecnología</b> del Norte contra las del Sur con <code>stats.ttest_ind(..., equal_var=False)</code>. Guarda el valor p en <code>p</code> y en <code>significativo</code> un booleano (p &lt; 0.05).</p>',
            'starter' => "import pandas as pd\nfrom scipy import stats\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => '',
            'solution' => "import pandas as pd\nfrom scipy import stats\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\ntec = ventas[ventas[\"categoria\"] == \"Tecnología\"]\nnorte = tec[tec[\"region\"] == \"Norte\"][\"total\"]\nsur = tec[tec[\"region\"] == \"Sur\"][\"total\"]\np = stats.ttest_ind(norte, sur, equal_var=False).pvalue\nsignificativo = bool(p < 0.05)\nprint(f\"p = {p:.3f} → {'significativo' if significativo else 'puede ser azar'}\")\n",
            'check' => <<<'PY'
import pandas as pd
from scipy import stats as _st
_v = pd.read_csv("ventas.csv")
_v["t"] = _v["unidades"] * _v["precio_unitario"]
_tec = _v[_v["categoria"] == "Tecnología"]
_p = _st.ttest_ind(_tec[_tec["region"] == "Norte"]["t"], _tec[_tec["region"] == "Sur"]["t"], equal_var=False).pvalue
assert abs(float(p) - _p) < 1e-9, f"p debería ser {_p:.4f}. ¿Filtraste solo Tecnología y usaste equal_var=False?"
assert bool(significativo) == bool(_p < 0.05), "significativo debe ser p < 0.05."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🎲 Chi²: ¿región y categoría están relacionadas?',
            'html' => '<p>Crea <code>tabla</code> con la cantidad de ventas por región (filas) y categoría (columnas) usando <code>pd.crosstab</code>. Aplica <code>stats.chi2_contingency(tabla)</code> y guarda el valor p en <code>p_chi</code> y los grados de libertad en <code>gl</code>.</p>',
            'starter' => "import pandas as pd\nfrom scipy import stats\n\nventas = pd.read_csv(\"ventas.csv\")\n\n",
            'hint' => '',
            'solution' => "import pandas as pd\nfrom scipy import stats\n\nventas = pd.read_csv(\"ventas.csv\")\n\ntabla = pd.crosstab(ventas[\"region\"], ventas[\"categoria\"])\nchi2, p_chi, gl, esperado = stats.chi2_contingency(tabla)\nprint(f\"chi² = {chi2:.2f}, gl = {gl}, p = {p_chi:.3f}\")\ntabla\n",
            'check' => <<<'PY'
import pandas as pd
from scipy import stats as _st
_v = pd.read_csv("ventas.csv")
_t = pd.crosstab(_v["region"], _v["categoria"])
_r = _st.chi2_contingency(_t)
assert tabla.shape == (4, 3) and int(tabla.values.sum()) == 300, "tabla debe contar las 300 ventas en 4 regiones × 3 categorías."
assert int(gl) == 6, "Con 4 × 3 hay (4−1)×(3−1) = 6 grados de libertad."
assert abs(float(p_chi) - float(_r[1])) < 1e-9, f"p_chi debería ser {_r[1]:.4f}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🥾 Bootstrap: intervalo de confianza',
            'html' => <<<'HTML'
<p>Estima un intervalo de confianza del 95% para el <b>ticket promedio</b> (media de <code>total</code>) con bootstrap:</p>
<ol>
  <li><code>rng = np.random.default_rng(42)</code></li>
  <li>Repite 3,000 veces: toma una muestra <b>con reemplazo</b> del mismo tamaño (<code>rng.choice(totales, size=len(totales), replace=True)</code>) y guarda su media</li>
  <li><code>ic</code> = tupla con los percentiles 2.5 y 97.5 de esas medias (<code>np.percentile</code>)</li>
</ol>
HTML,
            'starter' => "import numpy as np\nimport pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\ntotales = (ventas[\"unidades\"] * ventas[\"precio_unitario\"]).to_numpy()\n\n",
            'hint' => '',
            'solution' => "import numpy as np\nimport pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\ntotales = (ventas[\"unidades\"] * ventas[\"precio_unitario\"]).to_numpy()\n\nrng = np.random.default_rng(42)\nmedias = [rng.choice(totales, size=len(totales), replace=True).mean() for _ in range(3000)]\nic = (np.percentile(medias, 2.5), np.percentile(medias, 97.5))\nprint(f\"Media: {totales.mean():.2f} · IC 95%: {ic[0]:.2f} – {ic[1]:.2f}\")\n",
            'check' => <<<'PY'
import numpy as np
_rng = np.random.default_rng(123)
_m = np.array([_rng.choice(totales, len(totales)).mean() for _ in range(2000)])
_ancho = np.percentile(_m, 97.5) - np.percentile(_m, 2.5)
assert len(ic) == 2 and ic[0] < ic[1], "ic debe ser una tupla (inferior, superior)."
assert ic[0] < totales.mean() < ic[1], "El intervalo debe contener la media observada."
assert 0.8 * _ancho < ic[1] - ic[0] < 1.25 * _ancho, f"El ancho del intervalo ({ic[1] - ic[0]:.1f}) no es razonable; debería rondar {_ancho:.1f}. ¿Usaste replace=True y el tamaño completo?"
PY,
            'success' => '🔥 Con bootstrap puedes dar un rango de confianza para CUALQUIER métrica, no solo el promedio.',
        ],
    ],
];
