<?php
session_start();
include("db/config.php");

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
    exit;
}

$name = $_SESSION['login_user'];
$configFile = __DIR__ . '/config/whatsapp-gateway.php';
$defaultSettings = [
    'base_url' => '',
    'api_key' => '',
    'secret_key' => '',
    'enabled' => 0,
    'updated_at' => null,
];

$settings = $defaultSettings;
if (file_exists($configFile)) {
    $loadedSettings = include $configFile;
    if (is_array($loadedSettings)) {
        $settings = array_merge($defaultSettings, $loadedSettings);
    }
}

function isValidHttpUrl($url)
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $scheme = parse_url($url, PHP_URL_SCHEME);
    return in_array(strtolower((string) $scheme), ['http', 'https'], true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_whatsapp_gateway_settings'])) {
    $baseUrl = trim($_POST['base_url'] ?? '');
    $apiKey = trim($_POST['api_key'] ?? '');
    $secretKey = trim($_POST['secret_key'] ?? '');
    $enabled = isset($_POST['enabled']) ? 1 : 0;

    $errors = [];

    if ($apiKey === '') {
        $errors[] = 'API key is required.';
    }

    if ($secretKey === '') {
        $errors[] = 'Secret key is required.';
    }

    if (!isValidHttpUrl($baseUrl)) {
        $errors[] = 'Please enter a valid Base URL (http:// or https://).';
    }

    if (empty($errors)) {
        $settings = [
            'base_url' => $baseUrl,
            'api_key' => $apiKey,
            'secret_key' => $secretKey,
            'enabled' => $enabled,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $configContent = "<?php\nreturn " . var_export($settings, true) . ";\n";

        if (file_put_contents($configFile, $configContent, LOCK_EX) !== false) {
            $_SESSION['success'] = 'WhatsApp gateway settings saved successfully.';
            header('Location: whatsapp-gateway-settings.php');
            exit;
        }

        $_SESSION['error'] = 'Unable to save settings. Please check file permissions.';
        header('Location: whatsapp-gateway-settings.php');
        exit;
    }

    $_SESSION['error'] = implode(' ', $errors);
    header('Location: whatsapp-gateway-settings.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Admin Settings </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Include stylesheet -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" /> -->

    <!-- Include the Quill library -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script> -->



    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" />

    <style>
        .nav-pills .nav-link.active {
            background-color: #04a9f5;
            color: white;
            border-radius: 4px;
        }

        .nav-pills .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 5px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border-radius: 4px;
        }

        .nav-pills .nav-link:hover {
            background-color: #f0f0f0;
        }

        .nav-pills .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {

            .col-md-3,
            .col-md-9 {
                width: 100%;
                margin-bottom: 20px;
            }
        }

        .swal2-container {
            z-index: 20000 !important;
        }
    </style>
</head>

<body class="">
    <?php if (isset($_SESSION['success'])) { ?>
        <script>
            const notyf = new Notyf({
                position: {
                    x: 'center',
                    y: 'top'
                },
                types: [
                    {
                        type: 'success',
                        background: '#26c975', // Change background color
                        textColor: '#FFFFFF',  // Change text color
                        dismissible: true,
                        duration: 10000
                    }
                ]
            });
            notyf.success("<?php echo $_SESSION['success']; ?>");
        </script>
        <?php
        unset($_SESSION['success']);
        ?>
    <?php } ?>

    <?php if (isset($_SESSION['error'])) { ?>
        <script>
            const notyf = new Notyf({
                position: {
                    x: 'center',
                    y: 'top'
                },
                types: [
                    {
                        type: 'error',
                        background: '#ff1916',
                        textColor: '#FFFFFF',
                        dismissible: true,
                        duration: 10000
                    }
                ]
            });
            notyf.error("<?php echo $_SESSION['error']; ?>");
        </script>
        <?php
        unset($_SESSION['error']);
        ?>
    <?php } ?>


    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>


    <?php include 'navbar.php'; ?>

    <header class="navbar pcoded-header navbar-expand-lg navbar-light headerpos-fixed header-blue">
        <div class="m-header">
            <a class="mobile-menu" id="mobile-collapse" href="javascript:void(0);"><span></span></a>
            <a href="javascript:void(0);" class="b-brand" style="font-size:24px;">
                ADMIN PANEL

            </a>
            <a href="javascript:void(0);" class="mob-toggler">
                <i class="feather icon-more-vertical"></i>
            </a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">

                    <div class="search-bar">

                        <button type="button" class="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0);" class="full-screen" onClick="javascript:toggleFullScreen()"><i
                            class="feather icon-maximize"></i></a>
                </li>
            </ul>


        </div>
        </div>
        </li>

        <div class="dropdown drp-user">
            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
                <img src="assets/images/user.png" class="img-radius wid-40" alt="User-Profile-Image">
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-notification">
                <div class="pro-head">
                    <img src="assets/images/user.png" class="img-radius" alt="User-Profile-Image">
                    <span><?php echo $name ?></span>
                    <a href="logout.php" class="dud-logout" title="Logout">
                        <i class="feather icon-log-out"></i>
                    </a>
                </div>
                <ul class="pro-body">
                    <li><a href="logout.php" class="dropdown-item"><i class="feather icon-lock"></i> Log out</a></li>
                </ul>
            </div>
        </div>
        </li>
        </ul>
        </div>
    </header>

<section class="pcoded-main-container">
        <div class="pcoded-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h5 class="m-b-10">Server Setting</h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="configuration.php">Settings</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="gateway-page">
                <div class="card gateway-card">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="mb-2">WhatsApp Gateway Integration</h3>
                        <p class="text-muted mb-4">Configure your HMAC-secured gateway credentials for sending WhatsApp
                            notifications.</p>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <div id="testResult" class="alert d-none" role="alert"></div>

                        <form method="POST" id="gatewaySettingsForm" novalidate>
                            <input type="hidden" name="save_whatsapp_gateway_settings" value="1">

                            <div class="mb-3">
                                <label for="baseUrl" class="form-label">API Base URL</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                    <input type="url" class="form-control" id="baseUrl" name="base_url"
                                        placeholder="https://api.your-gateway.com"
                                        value="<?php echo htmlspecialchars((string) $settings['base_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                        required>
                                </div>
                                <small class="text-muted">Enter the root URL of your WhatsApp gateway API
                                    endpoint.</small>
                            </div>

                            <div class="mb-3">
                                <label for="apiKey" class="form-label">API Key</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                    <input type="text" class="form-control" id="apiKey" name="api_key"
                                        placeholder="Enter API key"
                                        value="<?php echo htmlspecialchars((string) $settings['api_key'], ENT_QUOTES, 'UTF-8'); ?>"
                                        required>
                                </div>
                                <small class="text-muted">Used as your public identifier while requesting gateway
                                    APIs.</small>
                            </div>

                            <div class="mb-3">
                                <label for="secretKey" class="form-label">Secret Key</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password" class="form-control" id="secretKey" name="secret_key"
                                        placeholder="Enter secret key"
                                        value="<?php echo htmlspecialchars((string) $settings['secret_key'], ENT_QUOTES, 'UTF-8'); ?>"
                                        required>
                                </div>
                                <small class="text-muted">Used to generate HMAC signatures for secure gateway
                                    authentication.</small>
                            </div>

                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" id="enableGateway" name="enabled" <?php echo (int) $settings['enabled'] === 1 ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="enableGateway">Enable Gateway</label>
                                <div><small class="text-muted">Turn off to keep settings saved but pause WhatsApp
                                        gateway usage.</small></div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary" id="saveSettingsBtn">Save
                                    Settings</button>
                                <button type="button" class="btn btn-outline-secondary" id="testConnectionBtn">Test
                                    Connection</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>




    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <!--<script src="assets/js/menu-setting.min.js"></script>-->

    <script src="assets/js/plugins/jquery.dataTables.min.js"></script>
    <script src="assets/js/plugins/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/plugins/buttons.colVis.min.js"></script>
    <script src="assets/js/plugins/buttons.print.min.js"></script>
    <script src="assets/js/plugins/pdfmake.min.js"></script>
    <script src="assets/js/plugins/jszip.min.js"></script>
    <script src="assets/js/plugins/dataTables.buttons.min.js"></script>
    <script src="assets/js/plugins/buttons.html5.min.js"></script>
    <script src="assets/js/plugins/buttons.bootstrap4.min.js"></script>
    <script src="assets/js/pages/data-export-custom.js"></script>


    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>

    <script>
        $(document).ready(function () {
            var $form = $('#gatewaySettingsForm');
            var $testButton = $('#testConnectionBtn');
            var $resultBox = $('#testResult');

            function showResult(type, message) {
                $resultBox
                    .removeClass('d-none alert-success alert-danger alert-warning')
                    .addClass('alert-' + type)
                    .text(message);
            }

            function isValidHttpUrl(url) {
                try {
                    var parsed = new URL(url);
                    return parsed.protocol === 'http:' || parsed.protocol === 'https:';
                } catch (error) {
                    return false;
                }
            }

            function validateGatewayFields() {
                var baseUrl = $('#baseUrl').val().trim();
                var apiKey = $('#apiKey').val().trim();
                var secretKey = $('#secretKey').val().trim();

                if (!apiKey) {
                    showResult('danger', 'API key is required.');
                    return null;
                }

                if (!secretKey) {
                    showResult('danger', 'Secret key is required.');
                    return null;
                }

                if (!isValidHttpUrl(baseUrl)) {
                    showResult('danger', 'Please enter a valid Base URL.');
                    return null;
                }

                return {
                    baseUrl: baseUrl,
                    apiKey: apiKey,
                    secretKey: secretKey
                };
            }

            $form.on('submit', function (event) {
                var payload = validateGatewayFields();
                if (!payload) {
                    event.preventDefault();
                }
            });

            $testButton.on('click', function () {
                var payload = validateGatewayFields();
                if (!payload) {
                    return;
                }

                $testButton.prop('disabled', true).text('Testing...');
                showResult('warning', 'Checking gateway connection...');

                $.ajax({
                    url: '/api/test-whatsapp-gateway',
                    type: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({
                        apiKey: payload.apiKey,
                        secretKey: payload.secretKey,
                        baseUrl: payload.baseUrl
                    }),
                    success: function (response) {
                        if (response && response.status === 'success') {
                            showResult('success', 'Gateway connected successfully');
                            return;
                        }

                        showResult('danger', 'Connection failed');
                    },
                    error: function () {
                        showResult('danger', 'Connection failed');
                    },
                    complete: function () {
                        $testButton.prop('disabled', false).text('Test Connection');
                    }
                });
            });
        });
    </script>


</body>

</html>