@extends('components.layouts.page')

@section('title', 'تکنوراه | سیستم هوشمندسازی تعامل با مشتریان')
@section('description', 'با تکنوراه، فرآیندهای ارتباط با مشتریان را هوشمندسازی کنید، سرنخ‌ها را به مشتریان وفادار تبدیل کرده و رشد کسب‌وکار خود را سرعت ببخشید.')
@section('keywords', 'اتوماسیون، تکنوراه، سفر مشتری، مدیریت سرنخ، افزایش نرخ تبدیل, CRM')
@section('author', 'تکنوراه')

@section('content')

    <main class="pt-16 lg:pt-20">

        <section id="home" class="relative bg-gradient-to-b from-green-50 to-gray-50 pt-20 pb-24 sm:pt-28 sm:pb-32 lg:pt-32">
            <div class="container mx-auto px-6">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div class="text-center lg:text-right">
                        <h1 class="text-4xl md:text-5xl lg:text-4xl font-extrabold text-gray-800 leading-tight">
                            سیستم <span class="text-green-600 lg:text-5xl">هوشمندسازی تعامل</span> با مشتریان
                        </h1>
                        <p class="mt-6 text-lg text-gray-600 max-w-2xl mx-auto lg:mx-0">
                            به‌صورت خودکار سفر مشتری را طراحی کنید، سرنخ‌های ارزشمند را شناسایی و پرورش دهید و با ارسال پیام‌های هوشمند در زمان مناسب، نرخ تبدیل خود را متحول کنید.
                        </p>
                        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                            <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3.5 bg-green-600 text-white font-semibold rounded-lg shadow-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-transform transform hover:scale-105">
                                ورود به پنل مارکتینگ
                            </a>
                            {{-- UPDATED BUTTON: This button now opens the modal --}}
                            <button @click="isDemoModalOpen = true" class="w-full sm:w-auto px-8 py-3.5 bg-white text-green-600 font-semibold rounded-lg border border-gray-200 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition">
                                درخواست مشاوره و دمو
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-center">
                        <img src="/assets/images/hero.jpg" alt="پلتفرم اتوماسیون تکنوراه" class="rounded-2xl shadow-2xl max-w-md w-full ring-1 ring-gray-900/10 transform hover:scale-105 transition-transform duration-500">
                    </div>
                </div>
            </div>
        </section>

        <div
            x-show="isDemoModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm"
            style="display: none;"
        >
            <div
                @click.outside="isDemoModalOpen = false"
                x-show="isDemoModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-90"
                class="relative bg-white w-full max-w-md p-8 rounded-2xl shadow-xl text-center"
            >
                <button @click="isDemoModalOpen = false" class="absolute top-4 left-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <h3 class="text-2xl font-bold text-gray-800">درخواست مشاوره و دمو</h3>
                <p class="mt-2 text-gray-600">یکی از راه‌های زیر را برای ارتباط انتخاب کنید. مشتاقانه منتظر صحبت با شما هستیم!</p>

                <div class="mt-8 space-y-4">

                    <a href="https://t.me/+989210327407" target="_blank" class="group flex items-center justify-center w-full px-5 py-4 bg-sky-50 border-2 border-sky-200 rounded-lg hover:bg-sky-100 hover:border-sky-400 transition-all duration-300">
                        <x-icons.telegram />
                        <span class="mr-3 font-semibold text-lg text-gray-700 group-hover:text-gray-900">ارسال پیام در تلگرام</span>
                    </a>
                    <a href="https://wa.me/989210327407" target="_blank" class="group flex items-center justify-center w-full px-5 py-4 bg-green-50 border-2 border-green-200 rounded-lg hover:bg-green-100 hover:border-green-400 transition-all duration-300">
                        <x-icons.whatsapp/>
                        <span class="mr-3 font-semibold text-lg text-gray-700 group-hover:text-gray-900">ارسال پیام در واتس‌اپ</span>
                    </a>

                    <a href="mailto:info@technorah.com" class="group flex items-center justify-center w-full px-5 py-4 bg-gray-100 border-2 border-gray-200 rounded-lg hover:bg-gray-200 hover:border-gray-400 transition-all duration-300">
                        <x-icons.mail/>
                        <span class="mr-3 font-semibold text-lg text-gray-700 group-hover:text-gray-900">ارسال ایمیل به ما</span>
                    </a>
                </div>
            </div>
        </div>

        <section id="features" class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center max-w-3xl mx-auto">
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-800">چرا کسب‌وکارهای پیشرو از تکنوراه استفاده می‌کنند؟</h2>
                    <p class="mt-4 text-lg text-gray-600">تکنوراه ابزارهای قدرتمندی برای درک، تعامل و تبدیل مشتریان در اختیار شما قرار می‌دهد.</p>
                </div>
                <div class="mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="bg-gray-50 p-8 rounded-xl border border-gray-200/80 hover:border-green-300 hover:shadow-lg transition-all duration-300">
                        <div class="bg-green-100 text-green-600 w-12 h-12 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <h3 class="mt-5 font-bold text-xl text-gray-800">اتوماسیون گردش‌کارها</h3>
                        <p class="mt-2 text-gray-600">گردش‌کارهای پیچیده برای ارسال ایمیل و پیامک را بر اساس رفتار کاربران تعریف کنید.</p>
                    </div>
                    <div class="bg-gray-50 p-8 rounded-xl border border-gray-200/80 hover:border-green-300 hover:shadow-lg transition-all duration-300">
                        <div class="bg-green-100 text-green-600 w-12 h-12 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10s5 2 5 2a8 8 0 012.657 6.657z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.343 14.657a4 4 0 015.314-5.314"></path></svg>
                        </div>
                        <h3 class="mt-5 font-bold text-xl text-gray-800">پرورش هوشمند سرنخ</h3>
                        <p class="mt-2 text-gray-600">سرنخ‌ها را بر اساس امتیاز (Lead Score) اولویت‌بندی کرده و تا مرحله خرید هدایت کنید.</p>
                    </div>
                    <div class="bg-gray-50 p-8 rounded-xl border border-gray-200/80 hover:border-green-300 hover:shadow-lg transition-all duration-300">
                        <div class="bg-green-100 text-green-600 w-12 h-12 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <h3 class="mt-5 font-bold text-xl text-gray-800">تحلیل و گزارش‌گیری</h3>
                        <p class="mt-2 text-gray-600">عملکرد کمپین‌ها را با گزارش‌های دقیق و بصری تحلیل کرده و استراتژی خود را بهینه کنید.</p>
                    </div>
                    <div class="bg-gray-50 p-8 rounded-xl border border-gray-200/80 hover:border-green-300 hover:shadow-lg transition-all duration-300">
                        <div class="bg-green-100 text-green-600 w-12 h-12 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
                        </div>
                        <h3 class="mt-5 font-bold text-xl text-gray-800">یکپارچگی و API</h3>
                        <p class="mt-2 text-gray-600">تکنوراه را به سادگی به سایر ابزارهای خود متصل کرده و یک اکوسیستم یکپارچه بسازید.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="advanced-features" class="py-20 bg-gray-50">
            <div class="container mx-auto px-6">
                <div class="text-center max-w-3xl mx-auto">
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-800">ابزارهای قدرتمند برای بهینه‌سازی سفر مشتری</h2>
                    <p class="mt-4 text-lg text-gray-600">از ردیابی دقیق رفتار کاربران تا بخش‌بندی هوشمند، همه چیز برای رشد شما آماده است.</p>
                </div>
                <div class="mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-white p-8 rounded-xl border border-gray-200/80">
                        <h3 class="font-semibold text-lg text-green-600">طراحی سفر مشتری (Customer Journey)</h3>
                        <p class="mt-2 text-gray-600">مسیر حرکت مشتری از اولین نقطه تماس تا وفاداری را به صورت بصری طراحی، اجرا و بهینه‌سازی کنید.</p>
                    </div>
                    <div class="bg-white p-8 rounded-xl border border-gray-200/80">
                        <h3 class="font-semibold text-lg text-green-600">بخش‌بندی هوشمند و امتیازدهی سرنخ</h3>
                        <p class="mt-2 text-gray-600">مخاطبان خود را بر اساس داده‌های دموگرافیک و رفتاری به گروه‌های دقیق تقسیم کرده و سرنخ‌ها را امتیازدهی کنید.</p>
                    </div>
                    <div class="bg-white p-8 rounded-xl border border-gray-200/80">
                        <h3 class="font-semibold text-lg text-green-600">ردیاب لینک (Link Tracker)</h3>
                        <p class="mt-2 text-gray-600">عملکرد هر لینک در ایمیل‌ها و پیامک‌ها را به دقت ردیابی کرده و بفهمید کدام محتوا بیشترین تعامل را ایجاد می‌کند.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="testimonials" class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center max-w-3xl mx-auto">
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-800">صدای مشتریان ما</h2>
                    <p class="mt-4 text-lg text-gray-600">ببینید کسب‌وکارهای دیگر چگونه با تکنوراه به اهداف خود رسیده‌اند.</p>
                </div>
                <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <div class="bg-gray-50 p-8 rounded-xl border border-gray-200/80">
                        <p class="text-gray-700 leading-relaxed">"با استفاده از تکنوراه، توانستیم تعامل با مشتریان بالقوه را %40 افزایش دهیم. ابزارهای اتوماسیون و تحلیل دقیق آن، دید ما را به کسب‌وکارمان تغییر داد."</p>
                        <div class="mt-5 pt-5 border-t border-gray-200">
                            <p class="font-bold text-gray-800">علی رضایی</p>
                            <p class="text-sm text-gray-500">مدیر رشد در شرکت نوین‌پرداز</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-8 rounded-xl border border-gray-200/80">
                        <p class="text-gray-700 leading-relaxed">"یکپارچگی تکنوراه با سیستم CRM ما فوق‌العاده بود. حالا تیم فروش ما سرنخ‌های بسیار باکیفیت‌تری دریافت می‌کند و نرخ بستن قراردادها به شکل چشمگیری بهتر شده است."</p>
                        <div class="mt-5 pt-5 border-t border-gray-200">
                            <p class="font-bold text-gray-800">مریم احمدی</p>
                            <p class="text-sm text-gray-500">مدیر عملیات در استارتاپ راهکاران</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="faq" class="py-20 bg-gray-50">
            <div class="container mx-auto px-6">
                <div class="text-center max-w-3xl mx-auto">
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-800">پاسخ به سوالات شما</h2>
                    <p class="mt-4 text-lg text-gray-600">هرآنچه برای شروع باید بدانید.</p>
                </div>
                <div class="mt-12 max-w-3xl mx-auto space-y-4">
                    <div class="bg-white p-6 rounded-lg border border-gray-200/80">
                        <h3 class="font-bold text-lg text-gray-800">سیستم اتوماسیون تکنوراه دقیقاً چه کاری انجام می‌دهد؟</h3>
                        <p class="mt-2 text-gray-600">تکنوراه به شما اجازه می‌دهد تا فرآیندهای تکراری در ارتباط با مشتریان (مانند ارسال ایمیل خوشامدگویی، پیگیری‌ها و...) را خودکار کنید. این کار باعث صرفه‌جویی در زمان و افزایش دقت در تعاملات می‌شود.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg border border-gray-200/80">
                        <h3 class="font-bold text-lg text-gray-800">آیا امکان اتصال تکنوراه به سایر نرم‌افزارها وجود دارد؟</h3>
                        <p class="mt-2 text-gray-600">بله، تکنوراه دارای یک API قدرتمند و مستندات کامل است که به شما اجازه می‌دهد آن را به راحتی به CRM، فروشگاه آنلاین و سایر ابزارهای مورد استفاده خود متصل کنید.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg border border-gray-200/80">
                        <h3 class="font-bold text-lg text-gray-800">پشتیبانی به چه صورت ارائه می‌شود؟</h3>
                        <p class="mt-2 text-gray-600">ما از طریق تیکت، ایمیل و تماس تلفنی پشتیبانی کامل فنی و مشاوره‌ای را به تمام کاربران خود ارائه می‌دهیم تا اطمینان حاصل کنیم که شما بهترین نتیجه را از پلتفرم ما می‌گیرید.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="cta" class="bg-green-600">
            <div class="container mx-auto px-6 py-20 text-center">
                <h2 class="text-3xl lg:text-4xl font-bold text-white">آماده‌اید رشد کسب‌وکارتان را هوشمند کنید؟</h2>
                <p class="mt-4 text-lg text-green-100 max-w-2xl mx-auto">همین امروز به جمع مشتریان پیشرو تکنوراه بپیوندید و اولین قدم را برای تحول در تعامل با مشتریان خود بردارید.</p>
                <div class="mt-8">
                    <button @click="isDemoModalOpen = true" class="px-10 py-4 bg-white text-green-600 font-bold rounded-lg shadow-lg hover:bg-gray-100 transition-transform transform hover:scale-105">
                        درخواست مشاوره و دمو
                    </button>
                </div>
            </div>
        </section>

    </main>
@endsection
