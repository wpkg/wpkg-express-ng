FROM evilfreelancer/alpine-apache-php5

ENV DB_DRIVER='mysql'
ENV DB_PERSISTENT='false'
ENV DB_HOST='localhost'
ENV DB_PORT=''
ENV DB_USER='wpkg'
ENV DB_PASS='wpkg_pass'
ENV DB_NAME='wpkg'
ENV DB_SCHEMA=''
ENV DB_PREFIX=''
ENV DB_ENCODING=''

WORKDIR /app

# Change documents root from "public" to "webroot"
RUN sed "s#/app/public#/app/webroot#" -i /etc/apache2/httpd.conf

ADD [".", "/app"]
RUN chown -R apache:apache /app
