<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/payments.php';
require_once __DIR__ . '/../includes/payment_services.php';
require_once __DIR__ . '/../includes/notifications.php';

requireLogin();
if (isAdmin()) { header('Location: /Nova_News/admin/index.php'); exit; }

$errorMessage = '';
$successMessage = '';

$planId = (int) ($_POST['plan_id'] ?? ($_GET['plan_id'] ?? 0));
$plan = $planId > 0 ? getPlanById($planId) : null;

$paymentServices = getAllPaymentServices();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMessage = 'Invalid CSRF token.';
    } else {
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $plan = getPlanById($planId);
        if (!$plan) {
            $errorMessage = 'Invalid plan selected.';
        } else {
            $paymentMethod = trim($_POST['payment_method'] ?? '');
            $accountName = trim($_POST['account_name'] ?? '');
            $accountPhone = trim($_POST['account_phone'] ?? '');
            if ($paymentMethod === '') {
                $errorMessage = 'Please select a payment method.';
            } elseif ($accountName === '') {
                $errorMessage = 'Account name is required.';
            } elseif ($accountPhone === '') {
                $errorMessage = 'Account phone is required.';
            } elseif (empty($_FILES['receipt_image']['name'])) {
                $errorMessage = 'E-Receipt screenshot is required.';
            } else {
                $targetDir = __DIR__ . '/../uploads/receipts/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $ext = strtolower(pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION));
                $newFilename = 'receipt_' . currentUserId() . '_' . time() . '.' . $ext;
                $targetPath = $targetDir . $newFilename;
                if (!move_uploaded_file($_FILES['receipt_image']['tmp_name'], $targetPath)) {
                    $errorMessage = 'Failed to upload receipt image. Please try again.';
                } else {
                    $receiptImage = 'uploads/receipts/' . $newFilename;
                    $subscriptionId = createUserSubscription(currentUserId(), $planId);
                    if ($subscriptionId === false) {
                        $errorMessage = 'Failed to create subscription. Please try again.';
                    } else {
                        $result = createPayment($subscriptionId, (float) $plan['final_price'], $paymentMethod, $accountName, $accountPhone, $receiptImage, 'pending');
                        if ($result !== false) {
                            createNotification(
                                'new_subscription',
                                'New Subscription Request',
                                htmlspecialchars(currentUserName()) . ' subscribed to ' . htmlspecialchars($plan['name']) . ' (' . number_format((float) $plan['final_price'], 0) . ' MMK). Awaiting approval.',
                                $subscriptionId,
                                'user_subscriptions'
                            );
                            flashMessage('success', 'Payment submitted! Your subscription will be activated once confirmed.');
                            header('Location: payments.php');
                            exit;
                        } else {
                            $errorMessage = 'Payment processing failed. Please contact support.';
                        }
                    }
                }
            }
        }
    }
}

