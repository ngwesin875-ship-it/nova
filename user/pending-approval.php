<?php
require_once __DIR__ . '/header.php';
?>

<main class="flex-1">
    <div class="max-w-xl mx-auto px-4 py-20 text-center">
        <div class="bg-white border border-amber-200 rounded-2xl p-10 shadow-sm">
            <div class="w-16 h-16 mx-auto mb-5 bg-amber-100 rounded-full flex items-center justify-center">
                <i class="fa-solid fa-clock text-amber-500 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 mb-3">Waiting for Approval</h1>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Your subscription payment has been submitted and is currently being reviewed by our admin team.
                You will gain access to premium content once your payment is confirmed.
            </p>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                <p class="text-amber-700 text-sm font-medium">
                    <i class="fa-solid fa-info-circle mr-1"></i>
                    This usually takes a few hours. You will be notified once approved.
                </p>
            </div>
            <a href="dashboard.php" class="inline-block px-6 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to Dashboard
            </a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
