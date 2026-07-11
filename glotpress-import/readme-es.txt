=== Plogins Waitlist - Back in Stock for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, back in stock, waitlist, stock notification, email
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lista de espera de reposición para WooCommerce. Tus clientes dejan su correo y les avisas cuando el producto vuelve al stock. Accesible y sin saltos de maquetación.

== Description ==

Plogins Waitlist añade un formulario de lista de espera a los productos de WooCommerce sin stock. Un cliente introduce su correo y, cuando vuelves a poner el producto «En stock», Plogins Waitlist avisa por correo a todas las personas en espera mediante el propio motor de correo de WordPress de tu sitio. No hay ningún servicio externo, ninguna cuenta que crear y nada sale de tu base de datos.

El formulario se renderiza en PHP en el resumen de producto individual, donde se sitúa en el flujo normal de la página en lugar de inyectarse tras la carga, así que no desplaza el contenido de alrededor. Al enviarlo se ejecuta una pequeña petición `fetch` en JavaScript puro, cargada con `defer` en el pie de página; el plugin no añade jQuery propio. En los productos variables se engancha al script de variaciones que ya trae WooCommerce, de modo que el formulario solo aparece una vez seleccionada una variación no disponible.

La accesibilidad fue una prioridad desde el principio y no una ocurrencia de última hora. El campo de correo lleva una etiqueta oculta visualmente, el consentimiento es una casilla obligatoria real y el mensaje de éxito o error se anuncia en una región `aria-live`, mientras que el formulario informa `aria-busy` durante el envío.

Los datos de los suscriptores viven en una única tabla `{prefix}_restock_waitlist` que el plugin crea y cuya versión controla. Las notificaciones se disparan en el hook `woocommerce_product_set_stock_status`, así que no hay ninguna cola ni tarea cron en segundo plano. Al desinstalar se elimina la tabla y se borran las opciones del plugin, sin dejar nada atrás.

Código fuente e incidencias: https://github.com/wppoland/plogins-waitlist . Los parches y los informes de errores son bienvenidos allí.

<strong>Funciones</strong>

* Formulario de lista de espera que se muestra automáticamente en las páginas de productos sin stock y en reserva («en reserva»)
* Productos variables: el formulario aparece después de que el cliente seleccione una variación no disponible
* Pestaña de WooCommerce <strong>Mi cuenta → Listas de espera</strong> para clientes con sesión iniciada (revisar listas, salir de la lista de espera)
* Envío asíncrono con una llamada fetch en JavaScript puro, de modo que la página no se recarga
* Campo de correo rellenado previamente para los clientes con sesión iniciada
* Casilla de consentimiento obligatoria en cada suscripción
* Notificación automática por correo en texto plano al reponer, enviada mediante `wp_mail`
* Encabezado y texto introductorio opcionales mostrados sobre el formulario
* Etiquetas del formulario, texto del botón, mensajes de envío en pantalla y asunto/introducción/cierre del correo de notificación personalizables
* Coloca el formulario con el shortcode `[restock_waitlist]` o el widget «Formulario de lista de espera» de Elementor
* Activa o desactiva las suscripciones de invitados (sin sesión iniciada)
* Línea de prueba social «N clientes ya están en espera» sobre el formulario (opcional, personalizable, con singular/plural)
* Lista de suscriptores en la administración con filtro por producto, exportación a CSV y eliminación con un clic
* Plantilla de formulario sobrescribible desde el tema (`yourtheme/restock/single-product/waitlist-form.php`)
* Compatible con WooCommerce HPOS (tablas de pedidos personalizadas) y los bloques de Carrito/Pago

= You may also like these plugins =

Más plugins gratuitos de WooCommerce de WPPoland:

