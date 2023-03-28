# TodoList - Project 8

Codacy Badge :
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/f9dd76845b1b4f1381e5a1b8507b93fd)](https://app.codacy.com/gh/sebzz07/todoList/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

## Installation :

This project has been developed under php 8.1 and symfony 6.1.

### Start this project in localhost mode, run some command lines:


1. clone the GitHub repo:

```git clone https://github.com/sebzz07/todoList.git```

2. install dependencies with composer:

```composer install```

3. install dependencies with npm (or yarn if you prefer) :

```npm install```

4. run :

```npm run build```

5. Create and fill out your own ```.env.*```


7. Create database and some fixtures via doctrine :

```symfony console doctrine:database:create```

```symfony console make:migration```

```symfony console doctrine:migrations:migrate```

```symfony console doctrine:fixtures:load```

8. run local server :

````symfony server:start -d````


*Now the project is normally deploy correctly*


## Information to Test the project :

1. Create database of test and fixtures via doctrine : 

2. Check if Xdebug work.

3. run phpunit to check everything is correct : 

(On windows)
```vendor\bin\phpunit --coverage-html public\coverage```

4. check the report "public/coverage/index.html"
