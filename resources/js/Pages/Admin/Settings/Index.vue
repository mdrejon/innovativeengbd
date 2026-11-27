<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    settings: Object,
    keys: Array,
});

const activeTab = ref('general');

// Used until a value is saved (matches the fallbacks in App\Services\Cms).
const defaults = { currency_symbol: '$', currency_code: 'USD', domain_currency_symbol: '৳', domain_currency_code: 'BDT' };

const form = useForm(Object.fromEntries(props.keys.map((key) => [key, props.settings[key] ?? defaults[key] ?? ''])));

const tabs = [
    ['general', 'General & Branding'],
    ['contact', 'Contact & Integrations'],
    ['currency', 'Currency'],
    ['seo', 'SEO & Tracking Scripts'],
];

const fields = {
    general: [
        ['site_name', 'Website name (default page title)'],
        ['brand_name', 'Brand name (logo alt, footer)'],
        ['site_tagline', 'Default meta description', 'textarea'],
        ['site_logo', 'Header logo path', 'mono'],
        ['site_logo_dark', 'Footer logo path', 'mono'],
        ['site_favicon', 'Favicon (SVG) path', 'mono'],
        ['established_year', 'Established year'],
    ],
    contact: [
        ['sales_email', 'Sales email (receives contact-form inquiries)'],
        ['support_email', 'Support email'],
        ['abuse_email', 'Abuse / NOC email'],
        ['whatsapp_number', 'WhatsApp number'],
        ['client_area_url', 'Client area URL'],
    ],
    currency: [
        ['currency_symbol', 'Hosting & server plans — symbol (e.g. $, ৳, €)'],
        ['currency_code', 'Hosting & server plans — code (e.g. USD, BDT)', 'mono'],
        ['domain_currency_symbol', 'Domain prices — symbol'],
        ['domain_currency_code', 'Domain prices — code', 'mono'],
    ],
    seo: [
        ['google_analytics_id', 'Google Analytics 4 ID', 'mono'],
        ['header_scripts', 'Custom code before </head> (GTM, Pixel…)', 'code'],
        ['footer_scripts', 'Custom code before </body>', 'code'],
        ['robots_txt', 'robots.txt', 'code'],
    ],
};

const save = () => form.post(route('admin.settings.update'), { preserveScroll: true });
const inputClass = 'w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg';
</script>

<template>
    <AdminLayout>
        <Head title="Global Settings" />

        <div class="space-y-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Global Website Settings</h2>
                <p class="text-sm text-slate-400">Brand, contact details and tracking scripts.</p>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs text-blue-800 flex items-start gap-3">
                <i class="fa-solid fa-circle-info mt-0.5"></i>
                <p>
                    The header strip, menus, social links, footer and floating dock are edited in
                    <Link :href="route('admin.layout.index')" class="font-bold underline">Site Layout</Link>.
                    Page titles and descriptions are set per page in <Link :href="route('admin.pages.index')" class="font-bold underline">Pages</Link>.
                    The domain Search / Transfer button links are set in
                    <Link :href="route('admin.layout.index', { tab: 'widget_domain_search' })" class="font-bold underline">Site Layout → Domain search</Link>.
                </p>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-2 shadow-sm flex gap-1 overflow-x-auto">
                <button v-for="tab in tabs" :key="tab[0]" :class="['px-4 py-2 text-xs rounded-lg whitespace-nowrap', activeTab === tab[0] ? 'bg-blue-600 text-white font-bold' : 'text-slate-600 hover:bg-slate-100']" @click="activeTab = tab[0]">{{ tab[1] }}</button>
            </div>

            <form class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm text-xs" @submit.prevent="save">
                <div v-if="activeTab === 'currency'" class="mb-5 rounded-xl bg-slate-50 border border-slate-200 p-4 text-slate-600 space-y-1.5 leading-relaxed">
                    <p><strong class="text-slate-800">Hosting &amp; server plans</strong> — plan cards, the order popup and any price written as <code class="px-1 bg-white border border-slate-200 rounded">{currency}4.15</code> in page sections.</p>
                    <p><strong class="text-slate-800">Domain prices</strong> — the domain price tables, search chips and prices written as <code class="px-1 bg-white border border-slate-200 rounded">{domain_currency}1,600</code>. Domain Pricing amounts are entered in this currency.</p>
                    <p class="text-slate-400">Only the symbol changes — amounts are not converted, so update prices when switching currency.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div v-for="field in fields[activeTab]" :key="field[0]" :class="['textarea', 'code'].includes(field[2]) ? 'sm:col-span-2' : ''">
                        <label class="block font-semibold text-slate-600 mb-1">{{ field[1] }}</label>
                        <textarea v-if="field[2] === 'textarea'" v-model="form[field[0]]" rows="2" :class="inputClass"></textarea>
                        <textarea v-else-if="field[2] === 'code'" v-model="form[field[0]]" rows="5" :class="[inputClass, 'font-mono text-[11px]']"></textarea>
                        <input v-else v-model="form[field[0]]" type="text" :class="[inputClass, field[2] === 'mono' ? 'font-mono text-[11px]' : '']" />
                        <p v-if="form.errors[field[0]]" class="mt-1 text-rose-600">{{ form.errors[field[0]] }}</p>
                    </div>
                </div>
                <div class="flex justify-end pt-6 mt-6 border-t border-slate-100">
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md">Save Settings</button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
