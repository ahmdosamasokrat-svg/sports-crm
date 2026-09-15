<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
 <meta charset="utf-8">
 <meta
  name="viewport"
  content="width=device-width,initial-scale=1"
 >
 <title>{{ __('crm.quotations') }} | CRM v2</title>
 <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="{{ asset('css/tajawal.css') }}?v=1.0.0">
 <link rel="stylesheet" href="{{ asset('crm-sidebar-shared.css') }}?v={{ time() }}">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
 <link
  rel="stylesheet"
  href="{{ asset('quotation-generator/crm-module.css') }}?v=crm-module-no-sidebar-v6"
 >
 <style>
  .crm-qbtn { min-height:42px; justify-content:center; }
 </style>
<body>
@include('partials.page-loader')
 <!-- CRM QUOTATION SHARED SIDEBAR V1 START -->
 <div class="crm-list-layout">

  @include('partials.crm-sidebar')
  <button class="crm-overlay" id="crmSidebarOverlay" type="button" aria-label="{{ __('crm.close_menu') }}"></button>

  <main class="crm-list-main">
 <!-- CRM QUOTATION SHARED SIDEBAR V1 END -->

  @php
    ob_start();
  @endphp
    @can('quotations.create')
    <a
      class="btn primary crm-qbtn"
      href="{{ route('v2.quotations.create') }}"
    >
      <i class="bi bi-plus-lg" aria-hidden="true" style="margin-inline-end:6px"></i>
      <span>{{ __('crm.create_quotation') }}</span>
    </a>
    @endcan
  @php
    $quoteActions = ob_get_clean();
  @endphp
  @include('partials.topbar', [
    'title' => __('crm.quotations'),
    'subtitle' => __('crm.saved_quotations_subtitle'),
    'icon' => 'bi-receipt-cutoff',
    'actions' => $quoteActions
  ])

   <form
    class="crm-qsearch"
    method="get"
    action="{{ route('v2.quotations.index') }}"
   >
    <input
     type="search"
     name="q"
     value="{{ $term }}"
     placeholder="{{ __('crm.quotation_search_placeholder') }}"
    >

    <button
     class="btn soft crm-qbtn light"
     type="submit"
    >
     {{ __('crm.search') }}
    </button>

    @if ($term !== '')
     <a
      class="btn soft crm-qbtn light"
      href="{{ route('v2.quotations.index') }}"
     >
      {{ __('crm.cancel') }}
     </a>
    @endif
   </form>

   <section class="crm-qcard">

    @if ($quotations->count())

     <table>
      <thead>
       <tr>
        <th>#</th>
        <th>{{ __('crm.quotation_number') }}</th>
        <th>{{ __('crm.client') }}</th>
        <th>{{ __('crm.date') }}</th>
        <th>{{ __('crm.prepared_by') }}</th>
        <th>{{ __('crm.system_title') }}</th>
        <th>{{ __('crm.total') }}</th>
        <th>{{ __('crm.saved_at') }}</th>
        <th>{{ __('crm.action') }}</th>
       </tr>
      </thead>

      <tbody>

       @foreach ($quotations as $quotation)

        <tr>

         <td>
          {{ $quotation->id }}
         </td>

         <td>
          <strong>
           {{ $quotation->quotation_no }}
          </strong>
         </td>

         <td>
          {{ $quotation->client_name }}
         </td>

         <td>
          {{
           $quotation->quote_date
            ? $quotation->quote_date->format('Y-m-d')
            : '—'
          }}
         </td>

         <td>
          {{ $quotation->prepared_by ?: '—' }}
         </td>

         <td>
          {{ $quotation->system_title ?: '—' }}
         </td>

         <td class="crm-qmoney">
          {{
           number_format(
            (float)
            $quotation->grand_total,
            2
           )
          }}
          جنيه
         </td>

         <td>
          {{
           optional(
            $quotation->created_at
           )->format(
            'Y-m-d H:i'
           )
          }}
         </td>

         <td>
          <a
           class="btn small soft crm-qbtn light"
           href="{{
            route(
             'v2.quotations.show',
             $quotation
            )
           }}"
          >
           {{ __('crm.open_print') }}
          </a>

        </tr>

       @endforeach

      </tbody>
     </table>

    @else

     <div class="crm-qempty">
      {{ __('crm.no_saved_quotations') }}
     </div>

    @endif

   </section>

   @if ($quotations->hasPages())

    <div class="crm-qpager">

     <span>
      صفحة
      {{ $quotations->currentPage() }}
      من
      {{ $quotations->lastPage() }}
     </span>

     <div class="crm-qpager-actions">

      @if (!$quotations->onFirstPage())
       <a
        class="crm-qbtn light"
        href="{{ $quotations->previousPageUrl() }}"
       >
        {{ __('crm.previous') }}
       </a>
      @endif

      @if ($quotations->hasMorePages())
       <a
        class="crm-qbtn light"
        href="{{ $quotations->nextPageUrl() }}"
       >
        {{ __('crm.next') }}
       </a>
      @endif

     </div>

    </div>

   @endif

  </main>
 </div>

 <script
  src="{{ asset('quotation-generator/crm-sidebar.js') }}"
 ></script>
</body>
</html>