$pageTitle = 'Complete Payment - Nova News';
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Page Header -->
        <div class="flex items-center gap-3 mb-8">
            <div class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 rounded-full uppercase tracking-wider">
                <i class="fa-solid fa-lock"></i> Secure Checkout
            </div>
        </div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-theme-adaptive mb-8">Complete Your Payment</h1>

        <?php if ($errorMessage): ?>
            <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-sm font-medium bg-red-50 text-red-700 border border-red-200">
                <i class="fa-solid fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-sm font-medium bg-green-50 text-green-700 border border-green-200">
                <i class="fa-solid fa-check-circle mr-2"></i> <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (!$plan): ?>
            <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center">
                <i class="fa-solid fa-triangle-exclamation text-4xl text-amber-500 mb-4"></i>
                <p class="text-gray-600 text-lg mb-4">No plan selected or plan not found.</p>
                <a href="subscribe.php" class="inline-block px-6 py-3 bg-gradient-to-r from-amber-500 to-yellow-500 text-white font-bold rounded-xl hover:brightness-110 transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Browse Plans
                </a>
            </div>
        <?php else: ?>

            <div class="grid md:grid-cols-2 gap-6 items-start">

                <!-- Left Column -->
                <div class="space-y-5">
                    <!-- Order Summary -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-receipt text-blue-500 text-sm"></i>
                            </div>
                            <h3 class="text-base font-bold text-gray-900">Order Summary</h3>
                        </div>
                        <div class="space-y-2.5 text-sm">
                            <div class="flex justify-between text-gray-500">
                                <span>Plan</span>
                                <span class="font-semibold text-gray-900"><?= htmlspecialchars($plan['name']) ?></span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Duration</span>
                                <span class="font-semibold text-gray-900"><?= (int) $plan['duration_months'] ?> Month<?= (int) $plan['duration_months'] > 1 ? 's' : '' ?></span>
                            </div>
                            <?php if ((float) $plan['discount_percentage'] > 0): ?>
                            <div class="flex justify-between text-gray-500">
                                <span>Original Price</span>
                                <span class="text-gray-400 line-through"><?= number_format((float) $plan['price'], 0) ?> MMK</span>
                            </div>
                            <div class="flex justify-between text-green-600">
                                <span>Discount</span>
                                <span class="font-semibold"><?= (int) $plan['discount_percentage'] ?>% OFF</span>
                            </div>
                            <?php endif; ?>
                            <div class="border-t border-gray-100 pt-2.5 flex justify-between items-center">
                                <span class="text-gray-500 font-medium">Total</span>
                                <span class="text-xl font-extrabold text-amber-600"><?= number_format((float) $plan['final_price'], 0) ?> <span class="text-sm font-semibold">MMK</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Choose Payment Method -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-wallet text-purple-500 text-sm"></i>
                            </div>
                            <h3 class="text-base font-bold text-gray-900">Choose Payment Method</h3>
                        </div>

                        <!-- Payment Method Icons -->
                        <div class="grid grid-cols-2 gap-2.5">
                            <?php foreach ($paymentServices as $svc): ?>
                            <button type="button" data-service="<?= htmlspecialchars($svc['name']) ?>" data-display="<?= htmlspecialchars($svc['display_name']) ?>"
                                class="method-btn flex items-center gap-3 bg-white border-2 border-gray-200 rounded-xl p-3 text-left hover:border-amber-500 hover:bg-amber-50/50 transition-all">
                                <img src="/Nova_News/<?= htmlspecialchars($svc['logo_image']) ?>"
                                     alt="<?= htmlspecialchars($svc['display_name']) ?>"
                                     class="w-10 h-10 rounded-full object-cover shrink-0">
                                <span class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($svc['display_name']) ?></span>
                            </button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Service Details (shown when a method is selected) -->
                        <div id="service-details" class="hidden mt-4 bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-4 border border-amber-200/60">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-bold text-amber-700 uppercase tracking-wide" id="detail-title">Send payment to:</h4>
                                <button type="button" id="change-method" class="text-xs text-gray-400 hover:text-gray-700 transition font-medium">Change</button>
                            </div>
                            <div id="detail-content"></div>
                        </div>

                        <template id="service-template">
                            <div class="space-y-3">
                                <div class="bg-white rounded-lg p-3 border border-amber-100">
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-0.5">Account Name</p>
                                    <p class="text-sm font-bold text-gray-900" data-field="account_name"></p>
                                </div>
                                <div class="bg-white rounded-lg p-3 border border-amber-100 relative group">
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-0.5">Phone Number</p>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-bold text-gray-900" data-field="phone_number"></p>
                                        <button type="button" data-copy class="copy-btn relative text-gray-400 hover:text-amber-600 transition-colors" title="Copy">
                                            <i class="fa-regular fa-copy text-xs"></i>
                                            <span class="copy-tooltip absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-[10px] font-medium rounded whitespace-nowrap opacity-0 pointer-events-none transition-opacity duration-200">Copied!</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="text-center pt-1" data-field="qr_container">
                                    <div class="inline-block bg-white p-2 rounded-xl border border-amber-100 shadow-sm">
                                        <img data-field="qr_image" class="w-28 h-28 object-contain rounded-lg" alt="QR Code">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <form method="post" action="" enctype="multipart/form-data" class="bg-white border border-gray-200 rounded-2xl p-5">
                        <?= csrfField() ?>
                        <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">
                        <input type="hidden" name="process_payment" value="1">
                        <input type="hidden" name="payment_method" id="selected-method" value="">

                        <!-- Your Account Info -->
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-user text-emerald-500 text-sm"></i>
                            </div>
                            <h3 class="text-base font-bold text-gray-900">Your Account Info</h3>
                        </div>
                        <p class="text-xs text-gray-400 mb-4">Enter the account details you used to make the payment.</p>

                        <div class="space-y-3">
                            <div>
                                <label for="account_name" class="block text-xs font-semibold text-gray-600 mb-1">Account Name <span class="text-red-500">*</span></label>
                                <input type="text" id="account_name" name="account_name" placeholder="Your full name on the account" required class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition">
                            </div>
                            <div>
                                <label for="account_phone" class="block text-xs font-semibold text-gray-600 mb-1">Account Phone <span class="text-red-500">*</span></label>
                                <input type="tel" id="account_phone" name="account_phone" placeholder="09XXXXXXXXX" pattern="[0-9]*" inputmode="numeric" required oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition">
                            </div>
                        </div>

                        <div class="border-t border-gray-100 mt-5 pt-5">
                            <label for="receipt_image" class="block text-xs font-semibold text-gray-600 mb-1.5">E-Receipt Screenshot <span class="text-red-500">*</span></label>
                            <label for="receipt_image" class="flex flex-col items-center justify-center w-full h-32 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-amber-400 hover:bg-amber-50/30 transition-all">
                                <i class="fa-solid fa-cloud-arrow-up text-2xl text-gray-300 mb-2"></i>
                                <span class="text-sm font-medium text-gray-500">Choose File</span>
                                <span class="text-[10px] text-gray-400 mt-0.5">PNG, JPG up to 5MB</span>
                            </label>
                            <input type="file" id="receipt_image" name="receipt_image" accept="image/*" required class="hidden">
                            <div id="receipt-preview" class="hidden mt-3 relative">
                                <img id="receipt-preview-img" src="" alt="E-Receipt Preview" class="w-full max-h-48 object-contain rounded-xl border border-gray-200 shadow-sm">
                                <button type="button" id="receipt-remove" class="absolute top-2 right-2 w-6 h-6 bg-red-500 text-white rounded-full text-xs hover:bg-red-600 transition flex items-center justify-center shadow"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>

                        <div class="mt-5 p-3 bg-gray-50 rounded-xl flex items-center gap-2.5">
                            <i class="fa-solid fa-shield-halved text-green-500"></i>
                            <span class="text-xs text-gray-500">Payment info is processed securely. Subscription activates after confirmation.</span>
                        </div>

                        <button type="submit" id="submit-btn" class="w-full mt-5 py-3 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-amber-500/25 text-sm disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none" disabled>
                            <i class="fa-solid fa-check-circle mr-1.5"></i> Submit Payment
                        </button>

                        <div class="text-center mt-3">
                            <a href="subscribe.php" class="text-xs text-gray-400 hover:text-gray-600 transition">
                                <i class="fa-solid fa-arrow-left mr-1"></i> Change Plan
                            </a>
                        </div>
                    </form>
                </div>

            </div>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const serviceData = <?= json_encode($paymentServices) ?>;
    const methodBtns = document.querySelectorAll('.method-btn');
    const serviceDetails = document.getElementById('service-details');
    const detailContent = document.getElementById('detail-content');
    const detailTitle = document.getElementById('detail-title');
    const changeMethod = document.getElementById('change-method');
    const selectedMethod = document.getElementById('selected-method');
    const submitBtn = document.getElementById('submit-btn');
    const template = document.getElementById('service-template');
    let selected = '';

    methodBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const name = this.dataset.service;
            const display = this.dataset.display;

            methodBtns.forEach(b => {
                b.classList.remove('border-amber-500', 'bg-amber-50');
                b.classList.add('border-gray-200');
            });
            this.classList.remove('border-gray-200');
            this.classList.add('border-amber-500', 'bg-amber-50');

            selected = name;
            selectedMethod.value = display;

            const svc = serviceData.find(s => s.name === name);
            if (svc) {
                const clone = template.content.cloneNode(true);
                clone.querySelector('[data-field="account_name"]').textContent = svc.account_name;
                clone.querySelector('[data-field="phone_number"]').textContent = svc.phone_number;
                const qrImg = clone.querySelector('[data-field="qr_image"]');
                const qrContainer = clone.querySelector('[data-field="qr_container"]');
                if (svc.qr_image) {
                    qrImg.src = '/Nova_News/' + svc.qr_image;
                } else {
                    qrContainer.remove();
                }
                detailContent.innerHTML = '';
                detailContent.appendChild(clone);
                detailTitle.textContent = 'Send payment to ' + display + ':';
                serviceDetails.classList.remove('hidden');
                submitBtn.disabled = false;
            }
        });
    });

    changeMethod.addEventListener('click', function () {
        methodBtns.forEach(b => {
            b.classList.remove('border-amber-500', 'bg-amber-50');
            b.classList.add('border-gray-200');
        });
        serviceDetails.classList.add('hidden');
        selectedMethod.value = '';
        selected = '';
        submitBtn.disabled = true;
    });

    // E-Receipt image preview
    const receiptInput = document.getElementById('receipt_image');
    const receiptPreview = document.getElementById('receipt-preview');
    const receiptPreviewImg = document.getElementById('receipt-preview-img');
    const receiptRemove = document.getElementById('receipt-remove');
    let receiptObjectUrl = null;

    receiptInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
            if (receiptObjectUrl) URL.revokeObjectURL(receiptObjectUrl);
            receiptObjectUrl = URL.createObjectURL(file);
            receiptPreviewImg.src = receiptObjectUrl;
            receiptPreview.classList.remove('hidden');
        }
    });

    receiptRemove.addEventListener('click', function () {
        receiptInput.value = '';
        if (receiptObjectUrl) URL.revokeObjectURL(receiptObjectUrl);
        receiptObjectUrl = null;
        receiptPreviewImg.src = '';
        receiptPreview.classList.add('hidden');
    });

    // Copy to clipboard
    serviceDetails.addEventListener('click', function (e) {
        const copyBtn = e.target.closest('.copy-btn');
        if (!copyBtn) return;
        const phoneEl = copyBtn.closest('.flex').querySelector('[data-field="phone_number"]');
        if (!phoneEl) return;
        const text = phoneEl.textContent.trim();
        navigator.clipboard.writeText(text).then(function () {
            const tooltip = copyBtn.querySelector('.copy-tooltip');
            tooltip.classList.remove('opacity-0');
            tooltip.classList.add('opacity-100');
            setTimeout(function () {
                tooltip.classList.remove('opacity-100');
                tooltip.classList.add('opacity-0');
            }, 1500);
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
