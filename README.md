
LOCAL INSTRUCTIONS

MYSQL
start mysql server with
mysql.server start
connect
mysql -u root -p
errors files
-u root -p -se "SHOW VARIABLES" | grep -e log_error -e general_log -e slow_query_log
check status
mysqladmin -u root -p status

WEB SERVER - needs php8
start php server with – you need to cd public
php -S localhost:8080

REDIS
once you have redis installed, run
redis-server
