<?php
return [
    'title' => 'Tu primer modelo predictivo',
    'icon' => '🔮',
    'minutes' => 14,
    'summary' => 'Regresión lineal con scikit-learn: entrenar, probar y predecir.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Predecir el futuro (más o menos)',
            'html' => <<<'HTML'
<p>Un <b>modelo</b> aprende una relación a partir de datos del pasado para <b>predecir</b> valores nuevos. La <b>regresión lineal</b> busca la mejor línea recta:</p>
<pre>ventas ≈ pendiente × publicidad + intercepto</pre>
<ul>
  <li><b>X</b> (features): lo que usas para predecir → gasto en publicidad</li>
  <li><b>y</b> (target): lo que quieres predecir → ventas</li>
</ul>
<p>Con <b>scikit-learn</b> todos los modelos se usan igual:</p>
<pre>from sklearn.linear_model import LinearRegression
modelo = LinearRegression()
modelo.fit(X, y)            # 1. entrenar
modelo.predict(X_nuevos)    # 2. predecir</pre>
<div class="tip">💡 <code>X</code> siempre va con <b>doble corchete</b> (<code>df[["publicidad"]]</code>) porque puede tener varias columnas; <code>y</code> con uno solo.</div>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Datos de campañas',
            'html' => '<p>Generamos datos de 60 campañas de marketing (publicidad y ventas). Mira la nube de puntos: se ve una tendencia clara.</p>',
            'code' => <<<'PY'
import numpy as np
import pandas as pd
import matplotlib.pyplot as plt

rng = np.random.default_rng(7)
publicidad = rng.uniform(500, 10000, 60).round(0)
ventas = (3.2 * publicidad + 5000 + rng.normal(0, 2500, 60)).round(0)
campañas = pd.DataFrame({"publicidad": publicidad, "ventas": ventas})

campañas.plot(kind="scatter", x="publicidad", y="ventas", color="#e5191c", figsize=(7, 4))
plt.title("¿Más publicidad = más ventas?")
plt.show()
campañas.head()
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📐 Entrena el modelo',
            'html' => '<p>Entrena una <code>LinearRegression</code> llamada <code>modelo</code> con <code>X = campañas[["publicidad"]]</code> y <code>y = campañas["ventas"]</code>. Guarda en <code>pendiente</code> el coeficiente (<code>modelo.coef_[0]</code>): cuánto suben las ventas por cada $1 de publicidad.</p>',
            'starter' => "import numpy as np\nimport pandas as pd\nfrom sklearn.linear_model import LinearRegression\n\nrng = np.random.default_rng(7)\npublicidad = rng.uniform(500, 10000, 60).round(0)\nventas = (3.2 * publicidad + 5000 + rng.normal(0, 2500, 60)).round(0)\ncampañas = pd.DataFrame({\"publicidad\": publicidad, \"ventas\": ventas})\n\n",
            'hint' => ['<code>X = campañas[["publicidad"]]</code> y <code>y = campañas["ventas"]</code>', '<code>modelo = LinearRegression()</code>, <code>modelo.fit(X, y)</code>, <code>pendiente = modelo.coef_[0]</code>'],
            'solution' => "import numpy as np\nimport pandas as pd\nfrom sklearn.linear_model import LinearRegression\n\nrng = np.random.default_rng(7)\npublicidad = rng.uniform(500, 10000, 60).round(0)\nventas = (3.2 * publicidad + 5000 + rng.normal(0, 2500, 60)).round(0)\ncampañas = pd.DataFrame({\"publicidad\": publicidad, \"ventas\": ventas})\n\nX = campañas[[\"publicidad\"]]\ny = campañas[\"ventas\"]\nmodelo = LinearRegression()\nmodelo.fit(X, y)\npendiente = modelo.coef_[0]\nprint(f\"Por cada $1 en publicidad, las ventas suben \${pendiente:.2f}\")\nprint(f\"Intercepto: {modelo.intercept_:.0f}\")\n",
            'check' => <<<'PY'
import numpy as np
from sklearn.linear_model import LinearRegression
assert isinstance(modelo, LinearRegression), "modelo debe ser un LinearRegression."
assert hasattr(modelo, "coef_"), "El modelo no está entrenado: usa modelo.fit(X, y)."
_e = np.polyfit(campañas["publicidad"], campañas["ventas"], 1)[0]
assert abs(pendiente - _e) < 1e-6, f"pendiente debería ser {_e:.4f}."
assert 2.8 < pendiente < 3.6, "La pendiente debería estar cerca de 3.2."
PY,
            'success' => 'El modelo "descubrió" que cada $1 de publicidad trae unos $3.2 de ventas. ¡Era justo la regla con la que generamos los datos! 🧠',
        ],
        [
            'type' => 'text',
            'title' => 'Entrenar y examinar por separado',
            'html' => <<<'HTML'
<p>Si evalúas el modelo con los mismos datos con los que aprendió, es como hacer un examen con las respuestas en la mano. 📝 La práctica correcta es <b>separar</b>:</p>
<pre>from sklearn.model_selection import train_test_split
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.25, random_state=42)

