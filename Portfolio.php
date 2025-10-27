<?php

	/* Esta es la clase solicitada en el ejercicio */
	class Portfolio {

		/* Las propiedades */
		private $ID; // El ID de la Cartera en el repositorio persistente de datos.
		private $assets_data; // La colección de Activos, un array de instancias de la clase Portfolio_Asset.
		private $assets_count; // La cantidad de Activos en la colección.
		private $value_initial; // El valor inicial de la Cartera.
		private $value_last; // El valor de la Cartera, recalculado tras actualizar los precios de los Activos.
		private $must_rebalance; // Una bandera que indica si es necesario efectuar el Rebalance.

		// Obviamente, podría haber más propiedades
		// no representadas en este ejemplo:
		// private whatever_1;
		// ...
		// private whatever_n;

		// El constructor.
		// Parámetros:
		// // $portfolio_id - El ID del registro almacenado en el repositorio permanente de datos.
		public function __construct($portfolio_id) {

			// Se recupera la información de la Cartera del almacenamiento permanente.
			// Se espera de regreso un array con los nombres de las propiedades como índices.
			$portfolio_data = $this->read_from_storage($portfolio_id);

			// Se llama a la función que procesa la información sobre la colección de Activos de la Cartera.
			// Se espera de regreso un objeto con la Colección de Activos y una propiedad indicando la cantidad de Activos en la colección.
			$assets_obj = $this->parse_assets_data($portfolio_data['assets']);

			// Se establecen las propiedades de la clase
			$this->ID = $portfolio_id;
			$this->value_initial = $portfolio_data['initial_value'];
			$this->assets_count = $assets_obj->qty;
			$this->assets_data = $assets_obj->collection;

			// Se llama a la función que recupera los precios actualizados de los Activos y reprocesa las Asignaciones.
			$this->calculate_allocations();
		}

		// Función que recupera la información de la Cartera del repositorio persistente de datos.
		// Parámetros:
		// // $portfolio_id - El ID del registro almacenado en el repositorio permanente de datos.
		// Devuelve: Un array con los nombres de las propiedades como índices.
		private function read_from_storage($portfolio_id) {

			$persistent_storage = new Persistent_Storage(); // Instanciamiento de la clase hipotética.
			$portfolio_query = $this->build_portfolio_query($portfolio_id); // Se llama a la función que crea la petición para el almacenamiento persistente.
			$portfolio_data = $persistent_storage->get_stored_data($portfolio_query); // Se hace la petición al almacenamiento persistente.
			$persistent_storage = null; // Ponemos nuestra basura en su lugar.

			return $portfolio_data;
		}

		// Función que crea la petición de la información de una Cartera al repositorio persistente de datos.
		// Parámetros:
		// // $portfolio_id - El ID del registro almacenado en el repositorio permanente de datos.
		// Devuelve: Un objeto con la petición para el repositorio, cuya estructura y contenido salen del ámbito de este ejemplo.
		private	function build_portfolio_query($portfolio_id) {

			$query_params = [
				$param_1 = 'portfolios',
				$param_2 = $portfolio_id,
				...$params /* Whatever */
			]; // Los parámetros para crear la petición.
			$query_builder = new Query_Builder(); // Instanciamos la clase hipotética.
			$query_data = $query_builder->get_query($query_params); // Creamos la petición.
			$query_builder = null; // Ponemos la basura en su lugar.

			return $query_data;
		}

		// Función que procesa la información sobre la colección de Activos de la Cartera.
		// Parámetros:
		// // $assets_records - Un array con los regitros de Activos asociados al ID de la Cartera recuperada del almacenamiento persistente.
		// Devuelve: Un objeto con dos propiedades:
		// // collection - La colección de Activos, un array de instancias de Portfolio_Asset.
		// // qty - La cantidad de Activos en la colección.
		private function parse_assets_data($assets_records) {

			// Inicializamos valores por default
			$asset_collection = array();
			$asset_count = 0;

			// Validamos que haya información de Activos que procesar
			if ($assets_records && is_array($assets_records)) {

				foreach ($assets_records as $asset_record) {

					$new_asset = new Portfolio_Asset($asset_record); // Instanciamos un objeto para cada Activo.
					$asset_collection[] = $new_asset;
					$asset_count++;
				}
			}

			return (object)array('qty' => $assets_count, 'collection' => $asset_collection);
		}

		// Función que recalcula las Asignaciones de los Activos tras recuperar sus precios actualizados.
		private function calculate_allocations() {

			$this->value_last = 0; // Inicializamos el acumulado de los nuevos valores de los Activos.

			foreach ($this->assets_data as $asset) {

				// Inicializamos una variable que pasaremos por referencia más adelante para recibir información de la Asignación de cada Activo:
				$messages = [];

				$asset->current_price(); // Ejecutamos el método público de la clase Portfolio_Asset para actualizar el valor del Activo.
				$percentage = $asset->get_asset_value() / $this->value_initial * 100; // Calculamos el porcentaje actual del valor del Activo con respecto al valor inicial de la Cartera.

				// Llamamos al setter de la clase Portfolio_Asset que establecerá el nuevo porcentaje calculado y nos reportará
				// si dicho porcentaje excede la variación tolerada según la configuración inicial de la Cartera,
				// en un parámetro que le pasamos por referencia.
				$asset->set_allocation_last_perc($percentage, $messages);

				// Si el nuevo porcentaje de Asignación excede la variación tolerada, establece la bandera
				// que indica que se requiere el Rebalance.
				if (isset($messages['Exceeded']) && $messages['Exceeded'] === true) $this->must_rebalance = true;

				// Actualizamos el acumulado de los nuevos valores de los Activos, añadiendo el valor total del Activo actual.
				$this->value_last += $asset->get_asset_value();
			}
		}

		// Función pública que recalcula las Asignaciones de los Activos y reporta si es necesario el Rebalance de la Cartera.
		// Devuelve: Un boolean indicando si debe efectuarse el Rebalance:
		// // true - La Cartera debe ser Rebalanceada.
		// // false - No es necesario Rebalancear la Cartera.
		public function rebalance_needed() {

			$this->calculate_allocations(); // Función que recalcula las Asignaciones.
			return $this->must_rebalance; // Se recupera de la propiedad correspondiente de la clase actual.
		}

		// Función ppublica que efectúa el Rebalance de la Cartera directamente, sin realizar más comprobaciones.
		public function do_rebalance() {

			foreach ($this->assets_data as $asset) {

				// Recuperamos nuevamente el último precio del Activo, y esta vez almacenamos el valor.
				$asset_price = $asset->current_price();

				// Calculamos el monto que se debe asignar al Activo, de acuerdo a su porcentaje del monto incial de la Cartera asignado.
				$required_value = $asset->get_allocated_perc() / 100 * $this->value_initial;

				// Recalculamos cuántas unidades del Activo requerimos, dividiendo el monto que se debe asignar al Activo entre su precio.
				// Siendo que el precio bien podría llegar a ser equivalente a cero, hacemos una validación rápida que nos evite la división en ese caso.
				$required_units = (!is_numeric($asset_price) || $asset_price === 0) ? 0 : $required_value / $asset_price;

				// Llamamos a la función pública de la clase Portfolio_Asset para ejecutar el Rebalance del Activo.
				$asset->rebalance($required_units);
			}

			$this->calculate_allocations(); // Terminando la operación, recalculamos de nuevo todo.
		}

		// Función pública que primero valida si es necesario el Rebalance de la Cartera y en caso afirmativo, lo ejecuta.
		public function check_and_rebalance() {

			if ($this->rebalance_needed()) $this->do_rebalance();
		}
	}

	/* La clase auxiliar para los elementos de la Colección de Activos */
	class Portfolio_Asset {

		/* Las propiedades */
		private $ID; // Suponemos que debe tener alguna especie de ID.
		private $asset_class; // Clase de Activo (Stock, Bond, etc...).
		private $asset_code; // Algún codigo debe tener (como APPL o META).
		private $asset_units; // Cantidad de unidades del Activo.
		private $asset_total_value; // Valor total del Activo (precio * unidades).

		private $allocation_exceeded = false; // Una bandera que indica si el valor actual del Activo excede los límites establecidos por la Asignación
		private $allocation_perc; // El porcentaje original Asignado al Activo.
		private $allocation_max_shift; // La tolerancia máxima para la variación en el valor del Activo.
		private $allocation_last_perc; // Último porcentaje del Activo con respecto a la Cartera, calculado con el último precio recuperado.

		private $price_last_update; // Fecha y hora de la última actualización de precio.
		private $price_last_value; // Último precio recuperado.
		private $price_last_variation; // Última variación de precio recuperada.
		private $price_prev_value; // Precio anterior al último recuperado.

		// Obviamente, aquí también podría haber más propiedades:
		// private whatever_1;
		// ...
		// private whatever_n;

		// El constructor.
		// Parámetros:
		// // $asset_properties - Un array con la información del Activo recuperada del almacenamiento persistente, inidizado con los nombres de las propiedades.
		public function __construct($asset_properties) {

			// Se asignan las propiedades con la información del registro:
			$this->ID = $asset_properties['ID'];
			$this->asset_class = $asset_properties['class'];
			$this->asset_code = $asset_properties['code'];
			$this->asset_units = $asset_properties['units'];
			$this->allocation_perc = $asset_properties['percentage'];
			$this->allocation_max_shift = $asset_properties['max_shift'];
		}

		// El método público que se solicitó en las instrucciones para recuperar el precio del Activo.
		// Simula el consumo de la API de un Broker, haciendo uso de una clase hipotética.
		public function current_price() {

			$broker_api_params = [
				$param_1 = 'asset_price',
				$param_2 = $asset_class,
				$param_3 = $asset_code,
				...$params /* Whatever */
			]; // Parámetros de la petición a la API.

			// Llamada a la función que consume la API.
			// Espera de regreso un objeto con la información del último estado del Activo en la Bolsa.
			$new_price_obj = $this->broker_api_call($broker_api_params);

			// Se establece el valor de las propiedades de la clase de acuerdo a la nueva información de precios:
			$this->price_prev_value = $this->price_last_value;
			$this->price_last_value = $new_price_obj->price;
			$this->price_last_variation = $new_price_obj->variation;
			$this->price_last_update = $new_price_obj->last_updated;
			$this->asset_total_value = $this->price_last_value * $this->asset_units;

			// Devolvemos el valor del último precio (lo utilizaremos en una ocasión).
			return $this->price_last_value;
		}

		// La función pública que efectúa el Rebalance del Activo.
		// Parámetros:
		// // $required_units - La cantidad de unidades necesarias para que este Activo cumpla su porcentaje de Asignación.
		public function rebalance($required_units) {

			$unit_difference = $required_units - $this->asset_units; // Calculamos la diferencia entre la cantidad de unidades que tenemos y las que necesitamos.

			if ($unit_difference === 0) return false; // Si no hay diferencia, nada que hacer. Salimos.

			// Si hay diferencia continuamos. Para la operación de compra-venta de Activos, nuevamente consumiremos la API del Broker:

			$broker_api_params = [
				$param_1 = 'asset_trade',
				$param_2 = (($unit_difference < 0) ? 'sell' : 'buy'),
				$param_3 = abs($unit_difference),
				...$params /* Whatever */
			]; // Parámetros de la petición a la API.

			$this->broker_api_call($broker_api_params); // Llamada a la función que consume la API.
			$this->asset_units = $required_units; // Actualizamos la cantidad de las unidades del Activo en la propiedad correspondiente.
			$this->update_stored_data('asset_units'); // Llamada a una función que actualiza el valor de las unidades del Activo en el almacenamiento persistente.
		}
		
		// Función que simula la actualización de la configuración del activo en el repositorio persistente de datos.
		// Su estructura no es tema de este ejemplo, asumimos que hace lo que debe.
		private function update_stored_data(...$params) {
			
			return true;
		}

		// Función que consume la API del Broker.
		// Parámetros:
		// // $params - Un array que contiene los parámetros para la petición.
		// Devuelve: Un objeto con la respuesta de la API.
		private function broker_api_call($params) {

			$broker_api = new Broker_API(); // Instanciamos la clase para consumir la API.
			$output = $broker_api->consume($params); // Realizamos la petición y almacenamos la respuesta.
			$broker_api = null; // Sacamos la basura.

			return $output;
		}

		/* ** Getters, Setters y algunas validaciones ** */

		// Ejemplo de función de validación.
		private function values_validations($var_to_eval) {

			return true;
		}

		// Un par de Getters
		public function get_allocated_perc() {

			return $this->allocation_perc;
		}

		public function get_asset_value() {

			return $this->asset_total_value;
		}

		// Setter que actualiza el porcentaje de Asignación del Activo de acuerdo al último precio recuperado
		// y reporta si se ha excedido la variación máxima tolerada para la Asignación.
		// Parámetros:
		// // $value - El nuevo valor del porcentaje para la propiedad de la clase.
		// // $response_details - Un array pasado por referencia donde se almacenará un mensaje en caso de detectar que la Asignación ha excedido su variación máxima tolerada.
		// Devuelve: Un booleano que indica si la operación se realizó con éxito.
		public function set_allocation_last_perc($value, &$response_details = []) {

			// Simulamos una pequeña validación...
			if ($this->values_validations($value)) {

				$this->allocation_last_perc = $value; // Actualizamos la propiedad.
				$this->allocation_exceeded = ($this->allocation_last_perc < ($this->allocation_perc - $this->allocation_max_shift) || ($this->allocation_perc + $this->allocation_max_shift) < $this->allocation_last_perc); // Calculamos si el porcentaje actualmente asignado excedió la variación máxima tolerada.
				$response_details = ['Exceeded' => $this->allocation_exceeded]; // Poblamos el parámetro recibido por referencia para reportar si el porcentaje de Asignación excedió los límites.

				return true;
			}

			return false;
		}
	}


?>
