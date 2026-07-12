<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/payment_services.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/notifications.php';

requireAdmin();

$notifCounts = getNotificationCounts();
$totalNotifs = array_sum($notifCounts);

$displayName = trim($_SESSION['username'] ?? 'Admin');
$displayInitial = strtoupper(substr($displayName, 0, 1));
$displayRole = (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Administrator' : 'Member';

$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flashMessage('error', 'Invalid CSRF token.');
        header('Location: payment-services.php');
        exit;
    }

    if (isset($_POST['create_service'])) {
        $name = trim($_POST['name'] ?? '');
        $displayName = trim($_POST['display_name'] ?? '');
        $phoneNumber = trim($_POST['phone_number'] ?? '');
        $accountName = trim($_POST['account_name'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || $displayName === '' || $phoneNumber === '' || $accountName === '') {
            flashMessage('error', 'All fields are required.');
        } elseif (getPaymentServiceByName($name)) {
            flashMessage('error', 'A service with this name already exists.');
        } else {
            $logoImage = '';
            if (!empty($_FILES['logo_image']['name'])) {
                $targetDir = __DIR__ . '/../uploads/services/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $ext = strtolower(pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION));
                $newFilename = 'logo_' . $name . '_' . time() . '.' . $ext;
                $targetPath = $targetDir . $newFilename;
                if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $targetPath)) {
                    $logoImage = 'uploads/services/' . $newFilename;
                }
            }
            $qrImage = null;
            if (!empty($_FILES['qr_image']['name'])) {
                $targetDir = __DIR__ . '/../uploads/services/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $ext = strtolower(pathinfo($_FILES['qr_image']['name'], PATHINFO_EXTENSION));
                $newFilename = 'qr_' . $name . '_' . time() . '.' . $ext;
                $targetPath = $targetDir . $newFilename;
                if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $targetPath)) {
                    $qrImage = 'uploads/services/' . $newFilename;
                }
            }
            $result = createPaymentService($name, $displayName, $phoneNumber, $logoImage, $accountName, $qrImage, $isActive);
            if ($result !== false) {
                flashMessage('success', 'Payment service created successfully.');
            } else {
                flashMessage('error', 'Failed to create payment service.');
            }
        }
        header('Location: payment-services.php');
        exit;
    }

    if (isset($_POST['update_service'])) {
        $id = (int) ($_POST['service_id'] ?? 0);
        $displayName = trim($_POST['display_name'] ?? '');
        $phoneNumber = trim($_POST['phone_number'] ?? '');
        $accountName = trim($_POST['account_name'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($displayName === '' || $phoneNumber === '' || $accountName === '') {
            flashMessage('error', 'Display name, phone number, and account name are required.');
        } else {
            $currentService = getPaymentServiceById($id);
            $logoImage = $currentService ? $currentService['logo_image'] : '';
            if (!empty($_FILES['logo_image']['name'])) {
                $targetDir = __DIR__ . '/../uploads/services/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $ext = strtolower(pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION));
                $newFilename = 'logo_' . $id . '_' . time() . '.' . $ext;
                $targetPath = $targetDir . $newFilename;
                if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $targetPath)) {
                    $logoImage = 'uploads/services/' . $newFilename;
                }
            }
            $newQr = null;
            if (!empty($_FILES['qr_image']['name'])) {
                $targetDir = __DIR__ . '/../uploads/services/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $ext = strtolower(pathinfo($_FILES['qr_image']['name'], PATHINFO_EXTENSION));
                $newFilename = 'qr_' . $id . '_' . time() . '.' . $ext;
                $targetPath = $targetDir . $newFilename;
                if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $targetPath)) {
                    $newQr = 'uploads/services/' . $newFilename;
                }
            }
            if (updatePaymentService($id, $displayName, $phoneNumber, $logoImage, $accountName, $newQr, $isActive)) {
                flashMessage('success', 'Payment service updated successfully.');
            } else {
                flashMessage('error', 'Failed to update payment service.');
            }
        }
        header('Location: payment-services.php');
        exit;
    }

    if (isset($_POST['toggle_active'])) {
        $id = (int) ($_POST['service_id'] ?? 0);
        togglePaymentServiceActive($id);
        header('Location: payment-services.php');
        exit;
    }

    if (isset($_POST['delete_service'])) {
        $id = (int) ($_POST['service_id'] ?? 0);
        if (deletePaymentService($id)) {
            flashMessage('success', 'Payment service deleted successfully.');
        } else {
            flashMessage('error', 'Failed to delete payment service.');
        }
        header('Location: payment-services.php');
        exit;
    }
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editService = $editId > 0 ? getPaymentServiceById($editId) : null;

