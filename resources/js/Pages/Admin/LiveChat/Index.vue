<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { chatRequest, clockTime, fullDate, playChime, shortTime } from '@/Components/LiveChat/chatApi';

/**
 * Agent inbox for website conversations. The same screen serves live chats
 * (channel "chat", polled every 4s) and support tickets (channel "ticket").
 */
const props = defineProps({
    channel: { type: String, default: 'chat' },
    filters: { type: Array, default: () => ['open', 'closed', 'all'] },
    priorities: { type: Object, default: () => ({}) },
    initialConversationId: { type: Number, default: null },
    available: Boolean,
    online: Boolean,
    mode: { type: String, default: 'auto' },
});

const isTicket = computed(() => props.channel === 'ticket');
const POLL_MS = isTicket.value ? 10000 : 4000;
const indexRoute = isTicket.value ? 'admin.tickets.index' : 'admin.live-chat.index';

const conversations = ref([]);
const filter = ref(props.filters[0] || 'open');
const search = ref('');
const selectedId = ref(props.initialConversationId);
const active = ref(null);
const messages = ref([]);
const draft = ref('');
const sending = ref(false);
const loading = ref(true);
const error = ref('');
const isAvailable = ref(props.available);
const isOnline = ref(props.online);
const unreadTotal = ref(0);
const thread = ref(null);
const composer = ref(null);

let timer = null;
let inFlight = null;
let reloadQueued = false;
let lastUnread = null;

const lastId = computed(() => (messages.value.length ? messages.value[messages.value.length - 1].id : 0));

const scrollToEnd = () => nextTick(() => {
    if (thread.value) thread.value.scrollTop = thread.value.scrollHeight;
});

const nearBottom = () => !thread.value || thread.value.scrollHeight - thread.value.scrollTop - thread.value.clientHeight < 80;

/**
 * Fetch the feed. A call made while a request is running is queued, so a
 * newly selected conversation never waits for the next poll.
 */
const load = () => {
    if (inFlight) {
        reloadQueued = true;
        return inFlight;
    }
    inFlight = fetchFeed().finally(() => {
        inFlight = null;
        if (reloadQueued) {
            reloadQueued = false;
            return load();
        }
    });
    return inFlight;
};

const fetchFeed = async () => {
    const requestedId = selectedId.value;
    try {
        const data = await chatRequest('GET', route('admin.live-chat.feed', {
            channel: props.channel,
            status: filter.value,
            search: search.value || undefined,
            conversation: requestedId || undefined,
            after: requestedId ? lastId.value : undefined,
        }));

        conversations.value = data.conversations;
        isOnline.value = data.online;
        isAvailable.value = data.available;

        if (lastUnread !== null && data.unread_total > lastUnread) playChime();
        lastUnread = data.unread_total;
        unreadTotal.value = data.unread_total;

        // A response for a conversation the agent has since switched away
        // from is ignored; the queued reload fetches the new selection.
        if (requestedId === selectedId.value) {
            applyActive(data.active, requestedId);
        }
        error.value = '';
    } catch (e) {
        error.value = e.status === 419 ? 'Your session expired. Reload the page.' : 'Connection problem — retrying…';
    } finally {
        loading.value = false;
    }
};

const applyActive = (incomingActive, requestedId) => {
    if (!incomingActive) {
        if (requestedId) {
            // Deleted by another agent.
            selectedId.value = null;
            active.value = null;
        }
        return;
    }

    const stick = nearBottom();
    const { messages: incoming, ...detail } = incomingActive;
    active.value = detail;
    messages.value.forEach((m) => {
        if (m.sender === 'agent' && m.id <= detail.visitor_read_up_to) m.read = true;
    });
    const known = new Set(messages.value.map((m) => m.id));
    const fresh = incoming.filter((m) => !known.has(m.id));
    if (fresh.length) {
        if (document.hidden && fresh.some((m) => m.sender === 'visitor')) playChime();
        messages.value = [...messages.value, ...fresh];
        if (stick) scrollToEnd();
    }
};