* [Plogins Tiers](https://wordpress.org/plugins/plogins-tiers/) - niveles de precios por cantidad y volumen con una tabla de precios renderizada en el servidor.
* [Sieve - Search & Filter](https://wordpress.org/plugins/sieve/) - búsqueda y filtrado de productos por AJAX rápidos para WooCommerce, sin jQuery.
* [Polski for WooCommerce](https://wordpress.org/plugins/polski/) - cumplimiento para el mercado polaco: GPSR, Omnibus, RGPD, facturas y módulos de tienda.

Explora el catálogo completo en https://plogins.com/es/ .

== Installation ==

1. Instala y activa WooCommerce (8.0 o posterior).
2. Instala Plogins Waitlist desde el directorio de plugins de WordPress o sube la carpeta `plogins-waitlist` a `/wp-content/plugins/`.
3. Activa el plugin desde la pantalla <strong>Plugins</strong>.
4. Opcionalmente entra en <strong>WooCommerce → Plogins Waitlist</strong> para personalizar las etiquetas y el texto de las notificaciones; los valores por defecto razonables funcionan de inmediato.
5. El formulario de lista de espera aparece automáticamente en cualquier página de producto sin stock o en reserva.

== Frequently Asked Questions ==

= Documentation and links =

* <strong>Documentación</strong> - https://plogins.com/es/plogins-waitlist/docs/
* <strong>Página del plugin</strong> - https://plogins.com/es/plogins-waitlist/
* <strong>Código fuente</strong> - https://github.com/wppoland/plogins-waitlist
* <strong>Informes de errores y peticiones de funciones</strong> - https://github.com/wppoland/plogins-waitlist/issues


= Does Plogins Waitlist require WooCommerce? =
Sí. Plogins Waitlist es una extensión de WooCommerce y requiere WooCommerce 8.0 o posterior. Mostrará un aviso en la administración y permanecerá inactivo si falta WooCommerce o está desactualizado.

= How are notifications sent? =
Cuando WooCommerce pone el estado de stock de un producto en `instock`, Plogins Waitlist envía un correo en texto plano a cada suscriptor pendiente de ese producto (y de su producto superior, en el caso de las variaciones) usando el propio motor de correo de WordPress de tu sitio (`wp_mail`). Los suscriptores a los que se avisa correctamente se marcan como notificados para no contactarlos dos veces.

= Does it work with variable products? =
Sí. Elige primero las opciones en el formulario de variaciones estándar de WooCommerce. Cuando la variación seleccionada está sin stock o en reserva, aparece el formulario de lista de espera y la suscripción se guarda para esa variación concreta.

= Can guests join the waitlist? =
Sí, por defecto. Puedes restringir las suscripciones a los clientes con sesión iniciada desmarcando <strong>Permitir suscripciones de invitados</strong> en <strong>WooCommerce → Plogins Waitlist</strong>.

= Can customers manage waitlists in My Account? =
Sí. Los clientes con sesión iniciada ven una pestaña <strong>Listas de espera</strong> en Mi cuenta con las suscripciones activas, el estado de stock actual y un botón para salir de cada lista.

= Does this comply with GDPR / consent requirements? =
Cada suscripción exige que el cliente marque una casilla de consentimiento explícita antes de poder unirse a la lista de espera; el formulario no se enviará sin ella. Los correos de los suscriptores se guardan únicamente en una tabla propia de tu base de datos de WordPress y nunca se envían a ningún servicio externo. Eres responsable de la redacción de tu etiqueta de consentimiento y de la política de privacidad de tu sitio.

= Can I export the subscriber list? =
Sí. Desde <strong>WooCommerce → Plogins Waitlist → Suscriptores</strong> puedes ver los suscriptores, filtrar por producto y exportar la lista como CSV.

= Does the form reload the page on submit? =
No. El formulario se envía con una llamada `fetch` en JavaScript puro y el resultado se anuncia en una región `aria-live`, así que la página no se mueve. Plogins Waitlist no carga jQuery para esto; en los productos variables sí depende del propio script de variaciones de WooCommerce para saber qué variación está seleccionada.


= Does this plugin work on WordPress Multisite? =

Sí. Este plugin es compatible con WordPress Multisite. Actívalo en toda la red o en sitios concretos; cada sitio conserva sus propios ajustes y datos.

== Screenshots ==

1. El formulario de lista de espera en un producto sin stock, donde un cliente deja su correo y marca la casilla de consentimiento obligatoria para recibir un aviso cuando el producto vuelva.
2. La pantalla de ajustes de Plogins Waitlist con tarjetas por secciones y ayuda contextual sobre el acceso de invitados, el encabezado y la introducción, las etiquetas del formulario, los mensajes en pantalla y el texto del correo de notificación.
3. La pestaña Listas de espera de Mi cuenta de un cliente, que muestra los productos que espera, el estado de stock actual y un botón para salir de cada lista.

== External Services ==

Plogins Waitlist no se conecta a ningún servicio externo. Los correos de aviso de reposición se envían mediante el propio motor de correo de WordPress de tu sitio (`wp_mail`); los datos de los suscriptores permanecen en tu base de datos de WordPress.

== Translations ==

Plogins Waitlist incluye traducciones al polaco, alemán y español de la interfaz del plugin. El dominio de texto es `plogins-waitlist`, de modo que los paquetes de idioma de WordPress.org también pueden sobrescribir o ampliar estas traducciones incluidas.

== Changelog ==

= 1.0.4 =
* Añadidas traducciones al polaco, alemán y español de la interfaz del plugin.

= 1.0.3 =
* Limpieza interna: se unificó la identidad interna del código a Waitlist (antes Restock). Sin cambios en tus ajustes ni datos; los alias heredados mantienen operativas las compilaciones PRO antiguas durante la actualización.

= 1.0.2 =
* Añadido en la pantalla de ajustes un resumen de las próximas funciones PRO.

= 1.0.1 =
* Primera versión estable.

= 0.4.1 =
* Documentación: se añadió una sección «Quizás también te guste» que enlaza los demás plugins gratuitos de WooCommerce de WPPoland. Sin cambios funcionales.

= 0.4.0 =
* Nuevo: línea opcional de prueba social sobre el formulario que muestra cuántos clientes ya están esperando un producto («12 clientes ya están esperando este artículo.»). Con singular/plural, texto totalmente personalizable con un marcador {count} y oculta cuando no espera nadie.
* Nuevo: eliminar un suscriptor directamente desde la lista de suscriptores de la administración (protegido con nonce y con confirmación).

= 0.3.3 =
* Nuevo: widget de Elementor para el formulario de lista de espera de reposición, para poder colocarlo con el editor de Elementor. Autoprotegido: solo se carga cuando Elementor está activo.

= 0.3.2 =
* Se corrigió el dominio de texto en tres cadenas de ayuda de la administración para que coincida con el slug del plugin.

= 0.3.1 =
* Renombrado a Plogins Waitlist for WooCommerce para tener un nombre de directorio distintivo y no genérico. Sin cambios funcionales.

= 0.3.0 =
* Nuevo: pestaña de WooCommerce <strong>Listas de espera</strong> en Mi cuenta con el estado de stock y la acción de salir de la lista de espera.
* Nuevo: suscripciones a la lista de espera que tienen en cuenta la variación en los productos variables (el formulario se muestra después de seleccionar una variación sin stock).
* Nuevo: ajustes para la etiqueta del menú de Mi cuenta, el aviso de variación y el mensaje de confirmación de baja.

= 0.2.0 =
* Nuevo: shortcode `[restock_waitlist]` para colocar el formulario de lista de espera manualmente (atributo `id` opcional para apuntar a un producto concreto).
* Nuevo: encabezado de formulario y texto introductorio opcionales, configurables desde la página de ajustes.
* Nuevo: mensajes de formulario en pantalla editables (éxito, correo no válido, falta de consentimiento, inicio de sesión requerido).
* Mejorado: la página de ajustes ahora expone cada etiqueta de formulario, mensaje y texto de correo que admite el motor, en lugar de depender de valores por defecto codificados.
* Mejorado: los ajustes opcionales vacíos ahora vuelven correctamente a los valores por defecto integrados.

= 0.1.0 =
* Lanzamiento inicial.
