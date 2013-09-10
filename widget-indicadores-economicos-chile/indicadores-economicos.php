<?php
/*
Plugin Name: Indicadores Económicos (Chile)
Plugin URI: http://wordpress.org/plugins/widget-indicadores-economicos-chile/
Description: Muestra los principales indicadores económicos para Chile.
Version: 1.5
Author: Cristhopher Riquelme
Author URI: mailto:cristriq@gmail.com
License: GPL2
*/

global $table_db_version;
$table_db_version = "1.0";

function table_install() {
	global $wpdb;
	global $table_db_version;
	$table_name = $wpdb->prefix . "indica_econo";

	$sql = "CREATE TABLE $table_name (
			id int(5) NOT NULL AUTO_INCREMENT,
			cod_indi varchar(30) DEFAULT '',
			val_indi decimal(12,2) DEFAULT 0,
			fecha_indi datetime DEFAULT '0000-00-00 00:00:00',
			UNIQUE KEY id (id)
			);";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	$class = new Indicadores_Widget();
	$class->actualizar_indicadores();
	
	add_option( "table_db_version", $table_db_version );
}
register_activation_hook( __FILE__, 'table_install' );

function table_uninstall() {
	global $wpdb; 
	$table_name = $wpdb->prefix . "indica_econo";
	
	$sql = "DROP TABLE $table_name";
	$wpdb->query($sql);
}
register_deactivation_hook( __FILE__, 'table_uninstall' );

function myplugin_update_db_check() {
    global $table_db_version;
    if (get_site_option( 'table_db_version' ) != $table_db_version) {
        table_install();
    }
}
add_action( 'plugins_loaded', 'myplugin_update_db_check' );

function carga_estilos_plugin() {
	wp_register_style('estilos_indicadores',
						plugins_url( 'css/style-indicadores.css' , __FILE__ ),
						array(),
						'1.0',
						'all');
	wp_enqueue_style('estilos_indicadores');
}
add_action('wp_print_styles', 'carga_estilos_plugin');

/*--------------------------------------------------------------------------------------------*/

function widget_register_indicadores_economicos() {
    register_widget('Indicadores_Widget');
}
add_action('widgets_init', 'widget_register_indicadores_economicos');

class Indicadores_Widget extends WP_Widget {
	
	public function __construct() {						
		parent::__construct(
			'indicadores-economicos-cl', //ID
			'Indicadores Económicos (Chile)', //Nombre			
			array(
				'classname' => 'widget_indicadores_economicos',
				'description' => 'Mostrar los principales indicadores económicos para Chile'
				) //Descripción			
		);		        
	}
	
	public function widget( $args, $instance ) {		
		$this->actualizar_indicadores();
		$this->mostrar_indicadores($instance);
	}
	
	public function form( $instance ) {
		global $wpdb;
		
		// Obligamos a $instance a ser un array con todas las opciones disponibles
		$instance = wp_parse_args( (array)$instance, array(
			'id_contenedor'  => '',
			'separador'      => '',
			'indicadores'    => '',
			'titulo_widget'  => '',
			'aplicar_diseno' => 0
		));
		
		// Filtramos los valores para que se muestren correctamente en los formularios
		$instance['id_contenedor'] = esc_attr($instance['id_contenedor']);
		$instance['separador'] = esc_attr($instance['separador']);
		$instance['indicadores'] = unserialize($instance['indicadores']);
		$instance['titulo_widget'] = esc_attr($instance['titulo_widget']);
		$instance['aplicar_diseno'] = esc_attr($instance['aplicar_diseno']);
		//echo'<pre>'; print_r($instance); echo'</pre>';
		
		// Mostramos el formulario
		?>
		<p>
			<label for="<?php echo $this->get_field_id('titulo_widget'); ?>">Título:</label>
			<input value="<?php echo $instance['titulo_widget']; ?>" class="widefat" type="text" id="<?php echo $this->get_field_id('titulo_widget'); ?>" name="<?php echo $this->get_field_name('titulo_widget'); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('id_contenedor'); ?>">ID lista de indicadores:</label>
			<input value="<?php echo $instance['id_contenedor']; ?>" class="widefat" type="text" id="<?php echo $this->get_field_id('id_contenedor'); ?>" name="<?php echo $this->get_field_name('id_contenedor'); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('separador'); ?>">Separador de indicadores:</label>
			<input value="<?php echo $instance['separador']; ?>" class="widefat" type="text" id="<?php echo $this->get_field_id('separador'); ?>" name="<?php echo $this->get_field_name('separador'); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('indicadores'); ?>">Indicadores a mostrar:</label><br />
			<?php
			$result = $wpdb->get_results("SELECT id, cod_indi FROM wp_indica_econo ORDER BY id ASC");
			foreach ( $result as $i => $row ):
			?>
			<input type="checkbox" name="<?php echo $this->get_field_name('indicadores').'['.$row->id.']'; ?>" id="<?php echo $this->get_field_id('indicadores').$row->id; ?>" value="<?php echo $row->cod_indi; ?>" <?php checked(isset($instance['indicadores'][$row->id]) ? 1 : 0); ?> /><span> <?php echo $row->cod_indi; ?></span><br />
			<?php
			endforeach;
			?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('aplicar_diseno'); ?>">¿Diseño por defecto?:</label>
			<input type="checkbox" name="<?php echo $this->get_field_name('aplicar_diseno'); ?>" id="<?php echo $this->get_field_id('aplicar_diseno'); ?>" value="1" <?php echo ($instance['aplicar_diseno'] == 1) ? 'checked="checked"' : ''; ?> />
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return array(
			'id_contenedor' => strip_tags($new_instance['id_contenedor']),
			'separador'     => strip_tags($new_instance['separador']),
			'indicadores'   => serialize($new_instance['indicadores']),
			'titulo_widget' => strip_tags($new_instance['titulo_widget']),
			'aplicar_diseno' => strip_tags($new_instance['aplicar_diseno'])
		);
	}

