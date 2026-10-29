# Changelog

## Non publié

## Version 1.2.0 (29/10/2026)

- La synchronisation signale désormais les comptes en erreur au lieu d'annoncer une réussite lorsque les notes n'ont pas été récupérées.
- Les tuiles des équipements activés ne sont plus grisées à tort et la page de configuration affiche « Nom de la note ».
- Les noms de notes accentués hérités d'un ancien encodage sont réparés sans écraser les noms personnalisés.
- Les retours à la ligne et les caractères HTML des notes restent du texte dans le widget et dans Google Keep.
- Le journal de diagnostic n'affiche plus le contenu des notes ni des modifications.
- Les éléments de liste et les noms d'équipements s'affichent comme du texte, sans interpréter de code HTML ; les identifiants de connexion ne passent plus dans la ligne de commande.
- Les équipements ne sont plus supprimés automatiquement si une réponse Google Keep est incomplète ; une note supprimée reste donc à retirer manuellement de Jeedom.
- Les listes vidées conservent l'action d'ajout sans afficher d'anciens éléments ; les pièces jointes d'une même note gardent des fichiers distincts.
- Le rafraîchissement programmé récupère les notes une fois par compte et n'actualise que les équipements activés déjà présents.
- Noms des nouveaux équipements compatibles avec les colonnes UTF-8 Jeedom : caractères Unicode sur quatre octets représentés par leur code, sans modifier les notes Google.
- Synchronisation des notes sans liste : suppression du count(null) qui interrompait le traitement sous PHP 8.
- Aide détaillée pour récupérer oauth_token lorsque la page Google reste en chargement.

## Version 1.01.0 (14/10/2026)

- Refonte de la structure du code afin d'améliorer sa lisibilité et sa maintenabilité
- Ajout de la connexion Google via le cookie oauth_token.
- Mise à jour de la gestion des erreurs.
- Amélioration de la documentation et des traductions.
- Traductions françaises : restauration des chemins Jeedom complets et des barres obliques échappées.
- Connexion Google par échange du cookie oauth_token avec gpsoauth ; validation Keep avant enregistrement, secrets transmis via stdin, compatibilité authenticate/resume et erreur lisible du login par mot de passe.

## Version 1.0.2 (14/09/2026)

- Ajout de la gestion des noms d'équipements compatibles UTF-8.
- Amélioration de la documentation sur la connexion Google.

## Version 1.00 (19/07/2026)

- Refonte de la gestion des commandes afin d'utiliser des tableaux plutôt que des chaînes de caractères, pour une meilleure lisibilité et une plus grande facilité de maintenance.
- Suppression des messages de console des modèles HTML et des fichiers PHP.
- Passage en JS Vanilla.

## Version 0.99 (20/12/2023)

- Modification de couleur par défaut : transparent à la place de blanc.
- Forçage d'installation de gpsoauth<1.0.3
- Correction .js manquant sur le panel.
- Modification du nom de fichier blob (edited au lieu de timestamp du téléchargement)
- Correction affichage du bouton Delete sur le widget

## Version 0.98 (22/09/2023)

- Correction des guillemets pour mot de passe.

## Version 0.97 (03/08/2023)

- Correction js pure/jquery.
- Correction hauteur de widget.

## Version 0.96 (28/07/2023)

- Suppression definitive de keyring.
- Renvoie et récupération de la sortie des commandes shell.

## Version 0.95 (22/07/2023)

- Correction d'affichage de la taille du widget.

## Version 0.94 (21/07/2023)

- Ajout d'un filtre de compte sur la page d'équipement.
- Correction des méthodes avec plusieurs comptes.
- Correction de l'affichage du logo.
- Correction de la couleur sur create_note.
- Première tentative d'utilisation d'un jeton au lieu de keyring.

## Version 0.93 (13/07/2023)

- Tri des équipements selon l'ordre Google Keep.
- Affichage d'info supplémentaire suur les icones des équipements au glissé de la souris.
- Correction du retrait de l'info 'List' dans le script

## Version 0.92 (12/07/2023)

- Amelioration générale du code (phpdoc et séquencement)
- Correction du bug de sauvegarde d'équipement (modifiait l'info pinned/archived/trashed)
- Gestion des médias dans les notes (images, enregistrements)
- Ajout des widget de commande pour images, audio et video.
- Gestion d'affichage des équipements asTable.

## Version 0.91 (09/07/2023)

- Suppression des notes retirées lors de synchronisation.
- Correction du tri des commandes.
- Correction des cases à cocher sur le dashboard.
- Ajout d'un panel.
- Ajout des couleurs choisis sur les icônes des équipements.
- Mise à jour de la page santé.
- Correction de l'id du market.
- Correction du nom d'équipement.
- Changement d'icône.
- Modification du nom du script.
- Correction de suppression totale des équipements lors de synchronisation.
- Ajout de paramètres d'équipement pour le widget dashboard.
- Ajout des couleurs de fond pour l'équipement.

## Version 0.40 (08/07/2023)

- Ajout du bouton pour créer une note.

## Version 0.10 (06/07/2023)

- Première version.
