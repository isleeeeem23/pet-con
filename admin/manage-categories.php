<?php
require_once 'includes/auth.php';
$page_title = 'Manage Pet Categories';

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

include 'includes/header.php';
?>

<div class="max-w-4xl">
    <div id="alert-container" class="mb-6 hidden"></div>

    <div class="glass p-8 rounded-3xl mb-8">
        <h3 class="text-xl font-bold text-white mb-6">Add New Category</h3>
        <form onsubmit="addEntry(event, 'categories')" class="flex gap-4">
            <input type="text" id="new-category-name" required placeholder="Category name (e.g. Reptiles)"
                class="flex-1 bg-slate-800 border-none rounded-xl py-3 px-4 text-white focus:ring-2 focus:ring-indigo-500 transition-all">
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                <i class="fa-solid fa-plus me-2"></i> Add
            </button>
        </form>
    </div>

    <div class="glass rounded-3xl overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-800/50 text-slate-400 border-b border-slate-700">
                <tr>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">ID</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Category Name</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Added On</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="lookup-table-body" class="divide-y divide-slate-800">
                <?php foreach ($categories as $cat): ?>
                    <tr id="row-<?php echo $cat['id']; ?>" class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4 text-slate-500 text-sm">#<?php echo $cat['id']; ?></td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-white"><?php echo htmlspecialchars($cat['name']); ?></span>
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-xs">
                            <?php echo date('M d, Y', strtotime($cat['created_at'])); ?></td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="deleteEntry(<?php echo $cat['id']; ?>, 'categories')"
                                class="p-2 rounded-lg hover:bg-rose-900/30 text-slate-400 hover:text-rose-400 transition-all">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    async function addEntry(event, table) {
        event.preventDefault();
        const input = document.getElementById('new-category-name');
        const name = input.value.trim();
        if (!name) return;

        try {
            const response = await fetch('api/manage-lookup.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add', table: table, name: name })
            });
            const data = await response.json();

            if (data.success) {
                window.location.reload();
            } else {
                showAlert(data.error || 'Failed to add entry.', 'error');
            }
        } catch (error) {
            showAlert('An error occurred.', 'error');
        }
    }

    async function deleteEntry(id, table) {
        if (!confirm('Are you sure you want to delete this category?')) return;

        try {
            const response = await fetch('api/manage-lookup.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', table: table, id: id })
            });
            const data = await response.json();

            if (data.success) {
                document.getElementById(`row-${id}`).remove();
                showAlert('Entry deleted successfully.', 'success');
            } else {
                showAlert(data.error || 'Failed to delete entry.', 'error');
            }
        } catch (error) {
            showAlert('An error occurred.', 'error');
        }
    }

    function showAlert(message, type) {
        const container = document.getElementById('alert-container');
        container.className = `mb-6 p-4 rounded-xl flex items-center gap-3 ${type === 'success' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'}`;
        container.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i><span>${message}</span>`;
        container.classList.remove('hidden');
        setTimeout(() => container.classList.add('hidden'), 3000);
    }
</script>

<?php include 'includes/footer.php'; ?>