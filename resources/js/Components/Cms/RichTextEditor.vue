<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

/**
 * WYSIWYG editor for article HTML (blog and knowledge base). Built on the
 * browser's contenteditable, so it needs no extra package; the "HTML" button
 * switches to the raw source. Images go through the CMS upload route.
 */
const model = defineModel({ type: String, default: '' });

const props = defineProps({
    placeholder: { type: String, default: 'Start writing…' },
    minHeight: { type: String, default: '24rem' },
});

const editor = ref(null);
const fileInput = ref(null);
const linkInput = ref(null);
const source = ref(false);
const fullscreen = ref(false);
const state = ref({});
const block = ref('p');
const linkBar = ref(false);
const link = ref({ url: '', newTab: false });
const uploading = ref(false);
const error = ref('');
let savedRange = null;

const blocks = [
    ['p', 'Paragraph'],
    ['h2', 'Heading 2'],
    ['h3', 'Heading 3'],
    ['h4', 'Heading 4'],
    ['pre', 'Code block'],
    ['blockquote', 'Quote'],
];

const tools = [
    [
        { command: 'bold', icon: 'fa-bold', title: 'Bold (Ctrl+B)' },
        { command: 'italic', icon: 'fa-italic', title: 'Italic (Ctrl+I)' },
        { command: 'underline', icon: 'fa-underline', title: 'Underline (Ctrl+U)' },
        { command: 'strikeThrough', icon: 'fa-strikethrough', title: 'Strikethrough' },
        { action: 'code', icon: 'fa-code', title: 'Inline code' },
    ],
    [
        { command: 'insertUnorderedList', icon: 'fa-list-ul', title: 'Bullet list' },
        { command: 'insertOrderedList', icon: 'fa-list-ol', title: 'Numbered list' },
        { command: 'outdent', icon: 'fa-outdent', title: 'Decrease indent' },
        { command: 'indent', icon: 'fa-indent', title: 'Increase indent' },
    ],
    [
        { command: 'justifyLeft', icon: 'fa-align-left', title: 'Align left' },
        { command: 'justifyCenter', icon: 'fa-align-center', title: 'Align centre' },
        { command: 'justifyRight', icon: 'fa-align-right', title: 'Align right' },
    ],
    [
        { action: 'link', icon: 'fa-link', title: 'Insert link (Ctrl+K)' },
        { command: 'unlink', icon: 'fa-link-slash', title: 'Remove link' },
        { action: 'image', icon: 'fa-image', title: 'Upload image' },
        { action: 'table', icon: 'fa-table', title: 'Insert table' },
        { command: 'insertHorizontalRule', icon: 'fa-minus', title: 'Divider line' },
    ],
    [
        { command: 'removeFormat', icon: 'fa-text-slash', title: 'Clear formatting' },
        { command: 'undo', icon: 'fa-rotate-left', title: 'Undo (Ctrl+Z)' },
        { command: 'redo', icon: 'fa-rotate-right', title: 'Redo (Ctrl+Y)' },
    ],
];

const isEmpty = computed(() => !(model.value || '').replace(/<(p|br|div)[^>]*>|<\/(p|div)>|&nbsp;|\s/gi, ''));

const words = computed(() => {
    const text = (model.value || '').replace(/<[^>]+>/g, ' ').replace(/&nbsp;/g, ' ').trim();
    return text ? text.split(/\s+/).length : 0;
});

const readMinutes = computed(() => Math.max(1, Math.round(words.value / 200)));

const sync = () => {
    if (!editor.value) return;
    const html = editor.value.innerHTML;
    model.value = /^(<p>)?(<br>)?(<\/p>)?$/i.test(html.trim()) ? '' : html;
};

const render = () => {
    if (editor.value && editor.value.innerHTML !== (model.value || '')) {
        editor.value.innerHTML = model.value || '';
    }
};