const startPolling = () => {
    clearInterval(timer);
    timer = setInterval(load, POLL_MS);
};

const select = (conversation) => {
    if (selectedId.value === conversation.id) return;
    selectedId.value = conversation.id;
    active.value = null;
    messages.value = [];
    window.history.replaceState(window.history.state, '', route(indexRoute, { conversation: conversation.id }));
    load().then(() => {
        scrollToEnd();
        composer.value?.focus();
    });
};

const back = () => {
    selectedId.value = null;
    active.value = null;
    messages.value = [];
    window.history.replaceState(window.history.state, '', route(indexRoute));
};

const send = async () => {
    const body = draft.value.trim();
    if (!body || sending.value || !selectedId.value) return;
    sending.value = true;
    try {
        const data = await chatRequest('POST', route('admin.live-chat.reply', selectedId.value), { body });
        draft.value = '';
        if (!messages.value.some((m) => m.id === data.message.id)) messages.value.push(data.message);
        scrollToEnd();
        load();
    } catch (e) {
        alert(e.message);
    } finally {
        sending.value = false;
        nextTick(() => composer.value?.focus());
    }
};

const onComposerKey = (event) => {
    if (event.key === 'Enter' && !event.shiftKey && !event.isComposing && !isTicket.value) {
        event.preventDefault();
        send();
    }
};

const update = async (payload) => {
    try {
        const data = await chatRequest('PATCH', route('admin.live-chat.update', selectedId.value), payload);
        active.value = { ...active.value, ...data.conversation };
        load();
    } catch (e) {
        alert(e.message);
    }
};

const closeConversation = () => {
    const question = isTicket.value ? 'Close this ticket?' : 'End this chat? The visitor will see that it has ended.';
    if (confirm(question)) update({ status: 'closed' });
};

const destroy = () => {
    if (!confirm(`Delete the ${isTicket.value ? 'ticket' : 'chat'} with ${active.value.name}? This cannot be undone.`)) return;
    router.delete(route('admin.live-chat.destroy', selectedId.value));
};

const toggleAvailable = async () => {
    try {
        const data = await chatRequest('POST', route('admin.live-chat.availability'), { available: !isAvailable.value });
        isAvailable.value = data.available;
        isOnline.value = data.online;
    } catch (e) {
        alert(e.message);
    }
};

let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(load, 300);
});
watch(filter, load);

const statusNote = computed(() => {
    if (props.mode === 'online') return 'Forced online in chat settings';
    if (props.mode === 'offline') return 'Forced offline in chat settings';
    return isAvailable.value ? 'You are available' : 'You are away';
});

const filterLabel = (option) => ({
    open: isTicket.value ? 'Open' : 'Open',
    answered: 'Answered',
    closed: isTicket.value ? 'Closed' : 'Ended',
    all: 'All',
}[option] || option);

const statusBadge = (status) => ({
    open: isTicket.value ? ['Needs reply', 'bg-amber-100 text-amber-700'] : ['Open', 'bg-emerald-100 text-emerald-700'],
    answered: ['Answered', 'bg-emerald-100 text-emerald-700'],
    closed: [isTicket.value ? 'Closed' : 'Ended', 'bg-slate-100 text-slate-500'],
}[status] || [status, 'bg-slate-100 text-slate-500']);

const priorityClass = (priority) => ({
    low: 'bg-slate-100 text-slate-500',
    normal: 'bg-blue-50 text-blue-700',
    high: 'bg-amber-100 text-amber-700',
    urgent: 'bg-rose-100 text-rose-700',
}[priority] || 'bg-slate-100 text-slate-500');

const whatsappLink = (phone) => {
    const digits = (phone || '').replace(/\D/g, '');
    return digits ? `https://wa.me/${digits}` : null;
};

const initials = (name) => (name || '?').split(/\s+/).map((part) => part[0]).join('').slice(0, 2).toUpperCase();

const fileSize = (bytes) => (bytes > 1048576 ? `${(bytes / 1048576).toFixed(1)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`);

onMounted(() => {
    load().then(scrollToEnd);
    startPolling();
});

