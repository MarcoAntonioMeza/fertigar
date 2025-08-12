import pandas as pd
import os
import pymysql

# Configuración de la base de datos
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASS = '2808'
DB_PORT = 3307
DB_NAME = 'dev_fertigar_yii'  # Cambia por el nombre real de tu base de datos

# Ruta al archivo Excel de clientes
EXCEL_PATH = os.path.join('catalogos', 'clientes.xlsx')

# Cargar el archivo Excel
df = pd.read_excel(EXCEL_PATH)

# Eliminar la columna 'proveedor' si existe
if 'proveedor' in df.columns:
    df = df.drop(columns=['proveedor'])

# Conexión a la base de datos
conn = pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASS, database=DB_NAME, charset='utf8mb4', port=DB_PORT)
cursor = conn.cursor()

# Inserta cada cliente (ajusta los campos según tu tabla)
for idx, row in df.iterrows():
    # Ejemplo: asume que tienes columnas 'nombre', 'rfc', 'telefono', etc.
    sql = """
        INSERT INTO cliente (nombre, rfc, telefono)
        VALUES (%s, %s, %s)
    """
    cursor.execute(sql, (
        row.get('nombre', ''),
        row.get('rfc', ''),
        row.get('telefono', '')
    ))

conn.commit()
cursor.close()
conn.close()

print("Carga completada.")