const refreshState = () => {
    if (!editor.value || !editor.value.contains(document.getSelection()?.anchorNode)) return;
    state.value = Object.fromEntries(['bold', 'italic', 'underline', 'strikeThrough', 'insertUnorderedList', 'insertOrderedList', 'justifyLeft', 'justifyCenter', 'justifyRight']
        .map((command) => [command, document.queryCommandState(command)]));
    const value = (document.queryCommandValue('formatBlock') || 'p').toLowerCase().replace(/[<>]/g, '');
    block.value = blocks.some(([tag]) => tag === value) ? value : 'p';
};

const saveSelection = () => {
    const selection = document.getSelection();
    if (selection?.rangeCount && editor.value?.contains(selection.anchorNode)) {
        savedRange = selection.getRangeAt(0).cloneRange();
    }
};

const restoreSelection = () => {
    editor.value.focus();
    if (savedRange) {
        const selection = document.getSelection();
        selection.removeAllRanges();
        selection.addRange(savedRange);
    }
};

const exec = (command, value = null) => {
    restoreSelection();
    document.execCommand(command, false, value);
    saveSelection();
    sync();
    refreshState();
};

const escapeHtml = (text) => text.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

const setBlock = (tag) => exec('formatBlock', `<${tag}>`);

const run = (tool) => {
    if (tool.command) {
        exec(tool.command);
    } else if (tool.action === 'code') {
        restoreSelection();
        const text = document.getSelection().toString() || 'code';
        exec('insertHTML', `<code>${escapeHtml(text)}</code>&#8203;`);
    } else if (tool.action === 'link') {
        openLinkBar();
    } else if (tool.action === 'image') {
        saveSelection();
        fileInput.value.click();
    } else if (tool.action === 'table') {
        const cells = (tag) => `<${tag}>&nbsp;</${tag}>`.repeat(3);
        exec('insertHTML', `<table><thead><tr>${cells('th')}</tr></thead><tbody><tr>${cells('td')}</tr><tr>${cells('td')}</tr></tbody></table><p><br></p>`);
    }
};

const openLinkBar = () => {
    saveSelection();
    const anchor = document.getSelection()?.anchorNode?.parentElement?.closest('a');
    link.value = { url: anchor?.getAttribute('href') || '', newTab: anchor?.target === '_blank' };
    linkBar.value = true;
    nextTick(() => linkInput.value?.focus());
};

const applyLink = () => {
    const url = link.value.url.trim();
    linkBar.value = false;
    if (!url) {
        exec('unlink');
        return;
    }
    restoreSelection();
    const selected = document.getSelection().toString();
    const attrs = `href="${escapeHtml(url)}"${link.value.newTab ? ' target="_blank" rel="noopener"' : ''}`;
    exec('insertHTML', `<a ${attrs}>${escapeHtml(selected || url)}</a>`);
};

const csrf = () => decodeURIComponent((document.cookie.match(/XSRF-TOKEN=([^;]+)/) || [])[1] || '');

const uploadImage = async (event) => {
    const file = event.target.files?.[0];
    event.target.value = '';
    if (!file) return;
    uploading.value = true;
    error.value = '';
    const body = new FormData();
    body.append('file', file);
    try {
        const response = await fetch(route('admin.uploads.store'), {
            method: 'POST',
            body,
            headers: { Accept: 'application/json', 'X-XSRF-TOKEN': csrf() },
            credentials: 'same-origin',
        });
        const json = await response.json();
        if (!response.ok) throw new Error(json.message || 'Upload failed');
        const alt = escapeHtml(file.name.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' '));
        exec('insertHTML', `<img src="/${json.path}" alt="${alt}" loading="lazy">`);
    } catch (e) {
        error.value = e.message;
    } finally {
        uploading.value = false;
    }
};

// Keep pasted content clean: no inline styles, classes, fonts or scripts.
const allowedAttributes = ['href', 'src', 'alt', 'title', 'target', 'rel', 'colspan', 'rowspan'];