onBeforeUnmount(() => {
    clearInterval(timer);
    clearTimeout(searchTimer);
});
</script>

<template>
    <AdminLayout>
        <Head :title="isTicket ? 'Support Tickets' : 'Live Chat'" />

        <div class="space-y-4">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i :class="[isTicket ? 'fa-solid fa-ticket' : 'fa-solid fa-comments', 'text-blue-600']"></i>
                        {{ isTicket ? 'Support Tickets' : 'Live Chat' }}
                        <span v-if="unreadTotal" class="text-xs bg-rose-500 text-white px-2 py-0.5 rounded-full">{{ unreadTotal }} waiting</span>
                    </h1>
                    <p class="text-xs text-slate-500">
                        <template v-if="isTicket">Tickets submitted on /submit-ticket. Replies are emailed to the customer and shown on their ticket page.</template>
                        <template v-else>Answer website visitors in real time. Keep this tab open to stay online.</template>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <template v-if="!isTicket">
                        <span :class="['inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold border', isOnline ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200']">
                            <span :class="['w-2 h-2 rounded-full', isOnline ? 'bg-emerald-500' : 'bg-amber-500']"></span>
                            Visitors see: {{ isOnline ? 'Online' : 'Offline' }}
                        </span>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold border bg-white border-slate-200 text-slate-700 hover:border-blue-300"
                            :aria-pressed="isAvailable"
                            :title="statusNote"
                            @click="toggleAvailable"
                        >
                            <span :class="['relative inline-block w-8 h-4 rounded-full transition', isAvailable ? 'bg-blue-600' : 'bg-slate-300']">
                                <span :class="['absolute top-0.5 w-3 h-3 rounded-full bg-white transition-all', isAvailable ? 'left-4' : 'left-0.5']"></span>
                            </span>
                            {{ isAvailable ? 'Available' : 'Away' }}
                        </button>
                    </template>
                    <a v-if="isTicket" href="/submit-ticket" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold border bg-white border-slate-200 text-slate-700 hover:border-blue-300">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Open ticket form
                    </a>
                    <Link :href="route('admin.layout.index', { tab: isTicket ? 'page_submit_ticket' : 'widget_live_chat' })" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold border bg-white border-slate-200 text-slate-700 hover:border-blue-300">
                        <i class="fa-solid fa-gear"></i> {{ isTicket ? 'Ticket settings' : 'Chat settings' }}
                    </Link>
                </div>
            </div>

            <p v-if="error" class="text-xs text-rose-600 bg-rose-50 border border-rose-200 rounded-lg px-3 py-2">{{ error }}</p>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-[320px_minmax(0,1fr)] xl:grid-cols-[320px_minmax(0,1fr)_270px]" style="height: calc(100vh - 210px); min-height: 520px">
                <!-- Conversation list -->
                <div :class="['flex flex-col border-r border-slate-200 min-h-0', selectedId ? 'hidden lg:flex' : 'flex']">
                    <div class="p-3 border-b border-slate-100 space-y-2">
                        <div :class="['grid gap-1 bg-slate-100 rounded-lg p-1 text-xs font-semibold', filters.length > 3 ? 'grid-cols-4' : 'grid-cols-3']">
                            <button v-for="option in filters" :key="option" type="button" :class="['py-1.5 rounded-md', filter === option ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500 hover:text-slate-700']" @click="filter = option">{{ filterLabel(option) }}</button>
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-xs text-slate-400"></i>
                            <input v-model="search" type="search" :placeholder="isTicket ? 'Search number, subject, name or email' : 'Search name, email or message'" class="w-full pl-8 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white" />
                        </div>
                    </div>
                    <ul class="flex-1 overflow-y-auto divide-y divide-slate-100">
                        <li v-if="loading" class="p-6 text-center text-xs text-slate-400"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading…</li>
                        <li v-else-if="!conversations.length" class="p-8 text-center text-xs text-slate-400">
                            <i :class="[isTicket ? 'fa-solid fa-ticket' : 'fa-regular fa-comments', 'text-2xl mb-2 block']"></i>
                            No {{ filter === 'all' ? '' : filterLabel(filter).toLowerCase() }} {{ isTicket ? 'tickets' : 'chats' }}{{ search ? ' match your search' : ' yet' }}.
                        </li>
                        <li v-for="conversation in conversations" :key="conversation.id">
                            <button type="button" :class="['w-full text-left px-3 py-3 flex gap-3 hover:bg-slate-50', selectedId === conversation.id ? 'bg-blue-50/70' : '']" @click="select(conversation)">
                                <span class="relative shrink-0 w-9 h-9 rounded-full bg-slate-200 text-slate-600 text-xs font-bold flex items-center justify-center">
                                    {{ initials(conversation.name) }}
                                    <span v-if="!isTicket && conversation.visitor_active && conversation.status === 'open'" class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-white" title="On the website now"></span>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center gap-2">
                                        <span :class="['truncate text-sm', conversation.unread ? 'font-bold text-slate-900' : 'font-semibold text-slate-700']">
                                            {{ isTicket ? conversation.subject : conversation.name }}
                                        </span>
                                        <span class="ml-auto shrink-0 text-[10px] text-slate-400">{{ shortTime(conversation.last_message_at) }}</span>
                                    </span>
                                    <span v-if="isTicket" class="block truncate text-[11px] text-slate-500">
                                        <span class="font-mono text-slate-400">{{ conversation.reference }}</span> · {{ conversation.name }}
                                    </span>
                                    <span :class="['block truncate text-xs', conversation.unread ? 'text-slate-800 font-medium' : 'text-slate-500']">
                                        <template v-if="conversation.last_sender === 'agent'">You: </template>{{ conversation.last_message }}
                                    </span>
                                    <span class="flex items-center gap-1.5 mt-1">
                                        <span v-if="isTicket" :class="['text-[10px] px-1.5 py-0.5 rounded font-semibold capitalize', priorityClass(conversation.priority)]">{{ conversation.priority }}</span>
                                        <span v-if="conversation.department" class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500">{{ conversation.department }}</span>
                                        <span v-if="isTicket || conversation.status === 'closed'" :class="['text-[10px] px-1.5 py-0.5 rounded', statusBadge(conversation.status)[1]]">{{ statusBadge(conversation.status)[0] }}</span>
                                        <span v-if="conversation.unread" class="ml-auto text-[10px] font-bold bg-rose-500 text-white rounded-full px-1.5">{{ conversation.unread }}</span>
                                    </span>
                                </span>
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Conversation -->
                <div :class="['flex-col min-h-0 min-w-0', selectedId ? 'flex' : 'hidden lg:flex']">
                    <div v-if="!selectedId" class="flex-1 flex flex-col items-center justify-center text-center p-8 text-slate-400">
                        <i :class="[isTicket ? 'fa-solid fa-ticket' : 'fa-regular fa-comment-dots', 'text-4xl mb-3']"></i>
                        <p class="text-sm font-semibold text-slate-500">Select a {{ isTicket ? 'ticket' : 'chat' }} to reply</p>
                        <p class="text-xs mt-1">New {{ isTicket ? 'tickets' : 'chats' }} appear on the left with a sound alert.</p>
                    </div>

                    <template v-else>
                        <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-200">
                            <button type="button" class="lg:hidden p-2 -ml-2 text-slate-500" aria-label="Back to list" @click="back"><i class="fa-solid fa-arrow-left"></i></button>
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-slate-800 truncate">
                                    <template v-if="isTicket"><span class="font-mono text-xs text-slate-400 mr-1">{{ active?.reference }}</span>{{ active?.subject ?? '…' }}</template>
                                    <template v-else>{{ active?.name ?? '…' }}</template>
                                </div>
                                <div class="text-xs text-slate-500 truncate">
                                    <template v-if="isTicket">{{ active?.name }} · {{ active?.email }}</template>
                                    <template v-else>
                                        {{ active?.email }}
                                        <template v-if="active">
                                            ·
                                            <span :class="active.visitor_active ? 'text-emerald-600' : 'text-slate-400'">{{ active.visitor_active ? 'On the website now' : 'Away' }}</span>
                                        </template>
                                    </template>
                                </div>
                            </div>
                            <template v-if="active">
                                <template v-if="isTicket">
                                    <select :value="active.priority" class="text-xs py-1.5 pl-2 pr-7 border border-slate-200 rounded-lg bg-white text-slate-700" title="Priority" @change="update({ priority: $event.target.value })">
                                        <option v-for="(label, value) in priorities" :key="value" :value="value">{{ label }}</option>
                                    </select>
                                    <select :value="active.status" class="text-xs py-1.5 pl-2 pr-7 border border-slate-200 rounded-lg bg-white text-slate-700" title="Status" @change="$event.target.value === 'closed' ? (closeConversation(), $event.target.value = active.status) : update({ status: $event.target.value })">
                                        <option value="open">Open (needs reply)</option>
                                        <option value="answered">Answered</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </template>
                                <template v-else>
                                    <button v-if="active.status !== 'closed'" type="button" class="px-3 py-1.5 text-xs font-semibold border border-slate-200 rounded-lg text-slate-600 hover:border-amber-300 hover:text-amber-700" @click="closeConversation">
                                        <i class="fa-solid fa-circle-stop mr-1"></i> End chat
                                    </button>
                                    <button v-else type="button" class="px-3 py-1.5 text-xs font-semibold border border-slate-200 rounded-lg text-slate-600 hover:border-blue-300 hover:text-blue-700" @click="update({ status: 'open' })">
                                        <i class="fa-solid fa-rotate-left mr-1"></i> Re-open
                                    </button>
                                </template>
                                <button type="button" class="p-2 text-slate-400 hover:text-rose-600" title="Delete" @click="destroy"><i class="fa-regular fa-trash-can"></i></button>
                            </template>
                        </div>

                        <ol ref="thread" class="flex-1 overflow-y-auto bg-slate-50 px-4 py-4 space-y-3" aria-live="polite">
                            <li v-for="message in messages" :key="message.id" :class="['flex flex-col', message.sender === 'agent' ? 'items-end' : message.sender === 'system' ? 'items-center' : 'items-start']">
                                <template v-if="message.sender === 'system'">
                                    <span class="text-[11px] text-slate-500 bg-white border border-slate-200 rounded-full px-3 py-1 max-w-[90%] text-center whitespace-pre-wrap">{{ message.body }}</span>
                                </template>
                                <template v-else>
                                    <span class="text-[10px] text-slate-400 mb-0.5">{{ message.sender === 'agent' ? message.author : active?.name }} · {{ isTicket ? fullDate(message.time) : clockTime(message.time) }}</span>
                                    <span :class="['max-w-[80%] px-3.5 py-2 text-sm whitespace-pre-wrap break-words', message.sender === 'agent' ? 'bg-blue-600 text-white rounded-2xl rounded-br-md' : 'bg-white border border-slate-200 text-slate-800 rounded-2xl rounded-bl-md']">{{ message.body }}</span>
                                    <span v-if="message.attachments && message.attachments.length" class="flex flex-wrap gap-1.5 mt-1.5 max-w-[80%]">
                                        <a v-for="file in message.attachments" :key="file.index" :href="file.url" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-[11px] font-semibold text-blue-700 hover:border-blue-300">
                                            <i class="fa-solid fa-paperclip"></i> {{ file.name }} <span class="text-slate-400 font-normal">{{ fileSize(file.size) }}</span>
                                        </a>
                                    </span>
                                    <span v-if="message.sender === 'agent'" class="text-[10px] mt-0.5" :class="message.read ? 'text-blue-500' : 'text-slate-300'">
                                        <i class="fa-solid fa-check-double"></i> {{ message.read ? 'Seen' : 'Sent' }}
                                    </span>
                                </template>
                            </li>
                        </ol>

                        <form class="border-t border-slate-200 p-3 bg-white" @submit.prevent="send">
                            <p v-if="active && active.status === 'closed'" class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-2 py-1 mb-2">
                                This {{ isTicket ? 'ticket is closed' : 'chat has ended' }}. Sending a reply re-opens it{{ isTicket || !active.visitor_active ? ' and emails the customer' : '' }}.
                            </p>
                            <div class="flex items-end gap-2">
                                <textarea
                                    ref="composer"
                                    v-model="draft"
                                    :rows="isTicket ? 4 : 2"
                                    maxlength="5000"
                                    :placeholder="isTicket ? 'Write your reply… The customer receives it by email and on their ticket page.' : 'Type a reply… (Enter to send, Shift+Enter for a new line)'"
                                    class="flex-1 resize-none px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white"
                                    @keydown="onComposerKey"
                                ></textarea>
                                <button type="submit" :disabled="sending || !draft.trim()" class="px-4 py-2.5 text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50">
                                    <i :class="sending ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-paper-plane'"></i>
                                    <span class="hidden sm:inline ml-1">{{ isTicket ? 'Send reply' : 'Send' }}</span>
                                </button>
                            </div>
                        </form>
                    </template>
                </div>

                <!-- Details -->
                <aside v-if="active" class="hidden xl:block border-l border-slate-200 overflow-y-auto p-4 text-xs space-y-4">
                    <div class="text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-blue-100 text-blue-700 font-bold text-lg flex items-center justify-center">{{ initials(active.name) }}</div>
                        <div class="mt-2 font-bold text-slate-800 text-sm">{{ active.name }}</div>
                        <span :class="['inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold', statusBadge(active.status)[1]]">{{ statusBadge(active.status)[0] }}</span>
                    </div>
                    <dl class="space-y-3">
                        <div v-if="isTicket">
                            <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Ticket</dt>
                            <dd class="font-mono text-slate-700">{{ active.reference }}</dd>
                        </div>
                        <div v-if="isTicket">
                            <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Priority</dt>
                            <dd><span :class="['px-2 py-0.5 rounded font-semibold capitalize', priorityClass(active.priority)]">{{ active.priority }}</span></dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Email</dt>
                            <dd><a :href="`mailto:${active.email}`" class="text-blue-600 hover:underline break-all">{{ active.email }}</a></dd>
                        </div>
                        <div v-if="active.phone">
                            <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Phone</dt>
                            <dd class="flex items-center gap-2">
                                <a :href="`tel:${active.phone}`" class="text-blue-600 hover:underline">{{ active.phone }}</a>
                                <a v-if="whatsappLink(active.phone)" :href="whatsappLink(active.phone)" target="_blank" rel="noopener" class="text-emerald-600" title="Open in WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                            </dd>
                        </div>
                        <div v-if="active.department">
                            <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Department</dt>
                            <dd class="text-slate-700">{{ active.department }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Assigned to</dt>
                            <dd class="flex items-center gap-2 text-slate-700">
                                {{ active.assignee || 'Nobody yet' }}
                                <button type="button" class="text-blue-600 hover:underline" @click="update({ assign_to_me: true })">Assign to me</button>
                            </dd>
                        </div>
                        <div v-if="active.page_url">
                            <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">{{ isTicket ? 'Came from page' : 'Started on page' }}</dt>
                            <dd><a :href="active.page_url" target="_blank" rel="noopener" class="text-blue-600 hover:underline break-all">{{ active.page_url }}</a></dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">{{ isTicket ? 'Opened' : 'Started' }}</dt>
                            <dd class="text-slate-700">{{ fullDate(active.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Last seen</dt>
                            <dd class="text-slate-700">{{ active.visitor_active ? 'Now' : fullDate(active.visitor_last_seen_at) }}</dd>
                        </div>
                        <div v-if="active.ip_address">
                            <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">IP address</dt>
                            <dd class="text-slate-700 font-mono">{{ active.ip_address }}</dd>
                        </div>
                        <div v-if="active.user_agent">
                            <dt class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Browser</dt>
                            <dd class="text-slate-500 break-words">{{ active.user_agent }}</dd>
                        </div>
                    </dl>
                </aside>
            </div>
        </div>
    </AdminLayout>
</template>