modelo.fit(X_train, y_train)          # aprende con el 75%
r2 = modelo.score(X_test, y_test)     # se examina con el 25% que nunca vio</pre>
<p>El <b>R²</b> va de 0 a 1: qué porcentaje de la variación de las ventas explica el modelo. 0.9 = explica el 90%.</p>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '📝 Examen del modelo',
            'html' => '<p>Separa los datos con <code>train_test_split(X, y, test_size=0.25, random_state=42)</code>, entrena <code>modelo</code> solo con el entrenamiento y guarda en <code>r2</code> su puntaje en los datos de prueba. Después predice cuánto venderías con $5,000 de publicidad y guárdalo en <code>prediccion</code> (un número).</p>',
            'starter' => "import numpy as np\nimport pandas as pd\nfrom sklearn.linear_model import LinearRegression\nfrom sklearn.model_selection import train_test_split\n\nrng = np.random.default_rng(7)\npublicidad = rng.uniform(500, 10000, 60).round(0)\nventas = (3.2 * publicidad + 5000 + rng.normal(0, 2500, 60)).round(0)\ncampañas = pd.DataFrame({\"publicidad\": publicidad, \"ventas\": ventas})\nX = campañas[[\"publicidad\"]]\ny = campañas[\"ventas\"]\n\n",
            'hint' => ['<code>X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.25, random_state=42)</code>', '<code>r2 = modelo.score(X_test, y_test)</code>', '<code>prediccion = modelo.predict(pd.DataFrame({"publicidad": [5000]}))[0]</code>'],
            'solution' => "import numpy as np\nimport pandas as pd\nfrom sklearn.linear_model import LinearRegression\nfrom sklearn.model_selection import train_test_split\n\nrng = np.random.default_rng(7)\npublicidad = rng.uniform(500, 10000, 60).round(0)\nventas = (3.2 * publicidad + 5000 + rng.normal(0, 2500, 60)).round(0)\ncampañas = pd.DataFrame({\"publicidad\": publicidad, \"ventas\": ventas})\nX = campañas[[\"publicidad\"]]\ny = campañas[\"ventas\"]\n\nX_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.25, random_state=42)\nmodelo = LinearRegression()\nmodelo.fit(X_train, y_train)\nr2 = modelo.score(X_test, y_test)\nprediccion = modelo.predict(pd.DataFrame({\"publicidad\": [5000]}))[0]\nprint(f\"R² en prueba: {r2:.3f}\")\nprint(f\"Con $5,000 de publicidad venderías ≈ \${prediccion:,.0f}\")\n",
            'check' => <<<'PY'
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression
_Xtr, _Xte, _ytr, _yte = train_test_split(X, y, test_size=0.25, random_state=42)
_m = LinearRegression().fit(_Xtr, _ytr)
assert abs(r2 - _m.score(_Xte, _yte)) < 1e-9, "r2 no coincide: entrena con X_train/y_train y evalúa con X_test/y_test (random_state=42)."
import pandas as pd
_p = _m.predict(pd.DataFrame({"publicidad": [5000]}))[0]
assert abs(float(prediccion) - float(_p)) < 1e-6, f"La predicción para 5000 debería ser {_p:,.2f}."
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'Tu modelo tiene R² = 0.99 con los datos de entrenamiento pero 0.40 con los de prueba. ¿Qué pasa?',
            'options' => ['El modelo es excelente', 'Sobreajuste: memorizó el entrenamiento y no generaliza', 'Faltan datos de prueba', 'Hay que usar más decimales'],
            'answer' => 1,
            'explain' => 'Es <b>sobreajuste</b> (overfitting): como un estudiante que memorizó el examen de práctica pero no entendió el tema. Por eso siempre evaluamos con datos que el modelo no vio.',
        ],
    ],
];
