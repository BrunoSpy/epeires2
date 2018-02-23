## Prérequis

* PC Linux
* Docker installé et configuré (`docker run hello-world` fonctionne)
* `docker-compose` installé (voir ici : https://docs.docker.com/compose/install/)

## Procédure d'installation

* Cloner le dépot

```
$ git clone <depot>
```

* Configurer l'application

```
$ cd <depot>/docker
$ cp local.php.dist local.php
```

* Lancer les containers via `docker-compose`

```
$ docker-compose up
```

> Cette commande prend un certain temps lors de son premier lancement. Les lancements suivants seront plus rapides.

* Attendre quelques secondes le bon fonctionnement de MariaDB (ligne de log `[Note] mysqld: ready for connections.`)

* Lancer les migrations

```
$ docker-compose exec epeires2 php public/index.php migrations:migrate
$ docker-compose exec epeires2 php public/index.php orm:generate-proxies
```

* Initialiser la base de données

```
$ docker-compose exec epeires2 php public/index.php initDB
```

* L'application est disponible sur `localhost:3000`

## Trucs et astuces

* Changer le port sur lequel l'application écoute :
  * Modifier `docker-compose.yml`
  * Relancer `docker-compose up`
* Placer l'application en arrière plan: `docker-compose up -d`
* Lancer un terminal dans le container Epeires2 : `docker-compose exec epeires2 /bin/bash`
* Lancer une commande PHP : `docker-compose exec epeires2 php <commande PHP>`
* Reconstruire le container Epeires (code PHP modifié par exemple) : `docker-compose build`
* Tester une autre version de base de données :
  * Supprimer les données: `docker-compose down --volumes`
  * Modifier `docker-compose.yml` pour faire référence à la version souhaitée
  * Relancer les containers: `docker-compose up`
  * Relancer les migrations
* Supprimer toute trace d'Epeires sur mon système:
  * `docker-compose down --volumes`
  * `docker system prune -a`
  * `rm -rf <depot>`

## Reste à faire

* Accès au B2B (WSDL + certificat)
* Environnement de développement