const cleanPaste = (html) => {
    const doc = new DOMParser().parseFromString(html, 'text/html');
    doc.querySelectorAll('script, style, meta, link, title, o\\:p').forEach((node) => node.remove());
    doc.querySelectorAll('span, font').forEach((node) => node.replaceWith(...node.childNodes));
    doc.body.querySelectorAll('*').forEach((node) => {
        [...node.attributes].forEach((attr) => {
            if (!allowedAttributes.includes(attr.name) || /^\s*javascript:/i.test(attr.value)) node.removeAttribute(attr.name);
        });
    });
    return doc.body.innerHTML;
};

const onPaste = (event) => {
    const html = event.clipboardData?.getData('text/html');
    const text = event.clipboardData?.getData('text/plain');
    if (!html && !text) return;
    event.preventDefault();
    saveSelection();
    exec('insertHTML', html ? cleanPaste(html) : escapeHtml(text).replace(/\n/g, '<br>'));
};

const onKeydown = (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        openLinkBar();
    }
};

const toggleSource = () => {
    source.value = !source.value;
    linkBar.value = false;
    if (!source.value) nextTick(render);
};

const onSelectionChange = () => {
    saveSelection();
    refreshState();
};

watch(model, () => { if (!source.value && document.activeElement !== editor.value) render(); });

onMounted(() => {
    document.execCommand('defaultParagraphSeparator', false, 'p');
    render();
    document.addEventListener('selectionchange', onSelectionChange);
});

onBeforeUnmount(() => document.removeEventListener('selectionchange', onSelectionChange));
</script>

