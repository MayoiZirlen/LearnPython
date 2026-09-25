<?php
return [
    'title' => 'Clasificación: ¿comprará o no?',
    'icon' => '🤖',
    'minutes' => 16,
    'summary' => 'Regresión logística, árboles de decisión, matriz de confusión, precisión y recall.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Predecir categorías',
            'html' => <<<'HTML'
<p>La regresión predice <b>números</b>. La <b>clasificación</b> predice <b>categorías</b>: ¿comprará o no?, ¿fraude o no?, ¿cliente premium?</p>
<p>Evaluar un clasificador con solo el <b>accuracy</b> (porcentaje de aciertos) es peligroso. La <b>matriz de confusión</b> muestra el detalle:</p>
<table>
<tr><th></th><th>Predijo NO</th><th>Predijo SÍ</th></tr>
<tr><th>Realmente NO</th><td>✅ Verdadero negativo</td><td>❌ Falso positivo</td></tr>
<tr><th>Realmente SÍ</th><td>❌ Falso negativo</td><td>✅ Verdadero positivo</td></tr>
</table>
<ul>
  <li><b>Precisión</b>: de los que predije SÍ, ¿cuántos eran SÍ? (evita falsas alarmas)</li>
  <li><b>Recall</b>: de todos los SÍ reales, ¿cuántos encontré? (evita que se escapen)</li>
</ul>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Los datos: visitantes de una tienda en línea',
            'code' => <<<'PY'
import numpy as np
import pandas as pd

rng = np.random.default_rng(3)
n = 800
clientes = pd.DataFrame({
    "visitas": rng.integers(1, 30, n),
    "minutos": rng.gamma(2, 6, n).round(1),
    "carrito": rng.integers(0, 6, n),
})
logit = -5 + 0.12 * clientes["visitas"] + 0.08 * clientes["minutos"] + 0.7 * clientes["carrito"]
clientes["compro"] = (rng.random(n) < 1 / (1 + np.exp(-logit))).astype(int)

print(clientes["compro"].value_counts(normalize=True).round(2))
clientes.head()
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'En un problema de fraude, el 99% de las transacciones son legítimas. Un modelo que SIEMPRE dice "no es fraude" tiene accuracy de…',
            'options' => ['1%', '50%', '99%', '0%'],
            'answer' => 2,
            'explain' => '¡99%! Y es inútil: no detecta ni un fraude (recall = 0). Por eso con clases desbalanceadas hay que mirar precisión y recall.',
        ],
        [
            'type' => 'exercise',
            'title' => '📈 Regresión logística',
            'html' => '<p>Separa <code>X</code> (visitas, minutos, carrito) y <code>y</code> (compro) con <code>train_test_split(X, y, test_size=0.3, random_state=0, stratify=y)</code>. Entrena <code>modelo = LogisticRegression(max_iter=1000)</code> y guarda en <code>accuracy</code> su puntaje en el conjunto de prueba.</p>',
            'starter' => "import numpy as np\nimport pandas as pd\nfrom sklearn.linear_model import LogisticRegression\nfrom sklearn.model_selection import train_test_split\n\nrng = np.random.default_rng(3)\nn = 800\nclientes = pd.DataFrame({\n    \"visitas\": rng.integers(1, 30, n),\n    \"minutos\": rng.gamma(2, 6, n).round(1),\n    \"carrito\": rng.integers(0, 6, n),\n})\nlogit = -5 + 0.12 * clientes[\"visitas\"] + 0.08 * clientes[\"minutos\"] + 0.7 * clientes[\"carrito\"]\nclientes[\"compro\"] = (rng.random(n) < 1 / (1 + np.exp(-logit))).astype(int)\n\n",
            'hint' => '',
            'solution' => "import numpy as np\nimport pandas as pd\nfrom sklearn.linear_model import LogisticRegression\nfrom sklearn.model_selection import train_test_split\n\nrng = np.random.default_rng(3)\nn = 800\nclientes = pd.DataFrame({\n    \"visitas\": rng.integers(1, 30, n),\n    \"minutos\": rng.gamma(2, 6, n).round(1),\n    \"carrito\": rng.integers(0, 6, n),\n})\nlogit = -5 + 0.12 * clientes[\"visitas\"] + 0.08 * clientes[\"minutos\"] + 0.7 * clientes[\"carrito\"]\nclientes[\"compro\"] = (rng.random(n) < 1 / (1 + np.exp(-logit))).astype(int)\n\nX = clientes[[\"visitas\", \"minutos\", \"carrito\"]]\ny = clientes[\"compro\"]\nX_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.3, random_state=0, stratify=y)\nmodelo = LogisticRegression(max_iter=1000)\nmodelo.fit(X_train, y_train)\naccuracy = modelo.score(X_test, y_test)\nprint(f\"Accuracy: {accuracy:.3f}\")\n",
            'check' => <<<'PY'
from sklearn.linear_model import LogisticRegression as _LR
from sklearn.model_selection import train_test_split as _tts
_X = clientes[["visitas", "minutos", "carrito"]]
_a, _b, _c, _d = _tts(_X, clientes["compro"], test_size=0.3, random_state=0, stratify=clientes["compro"])
_acc = _LR(max_iter=1000).fit(_a, _c).score(_b, _d)
assert abs(accuracy - _acc) < 1e-9, f"accuracy debería ser {_acc:.4f}. Revisa test_size, random_state y stratify."
assert accuracy > 0.7, "El modelo debería superar 70% de accuracy."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🧩 Matriz de confusión, precisión y recall',
            'html' => '<p>Con el mismo modelo, predice sobre <code>X_test</code> y calcula <code>matriz</code> (<code>confusion_matrix</code>), <code>precision</code> (<code>precision_score</code>) y <code>recall</code> (<code>recall_score</code>) de <code>sklearn.metrics</code>. Después guarda en <code>falsos_negativos</code> cuántos compradores reales el modelo NO detectó (sácalo de la matriz).</p>',
            'starter' => "import numpy as np\nimport pandas as pd\nfrom sklearn.linear_model import LogisticRegression\nfrom sklearn.model_selection import train_test_split\nfrom sklearn.metrics import confusion_matrix, precision_score, recall_score\n\nrng = np.random.default_rng(3)\nn = 800\nclientes = pd.DataFrame({\n    \"visitas\": rng.integers(1, 30, n),\n    \"minutos\": rng.gamma(2, 6, n).round(1),\n    \"carrito\": rng.integers(0, 6, n),\n})\nlogit = -5 + 0.12 * clientes[\"visitas\"] + 0.08 * clientes[\"minutos\"] + 0.7 * clientes[\"carrito\"]\nclientes[\"compro\"] = (rng.random(n) < 1 / (1 + np.exp(-logit))).astype(int)\nX = clientes[[\"visitas\", \"minutos\", \"carrito\"]]\ny = clientes[\"compro\"]\nX_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.3, random_state=0, stratify=y)\nmodelo = LogisticRegression(max_iter=1000).fit(X_train, y_train)\n\n",
            'hint' => '',
            'solution' => "import numpy as np\nimport pandas as pd\nfrom sklearn.linear_model import LogisticRegression\nfrom sklearn.model_selection import train_test_split\nfrom sklearn.metrics import confusion_matrix, precision_score, recall_score\n\nrng = np.random.default_rng(3)\nn = 800\nclientes = pd.DataFrame({\n    \"visitas\": rng.integers(1, 30, n),\n    \"minutos\": rng.gamma(2, 6, n).round(1),\n    \"carrito\": rng.integers(0, 6, n),\n})\nlogit = -5 + 0.12 * clientes[\"visitas\"] + 0.08 * clientes[\"minutos\"] + 0.7 * clientes[\"carrito\"]\nclientes[\"compro\"] = (rng.random(n) < 1 / (1 + np.exp(-logit))).astype(int)\nX = clientes[[\"visitas\", \"minutos\", \"carrito\"]]\ny = clientes[\"compro\"]\nX_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.3, random_state=0, stratify=y)\nmodelo = LogisticRegression(max_iter=1000).fit(X_train, y_train)\n\npred = modelo.predict(X_test)\nmatriz = confusion_matrix(y_test, pred)\nprecision = precision_score(y_test, pred)\nrecall = recall_score(y_test, pred)\nfalsos_negativos = int(matriz[1, 0])\nprint(matriz)\nprint(f\"Precisión: {precision:.2f} · Recall: {recall:.2f} · Se escaparon {falsos_negativos} compradores\")\n",
            'check' => <<<'PY'
from sklearn.metrics import confusion_matrix as _cm, precision_score as _ps, recall_score as _rs
_p = modelo.predict(X_test)
_m = _cm(y_test, _p)
assert (matriz == _m).all(), "matriz no coincide con confusion_matrix(y_test, pred)."
assert abs(precision - _ps(y_test, _p)) < 1e-9 and abs(recall - _rs(y_test, _p)) < 1e-9, "precision o recall no coinciden."
assert int(falsos_negativos) == int(_m[1, 0]), "Los falsos negativos están en la fila 'real = 1', columna 'predijo = 0': matriz[1, 0]."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🌳 Duelo: logística vs. árbol',
            'html' => '<p>Entrena un <code>DecisionTreeClassifier(max_depth=3, random_state=0)</code> con los mismos datos de entrenamiento. Guarda los accuracy de prueba en <code>acc_log</code> y <code>acc_arbol</code>, y en <code>ganador</code> el texto <code>"logistica"</code> o <code>"arbol"</code> según quién gane (si empatan, <code>"logistica"</code>).</p>',
            'starter' => "import numpy as np\nimport pandas as pd\nfrom sklearn.linear_model import LogisticRegression\nfrom sklearn.tree import DecisionTreeClassifier\nfrom sklearn.model_selection import train_test_split\n\nrng = np.random.default_rng(3)\nn = 800\nclientes = pd.DataFrame({\n    \"visitas\": rng.integers(1, 30, n),\n    \"minutos\": rng.gamma(2, 6, n).round(1),\n    \"carrito\": rng.integers(0, 6, n),\n})\nlogit = -5 + 0.12 * clientes[\"visitas\"] + 0.08 * clientes[\"minutos\"] + 0.7 * clientes[\"carrito\"]\nclientes[\"compro\"] = (rng.random(n) < 1 / (1 + np.exp(-logit))).astype(int)\nX = clientes[[\"visitas\", \"minutos\", \"carrito\"]]\ny = clientes[\"compro\"]\nX_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.3, random_state=0, stratify=y)\n\n",
            'hint' => '',
            'solution' => "import numpy as np\nimport pandas as pd\nfrom sklearn.linear_model import LogisticRegression\nfrom sklearn.tree import DecisionTreeClassifier\nfrom sklearn.model_selection import train_test_split\n\nrng = np.random.default_rng(3)\nn = 800\nclientes = pd.DataFrame({\n    \"visitas\": rng.integers(1, 30, n),\n    \"minutos\": rng.gamma(2, 6, n).round(1),\n    \"carrito\": rng.integers(0, 6, n),\n})\nlogit = -5 + 0.12 * clientes[\"visitas\"] + 0.08 * clientes[\"minutos\"] + 0.7 * clientes[\"carrito\"]\nclientes[\"compro\"] = (rng.random(n) < 1 / (1 + np.exp(-logit))).astype(int)\nX = clientes[[\"visitas\", \"minutos\", \"carrito\"]]\ny = clientes[\"compro\"]\nX_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.3, random_state=0, stratify=y)\n\nacc_log = LogisticRegression(max_iter=1000).fit(X_train, y_train).score(X_test, y_test)\nacc_arbol = DecisionTreeClassifier(max_depth=3, random_state=0).fit(X_train, y_train).score(X_test, y_test)\nganador = \"arbol\" if acc_arbol > acc_log else \"logistica\"\nprint(f\"Logística: {acc_log:.3f} · Árbol: {acc_arbol:.3f} → gana {ganador}\")\n",
            'check' => <<<'PY'
from sklearn.linear_model import LogisticRegression as _LR
from sklearn.tree import DecisionTreeClassifier as _DT
_l = _LR(max_iter=1000).fit(X_train, y_train).score(X_test, y_test)
_a = _DT(max_depth=3, random_state=0).fit(X_train, y_train).score(X_test, y_test)
assert abs(acc_log - _l) < 1e-9 and abs(acc_arbol - _a) < 1e-9, "Los accuracy no coinciden. ¿max_depth=3 y random_state=0?"
assert ganador == ("arbol" if _a > _l else "logistica"), "ganador no es correcto."
PY,
            'success' => '🤖 Comparar modelos con los mismos datos de prueba es exactamente lo que hace un científico de datos.',
        ],
    ],
];
