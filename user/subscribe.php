<?php
// user/subscribe.php — Plan selection page
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/subscription.php';

requireLogin();
if (isAdmin()) { header('Location: /Nova_News/admin/index.php'); exit; }

$plans     = getAllPlans();
$activeSub = getActiveSubscription(currentUserId());
$pageTitle = 'Choose Your Plan – Nova_News';

include __DIR__ . '/../includes/header.php';
?>
<main class="flex-1">
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <!-- Header -->
    <div class="text-center mb-12">
        <span class="inline-block px-4 py-1.5 text-xs font-bold text-amber-400 bg-amber-500/10 border border-amber-500/30 rounded-full uppercase tracking-wider mb-4">
            Premium Access
        </span>
        <h1 class="text-3xl md:text-4xl font-extrabold text-theme-adaptive mb-3">
            Unlock All Premium Content
        </h1>
        <p class="text-slate-500 text-lg max-w-xl mx-auto">
            Get unlimited access to in-depth news, exclusive analysis and breaking stories. Cancel anytime.
        </p>
    </div>

    <!-- Active Subscription Banner -->
    <?php if ($activeSub): ?>
    <div class="mb-8 p-4 bg-green-500/10 border border-green-500/30 rounded-xl flex items-center gap-3">
        <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="text-green-400 font-semibold text-sm">Active Subscription: <?= htmlspecialchars($activeSub['plan_name']) ?></p>
            <p class="text-slate-400 text-xs">Valid until <?= date('F j, Y', strtotime($activeSub['end_date'])) ?>. Subscribing again will replace your current plan.</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Plan Cards -->
    <form method="POST" action="/Nova_News/user/payment.php" id="plan-form">
        <?= csrfField() ?>
        <input type="hidden" name="plan_id" id="selected-plan-id" value="">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <?php foreach ($plans as $i => $plan):
                $isPopular = ($plan['duration_months'] === 3);
                $savings   = $plan['discount_percentage'] > 0 ? 'Save ' . (int)$plan['discount_percentage'] . '%' : null;
            ?>
            <div data-plan-id="<?= $plan['id'] ?>"
                 class="relative cursor-pointer border rounded-2xl p-6 transition-all duration-200 hover:-translate-y-1
                         <?= $isPopular ? 'border-amber-500/50 bg-gradient-to-b from-amber-50 to-white' : 'border-slate-200 bg-white hover:border-slate-300' ?>">

                <!-- Popular Badge -->
                <?php if ($isPopular): ?>
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                    <span class="px-4 py-1 text-xs font-bold bg-gradient-to-r from-amber-500 to-yellow-400 text-slate-900 rounded-full shadow-lg">
                        ★ MOST POPULAR
                    </span>
                </div>
                <?php endif; ?>

                <!-- Savings -->
                <?php if ($savings): ?>
                <div class="inline-block px-3 py-1 text-xs font-semibold bg-green-500/20 text-green-400 border border-green-500/30 rounded-full mb-4">
                    <?= $savings ?>
                </div>
                <?php else: ?>
                <div class="h-7 mb-4"></div>
                <?php endif; ?>

                <!-- Plan Info -->
                <h2 class="text-lg font-bold text-slate-900 mb-1"><?= htmlspecialchars($plan['name']) ?></h2>
                <p class="text-slate-500 text-sm mb-5"><?= $plan['duration_months'] ?> Month<?= $plan['duration_months'] > 1 ? 's' : '' ?> Access</p>

                <!-- Price -->
                <div class="mb-6">
                    <?php if ($plan['discount_percentage'] > 0): ?>
                    <p class="text-slate-400 text-sm line-through"><?= '$' . number_format($plan['price'], 2) ?></p>
                    <?php endif; ?>
                    <span class="text-4xl font-extrabold text-slate-900">$<?= number_format($plan['final_price'], 2) ?></span>
                    <span class="text-slate-500 text-sm ml-1">total</span>
                    <p class="text-slate-400 text-xs mt-1">
                        ~$<?= number_format($plan['final_price'] / $plan['duration_months'], 2) ?>/month
                    </p>
                </div>

                <!-- Features -->
                <ul class="space-y-2.5 mb-6">
                    <?php $features = ['Unlimited premium articles', 'Exclusive analysis & reports', 'Early access to breaking news', 'Ad-free reading experience']; ?>
                    <?php foreach ($features as $f): ?>
                     <li class="flex items-center gap-2 text-sm text-slate-600">
                        <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?= $f ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Check mark (selected) -->
                <div data-plan-check class="hidden absolute top-4 right-4 w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center">
            <button type="submit" id="proceed-btn"
                    class="px-10 py-3.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-900 font-bold rounded-2xl transition-all shadow-xl hover:shadow-amber-500/30 text-lg disabled:opacity-50 disabled:cursor-not-allowed">
                Continue to Payment →
            </button>
            <p class="text-slate-500 text-xs mt-3">🔒 Secure & simulated checkout. Cancel anytime.</p>
        </div>
    </form>

    <!-- Comparison table -->
    <div class="mt-16 bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h2 class="font-bold text-slate-900">Feature Comparison</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs text-slate-500 bg-slate-50 uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Feature</th>
                        <th class="px-6 py-3 text-center">Free</th>
                        <th class="px-6 py-3 text-center text-amber-500">Premium</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php $rows = [
                        ['Free articles', true,  true],
                        ['Premium articles', false, true],
                        ['Exclusive analysis', false, true],
                        ['Ad-free experience', false, true],
                        ['Breaking news alerts', true,  true],
                        ['Early access content', false, true],
                    ]; foreach ($rows as [$label, $free, $prem]): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3.5 text-slate-700"><?= $label ?></td>
                        <td class="px-6 py-3.5 text-center">
                            <?= $free ? '<svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>' : '<svg class="w-5 h-5 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' ?>
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            <?= $prem ? '<svg class="w-5 h-5 text-amber-500 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>' : '<svg class="w-5 h-5 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const planCards = document.querySelectorAll('[data-plan-id]');
    const hiddenInput = document.getElementById('selected-plan-id');
    const proceedBtn = document.getElementById('proceed-btn');

    planCards.forEach(card => {
        card.addEventListener('click', function () {
            planCards.forEach(c => {
                c.classList.remove('ring-2', 'ring-amber-500', 'border-amber-500');
                const check = c.querySelector('[data-plan-check]');
                if (check) check.classList.add('hidden');
            });

            this.classList.add('ring-2', 'ring-amber-500', 'border-amber-500');
            const check = this.querySelector('[data-plan-check]');
            if (check) check.classList.remove('hidden');

            hiddenInput.value = this.dataset.planId;
        });
    });

    proceedBtn.addEventListener('click', function (e) {
        if (!hiddenInput.value) {
            e.preventDefault();
            alert('Please select a plan first.');
        }
    });
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
