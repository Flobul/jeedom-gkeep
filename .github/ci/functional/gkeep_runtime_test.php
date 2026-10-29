<?php
namespace Cron {
    class FieldFactory {}
    class CronExpression {
        public function __construct($expression, $factory) {}
        public function isDue() { return true; }
    }
}
namespace {
    class eqLogic {
        public static $equipment = array();
        public static $existing = null;
        public static function byType($type) { return self::$equipment; }
        public static function byLogicalId($id, $type) { return self::$existing; }
        public function getIsEnable() { return 1; }
        public function getLogicalId() { return 'note123'; }
        public function getConfiguration($key, $default = null) { return $key === 'account' ? 'user@example.test' : $default; }
        public function refresh() { /* The cron must not call this per equipment. */ }
    }
    class FakeListEquipment extends eqLogic {
        public $commands = array('info' => array(), 'action' => array());
        public function getConfiguration($key, $default = null) { return $key === 'type' ? 'List' : parent::getConfiguration($key, $default); }
        public function getName() { return 'Liste'; }
        public function getId() { return 7; }
        public function setConfiguration($key, $value) {}
        public function setDisplay($key, $value) {}
        public function save() {}
        public function refreshWidget() {}
        public function getCmd($type, $logicalId = null) {
            if ($logicalId !== null) return $this->commands[$type][$logicalId] ?? null;
            return array_values($this->commands[$type] ?? array());
        }
    }
    class FakeEquipment extends eqLogic {
        public function getConfiguration($key, $default = null) {
            $values = array('account' => 'user" onmouseover="alert(1)', 'pinned' => false, 'type' => 'Text', 'color' => 'DEFAULT');
            return $values[$key] ?? $default;
        }
        public function getId() { return 1; }
        public function getHumanName($a = false, $b = false) { return '</span><img src=x onerror=alert(1)>'; }
        public function getImage() { return array('img' => 'plugins/gkeep/plugin_info/gkeep_icon.png'); }
        public function getIsVisible() { return 1; }
    }
    class cmd {
        public $logicalId;
        public $type;
        public $value;
        public function getLogicalId() { return $this->logicalId; }
        public function getType() { return $this->type; }
        public function getEqlogic() { return eqLogic::$existing; }
        public function execCmd() { return $this->value; }
        public function toHtml($version = 'dashboard', $options = '') { return 'display_value: #gkeepInitialValue#'; }
        public function event($value) { $this->value = $value; }
        public function getName() { return $this->logicalId; }
        public function getConfiguration($key, $default = null) { return $default; }
        public function setLogicalId($id) { $this->logicalId = $id; return $this; }
        public function setType($type) { $this->type = $type; return $this; }
        public function __call($name, $arguments) { return $this; }
        public function save() {
            eqLogic::$existing->commands[$this->type][$this->logicalId] = $this;
            return $this;
        }
        public function remove() { unset(eqLogic::$existing->commands[$this->type][$this->logicalId]); }
    }
    class system { public static function getCmdSudo() { return ''; } }
    class log { public static function add($class, $level, $message) {} }
    class config {
        public static function byKey($key, $plugin, $default = null) {
            if ($key === 'email') return array(1 => 'user@example.test');
            if ($key === 'token') return array(1 => 'fixture-token');
            if ($key === 'max_account_number') return 1;
            if ($key === 'autorefresh') return '* * * * *';
            return $default;
        }
    }
    function __($text, $file) { return $text; }
    function cleanComponanteName($name) { return $name; }
    $root = getenv('GKEEP_TEST_ROOT');
    if (!is_string($root) || $root === '' || !is_dir($root)) throw new \RuntimeException('GKEEP_TEST_ROOT required');
    $classFile = $root . '/core/class/gkeep.class.php';
    $src = file_get_contents(__DIR__ . '/../../../core/class/gkeep.class.php');
    $src = preg_replace('/^require_once .*core\.inc\.php.*;$/m', '', $src, 1, $count);
    if ($count !== 1) throw new \RuntimeException('core include marker changed');
    if (!is_dir(dirname($classFile))) mkdir(dirname($classFile), 0700, true);
    file_put_contents($classFile, $src);
    $cli = $root . '/resources/venv/bin/python3';
    if (!is_dir(dirname($cli))) mkdir(dirname($cli), 0700, true);
    file_put_contents($cli, <<<'SH'
#!/bin/sh
printf '%s\n' "$*" >> "$GKEEP_TEST_ROOT/argv.txt"
cat > "$GKEEP_TEST_ROOT/stdin.txt"
printf '%s\n' '{"code":0,"result":[]}'
SH
    );
    chmod($cli, 0700);
    require $classFile;
    $result = gkeep::sendCmdAndFormatResult(array('--username', 'user@example.test', '--token', 'fixture-token', 'get_notes', '--note_id', 'private note text'));
    if ($result['code'] !== 0) throw new \RuntimeException('CLI response lost');
    $argv = file_get_contents($root . '/argv.txt');
    $stdin = json_decode(file_get_contents($root . '/stdin.txt'), true);
    if (strpos($argv, 'fixture-token') !== false || strpos($argv, 'private note text') !== false || strpos($argv, '--request-stdin') === false || ($stdin['args'][3] ?? null) !== 'fixture-token') {
        throw new \RuntimeException('Private data exposed in process arguments or missing from stdin');
    }
    file_put_contents($root . '/argv.txt', '');
    gkeep::sendCmdAndFormatResult(array('--username', 'user@example.test', 'get_master_token', '--password', 'fixture-password'));
    if (strpos(file_get_contents($root . '/argv.txt'), 'fixture-password') !== false || strpos(file_get_contents($root . '/stdin.txt'), 'fixture-password') === false) {
        throw new \RuntimeException('Password leaked into process arguments or not transmitted');
    }
    file_put_contents($root . '/argv.txt', '');
    eqLogic::$equipment = array(new eqLogic(), new eqLogic(), new eqLogic());
    gkeep::update();
    $calls = array_filter(explode("\n", trim(file_get_contents($root . '/argv.txt'))));
    if (count($calls) !== 1) throw new \RuntimeException('Cron must fetch once per account, got ' . count($calls));
    $cronRequest = json_decode(file_get_contents($root . '/stdin.txt'), true);
    if (!in_array('--blob-note-ids', $cronRequest['args'] ?? array(), true)) {
        throw new \RuntimeException('Cron must restrict downloaded attachments to enabled equipment');
    }
    $display = file_get_contents(__DIR__ . '/../../../core/class/gkeep.display.php');
    $display = preg_replace('/^require_once .*gkeep\.class\.php.*;$/m', '', $display, 1, $count);
    if ($count !== 1) throw new \RuntimeException('Display include marker changed');
    file_put_contents($root . '/core/class/gkeep.display.php', $display);
    require $root . '/core/class/gkeep.display.php';
    ob_start();
    gkeep_display::displayEqLogicThumbnailContainer(array(new FakeEquipment()), 'unpinned');
    $html = ob_get_clean();
    if (strpos($html, '<img src=x onerror=alert(1)>') !== false || strpos($html, 'data-account="user" onmouseover=') !== false || strpos($html, '&lt;img') === false) {
        throw new \RuntimeException('Untrusted equipment name or account rendered as HTML');
    }
    eqLogic::$existing = new FakeListEquipment();
    $old = new cmd();
    $old->setLogicalId('removed-item')->setType('info')->save();
    gkeep::checkAndCreateEquipementAndCmd(array('id' => 'note123', 'title' => 'Liste', 'type' => 'List', 'list' => array(), 'text' => ''), 'user@example.test');
    if (eqLogic::$existing->getCmd('info', 'removed-item') !== null || !is_object(eqLogic::$existing->getCmd('action', 'addItem')) || is_object(eqLogic::$existing->getCmd('info', 'text'))) {
        throw new \RuntimeException('An emptied list must remove stale items, keep add-item and not become a text note');
    }
    $untrusted = "Acheter </script><img src=x onerror=alert(1)> et 'pain'\nSéries";
    gkeep::checkAndCreateEquipementAndCmd(array('id' => 'note123', 'title' => 'Liste', 'type' => 'List', 'list' => array(array('id' => 'cbx.1', 'text' => $untrusted, 'checked' => false, 'sort' => 0))), 'user@example.test');
    $item = eqLogic::$existing->getCmd('info', 'cbx.1');
    if ($item->execCmd() !== $untrusted) throw new \RuntimeException('List item command value must remain raw for scenarios and API');
    $rendered = $item->toHtml('dashboard');
    $literal = substr($rendered, strlen('display_value: '));
    if (strpos($literal, '</script>') !== false || strpos($literal, '<img') !== false || json_decode($literal) !== $untrusted) {
        throw new \RuntimeException('Widget initial state must be a safe JavaScript string literal');
    }
    echo "gkeep secure CLI and batched cron: OK\n";
}
