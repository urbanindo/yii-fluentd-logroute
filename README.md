# Log Route for Fluentd for Yii 1.1.*

## How To Use

### > Within Apps

- normal logging (yii may add trace info here by appending message with string)

```php
Yii::log('test-message', CLogger::LEVEL_INFO, 'fluent-log');
//{"level":"info","timestamp":1418182144.5746,"content":"test-message\nin /vagrant/protected/commands/TestCommand.php (21)\nin /vagrant/protected/yiic.php (24)\nin /vagrant/protected/yiic (4)","tag":"yii.info.fluent-log","time":"2014-12-10T10:29:04+07:00"}
```

- preferable way, clean logs without trace info

```php
Yii::getLogger()->log('test-message', CLogger::LEVEL_INFO, 'fluent-log');
// {"level":"info","timestamp":1418182025.9051,"content":"test-message","tag":"yii.info.fluent-log","time":"2014-12-10T10:27:05+07:00"}
```

- it is also possible to log array

```php
Yii::getLogger()->log(['test'=>'test-message'], CLogger::LEVEL_INFO, 'fluent-log');
//{"level":"info","timestamp":1418182681.3888,"content":{"test":"test-message"},"tag":"yii.info.fluent-log","time":"2014-12-10T10:38:01+07:00"}
```

### > Composer

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url":  "git@github.com:urbanindo/yii-fluentd-logroute.git"
        }
    ],
    "require": {
        "urbanindo/yii-fluentd-logroute": "dev-master",
    }
}
```

### > Yii Config
#### - fluentd specific config
- `host` : fluentd host. default: 'localhost'
- `port` : fluentd port. default: '24224'
- `options` :
 - `socket_timeout`     => `FluentLogger::SOCKET_TIMEOUT`, // default 3 s
 - `connection_timeout` => `FluentLogger::CONNECTION_TIMEOUT`, // default 3 s
 - `retry_socket`       => `true`, // retry in failure. Max retry is 5 times
 - `backoff_mode`       => `FluentLogger::BACKOFF_TYPE_USLEEP`, // backoff type. `0x01` == `BACKOFF_TYPE_EXPONENTIAL`, `0x02` == `BACKOFF_TYPE_USLEEP`
 - `backoff_base`       => `3`, // used in exponential backoff_mode, 0.003 sec, 0.009 sec, 0.027 sec, 0.081 sec, 0.243 sec
 - `usleep_wait`        => `FluentLogger::USLEEP_WAIT`, // 1000, equal to 0.001 ms
 - `persistent`         => `false`, // use fsockopen() instead of pfsockopen() http://www.php.net/pfsockopen

#### - yii log specific config
- `levels` : only logs with this levels will be logged. if empty log all levels.
- `categories`: only logs with this categories will be logged. if empty log all categories
- `except` : never log log with this except categories

#### - component specific config
- `tagFormat` : uses %l for level, and %c for category. it will be replaced with corresponding level and category

```
'log' => [
    'class' => 'CLogRouter',
    'routes' => [
        [
            'class' => '\Urbanindo\Yii\Component\Logger\FluentdLogRoute',
            'host' => 'localhost',
            'levels'=> 'info, error, warning',
            'port' => '24224',
            'tagFormat' => 'yii.%l.%c',
        ],
    ],
]
```

### > Fluentd Configuration

```
<source>
  type forward
  port 24224
  bind 0.0.0.0
</source>
	
<match yii.**>
  type forest
  subtype file
  escape_tag_separator /
  <template>
    path /var/log/td-agent/stack_name/php-app/__ESCAPED_TAG__
    time_slice_format %Y%m%d-%H
    buffer_chunk_limit 10m
    flush_interval 10m
    flush_at_shutdown true
    format json
    include_tag_key true
    include_time_key true
  </template>
</match>

```
## License

Apache2 License