<?php
/* This file is part of Jeedom. */
if (!isConnect('admin')) {
    throw new Exception(__('401 - Accès non autorisé', __FILE__));
}

function gkeepHealthEscape($_value)
{
    return htmlspecialchars((string) $_value, ENT_QUOTES, 'UTF-8');
}

$eqLogics = gkeep::byType('gkeep');
usort($eqLogics, 'gkeep::compareEqLogic');
$noteCount = 0;
$listCount = 0;
$pinnedCount = 0;
foreach ($eqLogics as $healthEqLogic) {
    if ($healthEqLogic->getConfiguration('type') === 'List') {
        $listCount++;
    } else {
        $noteCount++;
    }
    if ($healthEqLogic->getConfiguration('pinned', false) === true) {
        $pinnedCount++;
    }
}
?>
<?php include_file('desktop', 'plugin-banner', 'css', 'gkeep'); ?>
<?php include_file('desktop', 'health', 'css', 'gkeep'); ?>

<div id="gkeepHealth">
    <header id="gkeep-health-banner" class="gkeep-health-hero">
        <div class="gkeep-health-hero-icon"><i class="fas fa-heartbeat"></i></div>
        <div class="gkeep-health-hero-copy">
            <span class="gkeep-health-eyebrow">{{Diagnostic du plugin}}</span>
            <h2>{{Santé Google Keep}}</h2>
            <p>{{État des notes et listes, collaborateurs et dernières synchronisations.}}</p>
        </div>
        <button type="button" class="btn gkeep-health-refresh" id="bt_refreshHealth_gkeep"><i class="fas fa-sync-alt"></i> {{Rafraîchir}}</button>
    </header>

    <section class="gkeep-health-summary" aria-label="{{Résumé de la santé Google Keep}}">
        <article class="gkeep-health-summary-card">
            <span class="gkeep-health-summary-icon"><i class="fas fa-sticky-note"></i></span>
            <div><span>{{Équipements}}</span><strong><?php echo count($eqLogics); ?></strong><small>{{dans Jeedom}}</small></div>
        </article>
        <article class="gkeep-health-summary-card">
            <span class="gkeep-health-summary-icon"><i class="fas fa-file-alt"></i></span>
            <div><span>{{Notes}}</span><strong><?php echo (int) $noteCount; ?></strong><small>{{notes simples}}</small></div>
        </article>
        <article class="gkeep-health-summary-card">
            <span class="gkeep-health-summary-icon"><i class="fas fa-list-ul"></i></span>
            <div><span>{{Listes}}</span><strong><?php echo (int) $listCount; ?></strong><small>{{listes synchronisées}}</small></div>
        </article>
        <article class="gkeep-health-summary-card">
            <span class="gkeep-health-summary-icon"><i class="fas fa-thumbtack"></i></span>
            <div><span>{{Épinglés}}</span><strong><?php echo (int) $pinnedCount; ?></strong><small>{{éléments prioritaires}}</small></div>
        </article>
    </section>

    <section class="gkeep-health-panel">
        <div class="gkeep-health-panel-header">
            <i class="fas fa-sticky-note"></i>
            <h3>{{Notes et listes}}</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-condensed table-striped tablesorter" id="table_healthgkeep">
                <thead>
                    <tr>
                        <th>{{Appareil}}</th>
                        <th>{{Ordre}}</th>
                        <th>{{Type}}</th>
                        <th>{{Statut}}</th>
                        <th>{{Couleur}}</th>
                        <th>{{Collaborateurs}}</th>
                        <th>{{Dernière communication}}</th>
                        <th>{{Date création}}</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eqLogics as $eqLogic) {
                        $statuses = array();
                        if ($eqLogic->getConfiguration('pinned', false) === true) {
                            $statuses[] = '<span class="label label-success">{{Épinglé}}</span>';
                        }
                        if ($eqLogic->getConfiguration('archived', false) === true) {
                            $statuses[] = '<span class="label label-warning">{{Archivé}}</span>';
                        }
                        if ($eqLogic->getConfiguration('trashed', false) === true) {
                            $statuses[] = '<span class="label label-danger">{{Supprimé}}</span>';
                        }
                        $colorName = (string) $eqLogic->getConfiguration('color');
                        $collaborators = $eqLogic->getConfiguration('collaborators', array());
                    ?>
                        <tr<?php echo $eqLogic->getIsEnable() ? '' : ' class="gkeep-health-disabled"'; ?>>
                            <td><a href="<?php echo gkeepHealthEscape($eqLogic->getLinkToConfiguration()); ?>"><?php echo $eqLogic->getHumanName(true); ?></a></td>
                            <td><span class="label label-info"><?php echo gkeepHealthEscape($eqLogic->getConfiguration('sort')); ?></span></td>
                            <td><span class="label label-info"><?php echo gkeepHealthEscape($eqLogic->getConfiguration('type')); ?></span></td>
                            <td><?php echo count($statuses) > 0 ? implode(' ', $statuses) : '<span class="label label-default">{{Normal}}</span>'; ?></td>
                            <td><span class="label label-default coloredLabel" data-color="<?php echo gkeepHealthEscape($colorName); ?>" style="background-color:<?php echo gkeepHealthEscape(gkeep::getColor($colorName)); ?> !important;"><?php echo gkeepHealthEscape($colorName); ?></span></td>
                            <td>
                                <?php if (is_array($collaborators) && count($collaborators) > 0) { ?>
                                    <?php foreach ($collaborators as $collaborator) { ?><span class="label label-info gkeep-health-collaborator"><?php echo gkeepHealthEscape($collaborator); ?></span><?php } ?>
                                <?php } else { ?>
                                    <span class="text-muted">{{Aucun}}</span>
                                <?php } ?>
                            </td>
                            <td><?php echo gkeepHealthEscape($eqLogic->getStatus('lastCommunication')); ?></td>
                            <td><?php echo gkeepHealthEscape($eqLogic->getConfiguration('createtime')); ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (count($eqLogics) === 0) { ?>
                        <tr><td colspan="8" class="text-center text-muted">{{Aucune note Google Keep}}</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php include_file('desktop', 'health', 'js', 'gkeep'); ?>