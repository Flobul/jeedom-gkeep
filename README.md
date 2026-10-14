# Google Keep pour Jeedom

[![License](https://img.shields.io/badge/License-GPL--2.0-blue?style=flat-square)](https://github.com/Flobul/jeedom-gkeep/blob/HEAD/LICENSE)
[![Language](https://img.shields.io/badge/Language-PHP-informational?style=flat-square)](https://github.com/Flobul/jeedom-gkeep)
[![Last commit](https://img.shields.io/badge/Last_commit-14/09/2026-brightgreen?style=flat-square)](https://github.com/Flobul/jeedom-gkeep/commits)
[![Open issues](https://img.shields.io/badge/Open_issues-0-brightgreen?style=flat-square)](https://github.com/Flobul/jeedom-gkeep/issues)
[![Open pull requests](https://img.shields.io/badge/Open_pull_requests-0-brightgreen?style=flat-square)](https://github.com/Flobul/jeedom-gkeep/pulls)

<!-- market-badges:start -->
[![Note](https://img.shields.io/badge/Note-Aucun%20avis-lightgrey?style=flat-square)](https://market.jeedom.com/index.php?v=d&p=market_display&id=4423)
[![Prix](https://img.shields.io/badge/Prix-Gratuit-brightgreen?style=flat-square)](https://market.jeedom.com/index.php?v=d&p=market_display&id=4423)
[![Téléchargements](https://img.shields.io/badge/T%C3%A9l%C3%A9chargements-863-informational?style=flat-square)](https://market.jeedom.com/index.php?v=d&p=market_display&id=4423)
[![Commentaires](https://img.shields.io/badge/Commentaires-0-lightgrey?style=flat-square)](https://market.jeedom.com/index.php?v=d&p=market_display&id=4423)
<!-- market-badges:end -->

Synchronise ses notes depuis Google Keep.
Et crée un équipement par note.
Prend en compte l'ordre, les cases des items.

## Connexion Google avec navigateur

Si la connexion avec mot de passe d’application échoue (`UNKNOWN_ERR` notamment), utilisez la méthode avec navigateur :

1. Relancez les dépendances pour actualiser gpsoauth et gkeepapi.
2. Dans la configuration, renseignez l’adresse du compte Google puis sauvegardez.
3. Dans **Connexion Google avec navigateur**, cliquez sur **Ouvrir Google** et authentifiez-vous avec ce même compte, y compris la validation 2FA demandée.
4. Après avoir accepté la demande Google, la page peut rester sur **Bienvenue** ou un chargement : n’attendez pas de retour automatique vers Jeedom. Dans **cet onglet Google**, ouvrez les outils de développement avec **F12** (Mac : **Option + Commande + I**). Allez dans **Application** (menu `»` si masqué), puis **Stockage → Cookies → https://accounts.google.com**. Filtrez sur `oauth_token`, sélectionnez la ligne et copiez uniquement sa **Valeur**.

   Si cette ligne est absente, vérifiez que le consentement Google a été accepté, puis recommencez depuis Ouvrir Google. Le cookie ne se récupère pas dans l’onglet Jeedom. L’adresse de la page, son paramètre `TL` et le fragment `#close` ne sont pas le jeton.
5. Collez cette valeur dans le champ du plugin, conservez ou renseignez un Android ID de 16 caractères hexadécimaux, puis cliquez sur **Valider la connexion Google**.
6. Après confirmation, rechargez la configuration avant toute nouvelle sauvegarde et lancez la synchronisation.

Le cookie temporaire n’est pas enregistré dans la configuration et est transmis au processus Python par son entrée standard, pas dans ses arguments. Le master token n’est enregistré qu’après vérification de l’accès à Keep. Traitez ces deux secrets comme un mot de passe : ne les publiez pas et ne les joignez pas aux logs. Les comptes déjà connectés continuent à utiliser leur jeton existant.

Ce parcours est celui documenté par [gpsoauth](https://github.com/simon-weber/gpsoauth#alternative-flow) et [gkeepapi](https://gkeepapi.readthedocs.io/en/latest/#authenticating). Il utilise une API non officielle et peut être refusé selon les restrictions du compte Google.

## Emojis dans les titres

Certaines installations Jeedom refusent les caractères Unicode sur quatre octets dans le nom des équipements. À la création, gkeep utilise un repère textuel pour ces caractères : `💼 Travail` devient `[U+1F4BC] Travail`. Les accents sont conservés et deux emojis différents gardent des repères distincts. Le titre et le contenu dans Google Keep ne sont pas modifiés. Les noms des équipements déjà créés ne sont pas renommés automatiquement.
