<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Doorprize — {{ $event->title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f8fafc; color:#1e293b; height:100vh; overflow:hidden; display:flex; flex-direction:column; position:relative; }
        .bg-layer { position:absolute; inset:0; z-index:0; background: radial-gradient(circle at 50% 50%, #f0f7ff 0%, #e0e7ff 40%, #c7d2fe 100%); }
        .bg-layer img { width:100%; height:100%; object-fit:cover; opacity:.9; }
        .bg-overlay { position:absolute; inset:0; background: linear-gradient(135deg, rgba(99, 102, 241, 0.12) 0%, rgba(236, 72, 153, 0.12) 50%, rgba(255, 255, 255, 0.1) 100%); z-index:1; }
        .content { position:relative; z-index:2; display:flex; flex-direction:column; height:100vh; }

        /* Top bar */
        .top-bar { padding:16px 40px; display:flex; align-items:center; justify-content:space-between; background: rgba(255, 255, 255, 0.18); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02); }
        .top-bar .event-title { font-size:18px; font-weight:800; color:#1e293b; display:flex; align-items:center; gap:10px; }
        .top-bar .event-title .material-symbols-outlined { font-size:24px; color:#fbbf24; filter: drop-shadow(0 2px 4px rgba(251,191,36,0.3)); }
        .selectors { display:flex; gap:12px; }
        .selectors select { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); color:#fff; padding:10px 16px; border-radius:12px; font-size:13px; font-weight:600; font-family:inherit; cursor:pointer; min-width:180px; appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 12px center; }
        .selectors select:focus { outline:none; border-color:rgba(99,102,241,.6); }

        /* Center stage */
        .stage { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:24px; padding:0 40px; }
        .prize-info { display: inline-block; text-align:center; font-size:14px; font-weight:700; color:#475569; background: rgba(255, 255, 255, 0.65); padding: 10px 28px; border-radius: 100px; border: 1px solid rgba(255, 255, 255, 0.8); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03); }
        .prize-info .prize-name { font-size:26px; font-weight:900; color:#d97706; margin-bottom:2px; }

        /* Roller */
        .roller-container { width:100%; max-width:700px; height:340px; position:relative; overflow:hidden; border-radius:32px; background: rgba(255, 255, 255, 0.65); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 30px 60px rgba(0, 0, 0, 0.06), inset 0 0 0 1px rgba(255, 255, 255, 0.5); }
        .roller-mask { position:absolute; inset:0; z-index:3; pointer-events:none; background: linear-gradient(180deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0) 30%, rgba(255, 255, 255, 0) 70%, rgba(255, 255, 255, 0.95) 100%); }
        .roller-highlight { position:absolute; left:0; right:0; top:50%; transform:translateY(-50%); height:76px; border-top: 2px solid rgba(99, 102, 241, 0.35); border-bottom: 2px solid rgba(99, 102, 241, 0.35); background: rgba(99, 102, 241, 0.08); z-index:2; pointer-events:none; }
        .roller-track { position:absolute; left:0; right:0; top:0; transition:none; z-index:1; }
        .roller-item { height:72px; display:flex; align-items:center; justify-content:center; flex-direction:column; }
        .roller-item .rname { font-size:28px; font-weight:800; color:rgba(30, 41, 59, 0.35); transition:all 0.2s ease; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:90%; }
        .roller-item .rorg { font-size:13px; font-weight:600; color:rgba(30, 41, 59, 0.25); }
        .roller-item.active .rname { color: #4f46e5; font-size: 34px; font-weight: 900; text-shadow: 0 4px 15px rgba(99, 102, 241, 0.2); }
        .roller-item.active .rorg { color: #6366f1; font-weight: 700; }

        /* Winner reveal */
        .winner-reveal { display:none; flex-direction:column; align-items:center; text-align:center; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 2px dashed rgba(251, 191, 36, 0.6); padding: 40px 60px; border-radius: 36px; box-shadow: 0 30px 65px rgba(251, 191, 36, 0.15), 0 10px 25px rgba(0, 0, 0, 0.04); max-width: 600px; width: 100%; }
        .winner-reveal.visible { display:flex; animation:winnerPop .6s cubic-bezier(.175,.885,.32,1.275); }
        .winner-reveal .trophy { font-size:72px; color:#fbbf24; margin-bottom:16px; animation: prizeBounce 1s infinite alternate; filter: drop-shadow(0 4px 10px rgba(251,191,36,0.3)); }
        .winner-reveal .wname { font-size:48px; font-weight:900; background: linear-gradient(135deg, #4f46e5, #ec4899, #f59e0b); -webkit-background-clip:text; -webkit-text-fill-color:transparent; line-height:1.2; }
        .winner-reveal .worg { font-size:20px; font-weight:700; color:#475569; margin-top:8px; }
        .winner-reveal .wprize { font-size:15px; font-weight:800; color:#b45309; margin-top:18px; padding:10px 28px; border-radius:100px; background: rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.3); }
        @keyframes winnerPop { 0%{transform:scale(.5);opacity:0} 100%{transform:scale(1);opacity:1} }
        @keyframes prizeBounce { from { transform: translateY(0) scale(1); } to { transform: translateY(-10px) scale(1.05); } }

        /* Idle state */
        .idle-msg { text-align:center; color:#475569; font-size:18px; font-weight:700; background: rgba(255, 255, 255, 0.5); padding: 24px 48px; border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.8); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.02); }
        .idle-msg .material-symbols-outlined { font-size:56px; color:#6366f1; margin-bottom:12px; display:block; opacity:0.9; }
        .pulse { animation:pulse 2s ease-in-out infinite; }
        @keyframes pulse { 0%,100%{opacity:.6} 50%{opacity:.95} }

        /* Bottom bar */
        .bottom-bar { padding:20px 40px 28px; display:flex; align-items:center; justify-content:space-between; }
        .recent-winners { display:flex; align-items:center; gap:8px; flex-wrap:wrap; max-width:65%; }
        .recent-winners .label { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:#475569; margin-right:4px; }
        .winner-chip { padding:6px 14px; border-radius:100px; background: rgba(255, 255, 255, 0.8); border: 1px solid rgba(251, 191, 36, 0.4); font-size:12px; font-weight:700; color:#b45309; display:flex; align-items:center; gap:5px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03); }
        .winner-chip .material-symbols-outlined { font-size:14px; color: #f59e0b; }
        .eligible-count { display: none; }

        /* Draw button */
        .draw-btn { padding:16px 48px; border-radius:16px; font-size:16px; font-weight:800; font-family:inherit; cursor:pointer; border:none; transition:all .2s; text-transform:uppercase; letter-spacing:1px; display:flex; align-items:center; gap:10px; }
        .draw-btn.start { background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; box-shadow:0 8px 32px rgba(99,102,241,.3); }
        .draw-btn.start:hover { transform:translateY(-2px); box-shadow:0 12px 40px rgba(99,102,241,.4); }
        .draw-btn.stop { background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; box-shadow:0 8px 32px rgba(239,68,68,.3); }
        .draw-btn.stop:hover { transform:translateY(-2px); box-shadow:0 12px 40px rgba(239,68,68,.4); }
        .draw-btn:disabled { opacity:.4; cursor:not-allowed; transform:none !important; box-shadow:none !important; }
        .draw-btn .countdown { font-variant-numeric:tabular-nums; }

        /* No-slots badge */
        .no-slots { padding:12px 24px; border-radius:12px; background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.2); color:#f87171; font-size:14px; font-weight:700; }

        /* Grid of slots */
        .grid-slots {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            justify-content: center;
        }

        /* Slot card style */
        .slot-card {
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 24px;
            padding: 24px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(15px);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            min-height: 200px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.04);
        }

        .slot-card .prize-tag {
            font-size: 13px;
            font-weight: 800;
            color: #b45309;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 16px;
            opacity: 0.95;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            padding-bottom: 8px;
        }

        .card-slots-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }

        /* Slot Item style */
        .slot-item {
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 16px;
            padding: 16px;
            position: relative;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .slot-item.rolling {
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.15);
            background: rgba(99, 102, 241, 0.05);
        }

        .slot-item.winner-drawn {
            border-color: rgba(251, 191, 36, 0.5);
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.18);
            background: rgba(251, 191, 36, 0.08);
            transform: scale(1.03);
        }

        .slot-item .winner-name {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 4px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .slot-item .winner-company {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .slot-item.winner-drawn .winner-name {
            background: linear-gradient(135deg, #b45309, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 900;
        }

        .slot-item.winner-drawn .winner-company {
            color: #b45309;
            opacity: 0.9;
        }

        .slot-item .trophy-icon {
            font-size: 28px;
            color: #fbbf24;
            margin-bottom: 6px;
            display: none;
            filter: drop-shadow(0 0 10px rgba(251, 191, 36, 0.3));
        }

        .slot-item.winner-drawn .trophy-icon {
            display: block;
            animation: bounceIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.1); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }

        .slot-item .slot-redraw-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            color: #f87171;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 700;
            display: none;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 5;
        }
        .slot-item.winner-drawn:hover .slot-redraw-btn {
            display: flex;
        }
        .slot-item .slot-redraw-btn:hover {
            background: rgba(239, 68, 68, 0.3);
            border-color: rgba(239, 68, 68, 0.5);
            transform: scale(1.05);
        }

        /* Controls Right */
        .controls-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .icon-btn {
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            color: #334155;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .icon-btn:hover {
            background: #ffffff;
            border-color: #6366f1;
            color: #4f46e5;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.15);
        }

        .icon-btn:active {
            transform: translateY(0);
        }

        /* Modal Style */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(15px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 28px;
            width: 90%;
            max-width: 500px;
            padding: 32px;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(20px);
            animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            padding-bottom: 16px;
        }

        .modal-header h2 {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
        }

        .modal-close-btn {
            background: transparent;
            border: none;
            color: rgba(30, 41, 59, 0.5);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .modal-close-btn:hover {
            color: #1e293b;
        }

        .modal-body {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 400px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .modal-body::-webkit-scrollbar {
            width: 6px;
        }
        .modal-body::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.08);
            border-radius: 3px;
        }

        .session-modal-item {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            padding: 16px 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-align: left;
        }

        .session-modal-item:hover {
            background: rgba(99, 102, 241, 0.08);
            border-color: rgba(99, 102, 241, 0.3);
            transform: translateX(4px);
        }

        .session-modal-item.active {
            background: rgba(99, 102, 241, 0.12);
            border-color: #6366f1;
        }

        .session-modal-item .session-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .session-modal-item .session-name {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
        }

        .session-modal-item .session-meta {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
        }

        .session-modal-item .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .session-modal-item.active .status-dot {
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    {{-- Background --}}
    <div class="bg-layer">
        @if($event->doorprize_background)
            <img src="{{ asset('storage/' . $event->doorprize_background) }}" alt="Background"/>
        @elseif(file_exists(public_path('images/doorprize-default-bg.jpg')))
            <img src="{{ asset('images/doorprize-default-bg.jpg') }}" alt="Background"/>
        @elseif(file_exists(public_path('images/doorprize-default-bg.png')))
            <img src="{{ asset('images/doorprize-default-bg.png') }}" alt="Background"/>
        @elseif(file_exists(public_path('images/doorprize-default-bg.webp')))
            <img src="{{ asset('images/doorprize-default-bg.webp') }}" alt="Background"/>
        @endif
    </div>
    <div class="bg-overlay"></div>

    <div class="content">
        {{-- Top Bar --}}
        <div class="top-bar">
            <div class="event-title">
                <span class="material-symbols-outlined">redeem</span>
                {{ $event->title }} — Doorprize
            </div>
            <div class="controls-right">
                <button class="icon-btn" onclick="openSessionModal()" title="Select Session">
                    <span class="material-symbols-outlined">settings</span>
                    <span>Session</span>
                </button>
                <button class="icon-btn" onclick="toggleFullscreen()" title="Toggle Fullscreen">
                    <span class="material-symbols-outlined" id="fsIcon">fullscreen</span>
                </button>
                <select id="prizeSelect" onchange="onPrizeChange()" style="display:none">
                    <option value="">— Select Prize —</option>
                </select>
                <select id="sessionSelect" style="display:none"></select>
            </div>
        </div>

        {{-- Center Stage --}}
        <div class="stage">
            <div id="prizeInfo" style="display:none; text-align:center;">
                <div id="prizeImageContainer" style="display:none; margin: 0 auto 16px; width: 140px; height: 140px; overflow: hidden;">
                    <img id="prizeImage" src="" style="width:100%; height:100%; object-fit:contain;"/>
                </div>
                <div class="prize-info">
                    <div class="prize-name" id="prizeNameDisplay"></div>
                    <div style="display:none">Remaining: <span id="prizeRemaining"></span> / <span id="prizeTotal"></span></div>
                </div>
            </div>

            {{-- Idle --}}
            <div id="idleState" class="idle-msg pulse">
                <span class="material-symbols-outlined">casino</span>
                Select a session to begin
            </div>

            {{-- Roller --}}
            <div id="rollerContainer" class="roller-container" style="display:none">
                <div class="roller-mask"></div>
                <div class="roller-highlight"></div>
                <div class="roller-track" id="rollerTrack"></div>
            </div>

            {{-- Winner Reveal --}}
            <div id="winnerReveal" class="winner-reveal">
                <span class="material-symbols-outlined trophy">emoji_events</span>
                <div id="winnerPrizeImageContainer" style="display:none; margin: 0 auto 16px; width: 120px; height: 120px; overflow: hidden;">
                    <img id="winnerPrizeImage" src="" style="width:100%; height:100%; object-fit:contain;"/>
                </div>
                <div class="wname" id="winnerName"></div>
                <div class="worg" id="winnerOrg"></div>
                <div class="wprize" id="winnerPrize"></div>
                <button id="singleRedrawBtn" class="icon-btn" style="margin-top: 24px; background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.3); color: #f87171;" onclick="handleRedrawSingle()">
                    <span class="material-symbols-outlined">autorenew</span>
                    <span>Redraw</span>
                </button>
            </div>

            {{-- Multi Mode Stage --}}
            <div id="multiModeStage" style="display:none; width:100%;">
                <div id="slotsGrid" class="grid-slots"></div>
            </div>
        </div>

        <div class="bottom-bar">
            <div>
                <div id="recentWinners" style="display:none"></div>
                <div class="eligible-count" id="eligibleCount"></div>
            </div>
            <div id="btnArea">
                <button class="draw-btn start" id="drawBtn" disabled onclick="handleDrawBtn()">
                    <span class="material-symbols-outlined">casino</span>
                    <span id="btnLabel">Start</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Session Selection Modal --}}
    <div id="sessionModal" class="modal-overlay" style="display:none" onclick="closeSessionModalOnOverlay(event)">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Select Drawing Session</h2>
                <button class="modal-close-btn" onclick="closeSessionModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body" id="sessionModalList">
                <!-- Session items will be dynamically generated here -->
            </div>
        </div>
    </div>

<script>
// ─── Data ───
const DRAW_URL = @json(route('events.doorprize.draw', $event->slug));
const DRAW_SESSION_URL = @json(route('events.doorprize.draw-session', $event->slug));
const REDRAW_URL = @json(route('events.doorprize.redraw', $event->slug));
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let sessions = {!! $sessionsJson !!};
let allParticipants = {!! $eligibleNamesJson !!};
let globalBannedIds = @json($event->settings['doorprize_global_banned_ids'] ?? []);
let globalWonIds = @json($globalWonIds);
let eligibleNames = [];
let lastWinnerRecordId = null;

let selectedSessionId = null;
let selectedPrizeId = null;
let currentPrize = null;
let currentSession = null;
let isMultiMode = false;

// ─── States ───
let state = 'idle'; // idle | rolling | stopping | revealed | cooldown
let rollerAnim = null;
let rollerPos = 0;
let rollerSpeed = 0;
const ITEM_H = 72;
let multiModeIntervals = [];
let cooldownTimer = null;
let recentWinnersList = [];

// ─── Init ───
(function init() {
    // Restore session from localStorage if exists
    const stored = localStorage.getItem('selectedSessionId_' + @json($event->slug));
    if (stored) {
        selectedSessionId = parseInt(stored);
        currentSession = sessions.find(s => s.id === selectedSessionId) || null;
        if (currentSession) {
            isMultiMode = checkIsMultiMode(currentSession);
            if (!isMultiMode && currentSession.prizes.length > 0) {
                const p = currentSession.prizes[0];
                selectedPrizeId = p.id;
                currentPrize = p;
            }
        } else {
            selectedSessionId = null;
        }
    }

    // Filter eligibleNames immediately
    eligibleNames = getFilteredEligibleNames();

    updateEligibleCount();

    // Collect existing winners
    sessions.forEach(s => {
        s.prizes.forEach(p => {
            p.winners.forEach(w => {
                recentWinnersList.push({ name: w.name, prize: p.name });
            });
        });
    });
    renderRecentWinners();
    
    // Build modal list
    buildSessionModalList();
    
    // Initial UI update
    updateUI();
})();

function checkIsMultiMode(session) {
    if (!session) return false;
    if (session.prizes.length > 1) return true;
    if (session.prizes.length === 1 && session.prizes[0].max_winners > 1) return true;
    return false;
}

function getFilteredEligibleNames() {
    if (!currentSession) return [];

    return allParticipants.filter(p => {
        // 1. Exclude if globally banned
        if (globalBannedIds.includes(p.id)) return false;

        // 2. Exclude if already won (anti-double winner)
        if (globalWonIds.includes(p.id)) return false;

        // 3. Exclude if session-specific banned
        if (currentSession.banned_ids && currentSession.banned_ids.includes(p.id)) return false;

        // 4. Require check-in
        if (currentSession.require_checkin && !p.check_in) return false;

        // 5. Require feedback
        if (currentSession.require_feedback && !p.feedback_submitted) return false;

        return true;
    });
}

// ─── Fullscreen ───
function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            console.error(`Error attempting to enable fullscreen: ${err.message}`);
        });
    } else {
        document.exitFullscreen();
    }
}

document.addEventListener('fullscreenchange', () => {
    const icon = document.getElementById('fsIcon');
    if (document.fullscreenElement) {
        icon.textContent = 'fullscreen_exit';
    } else {
        icon.textContent = 'fullscreen';
    }
});

// ─── Modal drawing session selection ───
function openSessionModal() {
    buildSessionModalList();
    document.getElementById('sessionModal').style.display = 'flex';
}

function closeSessionModal() {
    document.getElementById('sessionModal').style.display = 'none';
}

function closeSessionModalOnOverlay(event) {
    if (event.target === document.getElementById('sessionModal')) {
        closeSessionModal();
    }
}

function buildSessionModalList() {
    const listContainer = document.getElementById('sessionModalList');
    listContainer.innerHTML = '';
    
    sessions.forEach(s => {
        const item = document.createElement('div');
        item.className = 'session-modal-item' + (s.id === selectedSessionId ? ' active' : '');
        item.onclick = () => selectSession(s.id);
        
        const totalRemaining = s.prizes.reduce((sum, p) => sum + p.remaining, 0);
        
        item.innerHTML = `
            <div class="session-info">
                <div class="session-name">${escHtml(s.name)}</div>
                <div class="session-meta">${s.prizes.length} prizes • ${totalRemaining} slots left</div>
            </div>
            <div class="status-dot"></div>
        `;
        listContainer.appendChild(item);
    });
}

function selectSession(sessionId) {
    selectedSessionId = sessionId;
    localStorage.setItem('selectedSessionId_' + @json($event->slug), sessionId);
    
    currentSession = sessions.find(s => s.id === selectedSessionId) || null;
    
    if (currentSession) {
        isMultiMode = checkIsMultiMode(currentSession);
        if (!isMultiMode && currentSession.prizes.length > 0) {
            const p = currentSession.prizes[0];
            selectedPrizeId = p.id;
            currentPrize = p;
        } else {
            selectedPrizeId = null;
            currentPrize = null;
        }
    } else {
        selectedPrizeId = null;
        currentPrize = null;
        isMultiMode = false;
    }

    eligibleNames = getFilteredEligibleNames();

    closeSessionModal();
    updateUI();
}

function onSessionChange() {
    // Deprecated, replaced by selectSession(sessionId)
}

function onPrizeChange() {
    // Deprecated
}

function updateUI() {
    const idle = document.getElementById('idleState');
    const roller = document.getElementById('rollerContainer');
    const reveal = document.getElementById('winnerReveal');
    const btn = document.getElementById('drawBtn');
    const info = document.getElementById('prizeInfo');
    const multiStage = document.getElementById('multiModeStage');

    // Reset visibility states
    idle.style.display = 'none';
    roller.style.display = 'none';
    reveal.classList.remove('visible');
    info.style.display = 'none';
    multiStage.style.display = 'none';
    btn.disabled = true;

    if (!currentSession) {
        idle.style.display = '';
        state = 'idle';
        return;
    }

    if (isMultiMode) {
        // Multi Mode
        multiStage.style.display = '';
        buildMultiModeSlots();

        const totalRemaining = currentSession.prizes.reduce((sum, p) => sum + p.remaining, 0);
        if (totalRemaining <= 0) {
            btn.disabled = true;
            btn.className = 'draw-btn start';
            document.getElementById('btnLabel').textContent = 'No Slots';
            state = 'idle';
        } else {
            btn.disabled = false;
            btn.className = 'draw-btn start';
            document.getElementById('btnLabel').textContent = 'Start';
            state = 'ready';
        }
    } else {
        // Single Mode
        if (!currentPrize) {
            idle.style.display = '';
            state = 'idle';
            return;
        }

        document.getElementById('prizeNameDisplay').textContent = currentPrize.name;
        document.getElementById('prizeRemaining').textContent = currentPrize.remaining;
        document.getElementById('prizeTotal').textContent = currentPrize.max_winners;
        
        const prizeImgContainer = document.getElementById('prizeImageContainer');
        const prizeImg = document.getElementById('prizeImage');
        if (currentPrize.image) {
            prizeImg.src = currentPrize.image;
            prizeImgContainer.style.display = 'block';
        } else {
            prizeImgContainer.style.display = 'none';
            prizeImg.src = '';
        }

        info.style.display = '';

        const activeWinners = currentPrize.winners ? currentPrize.winners.filter(w => w.status !== 'redraw') : [];

        if (currentPrize.remaining <= 0) {
            btn.disabled = true;
            btn.className = 'draw-btn start';
            document.getElementById('btnLabel').textContent = 'No Slots';
            state = 'idle';

            if (activeWinners.length > 0) {
                const lastW = activeWinners[activeWinners.length - 1];
                lastWinnerRecordId = lastW.id;
                showWinnerCardOnly(lastW, currentPrize.name);
            } else {
                roller.style.display = 'none';
                reveal.classList.remove('visible');
            }
        } else {
            roller.style.display = '';
            btn.disabled = false;
            btn.className = 'draw-btn start';
            document.getElementById('btnLabel').textContent = 'Start';
            state = 'ready';
            reveal.classList.remove('visible');
            buildRoller();
        }
    }
    updateEligibleCount();
}

function showWinnerCardOnly(winner, prizeName) {
    document.getElementById('winnerName').textContent = winner.name;
    document.getElementById('winnerOrg').textContent = winner.organization || '';
    document.getElementById('winnerPrize').textContent = '🎁 ' + prizeName;
    
    const winnerPrizeImgContainer = document.getElementById('winnerPrizeImageContainer');
    const winnerPrizeImg = document.getElementById('winnerPrizeImage');
    if (currentPrize && currentPrize.image) {
        winnerPrizeImg.src = currentPrize.image;
        winnerPrizeImgContainer.style.display = 'block';
    } else {
        winnerPrizeImgContainer.style.display = 'none';
        winnerPrizeImg.src = '';
    }

    document.getElementById('winnerReveal').classList.add('visible');
    document.getElementById('rollerContainer').style.display = 'none';
}

function updateEligibleCount() {
    document.getElementById('eligibleCount').textContent =
        `Eligible Users: ${eligibleNames.length}`;
}

// ─── Roller (Single Mode) ───
function buildRoller() {
    const track = document.getElementById('rollerTrack');
    track.innerHTML = '';
    if (eligibleNames.length === 0) return;

    const needed = Math.max(80, eligibleNames.length * 3);
    for (let i = 0; i < needed; i++) {
        const data = eligibleNames[i % eligibleNames.length];
        const div = document.createElement('div');
        div.className = 'roller-item';
        div.innerHTML = `<div class="rname">${escHtml(data.name)}</div><div class="rorg">${escHtml(data.organization)}</div>`;
        track.appendChild(div);
    }
    rollerPos = 0;
    track.style.transform = `translateY(0px)`;
    highlightCenter();
}

function highlightCenter() {
    const track = document.getElementById('rollerTrack');
    const items = track.children;
    const containerH = 340;
    const centerY = containerH / 2;
    for (let i = 0; i < items.length; i++) {
        const itemTop = i * ITEM_H + rollerPos;
        const itemCenter = itemTop + ITEM_H / 2;
        if (Math.abs(itemCenter - centerY) < ITEM_H / 2) {
            items[i].classList.add('active');
        } else {
            items[i].classList.remove('active');
        }
    }
}

// ─── Slots (Multi Mode) ───
function buildMultiModeSlots() {
    const grid = document.getElementById('slotsGrid');
    grid.innerHTML = '';
    
    if (!currentSession || !currentSession.prizes) return;

    currentSession.prizes.forEach(p => {
        const card = document.createElement('div');
        card.className = 'slot-card';
        
        const activeWinners = p.winners ? p.winners.filter(w => w.status !== 'redraw') : [];
        const emptyCount = Math.max(0, p.max_winners - activeWinners.length);
        
        let slotsHtml = '';
        
        // 1. Render active winners
        activeWinners.forEach(w => {
            slotsHtml += `
                <div class="slot-item winner-drawn" data-winner-id="${w.id}">
                    <button class="slot-redraw-btn" onclick="handleRedrawSlot(this)">
                        <span class="material-symbols-outlined" style="font-size:14px">autorenew</span>
                        <span>Redraw</span>
                    </button>
                    <span class="material-symbols-outlined trophy-icon">emoji_events</span>
                    <div class="winner-name">${escHtml(w.name)}</div>
                    <div class="winner-company">${escHtml(w.organization || '')}</div>
                </div>
            `;
        });
        
        // 2. Render empty slots
        for (let i = 0; i < emptyCount; i++) {
            slotsHtml += `
                <div class="slot-item">
                    <button class="slot-redraw-btn" onclick="handleRedrawSlot(this)">
                        <span class="material-symbols-outlined" style="font-size:14px">autorenew</span>
                        <span>Redraw</span>
                    </button>
                    <span class="material-symbols-outlined trophy-icon">emoji_events</span>
                    <div class="winner-name">???</div>
                    <div class="winner-company">Ready to draw</div>
                </div>
            `;
        }

        const prizeImgHtml = p.image 
            ? `<div style="width: 100%; height: 80px; margin-bottom: 8px; overflow: hidden;"><img src="${p.image}" style="width: 100%; height: 100%; object-fit: contain;" /></div>` 
            : '';

        card.innerHTML = `
            ${prizeImgHtml}
            <div class="prize-tag">🎁 ${escHtml(p.name)}</div>
            <div class="card-slots-container">
                ${slotsHtml}
            </div>
        `;
        grid.appendChild(card);
    });
}

function startMultiModeRolling() {
    state = 'rolling';
    const btn = document.getElementById('drawBtn');
    btn.className = 'draw-btn stop';
    document.getElementById('btnLabel').textContent = 'Stop';

    const items = document.querySelectorAll('.slot-item:not(.winner-drawn)');
    items.forEach(item => {
        item.classList.add('rolling');
    });

    multiModeIntervals = [];
    items.forEach((item, index) => {
        const nameEl = item.querySelector('.winner-name');
        const companyEl = item.querySelector('.winner-company');

        const intervalId = setInterval(() => {
            if (eligibleNames.length > 0) {
                const randomUser = eligibleNames[Math.floor(Math.random() * eligibleNames.length)];
                nameEl.textContent = randomUser.name;
                companyEl.textContent = randomUser.organization || '';
            }
        }, 60 + (index * 10)); // visually offsets the roll of different slots
        multiModeIntervals.push(intervalId);
    });
}

async function stopMultiModeRolling() {
    state = 'stopping';
    const btn = document.getElementById('drawBtn');
    btn.disabled = true;
    document.getElementById('btnLabel').textContent = '...';

    let result;
    try {
        const resp = await fetch(DRAW_SESSION_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ session_id: selectedSessionId }),
        });
        result = await resp.json();
        if (result.error) { alert(result.error); resetToReady(); return; }
    } catch (e) { alert('Network error'); resetToReady(); return; }

    // Clear shuffle intervals
    multiModeIntervals.forEach(id => clearInterval(id));
    multiModeIntervals = [];

    const items = document.querySelectorAll('.slot-item.rolling');
    const winners = result.winners;

    // Sequential reveal animation of the winners
    for (let i = 0; i < items.length; i++) {
        const item = items[i];
        const winner = winners[i];

        if (winner) {
            await new Promise(resolve => setTimeout(resolve, 200)); // 200ms delay per slot reveal

            item.classList.remove('rolling');
            item.classList.add('winner-drawn');
            item.setAttribute('data-winner-id', winner.id);
            item.querySelector('.winner-name').textContent = winner.name;
            item.querySelector('.winner-company').textContent = winner.organization || '';

            // Confetti burst targeted to slot item location
            const rect = item.getBoundingClientRect();
            confetti({
                particleCount: 40,
                spread: 50,
                origin: {
                    x: (rect.left + rect.width / 2) / window.innerWidth,
                    y: (rect.top + rect.height / 2) / window.innerHeight
                }
            });

            recentWinnersList.unshift({ name: winner.name, prize: winner.prize_name });
        } else {
            item.classList.remove('rolling');
            item.querySelector('.winner-name').textContent = 'Empty';
            item.querySelector('.winner-company').textContent = 'No eligible participants left';
        }
    }

    // Grand final confetti
    setTimeout(() => {
        confetti({
            particleCount: 150,
            spread: 120,
            origin: { y: 0.6 }
        });
    }, 200);

    allParticipants = result.eligibleNames;
    if (result.winners) {
        result.winners.forEach(w => {
            if (w.registration_id && !globalWonIds.includes(w.registration_id)) {
                globalWonIds.push(w.registration_id);
            }
        });
    }
    eligibleNames = getFilteredEligibleNames();
    
    // Update currentSession prizes remaining counters
    if (currentSession) {
        currentSession.prizes.forEach(p => {
            const updatedP = result.prizes.find(up => up.id === p.id);
            if (updatedP) {
                p.remaining = updatedP.remaining;
                p.winners_count = updatedP.winners_count;
                p.winners = updatedP.winners || [];
            }
        });
    }

    renderRecentWinners();
    updateEligibleCount();

    state = 'revealed';
    startCooldown(5);
}

// ─── Draw Controls ───
function handleDrawBtn() {
    if (state === 'ready' || state === 'idle') {
        if (isMultiMode) {
            startMultiModeRolling();
        } else {
            startRolling();
        }
    } else if (state === 'rolling') {
        if (isMultiMode) {
            stopMultiModeRolling();
        } else {
            stopRolling();
        }
    }
}

function startRolling() {
    state = 'rolling';
    const btn = document.getElementById('drawBtn');
    btn.className = 'draw-btn stop';
    document.getElementById('btnLabel').textContent = 'Stop';

    document.getElementById('winnerReveal').classList.remove('visible');
    document.getElementById('rollerContainer').style.display = '';

    rollerSpeed = 18;
    rollerPos = 0;

    function animate() {
        if (state !== 'rolling' && state !== 'stopping') return;
        const track = document.getElementById('rollerTrack');
        const totalH = track.children.length * ITEM_H;

        rollerPos -= rollerSpeed;
        if (Math.abs(rollerPos) >= totalH / 2) rollerPos = 0;

        track.style.transform = `translateY(${rollerPos}px)`;
        highlightCenter();
        rollerAnim = requestAnimationFrame(animate);
    }
    rollerAnim = requestAnimationFrame(animate);
}

async function stopRolling() {
    state = 'stopping';
    const btn = document.getElementById('drawBtn');
    btn.disabled = true;
    document.getElementById('btnLabel').textContent = '...';

    // Fire AJAX and deceleration in parallel so stop feels instant
    const fetchPromise = fetch(DRAW_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ session_id: selectedSessionId, prize_id: selectedPrizeId }),
    }).then(r => r.json());

    const deceleratePromise = decelerate();

    // Wait for both to finish
    let result;
    try {
        const [fetchResult] = await Promise.all([fetchPromise, deceleratePromise]);
        result = fetchResult;
        if (result.error) { alert(result.error); resetToReady(); return; }
    } catch (e) { alert('Network error'); resetToReady(); return; }

    allParticipants = result.eligibleNames;
    if (result.winner && result.winner.registration_id) {
        lastWinnerRecordId = result.winner.id;
        if (!globalWonIds.includes(result.winner.registration_id)) {
            globalWonIds.push(result.winner.registration_id);
        }
    }
    eligibleNames = getFilteredEligibleNames();

    if (currentPrize && result.prizes) {
        const updatedP = result.prizes.find(up => up.id === currentPrize.id);
        if (updatedP) {
            currentPrize.remaining = updatedP.remaining;
            currentPrize.winners_count = updatedP.winners_count;
            currentPrize.winners = updatedP.winners || [];
            document.getElementById('prizeRemaining').textContent = updatedP.remaining;
        }
    }
    if (currentSession && result.prizes) {
        currentSession.prizes.forEach(p => {
            const updatedP = result.prizes.find(up => up.id === p.id);
            if (updatedP) {
                p.remaining = updatedP.remaining;
                p.winners_count = updatedP.winners_count;
                p.winners = updatedP.winners || [];
            }
        });
    }

    cancelAnimationFrame(rollerAnim);
    document.getElementById('rollerContainer').style.display = 'none';
    showWinner(result.winner, result.prize.name);

    recentWinnersList.unshift({ name: result.winner.name, prize: result.prize.name });
    renderRecentWinners();
    updateEligibleCount();

    state = 'revealed';
    startCooldown(5);
}

function decelerate() {
    return new Promise(resolve => {
        let speed = rollerSpeed;
        const decayRate = 0.88; // Faster deceleration for snappier stop
        const track = document.getElementById('rollerTrack');
        const totalH = track.children.length * ITEM_H;

        function frame() {
            speed *= decayRate;
            rollerPos -= speed;
            if (Math.abs(rollerPos) >= totalH / 2) rollerPos = 0;
            track.style.transform = `translateY(${rollerPos}px)`;
            highlightCenter();

            if (speed > 0.5) {
                requestAnimationFrame(frame);
            } else {
                resolve();
            }
        }
        requestAnimationFrame(frame);
    });
}

function showWinner(winner, prizeName) {
    state = 'revealed';
    document.getElementById('winnerName').textContent = winner.name;
    document.getElementById('winnerOrg').textContent = winner.organization || '';
    document.getElementById('winnerPrize').textContent = '🎁 ' + prizeName;

    const winnerPrizeImgContainer = document.getElementById('winnerPrizeImageContainer');
    const winnerPrizeImg = document.getElementById('winnerPrizeImage');
    if (currentPrize && currentPrize.image) {
        winnerPrizeImg.src = currentPrize.image;
        winnerPrizeImgContainer.style.display = 'block';
    } else {
        winnerPrizeImgContainer.style.display = 'none';
        winnerPrizeImg.src = '';
    }

    document.getElementById('winnerReveal').classList.add('visible');

    const count = 200;
    const defaults = { origin: { y: 0.6 }, zIndex: 9999 };
    function fire(particleRatio, opts) {
        confetti({ ...defaults, particleCount: Math.floor(count * particleRatio), ...opts });
    }
    fire(0.25, { spread: 26, startVelocity: 55 });
    fire(0.2, { spread: 60 });
    fire(0.35, { spread: 100, decay: 0.91, scalar: 0.8 });
    fire(0.1, { spread: 120, startVelocity: 25, decay: 0.92, scalar: 1.2 });
    fire(0.1, { spread: 120, startVelocity: 45 });
}

function startCooldown(seconds) {
    let remaining = seconds;
    const btn = document.getElementById('drawBtn');
    btn.className = 'draw-btn start';
    btn.disabled = true;
    document.getElementById('btnLabel').innerHTML = `<span class="countdown">${remaining}s</span>`;

    cooldownTimer = setInterval(() => {
        remaining--;
        if (remaining <= 0) {
            clearInterval(cooldownTimer);
            resetToReady();
        } else {
            document.getElementById('btnLabel').innerHTML = `<span class="countdown">${remaining}s</span>`;
        }
    }, 1000);
}

function resetToReady() {
    cancelAnimationFrame(rollerAnim);
    multiModeIntervals.forEach(id => clearInterval(id));
    multiModeIntervals = [];

    const btn = document.getElementById('drawBtn');

    if (isMultiMode) {
        const totalRemaining = currentSession ? currentSession.prizes.reduce((sum, p) => sum + p.remaining, 0) : 0;
        if (totalRemaining <= 0) {
            btn.disabled = true;
            btn.className = 'draw-btn start';
            document.getElementById('btnLabel').textContent = 'No Slots';
            state = 'idle';
            return;
        }

        btn.disabled = false;
        btn.className = 'draw-btn start';
        document.getElementById('btnLabel').textContent = 'Start';
        state = 'ready';
        buildMultiModeSlots();
    } else {
        if (currentPrize && currentPrize.remaining <= 0) {
            btn.disabled = true;
            btn.className = 'draw-btn start';
            document.getElementById('btnLabel').textContent = 'No Slots';
            state = 'idle';
            document.getElementById('rollerContainer').style.display = 'none';
            return;
        }

        btn.disabled = false;
        btn.className = 'draw-btn start';
        document.getElementById('btnLabel').textContent = 'Start';
        state = 'ready';

        document.getElementById('winnerReveal').classList.remove('visible');
        document.getElementById('rollerContainer').style.display = '';
        buildRoller();
    }
}

function renderRecentWinners() {
    const el = document.getElementById('recentWinners');
    if (recentWinnersList.length === 0) {
        el.innerHTML = '<span class="label">Recent Winners:</span><span style="font-size:12px;color:rgba(255,255,255,.25);font-weight:500">No winners yet</span>';
        return;
    }
    let html = '<span class="label">Recent Winners:</span>';
    recentWinnersList.slice(0, 6).forEach(w => {
        html += `<div class="winner-chip"><span class="material-symbols-outlined">trophy</span>${escHtml(w.name)}</div>`;
    });
    if (recentWinnersList.length > 6) html += `<span style="font-size:11px;color:rgba(255,255,255,.3)">+${recentWinnersList.length - 6} more</span>`;
    el.innerHTML = html;
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

async function handleRedrawSingle() {
    if (!lastWinnerRecordId) return;

    if (!confirm('Redraw this winner?')) return;

    try {
        const resp = await fetch(REDRAW_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ winner_id: lastWinnerRecordId }),
        });
        const res = await resp.json();
        if (res.error) { alert(res.error); return; }

        // Update local data
        allParticipants = res.eligibleNames;
        globalWonIds = res.globalWonIds || [];
        eligibleNames = getFilteredEligibleNames();

        if (currentSession) {
            currentSession.prizes.forEach(p => {
                const updatedP = res.prizes.find(up => up.id === p.id);
                if (updatedP) {
                    p.remaining = updatedP.remaining;
                    p.winners_count = updatedP.winners_count;
                    p.winners = updatedP.winners || [];
                }
            });
        }
        if (currentPrize) {
            const updatedP = res.prizes.find(up => up.id === currentPrize.id);
            if (updatedP) {
                currentPrize.remaining = updatedP.remaining;
                currentPrize.winners_count = updatedP.winners_count;
                currentPrize.winners = updatedP.winners || [];
            }
        }

        updateEligibleCount();
        lastWinnerRecordId = null;

        // Hide winner reveal, show idle / ready to draw
        document.getElementById('winnerReveal').classList.remove('visible');
        
        // Reset state
        resetToReady();
    } catch (e) {
        alert('Network error');
    }
}

async function handleRedrawSlot(btn) {
    const item = btn.closest('.slot-item');
    const winnerId = item.getAttribute('data-winner-id');
    if (!winnerId) return;

    if (!confirm('Redraw this slot winner?')) return;

    try {
        const resp = await fetch(REDRAW_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ winner_id: winnerId }),
        });
        const res = await resp.json();
        if (res.error) { alert(res.error); return; }

        // Update local data
        allParticipants = res.eligibleNames;
        globalWonIds = res.globalWonIds || [];
        eligibleNames = getFilteredEligibleNames();

        if (currentSession) {
            currentSession.prizes.forEach(p => {
                const updatedP = res.prizes.find(up => up.id === p.id);
                if (updatedP) {
                    p.remaining = updatedP.remaining;
                    p.winners_count = updatedP.winners_count;
                    p.winners = updatedP.winners || [];
                }
            });
        }

        updateEligibleCount();

        // Reset this slot item to empty
        item.classList.remove('winner-drawn');
        item.removeAttribute('data-winner-id');
        item.querySelector('.winner-name').textContent = '???';
        item.querySelector('.winner-company').textContent = 'Ready to draw';

        // Check if there are any remaining unfilled slots
        const unfilled = document.querySelectorAll('.slot-item:not(.winner-drawn)').length;
        if (unfilled > 0) {
            state = 'ready';
            const drawBtn = document.getElementById('drawBtn');
            drawBtn.className = 'draw-btn start';
            drawBtn.disabled = false;
            document.getElementById('btnLabel').textContent = 'Start';
        }
    } catch (e) {
        alert('Network error');
    }
}
</script>
</body>
</html>
