import requests  # Importa la biblioteca para realizar solicitudes HTTP
from datetime import datetime  # Importa la clase datetime del módulo datetime

def generar_reporte_desde_api(base_url, fecha_inicio, fecha_fin, empleado_id=None, token=None):
    """
    Genera un reporte de marcaciones de empleados a partir de la API de Laravel.

    Args:
        base_url (str): La URL base de la API de Laravel (ej., "http://127.0.0.1:8000/api").
        fecha_inicio (str): Fecha de inicio del reporte (formato AAAA-MM-DD).
        fecha_fin (str): Fecha de fin del reporte (formato AAAA-MM-DD).
        empleado_id (int, opcional): ID del empleado para filtrar el reporte.  Por defecto, None.
        token (str, opcional): Token de autenticación Bearer. Por defecto, None.

    Returns:
        list: Una lista de diccionarios, donde cada diccionario representa una marcación.
              Retorna una lista vacía si no hay marcaciones que coincidan con los criterios.
              Retorna None si se produce un error.
    """
    url = f"{base_url}/reportes/marcaciones"  # Define la URL del endpoint de la API para el reporte
    params = {
        "fecha_inicio": fecha_inicio,
        "fecha_fin": fecha_fin,
    }
    if empleado_id:
        params["empleado_id"] = empleado_id  # Agrega el ID del empleado a los parámetros si se proporciona

    headers = {}
    if token:
        headers["Authorization"] = f"Bearer {token}"  # Agrega el token de autorización al encabezado si se proporciona

    try:
        response = requests.get(url, params=params, headers=headers)  # Realiza la solicitud GET a la API
        response.raise_for_status()  # Lanza una excepción para códigos de estado HTTP de error (4xx o 5xx)
        reporte = response.json()  # Decodifica la respuesta JSON de la API

        # Formatea las fechas utilizando Python
        for marcacion in reporte:
            marcacion['timestamp'] = datetime.strptime(marcacion['timestamp'], '%Y-%m-%d %H:%M:%S')
            marcacion['timestamp'] = marcacion['timestamp'].strftime('%Y-%m-%d %H:%M:%S')

        return reporte  # Retorna el reporte formateado

    except requests.exceptions.RequestException as e:
        print(f"Se produjo un error al comunicarse con la API: {e}")  # Imprime el mensaje de error
        return None  # Retorna None para indicar el error
    except ValueError as ve:
        print(f"Se produjo un error al decodificar la respuesta JSON: {ve}")
        return None

if __name__ == "__main__":
    # Ejemplo de uso de la función
    base_url = "http://127.0.0.1:8000/api"  # URL base de la API de Laravel.  Modificar si es diferente.
    fecha_inicio = "2024-01-01"
    fecha_fin = "2024-01-31"
    empleado_id = 1  # ID del empleado a incluir en el reporte. Usar None para todos.
    token = None  # Token de autenticación de la API.  Proporcionar si es necesario.

    reporte = generar_reporte_desde_api(base_url, fecha_inicio, fecha_fin, empleado_id, token)  # Llama a la función para obtener el reporte

    if reporte is not None:  # Verifica si la función retornó un valor válido
        if reporte:  # Verifica si el reporte contiene datos
            print("Reporte de Marcaciones:")
            for marcacion in reporte:  # Itera sobre las marcaciones en el reporte
                print(marcacion)  # Imprime cada marcación
        else:
            print("No se encontraron marcaciones para los criterios especificados.")  # Mensaje si no hay datos
    else:
        print("No se pudo generar el reporte debido a un error.")  # Mensaje si hubo un error
