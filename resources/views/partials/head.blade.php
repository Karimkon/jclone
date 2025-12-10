<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name'))</title>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Tailwind Config -->
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { 
                    'body': ['Outfit', 'sans-serif'], 
                    'display': ['Sora', 'sans-serif'] 
                },
                colors: {
                    brand: { 
                        50: '#eef2ff', 
                        100: '#e0e7ff', 
                        200: '#c7d2fe', 
                        300: '#a5b4fc', 
                        400: '#818cf8', 
                        500: '#6366f1', 
                        600: '#4f46e5', 
                        700: '#4338ca', 
                        800: '#3730a3', 
                        900: '#312e81' 
                    },
                    mint: { 
                        400: '#34d399', 
                        500: '#10b981', 
                        600: '#059669' 
                    },
                    coral: { 
                        400: '#fb7185', 
                        500: '#f43f5e', 
                        600: '#e11d48' 
                    },
                    gold: { 
                        400: '#fbbf24', 
                        500: '#f59e0b', 
                        600: '#d97706' 
                    },
                    ink: { 
                        50: '#f8fafc', 
                        100: '#f1f5f9', 
                        200: '#e2e8f0', 
                        300: '#cbd5e1', 
                        400: '#94a3b8', 
                        500: '#64748b', 
                        600: '#475569', 
                        700: '#334155', 
                        800: '#1e293b', 
                        900: '#0f172a', 
                        950: '#020617' 
                    }
                },
                animation: {
                    'float': 'float 6s ease-in-out infinite',
                    'slide-up': 'slideUp 0.5s ease forwards',
                    'bounce-soft': 'bounceSoft 2s ease-in-out infinite',
                },
                keyframes: {
                    float: { 
                        '0%, 100%': { transform: 'translateY(0)' }, 
                        '50%': { transform: 'translateY(-8px)' } 
                    },
                    slideUp: { 
                        '0%': { opacity: '0', transform: 'translateY(20px)' }, 
                        '100%': { opacity: '1', transform: 'translateY(0)' } 
                    },
                    bounceSoft: { 
                        '0%, 100%': { transform: 'translateY(0)' }, 
                        '50%': { transform: 'translateY(-5px)' } 
                    },
                }
            }
        }
    }
</script>

<!-- Custom Styles -->
<style>
    * { -webkit-font-smoothing: antialiased; }
    body { font-family: 'Outfit', sans-serif; }
    h1, h2, h3, h4, h5, h6, .font-display { font-family: 'Sora', sans-serif; }
    
    .btn-primary { 
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); 
        color: white; 
        transition: all 0.3s ease; 
        box-shadow: 0 4px 15px rgba(99,102,241,0.3); 
    }
    .btn-primary:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 6px 20px rgba(99,102,241,0.4); 
    }
    
    .section-line { 
        position: relative; 
        padding-left: 16px; 
    }
    .section-line::before { 
        content: ''; 
        position: absolute; 
        left: 0; 
        top: 50%; 
        transform: translateY(-50%); 
        width: 4px; 
        height: 24px; 
        border-radius: 4px; 
    }
    .section-line.brand::before { 
        background: linear-gradient(180deg, #6366f1, #a855f7); 
    }
    
    .line-clamp-1 { 
        display: -webkit-box; 
        -webkit-line-clamp: 1; 
        -webkit-box-orient: vertical; 
        overflow: hidden; 
    }
    .line-clamp-2 { 
        display: -webkit-box; 
        -webkit-line-clamp: 2; 
        -webkit-box-orient: vertical; 
        overflow: hidden; 
    }
    
    html { scroll-behavior: smooth; }
</style>