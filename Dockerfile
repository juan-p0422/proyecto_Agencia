# Usar la imagen oficial de MySQL
FROM mysql:8.0

# Variables de entorno para iniciar MySQL
ENV MYSQL_ROOT_PASSWORD=1234
ENV MYSQL_DATABASE=agencia
ENV MYSQL_USER=Rodo
ENV MYSQL_PASSWORD=1234

# Exponer el puerto estándar de MySQL
EXPOSE 3306

# Copiar scripts SQL si quieres inicializar tablas o datos
# (Opcional: crea la carpeta sql/ y coloca archivos .sql ahí)
# COPY ./sql/ /docker-entrypoint-initdb.d/

