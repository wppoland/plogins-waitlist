=== Plogins Waitlist - Back in Stock for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, back in stock, waitlist, stock notification, email
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Back-in-stock waitlist for WooCommerce. Shoppers leave their email, you email them on restock. Accessible, no layout shift.

== Description ==

Plogins Waitlist adds a waitlist form to out-of-stock WooCommerce products. A shopper enters their email, and when you set the product back to "In stock", Plogins Waitlist emails everyone waiting through your site's own WordPress mailer. There is no external service, no account to sign up for, and nothing leaves your database.

The form is rendered in PHP on the single-product summary, where it sits in the normal page flow rather than being injected after load, so it does not shift surrounding content. Submitting it runs a small vanilla-JavaScript `fetch` request loaded with `defer` in the footer; the plugin adds no jQuery of its own. On variable products it hooks into WooCommerce's existing variation script so the form only appears once an unavailable variation is selected.

Accessibility was a first-class concern rather than an afterthought. The email field carries a visually hidden label, consent is a real required checkbox, and the success/error message is announced through an `aria-live` region while the form reports `aria-busy` during submission.

Subscriber data lives in a single `{prefix}_restock_waitlist` table that the plugin creates and version-tracks. Notifications fire on the `woocommerce_product_set_stock_status` hook, so there is no queue or background cron to run. Uninstalling drops the table and removes the plugin's options, leaving nothing behind.

Source and issues: https://github.com/wppoland/plogins-waitlist . Patches and bug reports are welcome there.

<strong>Features</strong>

* Waitlist form shown automatically on out-of-stock and backorder ("on backorder") product pages
* Variable products: form appears after the shopper selects an unavailable variation
* WooCommerce <strong>My Account → Waitlists</strong> tab for logged-in customers (review lists, leave waitlist)
* Asynchronous submit with a vanilla-JavaScript fetch call, so the page does not reload
* Email field pre-filled for logged-in customers
* Required consent checkbox for every signup
* Automatic plain-text email notification on restock, sent via `wp_mail`
* Optional heading and intro text shown above the form
* Customisable form labels, button text, on-screen submit messages, and notification email subject/intro/closing text
* `[restock_waitlist]` shortcode for placing the form manually in a product template
* Toggle guest (not-logged-in) subscriptions on or off
* Social-proof "N shoppers are already waiting" line above the form (optional, customisable, singular/plural aware)
* Admin subscriber list with per-product filter, CSV export, and one-click remove
* Theme-overridable form template (`yourtheme/restock/single-product/waitlist-form.php`)
* Compatible with WooCommerce HPOS (Custom Order Tables) and Cart/Checkout Blocks

= You may also like these plugins =

More free WooCommerce plugins from WPPoland:

