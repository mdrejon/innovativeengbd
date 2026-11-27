<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    order: Object,
    statuses: Object,
});

const form = useForm({
    status: props.order.status,
    admin_notes: props.order.admin_notes || '',
    billing_cycle: props.order.billing_cycle || '',
    domain_name: props.order.domain_name || '',
    server_location: props.order.server_location || '',
    notes: props.order.notes || '',
});

const submit = () => {
    form.put(route('admin.orders.update', props.order.id), {
        preserveScroll: true,
    });
};

const deleteOrder = () => {
    if (confirm(`Are you sure you want to delete order #${props.order.order_number}? This cannot be undone.`)) {
        router.delete(route('admin.orders.destroy', props.order.id));
    }
};

const getStatusBadge = (st) => {
    switch (st) {
        case 'requested':
            return 'bg-amber-100 text-amber-800 border-amber-300';
        case 'pending':
            return 'bg-blue-100 text-blue-800 border-blue-300';
        case 'confirmed':
            return 'bg-emerald-100 text-emerald-800 border-emerald-300';
        case 'cancelled':
            return 'bg-rose-100 text-rose-800 border-rose-300';
        default:
            return 'bg-slate-100 text-slate-800 border-slate-300';
    }
};

const getWhatsAppLink = () => {
    if (!props.order.customer_phone) return null;
    const cleanPhone = props.order.customer_phone.replace(/[^0-9]/g, '');
    const text = encodeURIComponent(
        `Hello ${props.order.customer_name}, thank you for choosing Host Divine Networks! Regarding your order #${props.order.order_number} for ${props.order.plan_name}:`
    );
    return `https://wa.me/${cleanPhone}?text=${text}`;
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Order #${order.order_number}`" />

        <div class="space-y-6 max-w-5xl">
            <!-- Breadcrumbs / Top Navigation -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <Link :href="route('admin.orders.index')" class="hover:text-blue-600 transition flex items-center gap-1">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Back to All Orders</span>
                    </Link>
                    <span>/</span>
                    <span class="font-mono font-bold text-slate-800">#{{ order.order_number }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        v-if="getWhatsAppLink()"
                        :href="getWhatsAppLink()"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold flex items-center gap-1.5 transition shadow-sm"
                    >
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                        Chat Customer
                    </a>
                    <button
                        type="button"
                        @click="deleteOrder"
                        class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition"
                    >
                        <i class="fa-solid fa-trash text-xs"></i>
                        Delete
                    </button>
                </div>
            </div>

            <!-- Main Order Card Header -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-extrabold text-slate-900 font-mono">
                                #{{ order.order_number }}
                            </h1>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border" :class="getStatusBadge(order.status)">
                                {{ statuses[order.status] || order.status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">
                            Received on <strong class="text-slate-600">{{ order.created_at }}</strong> ({{ order.created_at_human }})
                        </p>
                    </div>

                    <div class="text-right sm:border-l sm:pl-6 border-slate-200">
                        <div class="text-xs text-slate-400 font-medium">Selected Plan</div>
                        <div class="text-xl font-extrabold text-blue-600">{{ order.plan_name }}</div>
                        <div class="text-sm font-semibold text-slate-700">
                            {{ order.plan_price || 'Custom' }} <span class="text-slate-400 font-normal">{{ order.plan_period || '' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Customer Details -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-user text-blue-500"></i>
                            Customer Information
                        </h3>
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/70 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Full Name</span>
                                <span class="font-semibold text-slate-800">{{ order.customer_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Email</span>
                                <a :href="`mailto:${order.customer_email}`" class="font-medium text-blue-600 hover:underline">
                                    {{ order.customer_email }}
                                </a>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Phone / WhatsApp</span>
                                <span class="font-medium text-slate-800 font-mono">{{ order.customer_phone }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Customer IP</span>
                                <span class="font-mono text-xs text-slate-500">{{ order.ip_address || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Configuration -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-sliders text-blue-500"></i>
                            Service Specifications
                        </h3>
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/70 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Category</span>
                                <span class="font-semibold text-slate-800">{{ order.plan_category || 'Hosting Service' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Billing Cycle</span>
                                <span class="font-semibold text-slate-800">{{ order.billing_cycle || 'Monthly' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Target Domain</span>
                                <span class="font-mono font-medium text-slate-800">{{ order.domain_name || 'None provided' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Server Location</span>
                                <span class="font-medium text-slate-800">{{ order.server_location || 'Default' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Notes -->
                <div v-if="order.notes" class="mt-6">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-message text-blue-500"></i>
                        Customer Notes & Instructions
                    </h3>
                    <div class="bg-amber-50/60 border border-amber-200 rounded-xl p-4 text-sm text-amber-950 whitespace-pre-line leading-relaxed">
                        {{ order.notes }}
                    </div>
                </div>
            </div>

            <!-- Admin Status & Management Form -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                    Update Order Status & Admin Notes
                </h3>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Order Status <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.status"
                                class="w-full text-sm py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                            >
                                <option value="requested">Requested (New / Awaiting Review)</option>
                                <option value="pending">Pending (Awaiting Payment / Setup)</option>
                                <option value="confirmed">Confirmed (Service Activated / Complete)</option>
                                <option value="cancelled">Cancelled (Declined / Closed)</option>
                            </select>
                            <div v-if="form.errors.status" class="text-rose-500 text-xs mt-1">{{ form.errors.status }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Billing Cycle
                            </label>
                            <input
                                v-model="form.billing_cycle"
                                type="text"
                                placeholder="Monthly, Annually, etc."
                                class="w-full text-sm py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Domain Name
                            </label>
                            <input
                                v-model="form.domain_name"
                                type="text"
                                placeholder="example.com"
                                class="w-full text-sm py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition font-mono"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Server Location / Node
                            </label>
                            <input
                                v-model="form.server_location"
                                type="text"
                                placeholder="USA / Singapore / Node-01"
                                class="w-full text-sm py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Admin Internal Notes
                            <span class="text-slate-400 font-normal lowercase">(only visible to administrators)</span>
                        </label>
                        <textarea
                            v-model="form.admin_notes"
                            rows="4"
                            placeholder="Add internal notes such as WHMCS client ID, invoice ID, transaction reference, cPanel account username, or phone conversation summary..."
                            class="w-full text-sm p-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <Link
                            :href="route('admin.orders.index')"
                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-md transition disabled:opacity-50 flex items-center gap-2"
                        >
                            <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
