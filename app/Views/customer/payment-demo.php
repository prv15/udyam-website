<?php $amount=number_format((float)$order['amount'],2); ?>
<section class="checkout-shell">
    <div class="checkout-trust">
        <div class="checkout-provider"><span>Pay</span>Yantra <small>DEMO SANDBOX</small></div>
        <i data-lucide="shield-check"></i>
        <div><strong>Secure hosted checkout</strong><small>No real money will be charged in demo mode.</small></div>
    </div>
    <div class="checkout-grid">
        <article class="checkout-summary">
            <span>Udyam Project Funding</span>
            <h2><?=htmlspecialchars($order['plan_name']??'Subscription')?></h2>
            <p><?=htmlspecialchars($order['plan_subtitle']??'')?></p>
            <dl><div><dt>Invoice</dt><dd><?=htmlspecialchars($order['invoice_number'])?></dd></div><div><dt>Amount</dt><dd>₹<?=$amount?></dd></div><div><dt>Gateway</dt><dd>PayYantra Demo</dd></div></dl>
            <div class="checkout-total"><span>Total payable</span><strong>₹<?=$amount?></strong></div>
        </article>
        <article class="checkout-payment">
            <span>Choose a demo payment method</span>
            <div class="payment-method active"><i data-lucide="scan-line"></i><div><strong>UPI</strong><small>Instant sandbox confirmation</small></div><b>Selected</b></div>
            <div class="payment-method"><i data-lucide="credit-card"></i><div><strong>Card</strong><small>Visa, Mastercard and RuPay</small></div></div>
            <div class="payment-method"><i data-lucide="landmark"></i><div><strong>Net Banking</strong><small>All major Indian banks</small></div></div>
            <form method="post" action="<?=htmlspecialchars(url('/customer/payments/demo/'.$order['public_token'].'/complete'))?>"><?=csrf_field()?><button class="primary-action checkout-pay">Simulate successful payment <i data-lucide="lock-keyhole"></i></button></form>
            <form method="post" action="<?=htmlspecialchars(url('/customer/payments/demo/'.$order['public_token'].'/fail'))?>"><?=csrf_field()?><button class="checkout-fail">Simulate declined payment</button></form>
            <small class="checkout-note"><i data-lucide="info"></i> Demo mode creates a real portal invoice and subscription record, but never contacts a bank.</small>
        </article>
    </div>
</section>
