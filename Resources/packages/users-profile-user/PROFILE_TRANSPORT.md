
#### Если вы измените файл конфигурации службы, вам необходимо перезагрузить демон:

``` bash

systemctl daemon-reload

```


####  Название файла в директории /etc/systemd/system

``` text

baks-PROFILE_NAME@.service

```


#### Содержимое файла

``` text
[Unit]
Description=PROFILE_NAME %i
StartLimitBurst=5
StartLimitIntervalSec=0

[Service]
ExecStart=php /.......PATH_TO_PROJECT......../bin/console messenger:consume PROFILE_UID --memory-limit=128m --time-limit=3600 --limit=100
Restart=always

[Install]
WantedBy=default.target

```


#### Команды для выполнения


``` bash

systemctl daemon-reload

systemctl enable baks-PROFILE_NAME@1.service
systemctl start baks-PROFILE_NAME@1.service
systemctl restart baks-PROFILE_NAME@1.service

systemctl disable baks-PROFILE_NAME@1.service
systemctl stop baks-PROFILE_NAME@1.service

```

#### Запуск из консоли на 1 минуту

``` bash

php bin/console messenger:consume baks-PROFILE_NAME --time-limit=60 -vv

```
