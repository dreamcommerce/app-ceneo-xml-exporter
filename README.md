Ceneo
=====

Application allows to generate extended Ceneo XML formats.

### Required permissions

Upon registration, make sure you request following permissions:

- Attributes
- Categories
- Deliveries
- Producers
- Products
- Taxes
- Units

### Application links

Application uses these links in admin panel:

- Options - https://app.example.com/options - application options: export enforcing, attributes mapping and exclusions management
- Add exclusion - https://app.example.com/exclusions/add - add product exclusion; needed to be added in product list context

### Requirements

Exporter need:

- PHP 5.4
- [Gearman](http://gearman.org/) (tested with 1.1.12 version)
- [Supervisord](http://supervisord.org/) is recommended for keeping export workers alive
- Cron access is recommended for scheduling jobs

### Installation

# register application in Appstore
# clone this repository
# ``composer install``
# bring yourself some coffee
# type required parameters - database, appstore credentials
# you can execute worker by typing ``php app/console ceneo:worker``

Install application in your shop and click on ``Generate XML`` what enqueue a job.

In order to enqueue all shops job daily, add ``php app/console ceneo:enqueue`` in cron.

### Supervisor

If you want to workers be maintained automatically and spawn more workers, use some user-level process manager, for example Supervisor.

Feel free to use this definition and modify for your needs

```
[program:ceneo_worker]
directory=/home/app/directory
command=/usr/bin/php app/console ceneo:worker -n --env=prod
process_name=%(program_name)s_%(process_num)02d
numprocs=5
autostart=true
aurorestart=true
redirect_stderr=true
```

It will spawn 5 workers and listen for jobs.