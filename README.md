# JobServer

JobServer is a skeleton repository used for creating background jobs. 
It contains the minimal configuration files and folders you will need for quick start from scratch.


## Installation

The recommended way to create a new application is through [Composer](http://getcomposer.org):

```sh
composer create-project tarantool/jobserver -s dev
```


## Quick start

First, create your own `docker-compose.override.yml` file by copying 
the [docker-compose.override.yml.dist](docker-compose.override.yml.dist) file 
and customize to your needs. Do the same for [.env.dist](.env.dist) 
and all `*.dist` files located in [app/config](app/config).

Then, browse to the project directory and execute this command:

```sh
docker-compose up -d
```

After the command has completed successfully, you'll have a running server ready to execute jobs.
If you open a log file in follow mode (`tail -f var/log/workers.log`), you'll see something like the following:

```sh
[2017-11-19 00:00:23] default:worker.DEBUG: Idling... [] []
[2017-11-19 00:00:24] default:worker.DEBUG: Idling... [] []
[2017-11-19 00:00:25] default:worker.DEBUG: Idling... [] []
```

Let's now try to add a task to the queue. This repository comes with a [demo job](src/UseCase/Greet/GreetHandler.php)
that writes a greeting to the log. By running the following command: 


```sh
docker-compose exec worker ./jobserver queue:put default -H tarantool \
    '{"payload": {"service": "greet", "args": {"name": "foobar"}}}'
```

we add a task to the `default` queue with a job payload, 
where `greet` is a job name and `foobar` is an argument passing to a job callable. 

Now in the log you will see that the job is executed:

```sh
[2017-11-19 00:00:32] jobserver.INFO: HELLO FOOBAR [] []
[2017-11-19 00:00:33] default:worker.DEBUG: Idling... [] []
[2017-11-19 00:00:34] default:worker.INFO: Task #0 was successfully processed. {"payload":{"args":{"name":"foobar"},"service":"greet"}} []
``` 

Also, you can run the job directly in the console, bypassing the queue:

```sh
docker-compose exec worker ./jobserver -vvv handler:greet foobar
```

To be able to run a job from the console, you need to write an adapter for the symfony command 
and register it in [app/config/commands.php](app/config/commands.php). This is how the adapter 
looks like for GreetHandler: [GreetCommand](src/UseCase/Greet/GreetCommand.php).

To see a list of all registered commands, run:

```sh
docker-compose exec worker ./jobserver
```


## Tarantool

To use a web interface for Tarantool, open your browser and access 
the [http://localhost:8001](http://localhost:8001/) address (make sure that 
Docker containers are running).

To get into Tarantool console as admin on a running Docker container, execute:

```sh
docker-compose exec tarantool tarantoolctl connect /var/run/tarantool/tarantool.sock
```

On a server:

```sh
sudo tarantoolctl enter jobserver_instance
```

On the server as a job queue user:

```sh
sudo tarantoolctl connect $TNT_JOBQUEUE_USER:$TNT_JOBQUEUE_PASSWORD@$TNT_JOBQUEUE_HOST:3301
```


## Tests

```sh
docker-compose exec worker vendor/bin/phpunit
```


## Debug

To debug a job runner, first, stop the worker container

```sh
docker-compose stop worker
``` 

Then, start listening for php debug connections and then execute:

```sh
LOCAL_IP=<your-local-ip> docker-compose run --rm worker bash -c ' \
    TNT_JOBQUEUE_PASSWORD=jobserver \
    vendor/bin/jobqueue run default \
    --config app/config/jobqueue.php \
    --executors-config app/config/executors.php \
    --user jobserver \
    --host tarantool \
'
```

> *Note*
>
> Check [this manual](https://confluence.jetbrains.com/display/PhpStorm/Simultaneous+debugging+sessions+with+PhpStorm) to learn 
> how to debug multiple processes (for example, the runner and background jobs) 
> simultaneously in PhpStorm.


## License

The library is released under the MIT License. See the bundled [LICENSE](LICENSE) file for details.
