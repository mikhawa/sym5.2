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
                            {{ item.userIduser.userlogin }}</h6>
                        <hr>
                    {% endfor %}
                {% endblock %}

Symfony does the joins itself!

However, the number of queries is too high, using production mode caching is one solution, creating your own SQL query is another!

### Twig truncate

To use truncate for words without cutting them (false), we will use this method:

    {{ item.messagetext|u.truncate(50, '...', false) }}

You need to install string-extra first:

    composer require twig/string-extra

documentation: https://twig.symfony.com/doc/3.x/filters/u.html

## Create sections

There are no route yet except for the homepage (and the Twig debug profiler), we can check it by typing:

    php bin/console debug:route

We will add a method in our HomeController:

    src/Controller/HomeController.php
    ...
    /**
     * @Route("/section/{slug}", name="section")
     */
    public function section(string $slug): Response
    {
        // sections to menuhaut
        $sections = $this->getDoctrine()
            ->getRepository(Section::class)
            ->findAll();

        // section to page
        $section = $this->getDoctrine()
            ->getRepository(Section::class)
            ->findOneBy(["sectionslug" => $slug]);
    
    // Twig's view
        return $this->render('home/section.html.twig', [
            'sectionsMenuHaut' => $sections,
            'section' => $section,
        ]);

In the new section.html.twig

    templates/home/section.html.twig
    ...
    {% block title %}{{ parent() }}Section {{ section.sectiontitle }}{% endblock %}
    {% block menuhaut %}
        {% for sections in sectionsMenuHaut %}
            <li class="nav-item">
                <a class="nav-link" href="#">{{ sections.sectiontitle }}</a>
            </li>
        {% endfor %}
    {% endblock %}
    {% block content %}
        <main role="main" class="flex-shrink-0">
            <div class="container">
                <h1 class="mt-5">Section {{ section.sectiontitle }}</h1>
                <hr>
            </div>
        </main>
    {% endblock %}

### Separate menu

To use a common's menu to our views, we  create:

    templates/home/_menu.html.twig
    ...
    {% for sections in sectionsMenuHaut %}
    <li class="nav-item">
        <a class="nav-link" 
    href="{{ path("section",{"slug":sections.sectionslug}) }}"
    >{{ sections.sectiontitle }}</a>
    </li>
    {% endfor %}

We import it into each of our views (index.html.twig and section.html.twig)

    templates/home/index.html.twig
    ...
    {% block menuhaut %}
        {{ include('home/_menu.html.twig') }}
    {% endblock %}

### Link into homepage
You can change the links to the sections in the home page on the articles:

    templates/home/index.html.twig
    ...
    <h5>{% for section in item.sectionIdsection %}
    <a href="{{ path("section",{"slug":section.sectionslug}) }}">{{ section.sectiontitle }}</a>
    {% if not loop.last  %} | {% endif %}
    {% endfor %}</h5>

{% if not loop.last  %} | {% endif %} => show the | if we are not at the end

Now you can test the top menu, and the links to the sections on the homepage

### Message by section

We create a new file which will be the manager of the Message class

    src/Repository/MessageRepository.php

This file is automatically created when we use the command:

    php bin/console make:entity

Here it is a modified copy for the Message table, with a new method in DQL (sql for Doctrine) named findAllMessagesBySection()

Copy this file into /src/Repository/MessageRepository.php:

https://raw.githubusercontent.com/mikhawa/sym5.2/main/src/Repository/MessageRepository.php

Now, update Message.php

    src/Entity/Message.php
    ...
    use App\Repository\MessageRepository;
    ...
    /**
    * Message
    * @ORM\Entity(repositoryClass=MessageRepository::class)
    ...

### Query for sections messages

Using findAllMessagesBySection()

    src/Controller/HomeController.php
    ...
    /**
     * @Route("/section/{slug}", name="section")
     */
    public function section(string $slug): Response
    {
    ...
    // All category's message
        $messages = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findAllMessagesBySection($section->getIdsection());

        // Twig's view
        return $this->render('home/section.html.twig', [
            'sectionsMenuHaut' => $sections,
            'section' => $section,
            'messages' => $messages,
        ]);
        ...

Copy into {% block subcontent %} the same as index.html.twig, changing the text lenght, and the number of articles:

    templates/home/index.html.twig
    ...
    <h1 class="mt-5">Section {{ section.sectiontitle }} 
    ({{ messages|length }})</h1>
    ...
    {% block subcontent %}
        {% for item in messages %}
    <h3>{{ item.messagetitle }}</h3>
        <h5>{% for section in item.sectionIdsection %}
    <a href="{{ path("section",{"slug":section.sectionslug}) }}"
    >{{ section.sectiontitle }}</a>{% if not loop.last  %} | {% endif %}
    {% endfor %}</h5>
        <p>{{ item.messagetext|u.truncate(120, '...', false) }}</p>
        <h6>
        Le {{ item.getMessagedate|date("d/m/Y à H:i") }}
        par {{ item.userIduser.userlogin }}         </h6>
    <hr>
    {% endfor %}
    {% endblock %}

### Query for message's detail

Article method

    src/Controller/HomeController.php
    ...
    /**
     * @Route("/article/{slug}", name="article")
     */
    public function article(string $slug): Response
    {
        // sections to menuhaut
        $sections = $this->getDoctrine()
            ->getRepository(Section::class)
            ->findAll();

        // message detail
        $message = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findOneBy(["messageslug" => $slug]);

        // Twig's view
        return $this->render('home/message.html.twig', [
            'sectionsMenuHaut' => $sections,
            'message' => $message,
        ]);
    }

#### Create the view

    templates/home/message.html.twig
    ...
    {% extends 'bootstrap_4.html.twig' %}

    {% block title %}{{ parent() }}{{ message.messagetitle }}{% endblock %}
    ...
    <h1 class="mt-5">{{ message.messagetitle }}</h1>
    ...
    {% block subcontent %}

    <h3>{{ message.messagetitle }}</h3>
                    <h5>{% for section in message.sectionIdsection %}
    <a href="{{ path("section",{"slug":section.sectionslug}) }}" >{{ section.sectiontitle }}</a>
    {% if not loop.last %} | {% endif %}
    {% endfor %}</h5>
    <p>{{ message.messagetext }}</p>
    ...

#### Update section.html.twig and index.html.twig
    
    templates/home/index.html.twig
    ...
    <p><a href="{{ path("article",{"slug":item.messageslug}) }}"
    >Lire la suite</a></p>

    
    templates/home/section.html.twig
    ...
    <p><a href="{{ path("article",{"slug":item.messageslug}) }}"
    >Lire la suite</a></p>

### Error 404

create an error 404 template
    
    templates/home/error404.html.twig
    ...
    {% extends 'bootstrap_4.html.twig' %}
    {% block title %}{{ parent() }}Error 404{% endblock %}
    {% block menuhaut %}
        {{ include('home/_menu.html.twig') }}
    {% endblock %}
    {% block content %}
        <main role="main" class="flex-shrink-0">
            <div class="container">
                <h1 class="mt-5">Error 404</h1>
                <hr>
                {% block subcontent %}
                    <h3>{{ errormessage }}</h3>
                    <hr>
                {% endblock %}
            </div>
        </main>
    {% endblock %}


update HomeController

    src/Controller/HomeController.php
    ...
    if(!$section){
            // Twig's view error 404
            return $this->render('home/error404.html.twig', [
                'sectionsMenuHaut' => $sections,
                'errormessage' => "Cette section n'existe pas",
            ]);
        }
    ...
    if(!$message){
            // Twig's view error 404
            return $this->render('home/error404.html.twig', [
                'sectionsMenuHaut' => $sections,
                'errormessage' => "Cet article n'existe plus",
            ]);
        }
    ...

## Authentificator

Open User.php:

    src/Entity/User.php
    ...
    use Symfony\Component\Security\Core\User\UserInterface;
    ...
    class User implements UserInterface
    ...
    // generate implements methods

    public function getRoles()
    {
        // TODO: Implement getRoles() method.
    }

    public function getPassword()
    {
        // TODO: Implement getPassword() method.
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function getUsername()
    {
        // TODO: Implement getUsername() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

Open console:

    php bin/console make:auth
    -> 1
    -> UserAuthenticator
    -> SecurityController
    -> App\Entity\User
    -> [1] userlogin
    -> yes
    
In config/security.yaml

    config/security.yam
    ...
     providers:
        users_in_memory: { memory: null }
        app_user_provider:
            entity:
                class: App\Entity\User
                property: userlogin
    ...
    main:
            anonymous: true
            lazy: true
            provider: users_in_memory
            guard:
                authenticators:
                    - App\Security\UserAuthenticator
            logout:
                path: app_logout
    ...

In src/Security/UserAuthenticator.php

    src/Security/UserAuthenticator.php
    ...
    public function checkCredentials($credentials, UserInterface $user)
    {
        // Check the user's password or other credentials and return true or false
        // If there are no credentials to check, you can just return true
        if($credentials['password'] == $user->getPassword()){
            return true;
        }else{
            throw new CustomUserMessageAuthenticationException('Error password');
        }
    }

in src/Entity/User.php

    src/Entity/User.php
    ...
    public function getRoles(): array
    {

       // get current Role
        $role = $this->roleIdrole->current();

        // get this value
        $roles[] = $role->getRolevalue();
        // get the default value
        $roles[] = 'ROLE_USER';
        // return value without duplicate ROLE (for ROLE_USER)
        return array_unique($roles);
    }

    public function getPassword(): string
    {
        return $this->getUserpwd();
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function getUsername():string
    {
        return $this->getUserlogin();
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

### Redirection

In src/Security/UserAuthenticator.php

    src/Security/UserAuthenticator.php
    ...
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        // Redirection
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }


You can login at this adress:

https://127.0.0.1:8000/login

with :
    
    Mikhawa
    1234
    -> ROLE_ADMIN (and ROLE_USER) 
    ---
    Michaël
    1234
    -> ROLE_MOD (and ROLE_USER) 
    ---
    Mike
    1234
    -> ROLE_USER

### Menu login/disconnect



## Create a CRUD

    php bin/console make:crud
    -> Message
    -> MessageController

Go to MessageController.php and update the root

    /**
    * @Route("/admin/message")
    */
    class MessageController extends     AbstractController
    {