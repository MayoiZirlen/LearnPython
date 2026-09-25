<?php
require __DIR__ . '/includes/bootstrap.php';

$snippets = [
    'Hola mundo' => "print(\"¡Hola, mundo! 🌎\")\n",
    'Explorar ventas' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nprint(ventas.shape)\nventas.head()\n",
    'Ventas por región' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\nventas.groupby(\"region\")[\"total\"].sum().sort_values(ascending=False)\n",
    'Gráfico de clima' => "import pandas as pd\nimport matplotlib.pyplot as plt\n\nclima = pd.read_csv(\"clima.csv\")\nfor ciudad, datos in clima.groupby(\"ciudad\"):\n    plt.plot(datos[\"num_mes\"], datos[\"temperatura\"], marker=\"o\", label=ciudad)\n\nplt.title(\"Temperatura promedio por mes\")\nplt.xlabel(\"Mes\")\nplt.ylabel(\"°C\")\nplt.legend()\nplt.show()\n",
    'Usar input()' => "nombre = input(\"¿Cómo te llamas? \")\nedad = int(input(\"¿Cuántos años tienes? \"))\nprint(f\"{nombre}, en 2050 tendrás {edad + 2050 - 2026} años\")\n",
];

$pageTitle = 'Laboratorio';
$page = 'playground';
$usesPython = true;
require __DIR__ . '/includes/header.php';
?>
<div class="playground-head">
  <div>
    <h1>🧪 Laboratorio</h1>
    <p class="muted">Tu espacio libre para experimentar. Tienes <b>numpy</b>, <b>pandas</b> y <b>matplotlib</b>, y estos archivos listos para leer:
      <code>ventas.csv</code>, <code>ventas_sucias.csv</code>, <code>clima.csv</code>.</p>
  </div>
  <div class="snippets">
    <?php foreach ($snippets as $name => $code): ?>
      <button class="chip" data-snippet="<?= e($code) ?>"><?= e($name) ?></button>
    <?php endforeach; ?>
  </div>
</div>
<div class="playground runner" data-runner data-persist="playground">
  <div class="pg-editor">
    <textarea class="code" data-code><?= e($snippets['Explorar ventas']) ?></textarea>
    <details class="inputs">
      <summary>⌨️ Entradas para <code>input()</code> (una por línea)</summary>
      <textarea data-inputs rows="3" placeholder="Ana&#10;25"></textarea>
    </details>
    <div class="runner-actions">
      <button class="btn btn-primary" data-run>▶ Ejecutar <kbd>Ctrl+Enter</kbd></button>
      <button class="btn btn-ghost" data-clear>🧹 Limpiar salida</button>
      <a class="btn btn-ghost" href="data/ventas.csv" download>⬇ ventas.csv</a>
    </div>
  </div>
  <div class="pg-output">
    <div class="output-title">Salida</div>
    <div class="output" data-output><span class="muted">Aquí aparecerá el resultado de tu código.</span></div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
