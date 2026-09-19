<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SokratCRM — {{ __('crm.kanban_view') }}</title>
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('css/tajawal.css') }}?v=1.0.0">
<link rel="stylesheet" href="{{ asset('crm-sidebar-shared.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('crm-notifications.css') }}?v=1.0.0">
<link rel="stylesheet" href="{{ asset('crm-dropdown.css') }}?v=1.0.1">
<style>
:root{
 --red:#ef4444;
 --red-dark:#dc2626;
 --dark:#182033;
 --muted:#8b94a5;
 --line:#e7e9ef;
 --bg:#f5f6f9;
 --shadow:none;
}
*{box-sizing:border-box}
body{
 margin:0;
 min-width:320px;
 background:var(--bg);
 color:var(--dark);
 font-family:'Plus Jakarta Sans', 'Cairo', sans-serif !important;
}
a{color:inherit}
.brand{
 display:flex;
 align-items:center;
 gap:10px;
 text-decoration:none
}
.brand img{
 width:52px;
 height:52px;
 object-fit:contain
}
.brand strong{
 display:block;
 color:var(--red);
 font:900 21px var(--font-primary)
}
.brand small{
 display:block;
 margin-top:4px;
 color:var(--muted);
 font-size:10px
}
.top-actions{
 margin-inline-start:auto;
 display:flex;
 align-items:center;
 gap:8px
}
.btn{
 min-height:44px;
 display:inline-flex;
 align-items:center;
 justify-content:center;
 gap:7px;
 padding:8px 16px;
 border:1px solid transparent;
 border-radius:11px;
 background:var(--red);
 color:#fff;
 text-decoration:none;
 font-size:13px;
 font-weight:900;
 box-shadow:none !important;
 cursor:pointer
}
.btn.light{
 border-color:var(--line);
 background:#fff;
 color:#596477;
 box-shadow:none
}
.crm-app{
 display:flex;
 min-height:100vh;
 width:100%;
 max-width:100vw;
 overflow-x:clip;
}
.crm-main,main{
 flex:1 1 auto;
 min-width:0;
 max-width:100%;
 padding:24px clamp(16px,2.5vw,36px) 48px;
 box-sizing:border-box;
}
.kanban-toolbar{
 display:flex;
 align-items:center;
 justify-content:space-between;
 gap:14px;
 margin-bottom:18px;
 flex-wrap:wrap;
}
.page-tools{
 display:flex;
 align-items:center;
 gap:12px;
 flex-wrap:wrap;
 width:100%;
 justify-content:space-between;
}
.summary{
 display:flex;
 gap:8px;
 flex-wrap:wrap
}
.summary span{
 padding:9px 12px;
 border:1px solid var(--line);
 border-radius:10px;
 background:#fff;
 color:#687385;
 font-size:11px;
 font-weight:bold
}
.summary b{
 margin-inline-start:5px;
 color:var(--dark);
 font:900 14px var(--font-primary)
}
.board-shell{
 overflow:hidden;
 border:1px solid var(--line);
 border-radius:20px;
 background:#eef0f4;
 box-shadow:var(--shadow)
}
.board-top-scroll{
 width:100%;
 height:20px;
 overflow-x:auto;
 overflow-y:hidden;
 overscroll-behavior-x:contain;
 scrollbar-width:thin;
 scrollbar-color:var(--red, #ef4444) transparent;
}
.board-top-scroll[hidden]{display:none}
.board-top-scroll:focus-visible{
 outline:none;
 box-shadow:inset 0 0 0 2px #dc263766
}
.board-top-scroll-inner{
 height:1px;
 pointer-events:none
}
.board-top-scroll::-webkit-scrollbar{
 height:10px;
}
.board-top-scroll::-webkit-scrollbar-track{
 background:transparent;
 border-radius:999px;
 margin:0 14px;
}
.board-top-scroll::-webkit-scrollbar-thumb{
 background:#ef4444;
 border-radius:999px;
 border:1px solid transparent;
}
.board-top-scroll::-webkit-scrollbar-thumb:hover{
 background:#dc2626;
}
.board{
 display:grid;
 grid-auto-flow:column;
 grid-auto-columns:minmax(270px,290px);
 gap:12px;
 min-height:610px;
 padding:14px;
 overflow-x:auto;
 overscroll-behavior-x:contain;
 scrollbar-width:none
}
.board::-webkit-scrollbar{
 display:none
}
.kanban-column{
 --column-color:#3478f6;
 min-width:0;
 height:max-content;
 min-height:560px;
 padding:12px;
 border:1px solid var(--line);
 border-top:4px solid var(--column-color);
 border-radius:16px;
 background:#f9fafb;
 scroll-snap-align:start
}
.kanban-column.no-answer{--column-color:#e59b16}
.kanban-column.interested{--column-color:#169a64}
.kanban-column.not-interested{--column-color:#ef4444}
.kanban-column.meeting{--column-color:#7b61df}
.kanban-column.quotation{--column-color:#e59b16}
.kanban-column.discussion{--column-color:#5865f2}
.kanban-column.contract{--column-color:#169a64}
.kanban-column.execution{--column-color:#7b61df}
.column-head{
 display:flex;
 align-items:center;
 gap:9px;
 padding:3px 2px 13px;
 border-bottom:1px solid var(--line)
}
.column-icon{
 width:28px;
 height:28px;
 flex:0 0 28px;
 display:flex;
 align-items:center;
 justify-content:center;
 border-radius:0;
 background:transparent !important;
 border:none !important;
 box-shadow:none !important;
 color:var(--column-color);
}
.column-icon svg{
 width:20px;
 height:20px;
 stroke:currentColor;
 fill:none;
 stroke-width:2;
 stroke-linecap:round;
 stroke-linejoin:round;
 display:block;
}
.column-icon i{
 font-size:18px;
 line-height:1;
}
.column-title{
 flex:1;
 margin:0;
 font-size:13px
}
.column-count{
 min-width:auto;
 height:auto;
 display:inline-flex;
 align-items:center;
 justify-content:center;
 border-radius:0;
 background:transparent !important;
 border:none !important;
 box-shadow:none !important;
 color:var(--column-color) !important;
 font:900 15px var(--font-mono, monospace);
 line-height:1;
 padding:0 4px;
}
.column-body{
 display:grid;
 place-items:center;
 min-height:470px;
 padding:18px
}
.kanban-empty{
 text-align:center;
 color:#929aa7
}
.kanban-empty i{
 width:52px;
 height:52px;
 display:grid;
 place-items:center;
 margin:0 auto 11px;
 border:1px dashed #ced3dc;
 border-radius:15px;
 background:#fff;
 color:var(--column-color);
 font-style:normal;
 font-size:21px
}
.kanban-empty strong{
 display:block;
 color:#687385;
 font-size:12px
}
.kanban-empty p{
 max-width:190px;
 margin:7px auto 0;
 font-size:10px;
 line-height:1.7
}
.notice{
 margin-top:14px;
 padding:12px 14px;
 border:1px solid #efd59f;
 border-radius:12px;
 background:#fff9ed;
 color:#93640e;
 font-size:11px;
 font-weight:bold
}
@media(max-width:768px){
 .topbar{align-items:stretch;flex-direction:column;gap:12px;padding:12px 16px}
 .top-actions{width:100%;margin:0;flex-wrap:wrap;display:flex;gap:8px}
 .top-actions .btn{flex:1 1 auto;min-height:44px}
 main{padding:16px 10px 36px;max-width:100vw;overflow-x:hidden}
 .page-head{align-items:flex-start;flex-direction:column;gap:12px}
 .board-shell{border-radius:14px;max-width:100%;overflow:hidden}
 .board{grid-auto-columns:minmax(280px,84vw);padding:10px;gap:10px;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch}
 .kanban-column{scroll-snap-align:start;scroll-snap-stop:always;min-height:500px}
}


/* CRM LIVE KANBAN START */

.column-body{
 display:flex;
 flex-direction:column;
 align-items:stretch;
 justify-content:flex-start;
 gap:10px;
 min-height:470px;
 padding:12px
}

.kanban-card{
 --card-stage-color:#3478f6;
 display:flex !important;
 flex-direction:column !important;
 gap:9px !important;
 padding:12px 14px !important;
 border:1px solid color-mix(in srgb, var(--card-stage-color) 25%, var(--line)) !important;
 border-top:3.5px solid var(--card-stage-color) !important;
 border-radius:14px !important;
 background:var(--card, #ffffff) !important;
 box-shadow:0 4px 14px rgba(15, 23, 42, 0.05) !important;
 box-sizing:border-box !important;
 width:100% !important;
 max-width:100% !important;
 transition:transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease !important;
}

.kanban-card:hover{
 transform:translateY(-2px) !important;
 box-shadow:0 8px 22px rgba(15, 23, 42, 0.09) !important;
 border-color:var(--card-stage-color) !important;
}

.kc-header{
 display:flex !important;
 align-items:center !important;
 gap:10px !important;
 min-width:0 !important;
 width:100% !important;
}

.kc-avatar{
 width:34px !important;
 height:34px !important;
 flex:0 0 34px !important;
 border-radius:10px !important;
 background:color-mix(in srgb, var(--card-stage-color) 12%, var(--card, #ffffff)) !important;
 color:var(--card-stage-color) !important;
 border:1px solid color-mix(in srgb, var(--card-stage-color) 30%, transparent) !important;
 display:flex !important;
 align-items:center !important;
 justify-content:center !important;
 font-weight:900 !important;
 font-size:14px !important;
 text-transform:uppercase !important;
 line-height:1 !important;
}

.kc-title-wrap{
 flex:1 1 auto !important;
 min-width:0 !important;
 display:flex !important;
 flex-direction:column !important;
 gap:2px !important;
 overflow:hidden !important;
}

.kc-name{
 color:var(--dark, #0f172a) !important;
 font-size:13.5px !important;
 font-weight:800 !important;
 text-decoration:none !important;
 overflow:hidden !important;
 text-overflow:ellipsis !important;
 white-space:nowrap !important;
 line-height:1.3 !important;
 display:block !important;
}

.kc-name:hover{
 color:var(--card-stage-color) !important;
}

.kc-source{
 display:inline-flex !important;
 align-items:center !important;
 gap:4px !important;
 font-size:11px !important;
 color:var(--muted, #64748b) !important;
 font-weight:600 !important;
 max-width:100% !important;
 overflow:hidden !important;
 text-overflow:ellipsis !important;
 white-space:nowrap !important;
}

.kc-source svg{
 flex-shrink:0 !important;
 opacity:0.75 !important;
 display:inline-block !important;
 vertical-align:middle !important;
}

.kc-source span{
 overflow:hidden !important;
 text-overflow:ellipsis !important;
 white-space:nowrap !important;
}

.kc-meta-grid{
 display:flex !important;
 flex-direction:column !important;
 gap:6px !important;
 background:var(--bg, #f8fafc) !important;
 border:1px solid var(--line, #e2e8f0) !important;
 border-radius:10px !important;
 padding:8px 10px !important;
 box-sizing:border-box !important;
 width:100% !important;
}

.kc-meta-item{
 display:flex !important;
 align-items:center !important;
 gap:8px !important;
 font-size:11.5px !important;
 line-height:1.2 !important;
 min-width:0 !important;
 width:100% !important;
}

.kc-meta-icon{
 display:inline-flex !important;
 align-items:center !important;
 justify-content:center !important;
 flex-shrink:0 !important;
 width:14px !important;
 height:14px !important;
}

.kc-meta-val{
 overflow:hidden !important;
 text-overflow:ellipsis !important;
 white-space:nowrap !important;
 font-weight:700 !important;
 color:var(--dark, #1e293b) !important;
 font-size:11.5px !important;
}

.kanban-phone.kc-meta-val{
 direction:ltr !important;
 color:#0284c7 !important;
 text-decoration:none !important;
 font-family:var(--font-mono, monospace) !important;
 font-size:11.5px !important;
}

.kanban-phone.kc-meta-val:hover{
 text-decoration:underline !important;
}

.kc-followup-badge{
 display:inline-flex !important;
 align-items:center !important;
 gap:6px !important;
 padding:4px 8px !important;
 border-radius:7px !important;
 font-size:10.5px !important;
 font-weight:700 !important;
 width:fit-content !important;
 max-width:100% !important;
 margin-top:2px !important;
}

.kc-followup-badge svg{
 flex-shrink:0 !important;
}

.kc-followup-badge .kc-badge-tag{
 font-size:9.5px !important;
 padding:1px 5px !important;
 border-radius:4px !important;
 font-weight:800 !important;
}

.kc-followup-overdue{
 background:rgba(239, 68, 68, 0.1) !important;
 color:#dc2626 !important;
 border:1px solid rgba(239, 68, 68, 0.25) !important;
}

.kc-followup-overdue .kc-badge-tag{
 background:#dc2626 !important;
 color:#ffffff !important;
}

.kc-followup-today{
 background:rgba(245, 158, 11, 0.1) !important;
 color:#d97706 !important;
 border:1px solid rgba(245, 158, 11, 0.25) !important;
}

.kc-followup-today .kc-badge-tag{
 background:#d97706 !important;
 color:#ffffff !important;
}

.kc-followup-upcoming{
 background:rgba(16, 185, 129, 0.08) !important;
 color:#059669 !important;
 border:1px solid rgba(16, 185, 129, 0.2) !important;
}

.kc-actions{
 display:flex !important;
 align-items:center !important;
 gap:8px !important;
 margin-top:6px !important;
 width:100% !important;
 box-sizing:border-box !important;
}

.kc-actions .btn{
 flex:1 1 50% !important;
 min-width:0 !important;
 min-height:34px !important;
 height:34px !important;
 padding:0 12px !important;
 font-size:11.5px !important;
 font-weight:800 !important;
 border-radius:9px !important;
 display:inline-flex !important;
 align-items:center !important;
 justify-content:center !important;
 gap:6px !important;
 text-decoration:none !important;
 transition:all 0.15s ease !important;
 white-space:nowrap !important;
 box-sizing:border-box !important;
 overflow:hidden !important;
}

.kc-actions .btn span{
 overflow:hidden !important;
 text-overflow:ellipsis !important;
 white-space:nowrap !important;
 font-size:11.5px !important;
 line-height:1 !important;
}

.kc-actions .btn svg{
 flex-shrink:0 !important;
 width:13.5px !important;
 height:13.5px !important;
}

.kc-action-call{
 background:var(--card-stage-color) !important;
 border-color:var(--card-stage-color) !important;
 color:#ffffff !important;
 flex:0 0 38% !important;
 max-width:38% !important;
}

.kc-action-call:hover{
 opacity:0.92 !important;
 transform:translateY(-1px) !important;
 box-shadow:0 3px 10px rgba(0,0,0,0.12) !important;
}

.kc-action-followup{
 background:var(--bg, #f8fafc) !important;
 border:1px solid var(--line, #e2e8f0) !important;
 color:var(--dark, #334155) !important;
 flex:1 1 62% !important;
}

.kc-action-followup:hover{
 border-color:var(--card-stage-color) !important;
 color:var(--card-stage-color) !important;
 transform:translateY(-1px) !important;
}

.kanban-empty{
 margin:auto
}

@media(max-width:768px){
 .kc-actions{
  flex-direction:row !important;
  flex-wrap:wrap !important;
  gap:6px !important;
 }
 .kc-actions .btn{
  min-height:36px !important;
  font-size:11px !important;
 }
 .kc-action-call{
  flex:1 1 100% !important;
 }
 .kanban-scope-btn{
  min-height:44px
 }
}



/* CRM KANBAN FOLLOWUP SCOPES START */

.kanban-followup-toolbar{
 padding:10px 11px;
 border-bottom:1px solid var(--line);
 background:#fff
}

.kanban-scope-buttons{
 display:grid;
 grid-template-columns:repeat(4, minmax(0, 1fr));
 gap:4px;
 width:100%;
 box-sizing:border-box;
}

.kanban-scope-btn{
 min-width:0;
 min-height:36px;
 display:flex;
 flex-direction:column;
 align-items:center;
 justify-content:center;
 gap:1px;
 padding:4px 2px;
 border:1px solid #e5e8ef;
 border-radius:8px;
 background:#f7f8fa;
 color:#697489;
 font-family:inherit;
 cursor:pointer;
 transition:all 0.12s ease;
 box-sizing:border-box;
 overflow:hidden;
}

.kanban-scope-btn span{
 display:block;
 max-width:100%;
 overflow:hidden;
 text-overflow:ellipsis;
 white-space:nowrap;
 font-size:8.5px;
 font-weight:800;
 line-height:1;
}

.kanban-scope-btn span i{
 font-size:9px;
 vertical-align:middle;
}

.kanban-scope-btn b{
 font:900 13px var(--font-primary);
 color:inherit;
 line-height:1.1;
 margin-top:1px;
}

.kanban-scope-btn.active{
 border-color:var(--column-color);
 background:
  color-mix(
   in srgb,
   var(--column-color) 11%,
   white
  );
 color:var(--column-color);
 box-shadow:none
}

.kanban-followup-current{
 display:flex;
 align-items:center;
 justify-content:space-between;
 gap:7px;
 margin-top:7px;
 color:#7d8798;
 font-size:9px;
 font-weight:bold
}

.kanban-followup-current strong{
 color:#445166;
 font-size:9px
}


.kanban-scope-panel[hidden]{
 display:none!important
}

.kanban-scope-panel{
 display:flex;
 flex-direction:column;
 gap:10px;
 width:100%
}

.kanban-scope-empty{
 min-height:220px;
 display:flex;
 flex-direction:column;
 align-items:center;
 justify-content:center;
 gap:7px;
 padding:18px 10px;
 color:#929bab;
 text-align:center
}

.kanban-scope-empty i{
 width:42px;
 height:42px;
 display:grid;
 place-items:center;
 border-radius:12px;
 background:#f3f5f8;
 font-style:normal;
 font-size:18px
}

.kanban-scope-empty strong{
 color:#657086;
 font-size:11px
}

.kanban-scope-empty p{
 margin:0;
 font-size:9px;
 line-height:1.7
}

@media(max-width:760px){
 .kanban-scope-btn span{
  font-size:8px
 }
}





/* CRM KANBAN FOLLOWUP PRIORITY COLORS START */

/*
 * Customer follow-up priority:
 * overdue  = red
 * today    = orange
 * upcoming = green
 */

.kanban-card[
 data-kanban-lead-scope="overdue"
]{
 --card-stage-color:
  #ef4444!important
}

.kanban-card[
 data-kanban-lead-scope="today"
]{
 --card-stage-color:
  #e59b16!important
}

.kanban-card[
 data-kanban-lead-scope="upcoming"
]{
 --card-stage-color:
  #169a64!important
}

.kanban-scope-btn[
 data-kanban-scope="overdue"
]{
 --followup-scope-color:#ef4444;
 border-color:
  color-mix(
   in srgb,
   #ef4444 28%,
   #e5e8ef
  );
 background:
  color-mix(
   in srgb,
   #ef4444 5%,
   white
  );
 color:#ef4444
}

.kanban-scope-btn[
 data-kanban-scope="today"
]{
 --followup-scope-color:#e59b16;
 border-color:
  color-mix(
   in srgb,
   #e59b16 28%,
   #e5e8ef
  );
 background:
  color-mix(
   in srgb,
   #e59b16 5%,
   white
  );
 color:#b87800
}

.kanban-scope-btn[
 data-kanban-scope="upcoming"
]{
 --followup-scope-color:#169a64;
 border-color:
  color-mix(
   in srgb,
   #169a64 28%,
   #e5e8ef
  );
 background:
  color-mix(
   in srgb,
   #169a64 5%,
   white
  );
 color:#11784e
}

.kanban-scope-btn[
 data-kanban-scope
].active{
 border-color:
  var(
   --followup-scope-color,
   var(--column-color)
  )!important;

 background:
  color-mix(
   in srgb,
   var(
    --followup-scope-color,
    var(--column-color)
   ) 13%,
   white
  )!important;

 color:
  var(
   --followup-scope-color,
   var(--column-color)
  )!important
}

/* CRM KANBAN FOLLOWUP PRIORITY COLORS END */



.kanban-followup-current{
 gap:8px!important;
 flex-wrap:wrap
}


/* CRM KANBAN DRAG DROP START */

.kanban-card[draggable="true"]{
 cursor:grab;
 user-select:none
}

.kanban-card[draggable="true"]:active{
 cursor:grabbing
}

.kanban-card.is-dragging{
 opacity:.42!important
}

.kanban-column.is-drop-target{
 outline:3px dashed
  var(--column-color)!important;
 outline-offset:-4px;
 background:
  color-mix(
   in srgb,
   var(--column-color) 5%,
   white
  )
}

.kanban-column.is-drop-target
 .column-head{
 background:
  color-mix(
   in srgb,
   var(--column-color) 15%,
   white
  )!important
}

.kanban-drag-hint{
 padding:9px 11px;
 border-bottom:1px dashed #e0e4eb;
 background:#fbfcfd;
 color:#7a8495;
 font-size:9px;
 font-weight:800;
 line-height:1.6;
 text-align:center
}

.kanban-drag-hint strong{
 color:#48566b
}

.kanban-followup-modal{
 position:fixed;
 inset:0;
 z-index:10000;
 display:none;
 align-items:center;
 justify-content:center;
 padding:20px;
 background:#111827a8;
 backdrop-filter:blur(4px)
}

.kanban-followup-modal.open{
 display:flex
}

.kanban-followup-dialog{
 width:min(1000px,96vw);
 height:min(860px,92vh);
 display:flex;
 flex-direction:column;
 overflow:hidden;
 border:1px solid #dfe3ea;
 border-radius:18px;
 background:#fff;
 box-shadow:0 30px 90px #11182755
}

.kanban-followup-modal-head{
 min-height:68px;
 display:flex;
 align-items:center;
 gap:12px;
 padding:12px 16px;
 border-bottom:1px solid #e8ebf0;
 background:#fff
}

.kanban-followup-modal-title{
 min-width:0;
 flex:1
}

.kanban-followup-modal-title h3{
 margin:0;
 color:#28354a;
 font-size:17px
}

.kanban-followup-modal-title p{
 margin:5px 0 0;
 color:#858f9f;
 font-size:10px;
 line-height:1.6
}

.kanban-followup-close{
 width:36px;
 height:36px;
 min-width:36px;
 min-height:36px;
 display:inline-flex;
 align-items:center;
 justify-content:center;
 border:1px solid #e1e5eb;
 border-radius:10px;
 background:#f8f9fb;
 color:#606b7e;
 padding:0;
 cursor:pointer;
 transition:all .18s ease;
 box-shadow:none !important
}

.kanban-followup-close:hover{
 color:var(--red, #ef4444);
 border-color:rgba(239, 68, 68, 0.35);
 background:rgba(239, 68, 68, 0.08);
 transform:scale(1.05)
}

.kanban-followup-frame{
 width:100%;
 min-height:0;
 flex:1;
 border:0;
 background:#f5f6f8
}

body.kanban-modal-open{
 overflow:hidden
}

.kanban-drag-toast{
 position:fixed;
 left:50%;
 bottom:25px;
 z-index:10020;
 max-width:min(520px,90vw);
 padding:11px 15px;
 border-radius:11px;
 background:#263247;
 color:#fff;
 font-size:11px;
 font-weight:900;
 box-shadow:0 14px 35px #11182735;
 transform:translateX(-50%);
 display:none
}

.kanban-drag-toast.show{
 display:block
}

@media(max-width:768px){
 .kanban-followup-modal{
  padding:10px;
  align-items:center
 }
 .kanban-followup-dialog,
 .kanban-utility-dialog{
  width:100%!important;
  max-width:100%!important;
  max-height:calc(100vh - 40px)!important;
  height:calc(100vh - 40px)!important;
  border-radius:14px!important;
  overflow-y:auto!important
 }
 .kanban-followup-modal-head{
  min-height:54px;
  padding:10px 14px
 }
 .kanban-followup-close{
  width:44px;
  height:44px;
  min-width:44px;
  min-height:44px
 }
}


/* CRM KANBAN DRAG DROP END */

/* CRM KANBAN FOLLOWUP SCOPES END */

/* CRM LIVE KANBAN END */


/* CRM KANBAN UTILITY POPUPS START */


.kanban-utility-dialog{
 width:min(1180px,96vw)!important;
 height:min(900px,92vh)!important
}

/* CRM KANBAN UTILITY POPUPS END */


/* CRM KANBAN TOOLBAR & REDESIGNED FILTERS START */
.kanban-filters-form{
 display:inline-flex;
 align-items:center;
 gap:10px;
 flex-wrap:wrap
}

.kanban-filter-control{
 display:inline-flex;
 align-items:center;
 position:relative;
 height:42px;
 padding:0;
 border:1px solid #e2e8f0;
 border-radius:11px;
 background:#ffffff;
 box-shadow:0 1px 3px rgba(0,0,0,0.03);
 cursor:pointer;
 user-select:none;
 transition:border-color 0.15s,box-shadow 0.15s,background-color 0.15s
}

.kanban-filter-control:hover{
 border-color:#cbd5e1
}

.kanban-filter-control:focus-within{
 border-color:var(--red,#ef4444);
 box-shadow:none !important
}

.kanban-filter-control .filter-icon{
 display:grid;
 place-items:center;
 margin-inline-start:12px;
 margin-inline-end:6px;
 color:#64748b;
 font-size:15px;
 pointer-events:none;
 flex-shrink:0
}

.kanban-filter-control .filter-label{
 font-size:12px;
 font-weight:700;
 color:#64748b;
 white-space:nowrap;
 margin-inline-end:6px;
 user-select:none;
 cursor:pointer;
 pointer-events:none
}

.kanban-filter-control select{
 appearance:none;
 -webkit-appearance:none;
 border:none;
 background:transparent;
 outline:none;
 height:100%;
 padding-inline-start:4px;
 padding-inline-end:32px;
 font-family:inherit;
 font-size:13px;
 font-weight:700;
 color:var(--dark,#182033);
 cursor:pointer;
 flex:1 1 auto
}

.kanban-filter-control select option{
 background:#ffffff;
 color:#182033
}

.kanban-filter-control .filter-chevron{
 position:absolute;
 inset-inline-end:11px;
 display:grid;
 place-items:center;
 color:#94a3b8;
 font-size:11px;
 pointer-events:none
}


.kanban-cards-list{
 display:flex;
 flex-direction:column;
 gap:10px;
 min-height:50px;
 width:100%;
 transition:opacity 0.15s ease
}

.kanban-cards-list.is-loading{
 opacity:0.45;
 pointer-events:none
}

.kanban-column-pagination{
 display:flex;
 align-items:center;
 justify-content:space-between;
 gap:8px;
 margin-top:auto;
 padding:8px 10px;
 border:1px solid #e5e8ef;
 border-radius:11px;
 background:#ffffff;
 box-shadow:0 2px 6px rgba(0,0,0,0.03)
}

.kanban-page-info{
 display:inline-flex;
 align-items:center;
 gap:4px;
 color:#64748b;
 font-size:11px;
 font-weight:700;
 font-variant-numeric:tabular-nums;
 white-space:nowrap;
 user-select:none
}

.kanban-page-range{
 color:#1e293b;
 font-weight:800
}

.kanban-page-sep{
 color:#94a3b8;
 font-size:10px
}

.kanban-page-total{
 color:#475569;
 font-weight:800
}

.kanban-page-actions{
 display:inline-flex;
 align-items:center;
 gap:5px
}

.kanban-page-btn{
 display:inline-flex;
 align-items:center;
 justify-content:center;
 width:34px;
 height:34px;
 padding:0;
 border:1px solid #e2e8f0;
 border-radius:8px;
 background:#f8fafc;
 color:#334155;
 font-size:13px;
 font-weight:700;
 cursor:pointer;
 transition:background-color 0.15s,border-color 0.15s,color 0.15s,opacity 0.15s;
 -webkit-tap-highlight-color:transparent
}

.kanban-page-btn:hover:not(:disabled){
 background:#ffffff;
 border-color:var(--column-color,#3478f6);
 color:var(--column-color,#3478f6);
 box-shadow:0 2px 6px rgba(0,0,0,0.06)
}

.kanban-page-btn:active:not(:disabled){
 transform:scale(0.96)
}

.kanban-page-btn:disabled{
 opacity:0.35;
 cursor:not-allowed;
 background:#f1f5f9;
 border-color:#e2e8f0;
 color:#94a3b8;
 pointer-events:none
}

/* Dark Mode Overrides */
html.dark-mode .summary span,
html.dark .summary span,
[data-theme="dark"] .summary span{
 background:#1e293b;
 border-color:rgba(255,255,255,0.12);
 color:#94a3b8
}

html.dark-mode .summary b,
html.dark .summary b,
[data-theme="dark"] .summary b{
 color:#f1f5f9
}

html.dark-mode .kanban-filter-control,
html.dark .kanban-filter-control,
[data-theme="dark"] .kanban-filter-control{
 background:#1e293b;
 border-color:rgba(255,255,255,0.12);
 box-shadow:0 1px 3px rgba(0,0,0,0.2)
}

html.dark-mode .kanban-filter-control:hover,
html.dark .kanban-filter-control:hover,
[data-theme="dark"] .kanban-filter-control:hover{
 border-color:rgba(255,255,255,0.25)
}

html.dark-mode .kanban-filter-control select,
html.dark .kanban-filter-control select,
[data-theme="dark"] .kanban-filter-control select{
 color:#f1f5f9
}

html.dark-mode .kanban-filter-control select option,
html.dark .kanban-filter-control select option,
[data-theme="dark"] .kanban-filter-control select option{
 background:#18181b;
 color:#f1f5f9
}

html.dark-mode .kanban-filter-control .filter-icon,
html.dark-mode .kanban-filter-control .filter-label,
html.dark-mode .kanban-filter-control .filter-chevron,
html.dark .kanban-filter-control .filter-icon,
html.dark .kanban-filter-control .filter-label,
html.dark .kanban-filter-control .filter-chevron,
[data-theme="dark"] .kanban-filter-control .filter-icon,
[data-theme="dark"] .kanban-filter-control .filter-label,
[data-theme="dark"] .kanban-filter-control .filter-chevron{
 color:#94a3b8
}

html.dark-mode .kanban-column-pagination,
html.dark .kanban-column-pagination,
[data-theme="dark"] .kanban-column-pagination{
 background:#1e293b;
 border-color:rgba(255,255,255,0.12)
}

html.dark-mode .kanban-page-range,
html.dark-mode .kanban-page-total,
html.dark .kanban-page-range,
html.dark .kanban-page-total,
[data-theme="dark"] .kanban-page-range,
[data-theme="dark"] .kanban-page-total{
 color:#f1f5f9
}

html.dark-mode .kanban-page-btn,
html.dark .kanban-page-btn,
[data-theme="dark"] .kanban-page-btn{
 background:rgba(255,255,255,0.05);
 border-color:rgba(255,255,255,0.12);
 color:#f1f5f9
}

html.dark-mode .kanban-page-btn:disabled,
html.dark .kanban-page-btn:disabled,
[data-theme="dark"] .kanban-page-btn:disabled{
 background:rgba(255,255,255,0.02);
 border-color:rgba(255,255,255,0.06);
 color:#64748b
}
html.dark-mode .board-top-scroll,
html.dark .board-top-scroll,
[data-theme="dark"] .board-top-scroll{
 scrollbar-color:#ef4444 transparent
}
html.dark-mode .board-top-scroll::-webkit-scrollbar-track,
html.dark .board-top-scroll::-webkit-scrollbar-track,
[data-theme="dark"] .board-top-scroll::-webkit-scrollbar-track{
 background:transparent
}
html.dark-mode .board-top-scroll::-webkit-scrollbar-thumb,
html.dark .board-top-scroll::-webkit-scrollbar-thumb,
[data-theme="dark"] .board-top-scroll::-webkit-scrollbar-thumb{
 background:#ef4444
}
html.dark-mode .board-top-scroll::-webkit-scrollbar-thumb:hover,
html.dark .board-top-scroll::-webkit-scrollbar-thumb:hover,
[data-theme="dark"] .board-top-scroll::-webkit-scrollbar-thumb:hover{
 background:#dc2626
}

/* Dark mode surface and content overrides */
html.dark-mode .kanban-page,
html.dark .kanban-page,
[data-theme="dark"] .kanban-page{
 --dark:#f1f5f9;
 --muted:#94a3b8;
 --line:#334155;
 --bg:#0f172a;
 --card:#1e293b;
 --shadow:0 12px 35px rgba(0,0,0,.3)
}

html.dark-mode body,
html.dark body,
[data-theme="dark"] body{
 background:#0f172a;
 color:#f1f5f9
}

html.dark-mode .btn.light,
html.dark .btn.light,
[data-theme="dark"] .btn.light{
 border-color:rgba(255,255,255,.12);
 background:#1e293b;
 color:#cbd5e1
}

html.dark-mode .board-shell,
html.dark .board-shell,
[data-theme="dark"] .board-shell{
 border-color:#334155;
 background:#0b1220
}

html.dark-mode .board,
html.dark .board,
[data-theme="dark"] .board{
 background:#0b1220
}

html.dark-mode .board-top-scroll,
html.dark .board-top-scroll,
[data-theme="dark"] .board-top-scroll{
 scrollbar-color:#475569 transparent
}

html.dark-mode .board-top-scroll::-webkit-scrollbar-track,
html.dark .board-top-scroll::-webkit-scrollbar-track,
[data-theme="dark"] .board-top-scroll::-webkit-scrollbar-track{
 background:rgba(255,255,255,.03)
}

html.dark-mode .board-top-scroll::-webkit-scrollbar-thumb,
html.dark .board-top-scroll::-webkit-scrollbar-thumb,
[data-theme="dark"] .board-top-scroll::-webkit-scrollbar-thumb{
 background:#475569
}

html.dark-mode .board-top-scroll::-webkit-scrollbar-thumb:hover,
html.dark .board-top-scroll::-webkit-scrollbar-thumb:hover,
[data-theme="dark"] .board-top-scroll::-webkit-scrollbar-thumb:hover{
 background:#64748b
}

html.dark-mode .kanban-column,
html.dark .kanban-column,
[data-theme="dark"] .kanban-column{
 border-color:#334155;
 background:#111827
}

html.dark-mode .column-head,
html.dark .column-head,
[data-theme="dark"] .column-head{
 border-color:#334155
}

html.dark-mode .column-icon,
html.dark .column-icon,
[data-theme="dark"] .column-icon{
 background:color-mix(in srgb,var(--column-color) 14%,#1e293b)
}

html.dark-mode .kanban-card,
html.dark .kanban-card,
[data-theme="dark"] .kanban-card{
 background: #1e293b;
 border-color: rgba(255, 255, 255, 0.08);
 border-top-color: var(--card-stage-color);
 box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
}

html.dark-mode .kanban-card:hover,
html.dark .kanban-card:hover,
[data-theme="dark"] .kanban-card:hover{
 border-color: var(--card-stage-color);
 box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
}

html.dark-mode .kc-avatar,
html.dark .kc-avatar,
[data-theme="dark"] .kc-avatar{
 background: color-mix(in srgb, var(--card-stage-color) 20%, #1e293b);
}

html.dark-mode .kc-name,
html.dark .kc-name,
[data-theme="dark"] .kc-name{
 color: #f8fafc;
}

html.dark-mode .kc-meta-grid,
html.dark .kc-meta-grid,
[data-theme="dark"] .kc-meta-grid{
 background: #0f172a;
 border-color: rgba(255, 255, 255, 0.06);
}

html.dark-mode .kc-meta-val,
html.dark .kc-meta-val,
[data-theme="dark"] .kc-meta-val{
 color: #e2e8f0;
}

html.dark-mode .kc-action-view,
html.dark-mode .kc-action-followup,
html.dark .kc-action-view,
html.dark .kc-action-followup,
[data-theme="dark"] .kc-action-view,
[data-theme="dark"] .kc-action-followup{
 background: #0f172a !important;
 border-color: rgba(255, 255, 255, 0.1) !important;
 color: #cbd5e1 !important;
}

html.dark-mode .kanban-phone,
html.dark .kanban-phone,
[data-theme="dark"] .kanban-phone{
 color:#8ab4f8
}

html.dark-mode .kanban-followup-toolbar,
html.dark .kanban-followup-toolbar,
[data-theme="dark"] .kanban-followup-toolbar{
 border-color:#334155;
 background:#111827
}

html.dark-mode .kanban-scope-btn,
html.dark .kanban-scope-btn,
[data-theme="dark"] .kanban-scope-btn{
 border-color:#334155;
 background:#172033;
 color:#aeb8c8
}

html.dark-mode .kanban-scope-btn.active,
html.dark .kanban-scope-btn.active,
[data-theme="dark"] .kanban-scope-btn.active{
 background:color-mix(in srgb,var(--followup-scope-color,var(--column-color)) 18%,#1e293b)!important
}

html.dark-mode .kanban-followup-current,
html.dark .kanban-followup-current,
[data-theme="dark"] .kanban-followup-current{
 color:#94a3b8
}

html.dark-mode .kanban-followup-current strong,
html.dark .kanban-followup-current strong,
[data-theme="dark"] .kanban-followup-current strong{
 color:#cbd5e1
}

html.dark-mode .kanban-scope-empty i,
html.dark .kanban-scope-empty i,
[data-theme="dark"] .kanban-scope-empty i{
 background:#172033
}

html.dark-mode .kanban-scope-empty strong,
html.dark .kanban-scope-empty strong,
[data-theme="dark"] .kanban-scope-empty strong{
 color:#cbd5e1
}

html.dark-mode .kanban-drag-hint,
html.dark .kanban-drag-hint,
[data-theme="dark"] .kanban-drag-hint{
 border-color:#334155;
 background:#172033;
 color:#aeb8c8
}

html.dark-mode .kanban-drag-hint strong,
html.dark .kanban-drag-hint strong,
[data-theme="dark"] .kanban-drag-hint strong{
 color:#e2e8f0
}

html.dark-mode .kanban-column.is-drop-target,
html.dark .kanban-column.is-drop-target,
[data-theme="dark"] .kanban-column.is-drop-target{
 background:color-mix(in srgb,var(--column-color) 8%,#111827)
}

html.dark-mode .kanban-column.is-drop-target .column-head,
html.dark .kanban-column.is-drop-target .column-head,
[data-theme="dark"] .kanban-column.is-drop-target .column-head{
 background:color-mix(in srgb,var(--column-color) 18%,#111827)!important
}

html.dark-mode .kanban-filter-control,
html.dark .kanban-filter-control,
[data-theme="dark"] .kanban-filter-control{
 border-color:#334155;
 background:#1e293b;
 box-shadow:0 1px 3px rgba(0,0,0,.2)
}

html.dark-mode .kanban-filter-control .filter-icon,
html.dark-mode .kanban-filter-control .filter-label,
html.dark .kanban-filter-control .filter-icon,
html.dark .kanban-filter-control .filter-label,
[data-theme="dark"] .kanban-filter-control .filter-icon,
[data-theme="dark"] .kanban-filter-control .filter-label{
 color:#94a3b8
}

html.dark-mode .kanban-filter-control select,
html.dark .kanban-filter-control select,
[data-theme="dark"] .kanban-filter-control select{
 color:#f1f5f9
}

html.dark-mode .kanban-filter-control select option,
html.dark .kanban-filter-control select option,
[data-theme="dark"] .kanban-filter-control select option{
 background:#1e293b;
 color:#f1f5f9
}

html.dark-mode .kanban-column-pagination,
html.dark .kanban-column-pagination,
[data-theme="dark"] .kanban-column-pagination{
 border-color:#334155;
 background:#172033;
 box-shadow:0 2px 6px rgba(0,0,0,.2)
}

html.dark-mode .kanban-page-info,
html.dark .kanban-page-info,
[data-theme="dark"] .kanban-page-info{
 color:#94a3b8
}

html.dark-mode .kanban-page-range,
html.dark .kanban-page-range,
[data-theme="dark"] .kanban-page-range{
 color:#f1f5f9
}

html.dark-mode .kanban-page-btn,
html.dark .kanban-page-btn,
[data-theme="dark"] .kanban-page-btn{
 border-color:#334155;
 background:#1e293b;
 color:#e2e8f0
}

html.dark-mode .kanban-page-btn:disabled,
html.dark .kanban-page-btn:disabled,
[data-theme="dark"] .kanban-page-btn:disabled{
 border-color:rgba(255,255,255,.08);
 background:#172033;
 color:#64748b
}

html.dark-mode .kanban-empty i,
html.dark .kanban-empty i,
[data-theme="dark"] .kanban-empty i{
 border-color:#475569;
 background:#172033
}

html.dark-mode .kanban-empty strong,
html.dark .kanban-empty strong,
[data-theme="dark"] .kanban-empty strong{
 color:#cbd5e1
}

html.dark-mode .notice,
html.dark .notice,
[data-theme="dark"] .notice{
 border-color:#6b531e;
 background:#2b2414;
 color:#f5c96a
}

html.dark-mode .kanban-followup-dialog,
html.dark .kanban-followup-dialog,
[data-theme="dark"] .kanban-followup-dialog{
 border-color:#334155;
 background:#1e293b
}

html.dark-mode .kanban-followup-modal-head,
html.dark .kanban-followup-modal-head,
[data-theme="dark"] .kanban-followup-modal-head{
 border-color:#334155;
 background:#172033
}

html.dark-mode .kanban-followup-modal-title h3,
html.dark .kanban-followup-modal-title h3,
[data-theme="dark"] .kanban-followup-modal-title h3{
 color:#f1f5f9
}

html.dark-mode .kanban-followup-close,
html.dark .kanban-followup-close,
[data-theme="dark"] .kanban-followup-close{
 border-color:#334155;
 background:#1e293b;
 color:#cbd5e1
}

html.dark-mode .kanban-followup-frame,
html.dark .kanban-followup-frame,
[data-theme="dark"] .kanban-followup-frame{
 background:#0f172a
}

@media(max-width:768px){
 .page-tools{
  flex-direction:column;
  align-items:stretch;
  gap:12px
 }
 .kanban-filters-form{
  width:100%;
  gap:8px
 }
 .kanban-filter-control{
  flex:1 1 auto;
  min-width:140px
 }
 .kanban-column-pagination{
  padding:8px 10px
 }
 .kanban-page-btn{
  width:40px;
  height:40px;
  font-size:15px
 }
}
/* CRM KANBAN TOOLBAR & REDESIGNED FILTERS END */

</style>
</head>
<body>
@include('partials.page-loader')

<div class="crm-app kanban-page">
 @include('partials.crm-sidebar')

 <main class="crm-main">
  @php
      $kanbanTopActions = '';
      if (auth()->user()->can('leads.create')) {
          $kanbanTopActions .= '<a class="btn primary" href="' . route('v2.leads.create') . '" data-kanban-create-popup draggable="false"><i class="bi bi-plus-lg"></i> ' . __('crm.add_lead_short') . '</a>';
      }
  @endphp

  @include('partials.topbar', [
      'title' => __('crm.kanban_view'),
      'subtitle' => __('crm.kanban_subtitle'),
      'icon' => 'bi-kanban-fill',
      'backUrl' => route('dashboard'),
      'backTitle' => __('crm.dashboard'),
      'actions' => $kanbanTopActions,
  ])

  <section class="kanban-toolbar">
   <div class="page-tools">
     <form class="kanban-filters-form" id="kanbanFiltersForm" method="get" action="{{ route('v2.leads.kanban') }}">
      @if ($canFilterByEmployee)
       <div style="min-width:200px;">
        <select id="kanbanEmployee" class="crm-custom-select" name="employee_id" aria-label="{{ __('crm.responsible_employee') }}" onchange="this.form.submit()" data-crm-dropdown data-icon='<svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><path d="M10.02 12c.005-.184.02-.375.034-.555.056-.704.14-1.282.266-1.745A4.9 4.9 0 0 0 8 9c-1.378 0-2.496.53-2.92 1.077-.184.238-.309.522-.387.828a.5.5 0 0 0 .97.234c.05-.195.13-.38.252-.538C6.27 10.158 7.08 9.8 8 9.8c.92 0 1.73.358 2.085.801.074.092.127.202.164.321.037.119.06.252.073.403.014.16.023.325.027.475H3.5a.5.5 0 0 0 0 1h9a.5.5 0 0 0 .5-.5c0-.368-.008-.687-.02-1z"/></svg>'>
         <option value="">{{ __('crm.all_employees') }}</option>
         @foreach ($employees as $employee)
          <option value="{{ $employee->id }}" @selected($selectedEmployeeId === $employee->id)>
           {{ $employee->name }}
          </option>
         @endforeach
        </select>
       </div>
      @endif

      <div style="min-width:180px;">
       <select id="kanbanPerPage" class="crm-custom-select" name="per_page" aria-label="{{ __('crm.cards_per_column') }}" onchange="this.form.submit()" data-crm-dropdown data-icon='<svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M4 2v2H2V2zm1 0h2v2H5zm4 0h2v2H9zm4 0h2v2h-2zm-8 4v2H2V6zm1 0h2v2H5zm4 0h2v2H9zm4 0h2v2h-2zm-8 4v2H2v-2zm1 0h2v2H5zm4 0h2v2H9zm4 0h2v2h-2zm-8 4v2H2v-2zm1 0h2v2H5zm4 0h2v2H9zm4 0h2v2h-2z"/></svg>'>
        @foreach ($allowedPageSizes ?? [10, 20, 30, 40, 50] as $size)
         <option value="{{ $size }}" @selected(($perPage ?? 10) === $size)>
          {{ __('crm.cards_per_column') }}: {{ $size }}
         </option>
        @endforeach
       </select>
      </div>

      @if (isset($categories) && $categories->isNotEmpty())
       <div style="min-width:180px;">
        <select id="kanbanCategory" class="crm-custom-select" name="category_id" aria-label="{{ __('crm.stage_category') }}" onchange="this.form.submit()" data-crm-dropdown data-icon='<svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2 3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3zm0 7a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-2z"/></svg>'>
         <option value="">{{ __('crm.all_stage_categories') }}</option>
         @foreach ($categories as $category)
          <option value="{{ $category->id }}" @selected((string)$selectedCategoryId === (string)$category->id)>
           {{ $category->name_ar }}
          </option>
         @endforeach
        </select>
       </div>
      @endif
    </form>

    <div class="summary">
     <span>
      {{ __('crm.total_leads') }}
      <b>
       {{ number_format($totalLeads) }}
      </b>
     </span>

     <span>
      {{ __('crm.status_count') }}
      <b>
       {{ count($kanbanColumns) }}
      </b>
     </span>
    </div>
   </div>
 </section>

 <section class="board-shell">
  <div
   class="board-top-scroll"
   data-kanban-top-scroll
   role="region"
   tabindex="0"
   aria-label="{{ __('crm.kanban_horizontal_scroll') }}"
   aria-controls="kanbanBoard"
   hidden
  >
   <div class="board-top-scroll-inner" data-kanban-top-scroll-inner></div>
  </div>

  <div
   class="board"
   id="kanbanBoard"
   aria-label="{{ __('crm.kanban_board_title') }}"
  >
   @foreach (
    $kanbanColumns
    as $column
   )
    <!-- CRM KANBAN DIRECT STATUS COLUMNS V4 START -->
    @php
     $hasFollowups = isset($column['has_followups'])
      ? (bool) $column['has_followups']
      : ! in_array(
       $column['code'],
       [
        'start',
        'new',
        'not_interested',
        'execution',
       ],
       true
      );
     $kanbanDirectStatus = ! $hasFollowups || request('scope') === 'all';
    @endphp

     <article
      class="kanban-column {{ $column['class'] }}"
      data-kanban-column="{{ $column['code'] }}"
      data-kanban-status-id="{{ $column['status_id'] }}"
      data-kanban-status-name="{{ $column['name'] }}"
      data-kanban-category-id="{{ $column['category_id'] ?? '' }}"
      style="
       --column-color:
        {{ $column['status_color'] }};
      "
     >
     @php
      $code = (string) ($column['code'] ?? '');
      $iconSvg = match(true) {
          str_contains($code, 'new') || str_contains($code, 'start') => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>',
          str_contains($code, 'interest') => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>',
          str_contains($code, 'not_interested') || str_contains($code, 'not-interested') => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"/></svg>',
          str_contains($code, 'no_answer') || str_contains($code, 'no-answer') => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/><line x1="23" y1="1" x2="17" y2="7"/><line x1="17" y1="1" x2="23" y2="7"/></svg>',
          str_contains($code, 'postponed') || str_contains($code, 'delayed') => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
          str_contains($code, 'meeting') || str_contains($code, 'negotiation') => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/></svg>',
          str_contains($code, 'quotation') || str_contains($code, 'offer') => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
          str_contains($code, 'discussion') || str_contains($code, 'negotiation_call') => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><line x1="8" y1="10" x2="8.01" y2="10"/><line x1="12" y1="10" x2="12.01" y2="10"/><line x1="16" y1="10" x2="16.01" y2="10"/></svg>',
          str_contains($code, 'contract') || str_contains($code, 'closing') => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
          str_contains($code, 'execution') || str_contains($code, 'operations') => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
          str_contains($code, 'final') || str_contains($code, 'installment') || str_contains($code, 'payment') => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
          default => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polygon points="12 6 12 12 14 14"/></svg>',
      };
     @endphp

      <header class="column-head">
       <span class="column-icon">
        {!! $iconSvg !!}
       </span>
       <h2 class="column-title">
        {{ $column['name'] }}
        @if (!empty($column['category_name']))
          <span style="font-size:10px; font-weight:700; color:{{ $column['category_color'] ?: '#64748b' }}; background:{{ $column['category_color'] ? $column['category_color'].'18' : '#f1f5f9' }}; padding:2px 6px; border-radius:6px; margin-inline-start:4px; vertical-align:middle;">
            {{ $column['category_name'] }}
          </span>
        @endif
       </h2>

      <span
       class="column-count"
       data-kanban-visible-count
       data-kanban-total-count-value="{{
        $column['total_count']
       }}"
       title="{{ __('crm.all_leads_in_stage') }}"
      >
       {{
        number_format(
         $column['total_count']
        )
       }}
      </span>
     </header>

          @if (!$kanbanDirectStatus)
<div class="kanban-followup-toolbar">
      <div class="kanban-scope-buttons">
       <button
        class="kanban-scope-btn active"
        type="button"
        data-kanban-scope="today"
        data-count="{{
         $column['scope_counts']['today']
        }}"
        data-label="{{ __('crm.today') }}"
        aria-pressed="true"
        title="{{ __('crm.today') }}"
       >
        <span><i class="bi bi-calendar-check" style="margin-inline-end:3px"></i> {{ __('crm.today') }}</span>
        <b>
         {{
          number_format(
           $column[
            'scope_counts'
           ]['today']
          )
         }}
        </b>
       </button>

       <button
        class="kanban-scope-btn"
        type="button"
        data-kanban-scope="overdue"
        data-count="{{
         $column['scope_counts']['overdue']
        }}"
        data-label="{{ __('crm.overdue') }}"
        aria-pressed="false"
        title="{{ __('crm.overdue') }}"
       >
        <span><i class="bi bi-exclamation-triangle" style="margin-inline-end:3px"></i> {{ __('crm.overdue') }}</span>
        <b>
         {{
          number_format(
           $column[
            'scope_counts'
           ]['overdue']
          )
         }}
        </b>
       </button>

       <button
        class="kanban-scope-btn"
        type="button"
        data-kanban-scope="upcoming"
        data-count="{{
         $column['scope_counts']['upcoming']
        }}"
        data-label="{{ __('crm.upcoming') }}"
        aria-pressed="false"
        title="{{ __('crm.upcoming') }}"
       >
        <span><i class="bi bi-calendar-week" style="margin-inline-end:3px"></i> {{ __('crm.upcoming') }}</span>
        <b>
         {{
          number_format(
           $column[
            'scope_counts'
           ]['upcoming']
          )
         }}
        </b>
       </button>
       <button
        class="kanban-scope-btn"
        type="button"
        data-kanban-scope="no_date"
        data-count="{{
         $column['scope_counts']['no_date']
        }}"
        data-label="{{ __('crm.no_date') }}"
        aria-pressed="false"
        title="{{ __('crm.no_date') }}"
       >
        <span><i class="bi bi-calendar-minus" style="margin-inline-end:3px"></i> {{ __('crm.no_date') }}</span>
        <b>
         {{
          number_format(
           $column[
            'scope_counts'
           ]['no_date']
          )
         }}
        </b>
       </button>
      </div>


      <div class="kanban-followup-current">
       <strong data-kanban-scope-label>
        {{ __('crm.today') }}
       </strong>

       <span>
        {{ __('crm.total_status') }}:
        {{ number_format(
         $column['total_count']
        ) }}
       </span>

       
      </div>
     </div>
     @endif

     <div class="column-body">
     @php
     $scopeEntries = $kanbanDirectStatus
      ? ['today' => __('crm.all_leads_in_stage')]
      : [
       'today' => __('crm.today'),
       'overdue' => __('crm.overdue'),
       'upcoming' => __('crm.upcoming'),
       'no_date' => __('crm.no_date'),
      ];
     @endphp
     @foreach ($scopeEntries as $scope => $scopeLabel)
       @php
        if ($kanbanDirectStatus) {
         if ($scope === 'today') {
          $scopeLeads = $column['all_leads'] ?? ($column['scope_leads']['all'] ?? $column['scope_leads']['today']);
          $scopeLabel = __('crm.all_leads_in_stage');
         } else {
          $scopeLeads = collect();
         }
        } else {
         $scopeLeads = $column['scope_leads'][$scope] ?? collect();
        }
       @endphp

       <div
        class="kanban-scope-panel"
        data-kanban-panel="{{ $scope }}"
        @if ($scope !== 'today')
         hidden
        @endif
       >
        @php
         $panelScope = $kanbanDirectStatus ? 'all' : $scope;
         $scopeTotal = $kanbanDirectStatus ? $column['total_count'] : ($column['scope_counts'][$scope] ?? 0);
         $initialFrom = $scopeTotal === 0 ? 0 : 1;
         $initialTo = min($scopeTotal, $perPage ?? 10);
        @endphp
        <div class="kanban-cards-list" data-kanban-cards-list>
         @include('leads.partials.kanban-column-cards', [
             'leads' => $scopeLeads,
             'column' => $column,
             'scope' => $panelScope,
         ])
        </div>
        <footer
         class="kanban-column-pagination"
         data-kanban-pagination
         data-status-id="{{ $column['status_id'] }}"
         data-scope="{{ $panelScope }}"
         data-page="1"
         data-page-size="{{ $perPage ?? 10 }}"
         data-total="{{ $scopeTotal }}"
        >
         <div class="kanban-page-info" data-kanban-page-info>
          <span class="kanban-page-range" data-kanban-page-range>{{ $initialFrom }}–{{ $initialTo }}</span>
          <span class="kanban-page-sep">{{ __('crm.of') }}</span>
          <span class="kanban-page-total" data-kanban-page-total>{{ number_format($scopeTotal) }}</span>
         </div>
         <div class="kanban-page-actions">
          <button
           type="button"
           class="kanban-page-btn prev"
           data-kanban-page-btn="prev"
           aria-label="{{ __('crm.previous') }}"
           title="{{ __('crm.previous') }}"
           disabled
          >
           @if (app()->getLocale() === 'ar')
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
           @else
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
           @endif
          </button>
          <button
           type="button"
           class="kanban-page-btn next"
           data-kanban-page-btn="next"
           aria-label="{{ __('crm.next') }}"
           title="{{ __('crm.next') }}"
           @if ($scopeTotal <= ($perPage ?? 10)) disabled @endif
          >
           @if (app()->getLocale() === 'ar')
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
           @else
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
           @endif
          </button>
         </div>
        </footer>
       </div>
      @endforeach
     </div>
    </article>
   @endforeach
  </div>
 </section>
<!-- CRM KANBAN DIRECT STATUS COLUMNS V4 END -->
 </main>
</div>

<!-- CRM KANBAN UTILITY MODAL START -->
<div
 class="kanban-followup-modal"
 id="crmKanbanUtilityModal"
 role="dialog"
 aria-modal="true"
 aria-hidden="true"
 aria-labelledby="crmKanbanUtilityTitle"
>
 <div
  class="kanban-followup-dialog kanban-utility-dialog"
 >
  <header class="kanban-followup-modal-head">
   <div class="kanban-followup-modal-title">
    <h3 id="crmKanbanUtilityTitle">
     Kanban
    </h3>

    <p id="crmKanbanUtilityDescription">
     عرض داخل Kanban
    </p>
   </div>

   <button
    class="kanban-followup-close"
    id="crmKanbanUtilityClose"
    type="button"
    aria-label="{{ __('crm.close') }}"
    title="{{ __('crm.close') }}"
   >
    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
     <path d="M12 4L4 12M4 4l8 8" />
    </svg>
   </button>
  </header>

  <iframe
   class="kanban-followup-frame"
   id="crmKanbanUtilityFrame"
   src="about:blank"
   title="Kanban Popup"
  ></iframe>
 </div>
</div>
<!-- CRM KANBAN UTILITY MODAL END -->


<!-- CRM KANBAN CARD ACTION MODAL START -->
<div
 class="kanban-followup-modal"
 id="crmKanbanActionModal"
 role="dialog"
 aria-modal="true"
 aria-hidden="true"
 aria-labelledby="crmKanbanActionTitle"
>
 <div class="kanban-followup-dialog">
  <header class="kanban-followup-modal-head">
   <div class="kanban-followup-modal-title">
    <h3 id="crmKanbanActionTitle">
     {{ __('crm.lead_data') }}
    </h3>

    <p id="crmKanbanActionDescription">
     {{ __('crm.view_data_in_kanban') }}
    </p>
   </div>

   <button
    class="kanban-followup-close"
    id="crmKanbanActionClose"
    type="button"
    aria-label="{{ __('crm.close') }}"
    title="{{ __('crm.close') }}"
   >
    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
     <path d="M12 4L4 12M4 4l8 8" />
    </svg>
   </button>
  </header>

  <iframe
   class="kanban-followup-frame"
   id="crmKanbanActionFrame"
   src="about:blank"
   title="بيانات العميل"
  ></iframe>
 </div>
</div>
<!-- CRM KANBAN CARD ACTION MODAL END -->


<!-- CRM KANBAN FOLLOWUP MODAL START -->
<div
 class="kanban-followup-modal"
 id="crmKanbanFollowupModal"
 role="dialog"
 aria-modal="true"
 aria-hidden="true"
 aria-labelledby="crmKanbanFollowupTitle"
>
 <div class="kanban-followup-dialog">
  <header class="kanban-followup-modal-head">
   <div class="kanban-followup-modal-title">
    <h3 id="crmKanbanFollowupTitle">
     {{ __('crm.log_followup_and_change_status') }}
    </h3>

    <p id="crmKanbanFollowupDescription">
     {{ __('crm.status_change_notice') }}
    </p>
   </div>

   <button
    class="kanban-followup-close"
    id="crmKanbanFollowupClose"
    type="button"
    aria-label="{{ __('crm.cancel_and_close') }}"
    title="{{ __('crm.close') }}"
   >
    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
     <path d="M12 4L4 12M4 4l8 8" />
    </svg>
   </button>
  </header>

  <iframe
   class="kanban-followup-frame"
   id="crmKanbanFollowupFrame"
   src="about:blank"
   title="تسجيل متابعة العميل"
  ></iframe>
 </div>
</div>
<!-- CRM KANBAN FOLLOWUP MODAL END -->

<div
 class="kanban-drag-toast"
 id="crmKanbanDragToast"
 role="status"
 aria-live="polite"
></div>



<!-- CRM KANBAN TOP SCROLL JS START -->
<script>
(() => {
 const board = document.getElementById('kanbanBoard');
 const topScroll = document.querySelector('[data-kanban-top-scroll]');
 const topScrollInner = document.querySelector('[data-kanban-top-scroll-inner]');

 if (!board || !topScroll || !topScrollInner) {
  return;
 }

 let syncing = false;

 const syncScroll = (source, target) => {
  if (syncing) {
   return;
  }

  syncing = true;
  target.scrollLeft = source.scrollLeft;
  syncing = false;
 };

 const updateTopScroll = () => {
  topScrollInner.style.width = `${board.scrollWidth}px`;
  topScroll.hidden = board.scrollWidth <= board.clientWidth + 1;

  if (!topScroll.hidden) {
   topScroll.scrollLeft = board.scrollLeft;
  }
 };

 topScroll.addEventListener(
  'scroll',
  () => syncScroll(topScroll, board),
  { passive: true }
 );

 board.addEventListener(
  'scroll',
  () => syncScroll(board, topScroll),
  { passive: true }
 );

 if ('ResizeObserver' in window) {
  const resizeObserver = new ResizeObserver(updateTopScroll);
  resizeObserver.observe(board);
 }

 window.addEventListener('resize', updateTopScroll, { passive: true });
 updateTopScroll();
 window.requestAnimationFrame(updateTopScroll);
})();
</script>
<!-- CRM KANBAN TOP SCROLL JS END -->


<!-- CRM KANBAN FOLLOWUP FILTER JS START -->
<script>
document.addEventListener(
 'DOMContentLoaded',
 () => {
  document
   .querySelectorAll(
    '[data-kanban-column]'
   )
   .forEach(
    (column) => {
     const buttons =
      Array.from(
       column.querySelectorAll(
        '[data-kanban-scope]'
       )
      );

     const panels =
      Array.from(
       column.querySelectorAll(
        '[data-kanban-panel]'
       )
      );

     const visibleCount =
      column.querySelector(
       '[data-kanban-visible-count]'
      );

     const scopeLabel =
      column.querySelector(
       '[data-kanban-scope-label]'
      );

     buttons.forEach(
      (button) => {
       button.addEventListener(
        'click',
        () => {
         const scope =
          button.dataset.kanbanScope;

         buttons.forEach(
          (item) => {
           const active =
            item === button;

           item.classList.toggle(
            'active',
            active
           );

           item.setAttribute(
            'aria-pressed',
            active
             ? 'true'
             : 'false'
           );
          }
         );

         panels.forEach(
          (panel) => {
           panel.hidden =
            panel.dataset.kanbanPanel
            !== scope;
          }
         );

         if (visibleCount) {
          visibleCount.textContent =
           button.dataset.count
           || '0';
         }

         if (scopeLabel) {
          scopeLabel.textContent =
           button.dataset.label
           || '';
         }
        }
       );
      }
     );
    }
   );
 }
);
</script>
<!-- CRM KANBAN FOLLOWUP FILTER JS END -->


<!-- CRM KANBAN DRAG DROP JS START -->
<script>
(() => {
 const modal =
  document.getElementById(
   'crmKanbanFollowupModal'
  );

 const frame =
  document.getElementById(
   'crmKanbanFollowupFrame'
  );

 const closeButton =
  document.getElementById(
   'crmKanbanFollowupClose'
  );

 const modalTitle =
  document.getElementById(
   'crmKanbanFollowupTitle'
  );

 const modalDescription =
  document.getElementById(
   'crmKanbanFollowupDescription'
  );

 const toast =
  document.getElementById(
   'crmKanbanDragToast'
  );

 let dragData = null;
 let toastTimer = null;
 let callPopupActive = false;

 const clearDropTargets = () => {
  document
   .querySelectorAll(
    '.kanban-column.is-drop-target'
   )
   .forEach(
    (column) => {
     column.classList.remove(
      'is-drop-target'
     );
    }
   );
 };

 const showToast = (message) => {
  if (!toast) {
   return;
  }

  window.clearTimeout(
   toastTimer
  );

  toast.textContent = message;

  toast.classList.add(
   'show'
  );

  toastTimer =
   window.setTimeout(
    () => {
     toast.classList.remove(
      'show'
     );
    },
    2600
   );
 };

 const closeModal = () => {
  if (!modal || !frame) {
   return;
  }

  modal.classList.remove(
   'open'
  );

  modal.setAttribute(
   'aria-hidden',
   'true'
  );

  document.body.classList.remove(
   'kanban-modal-open'
  );

  frame.src = 'about:blank';

  dragData = null;
  callPopupActive = false;
 };

 const openFollowupModal = (
  targetColumn
 ) => {
  callPopupActive = false;

  if (
   !dragData
   || !modal
   || !frame
  ) {
   return;
  }

  const targetStatusId =
   targetColumn.dataset
    .kanbanStatusId || '';

  const targetStatusName =
   targetColumn.dataset
    .kanbanStatusName || '';

  if (!targetStatusId) {
   return;
  }

  if (
   String(
    dragData.currentStatusId
   )
   === String(
    targetStatusId
   )
  ) {
   showToast(
    'العميل موجود بالفعل في هذه الحالة.'
   );

   dragData = null;
   return;
  }

  const url = new URL(
   dragData.followupUrl,
   window.location.href
  );
  url.protocol = window.location.protocol;
  url.host = window.location.host;

  url.searchParams.set(
   'kanban_popup',
   '1'
  );

  url.searchParams.set(
   'target_status_id',
   targetStatusId
  );

  if (modalTitle) {
   modalTitle.textContent =
    'نقل '
    + dragData.leadName
    + ' إلى '
    + targetStatusName;
  }

  if (modalDescription) {
   modalDescription.textContent =
    'من '
    + dragData.currentStatusName
    + ' إلى '
    + targetStatusName
    + ' — لن يتغير العميل إلا بعد حفظ المتابعة.';
  }

  frame.src =
   url.toString();

  modal.classList.add(
   'open'
  );

  modal.setAttribute(
   'aria-hidden',
   'false'
  );

  document.body.classList.add(
   'kanban-modal-open'
  );
 };


 /* CRM KANBAN CALL POPUP START */

 const openCallFollowupModal = (
  button
 ) => {
  if (
   !modal
   || !frame
   || !button
  ) {
   return;
  }

  const card =
   button.closest(
    '[data-kanban-lead]'
   );

  if (!card) {
   return;
  }

  const followupUrl =
   button.dataset
    .followupUrl
   || card.dataset
    .followupUrl
   || '';

  if (!followupUrl) {
   return;
  }

  const leadName =
   card.dataset
    .kanbanLeadName
   || 'العميل';

  const currentStatusName =
   card.dataset
    .currentStatusName
   || '';

  const url = new URL(
   followupUrl,
   window.location.href
  );

  url.searchParams.set(
   'kanban_popup',
   '1'
  );

  url.searchParams.set(
   'channel',
   'call'
  );

  /*
   * No target_status_id here.
   * A call follow-up starts from the current
   * status and allows the employee to choose
   * a different status before saving.
   */
  url.searchParams.delete(
   'target_status_id'
  );

  callPopupActive = true;
  dragData = null;

  if (modalTitle) {
   modalTitle.textContent =
    'تسجيل متابعة اتصال - '
    + leadName;
  }

  if (modalDescription) {
   modalDescription.textContent =
    currentStatusName
     ? (
      'الحالة الحالية: '
      + currentStatusName
      + ' — يمكنك تغيير الحالة بعد المكالمة ثم الحفظ.'
     )
     : 'سجل نتيجة المكالمة ثم احفظ المتابعة.';
  }

  frame.src =
   url.toString();

  modal.classList.add(
   'open'
  );

  modal.setAttribute(
   'aria-hidden',
   'false'
  );

  document.body.classList.add(
   'kanban-modal-open'
  );
 };

 /*
  * Drag/drop popup deliberately locks the
  * destination status.
  *
  * Call popup uses the same follow-up screen,
  * but unlocks the status selector because the
  * employee may change the customer's status
  * after the telephone call.
  */
 frame?.addEventListener(
  'load',
  () => {
   if (!callPopupActive) {
    return;
   }

   try {
    const doc =
     frame.contentDocument;

    if (!doc) {
     return;
    }

    const statusSelect =
     doc.getElementById(
      'lead_status_id'
     );

    if (statusSelect) {
     statusSelect.disabled = false;

     const hiddenStatus =
      doc.querySelector(
       'input[type="hidden"]'
       + '[name="lead_status_id"]'
      );

     hiddenStatus?.remove();

     const field =
      statusSelect.closest(
       '.field'
      );

     const helper =
      field?.querySelector(
       'small'
      );

     if (helper) {
      helper.textContent =
       'يمكنك تغيير الحالة بعد المكالمة '
       + 'ثم حفظ المتابعة.';
     }
    }

    const communicationSelect =
     doc.getElementById(
      'communication_type'
     );

    if (communicationSelect) {
     communicationSelect.value =
      'call';

     communicationSelect
      .dispatchEvent(
       new Event(
        'change',
        {
         bubbles:true
        }
       )
      );
    }
   } catch (error) {
    console.error(
     'Unable to prepare call follow-up popup.',
     error
    );
   }
  }
 );

 document.addEventListener('click', (event) => {
  const button = event.target.closest('[data-kanban-call]');
  if (!button) {
   return;
  }
  event.preventDefault();
  event.stopPropagation();
  openCallFollowupModal(button);
 });

 document.addEventListener('dragstart', (event) => {
  const callBtn = event.target.closest('[data-kanban-call]');
  if (callBtn) {
   event.preventDefault();
   event.stopPropagation();
   return;
  }

  const card = event.target.closest('.kanban-card[draggable="true"]');
  if (!card) {
   return;
  }

  dragData = {
   leadId:
    card.dataset
     .kanbanLead || '',
   leadName:
    card.dataset
     .kanbanLeadName
     || 'العميل',
   currentStatusId:
    card.dataset
     .currentStatusId
     || '',
   currentStatusName:
    card.dataset
     .currentStatusName
     || '',
   followupUrl:
    card.dataset
     .followupUrl
     || '',
  };

  card.classList.add('is-dragging');

  if (event.dataTransfer) {
   event.dataTransfer.effectAllowed = 'move';
   event.dataTransfer.setData('text/plain', dragData.leadId);
  }
 });

 document.addEventListener('dragend', (event) => {
  const card = event.target.closest('.kanban-card');
  if (card) {
   card.classList.remove('is-dragging');
  }
  clearDropTargets();
 });

 document.addEventListener('dragover', (event) => {
  if (!dragData) {
   return;
  }
  const column = event.target.closest('[data-kanban-status-id]');
  if (!column) {
   return;
  }
  event.preventDefault();
  if (event.dataTransfer) {
   event.dataTransfer.dropEffect = 'move';
  }
  clearDropTargets();
  column.classList.add('is-drop-target');
 });

 document.addEventListener('drop', (event) => {
  const column = event.target.closest('[data-kanban-status-id]');
  if (!column || !dragData) {
   return;
  }
  event.preventDefault();
  clearDropTargets();
  openFollowupModal(column);
 });

 closeButton?.addEventListener(
  'click',
  closeModal
 );

 modal?.addEventListener(
  'click',
  (event) => {
   if (event.target === modal) {
    closeModal();
   }
  }
 );

 document.addEventListener(
  'keydown',
  (event) => {
   if (
    event.key === 'Escape'
    && modal?.classList.contains(
     'open'
    )
   ) {
    closeModal();
   }
  }
 );

 window.addEventListener(
  'message',
  (event) => {
   if (
    event.origin
    !== window.location.origin
   ) {
    return;
   }

   if (
    event.data?.type
    === 'crm-kanban-popup-close'
    || event.data?.type
    === 'crm-kanban-cancel'
   ) {
    document.querySelectorAll('.kanban-followup-modal.open').forEach(modalEl => {
     modalEl.classList.remove('open');
     modalEl.setAttribute('aria-hidden', 'true');
     const iframe = modalEl.querySelector('iframe');
     if (iframe) iframe.src = 'about:blank';
    });
    document.body.classList.remove('kanban-modal-open');
    closeModal();
    return;
   }

   if (
    event.data?.type
    !== 'crm-kanban-followup-saved'
   ) {
    return;
   }

   if (modalTitle) {
    modalTitle.textContent =
     'تم حفظ المتابعة بنجاح';
   }

   if (modalDescription) {
    modalDescription.textContent =
     'يتم تحديث Kanban الآن...';
   }

   window.setTimeout(
    () => {
     window.location.reload();
    },
    250
   );
  }
 );
})();
</script>
<!-- CRM KANBAN DRAG DROP JS END -->


<!-- CRM KANBAN CALL DIALER AND POPUP START -->
<script>
(() => {
 const modal =
  document.getElementById(
   'crmKanbanFollowupModal'
  );

 const frame =
  document.getElementById(
   'crmKanbanFollowupFrame'
  );

 const title =
  document.getElementById(
   'crmKanbanFollowupTitle'
  );

 const description =
  document.getElementById(
   'crmKanbanFollowupDescription'
  );

 let activeCallPopup = false;

 const prepareCallPopup = (
  button
 ) => {
  if (
   !button
   || !modal
   || !frame
  ) {
   return false;
  }

  const card =
   button.closest(
    '[data-kanban-lead]'
   );

  if (!card) {
   return false;
  }

  const followupUrl =
   button.dataset.followupUrl
   || card.dataset.followupUrl
   || '';

  if (!followupUrl) {
   return false;
  }

  const leadName =
   card.dataset.kanbanLeadName
   || 'العميل';

  const currentStatus =
   card.dataset.currentStatusName
   || '';

  const url = new URL(
   followupUrl,
   window.location.href
  );
  url.protocol = window.location.protocol;
  url.host = window.location.host;

  url.searchParams.set(
   'kanban_popup',
   '1'
  );

  url.searchParams.set(
   'channel',
   'call'
  );

  /*
   * A normal CALL follow-up starts from
   * the current status.
   * It is not a drag/drop destination.
   */
  url.searchParams.delete(
   'target_status_id'
  );

  if (title) {
   title.textContent =
    'تسجيل متابعة اتصال - '
    + leadName;
  }

  if (description) {
   description.textContent =
    currentStatus
     ? (
      'الحالة الحالية: '
      + currentStatus
      + ' — سجل نتيجة المكالمة ثم احفظ المتابعة.'
     )
     : 'سجل نتيجة المكالمة ثم احفظ المتابعة.';
  }

  activeCallPopup = true;

  frame.src =
   url.toString();

  modal.classList.add(
   'open'
  );

  modal.setAttribute(
   'aria-hidden',
   'false'
  );

  document.body.classList.add(
   'kanban-modal-open'
  );

  return true;
 };

 /*
  * Drag/drop popup locks the destination status.
  * A telephone call is different:
  * employee can change the status after talking
  * with the customer.
  */
 frame?.addEventListener(
  'load',
  () => {
   if (!activeCallPopup) {
    return;
   }

   try {
    const doc =
     frame.contentDocument;

    if (!doc) {
     return;
    }

    const statusSelect =
     doc.getElementById(
      'lead_status_id'
     );

    if (statusSelect) {
     statusSelect.disabled = false;

     const hiddenStatus =
      doc.querySelector(
       'input[type="hidden"]'
       + '[name="lead_status_id"]'
      );

     hiddenStatus?.remove();

     const field =
      statusSelect.closest(
       '.field'
      );

     const helper =
      field?.querySelector(
       'small'
      );

     if (helper) {
      helper.textContent =
       'يمكنك تغيير حالة العميل '
       + 'بعد انتهاء المكالمة ثم الحفظ.';
     }
    }

    const communicationSelect =
     doc.getElementById(
      'communication_type'
     );

    if (communicationSelect) {
     communicationSelect.value =
      'call';

     communicationSelect
      .dispatchEvent(
       new Event(
        'change',
        {
         bubbles:true
        }
       )
      );
    }
   } catch (error) {
    console.error(
     'Unable to prepare call popup.',
     error
    );
   }
  }
 );

 document.addEventListener(
  'click',
  (event) => {
   const button = event.target.closest('[data-kanban-call-dial-popup]');
   if (!button) {
    return;
   }

   event.preventDefault();
   event.stopImmediatePropagation();

   const telHref =
    button.dataset.telHref
    || button.getAttribute('href')
    || '';

   const popupOpened = prepareCallPopup(button);

   if (!popupOpened) {
    if (telHref) {
     window.location.href = telHref;
    }
    return;
   }

   if (telHref) {
    window.setTimeout(() => {
     window.location.href = telHref;
    }, 180);
   }
  },
  true
 );

 document.addEventListener(
  'dragstart',
  (event) => {
   const button = event.target.closest('[data-kanban-call-dial-popup]');
   if (button) {
    event.preventDefault();
    event.stopPropagation();
   }
  }
 );

 window.addEventListener(
  'message',
  (event) => {
   if (
    event.origin
    !== window.location.origin
   ) {
    return;
   }

   if (
    event.data?.type
    === 'crm-kanban-followup-saved'
   ) {
    activeCallPopup = false;
   }
  }
 );
})();
</script>
<!-- CRM KANBAN CALL DIALER AND POPUP END -->


<!-- CRM KANBAN CARD ACTION POPUPS V2 START -->
<script>
(() => {
 const modal =
  document.getElementById(
   'crmKanbanActionModal'
  );

 const frame =
  document.getElementById(
   'crmKanbanActionFrame'
  );

 const closeButton =
  document.getElementById(
   'crmKanbanActionClose'
  );

 const title =
  document.getElementById(
   'crmKanbanActionTitle'
  );

 const description =
  document.getElementById(
   'crmKanbanActionDescription'
  );

 if (
  !modal
  || !frame
 ) {
  return;
 }

 let popupMode = '';

 const closePopup = () => {
  modal.classList.remove(
   'open'
  );

  modal.setAttribute(
   'aria-hidden',
   'true'
  );

  document.body.classList.remove(
   'kanban-modal-open'
  );

  frame.src = 'about:blank';

  popupMode = '';
 };

 const openPopup = (
  url,
  heading,
  subheading,
  mode
 ) => {
  popupMode = mode;

  if (title) {
   title.textContent =
    heading;
  }

  if (description) {
   description.textContent =
    subheading;
  }

  frame.src = url;

  modal.classList.add(
   'open'
  );

  modal.setAttribute(
   'aria-hidden',
   'false'
  );

  document.body.classList.add(
   'kanban-modal-open'
  );
 };

 /*
  * Follow-up popup uses the compact popup
  * version of the existing follow-up page.
  *
  * The generic follow-up button is NOT a
  * drag/drop operation, so the status selector
  * must remain editable.
  */
 frame.addEventListener(
  'load',
  () => {
   if (
    popupMode !== 'followup'
   ) {
    return;
   }

   try {
    const doc =
     frame.contentDocument;

    if (!doc) {
     return;
    }

    const statusSelect =
     doc.getElementById(
      'lead_status_id'
     );

    if (statusSelect) {
     statusSelect.disabled = false;

     const hiddenStatus =
      doc.querySelector(
       'input[type="hidden"]'
       + '[name="lead_status_id"]'
      );

     hiddenStatus?.remove();

     const field =
      statusSelect.closest(
       '.field'
      );

     const helper =
      field?.querySelector(
       'small'
      );

     if (helper) {
      helper.textContent =
       'يمكنك اختيار حالة العميل '
       + 'ثم حفظ المتابعة.';
     }
    }
   } catch (error) {
    console.error(
     'Unable to prepare follow-up popup.',
     error
    );
   }
  }
 );

 document.addEventListener('click', (event) => {
  const followupBtn = event.target.closest('[data-kanban-followup-popup]');
  if (followupBtn) {
   event.preventDefault();
   event.stopPropagation();

   const href = followupBtn.getAttribute('href');
   if (!href) {
    return;
   }

   const card = followupBtn.closest('[data-kanban-lead]');
   const leadName = card?.dataset.kanbanLeadName || 'العميل';
   const statusName = card?.dataset.currentStatusName || '';

   const url = new URL(href, window.location.href);
   url.protocol = window.location.protocol;
   url.host = window.location.host;
   url.searchParams.set('kanban_popup', '1');
   url.searchParams.delete('target_status_id');

   openPopup(
    url.toString(),
    'تسجيل متابعة - ' + leadName,
    statusName
     ? ('الحالة الحالية: ' + statusName + ' — سجل المتابعة ثم احفظ.')
     : 'سجل المتابعة ثم احفظ.',
    'followup'
   );
   return;
  }

  const customerBtn = event.target.closest('[data-kanban-customer-popup]');
  if (customerBtn) {
   event.preventDefault();
   event.stopPropagation();

   const href = customerBtn.getAttribute('href');
   if (!href) {
    return;
   }

   const card = customerBtn.closest('[data-kanban-lead]');
   const leadName = card?.dataset.kanbanLeadName || 'العميل';
   const statusName = card?.dataset.currentStatusName || '';

   const customerUrl = new URL(href, window.location.href);
   customerUrl.searchParams.set('kanban_popup', '1');
   openPopup(
    customerUrl.toString(),
    'بيانات العميل - ' + leadName,
    statusName
     ? ('الحالة الحالية: ' + statusName)
     : 'عرض بيانات العميل.',
    'customer'
   );
   return;
  }
 });

 document.addEventListener('dragstart', (event) => {
  const btn = event.target.closest('[data-kanban-followup-popup], [data-kanban-customer-popup]');
  if (btn) {
   event.preventDefault();
   event.stopPropagation();
  }
 });
 closeButton?.addEventListener(
  'click',
  closePopup
 );

 modal.addEventListener(
  'click',
  (event) => {
   if (
    event.target === modal
   ) {
    closePopup();
   }
  }
 );

 document.addEventListener(
  'keydown',
  (event) => {
   if (
    event.key === 'Escape'
    && modal.classList.contains(
     'open'
    )
   ) {
    closePopup();
   }
  }
 );
})();
</script>
<!-- CRM KANBAN CARD ACTION POPUPS V2 END -->


<!-- CRM KANBAN FIXED TOTAL COUNT START -->
<script>
(() => {
 const formatter =
  new Intl.NumberFormat(
   'en-US'
  );

 document
  .querySelectorAll(
   '[data-kanban-column]'
  )
  .forEach(
   (column) => {
    const counter =
     column.querySelector(
      '[data-kanban-total-count-value]'
     );

    if (!counter) {
     return;
    }

    const total =
     Number(
      counter.dataset
       .kanbanTotalCountValue
      || 0
     );

    const keepTotal = () => {
     counter.textContent =
      formatter.format(
       total
      );
    };

    keepTotal();

    column
     .querySelectorAll(
      '[data-kanban-scope]'
     )
     .forEach(
      (button) => {
       button.addEventListener(
        'click',
        () => {
         /*
          * Existing scope script may update
          * the header with the selected scope.
          * Restore the permanent STATUS TOTAL
          * immediately afterwards.
          */
         window.setTimeout(
          keepTotal,
          0
         );
        }
       );
      }
     );
   }
  );
})();
</script>
<!-- CRM KANBAN FIXED TOTAL COUNT END -->


<!-- CRM KANBAN UTILITY POPUPS JS START -->
<script>
(() => {
 const modal =
  document.getElementById(
   'crmKanbanUtilityModal'
  );

 const frame =
  document.getElementById(
   'crmKanbanUtilityFrame'
  );

 const closeButton =
  document.getElementById(
   'crmKanbanUtilityClose'
  );

 const title =
  document.getElementById(
   'crmKanbanUtilityTitle'
  );

 const description =
  document.getElementById(
   'crmKanbanUtilityDescription'
  );

 const leadsIndexUrl =
  @json(
   route('v2.leads')
  );

 if (
  !modal
  || !frame
 ) {
  return;
 }

 let utilityMode = '';

 const closePopup = () => {
  modal.classList.remove(
   'open'
  );

  modal.setAttribute(
   'aria-hidden',
   'true'
  );

  document.body.classList.remove(
   'kanban-modal-open'
  );

  frame.src = 'about:blank';
  utilityMode = '';
 };

 const openPopup = (
  url,
  heading,
  subheading,
  mode
 ) => {
  utilityMode = mode;

  if (title) {
   title.textContent =
    heading;
  }

  if (description) {
   description.textContent =
    subheading;
  }

  frame.src = url;

  modal.classList.add(
   'open'
  );

  modal.setAttribute(
   'aria-hidden',
   'false'
  );

  document.body.classList.add(
   'kanban-modal-open'
  );
 };


 document.addEventListener('click', (event) => {
  const createBtn = event.target.closest('[data-kanban-create-popup]');
  if (createBtn) {
   event.preventDefault();
   event.stopPropagation();

   const href = createBtn.getAttribute('href');
   if (!href) {
    return;
   }

   const createUrl = new URL(href, window.location.href);
   createUrl.searchParams.set('kanban_popup', '1');
   openPopup(
    createUrl.toString(),
    'إضافة عميل جديد',
    'أضف بيانات العميل من داخل Kanban.',
    'create'
   );
   return;
  }
 });

 document.addEventListener('dragstart', (event) => {
  const btn = event.target.closest('[data-kanban-create-popup]');
  if (btn) {
   event.preventDefault();
   event.stopPropagation();
  }
 });

 /*
  * Normal successful creation redirects
  * from /leads/create back to /leads.
  * When that happens inside the iframe,
  * refresh Kanban so the new customer
  * appears immediately.
  */
 frame.addEventListener(
  'load',
  () => {
   if (
    utilityMode !== 'create'
   ) {
    return;
   }

   try {
    const current =
     new URL(
      frame.contentWindow
       .location.href
     );

    const index =
     new URL(
      leadsIndexUrl,
      window.location.href
     );

    const clean = (value) =>
     value.replace(
      /\/+$/,
      ''
     );

    if (
     current.origin
      === index.origin
     &&
     clean(current.pathname)
      === clean(index.pathname)
     &&
     current.search === ''
    ) {
     window.location.reload();
    }
   } catch (error) {
    console.error(
     'Unable to inspect create popup.',
     error
    );
   }
  }
 );

 closeButton?.addEventListener(
  'click',
  closePopup
 );

 modal.addEventListener(
  'click',
  (event) => {
   if (
    event.target === modal
   ) {
    closePopup();
   }
  }
 );

 document.addEventListener(
  'keydown',
  (event) => {
   if (
    event.key === 'Escape'
    && modal.classList.contains(
     'open'
    )
   ) {
    closePopup();
   }
  }
 );

 /*
  * Keep the number beside each stage as
  * the TOTAL number of customers even
  * when changing follow-up filters.
  */
 document
  .querySelectorAll(
   '[data-kanban-column]'
  )
  .forEach(
   (column) => {
    const counter =
     column.querySelector(
      '[data-kanban-total-count-value]'
     );

    if (!counter) {
     return;
    }

    const total =
     Number(
      counter.dataset
       .kanbanTotalCountValue
      || 0
     );

    const restoreTotal = () => {
     counter.textContent =
      new Intl.NumberFormat(
       'en-US'
      ).format(
       total
      );
    };

    restoreTotal();

    column
     .querySelectorAll(
      '[data-kanban-scope]'
     )
     .forEach(
      (button) => {
       button.addEventListener(
        'click',
        () => {
         window.setTimeout(
          restoreTotal,
          0
         );
        }
       );
      }
     );
   }
  );
})();
</script>
<!-- CRM KANBAN UTILITY POPUPS JS END -->


<!-- CRM KANBAN INTERNAL PER-COLUMN PAGINATION JS START -->
<script>
(() => {
 const columnEndpoint = @json(route('v2.leads.kanban.column'));
 const employeeFilterSelect = document.querySelector('select[name="employee_id"]');
 const perPageSelect = document.querySelector('select[name="per_page"]');
 const locale = @json(app()->getLocale());
 const isRtl = locale === 'ar';

 const updatePaginationUI = (paginationEl, data) => {
  paginationEl.dataset.page = String(data.page);
  paginationEl.dataset.pageSize = String(data.pageSize || 10);
  paginationEl.dataset.total = String(data.total);

  const rangeEl = paginationEl.querySelector('[data-kanban-page-range]');
  const totalEl = paginationEl.querySelector('[data-kanban-page-total]');
  const prevBtn = paginationEl.querySelector('[data-kanban-page-btn="prev"]');
  const nextBtn = paginationEl.querySelector('[data-kanban-page-btn="next"]');

  if (rangeEl) {
   rangeEl.textContent = `${data.from}–${data.to}`;
  }
  if (totalEl) {
   totalEl.textContent = new Intl.NumberFormat('en-US').format(data.total);
  }
  if (prevBtn) {
   prevBtn.disabled = !data.hasPrevious;
  }
  if (nextBtn) {
   nextBtn.disabled = !data.hasMore;
  }
 };

 const loadColumnPage = async (paginationEl, targetPage) => {
  const statusId = paginationEl.dataset.statusId;
  const scope = paginationEl.dataset.scope || 'all';
  const pageSize = paginationEl.dataset.pageSize || (perPageSelect ? perPageSelect.value : '10');
  const panel = paginationEl.closest('[data-kanban-panel]');
  const cardsList = panel ? panel.querySelector('[data-kanban-cards-list]') : null;
  const prevBtn = paginationEl.querySelector('[data-kanban-page-btn="prev"]');
  const nextBtn = paginationEl.querySelector('[data-kanban-page-btn="next"]');

  if (!statusId || !cardsList) {
   return;
  }

  if (prevBtn) prevBtn.disabled = true;
  if (nextBtn) nextBtn.disabled = true;
  cardsList.classList.add('is-loading');

  const url = new URL(columnEndpoint, window.location.origin);
  url.searchParams.set('status_id', statusId);
  url.searchParams.set('scope', scope);
  url.searchParams.set('page', String(targetPage));
  url.searchParams.set('per_page', String(pageSize));

  if (employeeFilterSelect && employeeFilterSelect.value) {
   url.searchParams.set('employee_id', employeeFilterSelect.value);
  }

  try {
   const response = await fetch(url.toString(), {
    headers: {
     'Accept': 'application/json',
     'X-Requested-With': 'XMLHttpRequest',
    },
   });

   if (!response.ok) {
    throw new Error(`HTTP error ${response.status}`);
   }

   const data = await response.json();

   if (!data.success) {
    throw new Error('Failed to load cards');
   }

   cardsList.innerHTML = data.html;
   updatePaginationUI(paginationEl, data);
  } catch (error) {
   console.error('Kanban column pagination error:', error);
   const currentPage = Number(paginationEl.dataset.page || 1);
   const total = Number(paginationEl.dataset.total || 0);
   const currentPageSize = Number(paginationEl.dataset.pageSize || 10);
   if (prevBtn) prevBtn.disabled = currentPage <= 1;
   if (nextBtn) nextBtn.disabled = (currentPage * currentPageSize) >= total;

   const toast = document.getElementById('crmKanbanDragToast');
   if (toast) {
    toast.textContent = isRtl ? 'تعذر تحميل البيانات، يرجى المحاولة مرة أخرى' : 'Failed to load data, please try again.';
    toast.classList.add('show');
    window.setTimeout(() => toast.classList.remove('show'), 3000);
   }
  } finally {
   cardsList.classList.remove('is-loading');
  }
 };

 document.addEventListener('click', (event) => {
  const btn = event.target.closest('[data-kanban-page-btn]');
  if (!btn || btn.disabled) {
   return;
  }

  event.preventDefault();
  event.stopPropagation();

  // Make kanban filter controls fully clickable
  document.addEventListener('click', (event) => {
   const control = event.target.closest('.kanban-filter-control');
   if (!control) return;
   const select = control.querySelector('select');
   if (!select || event.target === select) return;

   event.preventDefault();
   if (typeof select.showPicker === 'function') {
    try {
     select.showPicker();
     return;
    } catch (_) {}
   }
   select.focus();
  });

  const paginationEl = btn.closest('[data-kanban-pagination]');
  if (!paginationEl) {
   return;
  }

  const action = btn.dataset.kanbanPageBtn;
  const currentPage = Number(paginationEl.dataset.page || 1);
  const targetPage = action === 'prev' ? Math.max(1, currentPage - 1) : (currentPage + 1);

  loadColumnPage(paginationEl, targetPage);
 });
})();
</script>
<!-- CRM KANBAN INTERNAL PER-COLUMN PAGINATION JS END -->
<script src="{{ asset('crm-dropdown.js') }}?v=1.0.1"></script>
</body>
</html>
