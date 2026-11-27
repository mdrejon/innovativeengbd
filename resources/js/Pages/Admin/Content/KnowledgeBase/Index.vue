<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RichTextEditor from '@/Components/Cms/RichTextEditor.vue';

/**
 * Knowledge-base articles shown on /knowledge-base. Categories (name, icon,
 * description) are edited in Site Layout → Knowledge base page.
 */
const props = defineProps({
    articles: Array,
    categories: Array,
    layoutUrl: String,
});

const showModal = ref(false);
const editing = ref(null);
const filterCategory = ref('');
const search = ref('');

const form = useForm({
    category: '',
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    icon: 'fa-solid fa-book-open',
    sort_order: 0,
    is_published: true,
    published_at: '',
    meta_title: '',
    meta_description: '',
});

const visible = computed(() => props.articles.filter((article) => {
    if (filterCategory.value && article.category !== filterCategory.value) return false;
    if (!search.value) return true;
    const q = search.value.toLowerCase();
    return article.title.toLowerCase().includes(q) || article.slug.includes(q) || (article.excerpt || '').toLowerCase().includes(q);
}));

const grouped = computed(() => {
    const map = new Map();
    visible.value.forEach((article) => {
        const key = article.category || 'Uncategorised';
        if (!map.has(key)) map.set(key, []);
        map.get(key).push(article);
    });
    return [...map.entries()];
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.category = filterCategory.value || props.categories[0] || '';
    showModal.value = true;
};

const openEdit = (article) => {
    editing.value = article;
    form.clearErrors();
    Object.keys(form.data()).forEach((key) => { form[key] = article[key] ?? ''; });
    form.published_at = article.published_at ? article.published_at.slice(0, 10) : '';
    form.is_published = Boolean(article.is_published);
    form.sort_order = article.sort_order ?? 0;
    showModal.value = true;
};

const save = () => {
    const options = { preserveScroll: true, onSuccess: () => { showModal.value = false; } };
    editing.value
        ? form.put(route('admin.knowledge-base.update', editing.value.id), options)
        : form.post(route('admin.knowledge-base.store'), options);
};

const slugify = () => {
    if (!editing.value && form.title) {
        form.slug = form.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }
};

const destroy = (article) => {
    if (confirm(`Delete "${article.title}"?`)) {
        router.delete(route('admin.knowledge-base.destroy', article.id), { preserveScroll: true });
    }
};

const helpfulRate = (article) => {
    const total = article.helpful_yes + article.helpful_no;
    return total ? `${Math.round((article.helpful_yes / total) * 100)}% helpful (${total})` : 'No votes yet';
};

const inputClass = 'w-full py-1.5 px-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white';
</script>

<template>
    <AdminLayout>
        <Head title="Knowledge Base Articles" />

        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Knowledge Base Articles</h2>
                    <p class="text-sm text-slate-400">
                        Guides shown on <a href="/knowledge-base" target="_blank" class="text-blue-600 hover:underline">/knowledge-base</a>.
                        Category names, icons and order are set in <Link :href="layoutUrl" class="text-blue-600 hover:underline">Site Layout → Knowledge base page</Link>.
                    </p>
                </div>
                <button type="button" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm flex items-center space-x-2 transition self-start sm:self-auto" @click="openCreate">
                    <i class="fa-solid fa-plus"></i>
                    <span>New Article</span>
                </button>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <select v-model="filterCategory" class="text-xs py-2 pl-3 pr-8 bg-white border border-slate-200 rounded-lg">
                    <option value="">All categories</option>
                    <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
                </select>
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-xs text-slate-400"></i>
                    <input v-model="search" type="search" placeholder="Search articles…" class="w-full pl-8 pr-3 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500" />
                </div>
                <span class="text-xs text-slate-400 self-center">{{ visible.length }} of {{ articles.length }} articles</span>
            </div>

            <div v-if="!articles.length" class="bg-white rounded-xl border border-dashed border-slate-300 p-12 text-center text-sm text-slate-400">
                <i class="fa-solid fa-book-open text-3xl mb-3 block"></i>
                No articles yet. Click <strong>New Article</strong> to write the first guide.
            </div>

            <div v-for="[category, items] in grouped" :key="category" class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-5 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500"><i class="fa-regular fa-folder-open mr-1"></i> {{ category }}</h3>
                    <span class="text-[11px] text-slate-400">{{ items.length }} {{ items.length === 1 ? 'article' : 'articles' }}</span>
                </div>
                <table class="w-full text-left text-xs text-slate-600">
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="article in items" :key="article.id" class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-800 flex items-center space-x-2">
                                    <i :class="[article.icon, 'text-slate-400 w-4 text-center']"></i>
                                    <span>{{ article.title }}</span>
                                </div>
                                <div class="text-slate-400 text-[11px] font-mono mt-0.5">/knowledge-base/{{ article.slug }}</div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-500">
                                <div><i class="fa-regular fa-eye mr-1"></i>{{ article.views }} views</div>
                                <div class="text-[11px] text-slate-400">{{ helpfulRate(article) }}</div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span :class="[article.is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500', 'text-[10px] px-2 py-0.5 rounded-full font-bold']">
                                    {{ article.is_published ? 'Published' : 'Draft' }}
                                </span>
                                <div class="text-[11px] text-slate-400 mt-1">Order {{ article.sort_order }}</div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-400 whitespace-nowrap">
                                {{ article.updated_at ? new Date(article.updated_at).toLocaleDateString() : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1">
                                <a :href="article.url" target="_blank" class="px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-lg font-semibold transition" title="Preview on website">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                </a>
                                <button type="button" class="px-2.5 py-1 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg font-semibold transition" @click="openEdit(article)">Edit</button>
                                <button type="button" class="p-1 text-rose-500 hover:text-rose-700 rounded transition" @click="destroy(article)"><i class="fa-regular fa-trash-can"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Editor -->
            <div v-if="showModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
                <div class="bg-white rounded-2xl border border-slate-200 max-w-5xl w-full p-6 shadow-2xl space-y-4 my-8">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-base font-bold text-slate-800">{{ editing ? 'Edit Article' : 'New Article' }}</h3>
                        <button type="button" class="text-slate-400 hover:text-slate-600 text-lg" @click="showModal = false"><i class="fa-solid fa-xmark"></i></button>
                    </div>

                    <form class="space-y-3 text-xs" @submit.prevent="save">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Title *</label>
                                <input v-model="form.title" type="text" required :class="inputClass" placeholder="e.g. How to point your domain to Host Divine" @blur="slugify" />
                                <p v-if="form.errors.title" class="text-rose-600 mt-1">{{ form.errors.title }}</p>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Slug *</label>
                                <input v-model="form.slug" type="text" required :class="[inputClass, 'font-mono']" />
                                <p v-if="form.errors.slug" class="text-rose-600 mt-1">{{ form.errors.slug }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Category *</label>
                                <input v-model="form.category" type="text" list="kb-categories" required :class="inputClass" />
                                <datalist id="kb-categories">
                                    <option v-for="category in categories" :key="category" :value="category" />
                                </datalist>
                                <p v-if="form.errors.category" class="text-rose-600 mt-1">{{ form.errors.category }}</p>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Icon</label>
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 shrink-0 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600"><i :class="form.icon || 'fa-regular fa-square'"></i></span>
                                    <input v-model="form.icon" type="text" :class="[inputClass, 'font-mono']" placeholder="fa-solid fa-book-open" />
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Order in category</label>
                                <input v-model.number="form.sort_order" type="number" min="0" :class="inputClass" />
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Short summary</label>
                            <textarea v-model="form.excerpt" rows="2" :class="inputClass" placeholder="One or two sentences shown in lists and search results."></textarea>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Article content *</label>
                            <RichTextEditor v-model="form.content" placeholder="Step 1 … Use Heading 3 for steps, lists for instructions and Code block for commands." />
                            <p v-if="form.errors.content" class="text-rose-600 mt-1">{{ form.errors.content }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Publish date</label>
                                <input v-model="form.published_at" type="date" :class="inputClass" />
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">SEO title (optional)</label>
                                <input v-model="form.meta_title" type="text" :class="inputClass" />
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">SEO description (optional)</label>
                                <input v-model="form.meta_description" type="text" :class="inputClass" />
                            </div>
                        </div>

                        <label class="flex items-center space-x-2 cursor-pointer pt-1">
                            <input v-model="form.is_published" type="checkbox" class="rounded text-blue-600" />
                            <span class="font-semibold text-slate-700">Published (visible on the website)</span>
                        </label>

                        <div class="flex justify-end space-x-2 pt-4 border-t border-slate-100">
                            <button type="button" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-lg font-semibold" @click="showModal = false">Cancel</button>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-blue-600 text-white rounded-lg font-bold shadow-sm disabled:opacity-60">Save Article</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
