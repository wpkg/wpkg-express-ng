FROM evilfreelancer/alpine-apache-php5

# Database parameters
ENV DB_DRIVER=mysqli
ENV DB_PERSISTENT=0
ENV DB_HOST=mysql
ENV DB_PORT=3306
ENV DB_USER=wpkg
ENV DB_PASS=wpkg_pass
ENV DB_NAME=wpkg
ENV DB_SCHEMA=''
ENV DB_PREFIX=''
ENV DB_ENCODING=utf8

# WPKG Express NG parameters
ENV WPKG_USER=admin
ENV WPKG_PASS=admin
ENV WPKG_SSL_FORCE=0
# Export disabled items
ENV WPKG_XML_DISABLED=0
# Format XML output
ENV WPKG_XML_FORMAT=1
# Protect XML output
ENV WPKG_XML_PROTECT=0
ENV WPKG_XML_USER=wpkg
ENV WPKG_XML_PASS=wpkg_pass

WORKDIR /app

# Change documents root from "public" to "webroot"
RUN sed "s#/app/public#/app/webroot#" -i /etc/apache2/httpd.conf

ADD [".", "/app"]
RUN chown -R apache:apache /app
