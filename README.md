# Crear un servicio REST

Se crea un switch para retornar la respuesta de cada tipo de petición HTTP (GET, POST, PUT y DELETE)

```php
switch ( strtoupper( $method ) ) {
	case 'GET':
		// response
		break;
	case 'POST':
		// response
		break;
	case 'PUT':
		// response
		break;
	case 'DELETE':
		// response
		break;
}
```

### Tipos de recursos

Se deben definir cuales son los tipos de recursos que se pueden consultar a la API.

```php
$allowedResourceTypes = [
	'books',
	'authors',
	'genres',
];
```

El uso de esto es, por ejemplo:

```php
curl http://rest-api.local.com?resource_type=books
```

Para definir la variable `resurce_type` y validarla se crea un pequeño condicional:

```php
$resourceType = $_GET['resource_type'];

if ( !in_array( $resourceType, $allowedResourceTypes ) ) {
	http_response_code( 400 );
	echo json_encode(
		[
			'error' => "$resourceType is un unkown",
		]
	);
	
	die;
}
```
<br/>

## Ejemplo de peticiones
```bash
# GET
# All books
http://api-rest.local.com?resource_type=books

# A book by ID
http://api-rest.local.com?resource_type=books&resource_id=1

# POST
# Add new book
http://api-rest.local.com?resource_type=books
# body JSON: {"titulo": "Ready Player One", "id_autor": 1, "id_genero": 3}

# PUT
# Change content for book #1
http://api-rest.local.com?resource_type=books&resource_id=1

# DELETE
# Delete book 1
http://api-rest.local.com?resource_type=books&resource_id=1
```

## Autenticación

### Vía HTTP

La autenticación HTTP, especialmente a través de encabezados como Basic Authentication, tiene sus ventajas y desventajas. Aquí hay algunos pros y contras:

**Pros:**

1. **Simplicidad:** La autenticación HTTP es simple de implementar y entender. Solo requiere el encabezado **`Authorization`** en las solicitudes HTTP.
2. **Rendimiento:** Puede ser más eficiente en términos de rendimiento en comparación con métodos más complejos, ya que no implica múltiples rondas de negociación.
3. **Compatibilidad:** Es compatible con prácticamente todos los navegadores y tecnologías web, ya que es un estándar ampliamente adoptado.

**Contras:**

1. **Seguridad limitada:** Basic Authentication transmite credenciales en texto simple, codificadas en Base64, que es fácilmente decodificable. Esto significa que es vulnerable a ataques de captura y reproducción si no se utiliza junto con HTTPS.
2. **Falta de funcionalidad avanzada:** No proporciona funcionalidades avanzadas como renovación automática de tokens o la posibilidad de revocar un token específico sin cambiar las credenciales.
3. **Incapacidad para manejar roles y permisos:** No ofrece un mecanismo integrado para gestionar roles y permisos, lo que puede ser necesario en sistemas más complejos.
4. **Necesidad de almacenar credenciales:** Los clientes deben almacenar las credenciales de usuario, ya que se requieren en cada solicitud. Esto puede ser un riesgo de seguridad si las credenciales se almacenan en el lado del cliente.
5. **Falta de estándares para el cierre de sesión:** No hay un estándar claro para cerrar la sesión en la autenticación HTTP, lo que puede dificultar la implementación de una funcionalidad de cierre de sesión efectiva.

En resumen, la autenticación HTTP es simple y fácil de implementar, pero tiene limitaciones de seguridad y funcionalidad que pueden hacerla menos adecuada para ciertos escenarios, especialmente aquellos que requieren un mayor nivel de seguridad y características avanzadas. En muchos casos, se prefiere el uso de protocolos de autenticación más robustos como OAuth 2.0.

Para acceder al API con autenticación por HTTP

```bash
curl http://hal:1234@api-rest.local.com/auth/http.php?resource_type=books
```

<br/>
<br/>

## Vía HMAC

La autenticación HMAC (Hash-Based Message Authentication Code) es un método de autenticación que utiliza una función hash criptográfica en combinación con una clave secreta para verificar la integridad y autenticidad de un mensaje. Aquí hay algunos pros y contras asociados con la autenticación HMAC:

**Pros:**

1. **Integridad y Autenticidad:** HMAC proporciona una verificación de integridad y autenticidad robusta. La combinación de una clave secreta y una función hash criptográfica garantiza que tanto el remitente como el receptor puedan verificar que el mensaje no ha sido alterado y que proviene de una fuente auténtica.
2. **Eficiencia:** HMAC es eficiente en términos de rendimiento y recursos, ya que utiliza operaciones de hash que son rápidas y computacionalmente eficientes.
3. **Flexibilidad:** Puede utilizarse en una variedad de contextos, como la autenticación de solicitudes en servicios web, la firma de mensajes en sistemas de mensajería, y la autenticación de datos en general.
4. **No se requiere almacenamiento de estado:** A diferencia de algunos métodos de autenticación basados en tokens, HMAC no requiere almacenar un estado del servidor. Cada mensaje contiene la información necesaria para su propia autenticación.

**Contras:**

