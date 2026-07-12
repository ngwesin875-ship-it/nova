<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/payments.php';
require_once __DIR__ . '/../includes/payment_services.php';

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
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <div class="text-center mb-10">
            <span class="inline-block px-4 py-1.5 text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 rounded-full uppercase tracking-wider mb-4">
                <i class="fa-solid fa-lock mr-1"></i> Secure Checkout
            </span>
            <h1 class="text-3xl md:text-4xl font-extrabold text-theme-adaptive mb-3">Complete Your Payment</h1>
        </div>

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

            <div class="grid md:grid-cols-5 gap-8">

                <!-- Order Summary -->
                <div class="md:col-span-2">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4">
                        <h3 class="text-lg font-bold text-gray-900">Order Summary</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Plan</span>
                                <span class="font-semibold text-gray-900"><?= htmlspecialchars($plan['name']) ?></span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Duration</span>
                                <span class="font-semibold text-gray-900"><?= (int) $plan['duration_months'] ?> Month<?= (int) $plan['duration_months'] > 1 ? 's' : '' ?></span>
                            </div>
                            <?php if ((float) $plan['discount_percentage'] > 0): ?>
                            <div class="flex justify-between text-gray-600">
                                <span>Original Price</span>
                                <span class="text-gray-400 line-through">$<?= number_format((float) $plan['price'], 2) ?></span>
                            </div>
                            <div class="flex justify-between text-green-600">
                                <span>Discount</span>
                                <span><?= (int) $plan['discount_percentage'] ?>% OFF</span>
                            </div>
                            <?php endif; ?>
                            <div class="border-t border-gray-200 pt-3 flex justify-between text-lg">
                                <span class="text-gray-600 font-medium">Total</span>
                                <span class="font-extrabold text-amber-600">$<?= number_format((float) $plan['final_price'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="md:col-span-3">
                    <form method="post" action="" enctype="multipart/form-data" class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
                        <?= csrfField() ?>
                        <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">
                        <input type="hidden" name="process_payment" value="1">
                        <input type="hidden" name="payment_method" id="selected-method" value="">

                        <h3 class="text-lg font-bold text-gray-900">Choose Payment Method</h3>

                        <!-- Payment Method Icons -->
                        <div class="grid grid-cols-3 gap-3">
                            <?php foreach ($paymentServices as $svc): ?>
                            <button type="button" data-service="<?= htmlspecialchars($svc['name']) ?>" data-display="<?= htmlspecialchars($svc['display_name']) ?>"
                                class="method-btn bg-white border-2 border-gray-200 rounded-xl p-4 text-center hover:border-amber-500 transition-all">
                                <img src="/Nova_News/<?= htmlspecialchars($svc['logo_image']) ?>"
                                     alt="<?= htmlspecialchars($svc['display_name']) ?>"
                                     class="w-12 h-12 mx-auto rounded-full object-cover mb-2">
                                <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($svc['display_name']) ?></p>
                            </button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Service Details (shown when a method is selected) -->
                        <div id="service-details" class="hidden bg-gray-50 rounded-xl p-4 border border-gray-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-amber-600" id="detail-title">Send payment to:</h4>
                                <button type="button" id="change-method" class="text-xs text-gray-500 hover:text-gray-700 transition">Change</button>
                            </div>
                            <div id="detail-content"></div>
                        </div>

                        <template id="service-template">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between text-gray-600">
                                    <span>Account Name</span>
                                    <span class="font-semibold text-gray-900" data-field="account_name"></span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Phone Number</span>
                                    <span class="font-semibold text-gray-900" data-field="phone_number"></span>
                                </div>
                                <div class="text-center pt-2" data-field="qr_container">
                                    <img data-field="qr_image" class="mx-auto w-32 h-32 object-contain rounded-lg border border-gray-300" alt="QR Code">
                                </div>
                            </div>
                        </template>

                        <!-- User Account Info -->
                        <div class="border-t border-gray-200 pt-5 space-y-4">
                            <h3 class="text-lg font-bold text-gray-900">Your Account Info</h3>
                            <p class="text-xs text-gray-500">Enter the account details you used to make the payment.</p>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="account_name" class="block text-sm font-semibold text-gray-700 mb-1.5">Your Account Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="account_name" name="account_name" placeholder="Your full name on the account" required class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 rounded-xl placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                                <div>
                                    <label for="account_phone" class="block text-sm font-semibold text-gray-700 mb-1.5">Your Account Phone <span class="text-red-500">*</span></label>
                                    <input type="text" id="account_phone" name="account_phone" placeholder="09XXXXXXXXX" required class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 rounded-xl placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            </div>

                            <div>
                                <label for="receipt_image" class="block text-sm font-semibold text-gray-700 mb-1.5">E-Receipt Screenshot <span class="text-red-500">*</span></label>
                                <input type="file" id="receipt_image" name="receipt_image" accept="image/*" required class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 rounded-xl file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-500 file:text-white file:font-bold hover:file:brightness-110 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <p class="text-xs text-gray-500 mt-1.5">Upload a screenshot of your payment confirmation.</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                            <div class="flex items-center gap-3 text-sm text-gray-600">
                                <i class="fa-solid fa-shield-halved text-green-500 text-lg"></i>
                                <span>Your payment information is processed securely. Your subscription will be activated after payment confirmation.</span>
                            </div>
                        </div>

                        <button type="submit" id="submit-btn" class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-white font-bold rounded-xl transition-all shadow-xl hover:shadow-amber-500/30 text-lg disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <i class="fa-solid fa-check-circle mr-1"></i> Submit Payment - $<?= number_format((float) $plan['final_price'], 2) ?>
                        </button>

                        <div class="text-center">
                            <a href="subscribe.php" class="text-sm text-gray-500 hover:text-gray-700 transition">
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
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
