# sym5.2

## Install Full Symfony 

With the console:

    symfony new --full sym52

#### To start the server

    cd sym52
    symfony server:start -d

The default URL is:

https://127.0.0.1:8000/

#### To stop the server

    symfony server:stop

## Configuration

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

#### Then generate the getters and setters

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

## Controller

    php bin/console make:controller

We are creating HomeController

We put the url at the root and we take the necessary entities

    src/Controller/HomeController.php
    ...
    use App\Entity\Message;
    use App\Entity\Section;
    ...
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {


## Template Twig

We will take a simple bootstrap template for this example:

Download this files:

https://github.com/mikhawa/sym5.2/blob/main/datas/templates.zip

1) Unzip the file and replace its contents with the files in /templates


    templates/
    templates/base.html.twig
    templates/bootstrap_4.html.twig
    templates/home/index.html.twig


2) Download this file then put it in the public folder

https://raw.githubusercontent.com/mikhawa/sym5.2/main/public/favicon.ico
   
    public/favicon.ico

## Queries with Doctrine

We are going to load the menu first, so let's leave room for it in Twig

    templates/home/index.html.twig
    ...
    {% block menuhaut %}{% endblock %}
    {% block content %}
    ...

Now with Doctrine we will recover all our sections

    src/Controller/HomeController.php
    ...
    // sections for menuhaut
        $sections = $this->getDoctrine()
            ->getRepository(Section::class)
            ->findAll();

        return $this->render('home/index.html.twig', [
            'sectionsMenuHaut' => $sections,
        ]);

In template:

    templates/home/index.html.twig
    ...
    {% block menuhaut %}
    {% for sections in sectionsMenuHaut %}
        <li class="nav-item">
            <a class="nav-link" href="#">{{ sections.sectiontitle }}</a>
        </li>
    {% endfor %}
    {% endblock %}

Now with Doctrine we will recover all our messages

    src/Controller/HomeController.php
    ...
    // messages for content
        $messages = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findBy([], ['idmessage' => 'desc']);

        return $this->render('home/index.html.twig', [
            'sectionsMenuHaut' => $sections,
            'messages' => $messages,
        ]);

In template:

    templates/home/index.html.twig
    ...
    <hr>
                {% block subcontent %}
                    {% for item in messages %}
                        <h3>{{ item.messagetitle }}</h3>
                        <h5>{% for section in item.sectionIdsection %}
                                {{ section.sectiontitle }} |
                            {% endfor %}</h5>
                        <p>{{ item.messagetext }}</p>
                        <h6>
                            Le {{ item.getMessagedate|date("d/m/Y à H:i") }} 
                            {# par {{ item.getUserIduser.userlogin }} #}</h6>
                        <hr>
                    {% endfor %}
                {% endblock %}

Everything works except to reach the user !