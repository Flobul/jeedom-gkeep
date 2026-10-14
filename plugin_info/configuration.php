<?php
/* This file is part of Jeedom. */
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
    include_file('desktop', '404', 'php');
    die();
}

$plugin = plugin::byId('gkeep');
$update = $plugin->getUpdate();
sendVarToJS('version', gkeep::$_pluginVersion);
$token = config::byKey('token', 'gkeep', array());
if (!is_array($token)) {
    $token = array();
}
$androidIds = config::byKey('android_id', 'gkeep', array());
if (!is_array($androidIds)) {
    $androidIds = array();
}
$accountCount = max(1, (int) config::byKey('max_account_number', 'gkeep', 1));
?>

<?php include_file('desktop', 'plugin-banner', 'css', 'gkeep'); ?>

<form class="form-horizontal" id="configuration_plugin_gkeep">
    <div class="gkeep-config-page">
        <header class="gkeep-config-hero">
            <div class="gkeep-config-identity">
                <div class="gkeep-config-logo"><img src="plugins/gkeep/plugin_info/gkeep_icon.png" alt="Google Keep"></div>
                <div>
                    <span class="gkeep-config-eyebrow">{{Configuration du plugin}}</span>
                    <h2>Google Keep</h2>
                    <p>{{Connectez votre compte Google Keep à Jeedom et retrouvez vos notes et listes.}}</p>
                </div>
            </div>
            <div class="gkeep-config-meta">
                <span><i class="fas fa-code-branch"></i> {{Version}} <strong>v<?php echo htmlspecialchars((string) gkeep::$_pluginVersion, ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <?php if (is_object($update)) { ?>
                    <span><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars((string) $update->getLocalVersion(), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="fas fa-stream"></i> <?php echo htmlspecialchars((string) $update->getConfiguration('version', 'stable'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="fas fa-cloud-download-alt"></i> <?php echo htmlspecialchars((string) $update->getSource(), ENT_QUOTES, 'UTF-8'); ?></span>
                <?php } ?>
            </div>
            <div class="gkeep-config-links">
                <a class="btn btn-sm gkeep-config-link-documentation" target="_blank" rel="noopener noreferrer" href="<?php echo htmlspecialchars((string) $plugin->getDocumentation(), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-book"></i> {{Documentation}}</a>
                <a class="btn btn-sm gkeep-config-link-changelog" target="_blank" rel="noopener noreferrer" href="<?php echo htmlspecialchars((string) $plugin->getChangelog(), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-list"></i> {{Changelog}}</a>
                <a class="btn btn-sm gkeep-config-link-community" target="_blank" rel="noopener noreferrer" href="https://community.jeedom.com/tag/plugin-gkeep"><i class="fas fa-comments"></i> {{Communauté}}</a>
            </div>
        </header>

        <section class="gkeep-config-card">
            <div class="gkeep-config-card-header">
                <i class="fas fa-sync-alt"></i>
                <div>
                    <h3>{{Synchronisation}}</h3>
                    <p>{{Définissez la fréquence de récupération des notes et listes Google Keep.}}</p>
                </div>
            </div>
            <div class="gkeep-config-card-body">
                <div class="form-group">
                    <label class="col-lg-4 control-label">
                        {{Intervalle de rafraîchissement des informations (cron)}}
                        <sup><i class="fas fa-question-circle" title="{{Sélectionnez l’intervalle auquel le plugin récupère les informations sur les serveurs Google Keep.}}"></i></sup>
                    </label>
                    <div class="col-lg-4">
                        <select class="configKey form-control" data-l1key="autorefresh">
                            <option value="* * * * *">{{Toutes les minutes}}</option>
                            <option value="*/2 * * * *">{{Toutes les 2 minutes}}</option>
                            <option value="*/3 * * * *">{{Toutes les 3 minutes}}</option>
                            <option value="*/4 * * * *">{{Toutes les 4 minutes}}</option>
                            <option value="*/5 * * * *">{{Toutes les 5 minutes}}</option>
                            <option value="*/10 * * * *">{{Toutes les 10 minutes}}</option>
                            <option value="*/15 * * * *">{{Toutes les 15 minutes}}</option>
                            <option value="*/30 * * * *">{{Toutes les 30 minutes}}</option>
                            <option value="*/45 * * * *">{{Toutes les 45 minutes}}</option>
                            <option value="">{{Jamais}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <section class="gkeep-config-card">
            <div class="gkeep-config-card-header">
                <i class="fas fa-user-cog"></i>
                <div>
                    <h3>{{Authentification}}</h3>
                    <p>{{Configurez les comptes Google Keep synchronisés par le plugin.}}</p>
                </div>
            </div>
            <div class="gkeep-config-card-body">
                <div class="form-group">
                    <label class="col-sm-4 control-label">
                        {{Nombre de comptes}}
                        <sup><i class="fas fa-question-circle" title="{{Entrez le nombre maximum de comptes à synchroniser.}}"></i></sup>
                    </label>
                    <div class="col-sm-2">
                        <input type="number" min="1" max="10" class="configKey form-control" data-l1key="max_account_number" placeholder="1">
                    </div>
                </div>

                <div class="gkeep-config-accounts">
                    <?php for ($i = 1; $i <= $accountCount; $i++) { ?>
                        <article class="gkeep-config-account">
                            <header><i class="fab fa-google"></i><h4>{{Compte n°}}<?php echo $i; ?></h4></header>
                            <div class="gkeep-config-account-body">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">
                                        {{Adresse email}}
                                        <sup><i class="fas fa-question-circle" title="{{Entrez l’identifiant.}}"></i></sup>
                                    </label>
                                    <div class="col-sm-7">
                                        <input type="text" class="configKey form-control" data-l1key="email" data-l2key="<?php echo $i; ?>" placeholder="mon@email.com">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">
                                        {{Mot de passe}}
                                        <sup><i class="fas fa-question-circle" title="{{Entrez le mot de passe.}}"></i></sup>
                                    </label>
                                    <div class="col-sm-7">
                                        <div class="input-group">
                                            <input type="text" class="inputPassword configKey form-control" data-l1key="password" data-l2key="<?php echo $i; ?>" placeholder="password">
                                            <span class="input-group-btn"><a class="btn btn-default bt_showPass roundedRight"><i class="fas fa-eye"></i></a></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">
                                        {{Ancienne connexion par mot de passe}}
                                        <sup><i class="fas fa-question-circle" title="{{Cliquez ici pour la première connexion.}}"></i></sup>
                                    </label>
                                    <div class="col-sm-7">
                                        <a data-credential="<?php echo $i; ?>" class="btn btn-success bt_getCredentials gkeep-legacy-login"><i class="fas fa-user-circle"></i></a>
                                    </div>
                                </div>

                                <div class="form-group gkeep-browser-auth" data-account="<?php echo $i; ?>">
                                    <label class="col-sm-4 control-label">{{Connexion Google avec navigateur}}</label>
                                    <div class="col-sm-7">
                                        <p>{{Enregistrez l’adresse email, puis connectez-vous au même compte Google dans la page ci-dessous. Dans les outils de développement, Application / Cookies, copiez uniquement la valeur du cookie oauth_token.}}</p>
                                        <p class="help-block">{{Après validation Google, la page peut rester sur un écran de chargement : n’attendez pas une redirection. Dans cet onglet Google, ouvrez F12 (Mac : Option + Commande + I), puis Application → Stockage → Cookies → https://accounts.google.com. Filtrez sur oauth_token et copiez uniquement sa Valeur. Si le cookie est absent, terminez le consentement Google ou recommencez le parcours ; le paramètre TL de l’adresse ne remplace pas ce cookie.}}</p>
                                        <div class="gkeep-browser-fields">
                                            <a href="https://accounts.google.com/EmbeddedSetup" target="_blank" rel="noopener noreferrer" class="btn btn-default"><i class="fas fa-external-link-alt"></i> {{Ouvrir Google}}</a>
                                            <label for="gkeep-oauth-cookie-<?php echo $i; ?>">{{Cookie OAuth (oauth_token)}}</label>
                                            <input id="gkeep-oauth-cookie-<?php echo $i; ?>" type="password" class="form-control gkeep-oauth-cookie" autocomplete="off" placeholder="oauth_token" aria-label="oauth_token">
                                            <label for="gkeep-android-id-<?php echo $i; ?>">{{Android ID}}</label>
                                            <input id="gkeep-android-id-<?php echo $i; ?>" type="text" class="form-control gkeep-android-id" value="<?php echo htmlspecialchars((string)($androidIds[$i] ?? '0123456789abcdef'), ENT_QUOTES, 'UTF-8'); ?>" maxlength="16" aria-label="Android ID">
                                            <p class="help-block">{{Identifiant hexadécimal de 16 caractères représentant l’appareil Android virtuel. Il doit rester identique pour l’échange du cookie et les connexions Google Keep suivantes.}}</p>
                                            <p class="help-block">{{Ne partagez jamais le cookie OAuth ni le master token.}}</p>
                                            <button type="button" class="btn btn-success gkeep-browser-login">{{Valider la connexion Google}}</button>
                                            <p class="gkeep-browser-result" role="status"></p>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($token[$i])) { ?>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">
                                            {{Jeton d'accès}}
                                            <sup><i class="fas fa-question-circle" title="{{Jeton d’accès généré}}"></i></sup>
                                        </label>
                                        <div class="col-sm-7">
                                            <input type="password" readonly class="configKey form-control" data-l1key="token" data-l2key="<?php echo $i; ?>" placeholder="{{Jeton enregistré}}">
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            </div>
        </section>
    </div>
</form>

<?php include_file('desktop', 'configuration', 'js', 'gkeep'); ?>