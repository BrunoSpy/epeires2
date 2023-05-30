## Comment utiliser l'API :

#### 1. Importer la config "api-tools-mvc-auth" du fichier **local.php.dist**

#### 2. Créer un fichier **users.htpasswd** dans le dossier data

Pour generer un mot de passe htpasswd :

**https://www.web2generators.com/apache-tools/htpasswd-generator**

Le contenu de **data/users.htpasswd** devrait ressembler à cela :

username:$aezrr1$ouds7c81$ARJDxFOIJDkmKP89Kijdov/

#### 3. Récupération des Customfields :

Les customfields étant configurable pour chaque centre, il peut être utile de les récuperer au bon format avant de créer un évènement.

Pour cela, il faut faire la requête POST :

**http://lien_vers_epeires/api/customfields/getcustomfields**
avec le contenu suivant :

**Basic Auth** : username:password (le password à utiliser est la version non cryptée du mot de passe htpasswd)

**Content-Type** : application/json

**Body** : 
```json
{
    "categoryname": "nom de la catégorie",
}
```
le nom de la catégorie doit être le même que celui utilisé dans l'application.

Cela va retourner un json sous cette forme :
```json
{
    "category": "Nom de la catégorie",
    "dateDebut": "",
    "dateFin": "",
    "customFields": {
        "Nom": "",
        "Description": ""
    },
    "_links": {
        "self": {
            "href": "http://lien_vers_epeires/api/customfields/getcustomfields"
        }
    }
}
```

#### 4. Création d'un évenement :

Il faudra donc renvoyer ce fichier json avec les informations voulues pour créer un évènement à l'adresse suivante :

**http://lien_vers_epeires/api/event/addnewevent** avec le contenu suivant de cette façon:

**Basic Auth** : username:password

**Body** :
```json 
{
    "category": "Nom de la catégorie",
    "dateDebut": "2023-05-23 09:23",
    "dateFin": "2023-05-23 11:23",
    "customFields": {
        "Nom": "Nom de l'évènement",
        "Description": "Description de l'évènement",
        "Alias" : "Alias de l'évènement"
    },
    "_links": {
        "self": {
            "href": "http://lien_vers_epeires/api/customfields/getcustomfields"
        }
    }
}
```

#### 5. Modification d'un évenement :

Vous pouvez aussi modifier un évènement en cours en utilisant une requête PUT à l'adresse suivante : 

**http://lien_vers_epeires/api/event/id_evenement_a_modifier**

La requête doit contenir le même **body** et **auth** que pour la création d'un évènement.
