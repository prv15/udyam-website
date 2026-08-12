<?php $totalPages=max(1,(int)ceil($total/$perPage));$tenderFilters=$tenderFilters??['search'=>$search,'region'=>'','invited_by'=>'','sort'=>'latest'];$tenderFilterOptions=$tenderFilterOptions??['regions'=>[],'organisations'=>[]];$partnerFilters=$partnerFilters??['search'=>$search,'state'=>'','turnover'=>''];$partnerFilterOptions=$partnerFilterOptions??['states'=>[],'turnovers'=>[]];$pageQuery=static function(int $targetPage) use($module,$search,$perPage,$tenderFilters,$partnerFilters):string{$params=$module==='tenders'?array_merge($tenderFilters,['page'=>$targetPage,'per_page'=>$perPage]):($module==='customers'?array_merge($partnerFilters,['page'=>$targetPage,'per_page'=>$perPage]):['page'=>$targetPage,'per_page'=>$perPage,'search'=>$search]);return '?'.http_build_query($params);};$turnoverLabel=static fn(string $value):string=>(string)($partnerFilterOptions['turnovers'][$value]??'Not provided'); ?>
<section class="module-management-page">
<div class="page-header">
    <div>
        <h2><?= htmlspecialchars($definition['title']) ?></h2>
        <p><?= $module==='customers'?'Search, segment and manage every partner relationship.':'Manage '.htmlspecialchars(strtolower($definition['title'])).'.' ?></p>
    </div>
    <div class="page-actions tender-page-actions">
        <?php if ($module === 'tenders'): ?><a class="btn btn-secondary" href="<?= htmlspecialchars(url('/admin/tenders/import')) ?>"><i data-lucide="upload"></i> Bulk import CSV</a><?php endif; ?>
        <a class="btn btn-primary" href="<?= htmlspecialchars(url('/admin/' . $module . '/create')) ?>">
            <i data-lucide="plus"></i> Add <?= htmlspecialchars($definition['singular']) ?>
        </a>
    </div>
</div>

