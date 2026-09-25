<?php
return [
    'title' => 'Texto y expresiones regulares',
    'icon' => '🔤',
    'minutes' => 12,
    'summary' => 'Extrae dominios, limpia teléfonos y encuentra códigos con regex.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'El accesor .str',
            'html' => <<<'HTML'
<p>Todos los métodos de texto que conoces funcionan sobre una columna entera anteponiendo <code>.str</code>:</p>
<pre>df["correo"].str.lower()
df["correo"].str.split("@").str[1]      # lo que va después de @
df["nombre"].str.len()
df["producto"].str.contains("lap", case=False)</pre>
<p>Y para patrones más complejos existen las <b>expresiones regulares</b> (regex): un mini-lenguaje para describir texto.</p>
<table>
<tr><th>Símbolo</th><th>Significa</th><th>Ejemplo</th></tr>
<tr><td><code>\d</code></td><td>un dígito</td><td><code>\d\d\d</code> → "123"</td></tr>
<tr><td><code>\D</code></td><td>cualquier cosa que NO sea dígito</td><td>guiones, espacios, paréntesis…</td></tr>
<tr><td><code>+</code></td><td>uno o más del anterior</td><td><code>\d+</code> → "2025"</td></tr>
<tr><td><code>[A-Z]</code></td><td>una letra mayúscula</td><td><code>[A-Z]-\d+</code> → "A-1023"</td></tr>
<tr><td><code>( )</code></td><td>la parte que quieres <b>extraer</b></td><td><code>#(\d+)</code> → "1023"</td></tr>
</table>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Magia con texto',
            'code' => <<<'PY'
import pandas as pd

df = pd.DataFrame({
    "cliente": ["ana lópez", "LUIS PÉREZ", "  carla ruiz "],
    "correo": ["Ana@Gmail.com", "luis@empresa.mx", "carla@gmail.com"],
    "nota": ["Pedido #A-1023 urgente", "pedido #B-77", "sin pedido"],
})

df["cliente"] = df["cliente"].str.strip().str.title()
df["dominio"] = df["correo"].str.lower().str.split("@").str[1]
df["pedido"] = df["nota"].str.extract(r"#([A-Z]-\d+)", expand=False)
df
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📧 ¿De qué dominio son?',
            'html' => '<p>Crea la columna <code>dominio</code> (lo que va después de la @, en minúsculas) y guarda en <code>por_dominio</code> cuántos clientes hay de cada dominio (<code>value_counts()</code>).</p>',
            'starter' => "import pandas as pd\n\nclientes = pd.DataFrame({\"correo\": [\n    \"ana@gmail.com\", \"LUIS@Hotmail.com\", \"carla@empresa.mx\", \"pedro@gmail.com\",\n    \"sofia@GMAIL.com\", \"diego@empresa.mx\", \"elena@outlook.com\",\n]})\n\n",
            'hint' => '<code>clientes["dominio"] = clientes["correo"].str.lower().str.split("@").str[1]</code>',
            'solution' => "import pandas as pd\n\nclientes = pd.DataFrame({\"correo\": [\n    \"ana@gmail.com\", \"LUIS@Hotmail.com\", \"carla@empresa.mx\", \"pedro@gmail.com\",\n    \"sofia@GMAIL.com\", \"diego@empresa.mx\", \"elena@outlook.com\",\n]})\n\nclientes[\"dominio\"] = clientes[\"correo\"].str.lower().str.split(\"@\").str[1]\npor_dominio = clientes[\"dominio\"].value_counts()\npor_dominio\n",
            'check' => <<<'PY'
assert dict(por_dominio) == {"gmail.com": 3, "empresa.mx": 2, "hotmail.com": 1, "outlook.com": 1}, f"Obtuviste {dict(por_dominio)}. ¿Pasaste todo a minúsculas?"
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📞 Teléfonos uniformes',
            'html' => '<p>Los teléfonos vienen escritos de mil formas. Crea <code>tel_limpio</code> dejando <b>solo los dígitos</b>: usa <code>.str.replace(r"\D", "", regex=True)</code>. Después crea <code>valido</code>: <code>True</code> si el teléfono limpio tiene exactamente 10 dígitos (<code>.str.len() == 10</code>).</p>',
            'starter' => "import pandas as pd\n\ndf = pd.DataFrame({\"telefono\": [\"(81) 1234-5678\", \"55 9876 5432\", \"+52 33-1111-2222\", \"12345\", \"998.765.4321\"]})\n\n",
            'hint' => ['<code>df["tel_limpio"] = df["telefono"].str.replace(r"\D", "", regex=True)</code>', '<code>df["valido"] = df["tel_limpio"].str.len() == 10</code>'],
            'solution' => "import pandas as pd\n\ndf = pd.DataFrame({\"telefono\": [\"(81) 1234-5678\", \"55 9876 5432\", \"+52 33-1111-2222\", \"12345\", \"998.765.4321\"]})\n\ndf[\"tel_limpio\"] = df[\"telefono\"].str.replace(r\"\\D\", \"\", regex=True)\ndf[\"valido\"] = df[\"tel_limpio\"].str.len() == 10\ndf\n",
            'check' => <<<'PY'
assert list(df["tel_limpio"]) == ["8112345678", "5598765432", "523311112222", "12345", "9987654321"], f"tel_limpio quedó: {list(df['tel_limpio'])}"
assert list(df["valido"]) == [True, True, False, False, True], "valido debe ser True solo cuando hay exactamente 10 dígitos."
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué extrae <code>str.extract(r"(\d+) kg")</code> del texto <code>"Caja de 25 kg"</code>?',
            'options' => ['"Caja"', '"25 kg"', '"25"', '"kg"'],
            'answer' => 2,
            'explain' => 'Solo se extrae lo que está entre paréntesis: <code>(\d+)</code> = uno o más dígitos → <b>"25"</b>.',
        ],
        [
            'type' => 'exercise',
            'title' => '🔍 Extrae el número de pedido',
            'html' => '<p>De la columna <code>nota</code>, extrae el código de pedido (formato: una letra mayúscula, guion y números, después de <code>#</code>) en la columna <code>pedido</code>. Después cuenta en <code>sin_pedido</code> cuántas notas no tienen código.</p>',
            'starter' => "import pandas as pd\n\ndf = pd.DataFrame({\"nota\": [\n    \"Cliente pide cambio #A-1023\", \"Entregar hoy #B-77 urgente\", \"Llamar al cliente\",\n    \"Factura del pedido #C-5\", \"sin datos\", \"#A-99 devuelto\",\n]})\n\n",
            'hint' => ['El patrón es <code>r"#([A-Z]-\d+)"</code>', '<code>df["pedido"] = df["nota"].str.extract(r"#([A-Z]-\d+)", expand=False)</code> y <code>sin_pedido = df["pedido"].isna().sum()</code>'],
            'solution' => "import pandas as pd\n\ndf = pd.DataFrame({\"nota\": [\n    \"Cliente pide cambio #A-1023\", \"Entregar hoy #B-77 urgente\", \"Llamar al cliente\",\n    \"Factura del pedido #C-5\", \"sin datos\", \"#A-99 devuelto\",\n]})\n\ndf[\"pedido\"] = df[\"nota\"].str.extract(r\"#([A-Z]-\\d+)\", expand=False)\nsin_pedido = df[\"pedido\"].isna().sum()\nprint(\"Sin pedido:\", sin_pedido)\ndf\n",
            'check' => <<<'PY'
import pandas as pd
_e = ["A-1023", "B-77", None, "C-5", None, "A-99"]
_g = [None if pd.isna(x) else x for x in df["pedido"]]
assert _g == _e, f"pedido quedó así: {_g}"
assert int(sin_pedido) == 2, "Hay 2 notas sin código de pedido."
PY,
            'success' => '¡Las regex dan miedo al principio, pero son un superpoder para limpiar texto! 🦸',
        ],
    ],
];
