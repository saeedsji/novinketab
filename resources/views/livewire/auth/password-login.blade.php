@section('title', ' ورود به پنل نوین کتاب')

<div dir="rtl">
    <div class="flex min-h-screen">
        {{-- Primary Content Area --}}
        <div class="flex flex-1 flex-col justify-center bg-surface-main px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm lg:w-96">

                {{-- Header --}}
                <div>
                    <div class="flex justify-center">
                        {{-- You can place your logo here e.g. <img class="h-10 w-auto" src="/logo.svg" alt="Company"> --}}
                    </div>
                    <h2 class="mt-8 text-2xl font-bold leading-9 tracking-tight text-text-main">
                        ورود به پنل نوین کتاب
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-text-muted">
                        لطفا اطلاعات خود را جهت ورود وارد کنید.
                    </p>
                </div>

                {{-- Login Form --}}
                <div class="mt-10">
                    <form wire:submit="login" class="space-y-6">

                        {{-- Email or Phone Input --}}
                        <div>
                            <label for="user" class="form-label">شماره موبایل یا ایمیل</label>
                            <div class="mt-2">
                                <input id="user" name="user" type="text" wire:model="user" dir="ltr" autofocus
                                       placeholder="example@gmail.com | 09..."
                                       class="form-input @error('user') border-danger-500 focus:border-danger-500 focus:ring-danger-500 @enderror">
                            </div>
                            @error('user') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        {{-- Password Input --}}
                        <div>
                            <label for="password" class="form-label">رمزعبور</label>
                            <div class="mt-2">
                                <input id="password" name="password" type="password" wire:model="password" dir="ltr"
                                       placeholder="••••••••"
                                       class="form-input @error('password') border-danger-500 focus:border-danger-500 focus:ring-danger-500 @enderror">
                            </div>
                            @error('password') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        {{-- Captcha Input --}}
                        <div>
                            <label for="captcha" class="form-label">{{ $captchaQuestion }}</label>
                            <div class="relative mt-2">
                                <input id="captcha" name="captcha" type="number" wire:model="captcha" dir="ltr"
                                       placeholder="{{$captchaQuestion}}"
                                       class="form-input @error('captcha') border-danger-500 focus:border-danger-500 focus:ring-danger-500 @enderror">
                                <button type="button" wire:click="generateCaptcha" class="absolute inset-y-0 right-0 flex items-center px-3 text-text-muted hover:text-primary-600 transition-colors" aria-label="ایجاد سوال جدید">
                                    <x-icons.refresh class="h-4 w-4"/>
                                </button>
                            </div>
                            @error('captcha') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        {{-- Rules Checkbox and Agreement --}}
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-x-3">
                                    <input id="rule" type="checkbox" wire:model="rule" name="rule" class="form-checkbox">
                                    <label for="rule" class="form-label !mb-0 cursor-pointer">قوانین را می‌پذیرم</label>
                                </div>
                                <x-dialog wire:model="showModal">
                                    <x-dialog.open>
                                        <button type="button" class="btn-link text-sm">
                                            مشاهده قوانین
                                        </button>
                                    </x-dialog.open>
                                    <x-dialog.panel>
                                        <div class="prose prose-base mt-5 max-w-none text-text-muted leading-7">
                                            <h3 class="text-text-main">قوانین و مقررات استفاده از پنل مدیریتی نوین کتاب</h3>
                                            <p>استفاده از پنل مدیریت نوین کتاب به‌منزله پذیرش کامل این قوانین است. هدف از تدوین این موارد، حفظ امنیت، انسجام عملکرد تیم و صیانت از داده‌های کاربران نهایی است.</p>

                                            <h4 class="text-text-main">۱. دسترسی، امنیت و مالکیت حساب</h4>
                                            <ul>
                                                <li>اطلاعات ورود (نام کاربری و رمز عبور) کاملاً محرمانه است و مسئولیت حفظ آن بر عهده شماست.</li>
                                                <li>کلیه فعالیت‌هایی که از طریق حساب شما انجام می‌شود به حساب شما منظور خواهد شد.</li>
                                                <li>در صورت مشاهده هرگونه دسترسی مشکوک یا غیرمجاز، موظف‌اید سریعاً تیم پشتیبانی نوین کتاب را مطلع نمایید.</li>
                                            </ul>

                                            <h4 class="text-text-main">۲. استفاده مسئولانه از سیستم</h4>
                                            <ul>
                                                <li>پنل صرفاً برای وظایف شغلی مرتبط با نوین کتاب مجاز است.</li>
                                                <li>هرگونه تلاش برای نفوذ به بخش‌های غیردر دسترس، دستکاری داده‌ها یا ایجاد اختلال در عملکرد سیستم تخلف محسوب می‌شود.</li>
                                                <li>دسترسی به اطلاعات کاربران نهایی فقط در صورت داشتن مجوز مشخص و در چارچوب وظایف حرفه‌ای مجاز است.</li>
                                            </ul>

                                            <h4 class="text-text-main">۳. مسئولیت قانونی در استفاده از سیستم</h4>
                                            <ul>
                                                <li>استفاده از پنل نوین کتاب صرفاً در چارچوب قوانین جاری کشور جمهوری اسلامی ایران مجاز است.</li>
                                                <li>مسئولیت کامل محتوای پیام‌های ارسالی (اعم از ایمیل، پیامک، پوش نوتیفیکیشن یا سایر کانال‌های ارتباطی) بر عهده صاحب حساب کاربری است.</li>
                                                <li>هرگونه ارسال پیام تبلیغاتی بدون رضایت مخاطب (Spam) یا نقض قوانین مربوط به حریم خصوصی و ارتباطات الکترونیکی، تخلف محسوب می‌شود و عواقب آن متوجه کاربر خواهد بود.</li>
                                                <li>نوین کتاب در قبال محتوای ارسال‌شده یا تبعات حقوقی ناشی از آن، هیچ‌گونه مسئولیتی نداشته و در صورت بروز تخلف، دسترسی کاربر به‌صورت موقت یا دائم مسدود خواهد شد.</li>
                                            </ul>

                                            <h4 class="text-text-main">۴. پیامدهای تخلف</h4>
                                            <p>نقض هر یک از بندهای فوق می‌تواند منجر به محدودیت یا قطع دسترسی، پیگرد سازمانی یا در موارد جدی، پیگیری قانونی گردد.</p>
                                            <p>این قوانین ممکن است به‌روزرسانی شوند. لطفاً به‌طور منظم این بخش را بررسی کنید. از همکاری حرفه‌ای شما با نوین کتاب سپاسگزاریم.</p>
                                        </div>
                                    </x-dialog.panel>
                                </x-dialog>
                            </div>
                            @error("rule") <p class="form-error">{{ $message }}</p> @enderror
                        </div>


                        {{-- Submit Button --}}
                        <div>
                            <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled" wire:target="login">
                                ورود به پنل
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        {{-- Image Panel --}}
        <div class="relative hidden w-0 flex-1 lg:block">
            <img class="absolute inset-0 h-full w-full object-cover" src="/assets/images/auth-hero.jpg" alt="A modern office building">
        </div>
    </div>
</div>
