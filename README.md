# sym5.2

create env.local with sym52 MariaDB database

    env.local
    ...
    DATABASE_URL="mysql://root:@127.0.0.1:3308/sym52"

## Import DB

Importing the DB structure (MariaDB 10.4):

### sym52

https://raw.githubusercontent.com/mikhawa/sym5.2/main/datas/sym52-structure.sql

![DB sym52](https://raw.githubusercontent.com/mikhawa/sym5.2/main/datas/sym52.png)


### Create entities

    php bin/console doctrine:mapping:import App\Entity annotation --path=src/Entity

####Then generate the getters and setters

    php bin/console make:entity --regenerate App\Entity\User
    
    php bin/console make:entity --regenerate App\Entity\Section

    php bin/console make:entity --regenerate App\Entity\Role

    php bin/console make:entity --regenerate App\Entity\Message

## Fixtures

    composer require orm-fixtures --dev

A file is created ->

    src/DataFixtures/AppFixtures.php

Update your AppFixtures with this datas:

https://raw.githubusercontent.com/mikhawa/sym5.2/main/src/DataFixtures/AppFixtures.php

For insert into DB:

    php bin/console doctrine:fixtures:load

