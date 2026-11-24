<?php
/**
 * adfmedia CMS installer
 *
 * Runs before Laravel boots (there is no .env yet). Each step is a plain
 * form; the real work is done by the classes in app/Install and, at the
 * end, by `php artisan cms:install:finish` run in this request.
 *
 * It only works on a site with no .env and no storage/installed.lock, so it
 * can't be used against a site that is already set up. Delete this folder
 * (public/install) once the site is installed.
 */

declare(strict_types=1);

use App\Install\DatabaseCreator;
use App\Install\EnvWriter;
use App\Install\InstallState;
use App\Install\Requirements;

$base = dirname(__DIR__, 2);

if (!is_file($base . '/vendor/autoload.php')) {
    http_response_code(500);
    exit('The vendor/ folder is missing. Upload a release package that includes it, or run composer install.');
}
require $base . '/vendor/autoload.php';

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');

session_name('cms_install');
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'cookie_secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);

$State = new InstallState($base);
$S = &$_SESSION['install'];
$S = is_array($S) ? $S : ['step' => 'requirements'];
$S['csrf'] ??= bin2hex(random_bytes(16));

// An install this session started may be finished after .env was written
$resuming = !empty($S['env_written']) && $State->hasEnv() && !$State->isInstalled();

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function go(string $step): never
{
    $_SESSION['install']['step'] = $step;
    header('Location: ./?step=' . urlencode($step), true, 303);
    exit;
}

/**
 * Optional modules on disk: not core, not dev-only
 *
 * @return array{core: array<string,string>, optional: array<string,string>}
 */
function modules(string $base): array
{
    $core = $optional = [];
    foreach (glob($base . '/modules/*/module.json') ?: [] as $file) {
        $manifest = json_decode((string) file_get_contents($file), true);
        if (!is_array($manifest) || empty($manifest['name'])) {
            continue;
        }
        if (!empty($manifest['dev_only'])) {
            continue;
        }
        $description = (string) ($manifest['description'] ?? '');
        !empty($manifest['core']) ? $core[$manifest['name']] = $description : $optional[$manifest['name']] = $description;
    }
    ksort($core);
    ksort($optional);

    return ['core' => $core, 'optional' => $optional];
}

$steps = [
    'requirements' => 'Server',
    'site'         => 'Site',
    'database'     => 'Database',
    'admin'        => 'Admin',
    'modules'      => 'Modules',
    'install'      => 'Install',
];

$errors = [];
$notices = [];

/* ------------------------------------------------------------------ */
/* Locked: already installed, or a .env this session didn't write      */
/* ------------------------------------------------------------------ */

if ($State->isInstalled() && ($S['step'] ?? '') !== 'done') {
    $view = 'locked';
    $locked_reason = 'This site is already installed.';
} elseif (!$State->wizardAllowed() && !$resuming && ($S['step'] ?? '') !== 'done') {
    $view = 'locked';
    $locked_reason = 'This site already has a .env file, so the installer won\'t run. To install from scratch, remove .env first.';
} else {
    $view = $S['step'] ?? 'requirements';
    if (isset($_GET['step']) && array_key_exists($_GET['step'], $steps) && !empty($S['reached'][$_GET['step']])) {
        $view = $_GET['step'];
    }
    if ($resuming && $view !== 'done') {
        $view = 'install';
    }
}

/* ------------------------------------------------------------------ */
/* Form posts                                                          */
/* ------------------------------------------------------------------ */