1. **Necesidad de una clave secreta:** La seguridad de HMAC depende en gran medida de la seguridad de la clave secreta. La gestión adecuada de las claves es crucial, y la pérdida o compromiso de una clave puede comprometer la seguridad del sistema.
2. **Complejidad en la implementación:** La implementación correcta de HMAC requiere conocimientos de criptografía y manejo seguro de claves. Errores en la implementación pueden llevar a vulnerabilidades de seguridad.
3. **Ausencia de control de acceso:** HMAC por sí mismo no proporciona control de acceso ni gestión de sesiones. Se utiliza principalmente para verificar la integridad y autenticidad de los mensajes, pero no aborda aspectos como la autorización.
4. **Posible ataque de fuerza bruta:** Si la clave secreta es débil o está expuesta, los atacantes podrían intentar ataques de fuerza bruta para descifrarla.

En general, HMAC es una técnica sólida y ampliamente utilizada para garantizar la integridad y autenticidad de los mensajes. Sin embargo, debe implementarse correctamente y combinarse con otras medidas de seguridad, según sea necesario para los requisitos específicos de un sistema. La gestión adecuada de las claves es fundamental para el éxito de la autenticación HMAC.

Para acceder al API con autenticación por HMAC

1. Obtiene el hash
En este caso lo hace con el parámetro `user_id` en el URL, pero el script podría estar en cualquier parte de la aplicación, solamente necesita conocer el secret y el ID de usuario.

```bash
http://api-rest.local.com/?user_id=1
# Time: 1703203133
# Hash: 1e6353a405d707aea2238811a3649bd0f21ef96c
```

1. Realiza la autenticación
Enviando el `id` de usuario, el `timestamp` y el `hash` en la cabecera

```bash
curl --location 'http://api-rest.local.com/?resource_type=books' \
--header 'X-HASH: 4d3cecec3040fe1333f0faf5a5588efafad6e72e' \
--header 'X-UID: 1' \
--header 'X-TIMESTAMP: 1703203487'
```
<br/>
<br/>

## Vía Access Token

En este ejemplo se simulará contar con 2 servidores. Uno proveerá el token de autenticación (`Server 1`)y el otro será el servidor que maneja el REST API (`Server 2`).

1. El cliente hace una primer petición al `Server 1` enviando sus credenciales y este devolverá un token.
2. El cliente ahora hace una petición al `Server 2`  enviando el token.
3. El `Server 2` hará una petición al `Server 1` para validar el token que le ha pasado el cliente.
4. Si el `Server 1` lo valida, el `Server 2` devuelve la información que pidió el cliente al REST API.

La autenticación con tokens de acceso (Access Tokens) es un enfoque comúnmente utilizado en sistemas de autenticación y autorización, especialmente en el contexto de OAuth 2.0. Aquí están algunos pros y contras asociados con la autenticación mediante tokens de acceso:

**Pros:**

1. **Seguridad:** Los tokens de acceso pueden ser diseñados para tener una duración limitada, lo que mejora la seguridad al limitar el tiempo durante el cual un token puede ser utilizado. Además, al utilizarse junto con HTTPS, proporcionan una capa adicional de seguridad al cifrar la comunicación.
2. **Escalabilidad:** La autenticación basada en tokens es altamente escalable. Los servidores pueden validar tokens de manera eficiente sin necesidad de consultar la base de datos en cada solicitud, ya que la información necesaria se encuentra en el propio token.
3. **Desacoplamiento:** Los tokens de acceso permiten el desacoplamiento entre el servidor de autenticación y el servidor de recursos. Esto significa que el servidor de recursos puede validar y procesar solicitudes sin la necesidad de comunicarse directamente con el servidor de autenticación en cada solicitud.
4. **Versatilidad:** Los tokens de acceso pueden utilizarse en una variedad de escenarios, como autenticación de API, autorización de usuarios en servicios web y acceso a recursos protegidos.

**Contras:**

1. **Necesidad de almacenamiento seguro:** Los tokens de acceso deben almacenarse de manera segura para evitar su interceptación o uso indebido. Si un token se compromete, un atacante podría acceder a recursos protegidos.
2. **Complejidad en la implementación:** La implementación y gestión de tokens de acceso puede ser compleja. Se requiere una correcta implementación de OAuth 2.0 u otro protocolo de autorización para garantizar la seguridad y la integridad del flujo de autenticación.
3. **Vigilancia del tiempo de vida del token:** La gestión del tiempo de vida de los tokens es esencial. Un tiempo de vida demasiado largo puede aumentar el riesgo de uso indebido, mientras que un tiempo de vida demasiado corto puede causar molestias a los usuarios al requerir autenticación frecuente.
4. **Dependencia de proveedores de identidad externos:** En algunos casos, la autenticación basada en tokens puede depender de proveedores de identidad externos (por ejemplo, OAuth proveedores de servicios), lo que podría introducir un punto único de fallo o depender de la disponibilidad de terceros.

En resumen, la autenticación con tokens de acceso es una práctica común y eficaz en muchos escenarios, pero requiere una implementación cuidadosa y una gestión adecuada para garantizar la seguridad y la eficiencia del sistema.

Para acceder al API con autenticación por Access Token

1. Se obtiene el token con un `POST`.

```bash
curl --location --request POST 'http://api-rest.local.com/auth/auth-server.php' \
--header 'X-Client-Id: 1' \
--header 'X-Secret: Secreto123'
```

1. Se realiza la petición al API enviando el Token