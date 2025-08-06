<div>
    <section id="faq" dir="rtl">
        <div class=" py-8 md:py-12 ">
            <div>
                <div class="text-center md:text-right">
                    <h2 class="font-bold tracking-tight text-gray-900 text-3xl mb-8">
                        سوالات متداول
                    </h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                    <div>
                        <dl>
                            @foreach ($user_faqs as $faq)
                                <x-ui.faq :faq="$faq" :loop="$loop" />
                            @endforeach
                        </dl>
                    </div>
                    <div>
                        <dl>
                            @foreach ($employer_faqs as $faq)
                                <x-ui.faq :faq="$faq" :loop="$loop" />
                            @endforeach
                        </dl>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
