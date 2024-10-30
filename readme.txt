=== Transferencia Online ===
Contributors: INESPAY
Donate link: https://www.inespay.com/transferenciaonline/
Tags: transferencia online, pagos, pasarela, psd2, inespay
Requires at least: 4.6
Tested up to: 6.6.2
Requires PHP: 7.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Redireccione a su cliente a su banco para que autorice un pago mediante transferencia bancaria en tiempo real.

== Description ==
Transferencia Online es un servicio de iniciación de pagos regulado en la nueva Directiva europea de Servicios de Pago (PSD2), autorizado y supervisado por Banco de España.
El Servicio te redirige a tu banco para que te identifiques y autorices el pago mediante transferencia bancaria.
Previo al pago, tu banco te mostrará un resumen de la transferencia bancaria que vas a emitir y te indicará si te aplica alguna comisión en concepto de emisión de transferencias.
En el caso de que tu banco te aplique alguna comisión, ésta será la misma que te aplica habitualmente cuando realizas una transferencia bancaria convencional desde tu banco.
No es necesario registrarse en el Servicio. Sólo necesitas disponer de Banca Online con tu entidad financiera.

### Serás redirigido a tu banco para autorizar el pago en tiempo real.

### Tu pedido será confirmado de forma inmediata.

== Installation ==

### **Existen 2 métodos de instalación del plugin:**

#### Automática
- Ve a tu escritorio de WordPress, **accede al menú Plugins y pulsa el botón Añadir Nuevo.**
- **En el campo de búsqueda inserta las palabras "Inespay"** para localizar el plugin.
- Pulsa el botón **Instalar y luego el botón Activar**. ¡Listo!

#### Manual
- **Descárgate el plugin** desde WordPress.
- **Sube el archivo zip vía FTP a la ruta "/wp-content/plugins"** o directamente desde el escritorio de WordPress accediendo al menú:
    Plugins > Añadir nuevo > Subir plugin
- Pulsa el botón **Instalar y luego el botón Activar**. ¡Listo!

### **Configuración**

1. Asegúrate de tener instalado el plugin de Transferencia Online para WooCommerce.
2. Una vez instalado nuestro módulo, accede al menú WooCommerce > Ajustes.
3. Selecciona la pestaña **"Pagos"** y te mostrará un submenú con los métodos de pago instalados en tu tienda WooCommerce. Selecciona la opción **TRANSFERENCIA ONLINE**.
4. Abre una página nueva a parte en tu navegador, accede o regístrate en el **Panel de control de Transferencia Online** [Acceso Dashboard](https://clients.inespay.com/build/signup)
5. Una vez dentro del Panel de control de Transferencia Online selecciona la opción **Claves API y copia las 2 Claves de Test.**
6. Vuelve al escritorio de WordPress y selecciona Test en el selector de Entorno. **Pega las 2 Claves de Test (API Key y API Token)** copiadas en el paso anterior y haz clic en el botón Guardar.
7. Ve a tu tienda WooCommerce y **realiza todos los pagos de prueba que consideres oportuno** para comprobar que todo funciona correctamente.
8. **Una vez superadas todas las pruebas de pago, puedes solicitar las Claves en Producción.** Para ello, deberás iniciar sesión en el Panel de Control de Transferencia Online, selecciona la opción Mi Cuenta y rellenar el formulario Mis Datos, facilitando la información que se solicita en el mismo. En las siguientes 24 horas recibirás una confirmación por email acerca de la autorización para utilizar el servicio de pago Transferencia Bancaria PSD2 a través del plugin en tu tienda WooCommerce.
9. Por último, **inicia sesión en el Panel de Control de Transferencia Online y selecciona la opción Claves API.** En ese momento aparecerán las 2 Claves en Producción (API Key y API Token). Copia y pega dichas claves en los mismos campos de tu escritorio de WordPress donde se introdujeron inicialmente las Claves de Test. Asegúrate de cambiar la opción Entorno a Real.

    ### ¡Hecho! 
    **Ya tienes configurada tu tienda WooCommerce para aceptar pagos mediante Transferencia Online.**

== Frequently asked questions ==

Para más información envía un email a soporte@inespay.com	

== Screenshots ==
1. Selecciona el método de pago Transferencia Online para pagar con tu banco.
2. Selecciona tu banco para ser redirigido.
3. Autentícate ante tu banco con tus claves habituales de Banca Online.
4. Comprueba el resumen de pago y autoriza el pago con el procedimiento habitual establecido por tu banco.
5. Pago completado.
6. Pedido realizado.
7. Configuración.

== Changelog ==
- fix versions 5,x
- **WooCommerce Compatibility**: Fully adapted the plugin for compatibility with the latest versions of WooCommerce.
- **Gutenberg Block Integration**: Improved the interface to work with the WordPress block editor, facilitating the addition of the Inespay payment method on checkout pages.
- Adaptations for proper functioning in WordPress version 6.6.x.
- Adaptations for proper functioning in WordPress version 5.9.x.

== Upgrade notice ==