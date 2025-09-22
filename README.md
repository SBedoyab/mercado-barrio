# MercadoBarrio. Patrones de diseño aplicados en un módulo de “Pedidos & Entregas”

## Contexto del proyecto
Este repositorio contiene el desarrollo de un ejercicio del curso de Arquitectura de Sistemas. El punto de partida era un código en `PHP` para un e‑commerce local ficticio llamado MercadoBarrio. En la versión inicial, todo el código estaba con lógica directa, no seguía prácticas OCP/DIP y no seguía ningún patrón de diseño. El reto consistió en refactorizar el código de PHP del módulo de "Confirmación de Pedido y Orquestación de Entrega" aplicando patrones de diseño para mejorar la modularidad del mismo.

La nueva versión, reorganiza/modulariza el código en una carpeta `app/`, también se hicieron pequeños cambios en diversos archivos de todo el código base. En `app/` se divide la lógica en clases e interfaces.

## Objetivo de la actividad
Se cita literalmente el objetivo descrito en el taller: 

> Cada equipo debe implementar **una funcionalidad específica** aplicando **tres patrones** (uno por familia).
>
> La elección del patrón **es libre** dentro de cada familia, **justificando** por qué ese patrón es el más adecuado." 

El scope acotado es especificamente mejorar el código que se encarga de confirma un pedido y coordina su envío. Esto anterior, es iniciado por un formulario donde se confirma un pedido de productos del usuario, se introduce correo, dirección, prioridad, fragilidad y productos, **EL CÓDIGO** debe:

1. Validar las entradas y construir los datos completos del pedido.
2. Insertar el pedido y sus items en la base de datos.
3. Seleccionar un proveedor de entrega según reglas de negocio. ← (Reglas de negocio incompletas en los requisitos)
4. Pedir la recogida al proveedor y registrar el envío y las notificaciones.

---

## Descripción del código que cumple el módulo “Confirmación de Pedido y Orquestación de Entrega”
El corazón de la aplicación es la función **`handle_create_order()`** definida en `app/functions.php`. Recibe la entrada del formulario, valida el email y la dirección y comienza una transacción de base de datos.

Para construir el pedido, se optó por el patrón creacional Builder donde: *`handle_create_order()`* crea una instancia de `\App\Builder\OrderBuilder` y llama a su función `build()`. Esta función:
1. Recorre los ítems del pedido,
2. consulta el peso de cada item,
3. calcula la suma total del peso en gramos,
4. genera un código de paquete,
5. genera una etiqueta de manipulación (Fragil/No Frágil). 
    
Los datos resultantes de `build()`: [customer_email, address, priority, fragility, total_weight, package_code, handling_label e items], en un array. Luego en `handle_create_order()` (*con los datos del pedido ya construidos y resueltos por la implementación del patrón `OrderBuilder.build()`*), la función inserta el pedido resuelto en la tabla `orders` de la db.

De allí, el código realiza la selección del proveedor de transporte. Aquí, se aplica el patrón de comportamiento Strategy, se crea un objeto `\App\Strategy\DeliverySelector()` con un array de las estrategias disponibles:
- EcoBikeStrategy: Aplica para pedido=express y fragilidad=(debil/alta).
- MotoYAStrategy: Para peso<=1200g y que NO sean: pedido=express y fragilidad=(Debil/Alta).
    >  ↑ el peso fue determinado por el código base, más no por una Regla de Negocio 
- PaqzStrategy: Es el Fallback, es decir, si las dos anteriores no se acomodan al pedido, sí o sí "cae" a esta tercera opción.

Para lo anterior, se llama la función `select()`. Donde cada estrategia contiene la interfaz `DeliveryStrategyInterface`, define cuándo se aplica `matches()`, devuelve el nombre del proveedor `getProviderName()` y un `getAdapter` que trae la comunicación con la API del proveedor (simulado).

Una vez seleccionada la estrategia, se solicita la recogida al proveedor mediante el patrón estructural Adapter. Todos los adaptadores (`EcoBikeAdapter`, `MotoYAAdapter` y `PaqzAdapter`) contienen la interfaz `DeliveryAdapterInterface` y exponen la función `requestPickup()`. Esta *request* devuelve un código de seguimiento. Finalmente se insertan las notificaciones para avisar que el pedido ha sido confirmado y se cierra la transacción.

## Patrones de diseño aplicados

| Patrón | Familia | Función en el proyecto | Uso |
|-|-|-|-|
| **Builder** `\App\Builder\OrderBuilder` | **Creacional** | Encapsula la creación de la estructura del pedido. Consulta datos de cada ítem, calcula el peso total, genera un código de paquete y determina la etiqueta de manipulación. Devuelve un arreglo con todas estas propiedades. | `handle_create_order()` crea un `OrderBuilder`, llama a `build()` y usa el resultado para insertar el pedido y sus ítems. |
| **Strategy** `\App\Strategy\DeliverySelector` + estrategias concretas | **Comportamental** | Selecciona el proveedor de envío según los datos del pedido, devuelve la primera estrategia que coincida. Cada estrategia conoce su proveedor y el adapter asociado. | `handle_create_order()` llama `DeliverySelector.select()`, luego llama `getProviderName()` y `getAdapter()` para continuar el flujo. |
| **Adapter** `\App\Adapter` + adaptadores concretos | **Estructural** | Interacción con APIs de proveedores "externos". Los adaptadores generan un tracking simulando la respuesta de la API real. | La Strategy seleccionada devuelve el adaptador correspondiente. `requestPickup()` se llama con los datos del pedido, devolviendo un tracking que se inserta en `shipments`. |
| **Singleton** `\App\Singleton\Database` | **Creacional** | Única instancia de conexión a la base de datos. La clase `Database` tiene la función `getInstance()` que crea esta conexión la primera vez y la reutiliza en llamadas posteriores (`PDO`). La función global `db()` de `app/db.php` retorna esa instancia única. | Todas las operaciones de la aplicación obtienen la conexión mediante `db()` |

---

## Créditos
Proyecto desarrollado por Santiago Bedoya, Kamila Mena y Edwarlin Mosquera en el curso de Arquitectura de Sistemas.
