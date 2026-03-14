<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$user = currentUser();

function navLink($href, $icon, $label, $current) {
    $file = basename($href);
    $active = ($current === $file) ? 'active' : '';
    echo "<a href='/coreinventory/$href' class='sidebar-link $active flex items-center gap-3 px-4 py-3 text-sm text-slate-400 rounded-r-lg'>$icon <span>$label</span></a>";
}
?>
<aside class="w-60 flex-shrink-0 flex flex-col" style="background:#0d0f12; border-right:1px solid #1c1f24; height:100vh;">
    <div class="px-5 py-5 border-b border-dark-700 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#22a36a;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        </div>
        <span class="font-bold text-white text-base tracking-tight">CoreInventory</span>
    </div>
    <div class="px-5 py-2 border-b" style="border-color:#1c1f24;">
        <?php $roleColors=['admin'=>'#22a36a','manager'=>'#60a5fa','staff'=>'#94a3b8']; $rc=$roleColors[$user['role']]??'#94a3b8'; ?>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:<?=$rc?>22;color:<?=$rc?>;"><?=strtoupper($user['role'])?></span>
        <span class="text-xs text-slate-600 ml-2"><?=htmlspecialchars($user['name'])?></span>
    </div>
    <nav class="flex-1 py-4 overflow-y-auto space-y-0.5">
        <p class="px-4 py-2 text-xs font-semibold text-slate-600 uppercase tracking-wider">Overview</p>
        <?php navLink('pages/dashboard.php','<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>','Dashboard',$currentPage); ?>
        <p class="px-4 py-2 mt-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Inventory</p>
        <?php navLink('pages/products.php','<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>','Products',$currentPage); ?>
        <?php if(hasRole('admin')): navLink('pages/warehouses.php','<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>','Warehouses',$currentPage); endif; ?>
        <p class="px-4 py-2 mt-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Operations</p>
        <?php navLink('pages/receipts.php','<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>','Receipts',$currentPage); ?>
        <?php navLink('pages/deliveries.php','<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 16 12 12 16 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>','Deliveries',$currentPage); ?>
        <?php navLink('pages/transfers.php','<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>','Transfers',$currentPage); ?>
        <?php if(hasRole('admin','manager')): navLink('pages/adjustments.php','<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>','Adjustments',$currentPage);
        else: ?>
        <div class="flex items-center gap-3 px-4 py-3 text-sm" style="opacity:0.3;border-left:3px solid transparent;cursor:not-allowed;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span class="text-slate-500">Adjustments</span>
        </div>
        <?php endif; ?>
        <?php navLink('pages/move_history.php','<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>','Move History',$currentPage); ?>
        <?php if(hasRole('admin')): ?>
        <p class="px-4 py-2 mt-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Admin</p>
        <?php navLink('pages/users.php','<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>','Manage Users',$currentPage); ?>
        <?php endif; ?>
    </nav>
    <div class="p-4 border-t border-dark-700">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold" style="background:#22a36a22;color:#22a36a;"><?=strtoupper(substr($user['name'],0,1))?></div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate"><?=htmlspecialchars($user['name'])?></p>
                <p class="text-xs text-slate-500 capitalize"><?=$user['role']?></p>
            </div>
            <a href="/coreinventory/logout.php" title="Logout" class="text-slate-600 hover:text-red-400 transition-colors">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </a>
        </div>
    </div>
</aside>
<!-- Chatbot Widget -->

<script src="https://www.gptbots.ai/widget/wenbn0iaopzeppmhvrcnyxo/chat.js"></script>
<main class="flex-1 overflow-y-auto" style="min-width:0; padding:0;">

