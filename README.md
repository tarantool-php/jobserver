# JobServer

JobServer is a skeleton repository used for creating background jobs. 
It contains the minimal configuration files and folders you will need for quick start from scratch.


## Setup

First, create your own `docker-compose.override.yml` file by copying 
the [docker-compose.override.yml.dist](docker-compose.override.yml.dist) file and customize to your needs.
Do the same for all `*.dist` files located in [app/config](app/config).

Then, browse to the project directory and execute this command:

```bash
docker-compose up -d
```


## Run cli

```bash
docker-compose exec worker ./jobserver
```


## Put jobs into the "default" queue

```bash
docker-compose exec worker ./jobserver queue:put default '{"payload": {"service": "greet", "args": {"name": "foobar"}}}' -H tarantool
``` 


## Debug cli

```bash
docker-compose exec worker bash -c 'PHP_IDE_CONFIG="serverName=jobserver.dev" ./jobserver'
```

To debug the job runner, run

```bash
docker-compose run --rm worker bash -c ' \
    PHP_IDE_CONFIG="serverName=jobserver.dev" \
    TNT_JOBQUEUE_PASSWORD=jobserver \
    vendor/bin/jobqueue run default \
    --config app/config/jobqueue.php \
    --executors-config app/config/executors.php \
    --log-file var/log/workers.log \
    --user jobserver \
    --host tarantool \
'
```


## Tarantool

[Admin Web Interface](http://localhost:8001/)

To get into Tarantool console as admin on a running Docker container, run:

```bash
docker-compose exec tarantool tarantoolctl connect /var/run/tarantool/tarantool.sock
```

On a server:

```bash
sudo tarantoolctl enter jobserver_instance
```

On the server as a job queue user:

```bash
sudo tarantoolctl connect $TNT_JOBQUEUE_USER:$TNT_JOBQUEUE_PASSWORD@$TNT_JOBQUEUE_HOST:3301
```


## Run tests

```bash
docker-compose exec worker vendor/bin/phpunit
```


## License

The library is released under the MIT License. See the bundled [LICENSE](LICENSE) file for details.
