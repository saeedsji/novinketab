
<nav class="flex flex-1 flex-col">
    <ul role="list" class="flex flex-1 flex-col gap-y-7">
        <li>
            <ul role="list" class="space-y-1">
                <x-panel.nav-link href="{{ route('dashboard.index') }}" :active=" request()->is('admin/dashboard')">
                    <x-icons.home/>
                    داشبورد
                </x-panel.nav-link>

                @can('مدیریت کاربران')
                    <x-panel.nav-link href="{{ route('user.index') }}" :active=" request()->is('admin/user')">
                        <x-icons.users/>
                        کاربران
                    </x-panel.nav-link>
                @endcan

                @can('مدیریت کتاب‌ها')
                    <x-panel.nav-link href="{{ route('book.index') }}"
                                      :active=" request()->is('admin/book')">
                        <x-icons.book/>
                        کتاب‌ها
                    </x-panel.nav-link>
                @endcan

                @can('مدیریت ایمپورت از پلتفرم ها')
                    <x-panel.nav-link href="{{ route('payment.import') }}"
                                      :active=" request()->is('admin/payment/import')">
                        <x-icons.download/>
                        ایمپورت از پلتفرم ها
                    </x-panel.nav-link>
                @endcan

                @can('مدیریت پرداخت ها')
                    <x-panel.nav-link href="{{ route('payment.index') }}"
                                      :active=" request()->is('admin/payment')">
                        <x-icons.activity/>
                        پرداخت ها
                    </x-panel.nav-link>
                @endcan

                @can('مدیریت بخش آنالیز')
                    <x-panel.nav-link href="{{ route('analytics.index') }}"
                                      :active=" request()->is('admin/analytics')">
                        <x-icons.pie-chart/>
                        تحلیل و آنالیز
                    </x-panel.nav-link>
                @endcan


                @can('مدیریت دسته بندی ها')
                    <x-panel.nav-link href="{{ route('category.index') }}" :active=" request()->is('admin/category')">
                        <x-icons.hash/>
                        دسته بندی ها
                    </x-panel.nav-link>
                @endcan
                @can('مدیریت نویسندگان')
                    <x-panel.nav-link href="{{ route('author.index') }}" :active=" request()->is('admin/author')">
                        <x-icons.clipboard/>
                        نویسندگان
                    </x-panel.nav-link>
                @endcan
                @can('مدیریت مترجمان')
                    <x-panel.nav-link href="{{ route('translator.index') }}" :active=" request()->is('admin/translator')">
                        <x-icons.globe/>
                        مترجمان
                    </x-panel.nav-link>
                @endcan
                @can('مدیریت گویندگان')
                    <x-panel.nav-link href="{{ route('narrator.index') }}"
                                      :active=" request()->is('admin/narrator')">
                        <x-icons.mic/>
                        گویندگان
                    </x-panel.nav-link>
                @endcan
                @can('مدیریت آهنگسازان')
                    <x-panel.nav-link href="{{ route('composer.index') }}"
                                      :active=" request()->is('admin/composer')">
                        <x-icons.music/>
                        آهنگسازان
                    </x-panel.nav-link>
                @endcan
                @can('مدیریت تدوینگران')
                    <x-panel.nav-link href="{{ route('editor.index') }}"
                                      :active=" request()->is('admin/editor')">
                        <x-icons.edit/>
                        تدوینگران
                    </x-panel.nav-link>
                @endcan
                @can('مدیریت ناشران')
                    <x-panel.nav-link href="{{ route('publisher.index') }}"
                                      :active=" request()->is('admin/publisher')">
                        <x-icons.zap/>
                        ناشران
                    </x-panel.nav-link>
                @endcan




            </ul>
        </li>
        <li>
            <div class="text-xs font-semibold leading-6 text-gray-400">تنظیمات</div>
            <ul role="list" class="mt-2 space-y-1">
                @role('ادمین اصلی')
                <x-panel.nav-link href="{{ route('session.index') }}" :active=" request()->is('admin/session')">
                    <x-icons.activity/>
                    نشست ها
                </x-panel.nav-link>

                <x-panel.nav-dropdown title="دسترسی ادمین" :active="request()->is('admin/role') || request()->is('admin/permission')">
                    <x-panel.nav-link :active=" request()->is('admin/role')" href="{{ route('role.index') }}">نقش ها</x-panel.nav-link>
                    <x-panel.nav-link :active=" request()->is('admin/permission*')" href="{{ route('permission.index') }}">دسترسی ها</x-panel.nav-link>
                </x-panel.nav-dropdown>
                @endrole
            </ul>
        </li>
    </ul>
</nav>
