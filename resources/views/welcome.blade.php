<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Anak Kecik | Photobooth</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&family=Mea+Culpa&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        maroon: {
                            50: '#fdf3f4',
                            100: '#fce6ea',
                            200: '#f8cdd6',
                            300: '#f1a6b7',
                            400: '#e7738e',
                            500: '#d74465',
                            600: '#bf2c4b',
                            700: '#a1203b',
                            800: '#851d34',
                            900: '#721b2f',
                            950: '#410b17', 
                        }
                    },
                    fontFamily: {
                        serif: ['Playfair Display', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                        cursive: ['Mea Culpa', 'cursive'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 1s ease-out forwards',
                        'slide-up': 'slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'print': 'printOut 3.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        printOut: {
                            '0%': { transform: 'translateY(-100%)', opacity: '0.8', boxShadow: 'none' },
                            '50%': { transform: 'translateY(-30%)', opacity: '0.9', boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)' },
                            '100%': { transform: 'translateY(0)', opacity: '1', boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #fcfcfc;
            color: #1a1a1a;
            overflow-x: hidden;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .screen { display: none; opacity: 0; transition: opacity 0.5s ease; }
        .screen.active { display: flex; opacity: 1; min-height: 100vh; }

        .glass-dark {
            background: rgba(43, 6, 14, 0.5);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        #flash {
            position: fixed; inset: 0; background: white; z-index: 9999; opacity: 0; pointer-events: none; transition: opacity 0.05s ease-out;
        }
        .flashing { opacity: 1 !important; transition: none !important; }

        .polaroid-frame {
            /* Handled dynamically by canvas now, but we keep a tiny border for the web preview */
            padding: 2px;
            transition: background-color 0.3s ease;
        }

        /* --- Expanded Video Filters --- */
        .filter-normal { filter: none; }
        .filter-bw { filter: grayscale(100%) contrast(1.1); }
        .filter-sepia { filter: sepia(0.8) contrast(1.1) brightness(0.9); }
        .filter-vintage { filter: sepia(0.3) saturate(1.4) contrast(1.1) hue-rotate(-10deg); }
        .filter-retro { filter: contrast(1.2) saturate(1.5) sepia(0.2) hue-rotate(10deg); }
        .filter-cool { filter: saturate(0.8) contrast(1.1) hue-rotate(15deg) brightness(0.95); }
        .filter-warm { filter: sepia(0.4) saturate(1.2) contrast(1.05) hue-rotate(-5deg); }
        .filter-faded { filter: contrast(0.8) saturate(0.6) brightness(1.1); }

        /* Custom UI Scrollbar for Layout Menus (Mobile) */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }
        
        /* Layout/Config icons */
        .layout-btn { flex: 0 0 auto; width: 80px; }
        .layout-btn.selected { border-color: #fff; background: rgba(255,255,255,0.1); }
        .filter-btn { flex: 0 0 auto; width: 60px; height: 60px; }
        .color-btn { flex: 0 0 auto; }
        
        .color-btn.selected { border: 2px solid white; outline: 2px solid #aaa; }
        .filter-btn.selected { border-color: white; transform: scale(1.05); }
        .timer-btn.selected { background: white; color: #410b17; font-weight: bold; border-color: white; }

        input::placeholder { color: rgba(255, 255, 255, 0.4); }

        /* Disable horizontal scroll caused by backgrounds */
        html, body { overflow-x: hidden; }

        /* Printer Slot Styling */
        #printer-slot-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; height: 80px;
            background: linear-gradient(to bottom, #111 0%, rgba(17,17,17,0.95) 60%, transparent 100%);
            z-index: 40;
            pointer-events: none;
            display: flex;
            justify-content: center;
        }
        #printer-slot-line {
            width: 600px;
            max-width: 90%;
            height: 4px;
            background: #000;
            margin-top: 10px;
            border-radius: 4px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.8), 0 1px 0 rgba(255,255,255,0.1);
        }

    </style>
</head>
<body class="antialiased font-sans flex flex-col items-center justify-center relative selection:bg-maroon-300 selection:text-maroon-900">

    <div id="flash"></div>

    <!-- Background Elements for dark screens -->
    <div id="dark-bg-elements" class="fixed inset-0 pointer-events-none hidden z-0 bg-maroon-950">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(114,27,47,0.4),rgba(43,6,14,1))]"></div>
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-maroon-600/10 blur-[100px] rounded-full"></div>
        <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-maroon-800/10 blur-[120px] rounded-full"></div>
    </div>

    <!-- ==================== SCREEN 1: WELCOME ==================== -->
    <main id="screen-welcome" class="screen active flex-col items-center justify-center w-full px-4 md:px-6 bg-[#fcfcfc] z-10 relative">
        <div class="fixed top-0 left-0 w-full h-1.5 bg-gradient-to-r from-maroon-600 via-maroon-800 to-maroon-950"></div>
        
        <div class="text-center w-full max-w-2xl mx-auto flex flex-col items-center animate-slide-up">
            <h4 class="text-maroon-800 tracking-[0.2em] text-[10px] md:text-sm uppercase font-semibold mb-6">Welcome to</h4>
            
            <div class="mb-8 relative pt-4 text-center flex flex-col items-center w-full">
                <span class="absolute -top-10 -left-6 md:-left-10 text-[6rem] md:text-[8rem] text-maroon-100 opacity-50 font-serif select-none hidden md:block">A</span>
                <span class="absolute -top-10 -right-2 md:-right-4 text-[6rem] md:text-[8rem] text-maroon-100 opacity-50 font-serif select-none hidden md:block">K</span>
                <h1 class="text-4xl sm:text-5xl md:text-[5.5rem] font-serif text-maroon-950 leading-tight relative z-10 tracking-tight">Anak Kecik</h1>
                <h2 class="text-3xl md:text-5xl font-cursive text-maroon-600 mt-0 relative z-10 tracking-widest transform -rotate-2">Photobooth</h2>
            </div>
            
            <p class="text-sm md:text-lg text-gray-600 font-light mb-12 max-w-sm mx-auto leading-relaxed">
                Step in, arrange your layout, and capture your finest moments.
            </p>

            <button id="btn-enter" class="group relative px-10 py-4 bg-maroon-900 text-white overflow-hidden rounded-sm transition-all hover:bg-maroon-950 shadow-xl hover:shadow-2xl hover:-translate-y-1">
                <span class="relative z-10 flex items-center font-medium tracking-widest text-[11px] md:text-sm uppercase">
                    Begin Setup
                    <svg class="w-4 h-4 ml-3 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </span>
            </button>
        </div>
    </main>

    <!-- ==================== SCREEN 2: SETUP & CAPTURE ==================== -->
    <main id="screen-capture" class="screen flex-col items-center justify-center w-full px-2 md:px-6 py-2 md:py-8 text-white z-10 relative">
        <div class="absolute top-6 left-6 hidden md:block z-50">
            <p class="font-serif text-maroon-200/50 text-xl italic pointer-events-none">A & K.</p>
        </div>

        <div class="w-full max-w-[1400px] mx-auto flex flex-col lg:flex-row gap-4 lg:gap-8 items-center justify-center h-full">
            
            <!-- Left: Robust Controls Panel -->
            <div class="w-full lg:w-[400px] xl:w-[450px] flex flex-col order-2 lg:order-1 h-[55vh] md:h-full lg:h-[85vh] pb-2 md:pb-0">
                <div class="glass-dark p-4 md:p-6 rounded-md md:shadow-2xl relative overflow-y-auto w-full h-full flex flex-col custom-scroll">
                    
                    <div class="flex items-center justify-between mb-4 md:mb-6 pb-2 border-b border-white/10 sticky top-0 bg-maroon-950/90 backdrop-blur-md z-10 -mt-4 md:-mt-6 pt-4 md:pt-6">
                        <h3 class="text-[10px] md:text-xs uppercase tracking-[0.2em] text-maroon-200 font-semibold">Booth Settings</h3>
                        <span class="text-[9px] md:text-[10px] text-white/50 uppercase tracking-widest"><span id="total-shots-preview">1</span> Shot</span>
                    </div>
                    
                    <div class="flex-1 flex flex-col gap-6 md:gap-8 overflow-y-auto pr-1 md:pr-2 pb-6">
                        
                        <!-- Format / Layout (Horizontal on Mobile) -->
                        <div>
                            <p class="text-xs md:text-sm font-medium mb-2 md:mb-3 text-gray-200">1. Select Layout</p>
                            <div class="flex flex-row lg:grid lg:grid-cols-4 gap-2 overflow-x-auto snap-x pb-2">
                                <!-- Single -->
                                <button data-layout="single" class="layout-btn snap-start selected border border-white/20 hover:border-white/50 rounded-sm p-3 flex flex-col items-center gap-2 transition-all">
                                    <div class="w-8 h-10 bg-white/10 border border-white/30 flex items-center justify-center p-1"><div class="w-full h-full bg-white/20"></div></div>
                                    <span class="text-[9px] uppercase tracking-wider text-gray-300 font-medium">Single</span>
                                </button>
                                <!-- Strip (1x3) -->
                                <button data-layout="strip_3" class="layout-btn snap-start border border-white/20 hover:border-white/50 rounded-sm p-3 flex flex-col items-center gap-2 transition-all">
                                    <div class="w-6 h-12 bg-white/10 border border-white/30 p-0.5 flex flex-col gap-0.5"><div class="w-full h-full bg-white/20"></div><div class="w-full h-full bg-white/20"></div><div class="w-full h-full bg-white/20"></div></div>
                                    <span class="text-[9px] uppercase tracking-wider text-gray-300 font-medium">1x3 Strip</span>
                                </button>
                                <!-- Strip (1x4) -->
                                <button data-layout="strip_4" class="layout-btn snap-start border border-white/20 hover:border-white/50 rounded-sm p-3 flex flex-col items-center gap-2 transition-all">
                                    <div class="w-5 h-12 bg-white/10 border border-white/30 p-0.5 flex flex-col gap-[1px]"><div class="w-full h-full bg-white/20"></div><div class="w-full h-full bg-white/20"></div><div class="w-full h-full bg-white/20"></div><div class="w-full h-full bg-white/20"></div></div>
                                    <span class="text-[9px] uppercase tracking-wider text-gray-300 font-medium">1x4 Strip</span>
                                </button>
                                <!-- Grid (2x2) -->
                                <button data-layout="grid" class="layout-btn snap-start border border-white/20 hover:border-white/50 rounded-sm p-3 flex flex-col items-center gap-2 transition-all">
                                    <div class="w-10 h-10 bg-white/10 border border-white/30 p-0.5 grid grid-cols-2 gap-0.5"><div class="bg-white/20"></div><div class="bg-white/20"></div><div class="bg-white/20"></div><div class="bg-white/20"></div></div>
                                    <span class="text-[9px] uppercase tracking-wider text-gray-300 font-medium">2x2 Grid</span>
                                </button>
                            </div>
                        </div>

                        <!-- Timer -->
                        <div>
                            <p class="text-xs md:text-sm font-medium mb-2 md:mb-3 text-gray-200">2. Inter-Shot Timer</p>
                            <div class="flex gap-2">
                                <button data-timer="3" class="timer-btn selected flex-1 py-2 md:py-3 border border-white/20 rounded-sm text-[10px] md:text-xs font-medium uppercase tracking-widest text-gray-300 transition-all">3s Quick</button>
                                <button data-timer="5" class="timer-btn flex-1 py-2 md:py-3 border border-white/20 rounded-sm text-[10px] md:text-xs font-medium uppercase tracking-widest text-gray-300 transition-all">5s Std</button>
                                <button data-timer="7" class="timer-btn flex-1 py-2 md:py-3 border border-white/20 rounded-sm text-[10px] md:text-xs font-medium uppercase tracking-widest text-gray-300 transition-all">7s Pose</button>
                            </div>
                        </div>

                        <!-- Filters (Horizontal on Mobile) -->
                        <div>
                            <p class="text-xs md:text-sm font-medium mb-2 md:mb-3 text-gray-200">3. Artistic Filter</p>
                            <div class="flex flex-row lg:grid lg:grid-cols-4 gap-3 overflow-x-auto snap-x pb-2">
                                <button data-filter="normal" class="filter-btn snap-start rounded-full bg-gray-500 border-2 border-transparent relative overflow-hidden group shrink-0">
                                    <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=150&auto=format&fit=crop" class="w-full h-full object-cover pointer-events-none">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/50 text-[8px] md:text-[9px] uppercase font-bold tracking-wider text-white opacity-0 group-hover:opacity-100 transition-opacity">Norm</span>
                                </button>
                                <button data-filter="vintage" class="filter-btn snap-start rounded-full border-2 border-transparent relative overflow-hidden group shrink-0">
                                    <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=150&auto=format&fit=crop" class="w-full h-full object-cover filter-vintage pointer-events-none">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/50 text-[8px] md:text-[9px] uppercase font-bold tracking-wider text-white opacity-0 group-hover:opacity-100 transition-opacity">Vint</span>
                                </button>
                                <button data-filter="cool" class="filter-btn snap-start rounded-full border-2 border-transparent relative overflow-hidden group shrink-0">
                                    <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=150&auto=format&fit=crop" class="w-full h-full object-cover filter-cool pointer-events-none">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/50 text-[8px] md:text-[9px] uppercase font-bold tracking-wider text-white opacity-0 group-hover:opacity-100 transition-opacity">Cool</span>
                                </button>
                                <button data-filter="warm" class="filter-btn snap-start rounded-full border-2 border-transparent relative overflow-hidden group shrink-0">
                                    <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=150&auto=format&fit=crop" class="w-full h-full object-cover filter-warm pointer-events-none">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/50 text-[8px] md:text-[9px] uppercase font-bold tracking-wider text-white opacity-0 group-hover:opacity-100 transition-opacity">Warm</span>
                                </button>
                                <button data-filter="faded" class="filter-btn snap-start rounded-full border-2 border-transparent relative overflow-hidden group shrink-0">
                                    <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=150&auto=format&fit=crop" class="w-full h-full object-cover filter-faded pointer-events-none">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/50 text-[8px] md:text-[9px] uppercase font-bold tracking-wider text-white opacity-0 group-hover:opacity-100 transition-opacity">Fade</span>
                                </button>
                                <button data-filter="bw" class="filter-btn snap-start rounded-full border-2 border-transparent relative overflow-hidden group shrink-0">
                                    <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=150&auto=format&fit=crop" class="w-full h-full object-cover filter-bw pointer-events-none">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/50 text-[8px] md:text-[9px] uppercase font-bold tracking-wider text-white opacity-0 group-hover:opacity-100 transition-opacity">B&W</span>
                                </button>
                                <button data-filter="sepia" class="filter-btn snap-start rounded-full border-2 border-transparent relative overflow-hidden group shrink-0">
                                    <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=150&auto=format&fit=crop" class="w-full h-full object-cover filter-sepia pointer-events-none">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/50 text-[8px] md:text-[9px] uppercase font-bold tracking-wider text-white opacity-0 group-hover:opacity-100 transition-opacity">Sep</span>
                                </button>
                                <button data-filter="retro" class="filter-btn snap-start rounded-full border-2 border-transparent relative overflow-hidden group shrink-0">
                                    <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=150&auto=format&fit=crop" class="w-full h-full object-cover filter-retro pointer-events-none">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/50 text-[8px] md:text-[9px] uppercase font-bold tracking-wider text-white opacity-0 group-hover:opacity-100 transition-opacity">Retr</span>
                                </button>
                            </div>
                        </div>

                        <!-- Frame Color (Horizontal on Mobile) -->
                        <div>
                            <p class="text-xs md:text-sm font-medium mb-2 md:mb-3 text-gray-200">4. Frame Background</p>
                            <div class="flex flex-row flex-wrap lg:flex-wrap gap-3 overflow-x-auto pb-2">
                                <button data-color="#ffffff" class="color-btn selected w-8 h-8 md:w-10 md:h-10 rounded-full shadow-md bg-white border border-gray-300 shrink-0" title="Classic White"></button>
                                <button data-color="#fdf3f4" class="color-btn w-8 h-8 md:w-10 md:h-10 rounded-full shadow-md bg-[#fdf3f4] border border-[#f8cdd6] shrink-0"></button>
                                <button data-color="#f4e7d3" class="color-btn w-8 h-8 md:w-10 md:h-10 rounded-full shadow-md bg-[#f4e7d3] border border-[#d8c3a5] shrink-0"></button>
                                <button data-color="#721b2f" class="color-btn w-8 h-8 md:w-10 md:h-10 rounded-full shadow-md bg-maroon-900 border border-maroon-950 shrink-0"></button>
                                <button data-color="#333333" class="color-btn w-8 h-8 md:w-10 md:h-10 rounded-full shadow-md bg-[#333] border border-[#111] shrink-0"></button>
                                <button data-color="#111111" class="color-btn w-8 h-8 md:w-10 md:h-10 rounded-full shadow-md bg-[#111] border border-black shrink-0"></button>
                            </div>
                        </div>

                        <!-- Custom Text / Description -->
                        <div>
                            <p class="text-xs md:text-sm font-medium mb-2 md:mb-3 text-gray-200">5. Custom Caption (Optional)</p>
                            <input type="text" id="custom-caption-input" maxlength="35" placeholder="e.g. Best Night Ever!" class="w-full bg-black/20 border border-white/20 rounded-sm text-white px-3 py-2 md:px-4 md:py-3 text-xs md:text-sm focus:outline-none focus:border-white/60 transition-colors font-serif italic text-center placeholder:font-sans placeholder:not-italic">
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="mt-2 md:mt-4 pt-4 border-t border-white/10 shrink-0 sticky bottom-0 bg-maroon-950/95 backdrop-blur-sm -mb-4 md:-mb-6 pb-4 md:pb-6 z-10">
                        <button id="btn-snap" disabled class="w-full py-3 md:py-4 bg-white text-maroon-950 hover:bg-gray-100 disabled:opacity-50 disabled:bg-gray-500 disabled:text-gray-300 font-semibold tracking-widest uppercase text-[10px] md:text-sm rounded-sm transition-all flex items-center justify-center gap-2 shadow-[0_0_15px_rgba(255,255,255,0.1)]">
                            <span id="btn-snap-icon">
                                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </span>
                            <span id="btn-snap-text">Start Capture</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: Viewfinder -->
            <div class="flex-1 flex flex-col justify-center items-center w-full max-w-[700px] order-1 lg:order-2 h-[40vh] md:h-[50vh] lg:h-[85vh]">
                <div id="viewfinder-container" class="relative w-full aspect-[4/3] max-h-full bg-black shadow-2xl overflow-hidden glass-dark p-1.5 md:p-3 pb-6 md:pb-12 rounded-sm flex flex-col transition-all duration-300 border border-white/5 mx-auto">
                    
                    <div class="relative w-full h-full overflow-hidden rounded-sm bg-[#111]">
                        <!-- Video Stream -->
                        <video id="camera-stream" autoplay playsinline class="absolute inset-0 w-full h-full object-cover transform scale-x-[-1] filter-normal transition-[filter] duration-300"></video>
                        
                        <!-- Connecting State -->
                        <div id="cam-status" class="absolute inset-0 flex flex-col items-center justify-center bg-black/80 backdrop-blur-md z-20">
                            <div class="w-6 h-6 md:w-8 md:h-8 border-t-2 border-r-2 border-maroon-400 rounded-full animate-spin mb-3 md:mb-4"></div>
                            <p class="font-serif tracking-widest text-[9px] md:text-sm text-gray-300">INITIALIZING OPTICS...</p>
                        </div>

                        <!-- Flash/Countdown Overlay -->
                        <div id="countdown-overlay" class="absolute inset-0 flex items-center justify-center z-30 hidden bg-black/30 backdrop-blur-[2px]">
                            <span id="countdown-text" class="text-[8rem] sm:text-[12rem] md:text-[16rem] font-serif font-bold text-white drop-shadow-2xl">3</span>
                        </div>
                    </div>
                    
                    <div class="absolute bottom-1 md:bottom-3 left-0 w-full text-center flex items-center justify-center gap-2">
                        <div id="capture-progress" class="hidden flex gap-1.5 md:gap-2">
                            <div class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full border border-gray-400" id="prog-1"></div>
                            <div class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full border border-gray-400" id="prog-2"></div>
                            <div class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full border border-gray-400" id="prog-3"></div>
                            <div class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full border border-gray-400" id="prog-4"></div>
                        </div>
                        <span id="viewfinder-brand" class="font-serif italic text-gray-400/80 tracking-widest text-[8px] md:text-[11px] uppercase">Anak Kecik</span>
                    </div>
                </div>
            </div>
            
        </div>
    </main>

    <!-- ==================== SCREEN 3: PROCESSING & RESULT ==================== -->
    <main id="screen-result" class="screen flex-col items-center justify-start min-h-screen w-full px-4 md:px-8 py-12 md:py-20 bg-[#f8f8f8] z-10 relative overflow-hidden">
        
        <!-- Animated Printer Slot Element -->
        <div id="printer-slot-overlay">
            <div id="printer-slot-line"></div>
        </div>
        
        <div class="w-full max-w-5xl mx-auto flex flex-col items-center h-full relative z-30 pt-4">
            
            <h2 id="result-title" class="text-2xl md:text-4xl lg:text-5xl font-serif text-maroon-950 mb-8 md:mb-12 tracking-wide text-center drop-shadow-sm">Developing layout...</h2>

            <!-- Result Wrapper (This is what animates OUT of the top slot) -->
            <div class="relative w-full flex justify-center perspective-[1000px]">
                <div id="polaroid-wrapper" class="relative shadow-2xl transition-all duration-500 z-10 opacity-0 translate-y-[-100%]">
                    <div id="polaroid-container" class="polaroid-frame bg-white rounded-sm">
                        <img id="result-img" class="block w-full h-auto object-contain transition-opacity duration-1000" src="" alt="Captured layout" />
                    </div>
                </div>
            </div>

            <!-- Result Actions -->
            <div id="result-actions" class="mt-12 md:mt-16 flex flex-col sm:flex-row gap-3 sm:gap-6 opacity-0 transition-opacity duration-1000 w-full max-w-lg justify-center z-40">
                <button id="btn-retake" class="w-full sm:w-auto px-8 md:px-10 py-3 md:py-4 bg-transparent border border-maroon-800 text-maroon-800 hover:bg-maroon-50 transition-colors uppercase tracking-widest text-[10px] md:text-xs font-semibold rounded-sm">
                    Start Over
                </button>
                <button id="btn-download" class="w-full sm:w-auto px-8 md:px-10 py-3 md:py-4 bg-maroon-900 text-white hover:bg-maroon-950 shadow-lg hover:shadow-xl transition-all uppercase tracking-widest text-[10px] md:text-xs font-semibold rounded-sm flex items-center justify-center gap-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download Memory
                </button>
            </div>

        </div>
    </main>

    <!-- Hidden final processing canvas -->
    <canvas id="offscreen-canvas" class="hidden"></canvas>


    <!-- Javascript App Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // State & Elements
            const state = {
                stream: null,
                isCapturing: false,
                layout: 'single', // single, strip_3, strip_4, grid
                filter: 'normal',
                color: '#ffffff',
                timerDelay: 3, 
                captionText: '',
                capturedPhotos: [],
                finalImageDataUrl: null,
                totalNeeded: 1
            };

            const screens = {
                welcome: document.getElementById('screen-welcome'),
                capture: document.getElementById('screen-capture'),
                result: document.getElementById('screen-result')
            };
            const darkBg = document.getElementById('dark-bg-elements');

            // Capture screen elements
            const video = document.getElementById('camera-stream');
            const camStatus = document.getElementById('cam-status');
            const btnSnap = document.getElementById('btn-snap');
            const countdownOverlay = document.getElementById('countdown-overlay');
            const countdownText = document.getElementById('countdown-text');
            const flashEl = document.getElementById('flash');
            const progressIndicators = document.getElementById('capture-progress');
            const shotsPreview = document.getElementById('total-shots-preview');
            const customCaptionInput = document.getElementById('custom-caption-input');

            // Result elements
            const resultTitle = document.getElementById('result-title');
            const resultImg = document.getElementById('result-img');
            const resultActions = document.getElementById('result-actions');
            const polaroidWrapper = document.getElementById('polaroid-wrapper');
            const polaroidContainer = document.getElementById('polaroid-container');

            // --- UI Interaction Handlers ---
            customCaptionInput.addEventListener('input', (e) => {
                state.captionText = e.target.value.trim();
            });

            document.querySelectorAll('.layout-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('.layout-btn').forEach(b => b.classList.remove('selected'));
                    e.currentTarget.classList.add('selected');
                    state.layout = e.currentTarget.dataset.layout;
                    
                    if(state.layout === 'single') state.totalNeeded = 1;
                    if(state.layout === 'strip_3') state.totalNeeded = 3;
                    if(state.layout === 'strip_4') state.totalNeeded = 4;
                    if(state.layout === 'grid') state.totalNeeded = 4;
                    
                    shotsPreview.innerText = state.totalNeeded;

                    // Update viewfinder shape
                    const vf = document.getElementById('viewfinder-container');
                    vf.classList.remove('aspect-[4/3]', 'aspect-[3/4]', 'aspect-[1/1]', 'aspect-[9/16]');
                    
                    if(state.layout === 'grid') vf.classList.add('aspect-[1/1]');
                    else if(state.layout === 'strip_3') vf.classList.add('aspect-[3/4]');
                    else if(state.layout === 'strip_4') vf.classList.add('aspect-[9/16]');
                    else vf.classList.add('aspect-[4/3]'); // single
                });
            });

            document.querySelectorAll('.timer-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('.timer-btn').forEach(b => b.classList.remove('selected'));
                    e.currentTarget.classList.add('selected');
                    state.timerDelay = parseInt(e.currentTarget.dataset.timer, 10);
                });
            });

            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('selected'));
                    const target = e.currentTarget;
                    target.classList.add('selected');
                    
                    video.className = video.className.replace(/filter-\w+/g, '');
                    state.filter = target.dataset.filter;
                    video.classList.add(`filter-${state.filter}`);
                });
            });

            document.querySelectorAll('.color-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('selected'));
                    e.currentTarget.classList.add('selected');
                    state.color = e.currentTarget.dataset.color;
                    
                    const vf = document.getElementById('viewfinder-container');
                    vf.style.backgroundColor = state.color;
                    
                    // Contrast logic for branding text
                    const brand = document.getElementById('viewfinder-brand');
                    const isLight = ['#ffffff', '#fdf3f4', '#f4e7d3'].includes(state.color.toLowerCase());
                    brand.style.color = isLight ? '#666' : '#bbb';
                });
            });

            // Navigation
            function switchScreen(screenKey) {
                Object.values(screens).forEach(s => s.classList.remove('active'));
                
                if (screenKey === 'capture') {
                    darkBg.classList.remove('hidden');
                } else {
                    darkBg.classList.add('hidden');
                }

                setTimeout(() => screens[screenKey].classList.add('active'), 10); // mobile rapid switch
            }

            document.getElementById('btn-enter').addEventListener('click', async () => {
                switchScreen('capture');
                await initializeCamera();
            });

            // --- CAMERA SETUP ---
            async function initializeCamera() {
                try {
                    camStatus.innerHTML = `
                        <div class="w-6 h-6 border-t-2 border-r-2 border-maroon-400 rounded-full animate-spin mb-3"></div>
                        <p class="font-serif tracking-widest text-[10px] text-gray-300">CONFIGURING LENS...</p>
                    `;
                    camStatus.classList.remove('hidden', 'opacity-0');
                    
                    state.stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } }, 
                        audio: false 
                    });
                    
                    video.srcObject = state.stream;
                    
                    video.onplaying = () => {
                        camStatus.classList.add('opacity-0');
                        setTimeout(() => camStatus.classList.add('hidden'), 300);
                        btnSnap.disabled = false;
                    };
                } catch (err) {
                    console.error("Camera error:", err);
                    camStatus.innerHTML = `
                        <svg class="w-8 h-8 text-red-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <p class="font-serif tracking-widest text-[9px] text-red-200 text-center uppercase">Hardware<br>Unavailable</p>
                    `;
                }
            }


            // --- CAPTURE SEQUENCE ---
            btnSnap.addEventListener('click', () => {
                if (state.isCapturing || !state.stream) return;
                state.isCapturing = true;
                btnSnap.disabled = true;
                state.capturedPhotos = [];

                if(state.totalNeeded > 1) {
                    progressIndicators.classList.remove('hidden');
                    for(let i=1; i<=4; i++) {
                        const dot = document.getElementById(`prog-${i}`);
                        if(i <= state.totalNeeded) {
                            dot.style.display = 'block';
                            dot.className = "w-2 h-2 md:w-2.5 md:h-2.5 rounded-full border border-white/50 bg-transparent";
                        } else {
                            dot.style.display = 'none';
                        }
                    }
                    document.getElementById('viewfinder-brand').classList.add('hidden');
                } else {
                    progressIndicators.classList.add('hidden');
                }

                startCaptureLoop(1);
            });

            function startCaptureLoop(currentPhotoNum) {
                // Ensure viewport scrolls to top on mobile so they see viewfinder
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                countdownOverlay.classList.remove('hidden');
                
                let count = state.timerDelay;
                countdownText.innerText = count;
                countdownText.classList.remove('animate-pulse-slow');
                void countdownText.offsetWidth;
                countdownText.classList.add('animate-pulse-slow');

                const countInterval = setInterval(() => {
                    count--;
                    if (count > 0) {
                        countdownText.innerText = count;
                        countdownText.classList.remove('animate-pulse-slow');
                        void countdownText.offsetWidth;
                        countdownText.classList.add('animate-pulse-slow');
                    } else {
                        clearInterval(countInterval);
                        countdownOverlay.classList.add('hidden');
                        captureAndSaveBuffer(currentPhotoNum);
                    }
                }, 1000);
            }

            function captureAndSaveBuffer(currentPhotoNum) {
                flashEl.classList.add('flashing');
                setTimeout(() => flashEl.classList.remove('flashing'), 120);

                if(state.totalNeeded > 1) {
                    const dot = document.getElementById(`prog-${currentPhotoNum}`);
                    dot.className = "w-2 h-2 md:w-2.5 md:h-2.5 rounded-full border border-white bg-white shadow-[0_0_8px_white]";
                }

                const tmpCanvas = document.createElement('canvas');
                tmpCanvas.width = 1200;
                tmpCanvas.height = 900; 
                const tCtx = tmpCanvas.getContext('2d');

                const vRatio = video.videoWidth / video.videoHeight;
                const cRatio = tmpCanvas.width / tmpCanvas.height;
                let dW = tmpCanvas.width, dH = tmpCanvas.height, oX = 0, oY = 0;

                if (vRatio > cRatio) {
                    dH = tmpCanvas.height; dW = video.videoWidth * (tmpCanvas.height / video.videoHeight);
                    oX = -(dW - tmpCanvas.width) / 2;
                } else {
                    dW = tmpCanvas.width; dH = video.videoHeight * (tmpCanvas.width / video.videoWidth);
                    oY = -(dH - tmpCanvas.height) / 2;
                }

                tCtx.save();
                tCtx.translate(tmpCanvas.width, 0);
                tCtx.scale(-1, 1);
                
                let filterStr = 'none';
                if(state.filter === 'bw') filterStr = 'grayscale(100%) contrast(1.1)';
                if(state.filter === 'sepia') filterStr = 'sepia(0.8) contrast(1.1) brightness(0.9)';
                if(state.filter === 'vintage') filterStr = 'sepia(0.3) saturate(1.4) contrast(1.1) hue-rotate(-10deg)';
                if(state.filter === 'retro') filterStr = 'contrast(1.2) saturate(1.5) sepia(0.2) hue-rotate(10deg)';
                if(state.filter === 'cool') filterStr = 'saturate(0.8) contrast(1.1) hue-rotate(15deg) brightness(0.95)';
                if(state.filter === 'warm') filterStr = 'sepia(0.4) saturate(1.2) contrast(1.05) hue-rotate(-5deg)';
                if(state.filter === 'faded') filterStr = 'contrast(0.8) saturate(0.6) brightness(1.1)';
                tCtx.filter = filterStr;

                tCtx.drawImage(video, oX, oY, dW, dH);
                tCtx.restore();

                state.capturedPhotos.push(tmpCanvas.toDataURL('image/jpeg', 0.95));

                if(currentPhotoNum < state.totalNeeded) {
                    setTimeout(() => {
                        startCaptureLoop(currentPhotoNum + 1);
                    }, 600); 
                } else {
                    stopCamera();
                    buildFinalLayout();
                }
            }

            function stopCamera() {
                if (state.stream) state.stream.getTracks().forEach(t => t.stop());
                state.stream = null;
                document.getElementById('viewfinder-brand').classList.remove('hidden');
                progressIndicators.classList.add('hidden');
            }


            // --- COMPOSITING AND RESULT ---
            function buildFinalLayout() {
                const c = document.getElementById('offscreen-canvas');
                const ctx = c.getContext('2d');
                
                const margin = 50; 
                const imageSpacing = 30;
                let cWidth = 1200;
                let cHeight = 1200; // BASE HEIGHT REDUCED DRASTICALLY 

                // Shape definitions with TIGHTER BOTTOM GAPS
                // The branding takes about ~160px height.
                if (state.layout === 'single') {
                    cWidth = 1200;
                    cHeight = margin + (900) + 180; // IMG + Padding Base
                } else if (state.layout === 'strip_3') {
                    cWidth = 600;
                    cHeight = margin + (3 * 375) + (2 * imageSpacing) + 160; 
                } else if (state.layout === 'strip_4') {
                    cWidth = 600;
                    cHeight = margin + (4 * 375) + (3 * imageSpacing) + 160;
                } else if (state.layout === 'grid') {
                    cWidth = 1200;
                    const h = ((1200 - (margin*2) - imageSpacing) / 2) * (3/4); // 480 * 0.75 = 382
                    cHeight = margin + (2 * h) + imageSpacing + 170;
                }

                c.width = cWidth;
                c.height = cHeight;

                // 1. Draw Background
                ctx.fillStyle = state.color;
                ctx.fillRect(0, 0, cWidth, cHeight);

                let loadedCount = 0;
                const imgObjects = [];

                state.capturedPhotos.forEach((src, idx) => {
                    const img = new Image();
                    img.onload = () => {
                        imgObjects[idx] = img;
                        loadedCount++;
                        if(loadedCount === state.totalNeeded) {
                            renderImagesOntoCanvas(c, ctx, imgObjects, cWidth, cHeight, margin, imageSpacing);
                        }
                    };
                    img.src = src;
                });
            }

            function renderImagesOntoCanvas(c, ctx, imgs, cWidth, cHeight, m, sp) {

                if (state.layout === 'single') {
                    const iW = cWidth - (m*2);
                    const iH = iW * (3/4); 
                    ctx.drawImage(imgs[0], m, m, iW, iH);
                    drawBranding(ctx, cWidth, cHeight, state.color, false);

                } else if (state.layout === 'strip_3' || state.layout === 'strip_4') {
                    const iW = cWidth - (m*2);
                    const iH = iW * (3/4);
                    imgs.forEach((img, idx) => {
                        ctx.drawImage(img, m, m + (idx * (iH + sp)), iW, iH);
                    });
                    drawBranding(ctx, cWidth, cHeight, state.color, true); 

                } else if (state.layout === 'grid') {
                    const iW = (cWidth - (m*2) - sp) / 2;
                    const iH = iW * (3/4);
                    ctx.drawImage(imgs[0], m, m, iW, iH);
                    ctx.drawImage(imgs[1], m + iW + sp, m, iW, iH);
                    ctx.drawImage(imgs[2], m, m + iH + sp, iW, iH);
                    ctx.drawImage(imgs[3], m + iW + sp, m + iH + sp, iW, iH);
                    drawBranding(ctx, cWidth, cHeight, state.color, false);
                }

                state.finalImageDataUrl = c.toDataURL('image/jpeg', 0.95);
                
                // Scale UI wrapper max sizes to look realistic
                if (state.layout === 'strip_3') polaroidWrapper.style.width = '240px';
                else if (state.layout === 'strip_4') polaroidWrapper.style.width = '200px';
                else if (state.layout === 'grid') polaroidWrapper.style.width = '380px';
                else polaroidWrapper.style.width = '400px'; // single
                
                polaroidWrapper.style.maxWidth = '90%';

                showResultScreen();
            }

            // BRANDING DRAW (New Reversed Order & Tighter Spacing)
            function drawBranding(ctx, cW, cH, frameColor, isStrip = false) {
                ctx.save();
                
                const isLightBg = ['#ffffff', '#fdf3f4', '#f4e7d3'].includes(frameColor.toLowerCase());
                ctx.fillStyle = isLightBg ? '#2b060e' : '#ffffff';
                ctx.textAlign = 'center';
                
                // Base Y anchor based on the tightly cropped canvas height
                const yAnchor = cH - (isStrip ? 40 : 50); 
                
                // 1. Optional Custom Text (TOP)
                if (state.captionText && state.captionText.length > 0) {
                    ctx.font = `italic ${isStrip ? '16px' : '22px'} 'Playfair Display', serif`;
                    ctx.fillText(state.captionText, cW/2, yAnchor - (isStrip ? 55 : 75));
                }
                
                // 2. Main Signature (MIDDLE)
                ctx.font = `${isStrip ? '36px' : '65px'} 'Mea Culpa', cursive`;
                ctx.fillText("Anak Kecik", cW/2, yAnchor - (isStrip ? 15 : 25));
                
                // 3. Date (BOTTOM)
                ctx.font = `italic ${isStrip ? '10px' : '14px'} 'Inter', sans-serif`;
                ctx.globalAlpha = 0.5;
                const today = new Date();
                const dOpts = { month: 'long', day: 'numeric', year: 'numeric' };
                ctx.fillText(`— ${today.toLocaleDateString('en-US', dOpts)} —`, cW/2, yAnchor);
                
                ctx.restore();
            }

            function showResultScreen() {
                switchScreen('result');
                
                // Reset Anim
                polaroidWrapper.classList.remove('animate-print');
                polaroidWrapper.style.transform = 'translateY(-100%)';
                polaroidWrapper.style.opacity = '0';
                
                resultTitle.innerText = "Developing layout...";
                resultTitle.style.opacity = '1';
                resultActions.style.opacity = '0';
                resultActions.classList.add('pointer-events-none');
                
                polaroidContainer.style.backgroundColor = state.color;
                resultImg.src = state.finalImageDataUrl;
                resultImg.style.opacity = '0'; // image fades in slightly as it "develops"
                
                // Start physical movement
                void polaroidWrapper.offsetWidth; 
                polaroidWrapper.classList.add('animate-print');

                setTimeout(() => {
                    // photo fades in while sliding
                    resultImg.style.opacity = '1';
                    
                    setTimeout(() => {
                        resultTitle.innerText = "Ready to keep.";
                        resultActions.style.opacity = '1';
                        resultActions.classList.remove('pointer-events-none');
                    }, 2500); 
                }, 500);
            }

            // --- RESULT ACTIONS ---
            document.getElementById('btn-retake').addEventListener('click', async () => {
                state.isCapturing = false;
                switchScreen('capture');
                btnSnap.disabled = true;
                await initializeCamera();
            });

            document.getElementById('btn-download').addEventListener('click', (e) => {
                e.preventDefault();
                const link = document.createElement('a');
                link.download = `booth-memory-${new Date().getTime()}.jpg`;
                link.href = state.finalImageDataUrl;
                link.click();
            });
        });
    </script>
</body>
</html>
