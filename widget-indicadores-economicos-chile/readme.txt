=== Widget Indicadores Econ&oacute;micos (Chile) ===
Contributors: Cristhopher Riquelme
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=KJMRNVD96DLZA
Tags: chile, indicadores, economicos, dolar, euro, uf, utm, tcm
Requires at least: 3.0.1
Tested up to: 3.6
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Muestra los principales indicadores econ&oacute;micos para Chile.
UF, UTM, D&oacute;lar, Euro, TCM

== Description ==

Un widget que entrega (a elecci&oacute;n del administrador) los principales indicadores econ&oacute;micos para Chile.
Estos se actualizan diariamente y se guardan en la propia base de datos de wordpress, agilizando en gran medida las peticiones al servidor y la carga de los indicadores.

== Installation ==

1. Descomprime el archivo y sube la carpeta a "/wp-content/plugins/"
2. Activa el plugin en la opci&oacute;n "Plugins" del men&uacute; de wordpress
3. En la opci&oacute;n "Widgets" toma y arrastra el widget "Indicadores Econ&oacute;micos (Chile)" a tu sidebar o al &aacute;rea donde deseas visualizarlo

Nota: T&uacute; decides si quieres dejarle el dise&ntilde;o que trae por defecto o si quieres dise&ntilde;arlo a tu gusto.

== Frequently Asked Questions ==

= &iquest;Qu&eacute; indicadores econ&oacute;micos entrega? =
Los valores actuales del D&oacute;lar, Euro, UF, UTM, y TCM.

= No muestra o no actualiza los indicadores econ&oacute;micos  =
Puede que el servidor donde tienes instalado wordpress tenga desactivada la funci&oacute;n "file_get_contents" o deshabilitada la directiva "allow_url_fopen" en php.ini

== Screenshots ==

1. Opciones del Widget
2. Dise&ntilde;o por defecto del widget

== Changelog ==

= 1.5 =
* Estructura HTML del widget modificada
* Se agreg&oacute; el t&iacute;tulo del widget y la fecha actual
* Opci&oacute;n para habilitar/deshabilitar el dise&ntilde;o que trae por defecto
* Modificaciones menores al c&oacute;digo

= 1.2 =
* Integraci&oacute;n de cURL para actualizar indicadores en caso de no poder utilizar la funci&oacute;n "file_get_contents"

= 1.0 =
* Primera versi&oacute;n del plugin

== Upgrade Notice ==

= 1.5 =
Varias modificaciones importantes adem&aacute;s de &uacute;tiles, especificadas en el Changelog.

= 1.2 =
Ahora el script se puede conectar mediante cURL para actualizar los indicadores en caso de que el servidor tenga deshabilitada la directiva "allow_url_fopen".

= 1.0 =
This version fixes a security related bug. Upgrade immediately.