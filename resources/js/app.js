import './bootstrap';



  const outEl = document.getElementById('out');
  const log = (x) => outEl.textContent = JSON.stringify(x, null, 2);

  const fmtType = (t) => ({
    deposit: 'إيداع',
    withdrawal: 'سحب',
    transfer_debit: 'تحويل - خصم',
    transfer_credit: 'تحويل - إضافة'
  }[t] || t);

  const uuid = () => (crypto?.randomUUID ? crypto.randomUUID() : (Date.now() + '-' + Math.random()));

  const api = async (method, url, body=null, idemKey=null) => {
    const headers = { 'Content-Type': 'application/json' };
    if (idemKey) headers['Idempotency-Key'] = idemKey;

    const res = await fetch('/api' + url, {
      method,
      headers,
      body: body ? JSON.stringify(body) : null
    });

    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch { data = { raw: text }; }
    if (!res.ok) throw data;
    return data;
  };

  // State
  let wallets = [];
  let selectedWalletId = null;

 const refreshSummary = async () => {
  document.getElementById('totalWallets').textContent = wallets.length.toString();

  if (!selectedWalletId) {
    document.getElementById('selectedWalletLabel').textContent = '—';
    document.getElementById('selectedBalance').textContent = '—';
    return;
  }

  const w = wallets.find(x => Number(x.id) === Number(selectedWalletId));
  document.getElementById('selectedWalletLabel').textContent =
    w ? `#${w.id} — ${w.owner_name} (${w.currency})` : `#${selectedWalletId}`;

  try {
    const b = await api('GET', `/wallets/${selectedWalletId}/balance`);
    document.getElementById('selectedBalance').textContent = b.balance ?? '—';
  } catch (e) { log(e); }
};
  const loadWallets = async (owner=null, currency=null) => {
    const params = new URLSearchParams();
    if (owner) params.set('owner_name', owner);
    if (currency) params.set('currency', currency.toUpperCase());

    const url = '/wallets' + (params.toString() ? `?${params.toString()}` : '');
    wallets = await api('GET', url);

    const sel = document.getElementById('walletSelect');
    sel.innerHTML = `<option value="">— اختر —</option>` + wallets.map(w => {
      return `<option value="${w.id}">#${w.id} — ${w.owner_name} (${w.currency}) — Balance: ${w.balance}</option>`;
    }).join('');

    await refreshSummary();
  };

  // Health
  document.getElementById('healthBtn').onclick = async () => {
    try {
      const r = await api('GET', '/health');
      log(r);
      document.getElementById('healthPill').textContent = `الحالة: ${r.status}`;
      document.getElementById('healthPill').className = "text-xs px-3 py-2 rounded-full bg-emerald-900/40 border border-emerald-700 text-emerald-200";
    } catch (e) {
      log(e);
      document.getElementById('healthPill').textContent = "الحالة: خطأ";
      document.getElementById('healthPill').className = "text-xs px-3 py-2 rounded-full bg-rose-900/40 border border-rose-700 text-rose-200";
    }
  };

  // Buttons
  document.getElementById('clearOutBtn').onclick = () => outEl.textContent = '';

  document.getElementById('refreshWalletsBtn').onclick = async () => {
    try { await loadWallets(); log({ok:true, message:'تم تحديث المحافظ'}); } catch (e) { log(e); }
  };

  document.getElementById('applyWalletFiltersBtn').onclick = async () => {
    try {
      const owner = document.getElementById('filterOwner').value.trim();
      const cur = document.getElementById('filterCurrency').value.trim();
      await loadWallets(owner || null, cur || null);
      log({ok:true, filters:{owner, currency:cur}});
    } catch (e) { log(e); }
  };

  document.getElementById('walletSelect').onchange = async (ev) => {
    selectedWalletId = ev.target.value ? parseInt(ev.target.value, 10) : null;
    await refreshSummary();
  };

  // Create wallet
  document.getElementById('createWalletBtn').onclick = async () => {
    try {
      const owner = document.getElementById('createOwner').value.trim();
      const currency = document.getElementById('createCurrency').value.trim().toUpperCase();
      const r = await api('POST', '/wallets', { owner_name: owner, currency });
      log(r);
      await loadWallets();
      selectedWalletId = r.id;
      document.getElementById('walletSelect').value = r.id;
      await refreshSummary();
    } catch (e) { log(e); }
  };

  // Deposit
  document.getElementById('depositBtn').onclick = async () => {
    try {
      if (!selectedWalletId) throw {error: 'اختر محفظة أولاً'};
      const amount = parseInt(document.getElementById('depositAmount').value, 10);
      const idem = (document.getElementById('depositIdem').value.trim() || uuid());
      const r = await api('POST', `/wallets/${selectedWalletId}/deposit`, { amount }, idem);
      log({idempotency_key: idem, result: r});
      await loadWallets();
      await refreshSummary();
    } catch (e) { log(e); }
  };

  // Withdraw
  document.getElementById('withdrawBtn').onclick = async () => {
    try {
      if (!selectedWalletId) throw {error: 'اختر محفظة أولاً'};
      const amount = parseInt(document.getElementById('withdrawAmount').value, 10);
      const idem = (document.getElementById('withdrawIdem').value.trim() || uuid());
      const r = await api('POST', `/wallets/${selectedWalletId}/withdraw`, { amount }, idem);
      log({idempotency_key: idem, result: r});
      await loadWallets();
      await refreshSummary();
    } catch (e) { log(e); }
  };

  // Transfer
  document.getElementById('transferBtn').onclick = async () => {
    try {
      const from = parseInt(document.getElementById('transferFrom').value, 10);
      const to = parseInt(document.getElementById('transferTo').value, 10);
      const amount = parseInt(document.getElementById('transferAmount').value, 10);
      const idem = (document.getElementById('transferIdem').value.trim() || uuid()); // required
      const r = await api('POST', `/transfers`, { from_wallet_id: from, to_wallet_id: to, amount }, idem);
      log({idempotency_key: idem, result: r});
      await loadWallets();
      await refreshSummary();
    } catch (e) { log(e); }
  };

  // Transactions (history)
  const renderTx = (pageObj) => {
    const body = document.getElementById('txBody');
    body.innerHTML = '';

    const items = pageObj.data || pageObj; // if you return plain array
    (items || []).forEach(tx => {
      const rel = tx.related_wallet_id ? `#${tx.related_wallet_id}` : '—';
      body.innerHTML += `
        <tr class="border-t border-slate-800">
          <td class="p-3 text-slate-200">${tx.id}</td>
          <td class="p-3 text-slate-200">${fmtType(tx.type)}</td>
          <td class="p-3 text-slate-200">${tx.amount}</td>
          <td class="p-3 text-slate-200">${rel}</td>
          <td class="p-3 text-slate-400">${tx.created_at}</td>
        </tr>
      `;
    });

    if (pageObj.meta) {
      document.getElementById('txMeta').textContent =
        `صفحة ${pageObj.meta.current_page} من ${pageObj.meta.last_page} — إجمالي عناصر: ${pageObj.meta.total}`;
    } else if (pageObj.current_page) {
      document.getElementById('txMeta').textContent =
        `صفحة ${pageObj.current_page} من ${pageObj.last_page} — إجمالي عناصر: ${pageObj.total}`;
    } else {
      document.getElementById('txMeta').textContent = `تم عرض ${items.length} عملية`;
    }
  };

  const loadTx = async () => {
    if (!selectedWalletId) throw {error:'اختر محفظة أولاً'};
    const type = document.getElementById('txType').value.trim();
    const from = document.getElementById('txFrom').value.trim();
    const to = document.getElementById('txTo').value.trim();
    const page = parseInt(document.getElementById('txPage').value, 10) || 1;
    const per = parseInt(document.getElementById('txPerPage').value, 10) || 20;

    const params = new URLSearchParams();
    if (type) params.set('type', type);
    if (from) params.set('from', from);
    if (to) params.set('to', to);
    params.set('page', page);
    params.set('per_page', per);

    const r = await api('GET', `/wallets/${selectedWalletId}/transactions?${params.toString()}`);
    renderTx(r);
    log({filters:{type, from, to, page, per_page: per}, result:'transactions loaded'});
  };

  document.getElementById('loadTxBtn').onclick = async () => { try { await loadTx(); } catch (e) { log(e); } };

  document.getElementById('prevTxBtn').onclick = async () => {
    const p = Math.max(1, (parseInt(document.getElementById('txPage').value,10) || 1) - 1);
    document.getElementById('txPage').value = p;
    try { await loadTx(); } catch (e) { log(e); }
  };

  document.getElementById('nextTxBtn').onclick = async () => {
    const p = (parseInt(document.getElementById('txPage').value,10) || 1) + 1;
    document.getElementById('txPage').value = p;
    try { await loadTx(); } catch (e) { log(e); }
  };

  // init
  (async () => {
    try {
      await loadWallets();
      document.getElementById('healthBtn').click();
    } catch (e) { log(e); }
  })();
