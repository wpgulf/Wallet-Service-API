<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>خدمات المحفظة   | Wallet Service</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    /* Scrollbar subtle */
    ::-webkit-scrollbar { height: 10px; width: 10px; }
    ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.35).35); border-radius: 999px; }
  </style>
 @vite(['resources/css/app.css', 'resources/js/app.js'])


</head>

<body class="bg-slate-950 text-slate-100 min-h-screen">
  <!-- Header -->
  <header class="border-b border-slate-800 bg-slate-950/80 backdrop-blur sticky top-0 z-20">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <img
  src="{{ asset('images/logo.svg') }}"
  alt="Bank Logo"
/>
        <div>
          <h1 class="text-xl font-bold">خدمات المحفظة </h1>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button id="healthBtn" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 hover:bg-slate-800 text-sm">
          فحص الخدمة
        </button>
        <span id="healthPill" class="text-xs px-3 py-2 rounded-full bg-slate-900 border border-slate-800 text-slate-300">
          الحالة: غير معروف
        </span>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-6 py-6 grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Left: Panels -->
    <section class="xl:col-span-2 space-y-6">

      <!-- Summary / Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
          <p class="text-slate-400 text-sm">إجمالي المحافظ</p>
          <p id="totalWallets" class="text-3xl font-bold mt-2">—</p>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
          <p class="text-slate-400 text-sm">المحفظة المختارة</p>
          <p id="selectedWalletLabel" class="text-lg font-semibold mt-2">—</p>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
          <p class="text-slate-400 text-sm">الرصيد الحالي</p>
          <p id="selectedBalance" class="text-3xl font-bold mt-2">—</p>
          <p class="text-slate-400 text-xs mt-1">*بالوحدات الصغيرة (Minor Units)</p>
        </div>
      </div>

      <!-- Accordion -->
      <div class="rounded-2xl border border-slate-800 bg-slate-900 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
          <h2 class="font-bold">لوحة العمليات</h2>
          <div class="flex items-center gap-2">
            <button id="refreshWalletsBtn" class="px-3 py-2 rounded-lg bg-slate-950 border border-slate-800 hover:bg-slate-800 text-sm">
              تحديث المحافظ
            </button>
          </div>
        </div>

        <!-- Section 1: Wallet Management -->
        <details open class="group border-b border-slate-800">
          <summary class="cursor-pointer list-none px-5 py-4 flex items-center justify-between hover:bg-slate-950/60">
            <div>
              <p class="font-semibold">1) إدارة المحافظ</p>
              <p class="text-slate-400 text-sm">إنشاء محفظة + عرض محفظة + قائمة مع فلاتر</p>
            </div>
            <span class="text-slate-400 group-open:rotate-180 transition">⌄</span>
          </summary>

          <div class="px-5 pb-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Create wallet -->
            <div class="rounded-xl border border-slate-800 bg-slate-950 p-4">
              <p class="font-semibold mb-3">إنشاء محفظة جديدة</p>
              <div class="grid gap-3">
                <input id="createOwner" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="اسم المالك (مثال: Ali)" />
                <input id="createCurrency" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="العملة (مثال: SAR)" value="SAR" maxlength="3" />
                <button id="createWalletBtn" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 font-semibold">
                  إنشاء
                </button>
                <p class="text-xs text-slate-400">سيبدأ الرصيد = 0</p>
              </div>
            </div>

            <!-- List wallets with filters -->
            <div class="rounded-xl border border-slate-800 bg-slate-950 p-4">
              <p class="font-semibold mb-3">قائمة المحافظ + فلاتر</p>
              <div class="grid gap-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <input id="filterOwner" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="فلتر بالمالك (اختياري)" />
                  <input id="filterCurrency" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="فلتر بالعملة (اختياري)" maxlength="3" />
                </div>
                <button id="applyWalletFiltersBtn" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 font-semibold">
                  تطبيق الفلاتر
                </button>

                <div class="mt-2">
                  <label class="text-xs text-slate-400">اختيار محفظة للعمل عليها</label>
                  <select id="walletSelect" class="mt-1 w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-800">
                    <option value="">— اختر —</option>
                  </select>
                  <p class="text-xs text-slate-400 mt-2">اختيار المحفظة سيحدث الرصيد ويجهز قسم العمليات</p>
                </div>
              </div>
            </div>
          </div>
        </details>

        <!-- Section 2: Deposits & Withdrawals -->
        <details class="group border-b border-slate-800">
          <summary class="cursor-pointer list-none px-5 py-4 flex items-center justify-between hover:bg-slate-950/60">
            <div>
              <p class="font-semibold">2) الإيداع والسحب</p>
              <p class="text-slate-400 text-sm">يدعم Idempotency-Key لمنع التكرار</p>
            </div>
            <span class="text-slate-400 group-open:rotate-180 transition">⌄</span>
          </summary>

          <div class="px-5 pb-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="rounded-xl border border-slate-800 bg-slate-950 p-4">
              <p class="font-semibold mb-3">إيداع</p>
              <div class="grid gap-3">
                <input id="depositAmount" type="number" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="المبلغ (Minor Units)" min="1" />
                <input id="depositIdem" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="Idempotency-Key (اتركه فارغ لإنشاء تلقائي)" />
                <button id="depositBtn" class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-500 font-semibold">
                  تنفيذ الإيداع
                </button>
                <p class="text-xs text-slate-400">تكرار نفس الـ key لن يكرر الأموال.</p>
              </div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-950 p-4">
              <p class="font-semibold mb-3">سحب</p>
              <div class="grid gap-3">
                <input id="withdrawAmount" type="number" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="المبلغ (Minor Units)" min="1" />
                <input id="withdrawIdem" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="Idempotency-Key (اتركه فارغ لإنشاء تلقائي)" />
                <button id="withdrawBtn" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 font-semibold">
                  تنفيذ السحب
                </button>
                <p class="text-xs text-slate-400">يرفض إذا الرصيد غير كافٍ.</p>
              </div>
            </div>
          </div>
        </details>

        <!-- Section 3: Transfers -->
        <details class="group border-b border-slate-800">
          <summary class="cursor-pointer list-none px-5 py-4 flex items-center justify-between hover:bg-slate-950/60">
            <div>
              <p class="font-semibold">3) التحويلات البنكية</p>
              <p class="text-slate-400 text-sm">عملية ذرّية (Debit/Credit) + منع Self-transfer + نفس العملة فقط</p>
            </div>
            <span class="text-slate-400 group-open:rotate-180 transition">⌄</span>
          </summary>

          <div class="px-5 pb-5">
            <div class="rounded-xl border border-slate-800 bg-slate-950 p-4">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input id="transferFrom" type="number" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="من محفظة (ID)" min="1" />
                <input id="transferTo" type="number" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="إلى محفظة (ID)" min="1" />
                <input id="transferAmount" type="number" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="المبلغ" min="1" />
                <button id="transferBtn" class="px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-500 font-semibold">
                  تنفيذ التحويل
                </button>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                <input id="transferIdem" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="Idempotency-Key (مطلوب للتحويل — سيُنشأ تلقائيًا إن تركته)" />
                <div class="text-xs text-slate-400 flex items-center">
                  التحويل يسجل طرفين (Debit/Credit) ويرتبط بـ transfer_pairs
                </div>
              </div>
            </div>
          </div>
        </details>

        <!-- Section 4: Transaction History -->
        <details class="group">
          <summary class="cursor-pointer list-none px-5 py-4 flex items-center justify-between hover:bg-slate-950/60">
            <div>
              <p class="font-semibold">4) سجل العمليات + فلاتر</p>
              <p class="text-slate-400 text-sm">فلترة بالنوع، نطاق تاريخ، Pagination</p>
            </div>
            <span class="text-slate-400 group-open:rotate-180 transition">⌄</span>
          </summary>

          <div class="px-5 pb-5">
            <div class="rounded-xl border border-slate-800 bg-slate-950 p-4">
              <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <select id="txType" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800">
                  <option value="">كل الأنواع</option>
                  <option value="deposit">إيداع</option>
                  <option value="withdrawal">سحب</option>
                  <option value="transfer_debit">تحويل - خصم</option>
                  <option value="transfer_credit">تحويل - إضافة</option>
                </select>
                <input id="txFrom" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="من تاريخ (YYYY-MM-DD)" />
                <input id="txTo" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="إلى تاريخ (YYYY-MM-DD)" />
                <input id="txPage" type="number" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="Page" value="1" min="1" />
                <input id="txPerPage" type="number" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800" placeholder="Per Page" value="20" min="1" max="100" />

              </div>
 <p class="text-xs text-slate-400 text-left">
    اختر عدد العمليات التي تريد عرضها في الصفحة (Per Page)
  </p>
              <div class="flex gap-3 mt-3">
                <button id="loadTxBtn" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 font-semibold">
                  تحميل السجل
                </button>
                <button id="prevTxBtn" class="px-4 py-2 rounded-lg bg-slate-900 border border-slate-800 hover:bg-slate-800">
                  السابق
                </button>
                <button id="nextTxBtn" class="px-4 py-2 rounded-lg bg-slate-900 border border-slate-800 hover:bg-slate-800">
                  التالي
                </button>
              </div>

              <div class="mt-4 overflow-auto border border-slate-800 rounded-xl">
                <table class="min-w-full text-sm">
                  <thead class="bg-slate-900">
                    <tr class="text-slate-300">
                      <th class="p-3 text-right">ID</th>
                      <th class="p-3 text-right">النوع</th>
                      <th class="p-3 text-right">المبلغ</th>
                      <th class="p-3 text-right">محفظة مرتبطة</th>
                      <th class="p-3 text-right">الوقت</th>
                    </tr>
                  </thead>
                  <tbody id="txBody" class="bg-slate-950"></tbody>
                </table>
              </div>

              <p id="txMeta" class="text-xs text-slate-400 mt-3">—</p>
            </div>
          </div>
        </details>

      </div>
    </section>

    <!-- Right: Output / Logs -->
    <aside class="space-y-6">
      <div class="rounded-2xl border border-slate-800 bg-slate-900 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
          <h2 class="font-bold">نتائج النظام</h2>
          <button id="clearOutBtn" class="px-3 py-2 rounded-lg bg-slate-950 border border-slate-800 hover:bg-slate-800 text-sm">مسح</button>
        </div>
        <pre id="out" class="p-5 text-xs bg-slate-950 overflow-auto h-[520px]"></pre>
      </div>

      <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
        <h3 class="font-bold mb-2">ملاحظات مهمة للعرض</h3>
        <ul class="text-sm text-slate-300 space-y-2">
          <li>• كل القيم <b>integers</b> (Minor Units) — بدون floats.</li>
          <li>• كل العمليات داخل <b>DB Transaction + Lock</b> لضمان Atomicity.</li>
          <li>• التحويل يسجل <b>Debit + Credit</b> (Double-entry).</li>
          <li>• Idempotency-Key يمنع تكرار الإيداع/السحب/التحويل.</li>
        </ul>
      </div>
    </aside>
  </main>


</body>
</html>