if ($view !== 'locked' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($S['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $action = post('action');

        if ($action === 'requirements') {
            if ((new Requirements($base))->passes()) {
                $S['reached']['site'] = true;
                go('site');
            }
            $errors[] = 'Fix the items marked in red first.';
        }

        if ($action === 'site') {
            $S['site'] = [
                'name'      => post('site_name'),
                'url'       => rtrim(post('url'), '/'),
                'env'       => in_array(post('env'), ['production', 'staging', 'local'], true) ? post('env') : 'production',
                'timezone'  => post('timezone', 'Europe/London'),
                'mail_from' => post('mail_from'),
            ];
            if ($S['site']['name'] === '') {
                $errors[] = 'Give the site a name.';
            }
            if (!filter_var($S['site']['url'], FILTER_VALIDATE_URL) || !preg_match('#^https?://#', $S['site']['url'])) {
                $errors[] = 'Enter the site URL, starting with http:// or https://';
            }
            if (!in_array($S['site']['timezone'], timezone_identifiers_list(), true)) {
                $errors[] = 'Choose a valid timezone, e.g. Europe/London.';
            }
            if ($S['site']['mail_from'] !== '' && !filter_var($S['site']['mail_from'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'The "emails sent from" address isn\'t valid.';
            }
            if (!$errors) {
                $S['db'] ??= DatabaseCreator::suggest($S['site']['name']) + ['host' => '127.0.0.1', 'port' => 3306, 'mode' => 'create', 'root_user' => 'root'];
                $S['reached']['database'] = true;
                go('database');
            }
        }

        if ($action === 'database') {
            $db = [
                'mode'      => post('mode') === 'existing' ? 'existing' : 'create',
                'host'      => post('host', '127.0.0.1'),
                'port'      => (int) post('port', '3306'),
                'database'  => post('database'),
                'username'  => post('username'),
                'password'  => (string) ($_POST['password'] ?? ''),
                'root_user' => post('root_user'),
            ];
            $S['db'] = $db;

            try {
                $Creator = new DatabaseCreator();
                if ($db['mode'] === 'create') {
                    $notices = $Creator->create(
                        $db['host'], $db['port'],
                        $db['root_user'], (string) ($_POST['root_password'] ?? ''),
                        $db['database'], $db['username'], $db['password']
                    );
                } else {
                    $Creator->checkExisting($db['host'], $db['port'], $db['username'], $db['password'], $db['database']);
                    $notices = ['Connected to ' . $db['database'] . ' as ' . $db['username']];
                }
                $S['db_ready'] = true;
                $S['db_notices'] = $notices;
                $S['reached']['admin'] = true;
                go('admin');
            } catch (Throwable $e) {
                $S['db_ready'] = false;
                $errors[] = $e->getMessage();
            }
        }

        if ($action === 'admin') {
            $S['admin'] = ['name' => post('admin_name'), 'email' => post('admin_email')];
            $password = (string) ($_POST['admin_password'] ?? '');
            if ($S['admin']['name'] === '') {
                $errors[] = 'Enter the admin\'s name.';
            }
            if (!filter_var($S['admin']['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Enter a valid email for the admin.';
            }
            if (strlen($password) < 12) {
                $errors[] = 'Use an admin password of at least 12 characters.';
            } elseif ($password !== (string) ($_POST['admin_password_confirm'] ?? '')) {
                $errors[] = 'The two passwords don\'t match.';
            }
            if (!$errors) {
                $S['admin']['password'] = $password;
                $S['reached']['modules'] = true;
                go('modules');
            }
        }

        if ($action === 'modules') {
            $available = array_keys(modules($base)['optional']);
            $S['modules'] = array_values(array_intersect((array) ($_POST['modules'] ?? []), $available));
            $S['reached']['install'] = true;
            go('install');
        }

        if ($action === 'install') {
            if (empty($S['db_ready']) || empty($S['admin']['password']) || empty($S['site'])) {
                $errors[] = 'Some steps aren\'t finished yet.';
            } else {
                @set_time_limit(600);

                // 1. .env (unless an earlier attempt in this session wrote it)
                if (!$resuming) {
                    try {
                        (new EnvWriter($base))->write([
                            'APP_NAME'          => $S['site']['name'],
                            'APP_ENV'           => $S['site']['env'],
                            'APP_KEY'           => EnvWriter::generateAppKey(),
                            'APP_DEBUG'         => $S['site']['env'] !== 'production',
                            'APP_URL'           => $S['site']['url'],
                            'APP_TIMEZONE'      => $S['site']['timezone'],
                            'DB_HOST'           => $S['db']['host'],
                            'DB_PORT'           => $S['db']['port'],
                            'DB_DATABASE'       => $S['db']['database'],
                            'DB_USERNAME'       => $S['db']['username'],
                            'DB_PASSWORD'       => $S['db']['password'],
                            'MAIL_FROM_ADDRESS' => $S['site']['mail_from'] ?: $S['admin']['email'],
                            'LOG_LEVEL'         => $S['site']['env'] === 'production' ? 'error' : 'debug',
                        ]);
                        $S['env_written'] = true;
                    } catch (Throwable $e) {
                        $errors[] = $e->getMessage();
                    }
                }

                // 2. Everything else, by Laravel now .env exists
                if (!$errors) {
                    try {
                        $app = require $base . '/bootstrap/app.php';
                        $Kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
                        $Kernel->bootstrap();
                        $exit = $Kernel->call('cms:install:finish', [
                            '--admin-name'     => $S['admin']['name'],
                            '--admin-email'    => $S['admin']['email'],
                            '--admin-password' => $S['admin']['password'],
                            '--modules'        => implode(',', $S['modules'] ?? []),
                            '--no-interaction' => true,
                        ]);
                        $output = trim($Kernel->output());

                        if ($exit === 0) {
                            $S = [
                                'step'   => 'done',
                                'csrf'   => $S['csrf'],
                                'url'    => $S['site']['url'],
                                'output' => $output,
                            ];
                            go('done');
                        }
                        $errors[] = 'The install didn\'t finish: ' . $output;
                    } catch (Throwable $e) {
                        $errors[] = 'The install didn\'t finish: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

$checks = $view === 'requirements' ? (new Requirements($base))->checks() : [];
$module_list = in_array($view, ['modules', 'install'], true) ? modules($base) : ['core' => [], 'optional' => []];
$logo = @file_get_contents($base . '/resources/adm/images/adfmedia-logo.svg') ?: '';
$logo = preg_replace('/<\?xml.*?\?>|<!--.*?-->/s', '', $logo);
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$guess_url = $scheme . '://' . preg_replace('/[^A-Za-z0-9.\-:]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Install | adfmedia CMS</title>
    <style>
        :root { --ink:#0a0a0a; --surface:#141414; --line:#262626; --paper:#f7f5f2; --muted:#a3a3a3; --red:#9c1006; --red-light:#e0483c; --ok:#3fb950; }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; background:var(--ink); color:var(--paper); font:15px/1.55 Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; position:relative; overflow-x:hidden; }
        body::before { content:""; position:fixed; top:-25%; left:-25%; width:900px; height:900px; border-radius:50%; background:rgba(156,16,6,.15); filter:blur(160px); pointer-events:none; }
        body::after { content:""; position:fixed; inset:0; background-image:radial-gradient(rgba(255,255,255,.06) 1px, transparent 1px); background-size:22px 22px; opacity:.3; pointer-events:none; }
        .wrap { position:relative; z-index:1; max-width:720px; margin:0 auto; padding:48px 20px 64px; }
        .brand { display:flex; align-items:center; gap:12px; margin-bottom:28px; font-size:22px; letter-spacing:-.01em; }
        .brand svg { width:40px; height:40px; }
        .brand b { font-weight:600; } .brand span { color:var(--red-light); }
        ol.steps { display:flex; flex-wrap:wrap; gap:6px; list-style:none; padding:0; margin:0 0 20px; font-size:13px; color:var(--muted); }
        ol.steps li { padding:4px 10px; border:1px solid var(--line); border-radius:999px; }
        ol.steps li.on { border-color:var(--red); color:var(--paper); background:rgba(156,16,6,.2); }
        ol.steps li.done { color:var(--paper); }
        ol.steps a { color:inherit; text-decoration:none; }
        .card { background:var(--surface); border:1px solid var(--line); border-radius:12px; padding:28px; }
        h1 { font-size:22px; margin:0 0 6px; } p.lead { margin:0 0 20px; color:var(--muted); }
        label { display:block; font-weight:600; font-size:14px; margin:16px 0 6px; }
        .help { font-size:13px; color:var(--muted); margin-top:4px; }
        input[type=text], input[type=email], input[type=url], input[type=password], input[type=number], select { width:100%; padding:10px 12px; border-radius:8px; border:1px solid var(--line); background:var(--ink); color:var(--paper); font:inherit; }
        input:focus, select:focus { outline:2px solid var(--red); outline-offset:1px; }
        .row { display:grid; grid-template-columns:1fr 1fr; gap:0 16px; } .row.r3 { grid-template-columns:2fr 1fr; }
        .btns { display:flex; justify-content:space-between; align-items:center; margin-top:28px; gap:12px; }
        button, .btn { display:inline-block; padding:11px 20px; border-radius:8px; border:0; background:var(--red); color:#fff; font:inherit; font-weight:600; cursor:pointer; text-decoration:none; }
        button:hover, .btn:hover { background:#b5150a; }
        .back { color:var(--muted); text-decoration:none; font-size:14px; }
        .msg { border-radius:8px; padding:12px 14px; margin-bottom:16px; font-size:14px; }
        .msg.err { background:rgba(224,72,60,.12); border:1px solid rgba(224,72,60,.5); }
        .msg.ok { background:rgba(63,185,80,.1); border:1px solid rgba(63,185,80,.45); }
        ul.checks { list-style:none; padding:0; margin:0; } ul.checks li { display:flex; justify-content:space-between; gap:12px; padding:8px 0; border-bottom:1px solid var(--line); font-size:14px; }
        ul.checks .ok { color:var(--ok); } ul.checks .bad { color:var(--red-light); font-weight:600; }
        ul.checks small { color:var(--muted); display:block; }
        .choice { display:flex; gap:10px; align-items:flex-start; padding:10px 12px; border:1px solid var(--line); border-radius:8px; margin-top:8px; font-weight:400; }
        .choice input { margin-top:4px; } .choice small { display:block; color:var(--muted); }
        .pill { display:inline-block; font-size:12px; padding:2px 8px; border-radius:999px; border:1px solid var(--line); margin:2px 4px 2px 0; }
        dl { display:grid; grid-template-columns:160px 1fr; gap:6px 12px; font-size:14px; margin:0; } dt { color:var(--muted); } dd { margin:0; word-break:break-all; }
        pre { white-space:pre-wrap; background:var(--ink); border:1px solid var(--line); border-radius:8px; padding:12px; font-size:13px; }
        [data-mode] { display:none; } .mode-create [data-mode=create], .mode-existing [data-mode=existing] { display:block; }
        @media (max-width:560px){ .row, .row.r3 { grid-template-columns:1fr; } dl { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand"><?= $logo ?><div><b>adf</b><span>media</span> CMS installer</div></div>

    <?php if (!in_array($view, ['locked', 'done'], true)): ?>
        <ol class="steps">
            <?php foreach ($steps as $key => $label): ?>
                <li class="<?= $key === $view ? 'on' : (!empty($S['reached'][$key]) || $key === 'requirements' ? 'done' : '') ?>">
                    <?php if ((!empty($S['reached'][$key]) || $key === 'requirements') && $key !== $view && !$resuming): ?>
                        <a href="./?step=<?= h($key) ?>"><?= h($label) ?></a>
                    <?php else: ?><?= h($label) ?><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <div class="card">
        <?php foreach ($errors as $error): ?><div class="msg err"><?= h($error) ?></div><?php endforeach; ?>

        <?php if ($view === 'locked'): ?>
            <h1>The installer is locked</h1>
            <p class="lead"><?= h($locked_reason) ?></p>
            <p>If this site is set up, delete the <code>public/install</code> folder from the server.</p>
            <div class="btns"><span></span><a class="btn" href="/adm">Go to the CMS</a></div>

        <?php elseif ($view === 'requirements'): ?>
            <h1>Check the server</h1>
            <p class="lead">The CMS needs these on this server before it can be installed.</p>
            <ul class="checks">
                <?php foreach ($checks as $check): ?>
                    <li>
                        <span><?= h($check['label']) ?><small><?= h($check['detail']) ?></small></span>
                        <span class="<?= $check['ok'] ? 'ok' : 'bad' ?>"><?= $check['ok'] ? 'OK' : 'Missing' ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= h($S['csrf']) ?>">
                <input type="hidden" name="action" value="requirements">
                <div class="btns"><a class="back" href="./">Check again</a><button type="submit">Continue</button></div>
            </form>

        <?php elseif ($view === 'site'): $site = $S['site'] ?? []; ?>
            <h1>About the site</h1>
            <p class="lead">These go into the site's settings file (.env). You can change them later.</p>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= h($S['csrf']) ?>">
                <input type="hidden" name="action" value="site">
                <label for="site_name">Site name</label>
                <input type="text" id="site_name" name="site_name" required value="<?= h($site['name'] ?? '') ?>" placeholder="e.g. Acme Ltd">
                <label for="url">Site URL</label>
                <input type="url" id="url" name="url" required value="<?= h($site['url'] ?? $guess_url) ?>">
                <div class="row">
                    <div>
                        <label for="env">Environment</label>
                        <select id="env" name="env">
                            <?php foreach (['production' => 'Live (production)', 'staging' => 'Staging', 'local' => 'Local development'] as $value => $label): ?>
                                <option value="<?= h($value) ?>" <?= ($site['env'] ?? 'production') === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="help">Live hides error details from visitors.</div>
                    </div>
                    <div>
                        <label for="timezone">Timezone</label>
                        <input type="text" id="timezone" name="timezone" value="<?= h($site['timezone'] ?? 'Europe/London') ?>">
                    </div>
                </div>
                <label for="mail_from">Emails sent from</label>
                <input type="email" id="mail_from" name="mail_from" value="<?= h($site['mail_from'] ?? '') ?>" placeholder="Leave empty to use the admin's email">
                <div class="btns"><a class="back" href="./?step=requirements">Back</a><button type="submit">Continue</button></div>
            </form>

        <?php elseif ($view === 'database'): $db = $S['db'] ?? []; ?>
            <h1>Database</h1>
            <p class="lead">The installer can create the database and a user that can only reach that database. Check the suggested names and password, or set your own.</p>
            <form method="post" class="mode-<?= h($db['mode'] ?? 'create') ?>" id="dbform">
                <input type="hidden" name="csrf" value="<?= h($S['csrf']) ?>">
                <input type="hidden" name="action" value="database">
                <label class="choice"><input type="radio" name="mode" value="create" <?= ($db['mode'] ?? 'create') === 'create' ? 'checked' : '' ?>>
                    <span>Create a new database and user<small>Needs a MySQL login allowed to create them, such as root or your Plesk database admin. It's used once and never saved.</small></span></label>
                <label class="choice"><input type="radio" name="mode" value="existing" <?= ($db['mode'] ?? '') === 'existing' ? 'checked' : '' ?>>
                    <span>Use a database I've already made<small>For example one created in Plesk. It must be empty.</small></span></label>

                <div class="row r3">
                    <div><label for="host">MySQL host</label><input type="text" id="host" name="host" value="<?= h($db['host'] ?? '127.0.0.1') ?>"></div>
                    <div><label for="port">Port</label><input type="number" id="port" name="port" value="<?= h($db['port'] ?? 3306) ?>"></div>
                </div>

                <div data-mode="create">
                    <div class="row">
                        <div><label for="root_user">Admin login</label><input type="text" id="root_user" name="root_user" value="<?= h($db['root_user'] ?? 'root') ?>" autocomplete="off"></div>
                        <div><label for="root_password">Its password</label><input type="password" id="root_password" name="root_password" autocomplete="off"><div class="help">Leave empty if it has none (Laragon's root).</div></div>
                    </div>
                </div>

                <div class="row">
                    <div><label for="database">Database name</label><input type="text" id="database" name="database" required value="<?= h($db['database'] ?? '') ?>"></div>
                    <div><label for="username">Database user</label><input type="text" id="username" name="username" required value="<?= h($db['username'] ?? '') ?>"></div>
                </div>
                <label for="password">Database user's password</label>
                <input type="text" id="password" name="password" required value="<?= h($db['password'] ?? '') ?>" autocomplete="off" spellcheck="false">
                <div class="help">Suggested at random. Keep a copy if you'll need it; it's saved in .env.</div>

                <div class="btns"><a class="back" href="./?step=site">Back</a><button type="submit">Set up the database</button></div>
            </form>
            <script>
                document.querySelectorAll('#dbform input[name=mode]').forEach(function (radio) {
                    radio.addEventListener('change', function () { document.getElementById('dbform').className = 'mode-' + radio.value; });
                });
            </script>

        <?php elseif ($view === 'admin'): $admin = $S['admin'] ?? []; ?>
            <?php foreach ($S['db_notices'] ?? [] as $notice): ?><div class="msg ok"><?= h($notice) ?></div><?php endforeach; ?>
            <h1>First admin</h1>
            <p class="lead">This person can sign in to the CMS and manage everything, including modules.</p>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= h($S['csrf']) ?>">
                <input type="hidden" name="action" value="admin">
                <div class="row">
                    <div><label for="admin_name">Name</label><input type="text" id="admin_name" name="admin_name" required value="<?= h($admin['name'] ?? '') ?>"></div>
                    <div><label for="admin_email">Email</label><input type="email" id="admin_email" name="admin_email" required value="<?= h($admin['email'] ?? '') ?>"></div>
                </div>
                <div class="row">
                    <div><label for="admin_password">Password</label><input type="password" id="admin_password" name="admin_password" required minlength="12" autocomplete="new-password"><div class="help">At least 12 characters.</div></div>
                    <div><label for="admin_password_confirm">Password again</label><input type="password" id="admin_password_confirm" name="admin_password_confirm" required minlength="12" autocomplete="new-password"></div>
                </div>
                <div class="btns"><a class="back" href="./?step=database">Back</a><button type="submit">Continue</button></div>
            </form>

        <?php elseif ($view === 'modules'): $chosen = $S['modules'] ?? []; ?>
            <h1>Modules</h1>
            <p class="lead">Core modules are always installed. Tick any extra features this site needs. You can add more later in Settings &gt; Modules.</p>
            <p><?php foreach ($module_list['core'] as $name => $description): ?><span class="pill"><?= h($name) ?></span><?php endforeach; ?></p>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= h($S['csrf']) ?>">
                <input type="hidden" name="action" value="modules">
                <?php foreach ($module_list['optional'] as $name => $description): ?>
                    <label class="choice"><input type="checkbox" name="modules[]" value="<?= h($name) ?>" <?= in_array($name, $chosen, true) ? 'checked' : '' ?>>
                        <span><?= h($name) ?><small><?= h($description) ?></small></span></label>
                <?php endforeach; ?>
                <div class="btns"><a class="back" href="./?step=admin">Back</a><button type="submit">Continue</button></div>
            </form>

        <?php elseif ($view === 'install'): ?>
            <h1><?= $resuming ? 'Finish installing' : 'Ready to install' ?></h1>
            <?php if ($resuming): ?>
                <p class="lead">The settings file was written but setting up the site didn't finish. Fix the problem shown, then try again.</p>
            <?php else: ?>
                <p class="lead">Check the details, then install. This takes up to a minute.</p>
            <?php endif; ?>
            <dl>
                <dt>Site</dt><dd><?= h($S['site']['name'] ?? '') ?> (<?= h($S['site']['url'] ?? '') ?>)</dd>
                <dt>Environment</dt><dd><?= h($S['site']['env'] ?? '') ?></dd>
                <dt>Database</dt><dd><?= h($S['db']['database'] ?? '') ?> on <?= h(($S['db']['host'] ?? '') . ':' . ($S['db']['port'] ?? '')) ?>, user <?= h($S['db']['username'] ?? '') ?></dd>
                <dt>Admin</dt><dd><?= h($S['admin']['name'] ?? '') ?> (<?= h($S['admin']['email'] ?? '') ?>)</dd>
                <dt>Modules</dt><dd><?= h(implode(', ', array_merge(array_keys($module_list['core']), $S['modules'] ?? []))) ?></dd>
            </dl>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= h($S['csrf']) ?>">
                <input type="hidden" name="action" value="install">
                <div class="btns"><?php if (!$resuming): ?><a class="back" href="./?step=modules">Back</a><?php else: ?><span></span><?php endif; ?><button type="submit"><?= $resuming ? 'Try again' : 'Install' ?></button></div>
            </form>

        <?php elseif ($view === 'done'): ?>
            <h1>Installed</h1>
            <p class="lead">The site is ready. Sign in with the admin email and password you chose.</p>
            <?php if (!empty($S['output'])): ?><pre><?= h($S['output']) ?></pre><?php endif; ?>
            <div class="msg err"><strong>Now delete the <code>public/install</code> folder from the server.</strong> The installer is locked, but it shouldn't stay online.</div>
            <div class="btns"><span></span><a class="btn" href="<?= h(($S['url'] ?? '') . '/adm') ?>">Sign in to the CMS</a></div>
            <?php $_SESSION['install'] = ['step' => 'done', 'csrf' => $S['csrf'], 'url' => $S['url'] ?? '']; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
