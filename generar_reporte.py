import pandas as pd
import argparse
import mysql.connector  # O psycopg2 para PostgreSQL

def generar_reporte(fecha_inicio, fecha_fin, db_config):
    try:
        # Conexión a la base de datos MySQL
        conexion = mysql.connector.connect(**db_config)
        cursor = conexion.cursor()

        # Consulta SQL para obtener los datos de las marcaciones
        consulta = """
            SELECT e.id AS empleado_id, e.nombre, e.apellido, m.timestamp AS fecha, m.tipo_marcacion
            FROM marcaciones m
            JOIN empleados e ON m.empleado_id = e.id
            WHERE m.timestamp BETWEEN %s AND %s
            ORDER BY e.id, m.timestamp;
        """

        cursor.execute(consulta, (fecha_inicio, fecha_fin))
        resultados = cursor.fetchall()

        # Convertir los resultados a un DataFrame de pandas
        columnas = ["empleado_id", "nombre", "apellido", "fecha", "tipo_marcacion"]
        df = pd.DataFrame(resultados, columns=columnas)

        # Crear una nueva columna 'hora' a partir de la columna 'fecha'
        df['hora'] = pd.to_datetime(df['fecha']).dt.time

        # Eliminar columna 'apellido'
        df = df.drop('apellido', axis=1)

        # Reorganizar columnas
        df = df[["empleado_id", "nombre", "fecha", "hora", "tipo_marcacion"]]

        # Exportar el DataFrame a un archivo CSV
        df.to_csv("reporte_marcaciones.csv", index=False)

        print("Reporte generado exitosamente: reporte_marcaciones.csv")

    except Exception as e:
        print(f"Error al generar el reporte: {e}")

    finally:
        if 'conexion' in locals() and conexion.is_connected():
            cursor.close()
            conexion.close()

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Generar reporte de marcaciones.")
    parser.add_argument("fecha_inicio", help="Fecha de inicio (YYYY-MM-DD HH:MM:SS)")
    parser.add_argument("fecha_fin", help="Fecha de fin (YYYY-MM-DD HH:MM:SS)")

    args = parser.parse_args()

    # Configuración de la base de datos MySQL
    db_config = {
        "host": "127.0.0.1",
        "user": "root",
        "password": "",
        "database": "marcaciones_db",
    }

    generar_reporte(args.fecha_inicio, args.fecha_fin, db_config)
