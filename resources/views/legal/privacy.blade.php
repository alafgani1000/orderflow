<x-legal-layout :title="__('legal.privacy.title')" :version="config('orderflow.legal.privacy_version')">
    <p class="text-sm sm:text-base leading-7 text-slate-600 mb-8">
        {{ __('legal.privacy.intro', ['company' => config('orderflow.company.name')]) }}
    </p>

    <div class="space-y-7">
        @foreach(trans('legal.privacy.sections') as $section)
            <section>
                <h2 class="text-base font-extrabold text-slate-900 mb-2">{{ $section['title'] }}</h2>
                <p class="text-sm leading-7 text-slate-600">
                    {{ str_replace(':company', config('orderflow.company.name'), $section['body']) }}
                </p>
            </section>
        @endforeach

        <section class="pt-6 border-t border-slate-200">
            <h2 class="text-base font-extrabold text-slate-900 mb-2">{{ __('legal.common.contact') }}</h2>
            <p class="text-sm leading-7 text-slate-600">{{ config('orderflow.company.support_email') }}</p>
        </section>
    </div>
</x-legal-layout>