<div class="card<?= $module === 'tenders' ? ' tender-module-card' : '' ?>">
    <form id="module-filters" method="get" action="<?= htmlspecialchars(url('/admin/' . $module)) ?>" class="filters-bar module-live-search-form" data-live-search-form onsubmit="return false">
        <div class="module-search-field"><i data-lucide="search"></i><input class="form-control" type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="<?= $module === 'customers' ? 'Search partners by name, company, email or phone…' : 'Search this section…' ?>" autocomplete="off" data-live-search></div>
        <span class="module-live-result" data-live-result aria-live="polite">Live search</span>
        <?php if($module==='tenders'): ?><span class="admin-tender-filter-label"><i data-lucide="sliders-horizontal"></i> Filters</span><select class="admin-tender-filter" name="region" aria-label="Filter by state" onchange="this.form.submit()"><option value="">All states</option><?php foreach($tenderFilterOptions['regions'] as $region): ?><option value="<?=htmlspecialchars($region)?>" <?= $tenderFilters['region']===$region?'selected':'' ?>><?=htmlspecialchars($region)?></option><?php endforeach;?></select><select class="admin-tender-filter" name="invited_by" aria-label="Filter by organisation" onchange="this.form.submit()"><option value="">All organisations</option><?php foreach($tenderFilterOptions['organisations'] as $organisation): ?><option value="<?=htmlspecialchars($organisation)?>" <?= $tenderFilters['invited_by']===$organisation?'selected':'' ?>><?=htmlspecialchars($organisation)?></option><?php endforeach;?></select><select class="admin-tender-filter" name="sort" aria-label="Sort or filter records" onchange="this.form.submit()"><option value="latest" <?= $tenderFilters['sort']==='latest'?'selected':'' ?>>Latest Date First</option><option value="deadline" <?= $tenderFilters['sort']==='deadline'?'selected':'' ?>>All Dated Tenders</option><option value="expired" <?= $tenderFilters['sort']==='expired'?'selected':'' ?>>Expired</option><option value="ongoing" <?= $tenderFilters['sort']==='ongoing'?'selected':'' ?>>Ongoing</option></select><?php endif; ?>
        <?php if($module==='customers'): ?><span class="admin-tender-filter-label"><i data-lucide="sliders-horizontal"></i> Filters</span><select class="admin-tender-filter" name="state" aria-label="Filter partners by state" onchange="this.form.submit()"><option value="">All states</option><?php foreach($partnerFilterOptions['states'] as $state):?><option value="<?=htmlspecialchars((string)$state)?>" <?= $partnerFilters['state']===$state?'selected':''?>><?=htmlspecialchars((string)$state)?></option><?php endforeach;?></select><select class="admin-tender-filter" name="turnover" aria-label="Filter partners by annual turnover" onchange="this.form.submit()"><option value="">All turnover ranges</option><?php foreach($partnerFilterOptions['turnovers'] as $value=>$label):?><option value="<?=htmlspecialchars($value)?>" <?= $partnerFilters['turnover']===$value?'selected':''?>><?=htmlspecialchars($label)?></option><?php endforeach;?></select><?php if($partnerFilters['state']!==''||$partnerFilters['turnover']!==''||$search!==''):?><a class="partner-filter-reset" href="<?=htmlspecialchars(url('/admin/customers'))?>">Clear</a><?php endif;?><?php endif; ?>
        <label class="table-page-size">Show <select name="per_page" onchange="this.form.submit()"><option value="10" <?= $perPage===10?'selected':'' ?>>10</option><option value="50" <?= $perPage===50?'selected':'' ?>>50</option><option value="100" <?= $perPage===100?'selected':'' ?>>100</option></select></label>
    </form>
    <form id="bulk-delete-form" method="post" action="<?= htmlspecialchars(url('/admin/' . $module . '/bulk-delete')) ?>" onsubmit="return confirm('Delete the selected records? This can be restored only from the database.')">
        <?= csrf_field() ?>
    </form>
    <div class="module-bulk-toolbar" data-bulk-toolbar hidden><span><i data-lucide="check-square"></i><b data-bulk-count>0</b> selected</span><button class="module-bulk-delete" type="submit" form="bulk-delete-form" disabled><i data-lucide="trash-2"></i> Delete selected</button></div>
    <div class="table-responsive">
        <?php if ($module === 'tenders'): ?>
        <table class="table tender-admin-table">
            <thead><tr><th class="module-select-column"><input type="checkbox" aria-label="Select all records" data-bulk-all></th><th>State / Region</th><th>Invited By</th><th>Tender / Project Details</th><th>Last Date</th><th>Time</th><th>Mode</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody><?php if ($records === []): ?><tr><td colspan="9">No records found.</td></tr><?php else: foreach ($records as $record): ?>
                <tr data-live-row><td class="module-select-column"><input form="bulk-delete-form" name="record_ids[]" value="<?= (int) $record['id'] ?>" type="checkbox" aria-label="Select <?= htmlspecialchars((string) $record['title']) ?>" data-bulk-item></td><td><span class="tender-region"><i data-lucide="map-pin"></i><?= htmlspecialchars((string) ($record['region'] ?? '—')) ?></span></td><td><span class="tender-inviter"><?= htmlspecialchars((string) ($record['invited_by'] ?? $record['department'] ?? '—')) ?></span></td><td><div class="tender-project"><strong><?= htmlspecialchars((string) $record['title']) ?></strong><span class="tender-type tender-type-<?= htmlspecialchars((string) ($record['type'] ?? 'tender')) ?>"><?= htmlspecialchars(ucfirst((string) ($record['type'] ?? 'tender'))) ?></span></div></td><td><span class="tender-deadline"><i data-lucide="calendar-days"></i><?= htmlspecialchars((string) ($record['deadline_label'] ?? $record['closing_date'] ?? '—')) ?></span></td><td><?= htmlspecialchars((string) ($record['submission_time'] ?? '—')) ?></td><td><span class="tender-mode"><?= htmlspecialchars((string) ($record['submission_mode'] ?? '—')) ?></span></td><td><span class="badge tender-status"><?= htmlspecialchars(ucfirst((string) $record['status'])) ?></span></td><td><div class="tender-actions"><a class="tender-action tender-action-edit" href="<?= htmlspecialchars(url('/admin/tenders/edit/' . $record['id'])) ?>" aria-label="Edit tender or notice" title="Edit"><i data-lucide="pencil"></i></a><form method="post" action="<?= htmlspecialchars(url('/admin/tenders/delete/' . $record['id'])) ?>" onsubmit="return confirm('Delete this record?')"><?= csrf_field() ?><button class="tender-action tender-action-delete" type="submit" aria-label="Delete tender or notice" title="Delete"><i data-lucide="trash-2"></i></button></form></div></td>
                </tr>
            <?php endforeach; endif; ?></tbody>
            <tbody><tr class="module-live-empty" data-live-empty hidden><td colspan="9">No matching records in this section.</td></tr></tbody>
        </table>
        <?php elseif ($module === 'customers'): ?>
        <table class="table partner-admin-table">
            <thead><tr><th class="module-select-column"><input type="checkbox" aria-label="Select all partners" data-bulk-all></th><th>Company</th><th>User contact</th><th>State</th><th>Annual turnover</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody><?php if($records===[]):?><tr><td colspan="7">No partners match these filters.</td></tr><?php else:foreach($records as $record):$company=trim((string)($record['company_name']??$record['company']??''))?:'Company not provided';$contact=trim((string)($record['contact_person']??$record['title']??''))?:'—';$initials=strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','',$company)?:'P',0,2));?>
            <tr data-live-row><td class="module-select-column"><input form="bulk-delete-form" name="record_ids[]" value="<?=(int)$record['id']?>" type="checkbox" aria-label="Select <?=htmlspecialchars($company)?>" data-bulk-item></td>
            <td><a class="partner-company-cell" href="<?=htmlspecialchars(url('/admin/customers/workspace/'.$record['id']))?>"><span class="partner-company-avatar"><?=htmlspecialchars($initials)?></span><span><strong><?=htmlspecialchars($company)?></strong><small>Partner #<?=(int)$record['id']?></small></span><i data-lucide="arrow-up-right"></i></a></td>
            <td><div class="partner-contact-cell"><strong><?=htmlspecialchars($contact)?></strong><a href="mailto:<?=htmlspecialchars((string)($record['user_email']??$record['email']??''))?>"><?=htmlspecialchars((string)($record['user_email']??$record['email']??'—'))?></a><small><?=htmlspecialchars((string)($record['mobile']??$record['phone']??'—'))?></small></div></td>
            <td><span class="partner-state"><i data-lucide="map-pin"></i><?=htmlspecialchars((string)($record['state']??'Not provided'))?></span></td><td><span class="partner-turnover"><?=htmlspecialchars($turnoverLabel((string)($record['annual_turnover_range']??'')))?></span></td><td><span class="badge"><?=htmlspecialchars(ucfirst((string)($record['user_status']??$record['status'])))?></span></td>
            <td><div class="tender-actions"><a class="tender-action tender-action-edit" href="<?=htmlspecialchars(url('/admin/customers/workspace/'.$record['id']))?>" title="Open workspace"><i data-lucide="layout-dashboard"></i></a><a class="tender-action" href="<?=htmlspecialchars(url('/admin/customers/edit/'.$record['id']))?>" title="Edit"><i data-lucide="pencil"></i></a><form method="post" action="<?=htmlspecialchars(url('/admin/customers/delete/'.$record['id']))?>" onsubmit="return confirm('Delete this partner?')"><?=csrf_field()?><button class="tender-action tender-action-delete" type="submit" title="Delete"><i data-lucide="trash-2"></i></button></form></div></td></tr>
            <?php endforeach;endif;?></tbody><tbody><tr class="module-live-empty" data-live-empty hidden><td colspan="7">No matching partners on this page.</td></tr></tbody>
        </table>
        <?php else: ?>
        <table class="table">
            <thead><tr><th class="module-select-column"><input type="checkbox" aria-label="Select all records" data-bulk-all></th><th>Title</th><th>Status</th><th>Order</th><th>Updated</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($records === []): ?>
                <tr><td colspan="6">No records found.</td></tr>
            <?php else: foreach ($records as $record): ?>
                <tr data-live-row><td class="module-select-column"><input form="bulk-delete-form" name="record_ids[]" value="<?= (int) $record['id'] ?>" type="checkbox" aria-label="Select <?= htmlspecialchars((string) $record['title']) ?>" data-bulk-item></td>
                    <td>
                        <?php if ($module === 'customers'): ?>
                            <a class="module-partner-link" href="<?= htmlspecialchars(url('/admin/customers/workspace/' . $record['id'])) ?>">
                                <?= htmlspecialchars((string) $record['title']) ?><i data-lucide="arrow-up-right"></i>
                            </a>
                        <?php else: ?>
                            <?= htmlspecialchars((string) $record['title']) ?>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge"><?= htmlspecialchars(ucfirst((string) $record['status'])) ?></span></td>
                    <td><?= (int) $record['sort_order'] ?></td>
                    <td><?= htmlspecialchars((string) $record['updated_at']) ?></td>
                    <td>
                        <?php if ($module === 'customers'): ?><a class="btn btn-sm btn-primary" href="<?= htmlspecialchars(url('/admin/customers/workspace/' . $record['id'])) ?>">Open workspace</a><?php endif; ?>
                        <a class="btn btn-sm" href="<?= htmlspecialchars(url('/admin/' . $module . '/edit/' . $record['id'])) ?>">Edit</a>
                        <form method="post" action="<?= htmlspecialchars(url('/admin/' . $module . '/delete/' . $record['id'])) ?>" class="d-inline" onsubmit="return confirm('Delete this record?')">
                            <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
            <tbody><tr class="module-live-empty" data-live-empty hidden><td colspan="6">No matching records in this section.</td></tr></tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php if ($totalPages > 1): ?>
        <nav class="pagination erp-pagination" aria-label="Table pages">
            <span class="erp-pagination-summary">Showing <?= min($total, (($page - 1) * $perPage) + 1) ?>–<?= min($total, $page * $perPage) ?> of <?= $total ?></span>
            <div class="erp-pagination-pages">
            <a class="erp-pagination-arrow <?= $page <= 1 ? 'is-disabled' : '' ?>" <?= $page <= 1 ? 'aria-disabled="true"' : 'href="' . htmlspecialchars($pageQuery($page-1)) . '"' ?> aria-label="Previous page">‹</a>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="<?= $i === $page ? 'active' : '' ?>" href="<?=htmlspecialchars($pageQuery($i))?>"><?= $i ?></a>
            <?php endfor; ?>
            <a class="erp-pagination-arrow <?= $page >= $totalPages ? 'is-disabled' : '' ?>" <?= $page >= $totalPages ? 'aria-disabled="true"' : 'href="' . htmlspecialchars($pageQuery($page+1)) . '"' ?> aria-label="Next page">›</a>
            </div>
        </nav>
    <?php endif; ?>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{const search=document.querySelector('[data-live-search]'),rows=[...document.querySelectorAll('[data-live-row]')],empty=document.querySelector('[data-live-empty]'),result=document.querySelector('[data-live-result]');const filter=()=>{const query=(search?.value||'').trim().toLowerCase();let visible=0;rows.forEach(row=>{const match=!query||row.textContent.toLowerCase().includes(query);row.hidden=!match;if(match)visible++;});if(empty)empty.hidden=visible!==0;if(result)result.textContent=query?`${visible} match${visible===1?'':'es'}`:'Live search';};search?.addEventListener('input',filter);filter();const items=[...document.querySelectorAll('[data-bulk-item]')],all=document.querySelector('[data-bulk-all]'),toolbar=document.querySelector('[data-bulk-toolbar]'),count=document.querySelector('[data-bulk-count]'),button=document.querySelector('.module-bulk-delete');const refresh=()=>{const selected=items.filter(item=>item.checked).length;if(toolbar)toolbar.hidden=selected===0;if(count)count.textContent=selected;if(button)button.disabled=selected===0;if(all)all.checked=items.length>0&&selected===items.length;};all?.addEventListener('change',()=>{items.forEach(item=>item.checked=all.checked);refresh();});items.forEach(item=>item.addEventListener('change',refresh));refresh();});</script>
</section>
