<div class="section-heading"><div><span>Organization identity</span><h2>Company information</h2><p>Keep billing, compliance and communication details current.</p></div></div>
<form method="post" class="portal-card portal-form" action="<?=htmlspecialchars(url('/customer/profile'))?>"><?=csrf_field()?>
<div class="form-grid"><?php foreach([
'company_name'=>'Company Name','contact_person'=>'Contact Person','mobile'=>'Mobile','gst_number'=>'GST Number','pan_number'=>'PAN Number','business_type'=>'Business Type','address_line_1'=>'Address Line 1','address_line_2'=>'Address Line 2','city'=>'City','state'=>'State','postal_code'=>'Postal Code','country'=>'Country'
] as $name=>$label):?><label><span><?=$label?></span><input name="<?=$name?>" value="<?=htmlspecialchars($profile[$name]??'')?>"></label><?php endforeach;?>
<label><span>Preferred Communication</span><select name="preferred_communication"><?php foreach(['email','phone','whatsapp','portal'] as $option):?><option value="<?=$option?>" <?=($profile['preferred_communication']??'email')===$option?'selected':''?>><?=ucfirst($option)?></option><?php endforeach;?></select></label></div>
<button class="primary-action" type="submit">Save profile <i data-lucide="check"></i></button></form>