<template>
    <div>
    <!-- Full screen moves to <body> so modal overlays (backdrop-filter) can't trap it. -->
    <Teleport to="body" :disabled="!fullscreen">
    <div :class="['rich-editor flex flex-col border border-slate-200 bg-white overflow-hidden text-xs', fullscreen ? 'fixed inset-0 z-[70] rounded-none' : 'rounded-xl focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100']">
        <div class="flex flex-wrap items-center gap-1 px-2 py-1.5 border-b border-slate-200 bg-slate-50 sticky top-0 z-10">
            <select :value="block" :disabled="source" class="h-8 pl-2 pr-7 py-0 text-xs font-semibold text-slate-700 bg-white border-slate-200 rounded-lg focus:ring-blue-500 disabled:opacity-40" title="Text style" @mousedown="saveSelection" @change="setBlock($event.target.value)">
                <option v-for="[tag, label] in blocks" :key="tag" :value="tag">{{ label }}</option>
            </select>
            <template v-for="(group, index) in tools" :key="index">
                <span class="w-px h-5 bg-slate-200 mx-0.5"></span>
                <button
                    v-for="tool in group"
                    :key="tool.icon"
                    type="button"
                    :title="tool.title"
                    :disabled="source"
                    :class="['w-8 h-8 rounded-lg text-[13px] flex items-center justify-center transition disabled:opacity-30', state[tool.command] ? 'bg-blue-100 text-blue-700' : 'text-slate-600 hover:bg-white hover:text-slate-900 hover:shadow-sm']"
                    @mousedown.prevent
                    @click="run(tool)"
                >
                    <i v-if="tool.action === 'image' && uploading" class="fa-solid fa-spinner fa-spin"></i>
                    <i v-else :class="['fa-solid', tool.icon]"></i>
                </button>
            </template>
            <div class="ml-auto flex items-center gap-1">
                <button type="button" :class="['h-8 px-2.5 rounded-lg text-[11px] font-bold flex items-center gap-1.5 transition', source ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-white hover:shadow-sm']" title="Edit HTML source" @click="toggleSource">
                    <i class="fa-solid fa-file-code"></i> HTML
                </button>
                <button type="button" class="w-8 h-8 rounded-lg text-slate-600 hover:bg-white hover:shadow-sm" :title="fullscreen ? 'Exit full screen' : 'Full screen'" @click="fullscreen = !fullscreen">
                    <i :class="['fa-solid', fullscreen ? 'fa-compress' : 'fa-expand']"></i>
                </button>
            </div>
        </div>

        <div v-if="linkBar" class="flex flex-wrap items-center gap-2 px-3 py-2 border-b border-slate-200 bg-blue-50/60 text-xs">
            <i class="fa-solid fa-link text-blue-600"></i>
            <input ref="linkInput" v-model="link.url" type="text" placeholder="https://… or /page" class="flex-1 min-w-[12rem] h-8 px-2 text-xs border-slate-200 rounded-lg focus:ring-blue-500" @keydown.enter.prevent="applyLink" @keydown.esc.prevent="linkBar = false" />
            <label class="inline-flex items-center gap-1.5 font-semibold text-slate-600"><input v-model="link.newTab" type="checkbox" class="rounded border-slate-300 text-blue-600" /> New tab</label>
            <button type="button" class="h-8 px-3 bg-blue-600 text-white font-bold rounded-lg" @click="applyLink">Apply</button>
            <button type="button" class="h-8 px-2 text-slate-500 hover:text-slate-700" @click="linkBar = false">Cancel</button>
        </div>

        <div :class="['relative flex-1 overflow-y-auto', fullscreen ? 'bg-slate-100 py-8' : '']">
            <textarea
                v-if="source"
                v-model="model"
                spellcheck="false"
                class="block w-full h-full border-0 bg-slate-900 text-slate-100 font-mono text-[12px] leading-relaxed p-4 focus:ring-0"
                :style="{ minHeight: fullscreen ? '100%' : minHeight }"
            ></textarea>
            <template v-else>
                <p v-if="isEmpty" class="absolute top-0 left-0 right-0 mx-auto max-w-3xl px-6 py-5 text-slate-400 pointer-events-none text-[15px]">{{ placeholder }}</p>
                <div
                    ref="editor"
                    contenteditable="true"
                    role="textbox"
                    aria-multiline="true"
                    :class="['rich-editor-body prose prose-slate max-w-3xl mx-auto px-6 py-5 text-[15px] focus:outline-none', fullscreen ? 'bg-white rounded-xl shadow-sm min-h-full' : '']"
                    :style="{ minHeight }"
                    @input="sync"
                    @blur="sync"
                    @paste="onPaste"
                    @keydown="onKeydown"
                    @mouseup="onSelectionChange"
                ></div>
            </template>
        </div>

        <div class="flex items-center justify-between gap-3 px-3 py-1.5 border-t border-slate-100 bg-slate-50 text-[11px] text-slate-400">
            <span>{{ words }} words · about {{ readMinutes }} min read</span>
            <span v-if="error" class="text-rose-600">{{ error }}</span>
            <span v-else>{{ source ? 'Editing HTML source' : 'Ctrl+K adds a link · paste keeps headings, lists and links' }}</span>
        </div>

        <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="uploadImage" />
    </div>
    </Teleport>
    </div>
</template>

<style scoped>
.rich-editor-body :deep(img) {
    border-radius: 0.5rem;
    max-width: 100%;
    height: auto;
}

.rich-editor-body :deep(table) {
    width: 100%;
    border-collapse: collapse;
}

.rich-editor-body :deep(th),
.rich-editor-body :deep(td) {
    border: 1px solid #e2e8f0;
    padding: 0.4rem 0.6rem;
    min-width: 4rem;
}

.rich-editor-body :deep(code)::before,
.rich-editor-body :deep(code)::after {
    content: none;
}

.rich-editor-body :deep(code) {
    background: #f1f5f9;
    border-radius: 0.25rem;
    padding: 0.1rem 0.3rem;
    font-weight: 500;
}

.rich-editor-body :deep(pre code) {
    background: transparent;
    padding: 0;
}
</style>
