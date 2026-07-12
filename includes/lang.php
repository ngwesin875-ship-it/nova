<?php
// Language switching helper
function getCurrentLang(): string {
    return $_COOKIE['nova_lang'] ?? 'en';
}

function __(string $key, array $replace = []): string {
    static $lang = null;
    static $translations = null;

    if ($lang === null) {
        $lang = getCurrentLang();
    }
    if ($translations === null) {
        $translations = getTranslations();
    }

    $text = $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;

    if (!empty($replace)) {
        foreach ($replace as $k => $v) {
            $text = str_replace('{' . $k . '}', $v, $text);
        }
    }

    return $text;
}

function getTranslations(): array {
    return [
        'en' => [
            // header
            'header.home' => 'Home',
            'header.categories' => 'Categories',
            'header.search' => 'Search news...',
            'header.signin' => 'Sign In',
            'header.get_started' => 'Get Started',
            'header.admin_panel' => 'Admin Panel',
            'header.signout' => 'Sign Out',
            'header.go_premium' => 'Go Premium',
            'header.premium' => 'PREMIUM',
            'header.my_dashboard' => 'My Dashboard',
            'header.subscription' => 'Subscription',
            'header.my_payments' => 'My Payments',
            'header.member' => 'Member',
            'header.administrator' => 'Administrator',

            // footer
            'footer.copyright' => '&copy; 2026 Nova News. Bringing Light to Truth. All rights reserved.',

            // hero
            'hero.premium_exclusive' => 'Premium Exclusive',
            'hero.read_article' => 'Read Article',
            'hero.unlock_article' => 'Unlock This Article',
            'hero.welcome_title' => 'Welcome to Nova News',
            'hero.welcome_desc' => 'Stay informed with the latest breaking news, exclusive stories, and in-depth analysis from around the world.',

            // sections
            'section.trending' => 'TRENDING TODAY',
            'section.browse_categories' => 'BROWSE CATEGORIES',
            'section.latest_news' => 'LATEST NEWS',
            'section.premium_articles' => 'PREMIUM ARTICLES',
            'section.view_all' => 'View All',
            'section.editors_pick' => 'Editor\'s Pick',

            // premium access
            'premium.title' => 'PREMIUM ACCESS',
            'premium.unlimited' => 'Unlimited premium articles',
            'premium.exclusive' => 'Exclusive analysis & reports',
            'premium.early_access' => 'Early access to breaking news',
            'premium.ad_free' => 'Ad-free reading experience',
            'premium.starting_from' => 'Starting from',
            'premium.per_month' => '/ Month',
            'premium.choose_plan' => 'Choose Plan',
            'premium.learn_more' => 'Learn More',

            // top categories
            'categories.title' => 'Top Categories',
            'categories.article' => 'Article',
            'categories.articles' => 'Articles',

            // latest news filter
            'filter.all' => 'All',
            'filter.free' => 'Free',
            'filter.premium' => 'Premium',

            // post badges
            'badge.premium' => 'Premium',
            'badge.free_article' => 'Free Article',

            // empty states
            'empty.no_articles' => 'No articles yet.',
            'empty.no_trending' => 'No trending articles yet.',
            'empty.no_premium' => 'No premium articles yet.',
            'empty.no_categories' => 'No categories yet.',
            'empty.no_results' => 'No articles found in this category yet.',

            // article page
            'article.back_to_news' => 'Back to News',
            'article.related_articles' => 'Related Articles',
            'article.premium_locked_title' => 'Premium Article',
            'article.premium_locked_desc' => 'This article is exclusive to premium subscribers. Subscribe to unlock full access.',
            'article.go_premium' => 'Go Premium',
            'article.sign_in_to_subscribe' => 'Sign In to Subscribe',

            // subscribe page
            'subscribe.title' => 'Choose Your Plan',
            'subscribe.per_month' => '/month',
            'subscribe.subscribe' => 'Subscribe Now',
            'subscribe.current_plan' => 'Current Plan',

            // auth
            'auth.sign_in' => 'Sign In',
            'auth.sign_up' => 'Get Started',
            'auth.email' => 'Email',
            'auth.password' => 'Password',
            'auth.username' => 'Username',

            // search
            'search.title' => 'Search Results',
            'search.no_results' => 'No results found for',
            'search.placeholder' => 'Search news...',

            // dashboard
            'dashboard.title' => 'My Dashboard',

            // payments
            'payments.title' => 'My Payments',

            // all posts
            'all_posts.all' => 'All News',
            'all_posts.free' => 'Free News',
            'all_posts.premium' => 'Premium News',
            'all_posts.total' => 'article(s) total',
            'all_posts.prev' => 'Previous',
            'all_posts.next' => 'Next',

            // article page
            'article.404' => 'Article not found.',
            'article.back_to_home' => 'Back to Home',

            // premium sidebar
            'sidebar.premium_access' => 'PREMIUM ACCESS',
            'sidebar.unlimited' => 'Unlimited premium articles',
            'sidebar.exclusive' => 'Exclusive analysis & reports',
            'sidebar.early_access' => 'Early access to breaking news',
            'sidebar.ad_free' => 'Ad-free experience',
            'sidebar.starting_from' => 'Starting from',
            'sidebar.per_month' => '/ Month',
            'sidebar.choose_plan' => 'Choose Plan',

            // editor's pick
            'editor_pick.title' => 'Editor\'s Pick',
            'editor_pick.premium' => 'Premium',
            'editor_pick.free' => 'Free',

            // latest news sidebar
            'latest_news.title' => 'LATEST NEWS',

            // article page
            'article.back_to_news' => 'Back to News',
            'article.related_articles' => 'Related Articles',
            'article.premium_locked_title' => 'Premium Article',
            'article.premium_locked_desc' => 'This article is exclusive to premium subscribers. Subscribe to unlock full access.',
            'article.go_premium' => 'Go Premium',
            'article.sign_in_to_subscribe' => 'Sign In to Subscribe',

            // premium
            'premium.access' => 'PREMIUM ACCESS',
            'premium.unlimited_articles' => 'Unlimited premium articles',
            'premium.exclusive_analysis' => 'Exclusive analysis & reports',
            'premium.early_access_news' => 'Early access to breaking news',
            'premium.ad_free_experience' => 'Ad-free reading experience',
            'premium.starting_from' => 'Starting from',
            'premium.per_month' => '/ Month',
            'premium.choose_plan' => 'Choose Plan',
            'premium.learn_more' => 'Learn More',

            // dashboard
            'dashboard.welcome_back' => 'Welcome back',
            'dashboard.overview' => 'Here\'s an overview of your account.',
            'dashboard.account' => 'Account',
            'dashboard.profile_details' => 'Profile Details',
            'dashboard.username' => 'Username',
            'dashboard.email' => 'Email',
            'dashboard.member_since' => 'Member Since',
            'dashboard.subscription' => 'Subscription',
            'dashboard.current_plan' => 'Current Plan',
            'dashboard.plan' => 'Plan',
            'dashboard.status' => 'Status',
            'dashboard.active' => 'Active',
            'dashboard.valid_until' => 'Valid Until',
            'dashboard.change_plan' => 'Change Plan',
            'dashboard.no_subscription' => 'No active subscription',
            'dashboard.subscribe_now' => 'Subscribe Now',
            'dashboard.payments' => 'Payments',
            'dashboard.recent_activity' => 'Recent Activity',
            'dashboard.paid' => 'Paid',
            'dashboard.view_all_payments' => 'View All Payments',
            'dashboard.no_payments' => 'No payments yet',
            'dashboard.quick_links' => 'Quick Links',
            'dashboard.manage_plan' => 'Manage your plan',
            'dashboard.view_payment_history' => 'View payment history',
            'dashboard.browse_news' => 'Browse News',
            'dashboard.read_latest' => 'Read latest articles',

            // subscribe page
            'subscribe.premium_access' => 'Premium Access',
            'subscribe.unlock_content' => 'Unlock All Premium Content',
            'subscribe.get_unlimited' => 'Get unlimited access to in-depth news, exclusive analysis and breaking stories. Cancel anytime.',
            'subscribe.active_subscription' => 'Active Subscription',
            'subscribe.valid_until' => 'Valid until',
            'subscribe.will_replace' => 'Subscribing again will replace your current plan.',
            'subscribe.most_popular' => 'MOST POPULAR',
            'subscribe.month_access' => 'Month Access',
            'subscribe.months_access' => 'Months Access',
            'subscribe.total' => 'total',
            'subscribe.continue_to_payment' => 'Continue to Payment',
            'subscribe.secure_checkout' => 'Secure & simulated checkout. Cancel anytime.',
            'subscribe.feature_comparison' => 'Feature Comparison',
            'subscribe.feature' => 'Feature',
            'subscribe.free' => 'Free',
            'subscribe.premium' => 'Premium',
            'subscribe.free_articles' => 'Free articles',
            'subscribe.premium_articles' => 'Premium articles',
            'subscribe.exclusive_analysis' => 'Exclusive analysis',
            'subscribe.ad_free_experience' => 'Ad-free experience',
            'subscribe.breaking_news_alerts' => 'Breaking news alerts',
            'subscribe.early_access_content' => 'Early access content',
            'subscribe.select_plan_first' => 'Please select a plan first.',
            'subscribe.unlimited_premium' => 'Unlimited premium articles',
            'subscribe.exclusive_reports' => 'Exclusive analysis & reports',
            'subscribe.early_access_breaking' => 'Early access to breaking news',
            'subscribe.ad_free_reading' => 'Ad-free reading experience',

            // payments page
            'payments.title' => 'My Payments',
            'payments.description' => 'View your payment history and subscription receipts.',
            'payments.new_subscription' => 'New Subscription',
            'payments.no_payments_yet' => 'No payments yet',
            'payments.subscribe_to_see' => 'Subscribe to a plan to see your payment history here.',
            'payments.view_plans' => 'View Plans',
            'payments.showing' => 'Showing',
            'payments.result' => 'result(s)',
            'payments.view' => 'View',

            // search page
            'search.results_for' => 'Search results for',
            'search.news' => 'Search News',
            'search.results_found' => 'result(s) found',
            'search.enter_term' => 'Enter a search term to find news articles.',
            'search.no_articles_found' => 'No articles found',
            'search.try_different' => 'Try a different search term.',

            // auth pages
            'auth.stay_informed' => 'Stay informed. Stay ahead.',
            'auth.welcome_back' => 'Welcome back',
            'auth.please_sign_in' => 'Please sign in to your account to continue',
            'auth.enter_email' => 'Enter your email',
            'auth.enter_password' => 'Enter your password',
            'auth.or' => 'or',
            'auth.no_account' => 'Don\'t have an account?',
            'auth.register_now' => 'Register',
            'auth.create_account' => 'Create your account',
            'auth.join_nova' => 'Join Nova News and start exploring.',
            'auth.full_name' => 'Full Name',
            'auth.enter_full_name' => 'Enter your full name',
            'auth.enter_email_address' => 'Enter your email address',
            'auth.create_password' => 'Create a password',
            'auth.password_hint' => 'Must be at least 6 characters with a mix of letters, numbers & symbols.',
            'auth.confirm_password' => 'Confirm Password',
            'auth.confirm_your_password' => 'Confirm your password',
            'auth.already_have_account' => 'Already have an account?',
            'auth.sign_in' => 'Sign In',

            // validation messages
            'validation.fill_all' => 'Please fill in all required fields.',
            'validation.valid_email' => 'Please enter a valid email address.',
            'validation.password_length' => 'Password must be at least 6 characters long.',
            'validation.passwords_match' => 'Passwords do not match.',
            'validation.email_exists' => 'An account with that email already exists.',
            'validation.something_wrong' => 'Something went wrong while creating your account.',
            'validation.enter_email_password' => 'Please enter both email and password.',
            'validation.incorrect_credentials' => 'Incorrect email or password.',
            'validation.no_account_found' => 'No account found with that email. Please sign up first.',
            'validation.database_error' => 'Database error. Please try again later.',
        ],

        'my' => [
            'header.home' => 'မူလစာမျက်နှာ',
            'header.categories' => 'အမျိုးအစားများ',
            'header.search' => 'သတင်းရှာရန်...',
            'header.signin' => 'ဝင်ရောက်ရန်',
            'header.get_started' => 'စတင်ရန်',
            'header.admin_panel' => 'အက်မင်',
            'header.signout' => 'ထွက်ရန်',
            'header.go_premium' => 'ပရီမီယံသို့',
            'header.premium' => 'ပရီမီယံ',
            'header.my_dashboard' => 'ဒက်ရှ်ဘုတ်',
            'header.subscription' => 'စာရင်းသွင်းမှု',
            'header.my_payments' => 'ငွေပေးချေမှုများ',
            'header.member' => 'အသုံးပြုသူ',
            'header.administrator' => 'အက်မင်နစ်စထရေးတာ',

            'footer.copyright' => '&copy; 2026 နီဗားနယူးစ်။ သမ္မာတရားကို အလင်းပြခြင်း။ မူပိုင်ခွင့်များရရှိထားသည်။',

            'hero.premium_exclusive' => 'ပရီမီယံသီးသန့်',
            'hero.read_article' => 'ဆောင်းပါးဖတ်ရန်',
            'hero.unlock_article' => 'ဤဆောင်းပါးကိုဖွင့်ရန်',
            'hero.welcome_title' => 'နီဗားနယူးစ်မှကြိုဆိုပါသည်',
            'hero.welcome_desc' => 'ကမ္ဘာတဝှမ်းမှ နောက်ဆုံးရသတင်းများ၊ သီးသန့်ဆောင်းပါးများနှင့် နက်ရှိုင်းသောခွဲခြမ်းစိတ်ဖြာမှုများဖြင့် အသိပေးနေပါစေ။',

            'section.trending' => 'ယနေ့ခေတ်စားနေသော',
            'section.browse_categories' => 'အမျိုးအစားများကြည့်ရန်',
            'section.latest_news' => 'နောက်ဆုံးသတင်းများ',
            'section.premium_articles' => 'ပရီမီယံဆောင်းပါးများ',
            'section.view_all' => 'အားလုံးကြည့်ရန်',
            'section.editors_pick' => 'အယ်ဒီတာရွေးချယ်သော',

            'premium.title' => 'ပရီမီယံဝန်ဆောင်မှု',
            'premium.unlimited' => 'အကန့်အသတ်မရှိ ပရီမီယံဆောင်းပါးများ',
            'premium.exclusive' => 'သီးသန့်ခွဲခြမ်းစိတ်ဖြာမှုများနှင့် အစီရင်ခံစာများ',
            'premium.early_access' => 'သတင်းသစ်များကို ကြိုတင်ကြည့်ရှုခွင့်',
            'premium.ad_free' => 'ကြော်ငြာကင်းစင်သော ဖတ်ရှုမှုအတွေ့အကြုံ',
            'premium.starting_from' => 'စတင်နှုန်း',
            'premium.per_month' => '/လ',
            'premium.choose_plan' => 'အစီအစဉ်ရွေးချယ်ရန်',
            'premium.learn_more' => 'ပိုမိုလေ့လာရန်',

            'categories.title' => 'ထိပ်တန်းအမျိုးအစားများ',
            'categories.article' => 'ဆောင်းပါး',
            'categories.articles' => 'ဆောင်းပါးများ',

            'filter.all' => 'အားလုံး',
            'filter.free' => 'အခမဲ့',
            'filter.premium' => 'ပရီမီယံ',

            'badge.premium' => 'ပရီမီယံ',
            'badge.free_article' => 'အခမဲ့ဆောင်းပါး',

            'empty.no_articles' => 'ဆောင်းပါးများမရှိသေးပါ။',
            'empty.no_trending' => 'ခေတ်စားနေသောဆောင်းပါးများမရှိသေးပါ။',
            'empty.no_premium' => 'ပရီမီယံဆောင်းပါးများမရှိသေးပါ။',
            'empty.no_categories' => 'အမျိုးအစားများမရှိသေးပါ။',
            'empty.no_results' => 'ဤအမျိုးအစားတွင် ဆောင်းပါးများမရှိသေးပါ။',

            'article.back_to_news' => 'သတင်းများသို့ပြန်ရန်',
            'article.related_articles' => 'ဆက်စပ်ဆောင်းပါးများ',
            'article.premium_locked_title' => 'ပရီမီယံဆောင်းပါး',
            'article.premium_locked_desc' => 'ဤဆောင်းပါးသည် ပရီမီယံစာရင်းသွင်းသူများအတွက်သာဖြစ်သည်။ အပြည့်အဝဖတ်ရှုရန် စာရင်းသွင်းပါ။',
            'article.go_premium' => 'ပရီမီယံသို့',
            'article.sign_in_to_subscribe' => 'စာရင်းသွင်းရန် အကောင့်ဝင်ပါ',

            'subscribe.title' => 'သင့်အစီအစဉ်ကိုရွေးချယ်ပါ',
            'subscribe.per_month' => '/လ',
            'subscribe.subscribe' => 'စာရင်းသွင်းရန်',
            'subscribe.current_plan' => 'လက်ရှိအစီအစဉ်',

            'auth.sign_in' => 'အကောင့်ဝင်ရန်',
            'auth.sign_up' => 'စတင်ရန်',
            'auth.email' => 'အီးမေးလ်',
            'auth.password' => 'စကားဝှက်',
            'auth.username' => 'အသုံးပြုသူအမည်',

            'search.title' => 'ရှာဖွေမှုရလဒ်များ',
            'search.no_results' => 'အတွက်ရလဒ်များမရှိပါ',
            'search.placeholder' => 'သတင်းရှာရန်...',

            'dashboard.title' => 'ဒက်ရှ်ဘုတ်',
            'payments.title' => 'ငွေပေးချေမှုများ',

            'all_posts.all' => 'သတင်းအားလုံး',
            'all_posts.free' => 'အခမဲ့သတင်းများ',
            'all_posts.premium' => 'ပရီမီယံသတင်းများ',
            'all_posts.total' => 'စုစုပေါင်း ဆောင်းပါး',
            'all_posts.prev' => 'ရှေ့သို့',
            'all_posts.next' => 'နောက်သို့',

            // article page
            'article.404' => 'ဆောင်းပါးမတွေ့ပါ။',
            'article.back_to_home' => 'မူလစာမျက်နှာသို့ပြန်ရန်',

            // premium sidebar
            'sidebar.premium_access' => 'ပရီမီယံဝန်ဆောင်မှု',
            'sidebar.unlimited' => 'အကန့်အသတ်မရှိ ပရီမီယံဆောင်းပါးများ',
            'sidebar.exclusive' => 'သီးသန့်ခွဲခြမ်းစိတ်ဖြာမှုများနှင့် အစီရင်ခံစာများ',
            'sidebar.early_access' => 'သတင်းသစ်များကို ကြိုတင်ကြည့်ရှုခွင့်',
            'sidebar.ad_free' => 'ကြော်ငြာကင်းစင်သော အတွေ့အကြုံ',
            'sidebar.starting_from' => 'စတင်နှုန်း',
            'sidebar.per_month' => '/လ',
            'sidebar.choose_plan' => 'အစီအစဉ်ရွေးချယ်ရန်',

            // editor's pick
            'editor_pick.title' => 'အယ်ဒီတာရွေးချယ်သော',
            'editor_pick.premium' => 'ပရီမီယံ',
            'editor_pick.free' => 'အခမဲ့',

            // latest news sidebar
            'latest_news.title' => 'နောက်ဆုံးသတင်းများ',

            // article page
            'article.back_to_news' => 'သတင်းများသို့ပြန်ရန်',
            'article.related_articles' => 'ဆက်စပ်ဆောင်းပါးများ',
            'article.premium_locked_title' => 'ပရီမီယံဆောင်းပါး',
            'article.premium_locked_desc' => 'ဤဆောင်းပါးသည် ပရီမီယံစာရင်းသွင်းသူများအတွက်သာဖြစ်သည်။ အပြည့်အဝဖတ်ရှုရန် စာရင်းသွင်းပါ။',
            'article.go_premium' => 'ပရီမီယံသို့',
            'article.sign_in_to_subscribe' => 'စာရင်းသွင်းရန် အကောင့်ဝင်ပါ',

            // premium
            'premium.access' => 'ပရီမီယံဝန်ဆောင်မှု',
            'premium.unlimited_articles' => 'အကန့်အသတ်မရှိ ပရီမီယံဆောင်းပါးများ',
            'premium.exclusive_analysis' => 'သီးသန့်ခွဲခြမ်းစိတ်ဖြာမှုများနှင့် အစီရင်ခံစာများ',
            'premium.early_access_news' => 'သတင်းသစ်များကို ကြိုတင်ကြည့်ရှုခွင့်',
            'premium.ad_free_experience' => 'ကြော်ငြာကင်းစင်သော ဖတ်ရှုမှုအတွေ့အကြုံ',
            'premium.starting_from' => 'စတင်နှုန်း',
            'premium.per_month' => '/လ',
            'premium.choose_plan' => 'အစီအစဉ်ရွေးချယ်ရန်',
            'premium.learn_more' => 'ပိုမိုလေ့လာရန်',

            // dashboard
            'dashboard.welcome_back' => 'ပြန်လည်ကြိုဆိုပါသည်',
            'dashboard.overview' => 'သင့်အကောင့်အကြောင်း အကျဉ်းချုပ်ဖြစ်သည်။',
            'dashboard.account' => 'အကောင့်',
            'dashboard.profile_details' => 'ပရိုဖိုင်အသေးစိတ်',
            'dashboard.username' => 'အသုံးပြုသူအမည်',
            'dashboard.email' => 'အီးမေးလ်',
            'dashboard.member_since' => 'အသုံးပြုသည့်အချိန်',
            'dashboard.subscription' => 'စာရင်းသွင်းမှု',
            'dashboard.current_plan' => 'လက်ရှိအစီအစဉ်',
            'dashboard.plan' => 'အစီအစဉ်',
            'dashboard.status' => 'အခြေအနေ',
            'dashboard.active' => 'ဖွင့်ထားသည်',
            'dashboard.valid_until' => 'သက်တမ်းကုန်ဆုံးချိန်',
            'dashboard.change_plan' => 'အစီအစဉ်ပြောင်းရန်',
            'dashboard.no_subscription' => 'စာရင်းသွင်းမှုမရှိသေးပါ',
            'dashboard.subscribe_now' => 'စာရင်းသွင်းရန်',
            'dashboard.payments' => 'ငွေပေးချေမှုများ',
            'dashboard.recent_activity' => 'နောက်ဆုံးလုပ်ဆောင်ချက်များ',
            'dashboard.paid' => 'ပေးချေပြီး',
            'dashboard.view_all_payments' => 'ငွေပေးချေမှုအားလုံးကြည့်ရန်',
            'dashboard.no_payments' => 'ငွေပေးချေမှုမရှိသေးပါ',
            'dashboard.quick_links' => 'လျင်မြန်သောလင့်ခ်များ',
            'dashboard.manage_plan' => 'သင့်အစီအစဉ်ကို စီမံရန်',
            'dashboard.view_payment_history' => 'ငွေပေးချေမှုမှတ်တမ်းကြည့်ရန်',
            'dashboard.browse_news' => 'သတင်းများကြည့်ရန်',
            'dashboard.read_latest' => 'နောက်ဆုံးဆောင်းပါးများဖတ်ရန်',

            // subscribe page
            'subscribe.premium_access' => 'ပရီမီယံဝန်ဆောင်မှု',
            'subscribe.unlock_content' => 'ပရီမီယံအကြောင်းအရာအားလုံးကို ဖွင့်ရန်',
            'subscribe.get_unlimited' => 'နက်ရှိုင်းသောသတင်းများ၊ သီးသန့်ခွဲခြမ်းစိတ်ဖြာမှုများနှင့် သတင်းသစ်များကို အကန့်အသတ်မရှိ ကြည့်ရှုပါ။ အချိန်မရွေးပယ်ဖျက်နိုင်ပါသည်။',
            'subscribe.active_subscription' => 'ဖွင့်ထားသည့်စာရင်းသွင်းမှု',
            'subscribe.valid_until' => 'သက်တမ်းကုန်ဆုံးချိန်',
            'subscribe.will_replace' => 'ထပ်မံစာရင်းသွင်းပါက လက်ရှိအစီအစဉ်ကို အစားထိုးမည်ဖြစ်သည်။',
            'subscribe.most_popular' => 'အရောင်းရဆုံး',
            'subscribe.month_access' => 'လအရေအတွက် ခွင့်ပြုချက်',
            'subscribe.months_access' => 'လအရေအတွက် ခွင့်ပြုချက်',
            'subscribe.total' => 'စုစုပေါင်း',
            'subscribe.continue_to_payment' => 'ငွေပေးချေမှုသို့ဆက်လက်ရန်',
            'subscribe.secure_checkout' => 'လုံခြုံပြီး စမ်းသုံးနိုင်သောငွေပေးချေမှု။ အချိန်မရွေးပယ်ဖျက်နိုင်ပါသည်။',
            'subscribe.feature_comparison' => 'လုပ်ဆောင်ချက်နှိုင်းယှဉ်ချက်',
            'subscribe.feature' => 'လုပ်ဆောင်ချက်',
            'subscribe.free' => 'အခမဲ့',
            'subscribe.premium' => 'ပရီမီယံ',
            'subscribe.free_articles' => 'အခမဲ့ဆောင်းပါးများ',
            'subscribe.premium_articles' => 'ပရီမီယံဆောင်းပါးများ',
            'subscribe.exclusive_analysis' => 'သီးသန့်ခွဲခြမ်းစိတ်ဖြာမှု',
            'subscribe.ad_free_experience' => 'ကြော်ငြာကင်းစင်သောအတွေ့အကြုံ',
            'subscribe.breaking_news_alerts' => 'သတင်းသစ်သတိပေးချက်များ',
            'subscribe.early_access_content' => 'ကြိုတင်ကြည့်ရှုခွင့်ရရှိသော အကြောင်းအရာ',
            'subscribe.select_plan_first' => 'ကျေးဇူးပြု၍ အစီအစဉ်တစ်ခုကို ရွေးချယ်ပါ။',
            'subscribe.unlimited_premium' => 'အကန့်အသတ်မရှိ ပရီမီယံဆောင်းပါးများ',
            'subscribe.exclusive_reports' => 'သီးသန့်ခွဲခြမ်းစိတ်ဖြာမှုများနှင့် အစီရင်ခံစာများ',
            'subscribe.early_access_breaking' => 'သတင်းသစ်များကို ကြိုတင်ကြည့်ရှုခွင့်',
            'subscribe.ad_free_reading' => 'ကြော်ငြာကင်းစင်သော ဖတ်ရှုမှုအတွေ့အကြုံ',

            // payments page
            'payments.title' => 'ငွေပေးချေမှုများ',
            'payments.description' => 'သင့်ငွေပေးချေမှုမှတ်တမ်းနှင့် စာရင်းသွင်းမှုပြေစာများကို ကြည့်ရှုပါ။',
            'payments.new_subscription' => 'စာရင်းသွင်းမှုအသစ်',
            'payments.no_payments_yet' => 'ငွေပေးချေမှုမရှိသေးပါ',
            'payments.subscribe_to_see' => 'သင့်ငွေပေးချေမှုမှတ်တမ်းကို ကြည့်ရန် အစီအစဉ်တစ်ခုသို့ စာရင်းသွင်းပါ။',
            'payments.view_plans' => 'အစီအစဉ်များကြည့်ရန်',
            'payments.showing' => 'ပြသနေသည်',
            'payments.result' => 'ရလဒ်',
            'payments.view' => 'ကြည့်ရန်',

            // search page
            'search.results_for' => 'အတွက်ရလဒ်များ',
            'search.news' => 'သတင်းရှာရန်',
            'search.results_found' => 'ရလဒ်များတွေ့ရှိပါပြီ',
            'search.enter_term' => 'သတင်းဆောင်းပါးများရှာရန် ရှာဖွေစကားလုံးထည့်ပါ။',
            'search.no_articles_found' => 'ဆောင်းပါးများမတွေ့ပါ',
            'search.try_different' => 'အခြားရှာဖွေစကားလုံးဖြင့် ထပ်ကြိုးစားပါ။',

            // auth pages
            'auth.stay_informed' => 'သတင်းအချက်အလက်ရရှိနေပါစေ။ ရှေ့ဆက်နေပါ။',
            'auth.welcome_back' => 'ပြန်လည်ကြိုဆိုပါသည်',
            'auth.please_sign_in' => 'ဆက်လက်အသုံးပြုရန် သင့်အကောင့်သို့ ဝင်ရောက်ပါ။',
            'auth.enter_email' => 'သင့်အီးမေးလ်ထည့်ပါ',
            'auth.enter_password' => 'သင့်စကားဝှက်ထည့်ပါ',
            'auth.or' => 'သို့',
            'auth.no_account' => 'အကောင့်မရှိဘူးလား?',
            'auth.register_now' => 'စာရင်းသွင်းရန်',
            'auth.create_account' => 'သင့်အကောင့်ဖန်တီးရန်',
            'auth.join_nova' => 'Nova News တွင် ပါဝင်ပြီး စတင်ရှာဖွေပါ။',
            'auth.full_name' => 'အမည်အပြည့်အစုံ',
            'auth.enter_full_name' => 'သင့်အမည်အပြည့်အစုံထည့်ပါ',
            'auth.enter_email_address' => 'သင့်အီးမေးလ်လိပ်စာထည့်ပါ',
            'auth.create_password' => 'စကားဝှက်ဖန်တီးရန်',
            'auth.password_hint' => 'အနည်းဆုံး အက္ခရာ ၆ လုံး၊ အက္ခရာများ၊ ဂဏန်းများနှင့် သင်္ကေတများ ပါဝင်ရပါမည်။',
            'auth.confirm_password' => 'စကားဝှက်အတည်ပြုရန်',
            'auth.confirm_your_password' => 'သင့်စကားဝှက်ကို အတည်ပြုပါ',
            'auth.already_have_account' => 'အကောင့်ရှိပြီးသားလား?',
            'auth.sign_in' => 'ဝင်ရောက်ရန်',

            // validation messages
            'validation.fill_all' => 'ကျေးဇူးပြု၍ လိုအပ်သည့်အကွက်အားလုံးကို ဖြည့်ပါ။',
            'validation.valid_email' => 'ကျေးဇူးပြု၍ မှန်ကန်သည့် အီးမေးလ်လိပ်စာထည့်ပါ။',
            'validation.password_length' => 'စကားဝှက်သည် အနည်းဆုံး အက္ခရာ ၆ လုံး ရှိရပါမည်။',
            'validation.passwords_match' => 'စကားဝှက်များ မကိုက်ညီပါ',
            'validation.email_exists' => 'ဤအီးမေးလ်ဖြင့် အကောင့်ရှိပြီးသားဖြစ်သည်။',
            'validation.something_wrong' => 'သင့်အကောင့်ဖန်တီးသည့်အခါ အမှားတစ်ခုခုဖြစ်ပေါ်ခဲ့သည်။',
            'validation.enter_email_password' => 'ကျေးဇူးပြု၍ အီးမေးလ်နှင့် စကားဝှက် နှစ်ခုလုံးထည့်ပါ။',
            'validation.incorrect_credentials' => 'အီးမေးလ် သို့မဟုတ် စကားဝှက် မှားယွင်းနေသည်။',
            'validation.no_account_found' => 'ဤအီးမေးလ်ဖြင့် အကောင့်မတွေ့ပါ။ ကျေးဇူးပြု၍ အရင်စာရင်းသွင်းပါ။',
            'validation.database_error' => 'ဒေတာဘေ့စ်အမှား။ ကျေးဇူးပြု၍ နောက်မှ ထပ်ကြိုးစားပါ။',
        ],
    ];
}
