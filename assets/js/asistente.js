/* Asistente de Excel: el usuario elige acciones y el sitio escribe (y explica) el código Python.
 *
 * Idea: una "receta" = archivo + hoja + lista de pasos. Cada paso genera unas líneas de Python
 * con un comentario en español. El script completo se ejecuta con Pyodide y, en paralelo,
 * se toma una "foto" de la tabla después de cada paso para saber qué columnas ofrecer después.
 */
(function () {
  const raiz = document.getElementById('asistente');
  if (!raiz) return;
  const E = App.escape;
  const $ = (sel) => raiz.querySelector(sel);

  // ---------- Utilidades para escribir Python ----------
  const py = (v) => JSON.stringify(String(v));                 // literal de texto válido en Python
  const col = (n) => `df[${py(n)}]`;
  const lista = (xs) => `[${xs.map(py).join(', ')}]`;
  const ident = (n) => {
    let s = String(n).normalize('NFC').replace(/[^\p{L}\p{N}_]+/gu, '_').replace(/^_+|_+$/g, '');
    if (!s || /^\p{N}/u.test(s)) s = 'col_' + s;
    return s;
  };
  const num = (v) => (v === '' || v === null || v === undefined || isNaN(Number(v)) ? null : String(Number(v)));

  /** Convierte un valor escrito por el usuario al literal correcto según el tipo de columna. */
  function literal(valor, tipo) {
    if (valor === '' || valor === undefined || valor === null) return null;
    if (tipo === 'num') return num(valor);
    if (tipo === 'fecha') return `pd.Timestamp(${py(valor)})`;
    if (tipo === 'bool') return /^(true|verdadero|sí|si|1)$/i.test(valor) ? 'True' : 'False';
    return py(valor);
  }

  const OPS_AGR = [['sum', 'suma'], ['mean', 'promedio'], ['count', 'conteo'], ['max', 'máximo'], ['min', 'mínimo'], ['median', 'mediana']];
  const SUFIJO = { sum: 'suma', mean: 'promedio', count: 'conteo', max: 'max', min: 'min', median: 'mediana' };
  const MESES = '{1: "Ene", 2: "Feb", 3: "Mar", 4: "Abr", 5: "May", 6: "Jun", 7: "Jul", 8: "Ago", 9: "Sep", 10: "Oct", 11: "Nov", 12: "Dic"}';
  const DIAS = '{0: "Lunes", 1: "Martes", 2: "Miércoles", 3: "Jueves", 4: "Viernes", 5: "Sábado", 6: "Domingo"}';

  /*
   * Catálogo de acciones. Cada una tiene:
   *   campos: qué le preguntamos al usuario
   *   codigo(p, ctx): devuelve las líneas de Python (o null si faltan datos)
   *   ctx.tipo(nombreColumna) da el tipo de la columna en ese momento.
   */
  const ACCIONES = {
    // ---- Limpiar ----
    duplicados: {
      grupo: '🧹 Limpiar', icono: '👯', titulo: 'Quitar filas duplicadas', campos: [],
      codigo: () => ['# 👯 Quitar filas repetidas (se queda la primera aparición)', 'df = df.drop_duplicates()'],
    },
    faltantes: {
      grupo: '🧹 Limpiar', icono: '🕳️', titulo: 'Datos faltantes',
      campos: [
        { k: 'col', label: 'Columna', tipo: 'col', todas: true },
        { k: 'accion', label: 'Qué hacer', tipo: 'opciones', opciones: [['eliminar', 'Eliminar esas filas'], ['mediana', 'Rellenar con la mediana'], ['promedio', 'Rellenar con el promedio'], ['cero', 'Rellenar con 0'], ['valor', 'Rellenar con un valor…']] },
        { k: 'valor', label: 'Valor', tipo: 'texto', si: (p) => p.accion === 'valor' },
      ],
      codigo: (p, ctx) => {
        if (p.accion === 'eliminar') {
          return p.col === '*'
            ? ['# 🕳️ Eliminar las filas que tengan algún dato vacío', 'df = df.dropna()']
            : [`# 🕳️ Eliminar las filas donde "${p.col}" está vacía`, `df = df.dropna(subset=[${py(p.col)}])`];
        }
        if (p.col === '*') {
          if (p.accion === 'cero') return ['# 🕳️ Rellenar todos los vacíos con 0', 'df = df.fillna(0)'];
          if (p.accion === 'valor' && p.valor !== undefined && p.valor !== '') return [`# 🕳️ Rellenar todos los vacíos con ${p.valor}`, `df = df.fillna(${py(p.valor)})`];
          return null;
        }
        const t = ctx.tipo(p.col);
        const relleno = {
          mediana: [`${col(p.col)}.median()`, 'la mediana'],
          promedio: [`${col(p.col)}.mean()`, 'el promedio'],
          cero: ['0', '0'],
          valor: [literal(p.valor, t), p.valor],
        }[p.accion];
        if (!relleno || !relleno[0]) return null;
        return [`# 🕳️ Rellenar los vacíos de "${p.col}" con ${relleno[1]}`, `${col(p.col)} = ${col(p.col)}.fillna(${relleno[0]})`];
      },
    },
    texto: {
      grupo: '🧹 Limpiar', icono: '✂️', titulo: 'Limpiar texto',
      campos: [
        { k: 'col', label: 'Columna de texto', tipo: 'col', filtro: 'texto' },
        { k: 'formato', label: 'Formato', tipo: 'opciones', opciones: [['title', 'Tipo Título'], ['upper', 'MAYÚSCULAS'], ['lower', 'minúsculas'], ['nada', 'Solo quitar espacios']] },
      ],
      codigo: (p) => {
        if (!p.col) return null;
        const f = p.formato === 'nada' ? '' : `.str.${p.formato}()`;
        return [`# ✂️ Quitar espacios sobrantes${f ? ' y unificar mayúsculas' : ''} en "${p.col}"`, `${col(p.col)} = ${col(p.col)}.str.strip()${f}`];
      },
    },
    reemplazar: {
      grupo: '🧹 Limpiar', icono: '🔁', titulo: 'Reemplazar un valor',
      campos: [
        { k: 'col', label: 'Columna', tipo: 'col' },
        { k: 'de', label: 'Cambiar', tipo: 'valor', de: 'col' },
        { k: 'a', label: 'Por', tipo: 'valor', de: 'col', libre: true },
      ],
      codigo: (p, ctx) => {
        const t = ctx.tipo(p.col);
        const de = literal(p.de, t), a = literal(p.a, t);
        if (!p.col || de === null || a === null) return null;
        return [`# 🔁 En "${p.col}", cambiar ${p.de} por ${p.a}`, `${col(p.col)} = ${col(p.col)}.replace(${de}, ${a})`];
      },
    },
    // ---- Filtrar y ordenar ----
    filtrar: {
      grupo: '🔎 Filtrar y ordenar', icono: '🔎', titulo: 'Filtrar filas',
      campos: [
        { k: 'col', label: 'Columna', tipo: 'col' },
        { k: 'op', label: 'Condición', tipo: 'opciones', opciones: (ctx, p) => {
          const t = ctx.tipo(p.col);
          const base = [['==', 'es igual a'], ['!=', 'es distinto de']];
          if (t === 'num' || t === 'fecha') base.push(['>', 'mayor que'], ['>=', 'mayor o igual a'], ['<', 'menor que'], ['<=', 'menor o igual a'], ['entre', 'está entre']);
          if (t === 'texto') base.push(['contiene', 'contiene el texto'], ['empieza', 'empieza con'], ['en', 'es uno de (separa con comas)']);
          base.push(['vacio', 'está vacía'], ['novacio', 'no está vacía']);
          return base;
        } },
        { k: 'valor', label: 'Valor', tipo: 'valor', de: 'col', si: (p) => !['vacio', 'novacio'].includes(p.op) },
        { k: 'valor2', label: 'y', tipo: 'valor', de: 'col', si: (p) => p.op === 'entre' },
      ],
      codigo: (p, ctx) => {
        if (!p.col || !p.op) return null;
        const t = ctx.tipo(p.col), c = col(p.col);
        const v = literal(p.valor, t);
        let cond, texto;
        switch (p.op) {
          case 'vacio': cond = `${c}.isna()`; texto = 'está vacía'; break;
          case 'novacio': cond = `${c}.notna()`; texto = 'tiene dato'; break;
          case 'contiene': if (!p.valor) return null; cond = `${c}.astype(str).str.contains(${py(p.valor)}, case=False, na=False)`; texto = `contiene "${p.valor}"`; break;
          case 'empieza': if (!p.valor) return null; cond = `${c}.astype(str).str.startswith(${py(p.valor)})`; texto = `empieza con "${p.valor}"`; break;
          case 'en': {
            const vals = String(p.valor || '').split(',').map((x) => x.trim()).filter(Boolean);
            if (!vals.length) return null;
            cond = `${c}.isin(${lista(vals)})`; texto = `es ${vals.join(' o ')}`; break;
          }
          case 'entre': {
            const v2 = literal(p.valor2, t);
            if (v === null || v2 === null) return null;
            cond = `${c}.between(${v}, ${v2})`; texto = `está entre ${p.valor} y ${p.valor2}`; break;
          }
          default:
            if (v === null) return null;
            cond = `${c} ${p.op} ${v}`; texto = `${p.op} ${p.valor}`;
        }
        return [`# 🔎 Quedarnos solo con las filas donde "${p.col}" ${texto}`, `df = df[${cond}]`];
      },
    },
    ordenar: {
      grupo: '🔎 Filtrar y ordenar', icono: '↕️', titulo: 'Ordenar',
      campos: [
        { k: 'col', label: 'Ordenar por', tipo: 'col' },
        { k: 'dir', label: 'Dirección', tipo: 'opciones', opciones: [['desc', 'De mayor a menor'], ['asc', 'De menor a mayor']] },
      ],
      codigo: (p) => (p.col ? [`# ↕️ Ordenar por "${p.col}" ${p.dir === 'asc' ? 'de menor a mayor' : 'de mayor a menor'}`, `df = df.sort_values(${py(p.col)}, ascending=${p.dir === 'asc' ? 'True' : 'False'})`] : null),
    },
    top: {
      grupo: '🔎 Filtrar y ordenar', icono: '🔝', titulo: 'Quedarse con las primeras N filas',
      campos: [{ k: 'n', label: 'Cuántas', tipo: 'numero', def: 10 }],
      codigo: (p) => (num(p.n) ? [`# 🔝 Quedarnos solo con las primeras ${p.n} filas`, `df = df.head(${parseInt(p.n, 10)})`] : null),
    },
    columnas: {
      grupo: '🔎 Filtrar y ordenar', icono: '📋', titulo: 'Elegir columnas',
      campos: [{ k: 'cols', label: 'Columnas a conservar', tipo: 'cols' }],
      codigo: (p) => (p.cols && p.cols.length ? ['# 📋 Conservar solo estas columnas (en este orden)', `df = df[${lista(p.cols)}]`] : null),
    },
    // ---- Transformar ----
    calcular: {
      grupo: '🧮 Calcular', icono: '➕', titulo: 'Columna calculada',
      campos: [
        { k: 'nombre', label: 'Nombre de la nueva columna', tipo: 'texto', def: 'total' },
        { k: 'a', label: 'Columna', tipo: 'col', filtro: 'num' },
        { k: 'op', label: 'Operación', tipo: 'opciones', opciones: [['*', '× multiplicar'], ['+', '+ sumar'], ['-', '− restar'], ['/', '÷ dividir']] },
        { k: 'modo', label: 'Con', tipo: 'opciones', opciones: [['col', 'otra columna'], ['num', 'un número']] },
        { k: 'b', label: 'Columna', tipo: 'col', filtro: 'num', si: (p) => p.modo !== 'num' },
        { k: 'n', label: 'Número', tipo: 'numero', si: (p) => p.modo === 'num' },
        { k: 'dec', label: 'Redondear a', tipo: 'opciones', opciones: [['', 'sin redondear'], ['0', '0 decimales'], ['1', '1 decimal'], ['2', '2 decimales']] },
      ],
      codigo: (p) => {
        const derecha = p.modo === 'num' ? num(p.n) : (p.b ? col(p.b) : null);
        if (!p.nombre || !p.a || !derecha) return null;
        let expr = `${col(p.a)} ${p.op} ${derecha}`;
        if (p.dec !== '' && p.dec !== undefined) expr = `(${expr}).round(${p.dec})`;
        const quien = p.modo === 'num' ? p.n : `"${p.b}"`;
        return [`# ➕ Nueva columna "${p.nombre}" = "${p.a}" ${p.op} ${quien}`, `${col(p.nombre)} = ${expr}`];
      },
    },
    fecha: {
      grupo: '🧮 Calcular', icono: '📅', titulo: 'Sacar año / mes / día de una fecha',
      campos: [
        { k: 'col', label: 'Columna de fecha', tipo: 'col' },
        { k: 'parte', label: 'Qué sacar', tipo: 'opciones', opciones: [['month', 'Número de mes'], ['nombre_mes', 'Nombre del mes'], ['year', 'Año'], ['quarter', 'Trimestre'], ['day', 'Día del mes'], ['dia_semana', 'Día de la semana']] },
      ],
      codigo: (p) => {
        if (!p.col) return null;
        const f = `pd.to_datetime(${col(p.col)})`;
        const def = {
          month: ['mes', `${f}.dt.month`], year: ['año', `${f}.dt.year`], quarter: ['trimestre', `${f}.dt.quarter`], day: ['día', `${f}.dt.day`],
          nombre_mes: ['nombre_mes', `${f}.dt.month.map(${MESES})`],
          dia_semana: ['dia_semana', `${f}.dt.dayofweek.map(${DIAS})`],
        }[p.parte];
        return [`# 📅 Convertir "${p.col}" a fecha y sacar: ${def[0]}`, `${col(def[0])} = ${def[1]}`];
      },
    },
    renombrar: {
      grupo: '🧮 Calcular', icono: '✏️', titulo: 'Renombrar columna',
      campos: [{ k: 'col', label: 'Columna', tipo: 'col' }, { k: 'nuevo', label: 'Nuevo nombre', tipo: 'texto' }],
      codigo: (p) => (p.col && p.nuevo ? [`# ✏️ Cambiar el nombre de "${p.col}" a "${p.nuevo}"`, `df = df.rename(columns={${py(p.col)}: ${py(p.nuevo)}})`] : null),
    },
    // ---- Resumir ----
    agrupar: {
      grupo: '🧺 Resumir', icono: '🧺', titulo: 'Agrupar y resumir',
      campos: [
        { k: 'por', label: 'Agrupar por', tipo: 'cols', max: 3 },
        { k: 'val', label: 'Columna a resumir', tipo: 'col', filtro: 'num' },
        { k: 'ops', label: 'Calcular', tipo: 'multi', opciones: OPS_AGR, def: ['sum'] },
      ],
      codigo: (p) => {
        if (!p.por || !p.por.length || !p.val || !p.ops || !p.ops.length) return null;
        const nombres = OPS_AGR.filter(([k]) => p.ops.includes(k)).map(([, n]) => n).join(', ');
        return [
          `# 🧺 Agrupar por ${p.por.map((x) => `"${x}"`).join(' y ')} y calcular ${nombres} de "${p.val}"`,
          `df = df.groupby(${lista(p.por)}, as_index=False).agg(`,
          ...p.ops.map((o) => `    ${ident(p.val)}_${SUFIJO[o]}=(${py(p.val)}, "${o}"),`),
          ').round(2)',
        ];
      },
    },
    pivote: {
      grupo: '🧺 Resumir', icono: '🔄', titulo: 'Tabla dinámica',
      campos: [
        { k: 'filas', label: 'Filas', tipo: 'col' },
        { k: 'columnas', label: 'Columnas', tipo: 'col' },
        { k: 'val', label: 'Valores', tipo: 'col', filtro: 'num' },
        { k: 'op', label: 'Operación', tipo: 'opciones', opciones: OPS_AGR },
      ],
      codigo: (p) => {
        if (!p.filas || !p.columnas || !p.val) return null;
        return [
          `# 🔄 Tabla dinámica: filas = "${p.filas}", columnas = "${p.columnas}", valores = ${SUFIJO[p.op]} de "${p.val}"`,
          `df = df.pivot_table(index=${py(p.filas)}, columns=${py(p.columnas)}, values=${py(p.val)},`,
          `                    aggfunc="${p.op}", fill_value=0).round(2).reset_index()`,
          '# los nombres de columna quedan como texto',
          'df.columns = [str(c) for c in df.columns]',
        ];
      },
    },
    unir: {
      grupo: '🧺 Resumir', icono: '🔗', titulo: 'Unir con otra hoja (BUSCARV)',
      soloExcel: true,
      campos: [
        { k: 'hoja', label: 'Hoja', tipo: 'hoja' },
        { k: 'clave', label: 'Columna en común', tipo: 'clave' },
        { k: 'como', label: 'Filas a conservar', tipo: 'opciones', opciones: [['left', 'Todas las de mi tabla'], ['inner', 'Solo las que coinciden']] },
      ],
      codigo: (p) => {
        if (!p.hoja || !p.clave) return null;
        return [
          `# 🔗 Traer las columnas de la hoja "${p.hoja}" que coinciden por "${p.clave}" (como un BUSCARV)`,
          `otra = pd.read_excel(archivo, sheet_name=${py(p.hoja)})`,
          `df = df.merge(otra, on=${py(p.clave)}, how="${p.como || 'left'}")`,
        ];
      },
    },
    // ---- Graficar y guardar ----
    grafico: {
      grupo: '📈 Graficar y guardar', icono: '📈', titulo: 'Gráfico',
      campos: [
        { k: 'tipo', label: 'Tipo', tipo: 'opciones', opciones: [['bar', '📊 Barras'], ['barh', '📊 Barras horizontales'], ['line', '📈 Líneas'], ['pie', '🥧 Pastel'], ['hist', '📶 Histograma'], ['scatter', '⚬ Dispersión']] },
        { k: 'x', label: 'Eje X / categorías', tipo: 'col', si: (p) => p.tipo !== 'hist' },
        { k: 'y', label: 'Valores', tipo: 'col', filtro: 'num' },
      ],
      codigo: (p) => {
        if (!p.y || (p.tipo !== 'hist' && !p.x)) return null;
        const color = 'color="#e5191c"';
        const dibujo = {
          bar: `df.plot(kind="bar", x=${py(p.x)}, y=${py(p.y)}, ${color}, legend=False, figsize=(9, 4))`,
          barh: `df.plot(kind="barh", x=${py(p.x)}, y=${py(p.y)}, ${color}, legend=False, figsize=(9, 4))`,
          line: `df.plot(kind="line", x=${py(p.x)}, y=${py(p.y)}, marker="o", ${color}, legend=False, figsize=(9, 4))`,
          pie: `df.set_index(${py(p.x)})[${py(p.y)}].plot(kind="pie", autopct="%1.0f%%", figsize=(6, 6))`,
          hist: `df[${py(p.y)}].plot(kind="hist", bins=15, ${color}, edgecolor="white", figsize=(8, 4))`,
          scatter: `df.plot(kind="scatter", x=${py(p.x)}, y=${py(p.y)}, ${color}, figsize=(7, 5))`,
        }[p.tipo];
        const titulo = p.tipo === 'hist' ? `Distribución de ${p.y}` : `${p.y} por ${p.x}`;
        return [
          `# 📈 Gráfico (no cambia la tabla)`,
          dibujo,
          `plt.title(${py(titulo)})`,
          ...(p.tipo === 'pie' ? ['plt.ylabel("")'] : []),
          'plt.tight_layout()',
          'plt.show()',
        ];
      },
      usa: ['plt'],
    },
    exportar: {
      grupo: '📈 Graficar y guardar', icono: '💾', titulo: 'Guardar como Excel con formato',
      campos: [
        { k: 'nombre', label: 'Nombre del archivo', tipo: 'texto', def: 'resultado.xlsx' },
        { k: 'hoja', label: 'Nombre de la hoja', tipo: 'texto', def: 'Resultado' },
      ],
      codigo: (p) => {
        if (!p.nombre) return null;
        const nombre = /\.xlsx$/i.test(p.nombre) ? p.nombre : p.nombre + '.xlsx';
        const hoja = (p.hoja || 'Resultado').slice(0, 31);
        return [
          `# 💾 Guardar en "${nombre}" con encabezados de color, columnas ajustadas y fila fija`,
          `with pd.ExcelWriter(${py(nombre)}, engine="openpyxl") as excel:`,
          `    df.to_excel(excel, sheet_name=${py(hoja)}, index=False)`,
          `    hoja = excel.sheets[${py(hoja)}]`,
          '    for celda in hoja[1]:                        # fila 1 = encabezados',
          '        celda.font = Font(bold=True, color="FFFFFF")',
          '        celda.fill = PatternFill("solid", fgColor="E5191C")',
          '    for columna in hoja.columns:                 # ancho según el contenido',
          '        ancho = max(len(str(c.value or "")) for c in columna) + 2',
          '        hoja.column_dimensions[columna[0].column_letter].width = min(ancho, 40)',
          '    hoja.freeze_panes = "A2"                     # inmovilizar encabezados',
          `print("✅ Guardado: ${nombre.replace(/"/g, '')} (búscalo en «Mis archivos» para descargarlo)")`,
        ];
      },
      usa: ['estilos'],
    },
  };

  // Recetas de ejemplo para empezar con un clic
  const RECETAS = [
    {
      nombre: '📊 Ventas por región', archivo: 'ventas.xlsx', hoja: 'Ventas', pasos: [
        { tipo: 'calcular', nombre: 'total', a: 'unidades', op: '*', modo: 'col', b: 'precio_unitario', dec: '2' },
        { tipo: 'agrupar', por: ['region'], val: 'total', ops: ['sum', 'mean', 'count'] },
        { tipo: 'ordenar', col: 'total_suma', dir: 'desc' },
        { tipo: 'grafico', tipo_g: 'bar', x: 'region', y: 'total_suma' },
        { tipo: 'exportar', nombre: 'ventas_por_region.xlsx', hoja: 'Por región' },
      ],
    },
    {
      nombre: '🧹 Limpiar datos sucios', archivo: 'ventas_sucias.csv', hoja: null, pasos: [
        { tipo: 'duplicados' },
        { tipo: 'texto', col: 'region', formato: 'title' },
        { tipo: 'faltantes', col: 'unidades', accion: 'mediana' },
        { tipo: 'faltantes', col: 'precio_unitario', accion: 'eliminar' },
        { tipo: 'exportar', nombre: 'ventas_limpias.xlsx', hoja: 'Limpias' },
      ],
    },
    {
      nombre: '🎯 Ventas vs. metas', archivo: 'ventas.xlsx', hoja: 'Ventas', pasos: [
        { tipo: 'calcular', nombre: 'total', a: 'unidades', op: '*', modo: 'col', b: 'precio_unitario', dec: '2' },
        { tipo: 'agrupar', por: ['vendedor'], val: 'total', ops: ['sum'] },
        { tipo: 'unir', hoja: 'Metas', clave: 'vendedor', como: 'left' },
        { tipo: 'calcular', nombre: 'avance_%', a: 'total_suma', op: '/', modo: 'col', b: 'meta_anual', dec: '' },
        { tipo: 'calcular', nombre: 'avance_%', a: 'avance_%', op: '*', modo: 'num', n: '100', dec: '1' },
        { tipo: 'ordenar', col: 'avance_%', dir: 'desc' },
        { tipo: 'grafico', tipo_g: 'barh', x: 'vendedor', y: 'avance_%' },
      ],
    },
    {
      nombre: '📅 Tendencia mensual', archivo: 'ventas.xlsx', hoja: 'Ventas', pasos: [
        { tipo: 'calcular', nombre: 'total', a: 'unidades', op: '*', modo: 'col', b: 'precio_unitario', dec: '2' },
        { tipo: 'fecha', col: 'fecha', parte: 'month' },
        { tipo: 'agrupar', por: ['mes'], val: 'total', ops: ['sum'] },
        { tipo: 'grafico', tipo_g: 'line', x: 'mes', y: 'total_suma' },
      ],
    },
  ];

  // ---------- Estado ----------
  const estado = {
    archivo: 'ventas.xlsx',
    hoja: 'Ventas',
    hojas: {},          // nombre de hoja -> columnas (para unir)
    pasos: [],          // [{id, tipo, p: {...}}]
    fotos: [],          // foto de la tabla después de cargar y de cada paso
    lineas: [],         // [{desde, hasta, paso}] para ubicar errores
    errorPaso: null,
    ultimoCodigo: '',
  };
  let contador = 0;
  const esExcel = () => /\.(xlsx|xlsm|xls)$/i.test(estado.archivo);

  // Columnas disponibles justo antes del paso i (foto 0 = después de cargar)
  function columnasAntes(i) {
    for (let k = Math.min(i, estado.fotos.length - 1); k >= 0; k--) {
      if (estado.fotos[k]) return estado.fotos[k].columnas;
    }
    return [];
  }
  function ctxPara(i) {
    const cols = columnasAntes(i);
    return {
      cols,
      tipo: (n) => (cols.find((c) => c.nombre === n) || {}).tipo || 'texto',
      info: (n) => cols.find((c) => c.nombre === n),
    };
  }

  // ---------- Generar el script ----------
  function generar(instrumentado) {
    const usa = new Set();
    const cuerpo = [];
    const lineas = [];
    const foto = instrumentado ? '; _foto(df)' : '';
    const cargar = esExcel()
      ? [`# 📂 Cargar la hoja "${estado.hoja}" del archivo ${estado.archivo}`, `archivo = ${py(estado.archivo)}`, `df = pd.read_excel(archivo, sheet_name=${py(estado.hoja)})${foto}`]
      : [`# 📂 Cargar el archivo ${estado.archivo}`, `archivo = ${py(estado.archivo)}`, `df = pd.read_csv(archivo)${foto}`];
    cuerpo.push(...cargar);
    estado.pasos.forEach((paso, i) => {
      const acc = ACCIONES[paso.tipo];
      const lns = acc.codigo(paso.p, ctxPara(i));
      paso.codigo = lns;
      if (!lns) { if (instrumentado) cuerpo.push(`_foto(df)`); else cuerpo.push(`# ⚠️ Paso ${i + 1} (${acc.titulo}): faltan datos`); return; }
      (acc.usa || []).forEach((u) => usa.add(u));
      cuerpo.push('');
      const desde = cuerpo.length + 1;
      const copia = lns.slice();
      if (foto && /#/.test(copia[copia.length - 1]) && !/^\s*print\(/.test(copia[copia.length - 1])) copia.push('_foto(df)');
      else copia[copia.length - 1] += foto;
      cuerpo.push(...copia);
      lineas.push({ desde, hasta: cuerpo.length, paso: i });
    });
    cuerpo.push('', '# 👀 Ver el resultado', 'print(f"Resultado: {len(df)} filas × {df.shape[1]} columnas")', 'df');
    const imports = ['import pandas as pd'];
    if (usa.has('plt')) imports.push('import matplotlib.pyplot as plt');
    if (usa.has('estilos')) imports.push('from openpyxl.styles import Font, PatternFill');
    imports.push('');
    const desplazamiento = imports.length;
    return {
      codigo: [...imports, ...cuerpo].join('\n') + '\n',
      lineas: lineas.map((l) => ({ ...l, desde: l.desde + desplazamiento, hasta: l.hasta + desplazamiento })),
    };
  }

  // ---------- Ejecutar ----------
  const ANTES = 'from harness import inspeccionar_df as _insp\n_fotos = []\ndef _foto(d):\n    _fotos.append(_insp(d))\n';
  const DESPUES = 'import json as _json\nprint(_json.dumps(_fotos))\n';
  let temporizador = null;
  let enCurso = 0;

  function programar() {
    clearTimeout(temporizador);
    temporizador = setTimeout(ejecutar, 350);
  }

  async function ejecutar() {
    const visible = generar(false);
    const inst = generar(true);
    estado.lineas = inst.lineas;
    estado.ultimoCodigo = visible.codigo;
    mostrarCodigo(visible.codigo);
    const yo = ++enCurso;
    const salida = $('#wz-salida');
    salida.innerHTML = '<div class="out-loading"><span class="spinner"></span> <span data-msg>Ejecutando tu receta…</span></div>';
    const r = await Py.ejecutar(inst.codigo, {
      antes: ANTES, despues: DESPUES, limite: 60000,
      onEstado: (t) => { const m = salida.querySelector('[data-msg]'); if (m) m.textContent = t; },
    });
    if (yo !== enCurso) return; // llegó una ejecución más nueva
    try {
      const fotos = JSON.parse((r.despues || '[]').trim().split('\n').pop());
      if (Array.isArray(fotos)) estado.fotos = mapearFotos(fotos);
    } catch (e) { /* sin fotos */ }
    estado.errorPaso = null;
    if (r.error && r.linea) {
      const l = estado.lineas.find((x) => r.linea >= x.desde && r.linea <= x.hasta);
      if (l) estado.errorPaso = { paso: l.paso, error: r.error, pista: r.pista };
    }
    mostrarSalida(salida, r);
    pintarPasos();
    App.api('run', { chart: (r.imagenes || []).length > 0 });
    document.dispatchEvent(new CustomEvent('lab-refrescar'));
  }

  /** Las fotos llegan en orden: carga + cada paso completo. Las alineamos con los pasos. */
  function mapearFotos(fotos) {
    const res = [fotos[0] || null];
    let k = 1;
    estado.pasos.forEach(() => { res.push(fotos[k] || null); k++; });
    return res;
  }

  // ---------- Pintar ----------
  let visor = null;
  function mostrarCodigo(codigo) {
    const cont = $('#wz-codigo');
    if (window.CodeMirror) {
      if (!visor) visor = CodeMirror(cont, { value: codigo, mode: 'python', readOnly: true, lineNumbers: true, viewportMargin: Infinity });
      else visor.setValue(codigo);
    } else {
      cont.innerHTML = `<pre class="code-block">${E(codigo)}</pre>`;
    }
  }
  function resaltar(i) {
    if (!visor) return;
    for (let n = 0; n < visor.lineCount(); n++) visor.removeLineClass(n, 'background', 'line-paso');
    const l = estado.lineas.find((x) => x.paso === i);
    if (l) for (let n = l.desde - 1; n < l.hasta; n++) visor.addLineClass(n, 'background', 'line-paso');
  }

  function opcionesCol(ctx, campo) {
    return ctx.cols
      .filter((c) => !campo.filtro || c.tipo === campo.filtro || (campo.filtro === 'num' && c.tipo === 'bool'))
      .map((c) => [c.nombre, c.nombre + ({ num: ' (123)', texto: ' (abc)', fecha: ' (📅)', bool: ' (✓)' }[c.tipo] || '')]);
  }

  function campoHTML(campo, paso, i) {
    const p = paso.p, ctx = ctxPara(i);
    const id = `f-${paso.id}-${campo.k}`;
    const val = p[campo.k];
    const sel = (ops, vacia) => `<select id="${id}" data-k="${campo.k}">${vacia ? `<option value="">${vacia}</option>` : ''}${ops.map(([v, t]) => `<option value="${E(v)}" ${String(val) === String(v) ? 'selected' : ''}>${E(t)}</option>`).join('')}</select>`;
    let control;
    switch (campo.tipo) {
      case 'col': {
        const ops = opcionesCol(ctx, campo);
        if (campo.todas) ops.unshift(['*', '(todas las columnas)']);
        if (val && !ops.some(([v]) => v === val)) ops.push([val, val + ' ⚠️ no existe']);
        control = sel(ops, '— elige —');
        break;
      }
      case 'opciones': {
        const ops = typeof campo.opciones === 'function' ? campo.opciones(ctx, p) : campo.opciones;
        control = sel(ops);
        break;
      }
      case 'hoja':
        control = sel(Object.keys(estado.hojas).filter((h) => h !== estado.hoja).map((h) => [h, h]), '— elige —');
        break;
      case 'clave': {
        const otras = estado.hojas[p.hoja] || [];
        const comunes = ctx.cols.map((c) => c.nombre).filter((n) => otras.includes(n));
        control = comunes.length ? sel(comunes.map((n) => [n, n]), '— elige —') : '<span class="muted small">No hay columnas con el mismo nombre</span>';
        break;
      }
      case 'cols': {
        const elegidas = val || [];
        control = `<div class="wz-checks" data-k="${campo.k}">${ctx.cols.map((c) => `<label><input type="checkbox" value="${E(c.nombre)}" ${elegidas.includes(c.nombre) ? 'checked' : ''}> ${E(c.nombre)}</label>`).join('')}</div>`;
        break;
      }
      case 'multi': {
        const elegidas = val || [];
        control = `<div class="wz-checks" data-k="${campo.k}">${campo.opciones.map(([v, t]) => `<label><input type="checkbox" value="${v}" ${elegidas.includes(v) ? 'checked' : ''}> ${t}</label>`).join('')}</div>`;
        break;
      }
      case 'valor': {
        const info = ctx.info(p[campo.de]) || {};
        const tipoInput = info.tipo === 'num' ? 'number' : info.tipo === 'fecha' ? 'date' : 'text';
        const dl = info.valores && info.valores.length ? `<datalist id="${id}-dl">${info.valores.map((v) => `<option value="${E(v)}">`).join('')}</datalist>` : '';
        control = `<input id="${id}" data-k="${campo.k}" type="${tipoInput}" step="any" value="${E(val ?? '')}" ${dl ? `list="${id}-dl"` : ''} placeholder="${info.tipo === 'texto' ? 'escribe o elige' : ''}">${dl}`;
        break;
      }
      case 'numero':
        control = `<input id="${id}" data-k="${campo.k}" type="number" step="any" value="${E(val ?? '')}">`;
        break;
      default:
        control = `<input id="${id}" data-k="${campo.k}" type="text" value="${E(val ?? '')}">`;
    }
    return `<label class="wz-field" for="${id}"><span>${E(campo.label)}</span>${control}</label>`;
  }

  function pintarPasos() {
    const ol = $('#wz-pasos');
    if (!estado.pasos.length) {
      ol.innerHTML = '<li class="wz-empty">Tu tabla está cargada. 👉 Pulsa <b>Agregar acción</b> para empezar a transformarla, o prueba una receta de arriba.</li>';
    } else {
      ol.innerHTML = estado.pasos.map((paso, i) => {
        const acc = ACCIONES[paso.tipo];
        const foto = estado.fotos[i + 1];
        const err = estado.errorPaso && estado.errorPaso.paso === i ? estado.errorPaso : null;
        const incompleto = !paso.codigo;
        return `<li class="wz-step ${err ? 'wz-error' : ''} ${incompleto ? 'wz-incompleto' : ''}" data-i="${i}">
          <div class="wz-step-head">
            <span class="wz-num">${i + 1}</span>
            <b>${acc.icono} ${E(acc.titulo)}</b>
            <span class="wz-rows">${incompleto ? '⚠️ completa los datos' : foto ? `→ ${foto.filas} filas × ${foto.columnas.length} col.` : ''}</span>
            <span class="wz-step-btns">
              <button data-mover="-1" title="Subir" ${i === 0 ? 'disabled' : ''}>▲</button>
              <button data-mover="1" title="Bajar" ${i === estado.pasos.length - 1 ? 'disabled' : ''}>▼</button>
              <button data-quitar title="Quitar">✕</button>
            </span>
          </div>
          ${acc.campos.length ? `<div class="wz-fields">${acc.campos.filter((c) => !c.si || c.si(paso.p)).map((c) => campoHTML(c, paso, i)).join('')}</div>` : ''}
          ${err ? `<div class="wz-err">😬 ${E(err.error.split('\n')[0])}${err.pista ? `<br><small>💡 ${E(err.pista)}</small>` : ''}</div>` : ''}
          ${paso.codigo ? `<details class="wz-snippet" open><summary>🐍 Código de este paso</summary><pre>${E(paso.codigo.join('\n'))}</pre></details>` : ''}
        </li>`;
      }).join('');
    }
    const f0 = estado.fotos[0];
    $('#wz-carga').textContent = f0 ? `${f0.filas} filas × ${f0.columnas.length} columnas` : '';
  }

  function pintarFuente(archivos) {
    const selA = $('#wz-archivo');
    const datos = (archivos || []).filter((a) => /\.(xlsx|xlsm|xls|csv)$/i.test(a.nombre)).map((a) => a.nombre);
    if (!datos.includes(estado.archivo)) datos.unshift(estado.archivo);
    selA.innerHTML = datos.map((n) => `<option ${n === estado.archivo ? 'selected' : ''}>${E(n)}</option>`).join('');
    const selH = $('#wz-hoja');
    const hojas = Object.keys(estado.hojas);
    selH.innerHTML = hojas.map((h) => `<option ${h === estado.hoja ? 'selected' : ''}>${E(h)}</option>`).join('');
    selH.parentElement.hidden = !esExcel();
  }

  function pintarMenu() {
    const grupos = {};
    Object.entries(ACCIONES).forEach(([k, a]) => { (grupos[a.grupo] = grupos[a.grupo] || []).push([k, a]); });
    $('#wz-menu').innerHTML = Object.entries(grupos).map(([g, accs]) => `<div class="wz-menu-group"><div class="wz-menu-title">${E(g)}</div>${accs.map(([k, a]) => `<button data-accion="${k}" ${a.soloExcel && !esExcel() ? 'disabled title="Solo para archivos de Excel"' : ''}>${a.icono} ${E(a.titulo)}</button>`).join('')}</div>`).join('');
    $('#wz-recetas').innerHTML = RECETAS.map((r, i) => `<button class="chip" data-receta="${i}">${E(r.nombre)}</button>`).join('');
  }

  // ---------- Cargar archivo / hoja ----------
  async function cargarFuente() {
    const salida = $('#wz-salida');
    salida.innerHTML = '<div class="out-loading"><span class="spinner"></span> Leyendo el archivo…</div>';
    const codigo = esExcel()
      ? `import pandas as pd, json\nhojas = pd.read_excel(${py(estado.archivo)}, sheet_name=None, nrows=5)\nprint(json.dumps({h: [str(c) for c in d.columns] for h, d in hojas.items()}))`
      : `import pandas as pd, json\nd = pd.read_csv(${py(estado.archivo)}, nrows=5)\nprint(json.dumps({"(CSV)": [str(c) for c in d.columns]}))`;
    const r = await Py.ejecutar(codigo, { limite: 60000 });
    if (r.error) { mostrarSalida(salida, r); return false; }
    estado.hojas = JSON.parse(r.stdout.trim().split('\n').pop());
    const nombres = Object.keys(estado.hojas);
    if (!esExcel()) estado.hoja = null;
    else if (!nombres.includes(estado.hoja)) estado.hoja = nombres[0];
    estado.fotos = [];
    pintarFuente(await Py.listar().catch(() => []));
    pintarMenu();
    return true;
  }

  function nuevoPaso(tipo, p = {}) {
    const acc = ACCIONES[tipo];
    const paso = { id: ++contador, tipo, p: {} };
    const ctx = ctxPara(estado.pasos.length);
    acc.campos.forEach((c) => {
      if (p[c.k] !== undefined) { paso.p[c.k] = p[c.k]; return; }
      if (c.def !== undefined) paso.p[c.k] = c.def;
      else if (c.tipo === 'opciones') {
        const ops = typeof c.opciones === 'function' ? c.opciones(ctx, paso.p) : c.opciones;
        paso.p[c.k] = ops[0][0];
      } else if (c.tipo === 'col' && !c.todas) {
        const ops = opcionesCol(ctx, c);
        // Evitamos elegir la misma columna dos veces en el mismo paso
        const usadas = Object.values(paso.p);
        const libre = ops.find(([v]) => !usadas.includes(v));
        if (libre) paso.p[c.k] = libre[0];
      } else if (c.tipo === 'col' && c.todas) paso.p[c.k] = (ctx.cols[0] || {}).nombre;
      else if (c.tipo === 'cols' && c.max) {
        // Para agrupar sugerimos la primera columna de texto con pocos valores distintos
        const t = ctx.cols.find((x) => x.tipo === 'texto' && x.distintos && x.distintos <= 50);
        paso.p[c.k] = t ? [t.nombre] : [];
      } else if (c.tipo === 'hoja') paso.p[c.k] = Object.keys(estado.hojas).find((h) => h !== estado.hoja);
      else if (c.tipo === 'clave') {
        const otras = estado.hojas[paso.p.hoja] || [];
        paso.p[c.k] = (ctx.cols.find((x) => otras.includes(x.nombre)) || {}).nombre;
      }
    });
    // "tipo_g" en las recetas es el tipo de gráfico (para no chocar con "tipo" de paso)
    if (p.tipo_g) paso.p.tipo = p.tipo_g;
    return paso;
  }

  // ---------- Eventos ----------
  $('#wz-agregar').addEventListener('click', () => {
    const m = $('#wz-menu');
    m.hidden = !m.hidden;
    App.sfx('tab');
  });
  $('#wz-menu').addEventListener('click', (ev) => {
    const b = ev.target.closest('[data-accion]');
    if (!b) return;
    estado.pasos.push(nuevoPaso(b.dataset.accion));
    $('#wz-menu').hidden = true;
    App.sfx('go');
    pintarPasos();
    ejecutar();
    setTimeout(() => { const ult = $('#wz-pasos').lastElementChild; if (ult) ult.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); }, 50);
  });

  $('#wz-pasos').addEventListener('click', (ev) => {
    const li = ev.target.closest('.wz-step');
    if (!li) return;
    const i = +li.dataset.i;
    if (ev.target.closest('[data-quitar]')) { estado.pasos.splice(i, 1); App.sfx('mal'); }
    else if (ev.target.closest('[data-mover]')) {
      const j = i + +ev.target.closest('[data-mover]').dataset.mover;
      [estado.pasos[i], estado.pasos[j]] = [estado.pasos[j], estado.pasos[i]];
      App.sfx('move');
    } else return;
    estado.fotos = estado.fotos.slice(0, 1);
    pintarPasos();
    ejecutar();
  });

  $('#wz-pasos').addEventListener('change', (ev) => {
    const li = ev.target.closest('.wz-step');
    if (!li) return;
    const paso = estado.pasos[+li.dataset.i];
    const grupo = ev.target.closest('.wz-checks');
    if (grupo) {
      paso.p[grupo.dataset.k] = [...grupo.querySelectorAll('input:checked')].map((x) => x.value);
    } else if (ev.target.dataset.k) {
      paso.p[ev.target.dataset.k] = ev.target.value;
      // Si cambia la columna de un filtro, la condición anterior puede no aplicar
      if (paso.tipo === 'filtrar' && ev.target.dataset.k === 'col') { paso.p.op = '=='; paso.p.valor = ''; paso.p.valor2 = ''; }
    }
    pintarPasos();
    programar();
  });

  $('#wz-pasos').addEventListener('mouseover', (ev) => {
    const li = ev.target.closest('.wz-step');
    resaltar(li ? +li.dataset.i : -1);
  });

  $('#wz-archivo').addEventListener('change', async (ev) => {
    estado.archivo = ev.target.value;
    estado.pasos = [];
    if (await cargarFuente()) { pintarPasos(); ejecutar(); }
  });
  $('#wz-hoja').addEventListener('change', (ev) => {
    estado.hoja = ev.target.value;
    estado.fotos = [];
    pintarPasos();
    ejecutar();
  });

  $('#wz-recetas').addEventListener('click', async (ev) => {
    const b = ev.target.closest('[data-receta]');
    if (!b) return;
    const r = RECETAS[+b.dataset.receta];
    App.sfx('go');
    estado.archivo = r.archivo;
    estado.hoja = r.hoja;
    if (!(await cargarFuente())) return;
    estado.pasos = [];
    // Agregamos los pasos uno por uno para que cada uno vea las columnas del anterior
    for (const pr of r.pasos) {
      const { tipo, ...params } = pr;
      estado.pasos.push(nuevoPaso(tipo, params));
      await ejecutar();
    }
  });

  $('#wz-abrir').addEventListener('click', () => {
    document.dispatchEvent(new CustomEvent('lab-modo', { detail: 'codigo' }));
    const runner = document.querySelector('.lab [data-runner]')._runner;
    runner.editor.setValue(estado.ultimoCodigo);
    App.toast('✏️ Tu receta está en el editor: ahora puedes modificarla a mano.', 'info');
  });
  $('#wz-copiar').addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(estado.ultimoCodigo); App.toast('📋 Código copiado', 'info', 2000); } catch (e) { /* sin portapapeles */ }
  });
  $('#wz-reiniciar').addEventListener('click', () => {
    if (estado.pasos.length && !confirm('¿Quitar todos los pasos?')) return;
    estado.pasos = [];
    estado.fotos = estado.fotos.slice(0, 1);
    pintarPasos();
    ejecutar();
  });

  // Archivos: se actualiza la lista cuando el laboratorio sube o crea archivos
  document.addEventListener('lab-archivos', (ev) => pintarFuente(ev.detail));
  document.addEventListener('lab-subido', async (ev) => {
    if (!raiz.offsetParent || !/\.(xlsx|xlsm|xls|csv)$/i.test(ev.detail)) return;
    estado.archivo = ev.detail;
    estado.pasos = [];
    if (await cargarFuente()) { pintarPasos(); ejecutar(); }
  });

  // Arranque: cuando Python esté listo cargamos el Excel de ejemplo
  let iniciado = false;
  async function arrancar() {
    if (iniciado) return;
    iniciado = true;
    pintarMenu();
    if (await cargarFuente()) { pintarPasos(); ejecutar(); }
  }
  document.addEventListener('py-estado', (ev) => { if (ev.detail.estado === 'listo' && raiz.offsetParent) arrancar(); });
  document.addEventListener('lab-modo', (ev) => { if (ev.detail === 'asistente' && Py.estado === 'listo') arrancar(); });
})();
