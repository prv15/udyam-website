<?php
$profile = $data['profile'] ?? [];
$profileComplete = !empty($profile['city']) && !empty($profile['state']) && !empty($profile['email_verified_at']);
$hasSubscription = !empty($data['subscription']);
$tenders = $data['tenders'] ?? [];
?>
<section class="welcome-panel"><div><span>Welcome back</span><h2><?=htmlspecialchars($customer['first_name'])?>, your Udyam workspace is ready.</h2><p>Track services, applications, documents, subscriptions and billing from one secure place.</p></div><a href="<?=htmlspecialchars(url('/customer/services'))?>" class="primary-action">Explore services <i data-lucide="arrow-right"></i></a></section>

<?php if(!$profileComplete):?><section class="dash-alert dash-alert-profile"><i data-lucide="user-round-cog"></i><div><strong>Your profile isn't complete yet</strong><p>Add your city and state, and confirm your email address, so we can match you with the right opportunities and keep your invoices accurate.</p></div><a href="<?=htmlspecialchars(url('/customer/profile'))?>" class="ghost-action">Complete profile <i data-lucide="arrow-right"></i></a></section><?php endif;?>

<section class="metric-grid">
<?php foreach([
['sparkles','Active Subscription',$data['subscription']['plan_name']??'No active plan'],
['clipboard-clock','Pending Applications',(string)$data['pendingApplications']],
['badge-check','Approved Services',(string)$data['approvedServices']],
['wallet-cards','Outstanding Payments','₹'.number_format((float)$data['outstanding'],2)]
] as $metric):?><article class="metric"><i data-lucide="<?=$metric[0]?>"></i><span><?=$metric[1]?></span><strong><?=htmlspecialchars($metric[2])?></strong></article><?php endforeach;?></section>

<?php if(!$hasSubscription):?><section class="subscribe-cta"><div class="subscribe-cta-glow"></div><div class="subscribe-cta-body"><span>Unlock the full workspace</span><h3>Subscribe to see every notice, tender and funding opportunity we track.</h3><p>Your plan also unlocks priority application support and document review.</p></div><a href="<?=htmlspecialchars(url('/customer/plans'))?>" class="subscribe-cta-button">Subscribe now <i data-lucide="arrow-right"></i></a></section><?php endif;?>

<section class="portal-card tenders-card"><div class="card-heading"><div><span>Government Updates</span><h3>Notices &amp; Tenders</h3></div><a href="<?=htmlspecialchars(url('/customer/plans'))?>"><?=$hasSubscription?'View all':'Unlock access'?></a></div>
<?php if(!$tenders):?><div class="empty">No active notices or tenders right now.</div><?php else:?><div class="tender-mini-table<?=$hasSubscription?'':' is-locked'?>"><?php foreach($tenders as $item):
    $title = (string)($item['title']??'Untitled');
    $lead = mb_substr($title,0,10); $rest = mb_substr($title,10);
    $closing = $item['closing_date']??null;
?><div class="tender-mini-row"><div class="tender-mini-title"><strong><?=htmlspecialchars($lead)?><?php if($rest!==''):?><span class="<?=$hasSubscription?'':'tender-blur'?>"><?=htmlspecialchars($rest)?></span><?php endif;?></strong><small><?=htmlspecialchars(ucfirst((string)($item['type']??'Tender')))?></small></div><div class="tender-mini-meta <?=$hasSubscription?'':'tender-blur'?>"><?=htmlspecialchars($item['department']??'—')?></div><div class="tender-mini-date <?=$hasSubscription?'':'tender-blur'?>"><?=$closing?htmlspecialchars(date('d M Y',strtotime($closing))):'—'?></div><div><?php if($hasSubscription):?><a class="tender-detail" href="<?=htmlspecialchars($item['document_url']??'#')?>">View <span>→</span></a><?php else:?><a class="tender-unlock" href="<?=htmlspecialchars(url('/customer/plans'))?>">Unlock <span>↗</span></a><?php endif;?></div></div><?php endforeach;?></div><?php endif;?></section>

<div class="portal-grid two"><section class="portal-card"><div class="card-heading"><div><span>Billing</span><h3>Recent invoices</h3></div><a href="<?=htmlspecialchars(url('/customer/billing/invoices'))?>">View all</a></div><?php if(!$data['invoices']):?><div class="empty">No invoices yet.</div><?php else:?><div class="portal-list"><?php foreach($data['invoices'] as $row):?><a href="<?=htmlspecialchars(url('/customer/billing/invoices/'.$row['id']))?>"><span><strong><?=htmlspecialchars($row['invoice_number'])?></strong><small><?=htmlspecialchars($row['issue_date'])?></small></span><b>₹<?=number_format((float)$row['total_amount'],2)?></b></a><?php endforeach;?></div><?php endif;?></section>
<section class="portal-card"><div class="card-heading"><div><span>Updates</span><h3>Notifications</h3></div><a href="<?=htmlspecialchars(url('/customer/notifications'))?>">View all</a></div><?php if(!$data['notifications']):?><div class="empty">You are all caught up.</div><?php else:?><div class="portal-list"><?php foreach($data['notifications'] as $row):?><div><i data-lucide="bell-ring"></i><span><strong><?=htmlspecialchars($row['title'])?></strong><small><?=htmlspecialchars($row['message'])?></small></span></div><?php endforeach;?></div><?php endif;?></section></div>
<section class="portal-card"><div class="card-heading"><div><span>Audit trail</span><h3>Recent activity</h3></div><a href="<?=htmlspecialchars(url('/customer/activity'))?>">Full history</a></div><div class="timeline"><?php foreach($data['activity'] as $row):?><div><i></i><span><strong><?=htmlspecialchars(ucwords(str_replace('_',' ',$row['action'])))?></strong><small><?=htmlspecialchars($row['description']??'')?> · <?=htmlspecialchars($row['created_at'])?></small></span></div><?php endforeach;?></div></section>
