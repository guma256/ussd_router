# ussd_router

#Redis installation on Ubuntu
sudo apt-get install redis-server -y
sudo systemctl enable redis-server.service && sudo systemctl start redis-server.service
sudo systemctl status redis-server.service

```sudo apt-get install php-redis -y```
##Redis RHEL installation
sudo yum install redis -y
sudo systemctl start redis.service
sudo systemctl status redis.service
####If you get php permission denied. Run the commands below
sudo /usr/sbin/setsebool httpd_can_network_connect=1
sudo  setsebool -P httpd_enable_homedirs 1
# ussd_simulator
