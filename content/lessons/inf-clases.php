<?php
return [
    'title' => 'Clases: crea tus propios objetos',
    'icon' => '🏗️',
    'minutes' => 15,
    'summary' => 'Programación orientada a objetos, métodos especiales y dataclasses.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Moldes para objetos',
            'html' => <<<'HTML'
<p>Un DataFrame, una lista o un string son <b>objetos</b>: tienen datos (atributos) y acciones (métodos). Con <code>class</code> puedes crear tus propios tipos de objeto.</p>
<pre>class Producto:
    def __init__(self, nombre, precio):   # se ejecuta al crear el objeto
        self.nombre = nombre               # self = "este objeto"
        self.precio = precio

    def con_iva(self):                     # un método
        return round(self.precio * 1.16, 2)

    def __repr__(self):                    # cómo se ve al imprimirlo
        return f"Producto({self.nombre!r}, {self.precio})"

laptop = Producto("Laptop", 850)
laptop.con_iva()     # 986.0</pre>
<p>Los métodos con doble guion bajo (<code>__len__</code>, <code>__contains__</code>, <code>__add__</code>…) son <b>especiales</b>: permiten que tu objeto funcione con <code>len()</code>, <code>in</code>, <code>+</code>…</p>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'dataclass: clases sin tanto código',
            'code' => <<<'PY'
from dataclasses import dataclass

@dataclass
class Venta:
    producto: str
    unidades: int
    precio: float

    def total(self):
        return self.unidades * self.precio

v = Venta("Mouse", 3, 25.5)
print(v)                 # __repr__ automático
print(v.total())
print(v == Venta("Mouse", 3, 25.5))   # __eq__ automático
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🏦 Cuenta bancaria',
            'html' => <<<'HTML'
<p>Crea la clase <code>Cuenta</code>:</p>
<ul>
  <li><code>Cuenta(titular, saldo=0)</code> guarda <code>titular</code>, <code>saldo</code> y una lista <code>movimientos</code> vacía</li>
  <li><code>depositar(monto)</code>: suma al saldo y agrega <code>("deposito", monto)</code> a movimientos. Si el monto es ≤ 0 lanza <code>ValueError</code></li>
  <li><code>retirar(monto)</code>: resta y agrega <code>("retiro", monto)</code>. Si no hay saldo suficiente lanza <code>ValueError</code> (y no cambia nada)</li>
</ul>
HTML,
            'starter' => "class Cuenta:\n    pass\n\n",
            'hint' => 'Valida primero y luego modifica: así si lanzas el error, el saldo queda intacto.',
            'solution' => "class Cuenta:\n    def __init__(self, titular, saldo=0):\n        self.titular = titular\n        self.saldo = saldo\n        self.movimientos = []\n\n    def depositar(self, monto):\n        if monto <= 0:\n            raise ValueError(\"El depósito debe ser positivo\")\n        self.saldo += monto\n        self.movimientos.append((\"deposito\", monto))\n\n    def retirar(self, monto):\n        if monto > self.saldo:\n            raise ValueError(\"Saldo insuficiente\")\n        self.saldo -= monto\n        self.movimientos.append((\"retiro\", monto))\n\n\nc = Cuenta(\"Ana\", 100)\nc.depositar(50)\nc.retirar(30)\nprint(c.saldo, c.movimientos)\n",
            'check' => <<<'PY'
c1 = Cuenta("Ana")
assert c1.titular == "Ana" and c1.saldo == 0 and c1.movimientos == [], "Revisa __init__ (saldo por defecto 0 y movimientos vacía)."
c2 = Cuenta("Luis", 100)
c2.depositar(50); c2.retirar(30)
assert c2.saldo == 120, f"Después de +50 y −30 el saldo debería ser 120, es {c2.saldo}."
assert c2.movimientos == [("deposito", 50), ("retiro", 30)], f"movimientos quedó: {c2.movimientos}"
for _accion, _monto in [("retirar", 1000), ("depositar", 0), ("depositar", -5)]:
    try:
        getattr(c2, _accion)(_monto)
    except ValueError:
        pass
    else:
        raise AssertionError(f"{_accion}({_monto}) debería lanzar ValueError.")
assert c2.saldo == 120 and len(c2.movimientos) == 2, "Una operación inválida no debe cambiar el saldo ni los movimientos."
assert Cuenta("X").movimientos is not c1.movimientos, "Cada cuenta debe tener su propia lista de movimientos."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📦 Inventario con superpoderes',
            'html' => <<<'HTML'
<p>Crea la clase <code>Inventario</code> que guarde productos y cantidades en un diccionario interno:</p>
<ul>
  <li><code>agregar(nombre, cantidad)</code> suma a lo que haya</li>
  <li><code>quitar(nombre, cantidad)</code> resta; si queda en 0 o menos, elimina el producto</li>
  <li><code>len(inv)</code> = cuántos productos distintos hay (<code>__len__</code>)</li>
  <li><code>"mouse" in inv</code> funciona (<code>__contains__</code>)</li>
  <li><code>inv["mouse"]</code> da la cantidad, o 0 si no existe (<code>__getitem__</code>)</li>
</ul>
HTML,
            'starter' => "class Inventario:\n    pass\n\n",
            'hint' => 'Guarda un dict en self.stock y usa .get(nombre, 0).',
            'solution' => "class Inventario:\n    def __init__(self):\n        self.stock = {}\n\n    def agregar(self, nombre, cantidad):\n        self.stock[nombre] = self.stock.get(nombre, 0) + cantidad\n\n    def quitar(self, nombre, cantidad):\n        restante = self.stock.get(nombre, 0) - cantidad\n        if restante <= 0:\n            self.stock.pop(nombre, None)\n        else:\n            self.stock[nombre] = restante\n\n    def __len__(self):\n        return len(self.stock)\n\n    def __contains__(self, nombre):\n        return nombre in self.stock\n\n    def __getitem__(self, nombre):\n        return self.stock.get(nombre, 0)\n\n\ninv = Inventario()\ninv.agregar(\"mouse\", 10)\ninv.agregar(\"teclado\", 3)\ninv.quitar(\"teclado\", 5)\nprint(len(inv), \"mouse\" in inv, inv[\"mouse\"], inv[\"teclado\"])\n",
            'check' => <<<'PY'
inv = Inventario()
assert len(inv) == 0, "Un inventario nuevo debe tener len 0."
inv.agregar("mouse", 10); inv.agregar("mouse", 5); inv.agregar("teclado", 3)
assert len(inv) == 2 and inv["mouse"] == 15, "agregar debe sumar a lo que haya."
assert "mouse" in inv and "monitor" not in inv, "__contains__ no funciona bien."
assert inv["monitor"] == 0, "inv[producto inexistente] debe dar 0."
inv.quitar("mouse", 4)
assert inv["mouse"] == 11, "quitar debe restar."
inv.quitar("teclado", 5)
assert "teclado" not in inv and len(inv) == 1, "Si la cantidad llega a 0 o menos, el producto se elimina."
inv2 = Inventario()
assert len(inv2) == 0, "Cada inventario debe ser independiente."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🧾 Ventas como objetos',
            'html' => '<p>Crea la <code>@dataclass</code> <code>Venta</code> con <code>producto</code> (str), <code>region</code> (str), <code>unidades</code> (int) y <code>precio</code> (float), y un método <code>total()</code>. Luego lee <code>ventas.csv</code> con <code>csv.DictReader</code> y crea la lista <code>ventas</code> de objetos <code>Venta</code> (convierte los tipos). Guarda en <code>mejor</code> la venta con mayor total (usa <code>max</code> con <code>key</code>).</p>',
            'starter' => "import csv\nfrom dataclasses import dataclass\n\n",
            'hint' => '<code>max(ventas, key=lambda v: v.total())</code>',
            'solution' => "import csv\nfrom dataclasses import dataclass\n\n@dataclass\nclass Venta:\n    producto: str\n    region: str\n    unidades: int\n    precio: float\n\n    def total(self):\n        return self.unidades * self.precio\n\nwith open(\"ventas.csv\", encoding=\"utf-8\") as f:\n    ventas = [Venta(r[\"producto\"], r[\"region\"], int(r[\"unidades\"]), float(r[\"precio_unitario\"])) for r in csv.DictReader(f)]\n\nmejor = max(ventas, key=lambda v: v.total())\nprint(len(ventas), mejor)\n",
            'check' => <<<'PY'
import dataclasses
import pandas as _pd
assert dataclasses.is_dataclass(Venta), "Venta debe ser una @dataclass."
assert len(ventas) == 300 and all(isinstance(v, Venta) for v in ventas), "ventas debe tener 300 objetos Venta."
assert isinstance(ventas[0].unidades, int) and isinstance(ventas[0].precio, float), "Convierte unidades a int y precio a float."
_v = _pd.read_csv("ventas.csv")
_i = (_v["unidades"] * _v["precio_unitario"]).idxmax()
assert abs(mejor.total() - float(_v.loc[_i, "unidades"] * _v.loc[_i, "precio_unitario"])) < 1e-6, "mejor no es la venta de mayor total."
PY,
        ],
    ],
];