$services = getAllPaymentServices();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - Payment Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-gray-100">

    <aside class="fixed left-0 top-0 h-screen overflow-y-auto z-50 w-72 bg-slate-900 text-white flex flex-col">

        <div class="h-16 flex items-center px-6 border-b border-slate-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-2xl font-bold text-white">
                    <?= htmlspecialchars($displayInitial) ?>
                </div>
                <div>
                    <h3 class="font-semibold"><?= htmlspecialchars($displayName) ?></h3>
                    <p class="text-sm text-green-400">● <?= htmlspecialchars($displayRole) ?></p>
                </div>
            </div>
        </div>

        <nav class="mt-6 flex-1">
            <a href="index.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-house mr-4"></i>
                Dashboard
            </a>
            <a href="posts.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-newspaper mr-4"></i>
                Posts
            </a>
            <a href="categories.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-folder mr-4"></i>
                Categories
            </a>
            <a href="users.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-users mr-4"></i>
                Users
            </a>
            <a href="plans.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-gem mr-4"></i>
                Subscription Plans
            </a>

            <a href="user-subscriptions.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-file-contract mr-4"></i> User Subscriptions</a>

            <a href="payments.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-credit-card mr-4"></i>
                Payments
            </a>

            <a href="payment-services.php" class="flex items-center px-6 py-4 bg-blue-600">
                <i class="fa-solid fa-money-bill-transfer mr-4"></i> Payment Services</a>
        </nav>

        <a href="/Nova_News/public/signin.php" class="flex items-center px-6 py-4 hover:bg-red-600">
            <i class="fa-solid fa-right-from-bracket mr-4"></i>
            Logout
        </a>

    </aside>

    <div class="ml-72 flex flex-col h-screen">

        <header class="bg-white shadow h-16 flex justify-between items-center px-8 shrink-0">
            <h2 class="text-3xl font-bold">Payment Services</h2>
            <div class="flex items-center gap-6">
                <button onclick="document.getElementById('add-modal').classList.remove('hidden')" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition text-sm">
                    <i class="fa-solid fa-plus mr-1"></i> Add Service
                </button>
                <button class="relative">
                    <i class="fa-regular fa-bell text-xl"></i>
                    <?php if ($totalNotifs > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center"><?= $totalNotifs > 9 ? '9+' : $totalNotifs ?></span>
                    <?php endif; ?>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold text-white"><?= htmlspecialchars($displayInitial) ?></div>
                    <span class="font-semibold"><?= htmlspecialchars($displayName) ?></span>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 space-y-8">

            <?php if ($flash): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-sm font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Services Table -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="border-b p-5">
                    <h3 class="text-xl font-bold">All Payment Services</h3>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <th class="p-5">ID</th>
                            <th class="p-5">Name</th>
                            <th class="p-5">Display Name</th>
                            <th class="p-5">Phone Number</th>
                            <th class="p-5">Logo</th>
                            <th class="p-5">Account Name</th>
                            <th class="p-5">QR Image</th>
                            <th class="p-5">Status</th>
                            <th class="p-5">Created</th>
                            <th class="p-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($services)): ?>
                            <tr>
                                <td colspan="10" class="p-10 text-center text-gray-500">
                                    <i class="fa-solid fa-money-bill-transfer text-4xl mb-3 block"></i>
                                    No payment services found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($services as $svc): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-5 text-gray-500"><?= (int) $svc['id'] ?></td>
                                <td class="p-5 font-mono text-sm text-gray-500"><?= htmlspecialchars($svc['name']) ?></td>
                                <td class="p-5 font-medium"><?= htmlspecialchars($svc['display_name']) ?></td>
                                <td class="p-5"><?= htmlspecialchars($svc['phone_number']) ?></td>
                                <td class="p-5">
                                    <?php if (!empty($svc['logo_image'])): ?>
                                        <a href="/Nova_News/<?= htmlspecialchars($svc['logo_image']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                            <img src="/Nova_News/<?= htmlspecialchars($svc['logo_image']) ?>" alt="Logo" class="w-10 h-10 object-cover rounded-lg border">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-5"><?= htmlspecialchars($svc['account_name']) ?></td>
                                <td class="p-5">
                                    <?php if ($svc['qr_image']): ?>
                                        <a href="/Nova_News/<?= htmlspecialchars($svc['qr_image']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fa-solid fa-qrcode"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-5">
                                    <form method="post" action="" class="inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="service_id" value="<?= (int) $svc['id'] ?>">
                                        <input type="hidden" name="toggle_active" value="1">
                                        <button type="submit" class="<?= (int) $svc['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> px-3 py-1 rounded-full text-sm font-medium hover:opacity-80 transition">
                                            <?= (int) $svc['is_active'] ? 'Active' : 'Inactive' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="p-5 text-gray-500 text-sm"><?= htmlspecialchars(date('M j, Y', strtotime($svc['created_at']))) ?></td>
                                <td class="p-5 text-right whitespace-nowrap">
                                    <button onclick="openEdit(<?= (int) $svc['id'] ?>)" class="text-blue-600 hover:text-blue-800 mr-4"><i class="fa-solid fa-edit"></i> Edit</button>
                                    <form method="post" action="" class="inline" onsubmit="return confirm('Delete <?= htmlspecialchars($svc['display_name']) ?>?');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="service_id" value="<?= (int) $svc['id'] ?>">
                                        <input type="hidden" name="delete_service" value="1">
                                        <button type="submit" class="text-red-600 hover:text-red-800"><i class="fa-solid fa-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <!-- Add Modal -->
    <div id="add-modal" class="hidden fixed inset-0 bg-black/50 z-[100] flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-xl font-bold">Add Payment Service</h3>
                <button onclick="document.getElementById('add-modal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>
            <form method="post" action="" enctype="multipart/form-data" class="p-6 space-y-4">
                <?= csrfField() ?>
                <input type="hidden" name="create_service" value="1">

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" placeholder="kpay, wavepay, ayapay" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <p class="text-xs text-gray-500 mt-1">Unique key, e.g. 'kpay'</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Display Name <span class="text-red-500">*</span></label>
                        <input type="text" name="display_name" placeholder="KPay" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                        <input type="text" name="phone_number" placeholder="09777777777" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Account Name <span class="text-red-500">*</span></label>
                        <input type="text" name="account_name" placeholder="Nova News" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Logo Image</label>
                    <input type="file" name="logo_image" accept="image/*" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">QR Code Image</label>
                    <input type="file" name="qr_image" accept="image/*" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="add_is_active" name="is_active" value="1" checked class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="add_is_active" class="text-sm font-medium text-gray-700">Active</label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('add-modal').classList.add('hidden')" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                        <i class="fa-solid fa-plus mr-1"></i> Create Service
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="hidden fixed inset-0 bg-black/50 z-[100] flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-xl font-bold">Edit Payment Service</h3>
                <button onclick="document.getElementById('edit-modal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>
            <form method="post" action="" enctype="multipart/form-data" class="p-6 space-y-4">
                <?= csrfField() ?>
                <input type="hidden" name="service_id" id="edit_service_id" value="">
                <input type="hidden" name="update_service" value="1">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Name</label>
                    <input type="text" id="edit_name_display" disabled class="w-full px-4 py-2.5 border border-gray-200 bg-gray-50 text-gray-500 rounded-lg cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Name cannot be changed after creation.</p>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Display Name <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_display_name" name="display_name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_phone_number" name="phone_number" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Account Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_account_name" name="account_name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Logo Image</label>
                    <div id="edit_logo_preview" class="mb-2 hidden">
                        <img id="edit_logo_img" class="w-16 h-16 object-cover rounded-lg border" alt="Logo">
                    </div>
                    <input type="file" name="logo_image" accept="image/*" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep current logo.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">QR Code Image</label>
                    <div id="edit_qr_preview" class="mb-2 hidden">
                        <img id="edit_qr_img" class="w-24 h-24 object-cover rounded-lg border" alt="QR">
                    </div>
                    <input type="file" name="qr_image" accept="image/*" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep current QR.</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="edit_is_active" class="text-sm font-medium text-gray-700">Active</label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                        <i class="fa-solid fa-save mr-1"></i> Update Service
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
const serviceData = <?= json_encode($services) ?>;

function openEdit(id) {
    const svc = serviceData.find(s => parseInt(s.id) === id);
    if (!svc) return;
    document.getElementById('edit_service_id').value = svc.id;
    document.getElementById('edit_name_display').value = svc.name;
    document.getElementById('edit_display_name').value = svc.display_name;
    document.getElementById('edit_phone_number').value = svc.phone_number;
    document.getElementById('edit_account_name').value = svc.account_name;
    document.getElementById('edit_is_active').checked = parseInt(svc.is_active) === 1;
    const logoPreview = document.getElementById('edit_logo_preview');
    const logoImg = document.getElementById('edit_logo_img');
    if (svc.logo_image) {
        logoImg.src = '/Nova_News/' + svc.logo_image;
        logoPreview.classList.remove('hidden');
    } else {
        logoPreview.classList.add('hidden');
    }
    const qrPreview = document.getElementById('edit_qr_preview');
    const qrImg = document.getElementById('edit_qr_img');
    if (svc.qr_image) {
        qrImg.src = '/Nova_News/' + svc.qr_image;
        qrPreview.classList.remove('hidden');
    } else {
        qrPreview.classList.add('hidden');
    }
    document.getElementById('edit-modal').classList.remove('hidden');
}
</script>

</body>
</html>