	public function mostrar_indicadores( $instance ) {
		global $wpdb;
		$sql_indicadores = '';
		$total_registros = 0;
		
		$instance['indicadores'] = unserialize($instance['indicadores']);
		if ( !empty($instance['indicadores']) ) {
			$sql_indicadores = "'".implode("','", $instance['indicadores'])."'";
		}
		
		$result = $wpdb->get_results("SELECT cod_indi, val_indi FROM wp_indica_econo WHERE cod_indi IN({$sql_indicadores}) ORDER BY id ASC");
		$total_registros = $wpdb->num_rows;
		?>
		<div id="widget-indicadores-economicos-chile" <?php echo ( $instance['aplicar_diseno'] == 1 ) ? 'class="default-style"' : ''; ?>>
			<?php if ( !empty($instance['titulo_widget']) ) echo '<h3>'.$instance['titulo_widget'].'</h3>'; ?>
			<span class="fecha-hoy"><?php echo $this->fecha_actual(); ?></span>
			<ul <?php echo ( !empty($instance['id_contenedor']) ) ? 'id="'.$instance['id_contenedor'].'"' : '' ; ?>>
			<?php
			$i = 1;
			foreach ( $result as $row ):
				echo '<li><span class="indicador">' . $row->cod_indi . ':</span> $' . number_format($row->val_indi,2,',','.') . '</li>';
				if ( $instance['separador'] != '' && $i != $total_registros ) {
					echo '<li class="separator">'.$instance['separador'].'</li>';
				}
				$i++;
			endforeach;
			?>
			</ul>
		</div>
		<?php       
	}

	public function fecha_actual() {
		$dias = Array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
		$meses = Array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
		
		$dia_semana = date("w");
		$dia = date("j");
		$mes = date("n") - 1;
		$anio = date("Y");
		
		return $dias[$dia_semana] . " " . $dia . " de " . $meses[$mes] . " de " . $anio;
	}
	
	public function actualizar_indicadores() {
		global $wpdb;
		
		$result = $wpdb->get_row("SELECT count(id) as total_registros FROM wp_indica_econo WHERE DATE_FORMAT(fecha_indi,'%d-%m-%Y')='".date("d-m-Y")."'");
		if ( $result->total_registros == 0 ) { 
			$contenido_url = '';
			$fecha_hoy = '';
			$array_datos = array();
			$indicadores = array();
			
			$fecha_hoy = current_time('mysql'); /*date("Y-m-d H:i:s")*/
			$url_indicadores = 'http://si3.bcentral.cl/indicadoresvalores/secure/indicadoresvalores.aspx';
			
			if( ini_get('allow_url_fopen') ) { // Es necesario tener habilitada la directiva allow_url_fopen para usar file_get_contents
				$contenido_url = file_get_contents($url_indicadores);
			} else { // De otra forma utilizamos cURL
				$curl = curl_init($url_indicadores);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				$contenido_url = curl_exec($curl);
				curl_close($curl);
			}
			
			if ( !empty($contenido_url) ) {
				preg_match_all('/<span[^>]+>(.*?)<\/span>/', $contenido_url, $array_datos);
				//echo '<pre>'; print_r($array_datos); echo '</pre>';
				
				$indicadores[] = array( //UF
						0 => 'UF', //$array_datos[1][1],
						1 => str_replace(',', '.', preg_replace('/[^0-9,]/', '', $array_datos[1][2]))
				);
				$indicadores[] = array( //UTM
						0 => 'UTM', //$array_datos[1][3],
						1 => str_replace(',', '.', preg_replace('/[^0-9,]/', '', $array_datos[1][4]))
				);
				$indicadores[] = array( //Dólar
						0 => 'Dólar', //$array_datos[1][5],
						1 => str_replace(',', '.', preg_replace('/[^0-9,]/', '', $array_datos[1][6]))
				);
				$indicadores[] = array( //Euro
						0 => 'Euro', //$array_datos[1][7],
						1 => str_replace(',', '.', preg_replace('/[^0-9,]/', '', $array_datos[1][8]))
				);
				$indicadores[] = array( //TCM
						0 => 'TCM', //$array_datos[1][9],
						1 => str_replace(',', '.', preg_replace('/[^0-9,]/', '', $array_datos[1][10]))
				);
				//echo '<pre>'; print_r($indicadores); echo '</pre>';
				
				if ( !empty($indicadores) ) {
					$wpdb->query("TRUNCATE TABLE wp_indica_econo"); //$wpdb->query("DELETE FROM wp_indica_econo");
					
					foreach ( $indicadores as $indicador ):
						$result = $this->join($indicador[0], $indicador[1], $fecha_hoy);
					endforeach;
				}
			}
		}
		
	}

	public function join($nom_indicador, $val_indicador, $fecha_indicador) {
		global $wpdb;
		//$result = $wpdb->query("INSERT INTO wp_indica_econo (cod_indi, val_indi, fecha_indi) VALUES ('{$nom_indicador}', {$val_indicador}, {$fecha_indicador})");
		$result = $wpdb->insert( 'wp_indica_econo', array( 'cod_indi' => $nom_indicador, 'val_indi' => $val_indicador, 'fecha_indi' => $fecha_indicador ) ); //metodo que se encarga de insertar datos en la base de datos.
		return $result;
	}
	
}