* [Plogins Tiers](https://wordpress.org/plugins/plogins-tiers/) - quantity and volume pricing tiers with a server-rendered price table.
* [Sieve - Search & Filter](https://wordpress.org/plugins/sieve/) - fast AJAX product search and filtering for WooCommerce, with no jQuery.
* [Polski for WooCommerce](https://wordpress.org/plugins/polski/) - Polish-market compliance: GPSR, Omnibus, GDPR, invoices and storefront modules.

Browse the full catalogue at https://plogins.com/es/ .

== Installation ==

1. Install and activate WooCommerce (8.0 or later).
2. Install Plogins Waitlist from the WordPress plugin directory, or upload the `plogins-waitlist` folder to `/wp-content/plugins/`.
3. Activate the plugin through the <strong>Plugins<strong> screen. 4. Optionally visit </strong>WooCommerce → Plogins Waitlist</strong> to customise labels and notification text; sensible defaults work out of the box.
5. The waitlist form appears automatically on any out-of-stock or backorder product page.

== Frequently Asked Questions ==

= Documentation and links =

* <strong>Documentation</strong> - https://plogins.com/es/plogins-waitlist/docs/
* <strong>Plugin page</strong> - https://plogins.com/es/plogins-waitlist/
* <strong>Source code</strong> - https://github.com/wppoland/plogins-waitlist
* <strong>Bug reports and feature requests</strong> - https://github.com/wppoland/plogins-waitlist/issues


= Does Plogins Waitlist require WooCommerce? =
Yes. Plogins Waitlist is a WooCommerce extension and requires WooCommerce 8.0 or later. It will show an admin notice and stay inactive if WooCommerce is missing or out of date.

= How are notifications sent? =
When WooCommerce sets a product's stock status to `instock`, Plogins Waitlist sends a plain-text email to every pending subscriber for that product (and its parent, for variations) using your site's own WordPress mailer (`wp_mail`). Subscribers who are emailed successfully are marked as notified so they are not contacted twice.

= Does it work with variable products? =
Yes. Choose options in the standard WooCommerce variation form first. When the selected variation is out of stock or on backorder, the waitlist form appears and the subscription is stored for that specific variation.

= Can guests join the waitlist? =
Yes by default. You can restrict signups to logged-in customers by unchecking <strong>Allow guest subscriptions</strong> in <strong>WooCommerce → Plogins Waitlist</strong>.

= Can customers manage waitlists in My Account? =
Yes. Logged-in customers see a <strong>Waitlists</strong> tab under My Account with active subscriptions, current stock status, and a button to leave each list.

= Does this comply with GDPR / consent requirements? =
Every signup requires the shopper to tick an explicit consent checkbox before they can join the waitlist; the form will not submit without it. Subscriber emails are stored only in a custom table in your own WordPress database and are never sent to any external service. You are responsible for the wording of your consent label and your site's privacy policy.

= Can I export the subscriber list? =
Yes. From <strong>WooCommerce → Plogins Waitlist → Subscribers</strong> you can view subscribers, filter by product, and export the list as CSV.

= Does the form reload the page on submit? =
No. El formulario se envía con una llamada `fetch` de JavaScript estándar y el resultado se anuncia en una región `aria-live`, por lo que la página permanece fija. Plogins Waitlist no carga jQuery para esto; en el caso de productos variables, depende del propio script de variación de WooCommerce para saber qué variación se selecciona.


= Does this plugin work on WordPress Multisite? =

Sí. Este complemento es compatible con WordPress Multisite. Activarlo en red o activarlo en sitios individuales; Cada sitio mantiene su propia configuración y datos.

== Screenshots ==

1. El formulario de lista de espera para un producto agotado, donde un comprador deja su correo electrónico y marca la casilla de consentimiento requerida para recibir una notificación cuando regrese.
2. La pantalla de configuración de la lista de espera de Plogins con tarjetas seccionadas y ayuda en línea para el acceso de invitados, el encabezado y la introducción, etiquetas de formulario, mensajes en pantalla y texto de notificación por correo electrónico.
3. La pestaña Listas de espera de Mi cuenta de un cliente, que muestra los productos que están esperando, el estado actual del stock y un botón para salir de cada lista.

== External Services ==

Plogins Waitlist no se conecta a ningún servicio externo. Los correos electrónicos de notificación de existencias nuevamente se envían a través del correo electrónico de WordPress de tu propio sitio (`wp_mail`); Los datos de los suscriptores permanecen en tu base de datos de WordPress.

== Changelog ==

= 1.0.3 =
*Limpieza interna: unificó la identidad del código interno a Lista de Espera (era Restock). No se realizan cambios en tu configuración o datos; Los alias heredados mantienen las compilaciones PRO antiguas funcionando durante la actualización.

= 1.0.2 =
* Se agregó una descripción general de la pantalla de configuración de las próximas funciones PRO.

= 1.0.1 =
* Primera versión estable.

= 0.4.1 =
* Documentos: se agregó una sección "También te puede gustar" que vincula los otros complementos gratuitos de WPPoland WooCommerce. Sin cambios funcionales.

= 0.4.0 =
* Nuevo: línea de prueba social opcional encima del formulario que muestra cuántos compradores ya están esperando un producto ("12 compradores ya están esperando este artículo"). Texto totalmente personalizable, con reconocimiento de singular/plural, con un marcador de posición {count} y oculto cuando no hay nadie esperando.
* Nuevo: eliminar un suscriptor directamente de la lista de suscriptores del administrador (no protegidos, con confirmación).

= 0.3.3 =
* Nuevo: widget de Elementor para el formulario de lista de espera de existencias nuevamente, para que pueda colocarse con el editor de Elementor. Autoprotegido: se carga solo cuando Elementor está activo.

= 0.3.2 =
* Corrige el dominio de texto en tres cadenas de ayuda del administrador para que coincida con el slug del complemento.

= 0.3.1 =
* Se cambió el nombre a Plogins Waitlist para WooCommerce para obtener un nombre de directorio distinto y no genérico. Sin cambios funcionales.

= 0.3.0 =
* Nuevo: pestaña <strong>Listas de espera</strong> de Mi cuenta de WooCommerce con estado de existencias y acción de salir de la lista de espera.
* Nuevo: inscripciones en lista de espera que reconocen variaciones en productos variables (el formulario se muestra después de seleccionar una variación agotada).
* Nuevo: configuración para la etiqueta del menú Mi cuenta, mensaje de variación y mensaje de confirmación para cancelar la suscripción.

= 0.2.0 =
* Nuevo: código abreviado `[restock_waitlist]` para colocar el formulario de lista de espera manualmente (atributo opcional `id` para apuntar a un producto específico).
* Nuevo: encabezado de formulario opcional y texto de introducción, configurable desde la página de configuración.
* Nuevo: mensajes de formulario editables en pantalla (éxito, correo electrónico no válido, consentimiento faltante, se requiere inicio de sesión).
* Mejorado: la página de configuración ahora expone todas las etiquetas de formulario, mensajes y textos de correo electrónico que admite el motor, en lugar de depender de valores predeterminados codificados.
* Mejorado: las configuraciones opcionales vacías ahora vuelven correctamente a los valores predeterminados integrados.

= 0.1.0 =
* Lanzamiento inicial.
