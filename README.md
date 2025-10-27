# Stock Portfolio Class Test

## Descripción

Este es un ejemplo de una *Clase* para control de ***Cartera de Inversión***, creado como parte del proceso de postulación para una (*increíble*) vacante.

Fue escrito haciendo uso de mi impresionante desconocimiento y mi amplia ignorancia sobre el tema de las ***Carteras de Inversión***, después de dedicar una noche *casi* completa a profundos estudios al respecto. Y aunque el tiempo establecido para esta tarea no permitió pruebas exhaustivas, toda mi inexperiencia en el sector bursátil me indica que el proceso de ***Rebalance*** hace lo que debería hacer.

### De acuerdo con las instrucciones:

- La clase de la **Cartera** es **Portfolio**.
- Cuenta con una colección de **Activos**, los cuales son instancias de una clase auxiliar llamada **Portfolio_Asset**.
- La clase **Portfolio_Asset** provee el método público **current_price()** para recuperar el precio del **Activo**.
- La clase **Portfolio** provee los métodos públicos **check_and_rebalance()** y **do_rebalance()**. El primero evalúa si el ***Rebalance*** es necesario y de serlo, lo efectúa. Para ello depende del segundo método, el cual efectúa el ***Rebalance*** directamente, sin comprobaciones.

### Duda de reglas del negocio:

Durante el curso de mi extensa investigación, no fui capaz de resolver una cuestión que podría afectar parte de la lógica de la clase. Asumo que si el precio de los ***Activos*** fluctúa, antes de realizar el ***Rebalance*** de la ***Cartera***, el valor total de esta cambia también. No me queda claro qué hacer con la diferencia, ni si el ***Rebalance*** debe calcularse sobre el valor inicial o el valor recalculado de la ***Cartera***, así que por ahora me limito a almacenar ese valor como un acumulado en una propiedad de la clase **Portfolio** y me olvido de él. Todos los cálculos se hacen sobre el valor inicial Asignado a la ***Cartera***.

## Aclaraciones

Debido a que las instrucciones del ejercicio ofrecen un amplio margen de estilo y técnica para la resolución del problema, creo pertinente hacer las siguientes aclaraciones:

- No fueron creados propiedades o métodos más allá de los requeridos para la satisfacción de las condiciones del ejercicio.
- Las propiedades de las clases son declaradas todas como **Privadas**. Ya que se asume que la naturaleza de estas operaciones es delicada, todas los accesos a las propiedades de las clases se hacen a través de los **getters** y **setters** correspondientes.
- Solo se proveen los **getters** y **setters** necesarios para el ejemplo.
- La configuración de cada ***Cartera*** y sus ***Activos*** está almacenada y es recuperada de algún medio de almacenamiento persistente por medio de un **ID** que se proporciona al momento de instanciar la clase de la ***Cartera*** (**Portfolio**).
- La naturaleza del almacenamiento persistente es irrelevante, pero se instancían las clases hipotéticas **Persistent_Storage** y **Query_Builder** para representar el acceso. Es en este almacenamiento hipotético donde se guardarían parámetros como los que solicitan las instrucciones (*40% META, 60% APPL*).
- Se asume que operaciones como recuperar el precio de los ***Activos*** o la compra-venta de los mismos se realiza a través de alguna agencia de corredores de bolsa, que debería de contar con una *API*, la cual se representa con la clase **Broker_API**.
- Aunque la descripción de la tarea está en inglés, la vacante dice ­«*Requires applying in Spanish*», por lo que tanto este **README** como los comentarios del código están en español.
- Elegí PHP porque es el lenguaje con el que últimamente he estado trabajando, así que lo tengo más presente, no por ninguna preferencia particular (de hecho, prefiero C#).

## Agradecimientos

A las amables personas de reclutamiento que se han tomado el tiempo de revisar este repositorio, les agradezco infinitamente. Me reitero a sus órdenes en [LinkedIn](https://www.linkedin.com/in/crro-it/).
