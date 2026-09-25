<?php
return [
    'title' => 'Valores atípicos y correlación',
    'icon' => '🎯',
    'minutes' => 13,
    'summary' => 'Detecta datos raros con IQR y z-score, y mide relaciones entre variables.',
    'steps' => [
        [
            'type' => 'text',
            'title' => '¿Error o descubrimiento?',
            'html' => <<<'HTML'
<p>Un <b>valor atípico</b> (outlier) es un dato muy diferente al resto: una venta de $50,000 cuando la mayoría son de $500. Puede ser un <b>error de captura</b>… o tu <b>mejor cliente</b>. Hay que encontrarlos y luego decidir.</p>
<p><b>Método IQR</b> (el del diagrama de caja):</p>
<pre>q1 = serie.quantile(0.25)      # 25% de los datos está por debajo
q3 = serie.quantile(0.75)      # 75% está por debajo
iqr = q3 - q1                  # rango intercuartil
limite_alto = q3 + 1.5 * iqr
limite_bajo = q1 - 1.5 * iqr</pre>
<p><b>Método z-score</b>: cuántas desviaciones estándar se aleja cada dato del promedio. Más de 3 suele considerarse extremo.</p>
<pre>z = (serie - serie.mean()) / serie.std()</pre>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'El diagrama de caja',
            'html' => '<p>Los puntitos fuera de los "bigotes" son los atípicos según el método IQR.</p>',
            'code' => <<<'PY'
import pandas as pd
import matplotlib.pyplot as plt

ventas = pd.read_csv("ventas.csv")
ventas["total"] = ventas["unidades"] * ventas["precio_unitario"]

ventas.boxplot(column="total", by="categoria", figsize=(8, 4))
plt.suptitle("")
plt.title("Distribución del total por categoría")
plt.ylabel("Total ($)")
plt.show()
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📦 Cazando atípicos con IQR',
            'html' => '<p>Calcula <code>q1</code>, <code>q3</code>, <code>iqr</code> y <code>limite_alto</code> para la columna <code>total</code>. Después crea <code>atipicos</code>: las ventas cuyo total supera el límite alto.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => ['<code>q1 = ventas["total"].quantile(0.25)</code> (igual para q3 con 0.75)', '<code>atipicos = ventas[ventas["total"] > limite_alto]</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nq1 = ventas[\"total\"].quantile(0.25)\nq3 = ventas[\"total\"].quantile(0.75)\niqr = q3 - q1\nlimite_alto = q3 + 1.5 * iqr\natipicos = ventas[ventas[\"total\"] > limite_alto]\nprint(f\"Límite: {limite_alto:.2f} → {len(atipicos)} ventas atípicas\")\natipicos[\"producto\"].value_counts()\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_t = _v["unidades"] * _v["precio_unitario"]
_q1, _q3 = _t.quantile(0.25), _t.quantile(0.75)
_lim = _q3 + 1.5 * (_q3 - _q1)
assert abs(iqr - (_q3 - _q1)) < 1e-6, "iqr debe ser q3 − q1."
assert abs(limite_alto - _lim) < 1e-6, "limite_alto debe ser q3 + 1.5 × iqr."
assert len(atipicos) == int((_t > _lim).sum()), f"Deberían ser {int((_t > _lim).sum())} ventas atípicas."
PY,
            'success' => '¿Viste qué producto aparece en casi todos los atípicos? No son errores: ¡son las laptops! 💻 Por eso hay que investigar antes de borrar.',
        ],
        [
            'type' => 'exercise',
            'title' => '📏 Z-score',
            'html' => '<p>Crea la columna <code>z</code> con el z-score del <code>total</code> y cuenta en <code>extremos</code> cuántas ventas tienen un z-score (en valor absoluto) mayor que 3. Usa <code>.abs()</code>.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => ['<code>ventas["z"] = (ventas["total"] - ventas["total"].mean()) / ventas["total"].std()</code>', '<code>extremos = (ventas["z"].abs() > 3).sum()</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nventas[\"z\"] = (ventas[\"total\"] - ventas[\"total\"].mean()) / ventas[\"total\"].std()\nextremos = (ventas[\"z\"].abs() > 3).sum()\nprint(\"Ventas extremas:\", extremos)\nventas.nlargest(5, \"z\")[[\"producto\", \"total\", \"z\"]]\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_t = _v["unidades"] * _v["precio_unitario"]
_z = (_t - _t.mean()) / _t.std()
assert ((ventas["z"] - _z).abs() < 1e-9).all(), "La columna z no coincide: (total − promedio) / desviación estándar."
assert int(extremos) == int((_z.abs() > 3).sum()), f"Deberían ser {int((_z.abs() > 3).sum())} ventas extremas."
PY,
        ],
        [
            'type' => 'text',
            'title' => 'Correlación: ¿se mueven juntas?',
            'html' => <<<'HTML'
<p>La <b>correlación</b> mide qué tanto dos variables se mueven juntas, de −1 a 1:</p>
<ul>
  <li><b>Cerca de 1</b>: cuando una sube, la otra también (estatura y peso).</li>
  <li><b>Cerca de −1</b>: cuando una sube, la otra baja (precio y cantidad vendida).</li>
  <li><b>Cerca de 0</b>: no hay relación lineal.</li>
</ul>
<pre>df["a"].corr(df["b"])     # entre dos columnas
df.corr(numeric_only=True)  # matriz con todas</pre>
<div class="warn">⚠️ <b>Correlación no es causalidad.</b> Las ventas de helado y los ahogamientos están correlacionados… porque ambos suben en verano. 🍦🏖️</div>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '🌧️ ¿Calor y lluvia van juntos?',
            'html' => '<p>Con <code>clima.csv</code>, calcula <code>corr_general</code>: la correlación entre <code>temperatura</code> y <code>lluvia_mm</code> para todas las filas. Luego crea <code>corr_ciudad</code>: una Series con esa correlación <b>para cada ciudad</b> (pista: agrupa y usa <code>apply</code> con una lambda).</p>',
            'starter' => "import pandas as pd\n\nclima = pd.read_csv(\"clima.csv\")\n\n",
            'hint' => ['<code>corr_general = clima["temperatura"].corr(clima["lluvia_mm"])</code>', '<code>corr_ciudad = clima.groupby("ciudad")[["temperatura", "lluvia_mm"]].apply(lambda g: g["temperatura"].corr(g["lluvia_mm"]))</code>'],
            'solution' => "import pandas as pd\n\nclima = pd.read_csv(\"clima.csv\")\n\ncorr_general = clima[\"temperatura\"].corr(clima[\"lluvia_mm\"])\ncorr_ciudad = clima.groupby(\"ciudad\")[[\"temperatura\", \"lluvia_mm\"]].apply(lambda g: g[\"temperatura\"].corr(g[\"lluvia_mm\"]))\nprint(f\"General: {corr_general:.2f}\")\ncorr_ciudad.round(2)\n",
            'check' => <<<'PY'
import pandas as pd
_c = pd.read_csv("clima.csv")
assert abs(corr_general - _c["temperatura"].corr(_c["lluvia_mm"])) < 1e-9, "corr_general no coincide."
_e = {k: g["temperatura"].corr(g["lluvia_mm"]) for k, g in _c.groupby("ciudad")}
assert len(corr_ciudad) == 4, "corr_ciudad debe tener un valor por ciudad."
for _k, _val in _e.items():
    assert abs(float(corr_ciudad[_k]) - _val) < 1e-9, f"La correlación de {_k} no coincide."
PY,
        ],
    ],
];
